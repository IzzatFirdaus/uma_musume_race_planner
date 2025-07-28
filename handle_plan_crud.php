<?php

// handle_plan_crud.php (Final Fix for Undefined Variable)
header('Content-Type: application/json');
$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

// Fetch mood, strategy, and condition mappings and default IDs
try {
    $moods_map = $pdo->query('SELECT label, id FROM moods')->fetchAll(PDO::FETCH_KEY_PAIR);
    $strategies_map = $pdo->query('SELECT label, id FROM strategies')->fetchAll(PDO::FETCH_KEY_PAIR);
    $conditions_map = $pdo->query('SELECT label, id FROM conditions')->fetchAll(PDO::FETCH_KEY_PAIR);

    // Get default IDs for fallbacks (querying by label)
    $default_mood_id = $moods_map['NORMAL'] ?? null;
    $default_strategy_id = $strategies_map['PACE'] ?? null;
    $default_condition_id = $conditions_map['N/A'] ?? null;

    // As a final fallback, ensure they are integers, perhaps 1 or a known default.
    // This assumes 1 is a valid, existing ID if the label isn't found.
    // In a real application, you'd ensure these defaults are seeded and known.
    if ($default_mood_id === null) {
        $default_mood_id = $pdo->query('SELECT id FROM moods LIMIT 1')->fetchColumn() ?: 1;
    }
    if ($default_strategy_id === null) {
        $default_strategy_id = $pdo->query('SELECT id FROM strategies LIMIT 1')->fetchColumn() ?: 1;
    }
    if ($default_condition_id === null) {
        $default_condition_id = $pdo->query('SELECT id FROM conditions LIMIT 1')->fetchColumn() ?: 1;
    }
} catch (PDOException $e) {
    $log->error('Failed to fetch lookup data or default IDs: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database initialization error.']);
    exit;
}


// Handle form submissions (Create/Update from detailed modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modalName'])) {
    $id = (int)($_POST['planId'] ?? 0);
    $log->info('Processing detailed plan submission.', ['plan_id' => $id ?: 'new']);

    // --- Data Collection & Validation ---
    $plan_title = trim($_POST['plan_title'] ?? 'Untitled Plan');
    $name = trim($_POST['modalName'] ?? '');
    $career_stage = $_POST['modalCareerStage'] ?? null;
    $class = $_POST['modalClass'] ?? null;
    $race_name = trim($_POST['modalRaceName'] ?? '');
    $turn_before = filter_var($_POST['modalTurnBefore'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999]]) ?: 0; // Assuming 0-999 turns
    $goal_main = trim($_POST['modalGoal'] ?? '');

    // Validate strategy, mood, condition IDs against fetched maps
    $strategy_id = isset($_POST['modalStrategy']) && array_key_exists((int)$_POST['modalStrategy'], $strategies_map) ? (int)$_POST['modalStrategy'] : $default_strategy_id;
    $mood_id = isset($_POST['modalMood']) && array_key_exists((int)$_POST['modalMood'], $moods_map) ? (int)$_POST['modalMood'] : $default_mood_id;
    $condition_id = isset($_POST['modalCondition']) && array_key_exists((int)$_POST['modalCondition'], $conditions_map) ? (int)$_POST['modalCondition'] : $default_condition_id;


    $energy = filter_var($_POST['energyRange'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]) ?: 0;
    $race_day = (isset($_POST['raceDaySwitch']) && $_POST['raceDaySwitch'] === 'on') ? 'yes' : 'no'; // Check for 'on' from checkbox
    $acquire_skill = (isset($_POST['acquireSkillSwitch']) && $_POST['acquireSkillSwitch'] === 'on') ? 'YES' : 'NO'; // Check for 'on'
    $skill_points = filter_var($_POST['skillPoints'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) ?: 0;
    $status_options = ['Planning', 'Active', 'Finished', 'Draft', 'Abandoned'];
    $status = in_array($_POST['modalStatus'] ?? '', $status_options) ? $_POST['modalStatus'] : 'Planning';
    $time_of_day = trim($_POST['modalTimeOfDay'] ?? '');
    $month = trim($_POST['modalMonth'] ?? '');
    $source = trim($_POST['modalSource'] ?? '');
    $growth_rate_speed = filter_var($_POST['growthRateSpeed'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => -100, 'max_range' => 100]]) ?: 0;
    $growth_rate_stamina = filter_var($_POST['growthRateStamina'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => -100, 'max_range' => 100]]) ?: 0;
    $growth_rate_power = filter_var($_POST['growthRatePower'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => -100, 'max_range' => 100]]) ?: 0;
    $growth_rate_guts = filter_var($_POST['growthRateGuts'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => -100, 'max_range' => 100]]) ?: 0;
    $growth_rate_wit = filter_var($_POST['growthRateWit'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => -100, 'max_range' => 100]]) ?: 0;


    // Decode JSON data from form (ensure data is an array for safety)
    $attributes_data = json_decode($_POST['attributes'] ?? '[]', true) ?: [];
    $skills_data = json_decode($_POST['skills'] ?? '[]', true) ?: [];
    $predictions_data = json_decode($_POST['predictions'] ?? '[]', true) ?: [];
    $goals_data = json_decode($_POST['goals'] ?? '[]', true) ?: [];
    $terrain_grades_data = json_decode($_POST['terrainGrades'] ?? '[]', true) ?: [];
    $distance_grades_data = json_decode($_POST['distanceGrades'] ?? '[]', true) ?: [];
    $style_grades_data = json_decode($_POST['styleGrades'] ?? '[]', true) ?: [];
    $turns_data = json_decode($_POST['turns'] ?? '[]', true) ?: []; // ADDED turns data collection

    try {
        $pdo->beginTransaction();

        $planIdToUse = $id; // Will be set on insert

        if ($id !== 0) {
            // --- UPDATE EXISTING PLAN ---
            $log->info('Updating main plan details.', ['plan_id' => $id]);
            $sql = 'UPDATE plans SET
                plan_title = ?, name = ?, career_stage = ?, class = ?, race_name = ?, turn_before = ?,
                goal = ?, strategy_id = ?, mood_id = ?, condition_id = ?, energy = ?, race_day = ?,
                acquire_skill = ?, total_available_skill_points = ?, status = ?, time_of_day = ?,
                month = ?, source = ?, growth_rate_speed = ?, growth_rate_stamina = ?,
                growth_rate_power = ?, growth_rate_guts = ?, growth_rate_wit = ?
                WHERE id = ?';
            $pdo->prepare($sql)->execute([
                $plan_title, $name, $career_stage, $class, $race_name, $turn_before,
                $goal_main, $strategy_id, $mood_id, $condition_id, $energy, $race_day,
                $acquire_skill, $skill_points, $status, $time_of_day, $month, $source,
                $growth_rate_speed, $growth_rate_stamina, $growth_rate_power,
                $growth_rate_guts, $growth_rate_wit, $id,
            ]);
        } else {
            // --- CREATE NEW PLAN ---
            $log->info('Creating new plan.');
            $sql = 'INSERT INTO plans (
                plan_title, name, career_stage, class, race_name, turn_before, goal, strategy_id,
                mood_id, condition_id, energy, race_day, acquire_skill, total_available_skill_points,
                status, time_of_day, month, source, growth_rate_speed, growth_rate_stamina,
                growth_rate_power, growth_rate_guts, growth_rate_wit
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([
                $plan_title, $name, $career_stage, $class, $race_name, $turn_before,
                $goal_main, $strategy_id, $mood_id, $condition_id, $energy, $race_day,
                $acquire_skill, $skill_points, $status, $time_of_day, $month, $source,
                $growth_rate_speed, $growth_rate_stamina, $growth_rate_power,
                $growth_rate_guts, $growth_rate_wit,
            ]);
            $planIdToUse = $pdo->lastInsertId();
            $log->info('New plan created successfully.', ['new_plan_id' => $planIdToUse, 'plan_title' => $plan_title]);
        }

        // --- Handle Related Data (UPSERT/DIFFING) ---
        $log->info('Processing related data.', ['plan_id' => $planIdToUse]);

        /**
         * Helper function for UPSERT (INSERT ... ON DUPLICATE KEY UPDATE) and DELETE for child tables.
         * This function assumes a UNIQUE KEY on (`plan_id`, `$identifierColumn`) exists in the database schema.
         *
         * @param PDO $pdo The PDO database connection.
         * @param int $planId The ID of the parent plan.
         * @param string $tableName The name of the child table.
         * @param array $incomingData An array of associative arrays representing the incoming child records.
         * @param string $identifierColumn The name of the column that uniquely identifies a child record within a plan (e.g., 'attribute_name', 'goal').
         * @param array $insertColumns An ordered array of column names for the INSERT part (excluding plan_id).
         * @param string $updateSetSql SQL SET clause for the ON DUPLICATE KEY UPDATE part (e.g., 'value = VALUES(value), grade = VALUES(grade)').
         * @param Monolog\Logger $log The logger instance.
         */
        function handleChildDataUpsert(
            $pdo,
            $planId,
            $tableName,
            $incomingData,
            $identifierColumn,
            array $insertColumns, // Use an array of column names
            $updateSetSql,
            $log
        ): void {
            $existingIdentifiers = [];
            // Only query for existing records if there's actually a plan ID (i.e., not a new plan)
            if ($planId > 0) { // Use > 0 to ensure it's a valid ID for fetching existing data
                $stmt = $pdo->prepare("SELECT `$identifierColumn` FROM `$tableName` WHERE `plan_id` = ?");
                $stmt->execute([$planId]);
                $existingIdentifiers = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            $incomingIdentifiers = [];
            $insertPlaceholders = rtrim(str_repeat('?,', count($insertColumns) + 1), ','); // +1 for plan_id

            // Construct the UPSERT SQL query
            $columnsSql = implode('`, `', $insertColumns);
            // This is the correct UPSERT syntax: INSERT ... ON DUPLICATE KEY UPDATE ...
            $upsertSql = "INSERT INTO `$tableName` (`plan_id`, `$columnsSql`) VALUES ($insertPlaceholders) ON DUPLICATE KEY UPDATE $updateSetSql";
            $upsertStmt = $pdo->prepare($upsertSql);

            foreach ($incomingData as $item) {
                $identifier = trim($item[$identifierColumn] ?? ''); // Ensure identifier is trimmed/cleaned
                if ($identifier === '' || $identifier === '0') {
                    $log->warning("Skipping $tableName record with empty identifier.", ['plan_id' => $planId, 'item' => $item]);
                    continue;
                }
                $incomingIdentifiers[] = $identifier;

                // Prepare params for UPSERT
                $params = [];
                foreach ($insertColumns as $col) {
                    $params[] = $item[$col] ?? null; // Ensure all columns are handled, provide null if missing
                }
                $upsertParams = array_merge([$planId], $params);

                try {
                    $upsertStmt->execute($upsertParams);
                    $log->debug("UPSERTed $tableName record.", ['plan_id' => $planId, 'identifier' => $identifier]);
                } catch (PDOException $e) {
                    $log->error("Failed to UPSERT $tableName record.", [
                        'plan_id' => $planId,
                        'identifier' => $identifier,
                        'message' => $e->getMessage(),
                        'sql' => $upsertStmt->queryString, // Log the SQL query
                    ]);
                    // Re-throw the exception to trigger the main transaction rollback
                    throw $e;
                }
            }

            // Delete records that are in DB but not in incoming data
            $toDeleteIdentifiers = array_diff($existingIdentifiers, $incomingIdentifiers);
            if ($toDeleteIdentifiers !== []) {
                $placeholders = rtrim(str_repeat('?,', count($toDeleteIdentifiers)), ',');
                $stmt_delete = $pdo->prepare("DELETE FROM `$tableName` WHERE `plan_id` = ? AND `$identifierColumn` IN ($placeholders)");
                $stmt_delete->execute(array_merge([$planId], $toDeleteIdentifiers));
                $log->info("Deleted old $tableName records.", ['plan_id' => $planId, 'identifiers' => $toDeleteIdentifiers]);
            }
        }


        // ATTRIBUTES
        handleChildDataUpsert(
            $pdo,
            $planIdToUse,
            'attributes',
            $attributes_data,
            'attribute_name',
            ['attribute_name', 'value', 'grade'], // Insert columns
            '`value` = VALUES(`value`), `grade` = VALUES(`grade`)', // Update SET clause
            $log
        );


        // GOALS
        handleChildDataUpsert(
            $pdo,
            $planIdToUse,
            'goals',
            $goals_data,
            'goal',
            ['goal', 'result'],
            '`result` = VALUES(`result`)',
            $log
        );

        // TERRAIN GRADES
        handleChildDataUpsert(
            $pdo,
            $planIdToUse,
            'terrain_grades',
            $terrain_grades_data,
            'terrain',
            ['terrain', 'grade'],
            '`grade` = VALUES(`grade`)',
            $log
        );

        // DISTANCE GRADES
        handleChildDataUpsert(
            $pdo,
            $planIdToUse,
            'distance_grades',
            $distance_grades_data,
            'distance',
            ['distance', 'grade'],
            '`grade` = VALUES(`grade`)',
            $log
        );

        // STYLE GRADES
        handleChildDataUpsert(
            $pdo,
            $planIdToUse,
            'style_grades',
            $style_grades_data,
            'style',
            ['style', 'grade'],
            '`grade` = VALUES(`grade`)',
            $log
        );

        // TURNS
        handleChildDataUpsert(
            $pdo,
            $planIdToUse,
            'turns',
            $turns_data,
            'turn_number',
            ['turn_number', 'speed', 'stamina', 'power', 'guts', 'wit'],
            '`speed` = VALUES(`speed`), `stamina` = VALUES(`stamina`), `power` = VALUES(`power`), `guts` = VALUES(`guts`), `wit` = VALUES(`wit`)',
            $log
        );


        // SKILLS & RACE PREDICTIONS (handle by ID for explicit update/delete/insert)
        // These tables don't have natural unique identifiers other than their auto-incrementing ID.
        // Frontend should send 'id' for existing records.

        // Skills
        $existing_skills_db = [];
        if ($planIdToUse > 0) { // <-- Use $planIdToUse and check if it's > 0
            $stmt = $pdo->prepare('SELECT id, skill_name FROM skills WHERE plan_id = ?');
            $stmt->execute([$planIdToUse]);
            $existing_skills_db = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [id => skill_name]
        }
        $incoming_skill_ids = [];
        foreach ($skills_data as $skill) {
            $skill_id = filter_var($skill['id'] ?? null, FILTER_VALIDATE_INT); // Validate ID
            if ($skill_id && array_key_exists($skill_id, $existing_skills_db)) {
                // Update existing skill
                $stmt_update = $pdo->prepare('UPDATE skills SET skill_name=?, sp_cost=?, acquired=?, tag=?, notes=? WHERE id=? AND plan_id=?');
                $stmt_update->execute([
                    trim((string) $skill['skill_name']), trim($skill['sp_cost'] ?? ''), ($skill['acquired'] === 'yes' ? 'yes' : 'no'), trim($skill['tag'] ?? ''), trim($skill['notes'] ?? ''),
                    $skill_id, $planIdToUse,
                ]);
                $incoming_skill_ids[] = $skill_id;
                $log->debug('Updated skill.', ['id' => $skill_id, 'plan_id' => $planIdToUse, 'skill_name' => $skill['skill_name']]);
            } elseif (!in_array(trim((string) $skill['skill_name']), ['', '0'], true)) {
                // Insert new skill (if skill name is not empty)
                $stmt_insert = $pdo->prepare('INSERT INTO skills (plan_id, skill_name, sp_cost, acquired, tag, notes) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt_insert->execute([
                    $planIdToUse, trim((string) $skill['skill_name']), trim($skill['sp_cost'] ?? ''), ($skill['acquired'] === 'yes' ? 'yes' : 'no'), trim($skill['tag'] ?? ''), trim($skill['notes'] ?? ''),
                ]);
                $log->debug('Inserted new skill.', ['plan_id' => $planIdToUse, 'skill_name' => $skill['skill_name']]);
            } else {
                $log->warning('Skipped inserting skill with empty name.', ['plan_id' => $planIdToUse, 'skill_data' => $skill]);
            }
        }
        // Delete skills that are in DB but not in incoming data
        $skills_to_delete_ids = array_diff(array_keys($existing_skills_db), $incoming_skill_ids);
        if ($skills_to_delete_ids !== []) {
            $placeholders = rtrim(str_repeat('?,', count($skills_to_delete_ids)), ',');
            $stmt_delete = $pdo->prepare("DELETE FROM skills WHERE plan_id = ? AND id IN ($placeholders)");
            $stmt_delete->execute(array_merge([$planIdToUse], $skills_to_delete_ids));
            $log->info('Deleted old skills.', ['plan_id' => $planIdToUse, 'ids' => $skills_to_delete_ids]);
        }


        // Race Predictions
        $existing_predictions_db = [];
        if ($planIdToUse > 0) { // <-- Use $planIdToUse and check if it's > 0
            $stmt = $pdo->prepare('SELECT id, race_name FROM race_predictions WHERE plan_id = ?');
            $stmt->execute([$planIdToUse]);
            $existing_predictions_db = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [id => race_name]
        }
        $incoming_prediction_ids = [];
        foreach ($predictions_data as $pred) {
            $pred_id = filter_var($pred['id'] ?? null, FILTER_VALIDATE_INT); // Validate ID
            if ($pred_id && array_key_exists($pred_id, $existing_predictions_db)) {
                // Update existing prediction
                $stmt_update = $pdo->prepare('UPDATE race_predictions SET race_name = ?, venue = ?, ground = ?, distance = ?, track_condition = ?, direction = ?, speed = ?, stamina = ?, power = ?, guts = ?, wit = ?, comment = ? WHERE id = ? AND plan_id = ?');
                $stmt_update->execute([
                    trim((string) $pred['race_name']), trim((string) $pred['venue']), trim((string) $pred['ground']), trim((string) $pred['distance']), trim($pred['track_condition'] ?? ''),
                    trim((string) $pred['direction']), trim((string) $pred['speed']), trim((string) $pred['stamina']), trim((string) $pred['power']), trim((string) $pred['guts']), trim((string) $pred['wit']),
                    trim($pred['comment'] ?? ''), $pred_id, $planIdToUse,
                ]);
                $incoming_prediction_ids[] = $pred_id;
                $log->debug('Updated prediction.', ['id' => $pred_id, 'plan_id' => $planIdToUse, 'race_name' => $pred['race_name']]);
            } elseif (!in_array(trim((string) $pred['race_name']), ['', '0'], true)) {
                // Insert new prediction (if race name is not empty)
                $stmt_insert = $pdo->prepare('INSERT INTO race_predictions (plan_id, race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt_insert->execute([
                    $planIdToUse, trim((string) $pred['race_name']), trim((string) $pred['venue']), trim((string) $pred['ground']), trim((string) $pred['distance']), trim($pred['track_condition'] ?? ''),
                    trim((string) $pred['direction']), trim((string) $pred['speed']), trim((string) $pred['stamina']), trim((string) $pred['power']), trim((string) $pred['guts']), trim((string) $pred['wit']), trim($pred['comment'] ?? ''),
                ]);
                $log->debug('Inserted new prediction.', ['plan_id' => $planIdToUse, 'race_name' => $pred['race_name']]);
            } else {
                $log->warning('Skipped inserting prediction with empty race name.', ['plan_id' => $planIdToUse, 'prediction_data' => $pred]);
            }
        }
        // Delete predictions that are in DB but not in incoming data
        $predictions_to_delete_ids = array_diff(array_keys($existing_predictions_db), $incoming_prediction_ids);
        if ($predictions_to_delete_ids !== []) {
            $placeholders = rtrim(str_repeat('?,', count($predictions_to_delete_ids)), ',');
            $stmt_delete = $pdo->prepare("DELETE FROM race_predictions WHERE plan_id = ? AND id IN ($placeholders)");
            $stmt_delete->execute(array_merge([$planIdToUse], $predictions_to_delete_ids));
            $log->info('Deleted old predictions.', ['plan_id' => $planIdToUse, 'ids' => $predictions_to_delete_ids]);
        }


        // Add to activity log
        $log_desc = $id !== 0 ? "Plan updated: $name" : "New plan created: $name";
        $log_icon = $id !== 0 ? 'bi-arrow-repeat' : 'bi-person-plus';
        $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')->execute([$log_desc, $log_icon]);

        $pdo->commit();
        echo json_encode(['success' => true, 'new_id' => $planIdToUse]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        $log->error('Database error in main plan handler', [
            'plan_id' => $id,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(), // Ensure file/line are logged
            'line' => $e->getLine(),
        ]);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'A database error occurred. Please check the logs.']);
    }
    exit;
}

// Handle delete action (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid delete ID.']);
        exit;
    }
    $log->info('Processing soft-delete request.', ['plan_id' => $id]);

    try {
        $pdo->prepare('UPDATE plans SET deleted_at = NOW() WHERE id = ?')->execute([$id]);

        $log->info('Plan soft-deleted successfully.', ['plan_id' => $id]);

        $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')
            ->execute(["Plan (ID: $id) soft-deleted", 'bi-trash']);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $log->error('Database error during soft-deletion', [
            'plan_id' => $id,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error during deletion.']);
    }
    exit();
}


// Handle Quick create form
$is_quick_create = $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['trainee_name']) && isset($_POST['career_stage'])
    && isset($_POST['traineeClass']) && isset($_POST['race_name']);

if ($is_quick_create) {
    $log->info('Processing quick create request.');

    // Input Sanitization and Validation
    $trainee_name = trim((string) $_POST['trainee_name']);
    $career_stage = in_array($_POST['career_stage'] ?? '', ['predebut','junior','classic','senior','finale']) ? $_POST['career_stage'] : null;
    $class = in_array($_POST['traineeClass'] ?? '', ['debut','maiden','beginner','bronze','silver','gold','platinum','star','legend']) ? $_POST['traineeClass'] : null;
    $race_name = trim((string) $_POST['race_name']);
    $status = 'Planning'; // Always planning for quick create

    if ($trainee_name === '' || $trainee_name === '0' || empty($career_stage) || empty($class)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields for quick create.']);
        exit;
    }

    $mood_id = $moods_map['NORMAL'] ?? $default_mood_id; // Use dynamically fetched default
    $strategy_id = $strategies_map['PACE'] ?? $default_strategy_id; // Use dynamically fetched default
    $condition_id = $conditions_map['N/A'] ?? $default_condition_id; // Use dynamically fetched default

    $acquire_skill_default = 'NO';
    $plan_title_default = $trainee_name . "'s New Plan";

    try {
        $pdo->beginTransaction();

        $sql = 'INSERT INTO plans (name, plan_title, career_stage, class, race_name, mood_id,
            strategy_id, condition_id, acquire_skill, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([
            $trainee_name, $plan_title_default, $career_stage, $class, $race_name, $mood_id,
            $strategy_id, $condition_id, $acquire_skill_default, $status,
        ]);
        $plan_id = $pdo->lastInsertId();

        // Create default attributes for the new plan
        $default_attributes = ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT'];
        $stmt_attr = $pdo->prepare('INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES (?, ?, 0, "G")');
        foreach ($default_attributes as $attr_name) {
            $stmt_attr->execute([$plan_id, $attr_name]);
        }

        $pdo->commit();

        $log->info('Plan quick-created successfully.', ['new_plan_id' => $plan_id, 'trainee_name' => $trainee_name]);

        $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')
             ->execute(['New plan quick-created: ' . $trainee_name, 'bi-person-plus']);

        echo json_encode(['success' => true, 'new_id' => $plan_id]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        $log->error('Database error in quick create', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Quick create failed.']);
    }
    exit();
}

// Fallback for invalid actions
http_response_code(400); // Bad Request
$log->warning('Invalid action requested in handle_plan_crud.php', ['request_data' => $_POST, 'server' => $_SERVER]);
echo json_encode(['success' => false, 'error' => 'No valid action specified.']);
