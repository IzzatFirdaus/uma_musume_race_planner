<?php

declare(strict_types=1);

/**
 * Uma Musume Race Planner API — Progress Chart
 * Returns stat progression per turn for a given plan.
 * Example: /api/progress.php?action=chart&plan_id=1
 * Response: { success: true, data: array, request_id: string }
 */

// Security and caching headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

ob_start();

$REQUEST_ID = bin2hex(random_bytes(8));
header('X-Request-Id: ' . $REQUEST_ID);

const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;

function send_json(int $status, array $payload): void
{
    http_response_code($status);
    if (!isset($payload['request_id'])) {
        global $REQUEST_ID;
        $payload['request_id'] = $REQUEST_ID;
    }
    ob_clean();
    echo json_encode($payload, JSON_FLAGS);
    exit;
}

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        send_json(405, ['success' => false, 'error' => 'Method Not Allowed.']);
    }
}

require_method('GET');

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    $log = require __DIR__ . '/../includes/logger.php';
} catch (Throwable $e) {
    send_json(500, ['success' => false, 'error' => 'Failed to initialize dependencies.']);
}

$action = $_GET['action'] ?? 'chart';

switch ($action) {
    case 'chart':
        $plan_id_raw = $_GET['plan_id'] ?? null;
        $plan_id = filter_var($plan_id_raw, FILTER_VALIDATE_INT);
        if (!$plan_id || $plan_id <= 0) {
            send_json(400, ['success' => false, 'error' => 'Invalid or missing plan_id.']);
        }
        try {
            $stmt = $pdo->prepare('SELECT turn_number AS turn, speed, stamina, power, guts, wit FROM turns WHERE plan_id = ? ORDER BY turn_number ASC');
            $stmt->execute([$plan_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            send_json(200, ['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to fetch progress chart data (api)', [
                    'plan_id' => $plan_id,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while fetching chart data.']);
        }
        break;

    default:
        send_json(400, ['success' => false, 'error' => 'Unknown action.']);
}
