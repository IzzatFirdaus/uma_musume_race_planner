<?php

// fetch_plan_details.php - Now only fetches main plan details
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($planId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid plan ID.']);
    exit;
}

try {
    // Fetch main plan details only
    $sql = '
        SELECT p.*, m.label AS mood_label, s.label AS strategy_label
        FROM plans p
        LEFT JOIN moods m ON p.mood_id = m.id
        LEFT JOIN strategies s ON p.strategy_id = s.id
        WHERE p.id = ? AND p.deleted_at IS NULL
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$planId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC); // Ensure associative array

    if ($plan) {
        echo json_encode(['success' => true, 'plan' => $plan]); // Wrap in 'plan' key for consistency
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Plan not found.']);
    }
} catch (PDOException $e) {
    http_response_code(500);

    $log->error('Failed to fetch main plan details', [
        'plan_id' => $planId,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    echo json_encode(['success' => false, 'error' => 'Database error while fetching main plan details.']);
}
