<?php

declare(strict_types=1);

// @phpstan-ignore-file

/**
 * includes/logger.php
 *
 * Monolog-based logger for Uma Musume Planner with safe fallbacks.
 * - Rotating daily logs when available, else single daily file
 * - Honors APP_DEBUG and LOG_LEVEL env vars
 * - Ensures logs directory exists
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env.php';
load_env();

// Resolve log directory (default to ../logs)
$defaultLogDir = realpath(__DIR__ . '/../logs') ?: (__DIR__ . '/../logs');
$logDir = rtrim(getenv('LOG_PATH') ?: $defaultLogDir, DIRECTORY_SEPARATOR);

// Ensure directory exists
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFilePath = $logDir . DIRECTORY_SEPARATOR . 'app-' . date('Y-m-d') . '.log';

// Class names
$monologClass     = 'Monolog\\Logger';
$formatterClass   = 'Monolog\\Formatter\\LineFormatter';
$streamHandler    = 'Monolog\\Handler\\StreamHandler';
$rotateHandler    = 'Monolog\\Handler\\RotatingFileHandler';
$psrNullClass     = 'Psr\\Log\\NullLogger';

// Determine level: LOG_LEVEL overrides APP_DEBUG
$levelMap = [
    'debug'     => 'DEBUG',
    'info'      => 'INFO',
    'notice'    => 'NOTICE',
    'warning'   => 'WARNING',
    'error'     => 'ERROR',
    'critical'  => 'CRITICAL',
    'alert'     => 'ALERT',
    'emergency' => 'EMERGENCY',
];
$logLevelName = strtolower((string) (getenv('LOG_LEVEL') ?: (getenv('APP_DEBUG') === 'true' ? 'debug' : 'info')));
$logLevelConst = $levelMap[$logLevelName] ?? 'INFO';

if (class_exists($monologClass)) {
    /** @var Monolog\Logger $log */
    $log = new $monologClass('app');
    try {
        $levelConstValue = constant($monologClass . '::' . $logLevelConst);

        // Custom log format with context and extra
        $formatter = new $formatterClass(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            true,
            true
        );

        // Prefer rotating handler if available
        if (class_exists($rotateHandler)) {
            $handler = new $rotateHandler($logDir . DIRECTORY_SEPARATOR . 'app.log', 14, $levelConstValue); // keep 14 files
        } else {
            $handler = new $streamHandler($logFilePath, $levelConstValue);
        }

        // @phpstan-ignore-next-line - dynamic handler may vary by installed Monolog version
        if (method_exists($handler, 'setFormatter')) {
            $handler->setFormatter($formatter);
        }
        // @phpstan-ignore-next-line - method presence depends on runtime type
        if (method_exists($log, 'pushHandler')) {
            $log->pushHandler($handler);
        }
    } catch (Throwable $e) {
        error_log('Logger setup failed: ' . $e->getMessage());
    }
} else {
    // Minimal PSR-compatible logger fallback
    if (class_exists($psrNullClass)) {
        $log = new $psrNullClass();
    } else {
        $log = new class () {
            public function info($msg, array $context = []): void
            {
                error_log($msg . ' ' . json_encode($context));
            }
            public function warning($msg, array $context = []): void
            {
                error_log($msg . ' ' . json_encode($context));
            }
            public function error($msg, array $context = []): void
            {
                error_log($msg . ' ' . json_encode($context));
            }
            public function debug($msg, array $context = []): void
            {
                error_log($msg . ' ' . json_encode($context));
            }
            public function pushHandler($h): void
            {
            }
        };
    }
}

return $log;
