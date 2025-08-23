<?php

declare(strict_types=1);

/**
 * Uma Musume Race Planner API — Plan Skills
 * GET action: returns skills with reference metadata for a given plan id.
 * Aligns with root get_plan_skills.php fields.
 * Response: { success: true, skills: array, request_id: string }
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
            $sql = 'SELECT r.skill_name, r.description, r.stat_type, r.tag, s.sp_cost, s.acquired, s.tag AS skill_tag, s.notes
                    FROM skills s
                    LEFT JOIN skill_reference r ON s.skill_reference_id = r.id
                    WHERE s.plan_id = ?
                    ORDER BY s.id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$plan_id]);
            $skills = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            send_json(200, ['success' => true, 'skills' => $skills]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to fetch plan skills (api)', [
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
