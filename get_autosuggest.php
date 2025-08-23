<?php

declare(strict_types=1);

/**
 * get_autosuggest.php
 *
 * Returns dynamic suggestions for input fields (name, race_name, goal, skill_name).
 * Method: GET
 * Params:
 * - field: one of ['name','race_name','goal','skill_name']
 * - query: partial text to search
 */

header('X-Content-Type-Options: nosniff');

$send_json = static function (array $payload, int $code = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET', true, 405);
    $send_json(['success' => false, 'error' => 'Method not allowed.'], 405);
}

try {
    /** @var PDO $pdo */
    $pdo = require __DIR__ . '/includes/db.php';
    $log = require __DIR__ . '/includes/logger.php';
} catch (Throwable $e) {
    $send_json(['success' => false, 'error' => 'Service unavailable.'], 503);
}

// For local-only application, hardcode the user ID. In multi-user, derive from session/auth.
$currentUserId = 1;

$field = (string) ($_GET['field'] ?? '');
$search_query = trim((string) ($_GET['query'] ?? ''));
if (strlen($search_query) > 64) {
    $search_query = substr($search_query, 0, 64);
}

// Whitelist map
$fieldMap = [
    'name'       => 'name',
    'race_name'  => 'race_name',
    'skill_name' => 'skill_name',
    'goal'       => 'goal',
];

// Validate field
if (!isset($fieldMap[$field])) {
    $send_json(['success' => false, 'error' => 'Invalid field for autosuggestion.'], 400);
}

$safeField = $fieldMap[$field];

try {
    if ($safeField === 'skill_name') {
        // Search within global reference
        $sql = 'SELECT skill_name, description, stat_type, tag FROM skill_reference';
        $params = [];
        if ($search_query !== '') {
            $sql .= ' WHERE skill_name LIKE :q';
            $params[':q'] = '%' . $search_query . '%';
        }
        $sql .= ' ORDER BY skill_name ASC LIMIT 10';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $send_json(['success' => true, 'suggestions' => $suggestions]);
    }

    // Plans-owned fields (restrict to current user)
    $sql = "SELECT DISTINCT `{$safeField}` AS value
            FROM plans
            WHERE `{$safeField}` IS NOT NULL AND `{$safeField}` != ''
              AND user_id = :uid";
    $params = [':uid' => $currentUserId];
    if ($search_query !== '') {
        $sql .= " AND `{$safeField}` LIKE :q";
        $params[':q'] = '%' . $search_query . '%';
    }
    $sql .= " ORDER BY `{$safeField}` ASC LIMIT 10";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $suggestions = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value');

    $send_json(['success' => true, 'suggestions' => $suggestions]);
} catch (Throwable $e) {
    $log->error('Autosuggest error', [
        'field' => $field,
        'query' => $search_query,
        'user_id' => $currentUserId,
        'message' => $e->getMessage(),
    ]);
    $send_json(['success' => false, 'error' => 'An unexpected server error occurred during autosuggest. Please try again later.'], 500);
}
