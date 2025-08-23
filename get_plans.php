<?php

declare(strict_types=1);

/**
 * get_plan_section.php
 *
 * Returns a specific section of plan data.
 * Method: GET
 * Params:
 * - id (int, required): plan_id
 * - type (string, required): one of attributes|skills|distance_grades|style_grades|terrain_grades|goals|predictions|turns
 */

header('X-Content-Type-Options: nosniff');

$send_json = static function (array $payload, int $code = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET', true, 405);
    $send_json(['success' => false, 'error' => 'Method not allowed.'], 405);
}

try {
    /** @var PDO $pdo */
    $pdo = require __DIR__ . '/includes/db.php';
    $log = require __DIR__ . '/includes/logger.php';
} catch (Throwable $e) {
    $send_json(['success' => false, 'error' => 'Service unavailable.'], 503);
}

$plan_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$type = (string) ($_GET['type'] ?? '');

if ($plan_id <= 0 || $type === '') {
    $send_json(['success' => false, 'error' => 'Missing or invalid parameters.'], 400);
}

$sectionMap = [
    'attributes' => [
        'sql' => 'SELECT attribute_name, value, grade FROM attributes WHERE plan_id = ? ORDER BY id',
        'key' => 'attributes',
    ],
    'skills' => [
        // Join with reference for name (the skills table stores skill_reference_id)
        'sql' => 'SELECT COALESCE(sr.skill_name,"") AS skill_name, s.sp_cost, s.acquired, s.tag, s.notes
                  FROM skills s
                  LEFT JOIN skill_reference sr ON s.skill_reference_id = sr.id
                  WHERE s.plan_id = ?
                  ORDER BY s.id',
        'key' => 'skills',
    ],
    'distance_grades' => [
        'sql' => 'SELECT distance, grade FROM distance_grades WHERE plan_id = ? ORDER BY id',
        'key'  => 'distance_grades',
    ],
    'style_grades' => [
        'sql' => 'SELECT style, grade FROM style_grades WHERE plan_id = ? ORDER BY id',
        'key' => 'style_grades',
    ],
    'terrain_grades' => [
        'sql' => 'SELECT terrain, grade FROM terrain_grades WHERE plan_id = ? ORDER BY id',
        'key' => 'terrain_grades',
    ],
    'goals' => [
        'sql' => 'SELECT goal, result FROM goals WHERE plan_id = ? ORDER BY id',
        'key' => 'goals',
    ],
    'predictions' => [
        'sql' => 'SELECT * FROM race_predictions WHERE plan_id = ? ORDER BY id',
        'key' => 'predictions',
    ],
    'turns' => [
        'sql' => 'SELECT * FROM turns WHERE plan_id = ? ORDER BY id',
        'key' => 'turns',
    ],
];

if (!array_key_exists($type, $sectionMap)) {
    $send_json(['success' => false, 'error' => 'Invalid type requested.'], 400);
}

try {
    $stmt = $pdo->prepare($sectionMap[$type]['sql']);
    $stmt->execute([$plan_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $send_json(['success' => true, $sectionMap[$type]['key'] => $data]);
} catch (Throwable $e) {
    $log->error("Failed to fetch plan section: {$type}", [
        'plan_id' => $plan_id,
        'message' => $e->getMessage(),
    ]);
    $send_json(['success' => false, 'error' => "A database error occurred fetching {$type}."], 500);
}
