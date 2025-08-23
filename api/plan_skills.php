<?php

/**
 * Uma Musume Race Planner API — Plan Skills
 * GET action: returns skills with reference metadata for a given plan id.
 * Aligns with root get_plan_skills.php fields.
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
            $sql = 'SELECT r.skill_name, r.description, r.stat_type, r.tag, s.sp_cost, s.acquired, s.tag AS skill_tag, s.notes
                    FROM skills s
                    LEFT JOIN skill_reference r ON s.skill_reference_id = r.id
                    WHERE s.plan_id = ?
                    ORDER BY s.id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$plan_id]);
            $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_clean();
            echo json_encode(['success' => true, 'skills' => $skills ?: []]);
        } catch (Throwable $e) {
            $log->error('Failed to fetch plan skills (api)', [
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
