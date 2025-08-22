<?php

// Lean PDO connector.
// Usage: $pdo = require __DIR__ . '/includes/db.php';

// Load environment variables if loader is present.
if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
    if (function_exists('load_env')) {
        load_env();
    }
}

// Read configuration from environment with sensible defaults.
$dbHost = getenv('DB_HOST') !== false ? getenv('DB_HOST') : '127.0.0.1';
$dbPort = getenv('DB_PORT') !== false ? getenv('DB_PORT') : '3306';
$dbName = getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: 'uma_musume_planner';
$dbUser = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
} catch (Exception $e) {
    // Keep logging minimal but informative for local debugging.
    $msg = method_exists($e, 'getMessage') ? $e->getMessage() : (string) $e;
    error_log('Database connection failed: ' . $msg);
    // Safe JSON response for consumers
    http_response_code(200);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed. Please check server logs.',
    ]);
    // Ensure no further execution when required
    exit(1);
}
