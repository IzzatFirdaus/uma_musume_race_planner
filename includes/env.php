<?php

// includes/env.php — Loads .env variables into PHP environment safely and once.

/**
 * Loads environment variables from .env file into PHP's runtime.
 * Avoids re-loading if already present.
 */
function load_env(): void
{
    static $loaded = false;
    if ($loaded || getenv('APP_VERSION')) {
        return;
    }

    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        error_log('.env file not found');
        return;
    }

    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || strpos($line, '=') === false) {
            continue;
        }
        [$key, $val] = array_map('trim', explode('=', $line, 2));
        putenv("$key=$val");
    }

    $loaded = true;
}