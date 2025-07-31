<?php

// includes/logger.php — Monolog-based logger for Uma Musume Planner

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env.php';
load_env();

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

$debugMode = getenv('APP_DEBUG') === 'true';
$logLevel = $debugMode ? Logger::DEBUG : Logger::INFO;

// Create new logger instance
$log = new Logger('app');

// Define log file path: logs/app-YYYY-MM-DD.log
$logFilePath = __DIR__ . '/../logs/app-' . date('Y-m-d') . '.log';

try {
    // Custom log format
    $formatter = new LineFormatter(
        "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
        'Y-m-d H:i:s',
        true,
        true
    );

    // Set up file handler
    $handler = new StreamHandler($logFilePath, $logLevel);
    $handler->setFormatter($formatter);
    $log->pushHandler($handler);
} catch (Exception $e) {
    error_log('Logger setup failed: ' . $e->getMessage());
}

// Return logger object
return $log;