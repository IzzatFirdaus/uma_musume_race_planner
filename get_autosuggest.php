<?php

require_once __DIR__ . '/config.php';
$pdo = require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

// The field to get suggestions for (e.g., 'skill_name', 'race_name')
$field = $_GET['field'] ?? '';

// Whitelist of valid fields for security
$validFields = ['name', 'race_name', 'skill_name', 'goal']; //

if (!in_array($field, $validFields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid field']);
    exit;
}

try {
    // --- V2.0 UPDATE: Handle skill_name separately to return richer data ---
    if ($field === 'skill_name') {
        // For skills, we need more than just the name to populate the contextual info box.
        // We select all relevant fields from the skill_reference table.
        $stmt = $pdo->query('SELECT skill_name, description, stat_type, tag FROM skill_reference ORDER BY skill_name ASC');
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // For other fields, we just need a simple list of distinct values.
        // This logic remains the same as the original file.
        $stmt = $pdo->prepare("SELECT DISTINCT `$field` AS value FROM plans WHERE `$field` IS NOT NULL AND `$field` != '' ORDER BY `$field` ASC");
        $stmt->execute();
        // Flatten the result into a simple array of strings.
        $suggestions = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value');
    }

    echo json_encode(['success' => true, 'suggestions' => $suggestions]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}