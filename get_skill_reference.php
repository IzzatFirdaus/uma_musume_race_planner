<?php

// get_skill_reference.php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

try {
    $search = $_GET['search'] ?? '';
    $stat_type = $_GET['stat_type'] ?? '';
    $tag = $_GET['tag'] ?? '';

    $whereConditions = [];
    $params = [];

    if (!empty($search)) {
        $whereConditions[] = 'skill_name LIKE ?';
        $params[] = "%$search%";
    }
    if (!empty($stat_type)) {
        $whereConditions[] = 'stat_type = ?';
        $params[] = $stat_type;
    }
    if (!empty($tag)) {
        $whereConditions[] = 'tag = ?';
        $params[] = $tag;
    }

    $sql = 'SELECT skill_name, tag, stat_type FROM skill_reference';
    if ($whereConditions !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $whereConditions);
    }
    $sql .= ' ORDER BY skill_name';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); // Pass the parameters array directly
    $skills = $stmt->fetchAll();

    echo json_encode($skills);
} catch (PDOException $e) {
    $log->error('Failed to fetch skill reference data', [
        'params' => $_GET,
        'message' => $e->getMessage(),
    ]);
    http_response_code(500);
    echo json_encode(['error' => 'A database error occurred.']);
}
