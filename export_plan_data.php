<?php

/**
 * export_plan_data.php
 *
 * API endpoint to export full plan details as JSON or plain-text (.txt) format.
 */

header('Content-Type: application/json');

$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isTxt = isset($_GET['format']) && $_GET['format'] === 'txt';

if ($planId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Plan ID provided.']);
    exit();
}

try {
    // 1. Main Plan
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
    ');
    $stmt->execute([$planId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plan) {
        http_response_code(404);
        echo json_encode(['error' => 'Plan not found or has been deleted.']);
        exit();
    }

    // 2. Sub-data
    $fetch = function ($sql) use ($pdo, $planId) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$planId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    };

    $plan['attributes'] = $fetch('SELECT attribute_name, value, grade FROM attributes WHERE plan_id = ?');
    $plan['skills'] = $fetch('
        SELECT s.skill_name, s.sp_cost, s.acquired, s.notes, sr.tag
        FROM skills s LEFT JOIN skill_reference sr ON s.skill_name = sr.skill_name
        WHERE s.plan_id = ?');
    $plan['terrain_grades'] = $fetch('SELECT terrain, grade FROM terrain_grades WHERE plan_id = ?');
    $plan['distance_grades'] = $fetch('SELECT distance, grade FROM distance_grades WHERE plan_id = ?');
    $plan['style_grades'] = $fetch('SELECT style, grade FROM style_grades WHERE plan_id = ?');
    $plan['race_predictions'] = $fetch('
        SELECT race_name, venue, ground, distance, track_condition, direction,
               speed, stamina, power, guts, wit, comment
        FROM race_predictions WHERE plan_id = ?');
    $plan['goals'] = $fetch('SELECT goal, result FROM goals WHERE plan_id = ?');

    if ($isTxt) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $plan['plan_title'] ?? 'uma_plan') . '.txt"');

        function pad($str, $len, $align = 'left')
        {
            $str = strval($str);
            $diff = $len - strlen($str);
            if ($diff <= 0) {
                return $str;
            }
            if ($align === 'right') {
                return str_repeat(' ', $diff) . $str;
            }
            if ($align === 'center') {
                $left = floor($diff / 2);
                $right = $diff - $left;
                return str_repeat(' ', $left) . $str . str_repeat(' ', $right);
            }
            return $str . str_repeat(' ', $diff);
        }

        function buildTable($headers, $rows, $aligns)
        {
            $widths = [];
            foreach ($headers as $i => $h) {
                $widths[$i] = max(strlen($h), ...array_map(fn($r) => strlen(strval($r[$i] ?? '')), $rows));
            }
            $line = function ($row) use ($widths, $aligns) {
                $cells = array_map(fn($c, $i) => pad($c, $widths[$i], $aligns[$i] ?? 'left'), $row, array_keys($row));
                return '| ' . implode(' | ', $cells) . ' |';
            };
            $divider = '|' . implode('|', array_map(fn($w) => str_repeat('-', $w + 2), $widths)) . '|';
            return implode("\n", [
                $line($headers),
                $divider,
                ...array_map($line, $rows)
            ]);
        }

        $out = '';
        $divider = "\n" . str_repeat('=', 80) . "\n\n";

        // General Info
        $out .= "## PLAN: " . ($plan['plan_title'] ?: 'Untitled Plan') . " ##\n\n";
        $info = [
            ['Trainee Name:', $plan['name']],
            ['Career Stage:', strtoupper($plan['career_stage']) . " (" . strtoupper($plan['month']) . " " . strtoupper($plan['time_of_day']) . ")"],
            ['Class:', strtoupper($plan['class'])],
            ['Status:', $plan['status']],
            ['Next Race:', $plan['race_name'] ?: 'N/A'],
            ['Turn Before:', $plan['turn_before'] ?? 0],
        ];
        $maxLen = max(array_map(fn($r) => strlen($r[0]), $info));
        foreach ($info as $r) {
            $out .= pad($r[0], $maxLen) . ' ' . $r[1] . "\n";
        }

        // Attributes
        $out .= $divider . "ATTRIBUTES\n";
        $attrs = array_map(fn($a) => [$a['attribute_name'], $a['value'], $a['grade']], $plan['attributes']);
        $out .= buildTable(['Attribute', 'Value', 'Grade'], $attrs, ['left', 'right', 'center']) . "\n";

        // Grades
        $out .= "\nAPTITUDE GRADES\n";
        $terr = array_column($plan['terrain_grades'], 'grade', 'terrain');
        $dist = array_column($plan['distance_grades'], 'grade', 'distance');
        $styl = array_column($plan['style_grades'], 'grade', 'style');
        $rows = [];
        $keys = [
            ['Sprint', 'Front', 'Turf'],
            ['Mile', 'Pace', 'Dirt'],
            ['Medium', 'Late', ''],
            ['Long', 'End', '']
        ];
        foreach ($keys as [$d, $s, $t]) {
            $rows[] = [$d, $dist[$d] ?? 'G', $s, $styl[$s] ?? 'G', $t, $terr[$t] ?? ''];
        }
        $out .= buildTable(['Distance', 'G', 'Style', 'G', 'Terrain', 'G'], $rows, ['left', 'center', 'left', 'center', 'left', 'center']) . "\n";

        // Summary & Growth
        $out .= $divider . "SUMMARY & GROWTH\n";
        $summary = [
            ['Total SP', $plan['total_available_skill_points']],
            ['Acquire Skill?', strtoupper($plan['acquire_skill'] ?? 'NO')],
            ['Conditions', $plan['condition_label'] ?? 'N/A'],
            ['Mood', $plan['mood_label'] ?? 'N/A'],
            ['Energy', ($plan['energy'] ?? 0) . ' / 100'],
            ['Race Day?', strtoupper($plan['race_day'] ?? 'no')],
            ['Goal', $plan['plan_goal'] ?: ''],
            ['Strategy', $plan['strategy_label'] ?? 'N/A'],
            ['---', '---'],
            ['Growth: Speed', '+' . ($plan['growth_rate_speed'] ?? 0) . '%'],
            ['Growth: Stamina', '+' . ($plan['growth_rate_stamina'] ?? 0) . '%'],
            ['Growth: Power', '+' . ($plan['growth_rate_power'] ?? 0) . '%'],
            ['Growth: Guts', '+' . ($plan['growth_rate_guts'] ?? 0) . '%'],
            ['Growth: Wit', '+' . ($plan['growth_rate_wit'] ?? 0) . '%'],
        ];
        $out .= buildTable(['Item', 'Value'], $summary, ['left', 'right']) . "\n";

        // Skills
        $out .= $divider . "ACQUIRED SKILLS\n";
        $skills = array_map(fn($s) => [
            $s['skill_name'], $s['sp_cost'] ?? 'N/A',
            strtolower($s['acquired']) === 'yes' ? '✅' : '❌',
            $s['notes'] ?? ''
        ], $plan['skills']);
        $out .= buildTable(['Skill Name', 'SP Cost', 'Acquired', 'Notes'], $skills, ['left', 'right', 'center', 'left']) . "\n";

        // Goals
        $out .= $divider . "CAREER GOALS\n" . str_repeat('-', 80) . "\n";
        if ($plan['goals']) {
            foreach ($plan['goals'] as $g) {
                $out .= "• {$g['goal']}" . ($g['result'] && $g['result'] !== 'Pending' ? " (Result: {$g['result']})" : '') . "\n";
            }
        } else {
            $out .= "No goals specified.\n";
        }

        // Predictions
        $out .= $divider . "RACE DAY PREDICTIONS\n" . str_repeat('-', 80) . "\n";
        if ($plan['race_predictions']) {
            foreach ($plan['race_predictions'] as $i => $p) {
                $out .= "Prediction #" . ($i + 1) . ": {$p['race_name']}\n";
                $out .= "  Venue: {$p['venue']}, {$p['ground']}, {$p['distance']}, {$p['direction']}, Track: {$p['track_condition']}\n";
                $out .= "  SPEED[{$p['speed']}] STAMINA[{$p['stamina']}] POWER[{$p['power']}] GUTS[{$p['guts']}] WIT[{$p['wit']}]\n";
                $out .= "  Comment: " . ($p['comment'] ?: 'N/A') . "\n\n";
            }
        } else {
            $out .= "No race predictions available.\n";
        }

        echo $out;
    } else {
        echo json_encode($plan);
    }
} catch (PDOException $e) {
    $log->error('Failed to export plan data', [
        'plan_id' => $planId,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    http_response_code(500);
    echo json_encode(['error' => 'An internal server error occurred.']);
}
