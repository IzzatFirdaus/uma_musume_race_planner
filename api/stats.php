<?php

declare(strict_types=1);

/**
 * Uma Musume Race Planner API — Stats
 * Provides summary statistics for plans and trainees.
 * Response: { success: true, stats: object, request_id: string }
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
    /** @var PDO|null $pdo */
    $log = require __DIR__ . '/../includes/logger.php';
    /** @var \Psr\Log\LoggerInterface|null $log */
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

            send_json(200, ['success' => true, 'stats' => $safeStats]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to fetch stats (api)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while fetching stats.']);
        }
        break;

    default:
        send_json(400, ['success' => false, 'error' => 'Unknown action.']);
}
