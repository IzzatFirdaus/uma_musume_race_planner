<?php

/**
 * get_stats.php
 *
 * Endpoint to fetch summary statistics for plans and trainees.
 *
 * Returns:
 * - total_plans: All undeleted plans
 * - active_plans: Count with status = 'Active'
 * - finished_plans: Count with status = 'Finished'
 * - planning_plans: Count with status = 'Planning'
 * - unique_trainees: Unique trainee names
 */

header('Content-Type: application/json');

$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

try {
    $sql = "
        SELECT
            COUNT(*) AS total_plans,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_plans,
            SUM(CASE WHEN status = 'Finished' THEN 1 ELSE 0 END) AS finished_plans,
            SUM(CASE WHEN status = 'Planning' THEN 1 ELSE 0 END) AS planning_plans,
            COUNT(DISTINCT name) AS unique_trainees
        FROM plans
        WHERE deleted_at IS NULL
    ";

    $stmt = $pdo->query($sql);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Force values to be integers (null fallback = 0)
    $safeStats = [
        'total_plans'     => (int)($stats['total_plans'] ?? 0),
        'active_plans'    => (int)($stats['active_plans'] ?? 0),
        'finished_plans'  => (int)($stats['finished_plans'] ?? 0),
        'planning_plans'  => (int)($stats['planning_plans'] ?? 0),
        'unique_trainees' => (int)($stats['unique_trainees'] ?? 0),
    ];

    echo json_encode([
        'success' => true,
        'stats' => $safeStats
    ]);
} catch (Exception $e) {
    $log->error('Failed to fetch stats', [
        'message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e,
        'file' => method_exists($e, 'getFile') ? $e->getFile() : '',
        'line' => method_exists($e, 'getLine') ? $e->getLine() : ''
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'A database error occurred while fetching stats.'
    ]);
}
