<?php

/**
 * Uma Musume Race Planner API — Plan
 * Provides plan listing, fetching (with labels), and duplication (with child data).
 * Standard JSON variants:
 *  - list: { success: true, plans: array }
 *  - get:  { success: true, plan: object }
 *  - duplicate: { success: true, new_plan_id: int }
 */

header('Content-Type: application/json');

ob_start();

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    $log = require __DIR__ . '/../includes/logger.php';
    require_once __DIR__ . '/../includes/functions.php';
} catch (Throwable $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to initialize dependencies.']);
    exit;
}

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        try {
            $query = "
                SELECT
                    p.*,
                    m.label AS mood,
                    s.label AS strategy,
                    a.attribute_name,
                    a.value AS attribute_value
                FROM plans p
                LEFT JOIN moods m ON p.mood_id = m.id
                LEFT JOIN strategies s ON p.strategy_id = s.id
                LEFT JOIN attributes a ON p.id = a.plan_id
                WHERE p.deleted_at IS NULL
                ORDER BY
                    CASE p.status
                        WHEN 'Active' THEN 1
                        WHEN 'Planning' THEN 2
                        WHEN 'Draft' THEN 3
                        WHEN 'Finished' THEN 4
                        WHEN 'Abandoned' THEN 5
                        ELSE 6
                    END,
                    p.updated_at DESC
            ";
            $raw = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

            $plans = [];
            foreach ($raw as $row) {
                $id = $row['id'];
                if (!isset($plans[$id])) {
                    $plans[$id] = $row;
                    $plans[$id]['stats'] = [
                        'speed' => 0,
                        'stamina' => 0,
                        'power' => 0,
                        'guts' => 0,
                        'wit' => 0,
                    ];
                    unset($plans[$id]['attribute_name'], $plans[$id]['attribute_value']);
                }
                if (!empty($row['attribute_name'])) {
                    $attr = strtolower((string)$row['attribute_name']);
                    if (isset($plans[$id]['stats'][$attr])) {
                        $plans[$id]['stats'][$attr] = (int)$row['attribute_value'];
                    }
                }
            }
            ob_clean();
            echo json_encode(['success' => true, 'plans' => array_values($plans)]);
        } catch (Throwable $e) {
            $log->error('Failed to list plans (api)', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            http_response_code(500);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'A database error occurred while listing plans.']);
        }
        break;

    case 'get':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id || !is_numeric($id) || $id <= 0) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Missing or invalid plan ID.']);
            break;
        }
        try {
            // Align with fetch_plan_details.php to include labels
            $sql = '
                SELECT 
                    p.*, 
                    m.label AS mood_label, 
                    s.label AS strategy_label
                FROM plans p
                LEFT JOIN moods m ON p.mood_id = m.id
                LEFT JOIN strategies s ON p.strategy_id = s.id
                WHERE p.id = ? AND p.deleted_at IS NULL
                LIMIT 1
            ';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$plan) {
                http_response_code(404);
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Plan not found.']);
                break;
            }
            ob_clean();
            echo json_encode(['success' => true, 'plan' => $plan]);
        } catch (Throwable $e) {
            $log->error('Failed to fetch plan (api)', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            http_response_code(500);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'A database error occurred while fetching plan.']);
        }
        break;

    case 'duplicate':
        $id = isset($_GET['id']) ? sanitize_input($_GET['id']) : null;
        if (!$id || !is_numeric($id) || $id <= 0) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Missing or invalid plan ID.']);
            break;
        }
        try {
            $pdo->beginTransaction();

            // Fetch original plan
            $stmt = $pdo->prepare('SELECT * FROM plans WHERE id = ? AND deleted_at IS NULL LIMIT 1');
            $stmt->execute([$id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$plan) {
                $pdo->rollBack();
                http_response_code(404);
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Plan not found.']);
                break;
            }

            // Prepare target row: set status 'Draft', tweak name, reset timestamps as NOW(), keep other fields
            $fields = array_diff(array_keys($plan), ['id', 'created_at', 'updated_at', 'deleted_at']);
            $columns = implode(',', $fields);
            $placeholders = implode(',', array_fill(0, count($fields), '?'));

            $plan['status'] = 'Draft';
            $plan['name'] = ($plan['name'] ?? 'Unnamed') . ' (Copy)';
            $now = date('Y-m-d H:i:s');
            $plan['created_at'] = $now;
            $plan['updated_at'] = $now;

            $values = [];
            foreach ($fields as $field) {
                $values[] = $plan[$field];
            }

            $insert = $pdo->prepare("INSERT INTO plans ($columns) VALUES ($placeholders)");
            $insert->execute($values);
            $newPlanId = (int)$pdo->lastInsertId();

            // Helper to duplicate simple child tables with (plan_id, ...) schema
            $copyTable = static function (PDO $pdo, string $table, array $columns) use ($id, $newPlanId) {
                $cols = implode(',', $columns);
                $placeholders = implode(',', array_fill(0, count($columns), '?'));
                $selectCols = implode(',', $columns);

                $src = $pdo->prepare("SELECT $selectCols FROM $table WHERE plan_id = ?");
                $src->execute([$id]);
                $rows = $src->fetchAll(PDO::FETCH_ASSOC);

                if (!$rows) {
                    return;
                }

                $insert = $pdo->prepare("INSERT INTO $table (plan_id, $cols) VALUES (?, $placeholders)");
                foreach ($rows as $r) {
                    $vals = [$newPlanId];
                    foreach ($columns as $c) {
                        $vals[] = $r[$c] ?? null;
                    }
                    $insert->execute($vals);
                }
            };

            // Duplicate attributes (include grade, align with export)
            $copyTable($pdo, 'attributes', ['attribute_name', 'value', 'grade']);
            // Duplicate grades and goals
            $copyTable($pdo, 'terrain_grades', ['terrain', 'grade']);
            $copyTable($pdo, 'distance_grades', ['distance', 'grade']);
            $copyTable($pdo, 'style_grades', ['style', 'grade']);
            $copyTable($pdo, 'goals', ['goal', 'result']);
            // Duplicate turns (for progress)
            $copyTable($pdo, 'turns', ['turn_number', 'speed', 'stamina', 'power', 'guts', 'wit']);
            // Duplicate race predictions
            $copyTable($pdo, 'race_predictions', [
                'race_name', 'venue', 'ground', 'distance', 'track_condition',
                'direction', 'speed', 'stamina', 'power', 'guts', 'wit', 'comment'
            ]);
            // Duplicate skills (keep reference ids intact)
            $copySkills = $pdo->prepare('SELECT skill_reference_id, sp_cost, acquired, tag, notes FROM skills WHERE plan_id = ?');
            $copySkills->execute([$id]);
            $skills = $copySkills->fetchAll(PDO::FETCH_ASSOC);
            if ($skills) {
                $insSkills = $pdo->prepare('INSERT INTO skills (plan_id, skill_reference_id, sp_cost, acquired, tag, notes) VALUES (?, ?, ?, ?, ?, ?)');
                foreach ($skills as $s) {
                    $insSkills->execute([
                        $newPlanId,
                        $s['skill_reference_id'],
                        $s['sp_cost'],
                        $s['acquired'],
                        $s['tag'],
                        $s['notes'],
                    ]);
                }
            }

            // Log activity
            $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')
                ->execute(['Plan duplicated: ' . ($plan['name'] ?? 'Unnamed'), 'bi-copy']);

            $pdo->commit();

            ob_clean();
            echo json_encode(['success' => true, 'new_plan_id' => $newPlanId]);
        } catch (Throwable $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $log->error('Failed to duplicate plan (api)', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            http_response_code(500);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'A database error occurred while duplicating plan.']);
        }
        break;

    default:
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        break;
}
