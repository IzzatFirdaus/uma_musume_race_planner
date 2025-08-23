<?php

declare(strict_types=1);

/**
 * handle_plan_crud.php
 *
 * Handles Create/Update/Delete for trainee plans and child data.
 * Method: POST
 * - Uses transactions and batch upserts where possible.
 * - Logs actions to activity_log.
 */

header('X-Content-Type-Options: nosniff');

// JSON responder
$respond = static function (array $payload, int $code = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

ob_start(); // Capture stray output (to return under debug key)

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST', true, 405);
    $respond(['success' => false, 'error' => 'Invalid request method. Only POST is allowed.'], 405);
}

try {
    $log = require __DIR__ . '/includes/logger.php';
    /** @var PDO $pdo */
    $pdo = require __DIR__ . '/includes/db.php';

    // Optional CSRF validation: if a token is provided, validate it.
    // Include functions.php if available and validate via validate_csrf_token.
    try {
        $csrfToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        if ($csrfToken !== null) {
            @require_once __DIR__ . '/includes/functions.php';
            if (function_exists('validate_csrf_token')) {
                if (!validate_csrf_token(is_string($csrfToken) ? $csrfToken : null)) {
                    $respond(['success' => false, 'error' => 'Invalid CSRF token.'], 403);
                }
            }
        }
    } catch (Throwable $ignore) {
        // If CSRF utilities unavailable, proceed (maintain backward-compatibility)
    }

    require_once __DIR__ . '/components/trainee_image_handler.php';

    $response = ['success' => false, 'error' => 'An unknown error occurred.'];
    $action_performed = 'unknown';

    // Lookup maps
    $moods_map = $pdo->query('SELECT id, label FROM moods')->fetchAll(PDO::FETCH_KEY_PAIR);
    $strategies_map = $pdo->query('SELECT id, label FROM strategies')->fetchAll(PDO::FETCH_KEY_PAIR);
    $conditions_map = $pdo->query('SELECT id, label FROM conditions')->fetchAll(PDO::FETCH_KEY_PAIR);

    $default_mood_id = array_search('NORMAL', $moods_map, true);
    if ($default_mood_id === false) {
        $default_mood_id = (int) ($pdo->query('SELECT id FROM moods LIMIT 1')->fetchColumn() ?: 1);
    }
    $default_strategy_id = array_search('PACE', $strategies_map, true);
    if ($default_strategy_id === false) {
        $default_strategy_id = (int) ($pdo->query('SELECT id FROM strategies LIMIT 1')->fetchColumn() ?: 1);
    }
    $default_condition_id = array_search('N/A', $conditions_map, true);
    if ($default_condition_id === false) {
        $default_condition_id = (int) ($pdo->query('SELECT id FROM conditions LIMIT 1')->fetchColumn() ?: 1);
    }

    // DELETE
    if (isset($_POST['delete_id'])) {
        $plan_id = (int) $_POST['delete_id'];
        $action_performed = 'delete';
        $pdo->beginTransaction();

        // Fetch current trainee image path
        $stmt_fetch_image = $pdo->prepare('SELECT trainee_image_path FROM plans WHERE id = ?');
        $stmt_fetch_image->execute([$plan_id]);
        $image_to_delete = (string) $stmt_fetch_image->fetchColumn();

        // Safe delete check for file path (prevent traversal)
        $safeDelete = static function (?string $relPath) use ($log): void {
            $relPath = $relPath ? ltrim($relPath, '/\\') : '';
            if ($relPath === '' || str_contains($relPath, '..')) {
                return;
            }
            // Restrict deletes to assets/ directory (adjust if your uploads path differs)
            if (!str_starts_with($relPath, 'assets/')) {
                return;
            }
            $full = realpath(__DIR__ . '/../' . $relPath);
            $assetsBase = realpath(__DIR__ . '/../assets');
            if ($full && $assetsBase && str_starts_with($full, $assetsBase) && is_file($full)) {
                if (@unlink($full)) {
                    $log->info('Deleted trainee image (on plan delete).', ['path' => $relPath]);
                } else {
                    $log->warning('Failed to delete trainee image file.', ['path' => $relPath]);
                }
            }
        };
        if ($image_to_delete !== '') {
            $safeDelete($image_to_delete);
        }

        $stmt = $pdo->prepare('UPDATE plans SET deleted_at = NOW() WHERE id = ?');
        $stmt->execute([$plan_id]);

        if ($stmt->rowCount() > 0) {
            $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')->execute(["Plan soft-deleted: ID {$plan_id}", 'bi-trash']);
            $pdo->commit();
            $log->info('Plan soft-deleted successfully.', ['plan_id' => $plan_id]);
            $response = ['success' => true, 'message' => 'Plan deleted successfully.'];
        } else {
            $pdo->rollBack();
            $log->warning('Attempted to delete non-existent or already deleted plan.', ['plan_id' => $plan_id]);
            $response = ['success' => false, 'error' => 'Plan not found or already deleted.'];
        }
    }
    // CREATE/UPDATE (Detailed form)
    elseif (isset($_POST['modalName'])) {
        $plan_id = isset($_POST['planId']) ? (int) $_POST['planId'] : 0;
        $action_performed = $plan_id > 0 ? 'update' : 'create';

        // Collect inputs (bounded and trimmed)
        $plan_title = trim((string) ($_POST['plan_title'] ?? 'Untitled Plan'));
        $name = trim((string) ($_POST['modalName'] ?? ''));
        $career_stage = $_POST['modalCareerStage'] ?? null;
        $class = $_POST['modalClass'] ?? null;
        $race_name = trim((string) ($_POST['modalRaceName'] ?? ''));
        $turn_before = max(0, min((int) ($_POST['modalTurnBefore'] ?? 0), 999));
        $goal_main = trim((string) ($_POST['modalGoal'] ?? ''));

        $strategy_id = isset($_POST['modalStrategy']) && array_key_exists((int) $_POST['modalStrategy'], $strategies_map) ? (int) $_POST['modalStrategy'] : $default_strategy_id;
        $mood_id = isset($_POST['modalMood']) && array_key_exists((int) $_POST['modalMood'], $moods_map) ? (int) $_POST['modalMood'] : $default_mood_id;
        $condition_id = isset($_POST['modalCondition']) && array_key_exists((int) $_POST['modalCondition'], $conditions_map) ? (int) $_POST['modalCondition'] : $default_condition_id;

        $energy = max(0, min((int) ($_POST['energyRange'] ?? 0), 100));
        $race_day = (isset($_POST['raceDaySwitch']) && $_POST['raceDaySwitch'] === 'on') ? 'yes' : 'no';
        $acquire_skill = (isset($_POST['acquireSkillSwitch']) && $_POST['acquireSkillSwitch'] === 'on') ? 'YES' : 'NO';
        $total_available_skill_points = max(0, (int) ($_POST['skillPoints'] ?? 0));
        $status_options = ['Planning', 'Active', 'Finished', 'Draft', 'Abandoned'];
        $status = in_array($_POST['modalStatus'] ?? '', $status_options, true) ? (string) $_POST['modalStatus'] : 'Planning';
        $time_of_day = trim((string) ($_POST['modalTimeOfDay'] ?? ''));
        $month = trim((string) ($_POST['modalMonth'] ?? ''));
        $source = trim((string) ($_POST['modalSource'] ?? ''));
        $growth_rate_speed = max(-100, min((int) ($_POST['growthRateSpeed'] ?? 0), 100));
        $growth_rate_stamina = max(-100, min((int) ($_POST['growthRateStamina'] ?? 0), 100));
        $growth_rate_power = max(-100, min((int) ($_POST['growthRatePower'] ?? 0), 100));
        $growth_rate_guts = max(-100, min((int) ($_POST['growthRateGuts'] ?? 0), 100));
        $growth_rate_wit = max(-100, min((int) ($_POST['growthRateWit'] ?? 0), 100));

        // Decode child data
        $attributes_data = json_decode((string) ($_POST['attributes'] ?? '[]'), true) ?: [];
        $skills_data = json_decode((string) ($_POST['skills'] ?? '[]'), true) ?: [];
        $predictions_data = json_decode((string) ($_POST['predictions'] ?? '[]'), true) ?: [];
        $goals_data = json_decode((string) ($_POST['goals'] ?? '[]'), true) ?: [];
        $terrain_grades_data = json_decode((string) ($_POST['terrainGrades'] ?? '[]'), true) ?: [];
        $distance_grades_data = json_decode((string) ($_POST['distanceGrades'] ?? '[]'), true) ?: [];
        $style_grades_data = json_decode((string) ($_POST['styleGrades'] ?? '[]'), true) ?: [];
        $turns_data = json_decode((string) ($_POST['turns'] ?? '[]'), true) ?: [];

        $pdo->beginTransaction();
        $planIdToUse = $plan_id;

        // UPDATE
        if ($plan_id > 0) {
            // Get current image path
            $stmt_get_current_image = $pdo->prepare('SELECT trainee_image_path FROM plans WHERE id = ?');
            $stmt_get_current_image->execute([$plan_id]);
            $currentDbImagePath = (string) $stmt_get_current_image->fetchColumn();

            $newImagePath = handleTraineeImageUpload(
                $pdo,
                $plan_id,
                $_FILES['traineeImageUpload'] ?? ['error' => UPLOAD_ERR_NO_FILE],
                $currentDbImagePath,
                $log
            );

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
                $acquire_skill, $total_available_skill_points, $status, $time_of_day,
                $month, $source, $growth_rate_speed, $growth_rate_stamina,
                $growth_rate_power, $growth_rate_guts, $growth_rate_wit,
                $newImagePath, $plan_id,
            ]);
        }
        // CREATE
        else {
            $sql = 'INSERT INTO plans (
                plan_title, name, career_stage, class, race_name, turn_before, goal, strategy_id,
                mood_id, condition_id, energy, race_day, acquire_skill, total_available_skill_points,
                status, time_of_day, month, source, growth_rate_speed, growth_rate_stamina,
                growth_rate_power, growth_rate_guts, growth_rate_wit, trainee_image_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $plan_title, $name, $career_stage, $class, $race_name, $turn_before, $goal_main,
                $strategy_id, $mood_id, $condition_id, $energy, $race_day, $acquire_skill,
                $total_available_skill_points, $status, $time_of_day, $month, $source,
                $growth_rate_speed, $growth_rate_stamina, $growth_rate_power, $growth_rate_guts,
                $growth_rate_wit, null,
            ]);
            $planIdToUse = (int) $pdo->lastInsertId();

            $newImagePath = handleTraineeImageUpload(
                $pdo,
                $planIdToUse,
                $_FILES['traineeImageUpload'] ?? ['error' => UPLOAD_ERR_NO_FILE],
                null,
                $log
            );
            if ($newImagePath !== null) {
                $pdo->prepare('UPDATE plans SET trainee_image_path = ? WHERE id = ?')->execute([$newImagePath, $planIdToUse]);
            }
        }

        // Upsert helper for child tables
        $handleChildDataUpsert = static function (
            PDO $pdo,
            int $planId,
            string $tableName,
            array $incomingData,
            string $identifierColumn,
            array $insertColumns,
            string $updateSetSql,
            $log
        ): void {
            $existingIdentifiers = [];
            $stmt = $pdo->prepare("SELECT `$identifierColumn` FROM `$tableName` WHERE `plan_id` = ?");
            $stmt->execute([$planId]);
            $existingIdentifiers = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $incomingIdentifiers = [];
            $upsertValues = [];
            $upsertPlaceholders = [];

            foreach ($incomingData as $item) {
                if ($tableName === 'attributes' && isset($item['attribute_name'])) {
                    $item['attribute_name'] = strtoupper((string) $item['attribute_name']);
                }
                $identifier_val = trim((string) ($item[$identifierColumn] ?? ''));
                if ($identifier_val === '' || $identifier_val === '0') {
                    $log->warning("Skipping {$tableName} record with empty identifier.", ['plan_id' => $planId, 'item' => $item]);
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

            if ($upsertPlaceholders !== []) {
                $columnsSql = implode('`, `', $insertColumns);
                $upsertSql = "INSERT INTO `$tableName` (`plan_id`, `$columnsSql`) VALUES " . implode(', ', $upsertPlaceholders) . " ON DUPLICATE KEY UPDATE {$updateSetSql}";
                $upsertStmt = $pdo->prepare($upsertSql);
                $upsertStmt->execute($upsertValues);
                $log->debug("UPSERTed {$tableName} records.", ['plan_id' => $planId, 'count' => count($incomingData)]);
            }

            $toDeleteIdentifiers = array_diff($existingIdentifiers, $incomingIdentifiers);
            if ($toDeleteIdentifiers !== []) {
                $placeholders = rtrim(str_repeat('?,', count($toDeleteIdentifiers)), ',');
                $stmt_delete = $pdo->prepare("DELETE FROM `$tableName` WHERE `plan_id` = ? AND `$identifierColumn` IN ({$placeholders})");
                $stmt_delete->execute(array_merge([$planId], $toDeleteIdentifiers));
                $log->info("Deleted old {$tableName} records.", ['plan_id' => $planId, 'identifiers' => $toDeleteIdentifiers]);
            }
        };

        // Child data upserts
        $handleChildDataUpsert($pdo, $planIdToUse, 'attributes', $attributes_data, 'attribute_name', ['attribute_name', 'value', 'grade'], '`value`=VALUES(`value`),`grade`=VALUES(`grade`)', $log);
        $handleChildDataUpsert($pdo, $planIdToUse, 'goals', $goals_data, 'goal', ['goal', 'result'], '`result`=VALUES(`result`)', $log);
        $handleChildDataUpsert($pdo, $planIdToUse, 'terrain_grades', $terrain_grades_data, 'terrain', ['terrain', 'grade'], '`grade`=VALUES(`grade`)', $log);
        $handleChildDataUpsert($pdo, $planIdToUse, 'distance_grades', $distance_grades_data, 'distance', ['distance', 'grade'], '`grade`=VALUES(`grade`)', $log);
        $handleChildDataUpsert($pdo, $planIdToUse, 'style_grades', $style_grades_data, 'style', ['style', 'grade'], '`grade`=VALUES(`grade`)', $log);
        $handleChildDataUpsert($pdo, $planIdToUse, 'turns', $turns_data, 'turn_number', ['turn_number', 'speed', 'stamina', 'power', 'guts', 'wit'], '`speed`=VALUES(`speed`),`stamina`=VALUES(`stamina`),`power`=VALUES(`power`),`guts`=VALUES(`guts`),`wit`=VALUES(`wit`)', $log);

        // Skills: replace all, then insert mapped to reference IDs
        $pdo->prepare('DELETE FROM skills WHERE plan_id = ?')->execute([$planIdToUse]);
        if (!empty($skills_data)) {
            $uniqueNames = [];
            foreach ($skills_data as $skill) {
                $nm = trim((string) ($skill['skill_name'] ?? ''));
                if ($nm !== '' && $nm !== '0') {
                    $uniqueNames[$nm] = true;
                }
            }

            $refMap = [];
            if ($uniqueNames !== []) {
                $placeholdersRef = rtrim(str_repeat('?,', count($uniqueNames)), ',');
                $sqlRef = "SELECT id, skill_name FROM skill_reference WHERE skill_name IN ({$placeholdersRef})";
                $stmtRef = $pdo->prepare($sqlRef);
                $stmtRef->execute(array_keys($uniqueNames));
                while ($row = $stmtRef->fetch(PDO::FETCH_ASSOC)) {
                    $refMap[$row['skill_name']] = (int) $row['id'];
                }

                foreach (array_keys($uniqueNames) as $name) {
                    if (!isset($refMap[$name])) {
                        $insRef = $pdo->prepare('INSERT INTO skill_reference (skill_name, description, stat_type, best_for, tag) VALUES (?, ?, ?, ?, ?)');
                        $insRef->execute([$name, '', '', '', '']);
                        $refMap[$name] = (int) $pdo->lastInsertId();
                        $log->info('Created missing skill_reference entry.', ['skill_name' => $name, 'id' => $refMap[$name]]);
                    }
                }
            }

            $insert_values = [];
            $placeholders = [];
            foreach ($skills_data as $skill) {
                $skillName = trim((string) ($skill['skill_name'] ?? ''));
                if ($skillName === '' || $skillName === '0') {
                    $log->warning('Skipped inserting skill with empty name.', ['plan_id' => $planIdToUse, 'skill_data' => $skill]);
                    continue;
                }
                $refId = $refMap[$skillName] ?? null;
                if ($refId === null) {
                    $log->warning('No skill_reference_id found for skill; skipped.', ['skill_name' => $skillName]);
                    continue;
                }
                $placeholders[] = '(?, ?, ?, ?, ?, ?)';
                $insert_values = array_merge($insert_values, [
                    $planIdToUse,
                    $refId,
                    trim((string) ($skill['sp_cost'] ?? '')),
                    (isset($skill['acquired']) && $skill['acquired'] === 'yes') ? 'yes' : 'no',
                    trim((string) ($skill['tag'] ?? '')),
                    trim((string) ($skill['notes'] ?? '')),
                ]);
            }
            if ($placeholders !== []) {
                $insert_sql = 'INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, tag, notes) VALUES ' . implode(', ', $placeholders);
                $stmt_insert_skills = $pdo->prepare($insert_sql);
                $stmt_insert_skills->execute($insert_values);
            }
        }

        // Race predictions: replace then insert
        $pdo->prepare('DELETE FROM race_predictions WHERE plan_id = ?')->execute([$planIdToUse]);
        if (!empty($predictions_data)) {
            $insert_values = [];
            $placeholders = [];
            foreach ($predictions_data as $pred) {
                $raceName = trim((string) ($pred['race_name'] ?? ''));
                if ($raceName === '' || $raceName === '0') {
                    $log->warning('Skipped inserting prediction with empty race name.', ['plan_id' => $planIdToUse, 'prediction_data' => $pred]);
                    continue;
                }
                $placeholders[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $insert_values = array_merge($insert_values, [
                    $planIdToUse,
                    $raceName,
                    trim((string) ($pred['venue'] ?? '')),
                    trim((string) ($pred['ground'] ?? '')),
                    trim((string) ($pred['distance'] ?? '')),
                    trim((string) ($pred['track_condition'] ?? '')),
                    trim((string) ($pred['direction'] ?? '')),
                    trim((string) ($pred['speed'] ?? '○')),
                    trim((string) ($pred['stamina'] ?? '○')),
                    trim((string) ($pred['power'] ?? '○')),
                    trim((string) ($pred['guts'] ?? '○')),
                    trim((string) ($pred['wit'] ?? '○')),
                    trim((string) ($pred['comment'] ?? '')),
                ]);
            }
            if ($placeholders !== []) {
                $insert_sql = 'INSERT INTO race_predictions (plan_id, race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment) VALUES ' . implode(', ', $placeholders);
                $stmt_insert_predictions = $pdo->prepare($insert_sql);
                $stmt_insert_predictions->execute($insert_values);
            }
        }

        // Activity log
        $log_desc = ($action_performed === 'update') ? "Plan updated: {$name}" : "New plan created: {$name}";
        $log_icon = ($action_performed === 'update') ? 'bi-arrow-repeat' : 'bi-person-plus';
        $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')->execute([$log_desc, $log_icon]);

        $pdo->commit();
        $response = ['success' => true, 'new_id' => $planIdToUse, 'message' => 'Plan saved successfully!'];
    }
    // QUICK CREATE
    elseif (isset($_POST['trainee_name'], $_POST['career_stage'], $_POST['traineeClass'])) {
        $action_performed = 'quick_create';

        $trainee_name = trim((string) $_POST['trainee_name']);
        $career_stage = in_array($_POST['career_stage'] ?? '', ['predebut','junior','classic','senior','finale'], true) ? (string) $_POST['career_stage'] : null;
        $class = in_array($_POST['traineeClass'] ?? '', ['debut','maiden','beginner','bronze','silver','gold','platinum','star','legend'], true) ? (string) $_POST['traineeClass'] : null;
        $race_name = trim((string) ($_POST['race_name'] ?? ''));

        if ($trainee_name === '' || $career_stage === null || $class === null) {
            $respond(['success' => false, 'error' => 'Missing required fields for quick create.'], 400);
        }

        $pdo->beginTransaction();
        $plan_title_default = $trainee_name . "'s New Plan";

        $sql = 'INSERT INTO plans (name, plan_title, career_stage, class, race_name, mood_id, strategy_id, condition_id, acquire_skill, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $pdo->prepare($sql)->execute([
            $trainee_name, $plan_title_default, $career_stage, $class, $race_name,
            $default_mood_id, $default_strategy_id, $default_condition_id, 'NO', 'Planning',
        ]);
        $planIdToUse = (int) $pdo->lastInsertId();

        // Default attributes
        $default_attributes = ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT'];
        $stmt_attr = $pdo->prepare('INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES (?, ?, 0, "G")');
        foreach ($default_attributes as $attr) {
            $stmt_attr->execute([$planIdToUse, $attr]);
        }

        $pdo->commit();
        $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')->execute(['New plan quick-created: ' . $trainee_name, 'bi-person-plus']);

        $response = ['success' => true, 'new_id' => $planIdToUse, 'message' => 'Plan quick-created successfully!'];
    }
    // Invalid POST action
    else {
        $respond(['success' => false, 'error' => 'No valid action specified in POST request.'], 400);
    }

    $stray_output = trim((string) ob_get_clean());
    if ($stray_output !== '') {
        $response['debug_output'] = $stray_output;
    }
    $respond($response);
} catch (Throwable $e) {
    try {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable $ignore) {
    }
    try {
        if (isset($log)) {
            $log->error('Error during plan operation.', [
                'message' => $e->getMessage(),
                'code' => (int) ($e->getCode() ?: 0),
            ]);
        }
    } catch (Throwable $ignore) {
    }
    $respond(['success' => false, 'error' => 'An error occurred.'], 500);
}
