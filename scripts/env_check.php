<?php

// scripts/env_check.php
// Inspect effective DB-related environment variables as seen by PHP (masks password length)
require_once __DIR__ . '/../includes/env.php';
load_env();
$keys = ['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASS','DB_USERNAME','DB_PASSWORD'];
$out = [];
foreach ($keys as $k) {
    $v = getenv($k);
    if ($v === false) {
        $v = $_ENV[$k] ?? $_SERVER[$k] ?? null;
    }
    if ($v === null) {
        $out[$k] = null;
        continue;
    }
    if (stripos($k, 'PASS') !== false || stripos($k, 'PASSWORD') !== false) {
        $out[$k] = 'SET (length=' . strlen($v) . ')';
    } else {
        $out[$k] = $v;
    }
}
foreach ($out as $k => $v) {
    echo $k . ': ' . (is_null($v) ? '<not set>' : $v) . PHP_EOL;
}

// Also print the PDO DSN that would be used (masking):
$dbHost = $out['DB_HOST'] ?? '127.0.0.1';
$dbPort = $out['DB_PORT'] ?? '3306';
$dbName = $out['DB_NAME'] ?? 'uma_musume_planner';
echo "Effective DSN: mysql:host={$dbHost};port={$dbPort};dbname={$dbName}" . PHP_EOL;
