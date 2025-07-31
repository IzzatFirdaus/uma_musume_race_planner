<?php

header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';
$plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = $_GET['type'] ?? '';
if ($plan_id <= 0 || empty($type)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid parameters.']);
    exit;
}

// Map section types to safe queries and fields
$sectionMap = [
    'attributes' => [
        'table' => 'attributes',
        'columns' => 'attribute_name, value, grade'
    ],
    'skills' => [
        'table' => 'skills',
        'columns' => 'skill_name, sp_cost, acquired, tag, notes'
    ],
    'distance_grades' => [
        'table' => 'distance_grades',
        'columns' => 'distance, grade'
    ],
    'style_grades' => [
        'table' => 'style_grades',
        'columns' => 'style, grade'
    ],
    'terrain_grades' => [
        'table' => 'terrain_grades',
        'columns' => 'terrain, grade'
    ],
    'goals' => [
        'table' => 'goals',
        'columns' => 'goal, result'
    ],
    'predictions' => [
        'table' => 'race_predictions',
        'columns' => '*'
    ],
    'turns' => [
        'table' => 'turns',
        'columns' => '*'
    ]
];
if (!array_key_exists($type, $sectionMap)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid type requested.']);
    exit;
}

$table = $sectionMap[$type]['table'];
$columns = $sectionMap[$type]['columns'];
try {
    $stmt = $pdo->prepare("SELECT $columns FROM `$table` WHERE plan_id = ? ORDER BY id");
    $stmt->execute([$plan_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, $type => $data]);
} catch (PDOException $e) {
    $log->error("Failed to fetch plan section: $type", [
        'plan_id' => $plan_id,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => "A database error occurred fetching $type."]);
}