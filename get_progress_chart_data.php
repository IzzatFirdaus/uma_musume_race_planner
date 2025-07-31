<?php

/**
 * get_progress_chart_data.php
 *
 * Returns JSON data representing stat progression per turn for a given plan.
 * * Input:
 * - plan_id (required): The ID of the training plan
 * * Output:
 * [
 * { "turn": 1, "speed": 100, "stamina": 90, "power": 80, "guts": 70, "wit": 60 },
 * ...
 * ]
 */

header('Content-Type: application/json');

$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

// Validate and sanitize input
$plan_id = isset($_GET['plan_id']) ? (int) $_GET['plan_id'] : 0;

if ($plan_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid or missing plan_id.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT turn_number AS turn, speed, stamina, power, guts, wit
        FROM turns
        WHERE plan_id = ?
        ORDER BY turn_number ASC
    ");
    $stmt->execute([$plan_id]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} catch (PDOException $e) {
    $log->error('Failed to fetch progress chart data', [
        'plan_id' => $plan_id,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'A database error occurred while fetching chart data.'
    ]);
}