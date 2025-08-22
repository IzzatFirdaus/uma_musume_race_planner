<?php

// includes/env.php
// Loads environment variables from .env file into PHP's runtime environment.
// Usage: require_once __DIR__ . '/env.php'; load_env();

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

    // Read each line, skip comments and empty lines
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        // Skip full-line comments or lines without '='
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        // Split at first '='
        [$key, $val] = array_map('trim', explode('=', $line, 2));
        // Remove inline comments (a # and anything after) unless inside quotes
        if (strlen($val) > 0) {
            // If value starts and ends with matching quotes, keep inner content
            if ((($val[0] === '"' && substr($val, -1) === '"') || ($val[0] === "'" && substr($val, -1) === "'"))) {
                $val = substr($val, 1, -1);
            } else {
                // Strip inline comment starting with ' #' or '#' preceded by whitespace
                $hashPos = strpos($val, ' #');
                if ($hashPos === false) {
                    $hashPos = strpos($val, '#');
                }
                if ($hashPos !== false) {
                    $val = substr($val, 0, $hashPos);
                }
            }
            $val = trim($val);
        }
        putenv("$key=$val");
    }

    $loaded = true;
}
