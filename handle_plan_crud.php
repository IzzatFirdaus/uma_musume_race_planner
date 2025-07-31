<?php

ob_start();

// It's recommended to remove these lines during development to see errors.
// Configure error reporting in your php.ini for a production server instead.
// ini_set('display_errors', 0);
// error_reporting(0);

// handle_plan_crud.php
// This script handles CRUD operations (Create, Read, Update, Delete) for trainee plans
// and their associated child data (attributes, skills, predictions, goals, grades, turns).
// It uses PDO transactions for atomicity and a helper function for UPSERT/DIFFing child data.

header('Content-Type: application/json'); // Set Content-Type header for JSON response
require_once __DIR__ . '/includes/logger.php'; // Include the logger utility
$pdo = require __DIR__ . '/includes/db.php'; // Include and execute the database connection script
$log = $log ?? (require __DIR__ . '/includes/logger.php'); // Ensure logger is available

// NEW: Include the trainee image handler for its PHP function
require_once __DIR__ . '/components/trainee_image_handler.php';

$response = ['success' => false, 'error' => 'An unknown error occurred.'];
$action_performed = 'unknown'; // For logging context

try {
    // Fetch mood, strategy, and condition mappings and default IDs for validation/fallbacks
    // These are loaded once at the beginning of the script.
    $moods_map = $pdo->query('SELECT label, id FROM moods')->fetchAll(PDO::FETCH_KEY_PAIR);
    $strategies_map = $pdo->query('SELECT label, id FROM strategies')->fetchAll(PDO::FETCH_KEY_PAIR);
    $conditions_map = $pdo->query('SELECT label, id FROM conditions')->fetchAll(PDO::FETCH_KEY_PAIR);

    // Determine fallback IDs in case specific labels are not found or associated IDs are null
    $default_mood_id = $moods_map['NORMAL'] ?? ($pdo->query('SELECT id FROM moods LIMIT 1')->fetchColumn() ?: 1);
    $default_strategy_id = $strategies_map['PACE'] ?? ($pdo->query('SELECT id FROM strategies LIMIT 1')->fetchColumn() ?: 1);
    $default_condition_id = $conditions_map['N/A'] ?? ($pdo->query('SELECT id FROM conditions LIMIT 1')->fetchColumn() ?: 1);

    // --- CONSOLIDATED POST REQUEST HANDLING ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle Delete operation
        if (isset($_POST['delete_id'])) {
            $plan_id = (int)$_POST['delete_id'];
            $action_performed = 'delete';

            $pdo->beginTransaction(); // Start transaction for delete

            // Before deleting the plan, fetch and delete its associated image file
            $stmt_fetch_image = $pdo->prepare('SELECT trainee_image_path FROM plans WHERE id = ?');
            $stmt_fetch_image->execute([$plan_id]);
            $image_to_delete = $stmt_fetch_image->fetchColumn();

            if ($image_to_delete && file_exists(__DIR__ . '/../' . $image_to_delete)) {
                if (unlink(__DIR__ . '/../' . $image_to_delete)) {
                    $log->info("Deleted trainee image during plan deletion for plan ID {$plan_id}: {$image_to_delete}");
                } else {
                    $log->error("Failed to delete trainee image during plan deletion for plan ID {$plan_id}: {$image_to_delete}");
                    // Continue with plan deletion even if image delete fails to avoid orphaned plans
                }
            }

            $stmt = $pdo->prepare('UPDATE plans SET deleted_at = NOW() WHERE id = ?');
            $stmt->execute([$plan_id]);

            if ($stmt->rowCount()) {
                $log_desc = "Plan soft-deleted: ID $plan_id";
                $log_icon = 'bi-trash';
                $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')->execute([$log_desc, $log_icon]);
                $pdo->commit();
                $response = ['success' => true, 'message' => 'Plan deleted successfully.'];
                $log->info('Plan soft-deleted successfully.', ['plan_id' => $plan_id]);
            } else {
                $pdo->rollBack();
                $response = ['success' => false, 'error' => 'Plan not found or already deleted.'];
                $log->warning('Attempted to delete non-existent or already deleted plan.', ['plan_id' => $plan_id]);
            }
        }
        // Handle Detailed Plan Create/Update (from plan_details_modal or plan-inline-details)
        elseif (isset($_POST['modalName'])) { // Indicator for a detailed plan submission
            $plan_id = isset($_POST['planId']) ? (int)$_POST['planId'] : 0;
            $action_performed = $plan_id > 0 ? 'update' : 'create';

            $log->info('Processing detailed plan submission.', ['plan_id' => $plan_id ?: 'new']);

            // --- Data Collection & Validation for Main Plan ---
            $plan_title = trim($_POST['plan_title'] ?? 'Untitled Plan');
            $name = trim($_POST['modalName'] ?? '');
            $career_stage = $_POST['modalCareerStage'] ?? null;
            $class = $_POST['modalClass'] ?? null;
            $race_name = trim($_POST['modalRaceName'] ?? '');
            $turn_before = filter_var($_POST['modalTurnBefore'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999]]) ?: 0;
            $goal_main = trim($_POST['modalGoal'] ?? '');

            // Validate and use fallback IDs for foreign keys
            $strategy_id = isset($_POST['modalStrategy']) && array_key_exists((int)$_POST['modalStrategy'], $strategies_map) ? (int)$_POST['modalStrategy'] : $default_strategy_id;
            $mood_id = isset($_POST['modalMood']) && array_key_exists((int)$_POST['modalMood'], $moods_map) ? (int)$_POST['modalMood'] : $default_mood_id;
            $condition_id = isset($_POST['modalCondition']) && array_key_exists((int)$_POST['modalCondition'], $conditions_map) ? (int)$_POST['modalCondition'] : $default_condition_id;

            $energy = filter_var($_POST['energyRange'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]) ?: 0;
            $race_day = (isset($_POST['raceDaySwitch']) && $_POST['raceDaySwitch'] === 'on') ? 'yes' : 'no';
            $acquire_skill = (isset($_POST['acquireSkillSwitch']) && $_POST['acquireSkillSwitch'] === 'on') ? 'YES' : 'NO';
            $total_available_skill_points = filter_var($_POST['skillPoints'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) ?: 0;
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

            // Decode JSON data from form (use ?: [] for robust empty array handling)
            $attributes_data = json_decode($_POST['attributes'] ?? '[]', true) ?: [];
            $skills_data = json_decode($_POST['skills'] ?? '[]', true) ?: [];
            $predictions_data = json_decode($_POST['predictions'] ?? '[]', true) ?: [];
            $goals_data = json_decode($_POST['goals'] ?? '[]', true) ?: [];
            $terrain_grades_data = json_decode($_POST['terrainGrades'] ?? '[]', true) ?: [];
            $distance_grades_data = json_decode($_POST['distanceGrades'] ?? '[]', true) ?: [];
            $style_grades_data = json_decode($_POST['styleGrades'] ?? '[]', true) ?: [];
            $turns_data = json_decode($_POST['turns'] ?? '[]', true) ?: [];

            // NEW: Image Upload Handling variables
            $newImagePath = null;
            $oldImagePath = trim($_POST['existingTraineeImagePath'] ?? ''); // Path of existing image from hidden field
            $clearImageFlag = isset($_POST['clear_trainee_image']) && $_POST['clear_trainee_image'] === '1';

            $pdo->beginTransaction(); // Start transaction for detailed save

            $planIdToUse = $plan_id; // Will be updated if inserting a new plan

            if ($plan_id > 0) {
                // --- UPDATE EXISTING PLAN ---
                // First, get the current trainee_image_path from the DB before calling handleTraineeImageUpload
                $stmt_get_current_image = $pdo->prepare('SELECT trainee_image_path FROM plans WHERE id = ?');
                $stmt_get_current_image->execute([$plan_id]);
                $currentDbImagePath = $stmt_get_current_image->fetchColumn();

                // Call the image upload handler. Pass the path from the DB, not from the hidden field, for robust deletion.
                $newImagePath = handleTraineeImageUpload(
                    $pdo,
                    $plan_id,
                    $_FILES['traineeImageUpload'] ?? ['error' => UPLOAD_ERR_NO_FILE], // Pass $_FILES array or empty if not set
                    $currentDbImagePath, // Use the path fetched directly from DB
                    $log
                );

                $log->info('Updating main plan details.', ['plan_id' => $plan_id, 'new_image_path' => $newImagePath]);
                $sql = 'UPDATE plans SET
                    plan_title = ?, name = ?, career_stage = ?, class = ?, race_name = ?, turn_before = ?,
                    goal = ?, strategy_id = ?, mood_id = ?, condition_id = ?, energy = ?, race_day = ?,
                    acquire_skill = ?, total_available_skill_points = ?, status = ?, time_of_day = ?,
                    month = ?, source = ?, growth_rate_speed = ?, growth_rate_stamina = ?,
                    growth_rate_power = ?, growth_rate_guts = ?, growth_rate_wit = ?,
                    trainee_image_path = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $plan_title, $name, $career_stage, $class, $race_name, $turn_before,
                    $goal_main, $strategy_id, $mood_id, $condition_id, $energy, $race_day,
                    $acquire_skill, $total_available_skill_points, $status, $time_of_day, $month, $source,
                    $growth_rate_speed, $growth_rate_stamina, $growth_rate_power,
                    $growth_rate_guts, $growth_rate_wit,
                    $newImagePath, // NEW: trainee_image_path
                    $plan_id,
                ]);
            } else {
                // --- CREATE NEW PLAN ---
                $log->info('Creating new plan.');
                $sql = 'INSERT INTO plans (
                    plan_title, name, career_stage, class, race_name, turn_before, goal, strategy_id,
                    mood_id, condition_id, energy, race_day, acquire_skill, total_available_skill_points,
                    status, time_of_day, month, source, growth_rate_speed, growth_rate_stamina,
                    growth_rate_power, growth_rate_guts, growth_rate_wit, trainee_image_path
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'; // NEW: 24 placeholders instead of 23
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $plan_title, $name, $career_stage, $class, $race_name, $turn_before,
                    $goal_main, $strategy_id, $mood_id, $condition_id, $energy, $race_day,
                    $acquire_skill, $total_available_skill_points, $status, $time_of_day, $month, $source,
                    $growth_rate_speed, $growth_rate_stamina, $growth_rate_power,
                    $growth_rate_guts, $growth_rate_wit,
                    null, // NEW: trainee_image_path is initially NULL for new plans, handled after insert for file processing
                ]);
                $planIdToUse = $pdo->lastInsertId(); // Get the ID of the newly inserted plan
                $log->info('New plan created successfully.', ['new_plan_id' => $planIdToUse, 'plan_title' => $plan_title]);

                // Now handle image upload for the newly created plan
                $newImagePath = handleTraineeImageUpload(
                    $pdo,
                    (int)$planIdToUse,
                    $_FILES['traineeImageUpload'] ?? ['error' => UPLOAD_ERR_NO_FILE],
                    null, // No old image path for a new plan
                    $log
                );
                // Update the newly created plan with the image path if one was uploaded
                if ($newImagePath !== null) {
                    $stmt_update_image = $pdo->prepare('UPDATE plans SET trainee_image_path = ? WHERE id = ?');
                    $stmt_update_image->execute([$newImagePath, $planIdToUse]);
                }
            }

            // --- Handle Related Data (UPSERT/DIFFING) ---
            $log->info('Processing related data.', ['plan_id' => $planIdToUse]);

            /**
             * Helper function for UPSERT (INSERT ... ON DUPLICATE KEY UPDATE) and DELETE for child tables.
             * This function assumes a UNIQUE KEY on (`plan_id`, `$identifierColumn`) exists in the database schema.
             * It processes incoming data, updates/inserts existing/new records, and deletes records not in incoming data.
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
                array $insertColumns,
                $updateSetSql,
                $log
            ): void {
                $existingIdentifiers = [];
                if ($planId > 0) {
                    $stmt = $pdo->prepare("SELECT `$identifierColumn` FROM `$tableName` WHERE `plan_id` = ?");
                    $stmt->execute([$planId]);
                    $existingIdentifiers = $stmt->fetchAll(PDO::FETCH_COLUMN);
                }

                $incomingIdentifiers = [];
                $upsertValues = [];
                $upsertPlaceholders = [];

                if (!empty($incomingData)) {
                    foreach ($incomingData as $item) {
                        // For 'attributes' table, ensure attribute_name is consistently uppercase for UPSERT matching
                        // This fixes potential case sensitivity issues between frontend and DB unique keys.
                        if ($tableName === 'attributes' && isset($item['attribute_name'])) {
                            $item['attribute_name'] = strtoupper($item['attribute_name']);
                        }

                        $identifier_val = trim($item[$identifierColumn] ?? '');
                        if ($identifier_val === '' || $identifier_val === '0') {
                            $log->warning("Skipping $tableName record with empty identifier value.", ['plan_id' => $planId, 'item' => $item, 'identifier_column' => $identifierColumn]);
                            continue;
                        }
                        $incomingIdentifiers[] = $identifier_val;

                        $current_row_values = [$planId]; // Start with plan_id
                        foreach ($insertColumns as $col) {
                            $current_row_values[] = $item[$col] ?? null;
                        }
                        $upsertValues = array_merge($upsertValues, $current_row_values);
                        $upsertPlaceholders[] = '(' . rtrim(str_repeat('?,', count($current_row_values)), ',') . ')';
                    }

                    // Perform UPSERT for new/updated records in a single batch INSERT ... ON DUPLICATE KEY UPDATE
                    if ($upsertPlaceholders !== []) {
                        $columnsSql = implode('`, `', $insertColumns);
                        $upsertSql = "INSERT INTO `$tableName` (`plan_id`, `$columnsSql`) VALUES " . implode(', ', $upsertPlaceholders) . " ON DUPLICATE KEY UPDATE $updateSetSql";
                        $upsertStmt = $pdo->prepare($upsertSql);
                        $upsertStmt->execute($upsertValues);
                        $log->debug("UPSERTed $tableName records.", ['plan_id' => $planId, 'count' => count($incomingData)]);
                    }
                }

                // Delete records that are in DB but no longer in the incoming data
                $toDeleteIdentifiers = array_diff($existingIdentifiers, $incomingIdentifiers);
                if ($toDeleteIdentifiers !== []) {
                    $placeholders = rtrim(str_repeat('?,', count($toDeleteIdentifiers)), ',');
                    $stmt_delete = $pdo->prepare("DELETE FROM `$tableName` WHERE `plan_id` = ? AND `$identifierColumn` IN ($placeholders)");
                    $stmt_delete->execute(array_merge([$planId], $toDeleteIdentifiers));
                    $log->info("Deleted old $tableName records.", ['plan_id' => $planId, 'identifiers' => $toDeleteIdentifiers]);
                }
            }


            // --- Process Child Data Using the Helper Function ---

            // ATTRIBUTES: `attribute_name` is unique per plan
            handleChildDataUpsert(
                $pdo,
                $planIdToUse,
                'attributes',
                $attributes_data,
                'attribute_name',
                ['attribute_name', 'value', 'grade'],
                '`value` = VALUES(`value`), `grade` = VALUES(`grade`)',
                $log
            );

            // GOALS: `goal` is unique per plan
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

            // TERRAIN GRADES: `terrain` is unique per plan
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

            // DISTANCE GRADES: `distance` is unique per plan
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

            // STYLE GRADES: `style` is unique per plan
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

            // TURNS: `turn_number` is unique per plan
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


            // SKILLS & RACE PREDICTIONS: These tables do NOT have natural unique keys (like skill_name or race_name)
            // within a plan. Thus, they are handled by deleting all associated records for the plan_id
            // and then re-inserting all submitted records. This ensures no orphaned records and reflects frontend state.

            // Skills
            $stmt_delete_skills = $pdo->prepare('DELETE FROM skills WHERE plan_id = ?');
            $stmt_delete_skills->execute([$planIdToUse]);
            $log->info('Cleared old skills for re-insertion.', ['plan_id' => $planIdToUse]);

            if ($skills_data !== []) {
                $insert_values = [];
                $placeholders = [];
                foreach ($skills_data as $skill) {
                    // Only insert if skill_name is not empty
                    if (!in_array(trim((string) $skill['skill_name']), ['', '0'], true) && trim((string) $skill['skill_name']) !== '0') {
                        $placeholders[] = '(?, ?, ?, ?, ?, ?)'; // plan_id, skill_name, sp_cost, acquired, tag, notes
                        $insert_values = array_merge($insert_values, [
                            $planIdToUse,
                            trim((string) $skill['skill_name']),
                            trim($skill['sp_cost'] ?? ''),
                            (isset($skill['acquired']) && $skill['acquired'] === 'yes' ? 'yes' : 'no'),
                            trim($skill['tag'] ?? ''),
                            trim($skill['notes'] ?? ''),
                        ]);
                    } else {
                        $log->warning('Skipped inserting skill with empty name.', ['plan_id' => $planIdToUse, 'skill_data' => $skill]);
                    }
                }
                if ($placeholders !== []) {
                    $insert_sql = 'INSERT INTO skills (plan_id, skill_name, sp_cost, acquired, tag, notes) VALUES ' . implode(', ', $placeholders);
                    $stmt_insert_skills = $pdo->prepare($insert_sql);
                    $stmt_insert_skills->execute($insert_values);
                    $log->debug('Inserted new skills.', ['plan_id' => $planIdToUse, 'count' => count($placeholders)]);
                }
            }

            // Race Predictions
            $stmt_delete_predictions = $pdo->prepare('DELETE FROM race_predictions WHERE plan_id = ?');
            $stmt_delete_predictions->execute([$planIdToUse]);
            $log->info('Cleared old predictions for re-insertion.', ['plan_id' => $planIdToUse]);

            if ($predictions_data !== []) {
                $insert_values = [];
                $placeholders = [];
                foreach ($predictions_data as $pred) {
                    // Only insert if race_name is not empty
                    if (!in_array(trim((string) $pred['race_name']), ['', '0'], true) && trim((string) $pred['race_name']) !== '0') {
                        $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'; // 13 columns
                        $insert_values = array_merge($insert_values, [
                            $planIdToUse,
                            trim((string) $pred['race_name']),
                            trim((string) $pred['venue'] ?? ''),
                            trim((string) $pred['ground'] ?? ''),
                            trim((string) $pred['distance'] ?? ''),
                            trim((string) $pred['track_condition'] ?? ''),
                            trim((string) $pred['direction'] ?? ''),
                            trim((string) $pred['speed'] ?? '○'),
                            trim((string) $pred['stamina'] ?? '○'),
                            trim((string) $pred['power'] ?? '○'),
                            trim((string) $pred['guts'] ?? '○'),
                            trim((string) $pred['wit'] ?? '○'),
                            trim((string) $pred['comment'] ?? ''),
                        ]);
                    } else {
                        $log->warning('Skipped inserting prediction with empty race name.', ['plan_id' => $planIdToUse, 'prediction_data' => $pred]);
                    }
                }
                if ($placeholders !== []) {
                    $insert_sql = 'INSERT INTO race_predictions (plan_id, race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment) VALUES ' . implode(', ', $placeholders);
                    $stmt_insert_predictions = $pdo->prepare($insert_sql);
                    $stmt_insert_predictions->execute($insert_values);
                    $log->debug('Inserted new predictions.', ['plan_id' => $planIdToUse, 'count' => count($placeholders)]);
                }
            }

            // Add to activity log for detailed save
            $log_desc = ($action_performed === 'update') ? "Plan updated: $name" : "New plan created: $name";
            $log_icon = ($action_performed === 'update') ? 'bi-arrow-repeat' : 'bi-person-plus';
            $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')->execute([$log_desc, $log_icon]);

            $pdo->commit(); // Commit the transaction
            $response = ['success' => true, 'new_id' => $planIdToUse, 'message' => 'Plan saved successfully!'];
        }
        // Handle Quick Create Plan (from quick_create_plan_modal)
        elseif (isset($_POST['trainee_name']) && isset($_POST['career_stage']) && isset($_POST['traineeClass'])) {
            $action_performed = 'quick_create';
            $log->info('Processing quick create request.');

            // Input Sanitization and Validation
            $trainee_name = trim((string) $_POST['trainee_name']);
            $career_stage = in_array($_POST['career_stage'] ?? '', ['predebut','junior','classic','senior','finale']) ? $_POST['career_stage'] : null;
            $class = in_array($_POST['traineeClass'] ?? '', ['debut','maiden','beginner','bronze','silver','gold','platinum','star','legend']) ? $_POST['traineeClass'] : null;
            $race_name = trim((string) $_POST['race_name'] ?? ''); // Added for quick create, ensure it's handled

            if ($trainee_name === '' || empty($career_stage) || empty($class)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing required fields for quick create.']);
                exit;
            }

            // Use dynamically fetched defaults for quick create
            $mood_id = $default_mood_id;
            $strategy_id = $default_strategy_id;
            $condition_id = $default_condition_id;

            $acquire_skill_default = 'NO';
            $plan_title_default = $trainee_name . "'s New Plan";

            $pdo->beginTransaction(); // Start transaction for quick create

            $sql = 'INSERT INTO plans (name, plan_title, career_stage, class, race_name, mood_id,
                strategy_id, condition_id, acquire_skill, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([
                $trainee_name, $plan_title_default, $career_stage, $class, $race_name, $mood_id,
                $strategy_id, $condition_id, $acquire_skill_default, 'Planning', // Status is always Planning for quick create
            ]);
            $planIdToUse = $pdo->lastInsertId();

            // Create default attributes for the new plan
            $default_attributes_for_new_plan = ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT']; // Keep uppercase for DB consistency
            $stmt_attr = $pdo->prepare('INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES (?, ?, 0, "G")');
            foreach ($default_attributes_for_new_plan as $attr_name) {
                $stmt_attr->execute([$planIdToUse, $attr_name]);
            }

            $pdo->commit(); // Commit quick create transaction

            $log->info('Plan quick-created successfully.', ['new_plan_id' => $planIdToUse, 'trainee_name' => $trainee_name]);

            $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')
                 ->execute(['New plan quick-created: ' . $trainee_name, 'bi-person-plus']);

            $response = ['success' => true, 'new_id' => $planIdToUse, 'message' => 'Plan quick-created successfully!'];
        }
        // Fallback if POST request is received but no known action matches
        else {
            http_response_code(400); // Bad Request
            $response = ['success' => false, 'error' => 'No valid action specified in POST request.'];
            $log->warning('No valid action specified in POST request.', ['request_data' => $_POST]);
        }
    } else {
        // Method Not Allowed for non-POST requests
        http_response_code(405);
        $response = ['success' => false, 'error' => 'Invalid request method. Only POST is allowed.'];
        $log->warning('Invalid request method.', ['method' => $_SERVER['REQUEST_METHOD']]);
    }
} catch (PDOException $e) {
    // Catch database-related errors
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // Rollback transaction on database error
    }
    $log->error("Database error during plan $action_performed operation.", [
        'message' => $e->getMessage(),
        'plan_id' => $plan_id ?? null,
        'trace' => $e->getTraceAsString(),
        'post_data' => $_POST, // Log relevant POST data for debugging
    ]);
    http_response_code(500);
    $response = ['success' => false, 'error' => 'A database error occurred: ' . $e->getMessage()];
} catch (Exception $e) {
    // Catch any other unexpected exceptions
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // Rollback transaction on general exception
    }
    $log->error("General error during plan $action_performed operation.", [
        'message' => $e->getMessage(),
        'plan_id' => $plan_id ?? null,
        'trace' => $e->getTraceAsString(),
        'post_data' => $_POST,
    ]);
    http_response_code(500);
    $response = ['success' => false, 'error' => 'An unexpected error occurred: ' . $e->getMessage()];
}

$stray_output = ob_get_clean();
if (!empty(trim($stray_output))) {
    // If there was any hidden error text, add it to our response
    $response['debug_output'] = $stray_output;
}

echo json_encode($response); // Output the JSON response
exit; // Ensure script terminates after output