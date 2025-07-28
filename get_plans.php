<?php

// get_plans.php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

try {
    // MODIFIED: The ORDER BY clause now sorts by a custom status priority
    // before sorting by the update timestamp.
    $query = "
        SELECT p.*, m.label AS mood, s.label AS strategy
        FROM plans p
        LEFT JOIN moods m ON p.mood_id = m.id
        LEFT JOIN strategies s ON p.strategy_id = s.id
        WHERE p.deleted_at IS NULL
        ORDER BY
            CASE p.status
                WHEN 'Active' THEN 1
                WHEN 'Planning' THEN 2
                WHEN 'Draft' THEN 3
                WHEN 'Finished' THEN 4
                WHEN 'Abandoned' THEN 5
                ELSE 6
            END,
            p.updated_at DESC
    ";
    $plans = $pdo->query($query)->fetchAll();

    echo json_encode(['success' => true, 'plans' => $plans]);
} catch (PDOException $e) {
    $log->error('Failed to fetch plan list', ['message' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A database error occurred.']);
}