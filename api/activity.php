<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

/**
 * Uma Musume Race Planner API — Activity Log
 * Provides recent activity entries for dashboard and history.
 * Standard JSON: { success: bool, activities?: array, error?: string, request_id: string, meta?: object }
 */

// Security and caching headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');


/** @var PDO|null $pdo */
$pdo = null;
ob_start(); // Guard against stray output

// Request correlation
$REQUEST_ID = bin2hex(random_bytes(8));
header('X-Request-Id: ' . $REQUEST_ID);

// JSON helpers
const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;

/**
 * Send a JSON response and terminate.
 */
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

/**
 * Require a specific HTTP method or return 405.
 */
function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        send_json(405, ['success' => false, 'error' => 'Method Not Allowed.']);
    }
}

/**
 * Get an integer from query with optional clamping and default.
 */
function get_int(string $key, int $default, int $min, int $max): int
{
    $val = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    if ($val === false || $val === null) {
        return $default;
    }
    return max($min, min($max, $val));
}

require_method('GET');

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    /** @var PDO|null $pdo */
    $log = require __DIR__ . '/../includes/logger.php';
    /** @var \Psr\Log\LoggerInterface|null $log */
} catch (Throwable $e) {
    send_json(500, ['success' => false, 'error' => 'Failed to initialize dependencies.', 'activities' => []]);
}

if (!($pdo instanceof PDO)) {
    throw new RuntimeException('Database not initialized.');
}
/** @var PDO $pdo */
/** @var \Psr\Log\LoggerInterface|null $log */

$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        try {
            // Pagination parameters with safe bounds
            $limit = get_int('limit', 5, 1, 50);
            $offset = get_int('offset', 0, 0, 1000);

            // Note: We safely inject validated integers for LIMIT/OFFSET
            $sql = sprintf(
                'SELECT description, icon_class, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT %d OFFSET %d',
                $limit,
                $offset
            );
            $stmt = $pdo->query($sql);
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            send_json(200, [
                'success' => true,
                'activities' => $activities ?: [],
                'meta' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => count($activities ?: []),
                ],
            ]);
        } catch (Throwable $e) {
            if (is_object($log) && method_exists($log, 'error')) {
                $log->error('Failed to fetch activities (api)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while fetching activities.', 'activities' => []]);
        }
        break;

    default:
        send_json(400, ['success' => false, 'error' => 'Unknown action.', 'activities' => []]);
}
