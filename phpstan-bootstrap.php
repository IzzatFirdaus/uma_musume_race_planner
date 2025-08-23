<?php

// phpstan-bootstrap.php
// Lightweight bootstrap for PHPStan static analysis.
// Provides a safe `$pdo` variable and loads vendor autoload.

// Load autoloader if present
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Ensure PDO is defined and provide a harmless instance for static analysis.
// Use sqlite in-memory if available; otherwise provide a typed null fallback.
if (!isset($pdo)) {
    try {
        if (class_exists('PDO')) {
            // Try sqlite in-memory (common and safe); if not available, this may throw.
            $pdo = @new PDO('sqlite::memory:');
            // Mark type for static analysis
            /** @var PDO $pdo */
        } else {
            // PDO not available - provide typed null for analysis
            /** @var PDO|null $pdo */
            $pdo = null;
        }
    } catch (Throwable $e) {
        // Fallback to a typed null for PHPStan so variable is defined.
        /** @var PDO|null $pdo */
        $pdo = null;
    }
}

// If packages require environment variables, ensure minimal safe defaults
if (!function_exists('load_env')) {
    function load_env(): void
    {
    }
}

// Provide typed globals for PHPStan analysis (stubs)
/** @var PDO|null $pdo */
$pdo = $pdo ?? null;

/** @var \Psr\Log\LoggerInterface|null $log */
$log = $log ?? null;

// Analysis-only helper: send_json() should be treated as terminating by PHPStan.
if (!function_exists('send_json')) {
    /**
     * Send JSON response. Analysis-only stub (does not exit here) to avoid dead-code reports.
     *
     * @param int $status
     * @param array $payload
     * @return void
     */
    function send_json(int $status, array $payload): void
    {
        // stub for static analysis
    }
}
