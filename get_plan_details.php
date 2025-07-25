<?php
// get_plan_details.php
require_once 'config.php'; // Ensure your database connection is included

header('Content-Type: application/json'); // Set header for JSON response

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($planId > 0) {
    // Fetch plan details
    $stmt = $conn->prepare("
        SELECT p.*, m.label AS mood, s.label AS strategy
        FROM plans p
        LEFT JOIN moods m ON p.mood_id = m.id
        LEFT JOIN strategies s ON p.strategy_id = s.id
        WHERE p.id = ? AND p.deleted_at IS NULL
    ");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $plan = $result->fetch_assoc();

    if ($plan) {
        // Return plan details as JSON
        echo json_encode($plan);
    } else {
        // Return an error message if plan not found
        echo json_encode(['error' => 'Plan not found']);
    }

    $stmt->close();
} else {
    // Return an error message for invalid ID
    echo json_encode(['error' => 'Invalid Plan ID']);
}

$conn->close(); // Close DB connection
?>