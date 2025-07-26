<?php

// includes/logger.php

require_once __DIR__ . '/../vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
// Create a log channel
$log = new Logger('app');
// Create a formatter
$formatter = new LineFormatter(
    "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
    "Y-m-d H:i:s", // Date format
    true, // Allow inline line breaks
    true  // Ignore empty context/extra
);
// Create a handler that writes to a file
// This will create a new log file each day in the 'logs' directory
$handler = new StreamHandler(__DIR__ . '/../logs/app-' . date('Y-m-d') . '.log', Logger::DEBUG);
$handler->setFormatter($formatter);
// Push the handler to the logger
$log->pushHandler($handler);
return $log;
