<?php

/**
 * Uma Musume Race Planner API — Plan Section (Consolidated)
 * Mirrors root get_plan_section.php to fetch various section data by type.
 * Example:
 *   /api/plan_section.php?type=attributes&id=1
 *   /api/plan_section.php?type=skills&id=1
 *   /api/plan_section.php?type=turns&id=1
 */

header('Content-Type: application/json');

ob_start();

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    $log = require __DIR__ . '/../includes/logger.php';
} catch (Throwable $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to initialize dependencies.']);
    exit;
}

$plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = $_GET['type'] ?? '';

if ($plan_id <= 0 || $type === '') {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Missing or invalid parameters.']);
    exit;
}

// Map section types to queries
$sectionMap = [
    'attributes' => [
        'sql' => 'SELECT attribute_name, value, grade FROM attributes WHERE plan_id = ? ORDER BY id',
        'key' => 'attributes'
    ],
    'skills' => [
        'sql' => 'SELECT r.skill_name, r.description, r.stat_type, r.tag, s.sp_cost, s.acquired, s.tag AS skill_tag, s.notes
                  FROM skills s
                  LEFT JOIN skill_reference r ON s.skill_reference_id = r.id
                  WHERE s.plan_id = ?
                  ORDER BY s.id',
        'key' => 'skills'
    ],
    'distance_grades' => [
        'sql' => 'SELECT distance, grade FROM distance_grades WHERE plan_id = ? ORDER BY id',
        'key' => 'distance_grades'
    ],
    'style_grades' => [
        'sql' => 'SELECT style, grade FROM style_grades WHERE plan_id = ? ORDER BY id',
        'key' => 'style_grades'
    ],
    'terrain_grades' => [
        'sql' => 'SELECT terrain, grade FROM terrain_grades WHERE plan_id = ? ORDER BY id',
        'key' => 'terrain_grades'
    ],
    'goals' => [
        'sql' => 'SELECT goal, result FROM goals WHERE plan_id = ? ORDER BY id',
        'key' => 'goals'
    ],
    'predictions' => [
        'sql' => 'SELECT * FROM race_predictions WHERE plan_id = ? ORDER BY id',
        'key' => 'predictions'
    ],
    'turns' => [
        'sql' => 'SELECT turn_number, speed, stamina, power, guts, wit FROM turns WHERE plan_id = ? ORDER BY turn_number',
        'key' => 'turns'
    ],
];

if (!isset($sectionMap[$type])) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid type requested.']);
    exit;
}

try {
    $sql = $sectionMap[$type]['sql'];
    $key = $sectionMap[$type]['key'];
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$plan_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ob_clean();
    echo json_encode(['success' => true, $key => $data]);
} catch (Throwable $e) {
    $log->error("Failed to fetch plan section: {$type} (api)", [
        'plan_id' => $plan_id,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => "A database error occurred fetching {$type}."]);
}
