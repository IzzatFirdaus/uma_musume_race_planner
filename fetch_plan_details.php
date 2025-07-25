<?php
// fetch_plan_details.php
require_once 'config.php'; // Include your database configuration

header('Content-Type: application/json');

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($planId > 0) {
    // Fetch plan details
    $plan_query = $conn->prepare("
        SELECT p.*, m.label AS mood_label, s.label AS strategy_label
        FROM plans p
        LEFT JOIN moods m ON p.mood_id = m.id
        LEFT JOIN strategies s ON p.strategy_id = s.id
        WHERE p.id = ? AND p.deleted_at IS NULL
    ");
    $plan_query->bind_param("i", $planId);
    $plan_query->execute();
    $plan_result = $plan_query->get_result();
    $plan = $plan_result->fetch_assoc();

    if ($plan) {
        // Fetch attributes
        $attributes_query = $conn->prepare("SELECT attribute_name, value, grade FROM attributes WHERE plan_id = ?");
        $attributes_query->bind_param("i", $planId);
        $attributes_query->execute();
        $attributes_result = $attributes_query->get_result();
        $plan['attributes'] = [];
        while ($row = $attributes_result->fetch_assoc()) {
            $plan['attributes'][] = $row;
        }

        // Fetch skills
        $skills_query = $conn->prepare("SELECT skill_name, sp_cost, acquired, tag, notes FROM skills WHERE plan_id = ?");
        $skills_query->bind_param("i", $planId);
        $skills_query->execute();
        $skills_result = $skills_query->get_result();
        $plan['skills'] = [];
        while ($row = $skills_result->fetch_assoc()) {
            $plan['skills'][] = $row;
        }

        // Fetch race predictions
        $predictions_query = $conn->prepare("SELECT race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment FROM race_predictions WHERE plan_id = ?");
        $predictions_query->bind_param("i", $planId);
        $predictions_query->execute();
        $predictions_result = $predictions_query->get_result();
        $plan['predictions'] = [];
        while ($row = $predictions_result->fetch_assoc()) {
            $plan['predictions'][] = $row;
        }

        // Fetch terrain grades
        $terrain_grades_query = $conn->prepare("SELECT terrain, grade FROM terrain_grades WHERE plan_id = ?");
        $terrain_grades_query->bind_param("i", $planId);
        $terrain_grades_query->execute();
        $terrain_grades_result = $terrain_grades_query->get_result();
        $plan['terrain_grades'] = [];
        while ($row = $terrain_grades_result->fetch_assoc()) {
            $plan['terrain_grades'][] = $row;
        }

        // Fetch distance grades
        $distance_grades_query = $conn->prepare("SELECT distance, grade FROM distance_grades WHERE plan_id = ?");
        $distance_grades_query->bind_param("i", $planId);
        $distance_grades_query->execute();
        $distance_grades_result = $distance_grades_query->get_result();
        $plan['distance_grades'] = [];
        while ($row = $distance_grades_result->fetch_assoc()) {
            $plan['distance_grades'][] = $row;
        }

        // Fetch style grades
        $style_grades_query = $conn->prepare("SELECT style, grade FROM style_grades WHERE plan_id = ?");
        $style_grades_query->bind_param("i", $planId);
        $style_grades_query->execute();
        $style_grades_result = $style_grades_query->get_result();
        $plan['style_grades'] = [];
        while ($row = $style_grades_result->fetch_assoc()) {
            $plan['style_grades'][] = $row;
        }

        // Fetch goals
        $goals_query = $conn->prepare("SELECT goal, result FROM goals WHERE plan_id = ?");
        $goals_query->bind_param("i", $planId);
        $goals_query->execute();
        $goals_result = $goals_query->get_result();
        $plan['goals'] = [];
        while ($row = $goals_result->fetch_assoc()) {
            $plan['goals'][] = $row;
        }

        // Fetch turns
        $turns_query = $conn->prepare("SELECT turn_number, speed, stamina, power, guts, wit FROM turns WHERE plan_id = ? ORDER BY turn_number ASC");
        $turns_query->bind_param("i", $planId);
        $turns_query->execute();
        $turns_result = $turns_query->get_result();
        $plan['turns'] = [];
        while ($row = $turns_result->fetch_assoc()) {
            $plan['turns'][] = $row;
        }


        echo json_encode(['success' => true, 'plan' => $plan]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Plan not found.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid plan ID.']);
}

$conn->close();
?>