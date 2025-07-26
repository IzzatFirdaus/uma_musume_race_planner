<?php

// get_stats.php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

try {
    $stats_query = "
        SELECT
            COUNT(*) AS total_plans,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_plans,
            SUM(CASE WHEN status = 'Finished' THEN 1 ELSE 0 END) AS finished_plans,
            SUM(CASE WHEN status = 'Planning' THEN 1 ELSE 0 END) AS planning_plans,
            COUNT(DISTINCT name) AS unique_trainees
        FROM plans
        WHERE deleted_at IS NULL
    ";
    $stmt = $pdo->query($stats_query);
    $stats = $stmt->fetch();

    // Cast values to integers
    foreach ($stats as $key => $value) {
        $stats[$key] = (int)$value;
    }

    echo json_encode(['success' => true, 'stats' => $stats]);
} catch (PDOException $e) {
    // Log the detailed error to your file
    $log->error('Failed to fetch stats', ['message' => $e->getMessage()]);

    // Send a generic error to the client
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A database error occurred while fetching stats.']);
}
