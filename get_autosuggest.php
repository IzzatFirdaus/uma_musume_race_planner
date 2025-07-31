<?php

// get_autosuggest.php
// Used to return dynamic suggestions for input fields (name, race_name, goal, skill_name)

// Removed session_start() and authentication check.
// For local-only application, hardcode the user ID.
$currentUserId = 1;

header('Content-Type: application/json');

// Include database and logger. These files are expected to return their respective objects.
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';


$field = $_GET['field'] ?? '';
$search_query = trim($_GET['query'] ?? '');

// CRITICAL IMPROVEMENT 1: Fix SQL Injection - Use a whitelist with column mapping
$fieldMap = [
    'name'       => 'name',
    'race_name'  => 'race_name',
    'skill_name' => 'skill_name',
    'goal'       => 'goal'
];

// Validate the requested field against the map
if (!isset($fieldMap[$field])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid field for autosuggestion.']);
    exit;
}

// Use the mapped, safe field name
$safeField = $fieldMap[$field];

try {
    if ($safeField === 'skill_name') {
        // Lookup skill_name from reference table (includes metadata)
        // skill_reference is global and not user-specific, so no user_id filter needed here.
        $sql = 'SELECT skill_name, description, stat_type, tag FROM skill_reference';
        $params = [];

        if ($search_query !== '') {
            $sql .= ' WHERE skill_name LIKE :search_query';
            $params[':search_query'] = '%' . $search_query . '%';
        }

        $sql .= ' ORDER BY skill_name ASC LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Lookup distinct string values from plans, ensuring ownership
        // Note: For fields like 'name', 'race_name', 'goal' that come from the 'plans' table,
        // we should also restrict by user_id to prevent leaking other users' data.
        $sql = "SELECT DISTINCT `{$safeField}` AS value
                FROM plans
                WHERE `{$safeField}` IS NOT NULL AND `{$safeField}` != ''
                AND user_id = :user_id"; // Add ownership check here

        $params = [':user_id' => $currentUserId]; // Bind current user ID
        if ($search_query !== '') {
            $sql .= " AND `{$safeField}` LIKE :search_query";
            $params[':search_query'] = '%' . $search_query . '%';
        }

        $sql .= " ORDER BY `{$safeField}` ASC LIMIT 10";
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
} catch (\Throwable $e) { // Added type hint for Throwable
    // Use the logger service
    $log->error('Autosuggest error', [
        'field' => $field,
        'query' => $search_query,
        'user_id' => $currentUserId, // Log user ID
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected server error occurred during autosuggest. Please try again later.' // Harden error message
    ]);
    exit; // Ensures no further output after an error response
}
