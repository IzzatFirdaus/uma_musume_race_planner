<?php

ob_start();

// handle_plan_crud.php
// Handles CRUD (Create, Read, Update, Delete) for trainee plans and their child data.
// Uses PDO transactions and includes helper for upserting child data.
// Improved error handling and more documentation comments.

// Set JSON response type
header('Content-Type: application/json');

require_once __DIR__ . '/includes/logger.php'; // Logging utility
$pdo = require __DIR__ . '/includes/db.php'; // PDO DB connection
$log = $log ?? (require __DIR__ . '/includes/logger.php'); // Ensure logger is available

require_once __DIR__ . '/components/trainee_image_handler.php'; // Trainee image upload handler

$response = ['success' => false, 'error' => 'An unknown error occurred.'];
$action_performed = 'unknown'; // For logging context

try {
    // Fetch lookups for moods, strategies, conditions, and their fallback IDs
    $moods_map = $pdo->query('SELECT id, label FROM moods')->fetchAll(PDO::FETCH_KEY_PAIR);
    $strategies_map = $pdo->query('SELECT id, label FROM strategies')->fetchAll(PDO::FETCH_KEY_PAIR);
    $conditions_map = $pdo->query('SELECT id, label FROM conditions')->fetchAll(PDO::FETCH_KEY_PAIR);

    // Fallback IDs for missing lookup values
    $default_mood_id = array_search('NORMAL', $moods_map) ?: ($pdo->query('SELECT id FROM moods LIMIT 1')->fetchColumn() ?: 1);
    $default_strategy_id = array_search('PACE', $strategies_map) ?: ($pdo->query('SELECT id FROM strategies LIMIT 1')->fetchColumn() ?: 1);
    $default_condition_id = array_search('N/A', $conditions_map) ?: ($pdo->query('SELECT id FROM conditions LIMIT 1')->fetchColumn() ?: 1);

    // --- MAIN POST HANDLING ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // DELETE
        if (isset($_POST['delete_id'])) {
            $plan_id = (int)$_POST['delete_id'];
            $action_performed = 'delete';
            $pdo->beginTransaction();

            // Delete trainee image if present
            $stmt_fetch_image = $pdo->prepare('SELECT trainee_image_path FROM plans WHERE id = ?');
            $stmt_fetch_image->execute([$plan_id]);
            $image_to_delete = $stmt_fetch_image->fetchColumn();

            if ($image_to_delete && file_exists(__DIR__ . '/../' . $image_to_delete)) {
                if (unlink(__DIR__ . '/../' . $image_to_delete)) {
                    $log->info("Deleted trainee image during plan deletion for plan ID {$plan_id}: {$image_to_delete}");
                } else {
                    $log->error("Failed to delete trainee image during plan deletion for plan ID {$plan_id}: {$image_to_delete}");
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
        // DETAILED PLAN CREATE/UPDATE (Modal or Inline Form)
        elseif (isset($_POST['modalName'])) {
            $plan_id = isset($_POST['planId']) ? (int)$_POST['planId'] : 0;
            $action_performed = $plan_id > 0 ? 'update' : 'create';

            $log->info('Processing detailed plan submission.', ['plan_id' => $plan_id ?: 'new']);

            // --- Collect and Validate Plan Data ---
            $plan_title = trim($_POST['plan_title'] ?? 'Untitled Plan');
            $name = trim($_POST['modalName'] ?? '');
            $career_stage = $_POST['modalCareerStage'] ?? null;
            $class = $_POST['modalClass'] ?? null;
            $race_name = trim($_POST['modalRaceName'] ?? '');
            $turn_before = max(0, min((int)($_POST['modalTurnBefore'] ?? 0), 999));
            $goal_main = trim($_POST['modalGoal'] ?? '');

            // Validate FK IDs
            $strategy_id = isset($_POST['modalStrategy']) && array_key_exists((int)$_POST['modalStrategy'], $strategies_map) ? (int)$_POST['modalStrategy'] : $default_strategy_id;
            $mood_id = isset($_POST['modalMood']) && array_key_exists((int)$_POST['modalMood'], $moods_map) ? (int)$_POST['modalMood'] : $default_mood_id;
            $condition_id = isset($_POST['modalCondition']) && array_key_exists((int)$_POST['modalCondition'], $conditions_map) ? (int)$_POST['modalCondition'] : $default_condition_id;

            $energy = max(0, min((int)($_POST['energyRange'] ?? 0), 100));
            $race_day = (isset($_POST['raceDaySwitch']) && $_POST['raceDaySwitch'] === 'on') ? 'yes' : 'no';
            $acquire_skill = (isset($_POST['acquireSkillSwitch']) && $_POST['acquireSkillSwitch'] === 'on') ? 'YES' : 'NO';
            $total_available_skill_points = max(0, (int)($_POST['skillPoints'] ?? 0));
            $status_options = ['Planning', 'Active', 'Finished', 'Draft', 'Abandoned'];
            $status = in_array($_POST['modalStatus'] ?? '', $status_options) ? $_POST['modalStatus'] : 'Planning';
            $time_of_day = trim($_POST['modalTimeOfDay'] ?? '');
            $month = trim($_POST['modalMonth'] ?? '');
            $source = trim($_POST['modalSource'] ?? '');
            $growth_rate_speed = max(-100, min((int)($_POST['growthRateSpeed'] ?? 0), 100));
            $growth_rate_stamina = max(-100, min((int)($_POST['growthRateStamina'] ?? 0), 100));
            $growth_rate_power = max(-100, min((int)($_POST['growthRatePower'] ?? 0), 100));
            $growth_rate_guts = max(-100, min((int)($_POST['growthRateGuts'] ?? 0), 100));
            $growth_rate_wit = max(-100, min((int)($_POST['growthRateWit'] ?? 0), 100));

            // Decode related data JSON (with robust empty fallback)
            $attributes_data = json_decode($_POST['attributes'] ?? '[]', true) ?: [];
            $skills_data = json_decode($_POST['skills'] ?? '[]', true) ?: [];
            $predictions_data = json_decode($_POST['predictions'] ?? '[]', true) ?: [];
            $goals_data = json_decode($_POST['goals'] ?? '[]', true) ?: [];
            $terrain_grades_data = json_decode($_POST['terrainGrades'] ?? '[]', true) ?: [];
            $distance_grades_data = json_decode($_POST['distanceGrades'] ?? '[]', true) ?: [];
            $style_grades_data = json_decode($_POST['styleGrades'] ?? '[]', true) ?: [];
            $turns_data = json_decode($_POST['turns'] ?? '[]', true) ?: [];

            // Image upload handling variables
            $oldImagePath = trim($_POST['existingTraineeImagePath'] ?? '');
            $clearImageFlag = isset($_POST['clear_trainee_image']) && $_POST['clear_trainee_image'] === '1';

            $pdo->beginTransaction(); // Begin DB transaction

            $planIdToUse = $plan_id;

            // --- PLAN UPDATE ---
            if ($plan_id > 0) {
                // Get current image path from DB for robust deletion
                $stmt_get_current_image = $pdo->prepare('SELECT trainee_image_path FROM plans WHERE id = ?');
                $stmt_get_current_image->execute([$plan_id]);
                $currentDbImagePath = $stmt_get_current_image->fetchColumn();

                // Call image upload handler
                $newImagePath = handleTraineeImageUpload(
                    $pdo,
                    $plan_id,
                    $_FILES['traineeImageUpload'] ?? ['error' => UPLOAD_ERR_NO_FILE],
                    $currentDbImagePath,
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
                    $newImagePath, // trainee_image_path
                    $plan_id,
                ]);
            }
            // --- PLAN CREATE ---
            else {
                $log->info('Creating new plan.');
                $sql = 'INSERT INTO plans (
                    plan_title, name, career_stage, class, race_name, turn_before, goal, strategy_id,
                    mood_id, condition_id, energy, race_day, acquire_skill, total_available_skill_points,
                    status, time_of_day, month, source, growth_rate_speed, growth_rate_stamina,
                    growth_rate_power, growth_rate_guts, growth_rate_wit, trainee_image_path
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $plan_title, $name, $career_stage, $class, $race_name, $turn_before,
                    $goal_main, $strategy_id, $mood_id, $condition_id, $energy, $race_day,
                    $acquire_skill, $total_available_skill_points, $status, $time_of_day, $month, $source,
                    $growth_rate_speed, $growth_rate_stamina, $growth_rate_power,
                    $growth_rate_guts, $growth_rate_wit,
                    null, // trainee_image_path initially null
                ]);
                $planIdToUse = $pdo->lastInsertId();

                // Image upload for new plan
                $newImagePath = handleTraineeImageUpload(
                    $pdo,
                    (int)$planIdToUse,
                    $_FILES['traineeImageUpload'] ?? ['error' => UPLOAD_ERR_NO_FILE],
                    null,
                    $log
                );
                if ($newImagePath !== null) {
                    $stmt_update_image = $pdo->prepare('UPDATE plans SET trainee_image_path = ? WHERE id = ?');
                    $stmt_update_image->execute([$newImagePath, $planIdToUse]);
                }
            }

            // === Helper for upserting child tables ===
            /**
             * Upserts and deletes child data for a plan.
             * Uses UNIQUE KEY on (plan_id, $identifierColumn) for upsert.
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
                        // For attributes, ensure uppercase for DB consistency
                        if ($tableName === 'attributes' && isset($item['attribute_name'])) {
                            $item['attribute_name'] = strtoupper($item['attribute_name']);
                        }
                        $identifier_val = trim($item[$identifierColumn] ?? '');
                        if ($identifier_val === '' || $identifier_val === '0') {
                            $log->warning("Skipping $tableName record with empty identifier value.", ['plan_id' => $planId, 'item' => $item, 'identifier_column' => $identifierColumn]);
                            continue;
                        }
                        $incomingIdentifiers[] = $identifier_val;

                        $current_row_values = [$planId];
                        foreach ($insertColumns as $col) {
                            $current_row_values[] = $item[$col] ?? null;
                        }
                        $upsertValues = array_merge($upsertValues, $current_row_values);
                        $upsertPlaceholders[] = '(' . rtrim(str_repeat('?,', count($current_row_values)), ',') . ')';
                    }

                    // Batch upsert
                    if ($upsertPlaceholders !== []) {
                        $columnsSql = implode('`, `', $insertColumns);
                        $upsertSql = "INSERT INTO `$tableName` (`plan_id`, `$columnsSql`) VALUES " . implode(', ', $upsertPlaceholders) . " ON DUPLICATE KEY UPDATE $updateSetSql";
                        $upsertStmt = $pdo->prepare($upsertSql);
                        $upsertStmt->execute($upsertValues);
                        $log->debug("UPSERTed $tableName records.", ['plan_id' => $planId, 'count' => count($incomingData)]);
                    }
                }

                // Delete records not in incoming data
                $toDeleteIdentifiers = array_diff($existingIdentifiers, $incomingIdentifiers);
                if ($toDeleteIdentifiers !== []) {
                    $placeholders = rtrim(str_repeat('?,', count($toDeleteIdentifiers)), ',');
                    $stmt_delete = $pdo->prepare("DELETE FROM `$tableName` WHERE `plan_id` = ? AND `$identifierColumn` IN ($placeholders)");
                    $stmt_delete->execute(array_merge([$planId], $toDeleteIdentifiers));
                    $log->info("Deleted old $tableName records.", ['plan_id' => $planId, 'identifiers' => $toDeleteIdentifiers]);
                }
            }

            // --- Process Child Data ---
            handleChildDataUpsert($pdo, $planIdToUse, 'attributes', $attributes_data, 'attribute_name', ['attribute_name', 'value', 'grade'], '`value`=VALUES(`value`),`grade`=VALUES(`grade`)', $log);
            handleChildDataUpsert($pdo, $planIdToUse, 'goals', $goals_data, 'goal', ['goal', 'result'], '`result`=VALUES(`result`)', $log);
            handleChildDataUpsert($pdo, $planIdToUse, 'terrain_grades', $terrain_grades_data, 'terrain', ['terrain', 'grade'], '`grade`=VALUES(`grade`)', $log);
            handleChildDataUpsert($pdo, $planIdToUse, 'distance_grades', $distance_grades_data, 'distance', ['distance', 'grade'], '`grade`=VALUES(`grade`)', $log);
            handleChildDataUpsert($pdo, $planIdToUse, 'style_grades', $style_grades_data, 'style', ['style', 'grade'], '`grade`=VALUES(`grade`)', $log);
            handleChildDataUpsert(
                $pdo,
                $planIdToUse,
                'turns',
                $turns_data,
                'turn_number',
                ['turn_number', 'speed', 'stamina', 'power', 'guts', 'wit'],
                '`speed`=VALUES(`speed`),`stamina`=VALUES(`stamina`),`power`=VALUES(`power`),`guts`=VALUES(`guts`),`wit`=VALUES(`wit`)',
                $log
            );

            // --- Skills & Race Predictions ---
            $stmt_delete_skills = $pdo->prepare('DELETE FROM skills WHERE plan_id = ?');
            $stmt_delete_skills->execute([$planIdToUse]);
            $log->info('Cleared old skills for re-insertion.', ['plan_id' => $planIdToUse]);

            // Insert skills but map skill_name -> skill_reference_id (create reference row if missing)
            if (!empty($skills_data)) {
                // Collect unique skill names from incoming payload
                $uniqueNames = [];
                foreach ($skills_data as $skill) {
                    $nm = trim((string)($skill['skill_name'] ?? ''));
                    if ($nm === '' || $nm === '0') {
                        continue;
                    }
                    $uniqueNames[$nm] = true;
                }

                $refMap = [];
                if ($uniqueNames !== []) {
                    // Find existing skill_reference ids for the names
                    $placeholdersRef = rtrim(str_repeat('?,', count($uniqueNames)), ',');
                    $sqlRef = "SELECT id, skill_name FROM skill_reference WHERE skill_name IN ($placeholdersRef)";
                    $stmtRef = $pdo->prepare($sqlRef);
                    $stmtRef->execute(array_keys($uniqueNames));
                    while ($row = $stmtRef->fetch(PDO::FETCH_ASSOC)) {
                        $refMap[$row['skill_name']] = $row['id'];
                    }

                    // Create missing skill_reference rows (minimal) and cache their ids
                    foreach (array_keys($uniqueNames) as $name) {
                        if (!isset($refMap[$name])) {
                            $insRef = $pdo->prepare('INSERT INTO skill_reference (skill_name, description, stat_type, best_for, tag) VALUES (?, ?, ?, ?, ?)');
                            $insRef->execute([$name, '', '', '', '']);
                            $refMap[$name] = $pdo->lastInsertId();
                            $log->info('Created missing skill_reference entry.', ['skill_name' => $name, 'id' => $refMap[$name]]);
                        }
                    }
                }

                // Prepare batch insert into skills using skill_reference_id
                $insert_values = [];
                $placeholders = [];
                foreach ($skills_data as $skill) {
                    $skillName = trim((string)($skill['skill_name'] ?? ''));
                    if ($skillName === '' || $skillName === '0') {
                        $log->warning('Skipped inserting skill with empty name.', ['plan_id' => $planIdToUse, 'skill_data' => $skill]);
                        continue;
                    }
                    $refId = $refMap[$skillName] ?? null;
                    // if refId still null, skip to avoid DB errors
                    if ($refId === null) {
                        $log->warning('No skill_reference_id found for skill; skipped.', ['skill_name' => $skillName]);
                        continue;
                    }
                    $placeholders[] = '(?, ?, ?, ?, ?, ?)';
                    $insert_values = array_merge($insert_values, [
                        $planIdToUse,
                        $refId,
                        trim((string)($skill['sp_cost'] ?? '')),
                        (isset($skill['acquired']) && $skill['acquired'] === 'yes' ? 'yes' : 'no'),
                        trim((string)($skill['tag'] ?? '')),
                        trim((string)($skill['notes'] ?? '')),
                    ]);
                }

                if ($placeholders !== []) {
                    $insert_sql = 'INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, tag, notes) VALUES ' . implode(', ', $placeholders);
                    $stmt_insert_skills = $pdo->prepare($insert_sql);
                    $stmt_insert_skills->execute($insert_values);
                    $log->debug('Inserted new skills (by reference).', ['plan_id' => $planIdToUse, 'count' => count($placeholders)]);
                }
            }

            $stmt_delete_predictions = $pdo->prepare('DELETE FROM race_predictions WHERE plan_id = ?');
            $stmt_delete_predictions->execute([$planIdToUse]);
            $log->info('Cleared old predictions for re-insertion.', ['plan_id' => $planIdToUse]);

            if ($predictions_data !== []) {
                $insert_values = [];
                $placeholders = [];
                foreach ($predictions_data as $pred) {
                    if (!in_array(trim((string)$pred['race_name']), ['', '0'], true)) {
                        $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                        $insert_values = array_merge($insert_values, [
                            $planIdToUse,
                            trim((string)$pred['race_name']),
                            trim((string)$pred['venue'] ?? ''),
                            trim((string)$pred['ground'] ?? ''),
                            trim((string)$pred['distance'] ?? ''),
                            trim((string)$pred['track_condition'] ?? ''),
                            trim((string)$pred['direction'] ?? ''),
                            trim((string)$pred['speed'] ?? '○'),
                            trim((string)$pred['stamina'] ?? '○'),
                            trim((string)$pred['power'] ?? '○'),
                            trim((string)$pred['guts'] ?? '○'),
                            trim((string)$pred['wit'] ?? '○'),
                            trim((string)$pred['comment'] ?? ''),
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

            // Add to activity log
            $log_desc = ($action_performed === 'update') ? "Plan updated: $name" : "New plan created: $name";
            $log_icon = ($action_performed === 'update') ? 'bi-arrow-repeat' : 'bi-person-plus';
            $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')->execute([$log_desc, $log_icon]);

            $pdo->commit();
            $response = ['success' => true, 'new_id' => $planIdToUse, 'message' => 'Plan saved successfully!'];
        }
        // QUICK CREATE PLAN (from quick_create_plan_modal)
        elseif (isset($_POST['trainee_name']) && isset($_POST['career_stage']) && isset($_POST['traineeClass'])) {
            $action_performed = 'quick_create';
            $log->info('Processing quick create request.');

            $trainee_name = trim((string)$_POST['trainee_name']);
            $career_stage = in_array($_POST['career_stage'] ?? '', ['predebut','junior','classic','senior','finale']) ? $_POST['career_stage'] : null;
            $class = in_array($_POST['traineeClass'] ?? '', ['debut','maiden','beginner','bronze','silver','gold','platinum','star','legend']) ? $_POST['traineeClass'] : null;
            $race_name = trim((string)$_POST['race_name'] ?? '');

            if ($trainee_name === '' || empty($career_stage) || empty($class)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing required fields for quick create.']);
                exit;
            }

            $mood_id = $default_mood_id;
            $strategy_id = $default_strategy_id;
            $condition_id = $default_condition_id;
            $acquire_skill_default = 'NO';
            $plan_title_default = $trainee_name . "'s New Plan";

            $pdo->beginTransaction();

            $sql = 'INSERT INTO plans (name, plan_title, career_stage, class, race_name, mood_id,
                strategy_id, condition_id, acquire_skill, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([
                $trainee_name, $plan_title_default, $career_stage, $class, $race_name, $mood_id,
                $strategy_id, $condition_id, $acquire_skill_default, 'Planning',
            ]);
            $planIdToUse = $pdo->lastInsertId();

            // Create default attributes for new plan
            $default_attributes_for_new_plan = ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT'];
            $stmt_attr = $pdo->prepare('INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES (?, ?, 0, "G")');
            foreach ($default_attributes_for_new_plan as $attr_name) {
                $stmt_attr->execute([$planIdToUse, $attr_name]);
            }

            $pdo->commit();
            $log->info('Plan quick-created successfully.', ['new_plan_id' => $planIdToUse, 'trainee_name' => $trainee_name]);
            $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')
                 ->execute(['New plan quick-created: ' . $trainee_name, 'bi-person-plus']);

            $response = ['success' => true, 'new_id' => $planIdToUse, 'message' => 'Plan quick-created successfully!'];
        }
        // Invalid POST action
        else {
            http_response_code(400);
            $response = ['success' => false, 'error' => 'No valid action specified in POST request.'];
            $log->warning('No valid action specified in POST request.', ['request_data' => $_POST]);
        }
    } else {
        // Only POST is allowed
        http_response_code(405);
        $response = ['success' => false, 'error' => 'Invalid request method. Only POST is allowed.'];
        $log->warning('Invalid request method.', ['method' => $_SERVER['REQUEST_METHOD']]);
    }
} catch (Exception $e) {
    // Safe, single-catch for any exception type. Guard method calls for strict static analysis.
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $errMsg = method_exists($e, 'getMessage') ? $e->getMessage() : (string)$e;
    $errCode = method_exists($e, 'getCode') ? $e->getCode() : 0;
    $log->error("Error during plan $action_performed operation.", [
        'message' => $errMsg,
        'plan_id' => isset($plan_id) ? $plan_id : null,
        'code' => $errCode,
    ]);
    http_response_code(500);
    $response = ['success' => false, 'error' => 'An error occurred.'];
}

$stray_output = ob_get_clean();
if (!empty(trim($stray_output))) {
    $response['debug_output'] = $stray_output;
}

echo json_encode($response);
exit;
