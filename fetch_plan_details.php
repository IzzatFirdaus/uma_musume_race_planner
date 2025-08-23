<?php

declare(strict_types=1);

/**
 * fetch_plan_details.php
 *
 * API endpoint to fetch a single plan's primary metadata.
 * Includes mood/strategy labels; excludes related sub-table data.
 *
 * Method: GET
 * Params:
 * - id (int, required)
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

$planId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($planId <= 0) {
    $send_json(['success' => false, 'error' => 'Invalid plan ID.'], 400);
}

try {
    $sql = '
        SELECT 
            p.*, 
            m.label AS mood_label, 
            s.label AS strategy_label
        FROM plans p
        LEFT JOIN moods m ON p.mood_id = m.id
        LEFT JOIN strategies s ON p.strategy_id = s.id
        WHERE p.id = ? AND p.deleted_at IS NULL
        LIMIT 1
    ';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$planId]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($plan) {
        $send_json(['success' => true, 'plan' => $plan]);
    } else {
        $send_json(['success' => false, 'error' => 'Plan not found.'], 404);
    }
} catch (Throwable $e) {
    $log->error('Failed to fetch main plan details', [
        'plan_id' => $planId,
        'message' => $e->getMessage(),
    ]);
    $send_json(['success' => false, 'error' => 'Database error while fetching main plan details.'], 500);
}
