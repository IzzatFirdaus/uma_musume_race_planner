<?php

// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

// includes/db.php — centralized PDO connector

// This check prevents the function from being redeclared if included multiple times.
if (!function_exists('load_env')) {
    function load_env(): void
    {
        if (!getenv('DB_HOST')) {
            $env = __DIR__ . '/../.env';
            if (file_exists($env)) {
                foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                    if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
                        continue;
                    }
                    [$k, $v] = array_map('trim', explode('=', $line, 2));
                    putenv("$k=$v");
                }
            }
        }
    }
}

load_env();

$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'uma_musume_planner';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

try {
    return new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // In a production environment, you would have a more robust error handling system
    error_log('Database Connection Error: ' . $e->getMessage());
    http_response_code(500);
    // Send a JSON error response as the frontend expects JSON
    echo json_encode(['success' => false, 'error' => 'Database connection failed. Please check server logs.']);
    exit;
}
