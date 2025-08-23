<?php

declare(strict_types=1);

/**
 * config.php
 *
 * Centralized, side-effect-free configuration access.
 * - Reads configuration from environment variables (loaded via includes/env.php elsewhere).
 * - Defines simple constants used across the app.
 * - Does NOT open DB connections or perform I/O.
 *
 * Best practices:
 * - Avoid hardcoding secrets; use .env + getenv().
 * - Keep config read-only; no die()/exit() here.
 * - No closing PHP tag (prevents accidental output).
 */

/**
 * Small helper to fetch env values with sane fallbacks.
 * Mirrors getenv(), but also checks $_ENV/$_SERVER when getenv returns false.
 */
function env(string $key, ?string $default = null): ?string
{
    $val = getenv($key);
    if ($val === false) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }
    if ($val === null || $val === '') {
        return $default;
    }
    return $val;
}

// Application identity and environment flags
if (!defined('APP_NAME')) {
    define('APP_NAME', env('APP_NAME', 'Uma Musume Planner'));
}
if (!defined('APP_ENV')) {
    define('APP_ENV', env('APP_ENV', 'production'));
}
if (!defined('APP_DEBUG')) {
    // Normalize boolean-like values from env
    $debug = strtolower((string) env('APP_DEBUG', 'false')) === 'true';
    define('APP_DEBUG', $debug);
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', env('APP_VERSION', '1.0.0'));
}

// THEME_COLOR: validate a 6-digit hex color, fallback to a safe default.
$appThemeColor = env('APP_THEME_COLOR');
if (!defined('THEME_COLOR')) {
    if ($appThemeColor && preg_match('/^#[a-fA-F0-9]{6}$/', $appThemeColor)) {
        define('THEME_COLOR', $appThemeColor);
    } else {
        define('THEME_COLOR', '#7d2b8b'); // default accent
    }
}
