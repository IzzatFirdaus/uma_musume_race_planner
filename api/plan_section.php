<?php

declare(strict_types=1);

/**
 * Uma Musume Race Planner API — Plan Section (Consolidated)
 * Mirrors root get_plan_section.php to fetch various section data by type.
 * Example:
 *   /api/plan_section.php?type=attributes&id=1
 *   /api/plan_section.php?type=skills&id=1
 *   /api/plan_section.php?type=turns&id=1
 * Response: { success: true, [key]: array, request_id: string }
 */

// Security and caching headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

ob_start();

$REQUEST_ID = bin2hex(random_bytes(8));
header('X-Request-Id: ' . $REQUEST_ID);

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

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        send_json(405, ['success' => false, 'error' => 'Method Not Allowed.']);
    }
}

require_method('GET');

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    $log = require __DIR__ . '/../includes/logger.php';
} catch (Throwable $e) {
    send_json(500, ['success' => false, 'error' => 'Failed to initialize dependencies.']);
}

$plan_id_raw = $_GET['id'] ?? null;
$plan_id = filter_var($plan_id_raw, FILTER_VALIDATE_INT);
$type = $_GET['type'] ?? '';

if (!$plan_id || $plan_id <= 0 || $type === '') {
    send_json(400, ['success' => false, 'error' => 'Missing or invalid parameters.']);
}

// Map section types to queries
$sectionMap = [
    'attributes' => [
        'sql' => 'SELECT attribute_name, value, grade FROM attributes WHERE plan_id = ? ORDER BY id',
        'key' => 'attributes'
    ],
    'skills' => [
        'sql' => 'SELECT r.skill_name, r.description, r.stat_type, r.tag, s.sp_cost, s.acquired, s.tag AS skill_tag, s.notes
                  FROM skills s
                  LEFT JOIN skill_reference r ON s.skill_reference_id = r.id
                  WHERE s.plan_id = ?
                  ORDER BY s.id',
        'key' => 'skills'
    ],
    'distance_grades' => [
        'sql' => 'SELECT distance, grade FROM distance_grades WHERE plan_id = ? ORDER BY id',
        'key' => 'distance_grades'
    ],
    'style_grades' => [
        'sql' => 'SELECT style, grade FROM style_grades WHERE plan_id = ? ORDER BY id',
        'key' => 'style_grades'
    ],
    'terrain_grades' => [
        'sql' => 'SELECT terrain, grade FROM terrain_grades WHERE plan_id = ? ORDER BY id',
        'key' => 'terrain_grades'
    ],
    'goals' => [
        'sql' => 'SELECT goal, result FROM goals WHERE plan_id = ? ORDER BY id',
        'key' => 'goals'
    ],
    'predictions' => [
        'sql' => 'SELECT * FROM race_predictions WHERE plan_id = ? ORDER BY id',
        'key' => 'predictions'
    ],
    'turns' => [
        'sql' => 'SELECT turn_number, speed, stamina, power, guts, wit FROM turns WHERE plan_id = ? ORDER BY turn_number',
        'key' => 'turns'
    ],
];

if (!isset($sectionMap[$type])) {
    send_json(400, ['success' => false, 'error' => 'Invalid type requested.']);
}

try {
    $sql = $sectionMap[$type]['sql'];
    $key = $sectionMap[$type]['key'];
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$plan_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    send_json(200, ['success' => true, $key => $data]);
} catch (Throwable $e) {
    if (isset($log)) {
        $log->error("Failed to fetch plan section: {$type} (api)", [
            'plan_id' => $plan_id,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
    send_json(500, ['success' => false, 'error' => "A database error occurred fetching {$type}."]);
}
