<?php

declare(strict_types=1);

/**
 * get_skill_reference.php
 *
 * Fetches skill reference data with optional filters.
 * Method: GET
 * Params:
 * - search (string, optional, partial match on skill_name)
 * - stat_type (string, optional, exact)
 * - tag (string, optional, exact)
 * - limit (int, optional) default 100, max 200
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

try {
    $search = trim((string) ($_GET['search'] ?? ''));
    $stat_type = trim((string) ($_GET['stat_type'] ?? ''));
    $tag = trim((string) ($_GET['tag'] ?? ''));
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 100;
    $limit = max(1, min($limit, 200));

    // Cap input sizes to avoid abuse
    if (strlen($search) > 64) {
        $search = substr($search, 0, 64);
    }
    if (strlen($stat_type) > 32) {
        $stat_type = substr($stat_type, 0, 32);
    }
    if (strlen($tag) > 32) {
        $tag = substr($tag, 0, 32);
    }

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = 'skill_name LIKE ?';
        $params[] = '%' . $search . '%';
    }
    if ($stat_type !== '') {
        $where[] = 'stat_type = ?';
        $params[] = $stat_type;
    }
    if ($tag !== '') {
        $where[] = 'tag = ?';
        $params[] = $tag;
    }

    $sql = 'SELECT skill_name, tag, stat_type, description FROM skill_reference';
    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY skill_name ASC LIMIT ?';

    $stmt = $pdo->prepare($sql);
    $i = 1;
    foreach ($params as $p) {
        $stmt->bindValue($i++, $p, PDO::PARAM_STR);
    }
    $stmt->bindValue($i, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $send_json(['success' => true, 'skills' => $skills]);
} catch (Throwable $e) {
    $log->error('Failed to fetch skill reference data', [
        'params' => $_GET,
        'message' => $e->getMessage(),
    ]);
    $send_json(['success' => false, 'error' => 'A database error occurred while fetching skill reference data.'], 500);
}
