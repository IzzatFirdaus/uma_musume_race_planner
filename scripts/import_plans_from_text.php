<?php

/**
 * Import Uma Musume plans from free-form text into the database.
 *
 * Usage:
 *   php scripts/import_plans_from_text.php --file=/path/to/plans.txt --user-id=5 [--dry-run] [--status=Planning]
 *   cat plans.txt | php scripts/import_plans_from_text.php --user-id=5 [--dry-run]
 *
 * Notes:
 * - Requires includes/db.php to return a PDO connection.
 * - Assumes schema similar to uma_musume_planner_230825.sql (plans.user_id is NOT NULL).
 * - Best-effort parser for your “PLAN …” blocks. It tolerates variations and missing fields.
 * - Skills: looks up skill_reference by exact name; if not found, creates a stub entry.
 * - Lookups (moods/conditions/strategies): created on the fly if absent.
 * - Goals: grouped as triples [name, target, result]; stored as goal = "name (target)", result = "result".
 * - Race predictions: parsed from the “RACE DAY PREDICTIONS” section if present.
 * - Plan status:
 *     - If provided via --status, uses that for all imported plans.
 *     - Otherwise deduced per plan by tags like [FINISHED], [CAREER END(S)], or “RACE DAY? ENDED!”
 *
 * Exit codes: 0 success, non-zero on fatal error.
 */

declare(strict_types=1);

// ---------- Config & Bootstrap ----------
ini_set('display_errors', '1');
error_reporting(E_ALL);

$root = dirname(__DIR__);

// Load DB (expects a PDO instance in returned value)
$pdo = require $root . '/includes/db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("SET NAMES utf8mb4");

// ---------- CLI Args ----------
$shortopts = "";
$longopts  = [
    "file::",       // optional: path to input file; if omitted, read STDIN
    "user-id:",     // required: user id for plans.user_id
    "dry-run::",    // optional flag
    "status::",     // optional: override status for all plans (Planning|Active|Finished|Draft|Abandoned)
    "help::",
];
$args = getopt($shortopts, $longopts);

if (isset($args['help'])) {
    echo "Usage: php scripts/import_plans_from_text.php --file=/path/to/plans.txt --user-id=5 [--dry-run] [--status=Planning]\n";
    exit(0);
}

$userId = isset($args['user-id']) ? (int)$args['user-id'] : 0;
if ($userId <= 0) {
    fwrite(STDERR, "Error: --user-id is required and must be > 0\n");
    exit(2);
}
$dryRun = array_key_exists('dry-run', $args);
$overrideStatus = isset($args['status']) ? trim((string)$args['status']) : null;
if ($overrideStatus && !in_array($overrideStatus, ['Planning','Active','Finished','Draft','Abandoned'], true)) {
    fwrite(STDERR, "Error: --status must be one of Planning|Active|Finished|Draft|Abandoned\n");
    exit(2);
}

$input = '';
if (isset($args['file']) && is_string($args['file']) && $args['file'] !== '') {
    if (!is_file($args['file'])) {
        fwrite(STDERR, "Error: File not found: {$args['file']}\n");
        exit(2);
    }
    $input = file_get_contents($args['file']);
} else {
    // Read from STDIN
    $input = stream_get_contents(STDIN);
}

if (!is_string($input) || trim($input) === '') {
    fwrite(STDERR, "Error: No input data provided. Use --file or pipe data via STDIN.\n");
    exit(2);
}

// ---------- Utilities ----------
function norm(string $s): string
{
    // Normalize whitespace, keep emoji/symbols, trim edges
    $s = preg_replace('/[ \t]+/u', ' ', $s);
    $s = preg_replace('/\h+/u', ' ', $s);
    return trim($s);
}

function normGrade(?string $g): ?string
{
    if ($g === null) {
        return null;
    }
    $g = strtoupper(preg_replace('/\s+/', '', $g));
    // Normalize cases like "C+" where a stray space may appear "C +"
    if (preg_match('/^[SABCDEFGL][+]?$/', $g)) {
        return $g;
    }
    // Accept basic grades like G, F, E, D, C, B, A, S with optional +
    if (preg_match('/^([SABCDEFGL])\s*\+?$/', $g, $m)) {
        return $m[1] . (str_contains($g, '+') ? '+' : '');
    }
    return $g;
}

function toIntOrNull(?string $v): ?int
{
    $v = $v !== null ? trim($v) : null;
    if ($v === null || $v === '' || strtoupper($v) === 'N/A') {
        return null;
    }
    if (preg_match('/^-?\d+$/', $v)) {
        return (int)$v;
    }
    // Try extract first integer
    if (preg_match('/-?\d+/', $v, $m)) {
        return (int)$m[0];
    }
    return null;
}

function yesNoToEnum(?string $v): ?string
{
    if ($v === null) {
        return null;
    }
    $v = strtoupper(trim($v));
    if (str_starts_with($v, 'Y')) {
        return 'yes';
    }
    if (str_starts_with($v, 'N')) {
        return 'no';
    }
    if (str_starts_with($v, 'ENDED')) {
        return 'no';
    }
    return null;
}

function acquireSkillEnum(?string $v): ?string
{
    if ($v === null) {
        return null;
    }
    $v = strtoupper(trim($v));
    if (str_starts_with($v, 'Y')) {
        return 'YES';
    }
    if (str_starts_with($v, 'N')) {
        return 'NO';
    }
    if ($v === 'N/A') {
        return 'NO';
    }
    return null;
}

function detectStatusTag(string $block): ?string
{
    // If tags like [FINISHED], [CAREER END(S)], treat as Finished
    if (preg_match('/\[(FINISHED|CAREER\s*END\S*)\]/i', $block)) {
        return 'Finished';
    }
    // If "RACE DAY? ENDED" appears, likely finished
    if (preg_match('/RACE\s*DAY\?\s*ENDED!?/i', $block)) {
        return 'Finished';
    }
    return null;
}

function detectCareerParts(string $line): array
{
    // Attempt to parse "SENIOR YEAR LATE APR" or "CLASSIC YEAR EARLY NOV"
    $careerStage = null;
    $timeOfDay = null;
    $month = null;
    $s = strtoupper(norm($line));
    if (str_contains($s, 'PREDEBUT')) {
        $careerStage = 'predebut';
    } elseif (str_contains($s, 'JUNIOR')) {
        $careerStage = 'junior';
    } elseif (str_contains($s, 'CLASSIC')) {
        $careerStage = 'classic';
    } elseif (str_contains($s, 'SENIOR')) {
        $careerStage = 'senior';
    } elseif (str_contains($s, 'FINALE')) {
        $careerStage = 'finale';
    }

    if (str_contains($s, 'EARLY')) {
        $timeOfDay = 'EARLY';
    } elseif (str_contains($s, 'LATE')) {
        $timeOfDay = 'LATE';
    }

    // Month tokens: JAN..DEC, also 'NOV', 'APR', etc.
    if (preg_match('/\b(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\b/i', $line, $m)) {
        $month = strtoupper($m[1]);
    }
    return [$careerStage, $timeOfDay, $month];
}

function detectClassToken(string $line): ?string
{
    $s = strtoupper(norm($line));
    $classes = ['DEBUT','MAIDEN','BEGINNER','BRONZE','SILVER','GOLD','PLATINUM','STAR','LEGEND'];
    foreach ($classes as $c) {
        if (preg_match('/\b' . preg_quote($c, '/') . '\b/', $s)) {
            return strtolower($c);
        }
    }
    return null;
}

function sanitizeSkillName(string $name): string
{
    return norm($name);
}

function acquiredFromSymbol(?string $s): ?string
{
    if ($s === null) {
        return null;
    }
    $s = trim($s);
    if (str_contains($s, '✅')) {
        return 'yes';
    }
    if (str_contains($s, '❌')) {
        return 'no';
    }
    // fallback: explicit yes/no strings
    $upper = strtoupper($s);
    if (str_starts_with($upper, 'Y')) {
        return 'yes';
    }
    if (str_starts_with($upper, 'N')) {
        return 'no';
    }
    return null;
}

// ---------- DB helper lookups ----------
function findOrCreateLookup(PDO $pdo, string $table, string $label): int
{
    $label = norm($label);
    $sqlSel = "SELECT id FROM {$table} WHERE label = :label LIMIT 1";
    $stmt = $pdo->prepare($sqlSel);
    $stmt->execute([':label' => $label]);
    $id = $stmt->fetchColumn();
    if ($id !== false) {
        return (int)$id;
    }

    $sqlIns = "INSERT INTO {$table} (label) VALUES (:label)";
    $stmt = $pdo->prepare($sqlIns);
    $stmt->execute([':label' => $label]);
    return (int)$pdo->lastInsertId();
}

function findOrCreateSkillReference(PDO $pdo, string $name): int
{
    $name = norm($name);
    $stmt = $pdo->prepare("SELECT id FROM skill_reference WHERE skill_name = :name LIMIT 1");
    $stmt->execute([':name' => $name]);
    $id = $stmt->fetchColumn();
    if ($id !== false) {
        return (int)$id;
    }

    // Create stub; description/stat_type/best_for/tag unknown
    $stmt = $pdo->prepare("INSERT INTO skill_reference (skill_name) VALUES (:name)");
    $stmt->execute([':name' => $name]);
    return (int)$pdo->lastInsertId();
}

// ---------- Parser ----------
/**
 * Split input into plan blocks by lines that start with "PLAN".
 * Returns array of strings, each a plan block.
 */
function splitPlans(string $input): array
{
    // Normalize line endings
    $input = str_replace("\r\n", "\n", $input);
    $input = str_replace("\r", "\n", $input);
    // Add sentinel at start to help split
    $input = "\n" . $input;
    $parts = preg_split('/\n(?=PLAN\b)/u', $input);
    $blocks = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p === '' || !str_starts_with($p, 'PLAN')) {
            continue;
        }
        $blocks[] = $p;
    }
    return $blocks;
}

/**
 * Parse one plan block into a structured associative array.
 * The function is heuristic and resilient to missing or extra lines.
 */
function parsePlanBlock(string $block): array
{
    $lines = array_values(array_filter(array_map(fn ($l) => rtrim($l, " \t"), explode("\n", $block)), fn ($l) => true));
    $idx = 0;
    $N = count($lines);

    $plan = [
        'raw_tag_status' => detectStatusTag($block),
        'turn_before' => null,
        'race_name' => null,
        'plan_title' => null, // not present explicitly; we may derive from Name
        'name' => null,
        'career_stage' => null,
        'class' => null,
        'time_of_day' => null,
        'month' => null,
        'total_available_skill_points' => null,
        'acquire_skill' => null, // YES/NO
        'mood' => null,
        'condition' => null,
        'strategy' => null,
        'energy' => null,
        'race_day' => null, // yes/no
        'goal' => null,
        'growth_rates' => ['speed' => 0,'stamina' => 0,'power' => 0,'guts' => 0,'wit' => 0],
        'attributes' => [], // [ ['attribute_name'=>'SPEED','value'=>..,'grade'=>..], ... ]
        'terrain_grades' => [], // [ ['terrain'=>'Turf','grade'=>'A'], ... ]
        'distance_grades' => [],
        'style_grades' => [],
        'skills' => [], // [ ['name'=>..., 'sp_cost'=>..., 'acquired'=>yes/no, 'notes'=>...], ... ]
        'predictions' => [], // at most one in examples
        'goals' => [], // [ ['goal'=>..., 'result'=>...], ... ]
    ];

    // Helpers to find line indexes for section headers
    $findIndex = function (string $pattern) use ($lines): ?int {
        foreach ($lines as $i => $l) {
            if (preg_match($pattern, $l)) {
                return $i;
            }
        }
        return null;
    };

    // TURN BEFORE section
    $iTurn = $findIndex('/^TURN\s+BEFORE/i');
    if ($iTurn !== null) {
        // value likely on same line following or next non-empty line(s)
        $val = null;
        // try next line(s)
        for ($j = $iTurn + 1; $j < min($iTurn + 4, $N); $j++) {
            $cand = trim($lines[$j]);
            if ($cand === '' || preg_match('/^(RACE|Name|Attribute|Skill Name)/i', $cand)) {
                break;
            }
            $val = $cand;
            break;
        }
        $plan['turn_before'] = toIntOrNull($val);
    }

    // RACE section
    $iRace = $findIndex('/^RACE\b/i');
    if ($iRace !== null) {
        for ($j = $iRace + 1; $j < min($iRace + 4, $N); $j++) {
            $cand = trim($lines[$j]);
            if ($cand === '' || preg_match('/^(Name|Attribute|Skill Name|PLAN|\*Start|TURN\s+BEFORE|[A-Z ]+:)/i', $cand)) {
                continue;
            }
            $plan['race_name'] = norm($cand);
            break;
        }
    }

    // Name / Career Stage / Class section
    $iNameHdr = $findIndex('/^Name$/i');
    if ($iNameHdr !== null) {
        // Expect 3-6 lines after header for values
        $nameLine = null;
        $careerLine = null;
        $classLine = null;
        $j = $iNameHdr + 1;
        $grabbed = 0;
        for (; $j < min($iNameHdr + 10, $N) && $grabbed < 3; $j++) {
            $cand = trim($lines[$j]);
            if ($cand === '' || preg_match('/^(Attribute|Skill Name|TOTAL AVAILABLE SKILL POINTS|CONDITIONS?|GROWTH RATE|\*|RACE DAY PREDICTIONS|PLAN)/i', $cand)) {
                break;
            }
            if ($nameLine === null) {
                $nameLine = $cand;
                $grabbed++;
                continue;
            }
            if ($careerLine === null) {
                $careerLine = $cand;
                $grabbed++;
                continue;
            }
            if ($classLine === null) {
                $classLine = $cand;
                $grabbed++;
                continue;
            }
        }
        if ($nameLine) {
            $plan['name'] = norm($nameLine);
            // Derive plan_title as "[Name] Plan" if no explicit title present
            $plan['plan_title'] = $plan['name'] . ' Plan';
        }
        if ($careerLine) {
            [$stage,$tod,$mon] = detectCareerParts($careerLine);
            $plan['career_stage'] = $stage;
            $plan['time_of_day'] = $tod;
            $plan['month'] = $mon;
            // Sometimes month/time/class spread over next lines; check classLine too
            [$stage2,$tod2,$mon2] = detectCareerParts($classLine ?? '');
            if (!$plan['career_stage'] && $stage2) {
                $plan['career_stage'] = $stage2;
            }
            if (!$plan['time_of_day'] && $tod2) {
                $plan['time_of_day'] = $tod2;
            }
            if (!$plan['month'] && $mon2) {
                $plan['month'] = $mon2;
            }
        }
        $classToken = detectClassToken(($classLine ?? '') . ' ' . ($careerLine ?? ''));
        if ($classToken) {
            $plan['class'] = $classToken;
        }
        // In some blocks class appears further lines; try a few more
        for ($k = $iNameHdr + 1; $k < $iNameHdr + 10; $k++) {
            if ($k >= $N) {
                break;
            }
            $ct = detectClassToken($lines[$k]);
            if ($ct) {
                $plan['class'] = $ct;
                break;
            }
        }
    }

    // Attributes section
    $iAttrHdr = $findIndex('/^Attribute$/i');
    if ($iAttrHdr !== null) {
        // After a header trio lines until we hit next header (Skill Name or TOTAL or Conditions etc.)
        $j = $iAttrHdr + 1;
        // Skip column headers if present
        while ($j < $N && preg_match('/^(Value|Grade)/i', trim($lines[$j]))) {
            $j++;
        }
        $attrs = [];
        $expected = ['SPEED','STAMINA','POWER','GUTS','WIT'];
        $seen = 0;
        while ($j < $N) {
            $line = trim($lines[$j]);
            if ($line === '' || preg_match('/^(Skill Name|TOTAL AVAILABLE SKILL POINTS|Conditions?|Mood|ENERGY|RACE DAY\?|GOAL|GROWTH RATE|Terrain|Distance|Style|RACE DAY PREDICTIONS|PLAN)/i', $line)) {
                break;
            }
            $name = strtoupper(norm($line));
            $val = null;
            $grade = null;
            // Try read next two for value + grade
            if ($j + 1 < $N) {
                $val = trim($lines[$j + 1]);
            }
            if ($j + 2 < $N) {
                $grade = trim($lines[$j + 2]);
            }
            $attrs[] = [
                'attribute_name' => $name,
                'value' => toIntOrNull($val) ?? 0,
                'grade' => normGrade($grade),
            ];
            $seen++;
            $j += 3;
            if ($seen >= 5) {
                break;
            }
        }
        $plan['attributes'] = $attrs;
    }

    // Skills section
    $iSkillHdr = $findIndex('/^Skill Name$/i');
    if ($iSkillHdr !== null) {
        // Read until TOTAL AVAILABLE SKILL POINTS
        $skills = [];
        $j = $iSkillHdr + 1;
        // Skip column headers if present
        $skipHeaders = ['SP Cost','Acquired (✅/❌)','Notes'];
        for ($k = 0; $k < 4 && $j < $N; $k++) {
            $cand = trim($lines[$j]);
            if (in_array($cand, $skipHeaders, true)) {
                $j++;
                continue;
            }
            break;
        }
        $current = ['name' => null,'sp_cost' => null,'acquired' => null,'notes' => ''];
        $stopPattern = '/^(TOTAL AVAILABLE SKILL POINTS|CONDITIONS?|Mood|ENERGY|RACE DAY\?|GOAL|GROWTH RATE|Terrain|Distance|Style|RACE DAY PREDICTIONS|PLAN)/i';
        for (; $j < $N; $j++) {
            $line = rtrim($lines[$j]);
            $t = trim($line);
            if ($t === '') {
                continue;
            }
            if (preg_match($stopPattern, $t)) {
                break;
            }

            // If looks like a new skill line
            $looksLikeNewSkill = (!preg_match('/^(N\/A|\d+|✅|❌)$/u', $t)) &&
                                 (!str_starts_with($t, '(')) &&
                                 (!preg_match('/^\d+,\d+|\d+\.\d+$/', $t));
            if ($looksLikeNewSkill && $current['name']) {
                // finalize previous if it has at least a name
                $skills[] = [
                    'name' => sanitizeSkillName((string)$current['name']),
                    'sp_cost' => $current['sp_cost'],
                    'acquired' => $current['acquired'] ?? 'no',
                    'notes' => trim($current['notes']),
                ];
                $current = ['name' => null,'sp_cost' => null,'acquired' => null,'notes' => ''];
            }
            if ($current['name'] === null && $looksLikeNewSkill) {
                $current['name'] = $t;
                continue;
            }
            // Not a new name => try categorize as cost / acquired / notes
            if ($current['sp_cost'] === null && (preg_match('/^\d+$/', $t) || strtoupper($t) === 'N/A')) {
                $current['sp_cost'] = strtoupper($t) === 'N/A' ? null : $t;
                continue;
            }
            if ($current['acquired'] === null && (str_contains($t, '✅') || str_contains($t, '❌') || preg_match('/^(YES|NO)$/i', $t))) {
                $current['acquired'] = acquiredFromSymbol($t) ?? 'no';
                continue;
            }
            // Otherwise append to notes
            $current['notes'] = trim($current['notes'] . ' ' . $t);
        }
        if ($current['name']) {
            $skills[] = [
                'name' => sanitizeSkillName((string)$current['name']),
                'sp_cost' => $current['sp_cost'],
                'acquired' => $current['acquired'] ?? 'no',
                'notes' => trim($current['notes']),
            ];
        }
        $plan['skills'] = $skills;
    }

    // Totals
    $iTot = $findIndex('/^TOTAL AVAILABLE SKILL POINTS$/i');
    if ($iTot !== null) {
        $v = null;
        for ($j = $iTot + 1; $j < $iTot + 3 && $j < $N; $j++) {
            $cand = trim($lines[$j]);
            if ($cand !== '') {
                $v = $cand;
                break;
            }
        }
        $plan['total_available_skill_points'] = toIntOrNull($v);
    }
    $iAcquire = $findIndex('/^ACQUIRE SKILL\?/i');
    if ($iAcquire !== null) {
        $v = null;
        for ($j = $iAcquire + 1; $j < $iAcquire + 3 && $j < $N; $j++) {
            $cand = trim($lines[$j]);
            if ($cand !== '') {
                $v = $cand;
                break;
            }
        }
        $plan['acquire_skill'] = acquireSkillEnum($v) ?? 'NO';
    }

    // Conditions / Mood / Energy / Race day / Goal / Strategy
    $mapSimple = function (string $header) use ($lines, $N): ?string {
        $i = null;
        foreach ($lines as $idx => $l) {
            if (preg_match('/^' . preg_quote($header, '/') . '$/i', trim($l))) {
                $i = $idx;
                break;
            }
        }
        if ($i === null) {
            return null;
        }
        for ($j = $i + 1; $j < $i + 4 && $j < $N; $j++) {
            $cand = trim($lines[$j]);
            if ($cand !== '' && !preg_match('/^[A-Z ]+\:?$/', $cand)) {
                return $cand;
            }
        }
        return null;
    };
    $plan['condition'] = $mapSimple('Conditions') ?? $mapSimple('CONDITIONS');
    $plan['mood'] = $mapSimple('Mood') ?? $mapSimple('MOOD');
    $energyLabel = $mapSimple('ENERGY') ?? $mapSimple('ENERGY (n/100%)') ?? $mapSimple('ENERGY (%)');
    $plan['energy'] = toIntOrNull($energyLabel);
    $raceDay = $mapSimple('RACE DAY?') ?? $mapSimple('RACE DAY');
    $plan['race_day'] = yesNoToEnum($raceDay) ?? 'no';
    $plan['goal'] = $mapSimple('GOAL');
    $plan['strategy'] = $mapSimple('STRATEGY');

    // Growth rates
    $iGR = $findIndex('/^GROWTH RATE/i');
    if ($iGR !== null) {
        // Read a few lines with stat names and possibly numbers further below
        $stats = ['SPEED','STAMINA','POWER','GUTS','WIT'];
        $values = [];
        // Collect next ~12 non-empty lines
        $buf = [];
        for ($j = $iGR + 1; $j < $N && $j < $iGR + 20; $j++) {
            $t = trim($lines[$j]);
            if ($t === '') {
                continue;
            }
            if (preg_match('/^(Terrain|Distance|Style|RACE DAY PREDICTIONS|PLAN|Attribute|Skill Name)/i', $t)) {
                break;
            }
            $buf[] = $t;
        }
        // Strategy: map stat names present in buf, take next numeric token as value
        for ($i = 0; $i < count($buf); $i++) {
            $label = strtoupper(norm($buf[$i]));
            $key = null;
            foreach ($stats as $sname) {
                if ($label === $sname) {
                    $key = strtolower($sname);
                    break;
                }
            }
            if ($key !== null) {
                // find next numeric-like
                for ($k = $i + 1; $k < count($buf); $k++) {
                    $v = toIntOrNull($buf[$k]);
                    if ($v !== null) {
                        $values[$key] = $v;
                        break;
                    }
                }
            }
        }
        $plan['growth_rates'] = array_merge($plan['growth_rates'], $values);
    }

    // Terrain grades
    $iTerr = $findIndex('/^Terrain$/i');
    if ($iTerr !== null) {
        // Next lines alternate: label, grade; until next header
        $j = $iTerr + 1;
        // Skip "Grade" line if present
        if ($j < $N && preg_match('/^Grade$/i', trim($lines[$j]))) {
            $j++;
        }
        for (; $j + 1 < $N; $j += 2) {
            $lab = trim($lines[$j]);
            $gr = trim($lines[$j + 1]);
            if ($lab === '' || preg_match('/^(Distance|Style|RACE DAY|PLAN|Attribute|Skill Name|GROWTH RATE)/i', $lab)) {
                break;
            }
            $plan['terrain_grades'][] = ['terrain' => norm($lab), 'grade' => normGrade($gr)];
        }
    }
    // Distance grades
    $iDist = $findIndex('/^Distance$/i');
    if ($iDist !== null) {
        $j = $iDist + 1;
        if ($j < $N && preg_match('/^Grade$/i', trim($lines[$j]))) {
            $j++;
        }
        for (; $j + 1 < $N; $j += 2) {
            $lab = trim($lines[$j]);
            $gr = trim($lines[$j + 1]);
            if ($lab === '' || preg_match('/^(Style|RACE DAY|PLAN|Attribute|Skill Name|GROWTH RATE)/i', $lab)) {
                break;
            }
            $plan['distance_grades'][] = ['distance' => norm($lab), 'grade' => normGrade($gr)];
        }
    }
    // Style grades
    $iStyle = $findIndex('/^Style$/i');
    if ($iStyle !== null) {
        $j = $iStyle + 1;
        if ($j < $N && preg_match('/^Grade$/i', trim($lines[$j]))) {
            $j++;
        }
        for (; $j + 1 < $N; $j += 2) {
            $lab = trim($lines[$j]);
            $gr = trim($lines[$j + 1]);
            if ($lab === '' || preg_match('/^(RACE DAY|PLAN|Attribute|Skill Name|GROWTH RATE|Terrain|Distance)/i', $lab)) {
                break;
            }
            $plan['style_grades'][] = ['style' => norm($lab), 'grade' => normGrade($gr)];
        }
    }

    // Race Day Predictions
    $iPred = $findIndex('/^RACE DAY PREDICTIONS:/i');
    if ($iPred !== null) {
        $j = $iPred + 1;
        $pred = [
            'race_name' => null,
            'venue' => null,
            'ground' => null,
            'distance' => null,
            'track_condition' => null,
            'direction' => null,
            'speed' => null,
            'stamina' => null,
            'power' => null,
            'guts' => null,
            'wit' => null,
            'comment' => null,
        ];
        // Expect a set: grade class (G1/G2...), race name, venue, ground, distance, distance-class, direction
        $buf = [];
        for (; $j < $N && count($buf) < 12; $j++) {
            $t = trim($lines[$j]);
            if ($t === '') {
                continue;
            }
            if (preg_match('/^(SPEED|STAMINA|POWER|GUTS|WIT)$/i', $t)) {
                $buf[] = $t;
                break;
            }
            $buf[] = $t;
        }
        // Map best we can
        // e.g., [G1, TENNO SHO (SPRING), KYOTO, TURF, 3200M, LONG, RIGHT/OUTER]
        if (count($buf) >= 2) {
            $pred['race_name'] = norm($buf[1]);
        }
        if (count($buf) >= 3) {
            $pred['venue'] = norm($buf[2] ?? '');
        }
        if (count($buf) >= 4) {
            $pred['ground'] = norm($buf[3] ?? '');
        }
        if (count($buf) >= 5) {
            $pred['distance'] = norm($buf[4] ?? '');
        }
        if (count($buf) >= 6) {
            $pred['track_condition'] = norm($buf[5] ?? '');
        }
        if (count($buf) >= 7) {
            $pred['direction'] = norm($buf[6] ?? '');
        }

        // Next, stats labels then values lines (5 labels then a line with 5 symbols)
        // Consume labels SPEED..WIT
        for ($k = 0; $k < 5 && $j < $N; $k++, $j++) {
            if ($j < $N && !preg_match('/^(SPEED|STAMINA|POWER|GUTS|WIT)$/i', trim($lines[$j]))) {
                break;
            }
        }
        // Next non-empty line should have the symbols for 5 stats (may be separated by spaces)
        for (; $j < $N; $j++) {
            $symLine = trim($lines[$j]);
            if ($symLine === '') {
                continue;
            }
            // Attempt to extract first 5 symbols
            $symbols = preg_split('/\s+/', $symLine);
            // Sometimes the first symbols line is a comment; try next line if less than 5
            if (count($symbols) < 5 && $j + 1 < $N) {
                $next = trim($lines[$j + 1]);
                $nparts = preg_split('/\s+/', $next);
                if (count($nparts) >= 5) {
                    $symbols = $nparts;
                    $j++;
                }
            }
            $map = ['speed','stamina','power','guts','wit'];
            for ($m = 0; $m < 5 && $m < count($symbols); $m++) {
                $pred[$map[$m]] = $symbols[$m];
            }
            // Try to capture a quoted comment on next line(s)
            if ($j + 1 < $N) {
                $q = trim($lines[$j + 1]);
                if ($q !== '' && ($q[0] === '"' || $q[0] === '“' || $q[0] === "'")) {
                    $pred['comment'] = trim($q, "\"'“”");
                }
            }
            break;
        }
        // Store only if we have at least a race name or any stat
        if ($pred['race_name'] || $pred['speed']) {
            $plan['predictions'][] = $pred;
        }
    }

    // GOALS: parse as triples [name, target, result]
    $iGoals = $findIndex('/^GOALS$/i');
    if ($iGoals !== null) {
        $j = $iGoals + 1;
        $buf = [];
        for (; $j < $N; $j++) {
            $t = trim($lines[$j]);
            if ($t === '') {
                continue;
            }
            if (preg_match('/^(PLAN|RACE DAY PREDICTIONS|Attribute|Skill Name|GROWTH RATE|Terrain|Distance|Style|TURN\s+BEFORE|Name)$/i', $t)) {
                break;
            }
            $buf[] = $t;
        }
        for ($k = 0; $k + 2 < count($buf); $k += 3) {
            $gname = norm($buf[$k]);
            $target = norm($buf[$k + 1]);
            $result = norm($buf[$k + 2]);
            $goalText = $gname;
            if ($target !== '' && strtoupper($target) !== 'N/A') {
                $goalText .= " ({$target})";
            }
            $plan['goals'][] = ['goal' => $goalText, 'result' => $result];
        }
    }

    return $plan;
}

// ---------- Insertion ----------
function insertPlan(PDO $pdo, int $userId, array $plan, ?string $overrideStatus = null, bool $dryRun = false): int
{
    // Resolve lookups
    $moodId = null;
    if (!empty($plan['mood']) && strtoupper($plan['mood']) !== 'N/A') {
        $moodId = findOrCreateLookup($pdo, 'moods', (string)$plan['mood']);
    }
    $conditionId = null;
    if (!empty($plan['condition']) && strtoupper($plan['condition']) !== 'N/A') {
        $conditionId = findOrCreateLookup($pdo, 'conditions', (string)$plan['condition']);
    }
    $strategyId = null;
    if (!empty($plan['strategy']) && strtoupper($plan['strategy']) !== 'N/A') {
        $strategyId = findOrCreateLookup($pdo, 'strategies', strtoupper((string)$plan['strategy']));
    }

    $status = $overrideStatus ?? ($plan['raw_tag_status'] ?? 'Planning');

    $sql = "INSERT INTO plans
        (user_id, plan_title, turn_before, race_name, name, career_stage, class, time_of_day, month,
         total_available_skill_points, acquire_skill, mood_id, condition_id, energy, race_day, goal,
         strategy_id, growth_rate_speed, growth_rate_stamina, growth_rate_power, growth_rate_guts, growth_rate_wit,
         status, source, trainee_image_path, deleted_at, created_at, updated_at)
        VALUES
        (:user_id, :plan_title, :turn_before, :race_name, :name, :career_stage, :class, :time_of_day, :month,
         :total_sp, :acquire_skill, :mood_id, :condition_id, :energy, :race_day, :goal,
         :strategy_id, :gr_speed, :gr_stamina, :gr_power, :gr_guts, :gr_wit,
         :status, :source, NULL, NULL, NOW(), NOW())";

    $params = [
        ':user_id' => $userId,
        ':plan_title' => $plan['plan_title'] ?? ($plan['name'] ? $plan['name'] . ' Plan' : null),
        ':turn_before' => $plan['turn_before'],
        ':race_name' => $plan['race_name'],
        ':name' => $plan['name'],
        ':career_stage' => $plan['career_stage'],
        ':class' => $plan['class'],
        ':time_of_day' => $plan['time_of_day'],
        ':month' => $plan['month'],
        ':total_sp' => $plan['total_available_skill_points'],
        ':acquire_skill' => $plan['acquire_skill'] ?? 'NO',
        ':mood_id' => $moodId,
        ':condition_id' => $conditionId,
        ':energy' => $plan['energy'],
        ':race_day' => $plan['race_day'] ?? 'no',
        ':goal' => $plan['goal'],
        ':strategy_id' => $strategyId,
        ':gr_speed' => (int)($plan['growth_rates']['speed'] ?? 0),
        ':gr_stamina' => (int)($plan['growth_rates']['stamina'] ?? 0),
        ':gr_power' => (int)($plan['growth_rates']['power'] ?? 0),
        ':gr_guts'  => (int)($plan['growth_rates']['guts'] ?? 0),
        ':gr_wit'   => (int)($plan['growth_rates']['wit'] ?? 0),
        ':status' => $status,
        ':source' => 'Imported from text',
    ];

    if ($dryRun) {
        echo "[DRY-RUN] Would insert plan: " . json_encode($params, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        // Return a fake ID
        return -1;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $planId = (int)$pdo->lastInsertId();

    // Log activity
    $stmt = $pdo->prepare("INSERT INTO activity_log (description, icon_class) VALUES (:desc, 'bi-upload')");
    $stmt->execute([':desc' => 'Imported plan: ID ' . $planId . ' (' . ($plan['name'] ?? 'Unknown') . ')']);

    return $planId;
}

function insertAttributes(PDO $pdo, int $planId, array $attributes, bool $dryRun = false): void
{
    if (empty($attributes)) {
        return;
    }
    $sql = "INSERT INTO attributes (plan_id, attribute_name, value, grade)
            VALUES (:pid, :name, :value, :grade)";
    $stmt = $pdo->prepare($sql);
    foreach ($attributes as $a) {
        $params = [
            ':pid' => $planId,
            ':name' => strtoupper((string)$a['attribute_name']),
            ':value' => (int)($a['value'] ?? 0),
            ':grade' => $a['grade'] ?? null,
        ];
        if ($dryRun) {
            echo "[DRY-RUN] Would insert attribute: " . json_encode($params, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            continue;
        }
        $stmt->execute($params);
    }
}

function insertGrades(PDO $pdo, int $planId, array $grades, string $table, string $col, bool $dryRun = false): void
{
    if (empty($grades)) {
        return;
    }
    $sql = "INSERT INTO {$table} (plan_id, {$col}, grade) VALUES (:pid, :key, :grade)";
    $stmt = $pdo->prepare($sql);
    foreach ($grades as $g) {
        $params = [
            ':pid' => $planId,
            ':key' => $g[$col],
            ':grade' => $g['grade'] ?? null,
        ];
        if ($dryRun) {
            echo "[DRY-RUN] Would insert grade into {$table}: " . json_encode($params, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            continue;
        }
        $stmt->execute($params);
    }
}

function insertSkills(PDO $pdo, int $planId, array $skills, bool $dryRun = false): void
{
    if (empty($skills)) {
        return;
    }
    $sql = "INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, tag, notes)
            VALUES (:pid, :sid, :sp_cost, :acquired, :tag, :notes)";
    $stmt = $pdo->prepare($sql);
    foreach ($skills as $s) {
        $name = sanitizeSkillName((string)$s['name']);
        if ($name === '') {
            continue;
        }
        $refId = findOrCreateSkillReference($pdo, $name);
        $params = [
            ':pid' => $planId,
            ':sid' => $refId,
            ':sp_cost' => isset($s['sp_cost']) ? (preg_match('/^\d+$/', (string)$s['sp_cost']) ? (string)$s['sp_cost'] : null) : null,
            ':acquired' => $s['acquired'] ?? 'no',
            ':tag' => null,
            ':notes' => $s['notes'] ?? null,
        ];
        if ($dryRun) {
            echo "[DRY-RUN] Would insert skill: " . json_encode(['name' => $name] + $params, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            continue;
        }
        $stmt->execute($params);
    }
}

function insertPredictions(PDO $pdo, int $planId, array $preds, bool $dryRun = false): void
{
    if (empty($preds)) {
        return;
    }
    $sql = "INSERT INTO race_predictions
            (plan_id, race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment)
            VALUES
            (:pid, :race_name, :venue, :ground, :distance, :track_condition, :direction, :speed, :stamina, :power, :guts, :wit, :comment)";
    $stmt = $pdo->prepare($sql);
    foreach ($preds as $p) {
        $params = [
            ':pid' => $planId,
            ':race_name' => $p['race_name'] ?? null,
            ':venue' => $p['venue'] ?? null,
            ':ground' => $p['ground'] ?? null,
            ':distance' => $p['distance'] ?? null,
            ':track_condition' => $p['track_condition'] ?? null,
            ':direction' => $p['direction'] ?? null,
            ':speed' => $p['speed'] ?? '○',
            ':stamina' => $p['stamina'] ?? '○',
            ':power' => $p['power'] ?? '○',
            ':guts' => $p['guts'] ?? '○',
            ':wit' => $p['wit'] ?? '○',
            ':comment' => $p['comment'] ?? null,
        ];
        if ($dryRun) {
            echo "[DRY-RUN] Would insert race_prediction: " . json_encode($params, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            continue;
        }
        $stmt->execute($params);
    }
}

function insertGoals(PDO $pdo, int $planId, array $goals, bool $dryRun = false): void
{
    if (empty($goals)) {
        return;
    }
    $sql = "INSERT INTO goals (plan_id, goal, result) VALUES (:pid, :goal, :result)";
    $stmt = $pdo->prepare($sql);
    foreach ($goals as $g) {
        $params = [
            ':pid' => $planId,
            ':goal' => $g['goal'],
            ':result' => $g['result'] ?? 'Pending',
        ];
        if ($dryRun) {
            echo "[DRY-RUN] Would insert goal: " . json_encode($params, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            continue;
        }
        $stmt->execute($params);
    }
}

// ---------- Main ----------
$blocks = splitPlans($input);
if (empty($blocks)) {
    fwrite(STDERR, "No PLAN blocks were detected in input.\n");
    exit(3);
}

$imported = 0;
$failed = 0;

foreach ($blocks as $idx => $block) {
    $summaryName = 'Unknown';
    try {
        $plan = parsePlanBlock($block);
        $summaryName = $plan['name'] ?? ($plan['race_name'] ?? "Plan#" . ($idx + 1));

        if ($dryRun) {
            echo "----- DRY-RUN: Parsed Plan: {$summaryName} -----\n";
            echo json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            $imported++;
            continue;
        }

        $pdo->beginTransaction();

        $planId = insertPlan($pdo, $userId, $plan, $overrideStatus, false);
        insertAttributes($pdo, $planId, $plan['attributes'], false);
        insertGrades($pdo, $planId, $plan['terrain_grades'], 'terrain_grades', 'terrain', false);
        insertGrades($pdo, $planId, $plan['distance_grades'], 'distance_grades', 'distance', false);
        insertGrades($pdo, $planId, $plan['style_grades'], 'style_grades', 'style', false);
        insertSkills($pdo, $planId, $plan['skills'], false);
        insertPredictions($pdo, $planId, $plan['predictions'], false);
        insertGoals($pdo, $planId, $plan['goals'], false);

        $pdo->commit();

        echo "Imported plan #{$planId}: {$summaryName}\n";
        $imported++;
    } catch (Throwable $e) {
        $failed++;
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, "Failed to import plan '{$summaryName}': {$e->getMessage()}\n");
        // Optionally print a shortened excerpt of the block for debugging
        $excerpt = substr($block, 0, 400);
        fwrite(STDERR, "--- Block excerpt ---\n{$excerpt}\n---------------------\n");
    }
}

echo "Done. Imported: {$imported}, Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
