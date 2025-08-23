<?php

declare(strict_types=1);

/**
 * export_plan_data.php
 *
 * API endpoint to export full plan details as JSON (default) or Plain Text (txt) format.
 * - GET parameters:
 *   - id (int, required): Plan ID
 *   - format (string, optional): 'txt' to download a .txt summary; default JSON
 *
 * Best practices implemented:
 * - Strict types, input validation, prepared statements, safe logging, and no leakage of internals.
 * - Uses mb_* string functions where available to handle multibyte names safely.
 * - Sanitized filename for Content-Disposition.
 */

header('X-Content-Type-Options: nosniff');

// Utilities
$send_json = static function (array $payload, int $code = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

// Enforce GET
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET', true, 405);
    $send_json(['success' => false, 'error' => 'Method not allowed. Use GET.'], 405);
}

try {
    /** @var PDO $pdo */
    $pdo = require __DIR__ . '/includes/db.php';
    $log = require __DIR__ . '/includes/logger.php';
} catch (Throwable $e) {
    $send_json(['success' => false, 'error' => 'Service unavailable.'], 503);
}

// Inputs
$planId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$format = strtolower((string) ($_GET['format'] ?? 'json'));
$isTxt = $format === 'txt';

if ($planId <= 0) {
    $send_json(['success' => false, 'error' => 'Invalid Plan ID provided.'], 400);
}

try {
    // Main plan
    $stmt = $pdo->prepare('
        SELECT
            p.id, p.plan_title, p.turn_before, p.race_name, p.name, p.career_stage,
            p.class, p.time_of_day, p.month, p.total_available_skill_points, p.acquire_skill,
            m.label AS mood_label, cond.label AS condition_label, p.energy, p.race_day,
            p.goal AS plan_goal, strat.label AS strategy_label, p.growth_rate_speed,
            p.growth_rate_stamina, p.growth_rate_power, p.growth_rate_guts,
            p.growth_rate_wit, p.status, p.source, p.trainee_image_path
        FROM plans p
        LEFT JOIN moods m ON p.mood_id = m.id
        LEFT JOIN conditions cond ON p.condition_id = cond.id
        LEFT JOIN strategies strat ON p.strategy_id = strat.id
        WHERE p.id = ? AND p.deleted_at IS NULL
        LIMIT 1
    ');
    $stmt->execute([$planId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plan) {
        $send_json(['success' => false, 'error' => 'Plan not found or has been deleted.'], 404);
    }

    // Helper to fetch list data
    $fetchList = static function (PDO $pdo, string $sql, int $planId): array {
        $st = $pdo->prepare($sql);
        $st->execute([$planId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    };

    // Child data
    $plan['attributes'] = $fetchList($pdo, 'SELECT attribute_name, value, grade FROM attributes WHERE plan_id = ? ORDER BY id', $planId);

    // Skills via reference join to resolve skill_name
    $plan['skills'] = $fetchList(
        $pdo,
        'SELECT COALESCE(sr.skill_name, "") AS skill_name, s.sp_cost, s.acquired, s.notes, s.tag
         FROM skills s
         LEFT JOIN skill_reference sr ON s.skill_reference_id = sr.id
         WHERE s.plan_id = ?
         ORDER BY s.id',
        $planId
    );

    $plan['terrain_grades'] = $fetchList($pdo, 'SELECT terrain, grade FROM terrain_grades WHERE plan_id = ? ORDER BY id', $planId);
    $plan['distance_grades'] = $fetchList($pdo, 'SELECT distance, grade FROM distance_grades WHERE plan_id = ? ORDER BY id', $planId);
    $plan['style_grades'] = $fetchList($pdo, 'SELECT style, grade FROM style_grades WHERE plan_id = ? ORDER BY id', $planId);
    $plan['race_predictions'] = $fetchList(
        $pdo,
        'SELECT race_name, venue, ground, distance, track_condition, direction,
                speed, stamina, power, guts, wit, comment
         FROM race_predictions
         WHERE plan_id = ?
         ORDER BY id',
        $planId
    );
    $plan['goals'] = $fetchList($pdo, 'SELECT goal, result FROM goals WHERE plan_id = ? ORDER BY id', $planId);

    if (!$isTxt) {
        $send_json(['success' => true, 'plan' => $plan]);
    }

    // TXT response
    // Switch headers for plain text download
    $filenameBase = (string) ($plan['plan_title'] ?? 'uma_plan');
    // Multibyte-safe sanitize for filename (keep alnum, dash, underscore)
    $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $filenameBase) ?: 'uma_plan';
    $downloadName = $safeBase . '.txt';
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    }

    // mb_* helpers
    $mb_len = static function (string $s): int {
        return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    };
    $mb_sub = static function (string $s, int $start, int $len): string {
        if (function_exists('mb_substr')) {
            return mb_substr($s, $start, $len, 'UTF-8');
        }
        return substr($s, $start, $len);
    };

    // Pad and table builders (UTF-8 aware)
    $pad = static function (string $str, int $len, string $align = 'left') use ($mb_len): string {
        $slen = $mb_len($str);
        $diff = $len - $slen;
        if ($diff <= 0) {
            return $str;
        }
        if ($align === 'right') {
            return str_repeat(' ', $diff) . $str;
        }
        if ($align === 'center') {
            $left = (int) floor($diff / 2);
            $right = $diff - $left;
            return str_repeat(' ', $left) . $str . str_repeat(' ', $right);
        }
        return $str . str_repeat(' ', $diff);
    };

    $buildTable = static function (array $headers, array $rows, array $aligns) use ($pad, $mb_len): string {
        $widths = [];
        foreach ($headers as $i => $h) {
            $colMax = $mb_len((string) $h);
            foreach ($rows as $row) {
                $cell = isset($row[$i]) ? (string) $row[$i] : '';
                $colMax = max($colMax, $mb_len($cell));
            }
            $widths[$i] = $colMax;
        }
        $line = static function (array $row) use ($widths, $aligns, $pad): string {
            $cells = [];
            foreach (array_values($row) as $i => $c) {
                $cells[] = $pad((string) $c, $widths[$i], $aligns[$i] ?? 'left');
            }
            return '| ' . implode(' | ', $cells) . ' |';
        };
        $divider = '|' . implode('|', array_map(static fn ($w) => str_repeat('-', (int) $w + 2), $widths)) . '|';
        $out = [];
        $out[] = $line($headers);
        $out[] = $divider;
        foreach ($rows as $r) {
            $out[] = $line($r);
        }
        return implode("\n", $out);
    };

    $out = '';
    $bigDiv = "\n" . str_repeat('=', 80) . "\n\n";

    // General Info
    $out .= '## PLAN: ' . (($plan['plan_title'] ?? '') !== '' ? $plan['plan_title'] : 'Untitled Plan') . " ##\n\n";
    $info = [
        ['Trainee Name:', (string) ($plan['name'] ?? '')],
        ['Career Stage:', strtoupper((string) ($plan['career_stage'] ?? '')) . ' (' . strtoupper((string) ($plan['month'] ?? '')) . ' ' . strtoupper((string) ($plan['time_of_day'] ?? '')) . ')'],
        ['Class:', strtoupper((string) ($plan['class'] ?? ''))],
        ['Status:', (string) ($plan['status'] ?? '')],
        ['Next Race:', (string) ($plan['race_name'] ?? 'N/A')],
        ['Turn Before:', (string) ($plan['turn_before'] ?? 0)],
    ];
    $maxKeyLen = 0;
    foreach ($info as $r) {
        $maxKeyLen = max($maxKeyLen, $mb_len((string) $r[0]));
    }
    foreach ($info as $r) {
        $out .= $pad((string) $r[0], $maxKeyLen) . ' ' . (string) $r[1] . "\n";
    }

    // Attributes
    $out .= $bigDiv . "ATTRIBUTES\n";
    $attrs = array_map(static fn ($a) => [
        (string) ($a['attribute_name'] ?? ''),
        (string) ($a['value'] ?? ''),
        (string) ($a['grade'] ?? ''),
    ], $plan['attributes'] ?? []);
    $out .= $buildTable(['Attribute', 'Value', 'Grade'], $attrs, ['left', 'right', 'center']) . "\n";

    // Grades
    $out .= "\nAPTITUDE GRADES\n";
    $terr = array_column($plan['terrain_grades'] ?? [], 'grade', 'terrain');
    $dist = array_column($plan['distance_grades'] ?? [], 'grade', 'distance');
    $styl = array_column($plan['style_grades'] ?? [], 'grade', 'style');
    $rows = [];
    $keys = [
        ['Sprint', 'Front', 'Turf'],
        ['Mile', 'Pace', 'Dirt'],
        ['Medium', 'Late', ''],
        ['Long', 'End', ''],
    ];
    foreach ($keys as [$d, $s, $t]) {
        $rows[] = [$d, $dist[$d] ?? 'G', $s, $styl[$s] ?? 'G', $t, $t !== '' ? ($terr[$t] ?? 'G') : ''];
    }
    $out .= $buildTable(['Distance', 'G', 'Style', 'G', 'Terrain', 'G'], $rows, ['left', 'center', 'left', 'center', 'left', 'center']) . "\n";

    // Summary & Growth
    $out .= $bigDiv . "SUMMARY & GROWTH\n";
    $summary = [
        ['Total SP', (string) ($plan['total_available_skill_points'] ?? 0)],
        ['Acquire Skill?', strtoupper((string) ($plan['acquire_skill'] ?? 'NO'))],
        ['Conditions', (string) ($plan['condition_label'] ?? 'N/A')],
        ['Mood', (string) ($plan['mood_label'] ?? 'N/A')],
        ['Energy', (string) (($plan['energy'] ?? 0) . ' / 100')],
        ['Race Day?', strtoupper((string) ($plan['race_day'] ?? 'no'))],
        ['Goal', (string) ($plan['plan_goal'] ?? '')],
        ['Strategy', (string) ($plan['strategy_label'] ?? 'N/A')],
        ['---', '---'],
        ['Growth: Speed', '+' . (string) ($plan['growth_rate_speed'] ?? 0) . '%'],
        ['Growth: Stamina', '+' . (string) ($plan['growth_rate_stamina'] ?? 0) . '%'],
        ['Growth: Power', '+' . (string) ($plan['growth_rate_power'] ?? 0) . '%'],
        ['Growth: Guts', '+' . (string) ($plan['growth_rate_guts'] ?? 0) . '%'],
        ['Growth: Wit', '+' . (string) ($plan['growth_rate_wit'] ?? 0) . '%'],
    ];
    $out .= $buildTable(['Item', 'Value'], $summary, ['left', 'right']) . "\n";

    // Skills
    $out .= $bigDiv . "ACQUIRED SKILLS\n";
    $skills = array_map(static fn ($s) => [
        (string) ($s['skill_name'] ?? ''),
        (string) ($s['sp_cost'] ?? 'N/A'),
        strtolower((string) ($s['acquired'] ?? 'no')) === 'yes' ? '✅' : '❌',
        (string) ($s['notes'] ?? ''),
    ], $plan['skills'] ?? []);
    $out .= $buildTable(['Skill Name', 'SP Cost', 'Acquired', 'Notes'], $skills, ['left', 'right', 'center', 'left']) . "\n";

    // Goals
    $out .= $bigDiv . "CAREER GOALS\n" . str_repeat('-', 80) . "\n";
    if (!empty($plan['goals'])) {
        foreach ($plan['goals'] as $g) {
            $goalTxt = (string) ($g['goal'] ?? '');
            $resTxt = (string) ($g['result'] ?? '');
            $out .= "• " . $goalTxt . (($resTxt !== '' && strtolower($resTxt) !== 'pending') ? " (Result: {$resTxt})" : '') . "\n";
        }
    } else {
        $out .= "No goals specified.\n";
    }

    // Predictions
    $out .= $bigDiv . "RACE DAY PREDICTIONS\n" . str_repeat('-', 80) . "\n";
    if (!empty($plan['race_predictions'])) {
        foreach ($plan['race_predictions'] as $i => $p) {
            $out .= 'Prediction #' . ($i + 1) . ': ' . (string) ($p['race_name'] ?? '') . "\n";
            $out .= '  Venue: ' . (string) ($p['venue'] ?? '') . ', ' . (string) ($p['ground'] ?? '') . ', ' . (string) ($p['distance'] ?? '') . ', ' . (string) ($p['direction'] ?? '') . ', Track: ' . (string) ($p['track_condition'] ?? '') . "\n";
            $out .= '  SPEED[' . (string) ($p['speed'] ?? '') . '] STAMINA[' . (string) ($p['stamina'] ?? '') . '] POWER[' . (string) ($p['power'] ?? '') . '] GUTS[' . (string) ($p['guts'] ?? '') . '] WIT[' . (string) ($p['wit'] ?? '') . "]\n";
            $out .= '  Comment: ' . ((string) ($p['comment'] ?? 'N/A') ?: 'N/A') . "\n\n";
        }
    } else {
        $out .= "No race predictions available.\n";
    }

    echo $out;
    exit;
} catch (Throwable $e) {
    // Log minimal info
    try {
        if (isset($log)) {
            $log->error('Failed to export plan data', [
                'plan_id' => $planId,
                'message' => $e->getMessage(),
            ]);
        }
    } catch (Throwable $ignore) {
    }
    $send_json(['success' => false, 'error' => 'An internal server error occurred.'], 500);
}
