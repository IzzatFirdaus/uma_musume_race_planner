<?php

/**
 * Uma Musume Race Planner API — Stats
 * Provides summary statistics for plans and trainees.
 */

header('Content-Type: application/json');

ob_start();

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    $log = require __DIR__ . '/../includes/logger.php';
} catch (Throwable $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to initialize dependencies.']);
    exit;
}

$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
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
            $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $safeStats = [
                'total_plans'     => (int)($stats['total_plans'] ?? 0),
                'active_plans'    => (int)($stats['active_plans'] ?? 0),
                'finished_plans'  => (int)($stats['finished_plans'] ?? 0),
                'planning_plans'  => (int)($stats['planning_plans'] ?? 0),
                'unique_trainees' => (int)($stats['unique_trainees'] ?? 0),
            ];

            ob_clean();
            echo json_encode(['success' => true, 'stats' => $safeStats]);
        } catch (Throwable $e) {
            $log->error('Failed to fetch stats (api)', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            http_response_code(500);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'A database error occurred while fetching stats.']);
        }
        break;

    default:
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        break;
}
