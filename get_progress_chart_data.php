<?php

// get_progress_chart_data.php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

try {
    // Get the plan ID from the URL query parameter
    $plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Validate the plan ID
    if ($plan_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid Plan ID.']);
        exit;
    }

    // Prepare and execute the query to fetch all turns for the given plan
    $stmt = $pdo->prepare('SELECT turn_number, speed, stamina, power, guts, wit FROM turns WHERE plan_id = ? ORDER BY turn_number');
    $stmt->execute([$plan_id]);
    $turns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data in a consistent JSON format
    echo json_encode(['success' => true, 'turns' => $turns]);
} catch (PDOException $e) {
    // Log any database errors
    $log->error('Failed to fetch plan turns', [
        'plan_id' => $plan_id ?? 0,
        'message' => $e->getMessage()
    ]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A database error occurred.']);
}
