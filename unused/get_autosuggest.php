<?php

require_once 'config.php';
$pdo = require_once 'db.php';

header('Content-Type: application/json');

$field = $_GET['field'] ?? '';
$validFields = ['name', 'race_name', 'skill_name', 'goal'];

if (!in_array($field, $validFields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid field']);
    exit;
}

try {
    if ($field === 'skill_name') {
        $stmt = $pdo->query('SELECT DISTINCT skill_name AS value FROM skill_reference ORDER BY skill_name ASC');
    } else {
        $stmt = $pdo->prepare("SELECT DISTINCT `$field` AS value FROM plans WHERE `$field` IS NOT NULL AND `$field` != '' ORDER BY `$field` ASC");
        $stmt->execute();
    }

    $values = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value');

    echo json_encode(['success' => true, 'suggestions' => $values]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
