<?php

/**
 * get_skill_reference.php
 *
 * This script provides an API endpoint to fetch skill reference data from the database.
 * It supports filtering skills by search term (skill name), stat type, and tag.
 * The results are ordered by skill name. This data is primarily used for
 * autocomplete functionality and providing contextual information in the frontend.
 *
 * Dependencies:
 * - includes/db.php: For establishing the PDO database connection.
 * - includes/logger.php: For logging any errors that occur during script execution.
 *
 * Input Parameters (via GET request):
 * - 'search' (optional): A string to filter skill names (case-insensitive LIKE search).
 * - 'stat_type' (optional): A specific stat type (e.g., 'speed', 'stamina') to filter by.
 * - 'tag' (optional): A specific tag (e.g., 'runner', 'strategy') to filter by.
 *
 * Output:
 * - JSON array of skill objects, each containing 'skill_name', 'tag', 'stat_type', and 'description'.
 * - In case of a database error, returns a JSON object with an 'error' message and sets HTTP status to 500.
 */

header('Content-Type: application/json'); // Set the Content-Type header to indicate JSON response
$pdo = require __DIR__ . '/includes/db.php'; // Include and execute the database connection script
$log = require __DIR__ . '/includes/logger.php'; // Include and execute the logger script for error logging

try {
    // Retrieve and sanitize GET parameters for filtering
    $search = $_GET['search'] ?? '';
    $stat_type = $_GET['stat_type'] ?? '';
    $tag = $_GET['tag'] ?? '';

    // Initialize arrays to build the WHERE clause dynamically
    $whereConditions = [];
    $params = [];

    // Add conditions based on provided parameters
    if (!empty($search)) {
        $whereConditions[] = 'skill_name LIKE ?'; // Use LIKE for partial matching
        $params[] = "%$search%"; // Add wildcard for LIKE search
    }
    if (!empty($stat_type)) {
        $whereConditions[] = 'stat_type = ?'; // Exact match for stat_type
        $params[] = $stat_type;
    }
    if (!empty($tag)) {
        $whereConditions[] = 'tag = ?'; // Exact match for tag
        $params[] = $tag;
    }

    // Base SQL query to select relevant skill columns
    // 'description' is included for contextual info in the frontend autocomplete
    $sql = 'SELECT skill_name, tag, stat_type, description FROM skill_reference';

    // Append WHERE clause if any conditions are present
    if ($whereConditions !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $whereConditions); // Combine conditions with AND
    }
    // Add ORDER BY clause to sort results by skill name
    $sql .= ' ORDER BY skill_name';

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $pdo->prepare($sql);
    // Execute the prepared statement with the collected parameters
    $stmt->execute($params);
    // Fetch all matching skills as an associative array
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array for consistent object keys

    // Encode the fetched skills data into a JSON array and output it
    echo json_encode($skills);
} catch (PDOException $e) {
    // Log the database error for debugging purposes, including the parameters received
    $log->error('Failed to fetch skill reference data', [
        'params' => $_GET,
        'message' => $e->getMessage(),
    ]);
    // Set HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return a generic JSON error message to the client
    echo json_encode(['error' => 'A database error occurred.']);
}
