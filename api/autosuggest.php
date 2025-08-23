<?php

declare(strict_types=1);

/**
 * Uma Musume Race Planner API — Autosuggest
 * Provides autosuggestions for supported fields.
 * Standard JSON: { success: bool, suggestions?: array, error?: string, request_id: string, meta?: object }
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

function get_int(string $key, int $default, int $min, int $max): int
{
    $val = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    if ($val === false || $val === null) {
        return $default;
    }
    return max($min, min($max, $val));
}

function get_string(string $key, string $default = ''): string
{
    $val = filter_input(INPUT_GET, $key, FILTER_UNSAFE_RAW);
    return trim($val ?? $default);
}

require_method('GET');

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    /** @var PDO|null $pdo */
    $log = require __DIR__ . '/../includes/logger.php';
    /** @var \Psr\Log\LoggerInterface|null $log */
} catch (Throwable $e) {
    send_json(500, ['success' => false, 'error' => 'Failed to initialize dependencies.', 'suggestions' => []]);
}

/** @var PDO $pdo */
/** @var \Psr\Log\LoggerInterface $log */

$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        // NOTE: Replace with authenticated user id when auth is introduced
        $currentUserId = 1;

        $field = get_string('field');
        $search_query = get_string('query');
        $limit = get_int('limit', 10, 1, 50);

        $fieldMap = [
            'name'       => 'name',
            'race_name'  => 'race_name',
            'skill_name' => 'skill_name',
            'goal'       => 'goal',
        ];

        if (!isset($fieldMap[$field])) {
            send_json(400, ['success' => false, 'error' => 'Invalid field for autosuggestion.', 'suggestions' => []]);
        }

        $safeField = $fieldMap[$field];

        try {
            if ($safeField === 'skill_name') {
                // Skill reference suggestions (rich objects)
                $sql = 'SELECT skill_name, description, stat_type, tag FROM skill_reference';
                $params = [];
                if ($search_query !== '') {
                    $sql .= ' WHERE skill_name LIKE :search_query';
                    $params[':search_query'] = '%' . $search_query . '%';
                }
                $sql .= ' ORDER BY skill_name ASC LIMIT ' . (int)$limit;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } else {
                // Distinct values from plans (strings)
                $sql = "SELECT DISTINCT `{$safeField}` AS value
                        FROM plans
                        WHERE `{$safeField}` IS NOT NULL AND `{$safeField}` != ''
                          AND user_id = :user_id";
                $params = [':user_id' => $currentUserId];
                if ($search_query !== '') {
                    $sql .= " AND `{$safeField}` LIKE :search_query";
                    $params[':search_query'] = '%' . $search_query . '%';
                }
                $sql .= " ORDER BY `{$safeField}` ASC LIMIT " . (int)$limit;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $suggestions = array_column($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [], 'value');
            }

            send_json(200, [
                'success' => true,
                'suggestions' => $suggestions,
                'meta' => [
                    'limit' => $limit,
                    'count' => is_array($suggestions) ? count($suggestions) : 0,
                ],
            ]);
        } catch (Throwable $e) {
            if (isset($log)) {
                $log->error('Autosuggest error (api)', [
                    'field' => $field,
                    'query' => $search_query,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
            send_json(500, ['success' => false, 'error' => 'An unexpected server error occurred during autosuggest.', 'suggestions' => []]);
        }
        break;

    default:
        send_json(400, ['success' => false, 'error' => 'Unknown action.', 'suggestions' => []]);
}
