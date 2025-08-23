<?php

/**
 * Uma Musume Race Planner API — Progress Chart
 * Returns stat progression per turn for a given plan.
 * Example: /api/progress.php?action=chart&plan_id=1
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

$action = $_GET['action'] ?? 'chart';

switch ($action) {
    case 'chart':
        $plan_id = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;
        if ($plan_id <= 0) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid or missing plan_id.']);
            break;
        }
        try {
            $stmt = $pdo->prepare('SELECT turn_number AS turn, speed, stamina, power, guts, wit FROM turns WHERE plan_id = ? ORDER BY turn_number ASC');
            $stmt->execute([$plan_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_clean();
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            $log->error('Failed to fetch progress chart data (api)', [
                'plan_id' => $plan_id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            http_response_code(500);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'A database error occurred while fetching chart data.']);
        }
        break;

    default:
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        break;
}
