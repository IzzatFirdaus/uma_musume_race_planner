<?php

/**
 * Uma Musume Race Planner API — Activity Log
 * Provides recent activity entries for dashboard and history.
 * Standard JSON: { success: bool, activities?: array, error?: string }
 */

header('Content-Type: application/json');

ob_start(); // Guard against stray output

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    $log = require __DIR__ . '/../includes/logger.php';
} catch (Throwable $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to initialize dependencies.', 'activities' => []]);
    exit;
}

$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        try {
            $stmt = $pdo->query('SELECT description, icon_class, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 5');
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_clean();
            echo json_encode(['success' => true, 'activities' => $activities]);
        } catch (Throwable $e) {
            $log->error('Failed to fetch activities (api)', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            http_response_code(500);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'A database error occurred while fetching activities.', 'activities' => []]);
        }
        break;

    default:
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Unknown action.', 'activities' => []]);
        break;
}
