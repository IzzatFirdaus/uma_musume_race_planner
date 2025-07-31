<?php

/**
 * get_skill_reference.php
 *
 * API endpoint to fetch skill reference data with optional filters:
 * - 'search': Partial match against skill name
 * - 'stat_type': Exact match on stat_type
 * - 'tag': Exact match on tag
 *
 * Returns:
 * - JSON array of skill objects (name, tag, type, description)
 * - Or error JSON on database failure
 */

header('Content-Type: application/json');

$pdo = require __DIR__ . '/includes/db.php';
$log = require __DIR__ . '/includes/logger.php';

try {
    // Extract and sanitize input parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $stat_type = isset($_GET['stat_type']) ? trim($_GET['stat_type']) : '';
    $tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = 'skill_name LIKE ?';
        $params[] = "%$search%";
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
    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY skill_name';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'skills' => $skills,
    ]);
} catch (PDOException $e) {
    $log->error('Failed to fetch skill reference data', [
        'params' => $_GET,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'A database error occurred while fetching skill reference data.'
    ]);
}