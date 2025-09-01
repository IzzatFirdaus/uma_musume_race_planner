
<?php
// Start output buffering to prevent accidental output
ob_start();

/**
 * get_plans.php
 *
 * This script retrieves a list of all non-deleted trainee plans from the database.
 * It also fetches associated mood and strategy labels, and importantly,
 * includes the main attribute (stats) values for each plan.
 *
 * Plans are ordered by a custom status priority (Active, Planning, Draft, Finished, Abandoned)
 * and then by their last update timestamp in descending order.
 *
 * Dependencies:
 * - includes/db.php: For establishing the PDO database connection.
 * - includes/logger.php: For logging any errors that occur during database operations.
 *
 * Output:
 * - JSON response containing 'success' status and an array of 'plans'.
 * - Each plan object in the 'plans' array will include top-level plan details
 * and a nested 'stats' object with 'speed', 'stamina', 'power', 'guts', 'wit' values.
 */

$buffered = true;
header('Content-Type: application/json'); // Set Content-Type header for JSON response
$pdo = require __DIR__ . '/includes/db.php'; // Include and execute the database connection script
$log = require __DIR__ . '/includes/logger.php'; // Include and execute the logger script

try {
    // SQL query to fetch plans, their associated mood and strategy,
    // and all attributes.
    // We LEFT JOIN with 'attributes' to ensure plans without attributes are still included.
    // This will result in multiple rows per plan if a plan has multiple attributes.
    $query = "
        SELECT
            p.*,                -- Select all columns from the plans table
            m.label AS mood,    -- Select mood label from moods table, aliased as 'mood'
            s.label AS strategy,-- Select strategy label from strategies table, aliased as 'strategy'
            a.attribute_name,   -- Select attribute name (e.g., 'speed', 'stamina')
            a.value AS attribute_value -- Select attribute value, aliased to avoid conflicts
        FROM
            plans p
        LEFT JOIN
            moods m ON p.mood_id = m.id
        LEFT JOIN
            strategies s ON p.strategy_id = s.id
        LEFT JOIN
            attributes a ON p.id = a.plan_id -- Join with attributes table on plan_id
        WHERE
            p.deleted_at IS NULL -- Only fetch plans that are not soft-deleted
        ORDER BY
            CASE p.status       -- Custom sorting based on plan status
                WHEN 'Active' THEN 1
                WHEN 'Planning' THEN 2
                WHEN 'Draft' THEN 3
                WHEN 'Finished' THEN 4
                WHEN 'Abandoned' THEN 5
                ELSE 6          -- Fallback for any other status
            END,
            p.updated_at DESC   -- Secondary sort by last update timestamp (most recent first)
    ";

    // Execute the query and fetch all results
    // PDO::FETCH_ASSOC ensures results are returned as associative arrays (column_name => value)
    $raw_plans = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    // Initialize an array to hold the processed plans with nested attributes
    $plans_with_attributes = [];

    // Process the raw results to group attributes under a 'stats' key for each plan
    foreach ($raw_plans as $row) {
        $plan_id = $row['id'];

        // If this is the first time we encounter this plan_id, initialize its structure
        if (!isset($plans_with_attributes[$plan_id])) {
            $plans_with_attributes[$plan_id] = $row; // Copy all top-level plan details
            // Initialize the 'stats' object with default zero values for all attributes
            $plans_with_attributes[$plan_id]['stats'] = [
                'speed' => 0,
                'stamina' => 0,
                'power' => 0,
                'guts' => 0,
                'wit' => 0,
            ];
            // Remove the raw attribute columns from the top level, as they'll be nested
            unset($plans_with_attributes[$plan_id]['attribute_name']);
            unset($plans_with_attributes[$plan_id]['attribute_value']);
        }

        // If attribute data exists for this row, add it to the 'stats' object
        if ($row['attribute_name']) {
            $attr_name = strtolower((string) $row['attribute_name']); // Ensure attribute name is lowercase
            // Check if the attribute name is one of the expected stats
            if (isset($plans_with_attributes[$plan_id]['stats'][$attr_name])) {
                $plans_with_attributes[$plan_id]['stats'][$attr_name] = (int)$row['attribute_value']; // Cast to int
            }
        }
    }

    // Convert the associative array (keyed by plan_id) to an indexed array for final JSON output
    $final_plans = array_values($plans_with_attributes);

    // Encode the final array of plans into a JSON response
    $json = json_encode(['success' => true, 'plans' => $final_plans]);
    // Capture and clear any accidental output produced earlier, expose limited debug headers
    $accidental = ob_get_contents();
    header('X-Debug-Output-Length: ' . strlen($accidental));
    if ($accidental) {
        header('X-Debug-Output: ' . substr(str_replace(["\r", "\n"], [' ', ' '], $accidental), 0, 100));
    }
    ob_clean(); // Remove accidental output
    echo $json;
} catch (PDOException $e) {
    // Log the database error for debugging purposes
    $log->error('Failed to fetch plan list', [
        'message' => method_exists($e, 'getMessage') ? $e->getMessage() : (string)$e,
        'file' => method_exists($e, 'getFile') ? $e->getFile() : '',
        'line' => method_exists($e, 'getLine') ? $e->getLine() : '',
    ]);
    // Set HTTP response code to 500 (Internal Server Error)
    http_response_code(500);
    // Return a JSON error message to the client (preserve limited debug headers)
    $json = json_encode(['success' => false, 'error' => 'A database error occurred. Please try again later.']);
    $accidental = ob_get_contents();
    header('X-Debug-Output-Length: ' . strlen($accidental));
    if ($accidental) {
        header('X-Debug-Output: ' . substr(str_replace(["\r", "\n"], [' ', ' '], $accidental), 0, 100));
    }
    ob_clean();
    echo $json;
}
