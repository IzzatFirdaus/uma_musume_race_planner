<?php

// get_plan_terrain_grades.php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

try {
    $plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($plan_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid Plan ID.']); // Consistent error response
        exit;
    }

    $stmt = $pdo->prepare('SELECT terrain, grade FROM terrain_grades WHERE plan_id = ? ORDER BY id');
    $stmt->execute([$plan_id]);
    $terrain_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'terrain_grades' => $terrain_grades]); // Consistent success response
} catch (PDOException $e) {
    $log->error('Failed to fetch plan terrain grades', [
        'plan_id' => $plan_id ?? 0,
        'message' => $e->getMessage(),
        'file' => $e->getFile(), // Added for debugging
        'line' => $e->getLine(),  // Added for debugging
    ]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A database error occurred.']); // Consistent error response
}