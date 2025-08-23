<?php

declare(strict_types=1);

/**
 * get_activities.php
 *
 * Returns the latest activity log entries.
 * Method: GET
 * Params:
 * - limit (int, optional) default 5, max 20
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

$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 5;
$limit = max(1, min($limit, 20)); // clamp

try {
    $stmt = $pdo->prepare('SELECT description, icon_class, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $send_json(['success' => true, 'activities' => $activities]);
} catch (Throwable $e) {
    $log->error('Failed to fetch activities', ['message' => $e->getMessage()]);
    $send_json(['success' => false, 'error' => 'A database error occurred while fetching activities.'], 500);
}
