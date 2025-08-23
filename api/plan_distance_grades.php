<?php

declare(strict_types=1);

/**
 * Uma Musume Race Planner API — Plan Distance Grades
 * GET action: returns distance grades for a given plan id.
 * Response: { success: true, distance_grades: array, request_id: string }
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

function get_plan_id(): int
{
    $val = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    return ($val !== false && $val !== null) ? max(1, $val) : 0;
}

require_method('GET');

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    /** @var PDO $pdo */
    $log = require __DIR__ . '/../includes/logger.php';
    /** @var \Psr\Log\LoggerInterface $log */
} catch (Throwable $e) {
    send_json(500, ['success' => false, 'error' => 'Failed to initialize dependencies.']);
}

if (!($pdo instanceof PDO)) {
    throw new RuntimeException('Database not initialized.');
}
/** @var PDO $pdo */
/** @var \Psr\Log\LoggerInterface|null $log */

$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        $plan_id = get_plan_id();
        if ($plan_id <= 0) {
            send_json(400, ['success' => false, 'error' => 'Invalid Plan ID.']);
        }
        try {
            $stmt = $pdo->prepare('SELECT distance, grade FROM distance_grades WHERE plan_id = ? ORDER BY id');
            $stmt->execute([$plan_id]);
            $distance_grades = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            send_json(200, ['success' => true, 'distance_grades' => $distance_grades]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to fetch plan distance grades (api)', [
                    'plan_id' => $plan_id,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred.']);
        }
        break;

    default:
        send_json(400, ['success' => false, 'error' => 'Unknown action.']);
}
