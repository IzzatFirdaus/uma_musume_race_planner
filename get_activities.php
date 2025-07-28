<?php

// get_activities.php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';
try {
    $stmt = $pdo->query('SELECT description, icon_class, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 5');
    // Adjust limit as needed
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'activities' => $activities]);
} catch (PDOException $e) {
    $log->error('Failed to fetch activities', ['message' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'A database error occurred while fetching activities.']);
}
