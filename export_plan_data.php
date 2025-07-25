<?php
// export_plan_data.php
// This file fetches all data related to a specific plan ID
// and returns it as a JSON object for client-side processing.

require_once 'config.php'; // Include your database configuration

header('Content-Type: application/json'); // Set header for JSON response

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($planId <= 0) {
    echo json_encode(['error' => 'Invalid Plan ID provided.']);
    exit();
}

try {
    // Initialize an empty array to hold all plan data
    $planData = [];

    // --- 1. Fetch main plan details ---
    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.plan_title,
            p.turn_before,
            p.race_name,
            p.name,
            p.career_stage,
            p.class,
            p.time_of_day,
            p.month,
            p.total_available_skill_points,
            p.acquire_skill,
            m.label AS mood_label,
            cond.label AS condition_label,
            p.energy,
            p.race_day,
            p.goal AS plan_goal, -- Alias to avoid conflict with goals.goal
            strat.label AS strategy_label,
            p.growth_rate_speed,
            p.growth_rate_stamina,
            p.growth_rate_power,
            p.growth_rate_guts,
            p.growth_rate_wit,
            p.status,
            p.source
        FROM plans p
        LEFT JOIN moods m ON p.mood_id = m.id
        LEFT JOIN conditions cond ON p.condition_id = cond.id
        LEFT JOIN strategies strat ON p.strategy_id = strat.id
        WHERE p.id = ? AND p.deleted_at IS NULL
    ");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $plan = $result->fetch_assoc();
    $stmt->close();

    if (!$plan) {
        echo json_encode(['error' => 'Plan not found or has been deleted.']);
        exit();
    }
    $planData = $plan; // Add main plan details to planData

    // --- 2. Fetch Attributes ---
    $stmt = $conn->prepare("SELECT attribute_name, value, grade FROM attributes WHERE plan_id = ?");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $planData['attributes'] = [];
    while ($row = $result->fetch_assoc()) {
        $planData['attributes'][] = $row;
    }
    $stmt->close();

    // --- 3. Fetch Skills ---
    // Join with skill_reference to get the tag for formatting (e.g., 🔺, 🔋)
    $stmt = $conn->prepare("
        SELECT s.skill_name, s.sp_cost, s.acquired, s.notes, sr.tag
        FROM skills s
        LEFT JOIN skill_reference sr ON s.skill_name = sr.skill_name
        WHERE s.plan_id = ?
    ");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $planData['skills'] = [];
    while ($row = $result->fetch_assoc()) {
        $planData['skills'][] = $row;
    }
    $stmt->close();

    // --- 4. Fetch Terrain Grades ---
    $stmt = $conn->prepare("SELECT terrain, grade FROM terrain_grades WHERE plan_id = ?");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $planData['terrain_grades'] = [];
    while ($row = $result->fetch_assoc()) {
        $planData['terrain_grades'][] = $row;
    }
    $stmt->close();

    // --- 5. Fetch Distance Grades ---
    $stmt = $conn->prepare("SELECT distance, grade FROM distance_grades WHERE plan_id = ?");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $planData['distance_grades'] = [];
    while ($row = $result->fetch_assoc()) {
        $planData['distance_grades'][] = $row;
    }
    $stmt->close();

    // --- 6. Fetch Style Grades ---
    $stmt = $conn->prepare("SELECT style, grade FROM style_grades WHERE plan_id = ?");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $planData['style_grades'] = [];
    while ($row = $result->fetch_assoc()) {
        $planData['style_grades'][] = $row;
    }
    $stmt->close();

    // --- 7. Fetch Race Predictions ---
    $stmt = $conn->prepare("
        SELECT
            race_name,
            venue,
            ground,
            distance,
            track_condition,
            direction,
            speed,
            stamina,
            power,
            guts,
            wit,
            comment
        FROM race_predictions
        WHERE plan_id = ?
    ");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $planData['race_predictions'] = [];
    while ($row = $result->fetch_assoc()) {
        $planData['race_predictions'][] = $row;
    }
    $stmt->close();

    // --- 8. Fetch Goals ---
    $stmt = $conn->prepare("SELECT goal, result FROM goals WHERE plan_id = ?");
    $stmt->bind_param("i", $planId);
    $stmt->execute();
    $result = $stmt->get_result();
    $planData['goals'] = [];
    while ($row = $result->fetch_assoc()) {
        $planData['goals'][] = $row;
    }
    $stmt->close();

    // Return the aggregated data as JSON
    echo json_encode($planData);

} catch (Exception $e) {
    // Handle any exceptions during database operations
    error_log("Error fetching plan data: " . $e->getMessage());
    echo json_encode(['error' => 'An internal server error occurred.']);
} finally {
    // Ensure the database connection is closed
    $conn->close();
}
?>
