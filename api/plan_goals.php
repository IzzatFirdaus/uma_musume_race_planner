<?php

/**
 * Uma Musume Race Planner API — Plan Goals
 * GET action: returns goals for a given plan id.
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
        $plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($plan_id <= 0) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid Plan ID.']);
            break;
        }
        try {
            $stmt = $pdo->prepare('SELECT goal, result FROM goals WHERE plan_id = ? ORDER BY id');
            $stmt->execute([$plan_id]);
            $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_clean();
            echo json_encode(['success' => true, 'goals' => $goals]);
        } catch (Throwable $e) {
            $log->error('Failed to fetch plan goals (api)', [
                'plan_id' => $plan_id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            http_response_code(500);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'A database error occurred.']);
        }
        break;

    default:
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        break;
}
