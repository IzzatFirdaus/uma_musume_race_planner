<?php

declare(strict_types=1);

const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE;
function send_json(int $status, array $payload): void
{
    http_response_code($status);
    if (!isset($payload['request_id'])) {
        global $REQUEST_ID;
        $payload['request_id'] = $REQUEST_ID;
    }
    ob_clean();
    echo json_encode($payload, JSON_FLAGS);
    exit;
}
// ...setup code...


// ...existing setup code...


/**
 * Uma Musume Race Planner API — Plan
 * Provides plan listing, fetching (with labels), and duplication (with child data).
 * Standard JSON variants:
 *  - list: { success: true, plans: array, meta?: object }
 *  - get:  { success: true, plan: object }
 *  - duplicate: { success: true, new_plan_id: int }
 */

// Security and caching headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');


/** @var PDO|null $pdo */
$pdo = null;
ob_start();

$REQUEST_ID = bin2hex(random_bytes(8));
header('X-Request-Id: ' . $REQUEST_ID);



function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        send_json(405, ['success' => false, 'error' => 'Method Not Allowed.']);
    }
}

function get_int(string $key, ?int $default = null, ?int $min = null, ?int $max = null): ?int
{
    $val = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    if ($val === false || $val === null) {
        return $default;
    }
    if ($min !== null) {
        $val = max($min, $val);
    }
    if ($max !== null) {
        $val = min($max, $val);
    }
    return $val;
}

function get_string(string $key, string $default = ''): string
{
    $val = filter_input(INPUT_GET, $key, FILTER_UNSAFE_RAW);
    return trim($val ?? $default);
}

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    $log = require __DIR__ . '/../includes/logger.php';
    require_once __DIR__ . '/../includes/functions.php';
} catch (Throwable $e) {
    send_json(500, ['success' => false, 'error' => 'Failed to initialize dependencies.']);
}

if ($pdo === null) {
    throw new RuntimeException('Database not initialized.');
}
/** @var PDO $pdo */
/** @var \Psr\Log\LoggerInterface|null $log */

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        require_method('GET');
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
                $id = (int)$row['id'];
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

            // Optional limit after aggregation to avoid breaking join logic
            $limit = get_int('limit', null, 1, 1000);
            $plansList = array_values($plans);
            if ($limit !== null) {
                $plansList = array_slice($plansList, 0, $limit);
            }

            send_json(200, [
                'success' => true,
                'plans' => $plansList,
                'meta' => [
                    'count' => count($plansList),
                    'total' => count($plans),
                    'limited' => $limit !== null,
                    'limit' => $limit,
                ],
            ]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to list plans (api)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while listing plans.']);
        }
        break;

    case 'get':
        require_method('GET');
        $id = get_int('id', null, 1, PHP_INT_MAX);
        if ($id === null) {
            send_json(400, ['success' => false, 'error' => 'Missing or invalid plan ID.']);
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
                send_json(404, ['success' => false, 'error' => 'Plan not found.']);
            }
            send_json(200, ['success' => true, 'plan' => $plan]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to fetch plan (api)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while fetching plan.']);
        }
        break;

    case 'delete':
        // Soft-delete a plan (POST expected)
        require_method('POST');
        $id = get_int('id', null, 1, PHP_INT_MAX);
        if ($id === null) {
            send_json(400, ['success' => false, 'error' => 'Missing or invalid plan ID.']);
        }
        try {
            $stmt = $pdo->prepare('UPDATE plans SET deleted_at = NOW() WHERE id = ?');
            $stmt->execute([$id]);
            send_json(200, ['success' => true]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to delete plan (api)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'plan_id' => $id,
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while deleting plan.']);
        }
        break;

    case 'update':
        // Update plan fields (POST expected)
        require_method('POST');
        // Read input (support JSON body or form-encoded)
        $input = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $input = $data;
            }
        } else {
            // fall back to $_POST
            $input = $_POST;
        }

        $id = isset($input['id']) ? filter_var($input['id'], FILTER_VALIDATE_INT) : null;
        if (!$id || $id <= 0) {
            send_json(400, ['success' => false, 'error' => 'Missing or invalid plan ID.']);
        }

        // Whitelist of allowed updatable fields
        $allowed = [
            'plan_title', 'name', 'race_name', 'turn_before', 'career_stage', 'class', 'time_of_day', 'month',
            'total_available_skill_points', 'acquire_skill', 'mood_id', 'condition_id', 'strategy_id',
            'energy', 'race_day', 'goal', 'growth_rate_speed', 'growth_rate_stamina', 'growth_rate_power',
            'growth_rate_guts', 'growth_rate_wit', 'status', 'source', 'trainee_image_path'
        ];

        $fields = [];
        $values = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $input)) {
                $fields[] = "`$f` = ?";
                $values[] = $input[$f];
            }
        }

        if (empty($fields)) {
            send_json(400, ['success' => false, 'error' => 'No valid fields provided to update.']);
        }

        $values[] = $id; // for WHERE
        $sql = 'UPDATE plans SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ? AND deleted_at IS NULL';

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            // Notify listeners in-process if needed (frontend listens to planUpdated event client-side)
            send_json(200, ['success' => true]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to update plan (api)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'plan_id' => $id,
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while updating plan.']);
        }
        break;

    case 'create':
        require_method('POST');
        $input = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $input = $data;
            }
        } else {
            $input = $_POST;
        }

        // Whitelist of allowed fields for creation
        $allowed = [
            'plan_title', 'name', 'race_name', 'turn_before', 'career_stage', 'class', 'time_of_day', 'month',
            'total_available_skill_points', 'acquire_skill', 'mood_id', 'condition_id', 'strategy_id',
            'energy', 'race_day', 'goal', 'growth_rate_speed', 'growth_rate_stamina', 'growth_rate_power',
            'growth_rate_guts', 'growth_rate_wit', 'status', 'source', 'trainee_image_path'
        ];
        $fields = [];
        $values = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $input)) {
                $fields[] = "`$f`";
                $values[] = $input[$f];
            }
        }
        if (empty($fields)) {
            send_json(400, ['success' => false, 'error' => 'No valid fields provided to create.']);
        }
        $fields[] = '`created_at`';
        $fields[] = '`updated_at`';
        $values[] = date('Y-m-d H:i:s');
        $values[] = date('Y-m-d H:i:s');
        $sql = 'INSERT INTO plans (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')';
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            $newPlanId = (int)$pdo->lastInsertId();
            send_json(200, ['success' => true, 'id' => $newPlanId]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Failed to create plan (api)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while creating plan.']);
        }
        break;
    case 'duplicate':
        // Prefer POST for mutations; allow GET for backward compatibility but mark deprecated via header.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Deprecation: true'); // Hint: migrate to POST
        }
        $idRaw = $_POST['id'] ?? $_GET['id'] ?? null;
        $id = filter_var($idRaw, FILTER_VALIDATE_INT);
        if (!$id || $id <= 0) {
            send_json(400, ['success' => false, 'error' => 'Missing or invalid plan ID.']);
        }

        try {
            $pdo->beginTransaction();

            // Fetch original plan
            $stmt = $pdo->prepare('SELECT * FROM plans WHERE id = ? AND deleted_at IS NULL LIMIT 1');
            $stmt->execute([$id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$plan) {
                $pdo->rollBack();
                send_json(404, ['success' => false, 'error' => 'Plan not found.']);
            }

            // Prepare target row: set status 'Draft', tweak name, reset timestamps as NOW(), keep other fields (exclude id & deleted_at)
            unset($plan['id'], $plan['deleted_at']);
            $plan['status'] = 'Draft';
            $origName = trim((string)($plan['name'] ?? 'Unnamed'));
            $plan['name'] = ($origName !== '' ? $origName : 'Unnamed') . ' (Copy)';
            $now = date('Y-m-d H:i:s');
            $plan['created_at'] = $now;
            $plan['updated_at'] = $now;

            $fields = array_keys($plan);
            $columns = implode(',', array_map(static fn ($c) => "`$c`", $fields));
            $placeholders = implode(',', array_fill(0, count($fields), '?'));
            $values = array_values($plan);

            $insert = $pdo->prepare("INSERT INTO plans ($columns) VALUES ($placeholders)");
            $insert->execute($values);
            $newPlanId = (int)$pdo->lastInsertId();

            // Helper to duplicate simple child tables with (plan_id, ...) schema
            $copyTable = static function (PDO $pdo, string $table, array $columns, int $srcPlanId, int $dstPlanId): void {
                $cols = implode(',', array_map(static fn ($c) => "`$c`", $columns));
                $placeholders = implode(',', array_fill(0, count($columns), '?'));
                $selectCols = implode(',', array_map(static fn ($c) => "`$c`", $columns));

                $src = $pdo->prepare("SELECT $selectCols FROM `$table` WHERE plan_id = ?");
                $src->execute([$srcPlanId]);
                $rows = $src->fetchAll(PDO::FETCH_ASSOC);

                if (!$rows) {
                    return;
                }

                $insert = $pdo->prepare("INSERT INTO `$table` (plan_id, $cols) VALUES (?, $placeholders)");
                foreach ($rows as $r) {
                    $vals = [$dstPlanId];
                    foreach ($columns as $c) {
                        $vals[] = $r[$c] ?? null;
                    }
                    $insert->execute($vals);
                }
            };

            // Duplicate attributes (include grade, align with export)
            $copyTable($pdo, 'attributes', ['attribute_name', 'value', 'grade'], $id, $newPlanId);
            // Duplicate grades and goals
            $copyTable($pdo, 'terrain_grades', ['terrain', 'grade'], $id, $newPlanId);
            $copyTable($pdo, 'distance_grades', ['distance', 'grade'], $id, $newPlanId);
            $copyTable($pdo, 'style_grades', ['style', 'grade'], $id, $newPlanId);
            $copyTable($pdo, 'goals', ['goal', 'result'], $id, $newPlanId);
            // Duplicate turns (for progress)
            $copyTable($pdo, 'turns', ['turn_number', 'speed', 'stamina', 'power', 'guts', 'wit'], $id, $newPlanId);
            // Duplicate race predictions
            $copyTable($pdo, 'race_predictions', [
                'race_name', 'venue', 'ground', 'distance', 'track_condition',
                'direction', 'speed', 'stamina', 'power', 'guts', 'wit', 'comment'
            ], $id, $newPlanId);

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
            $desc = 'Plan duplicated: ' . ($plan['name'] ?? 'Unnamed');
            $pdo->prepare('INSERT INTO activity_log (description, icon_class) VALUES (?, ?)')
                ->execute([$desc, 'bi-copy']);

            $pdo->commit();

            send_json(200, ['success' => true, 'new_plan_id' => $newPlanId]);
        } catch (Throwable $e) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if (isset($log)) {
                $log->error('Failed to duplicate plan (api)', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'A database error occurred while duplicating plan.']);
        }
        break;

    default:
        send_json(400, ['success' => false, 'error' => 'Unknown action.']);
}
