<?php

// get_plan_skills.php
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

    // The skills table stores a reference id to the skill_reference table.
    // Join with skill_reference to obtain the human-readable skill_name and metadata.
    $sql = 'SELECT r.skill_name, s.sp_cost, s.acquired, s.tag, s.notes '
        . 'FROM skills s '
        . 'LEFT JOIN skill_reference r ON s.skill_reference_id = r.id '
        . 'WHERE s.plan_id = ? ORDER BY s.id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$plan_id]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'skills' => $skills]); // Consistent success response
} catch (Exception $e) {
    $log->error('Failed to fetch plan skills', [
        'plan_id' => $plan_id ?? 0,
        'message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e,
        'file' => method_exists($e, 'getFile') ? $e->getFile() : '',
        'line' => method_exists($e, 'getLine') ? $e->getLine() : '',
    ]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A database error occurred.']); // Consistent error response
}
