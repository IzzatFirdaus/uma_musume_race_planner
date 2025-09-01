<?php

// includes/db.php — Centralized PDO database connector for Uma Musume Planner

require_once __DIR__ . '/env.php';
load_env();

// Fetch environment variables or use defaults
$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'uma_musume_planner';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$debugMode = getenv('APP_DEBUG') === 'true';

try {
    // Return PDO instance configured for security and performance
    return new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => $debugMode ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Log the error (optional: inject logger here)
    error_log('Database Connection Error: ' . $e->getMessage());

    // Send API-friendly JSON error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed. Please check server logs.',
    ]);
    exit;
}
