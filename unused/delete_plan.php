<?php

require_once 'config.php';
$pdo = require_once 'db.php';

header('Content-Type: application/json');

$planId = $_POST['id'] ?? null;
if (!$planId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing plan ID']);
    exit;
}

try {
    $pdo->prepare('UPDATE plans SET deleted_at = NOW() WHERE id = ?')->execute([$planId]);

    $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')
        ->execute(["Deleted plan ID: $planId", 'bi-trash text-danger']);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
