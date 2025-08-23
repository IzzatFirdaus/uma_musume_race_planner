<?php

declare(strict_types=1);

/**
 * get_stats.php
 *
 * Returns summary statistics for plans and trainees.
 * Method: GET
 */

header('X-Content-Type-Options: nosniff');

$send_json = static function (array $payload, int $code = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET', true, 405);
    $send_json(['success' => false, 'error' => 'Method not allowed.'], 405);
}

try {
    /** @var PDO $pdo */
    $pdo = require __DIR__ . '/includes/db.php';
    $log = require __DIR__ . '/includes/logger.php';
} catch (Throwable $e) {
    $send_json(['success' => false, 'error' => 'Service unavailable.'], 503);
}

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

    $send_json(['success' => true, 'stats' => $safeStats]);
} catch (Throwable $e) {
    $log->error('Failed to fetch stats', ['message' => $e->getMessage()]);
    $send_json(['success' => false, 'error' => 'A database error occurred while fetching stats.'], 500);
}
