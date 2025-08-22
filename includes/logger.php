<?php

// includes/logger.php — Monolog-based logger for Uma Musume Planner

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env.php';
load_env();

// Define log file path: logs/app-YYYY-MM-DD.log
$logFilePath = __DIR__ . '/../logs/app-' . date('Y-m-d') . '.log';

// If Monolog is available, use it; otherwise fall back to a PSR NullLogger
$monologClass = 'Monolog\\Logger';
$formatterClass = 'Monolog\\Formatter\\LineFormatter';
$handlerClass = 'Monolog\\Handler\\StreamHandler';
$psrNullClass = 'Psr\\Log\\NullLogger';

if (class_exists($monologClass)) {
    $debugMode = getenv('APP_DEBUG') === 'true';
    $logLevel = $debugMode ? constant($monologClass . '::DEBUG') : constant($monologClass . '::INFO');

    // Create new logger instance
    $log = new $monologClass('app');

    try {
        // Custom log format
        $formatter = new $formatterClass(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true
        );

        // Set up file handler
        $handler = new $handlerClass($logFilePath, $logLevel);
        if (method_exists($handler, 'setFormatter')) {
            $handler->setFormatter($formatter);
        }
        if (method_exists($log, 'pushHandler')) {
            $log->pushHandler($handler);
        }
    } catch (Exception $e) {
        error_log('Logger setup failed: ' . $e->getMessage());
    }
} else {
    // Provide a minimal PSR-compatible logger so callers still receive a logger object
    if (class_exists($psrNullClass)) {
        $log = new $psrNullClass();
    } else {
        // Last resort: simple anonymous logger that implements the minimal interface
        $log = new class () {
            public function info($msg, array $context = [])
            {
                error_log($msg);
            }
            public function warning($msg, array $context = [])
            {
                error_log($msg);
            }
            public function error($msg, array $context = [])
            {
                error_log($msg);
            }
            public function debug($msg, array $context = [])
            {
                error_log($msg);
            }
            public function pushHandler($h)
            {
            }
        };
    }
}

// Return logger object
return $log;
