<?php

// includes/logger.php — Monolog-based logger for Uma Musume Planner

require_once __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
    if (function_exists('load_env')) {
        load_env();
    }
}

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

// Define log file path and debug detection
$logFilePath = __DIR__ . '/../logs/app-' . date('Y-m-d') . '.log';
$debugMode = getenv('APP_DEBUG') === 'true';

// Prepare a default logger variable for fallbacks
$log = null;

// Prefer Monolog if available
if (class_exists(MonologLogger::class)) {
    // Determine level constant availability (Monolog v3 introduces Level enum)
    if (class_exists('Monolog\\Level')) {
        // Monolog v3+ - use Level enum
        $levelClass = 'Monolog\\Level';
        $logLevel = $debugMode ? constant($levelClass . '::Debug') : constant($levelClass . '::Info');
    } else {
        // Older Monolog - use integer constants from Monolog\Logger
        $levelClass = MonologLogger::class;
        $logLevel = $debugMode ? constant($levelClass . '::DEBUG') : constant($levelClass . '::INFO');
    }

    try {
        $log = new MonologLogger('app');
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true
        );
        $handler = new StreamHandler($logFilePath, $logLevel);
        if (method_exists($handler, 'setFormatter')) {
            $handler->setFormatter($formatter);
        }
        if (method_exists($log, 'pushHandler')) {
            $log->pushHandler($handler);
        }
    } catch (Exception $e) {
        error_log('Logger setup failed: ' . $e->getMessage());
    }
}

// Fallback: PSR NullLogger or minimal anonymous logger
if ($log === null) {
    if (class_exists('Psr\\Log\\NullLogger')) {
        $log = new Psr\Log\NullLogger();
    } else {
        $log = new class () {
            public function info($msg, array $context = []) { error_log($msg); }
            public function warning($msg, array $context = []) { error_log($msg); }
            public function error($msg, array $context = []) { error_log($msg); }
            public function debug($msg, array $context = []) { error_log($msg); }
            public function pushHandler($h) {}
        };
    }
}

// Return logger object
return $log;
