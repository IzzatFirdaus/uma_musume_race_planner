<?php

// export_plan_data.php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($planId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Plan ID provided.']);
    exit();
}

try {
    // --- 1. Fetch main plan details ---
    $sql = '
        SELECT
            p.id, p.plan_title, p.turn_before, p.race_name, p.name, p.career_stage,
            p.class, p.time_of_day, p.month, p.total_available_skill_points, p.acquire_skill,
            m.label AS mood_label, cond.label AS condition_label, p.energy, p.race_day,
            p.goal AS plan_goal, strat.label AS strategy_label, p.growth_rate_speed,
            p.growth_rate_stamina, p.growth_rate_power, p.growth_rate_guts,
            p.growth_rate_wit, p.status, p.source
        FROM plans p
        LEFT JOIN moods m ON p.mood_id = m.id
        LEFT JOIN conditions cond ON p.condition_id = cond.id
        LEFT JOIN strategies strat ON p.strategy_id = strat.id
        WHERE p.id = ? AND p.deleted_at IS NULL
    ';
    $stmt = $pdo->prepare($sql);
    // It's good practice to check if prepare was successful, though PDO::ERRMODE_EXCEPTION helps
    // by throwing an exception if prepare fails due to bad SQL.
    $stmt->execute([$planId]);
    $planData = $stmt->fetch(PDO::FETCH_ASSOC); // Ensure associative array

    if (!$planData) {
        http_response_code(404);
        echo json_encode(['error' => 'Plan not found or has been deleted.']);
        exit();
    }

    // --- Fetch all related data using prepared statements ---
    // Corrected pattern for fetching data
    $stmtAttributes = $pdo->prepare('SELECT attribute_name, value, grade FROM attributes WHERE plan_id = ?');
    $stmtAttributes->execute([$planId]);
    $planData['attributes'] = $stmtAttributes->fetchAll(PDO::FETCH_ASSOC);

    $stmtSkills = $pdo->prepare('SELECT s.skill_name, s.sp_cost, s.acquired, s.notes, sr.tag FROM skills s LEFT JOIN skill_reference sr ON s.skill_name = sr.skill_name WHERE s.plan_id = ?');
    $stmtSkills->execute([$planId]);
    $planData['skills'] = $stmtSkills->fetchAll(PDO::FETCH_ASSOC);

    $stmtTerrainGrades = $pdo->prepare('SELECT terrain, grade FROM terrain_grades WHERE plan_id = ?');
    $stmtTerrainGrades->execute([$planId]);
    $planData['terrain_grades'] = $stmtTerrainGrades->fetchAll(PDO::FETCH_ASSOC);

    $stmtDistanceGrades = $pdo->prepare('SELECT distance, grade FROM distance_grades WHERE plan_id = ?');
    $stmtDistanceGrades->execute([$planId]);
    $planData['distance_grades'] = $stmtDistanceGrades->fetchAll(PDO::FETCH_ASSOC);

    $stmtStyleGrades = $pdo->prepare('SELECT style, grade FROM style_grades WHERE plan_id = ?');
    $stmtStyleGrades->execute([$planId]);
    $planData['style_grades'] = $stmtStyleGrades->fetchAll(PDO::FETCH_ASSOC);

    $stmtRacePredictions = $pdo->prepare('SELECT race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment FROM race_predictions WHERE plan_id = ?');
    $stmtRacePredictions->execute([$planId]);
    $planData['race_predictions'] = $stmtRacePredictions->fetchAll(PDO::FETCH_ASSOC);

    $stmtGoals = $pdo->prepare('SELECT goal, result FROM goals WHERE plan_id = ?');
    $stmtGoals->execute([$planId]);
    $planData['goals'] = $stmtGoals->fetchAll(PDO::FETCH_ASSOC);

    // Return the aggregated data as JSON
    echo json_encode($planData);
} catch (PDOException $e) {
    // Handle any exceptions during database operations
    $log->error('Failed to export plan data', [
        'plan_id' => $planId,
        'message' => $e->getMessage(),
        'file' => $e->getFile(), // Added for better logging
        'line' => $e->getLine(),  // Added for better logging
    ]);
    http_response_code(500);
    echo json_encode(['error' => 'An internal server error occurred.']);
}