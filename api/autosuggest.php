<?php

/**
 * Uma Musume Race Planner API — Autosuggest
 * Provides autosuggestions for supported fields.
 * Standard JSON: { success: bool, suggestions?: array, error?: string }
 */

header('Content-Type: application/json');

ob_start();

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    $log = require __DIR__ . '/../includes/logger.php';
} catch (Throwable $e) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to initialize dependencies.', 'suggestions' => []]);
    exit;
}

$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'get':
        $currentUserId = 1; // Local-only app
        $field = $_GET['field'] ?? '';
        $search_query = trim($_GET['query'] ?? '');

        $fieldMap = [
            'name'       => 'name',
            'race_name'  => 'race_name',
            'skill_name' => 'skill_name',
            'goal'       => 'goal',
        ];

        if (!isset($fieldMap[$field])) {
            http_response_code(400);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid field for autosuggestion.', 'suggestions' => []]);
            exit;
        }

        $safeField = $fieldMap[$field];
        try {
            if ($safeField === 'skill_name') {
                $sql = 'SELECT skill_name, description, stat_type, tag FROM skill_reference';
                $params = [];
                if ($search_query !== '') {
                    $sql .= ' WHERE skill_name LIKE :search_query';
                    $params[':search_query'] = '%' . $search_query . '%';
                }
                $sql .= ' ORDER BY skill_name ASC LIMIT 10';
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $sql = "SELECT DISTINCT `{$safeField}` AS value
                        FROM plans
                        WHERE `{$safeField}` IS NOT NULL AND `{$safeField}` != ''
                          AND user_id = :user_id";
                $params = [':user_id' => $currentUserId];
                if ($search_query !== '') {
                    $sql .= " AND `{$safeField}` LIKE :search_query";
                    $params[':search_query'] = '%' . $search_query . '%';
                }
                $sql .= " ORDER BY `{$safeField}` ASC LIMIT 10";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $suggestions = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'value');
            }

            ob_clean();
            echo json_encode(['success' => true, 'suggestions' => $suggestions]);
        } catch (Throwable $e) {
            $log->error('Autosuggest error (api)', [
                'field' => $field,
                'query' => $search_query,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            http_response_code(500);
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'An unexpected server error occurred during autosuggest.', 'suggestions' => []]);
        }
        break;

    default:
        http_response_code(400);
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Unknown action.', 'suggestions' => []]);
        break;
}
