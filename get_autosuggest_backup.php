<?php

// get_autosuggest.php
// Used to return dynamic suggestions for input fields (name, race_name, goal, skill_name)

require_once __DIR__ . '/config.php';
$pdo = require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$field = $_GET['field'] ?? '';
$search_query = trim($_GET['query'] ?? '');

// Only allow predefined fields to prevent SQL injection
$validFields = ['name', 'race_name', 'skill_name', 'goal'];

if (!in_array($field, $validFields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid field']);
    exit; // Ensures no further output after an invalid field error
}

try {
    if ($field === 'skill_name') {
        // Lookup skill_name from reference table (includes metadata)
        $sql = 'SELECT skill_name, description, stat_type, tag FROM skill_reference';
        $params = [];

        if ($search_query !== '') {
            $sql .= ' WHERE skill_name LIKE ?';
            $params[] = '%' . $search_query . '%';
        }

        $sql .= ' ORDER BY skill_name ASC LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Lookup distinct string values from plans
        $sql = "SELECT DISTINCT `$field` AS value
                FROM plans
                WHERE `$field` IS NOT NULL AND `$field` != ''";

        $params = [];
        if ($search_query !== '') {
            $sql .= " AND `$field` LIKE ?";
            $params[] = '%' . $search_query . '%';
        }

        $sql .= " ORDER BY `$field` ASC LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Return array of plain strings
        $suggestions = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value');
    }

    echo json_encode([
        'success' => true,
        'suggestions' => $suggestions
    ]);
    exit; // Ensures no further output after a successful response
} catch (Throwable $e) {
    // It's highly recommended to use a proper logger here instead of error_log directly,
    // especially for production environments. Ensure display_errors is OFF in php.ini
    // for production to prevent sensitive information or malformed JSON responses.
    error_log("Autosuggest error for field '{$field}': " . $e->getMessage()); // Log the error on the server side
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error processing autosuggest request: ' . $e->getMessage() // Include a safe error message for debugging
    ]);
    exit; // Ensures no further output after an error response
}