<?php

declare(strict_types=1);

/**
 * includes/env.php
 *
 * Loads environment variables from .env file into PHP's runtime environment.
 * - Idempotent: safe to call multiple times
 * - Preserves existing environment variables
 * - Supports quoted values and inline comments
 * - Optionally sets PHP default timezone if TIMEZONE is provided
 *
 * Usage:
 *   require_once __DIR__ . '/env.php';
 *   load_env();
 */

function load_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }

    $envFile = __DIR__ . '/../.env';
    if (!file_exists($envFile)) {
        error_log('.env file not found at ' . $envFile);
        return;
    }

    // Normalize line endings and strip BOM if present
    $contents = file_get_contents($envFile);
    if ($contents === false) {
        error_log('Failed to read .env file at ' . $envFile);
        return;
    }
    // Remove UTF-8 BOM
    if (substr($contents, 0, 3) === "\xEF\xBB\xBF") {
        $contents = substr($contents, 3);
    }
    $lines = preg_split('/\R/u', $contents) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }

        // Split into key and value at the first '='
        [$key, $val] = array_map('trim', explode('=', $line, 2));
        if ($key === '') {
            continue;
        }

        // Handle quoted values
        if ($val !== '') {
            $first = $val[0];
            $last = substr($val, -1);
            $isQuoted = ($first === '"' && $last === '"') || ($first === "'" && $last === "'");
            if ($isQuoted) {
                $val = substr($val, 1, -1);
            } else {
                // Strip inline comments starting with '#' anywhere in the value
                // (e.g. "DB_PASS=   # comment" or "DB_PASS=#comment").
                $hashPos = strpos($val, '#');
                if ($hashPos !== false) {
                    $val = substr($val, 0, $hashPos);
                }
                $val = trim($val);
            }
        }

        // Always apply .env values into the runtime environment so local .env
        // overrides system environment variables when present (useful for dev).
        // If a value is intentionally empty in .env, explicitly unset any
        // existing system-level environment variable so the empty value takes
        // precedence in PHP (getenv will return false / empty and fallbacks work).
        if ($val === '') {
            // Unset system env (portable approach)
            putenv($key);
        } else {
            putenv("$key=$val");
        }
        // Mirror into superglobals for broader compatibility
        $_ENV[$key] = $val;
        $_SERVER[$key] = $val;
    }

    // Optional: set default timezone for consistency across logs and date() calls
    $tz = getenv('TIMEZONE');
    if ($tz && is_string($tz)) {
        @date_default_timezone_set($tz);
    }

    $loaded = true;
}
