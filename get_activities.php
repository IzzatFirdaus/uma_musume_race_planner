<?php

// get_activities.php

ob_start(); // Start clean buffer
header('Content-Type: application/json');

$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

try {
    $stmt = $pdo->query('SELECT description, icon_class, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 5');
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Clear any accidental whitespace or output
    ob_clean();
    echo json_encode(['success' => true, 'activities' => $activities]);
} catch (PDOException $e) {
    $log->error('Failed to fetch activities', ['message' => $e->getMessage()]);
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'A database error occurred while fetching activities.']);
}