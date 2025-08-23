<?php

declare(strict_types=1);

/**
 * includes/db.php
 *
 * Lean, secure PDO connector with sensible defaults and modern best practices.
 * Usage: $pdo = require __DIR__ . '/includes/db.php';
 *
 * - Reads configuration from environment variables loaded via includes/env.php
 * - Fails fast by throwing a RuntimeException (callers should catch)
 * - Disables emulated prepares for real parameter binding
 * - Uses utf8mb4 by default
 * - Optional MySQL SSL (DB_SSL_CA) and configurable timeout
 */

////////////////////////////
// Environment bootstrap  //
////////////////////////////

$envPath = __DIR__ . '/env.php';
if (file_exists($envPath)) {
    require_once $envPath;
    if (function_exists('load_env')) {
        load_env();
    }
}

/**
 * Get environment variable with fallbacks.
 */
$env = static function (string $key, ?string $default = null): ?string {
    $v = getenv($key);
    if ($v === false) {
        $v = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }
    if ($v === null || $v === '') {
        return $default;
    }
    return $v;
};

////////////////////////////
// Configuration          //
////////////////////////////

$dbHost    = $env('DB_HOST', '127.0.0.1');
$dbPort    = $env('DB_PORT', '3306');
$dbName    = $env('DB_DATABASE') ?: $env('DB_NAME', 'uma_musume_planner');
$dbUser    = $env('DB_USERNAME') ?: $env('DB_USER', 'root');
$dbPass    = $env('DB_PASSWORD') ?: $env('DB_PASS', '');
$dbCharset = $env('DB_CHARSET', 'utf8mb4');
$dbTimeout = (int)($env('DB_TIMEOUT', '5') ?? '5'); // seconds
$dbSslCa   = $env('DB_SSL_CA'); // optional path to CA cert for TLS

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $dbHost,
    $dbPort,
    $dbName,
    $dbCharset
);

////////////////////////////
// Connection (PDO)       //
////////////////////////////

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
        PDO::ATTR_STRINGIFY_FETCHES  => false,
        PDO::ATTR_TIMEOUT            => $dbTimeout,
    ];

    // Security: disable multiple statements if supported
    if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
        $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = false;
    }

    // Optional TLS if CA provided
    if ($dbSslCa && is_readable($dbSslCa) && defined('PDO::MYSQL_ATTR_SSL_CA')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $dbSslCa;
        // Verify server cert if supported (older drivers may ignore)
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
    }

    // Ensure UTF8MB4 at session level (defensive)
    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$dbCharset}";
    }

    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

    return $pdo;
} catch (Throwable $e) {
    // Log minimal details for diagnostics without leaking credentials
    $msg = method_exists($e, 'getMessage') ? $e->getMessage() : (string)$e;
    error_log('[DB] Connection failed: ' . $msg);

    // Do not echo/print JSON here. Throw for callers (API endpoints) to handle.
    throw new RuntimeException('Database connection failed.');
}
