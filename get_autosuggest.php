<?php

// get_autosuggest.php

require_once __DIR__ . '/config.php';
$pdo = require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$field = $_GET['field'] ?? '';
$search_query = $_GET['query'] ?? '';

$validFields = ['name', 'race_name', 'skill_name', 'goal'];

if (!in_array($field, $validFields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid field']);
    exit;
}

try {
    if ($field === 'skill_name') {
        $sql = 'SELECT skill_name, description, stat_type, tag FROM skill_reference';
        $params = [];

        if (!empty($search_query)) {
            $sql .= ' WHERE skill_name LIKE ?';
            $params[] = '%' . $search_query . '%';
        }
        $sql .= ' ORDER BY skill_name ASC LIMIT 10';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql = "SELECT DISTINCT `$field` AS value FROM plans WHERE `$field` IS NOT NULL AND `$field` != ''";
        $params = [];

        if (!empty($search_query)) {
            $sql .= ' AND `' . $field . '` LIKE ?';
            $params[] = '%' . $search_query . '%';
        }
        $sql .= ' ORDER BY `' . $field . '` ASC LIMIT 10';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $suggestions = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value');
    }

    echo json_encode(['success' => true, 'suggestions' => $suggestions]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
