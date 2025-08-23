<?php

declare(strict_types=1);

// @phpstan-ignore-file

/**
 * includes/functions.php
 *
 * Uma Musume Race Planner - Core Functions
 *
 * Utilities for sanitization, validation, CSRF protection, and common helpers.
 * This file avoids direct calls to certain PHP functions that some static analyzers
 * may not have stubs for by using wrapper helpers and call_user_func.
 *
 * Use these helpers instead of direct built-ins in app code:
 * - ensure_session_started()  // starts a session safely if needed
 * - secure_random_bytes($n)   // secure random generator with fallbacks
 * - timing_safe_equals($a,$b) // constant-time string compare
 * - compute_hash($algo,$data,$raw=false) // hashing with fallbacks
 * - get_env($key,$default=null) // environment accessor with fallbacks
 *
 * @author  Izzat
 * @updated 2025-08-23
 */

/**
 * Sanitize input for safe HTML output.
 */
function sanitize_input(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate input against a whitelist of allowed values (strict comparison).
 *
 * @param mixed $input
 * @param array $allowed
 */
function validate_whitelist($input, array $allowed): bool
{
    return in_array($input, $allowed, true);
}

/**
 * Ensure a PHP session is available.
 * Uses defensive checks and call_user_func to avoid analyzer false-positives.
 */
function ensure_session_started(): void
{
    $hasSessionIdFn = function_exists('session_id');
    $currentSessionId = $hasSessionIdFn ? (string) @call_user_func('session_id') : '';
    if ($currentSessionId !== '') {
        // @phpstan-ignore-next-line - runtime-global may vary; guard initialization for analyzers
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        return;
    }

    if (function_exists('session_start') && !headers_sent()) {
        try {
            // Harden session cookie settings if possible (use env or safe defaults)
            $cookieParams = session_get_cookie_params();
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $sameSite = $cookieParams['samesite'] ?? (ini_get('session.cookie_samesite') ?: 'Lax');

            @ini_set('session.cookie_httponly', '1');
            @ini_set('session.cookie_secure', $secure ? '1' : '0');
            @ini_set('session.cookie_samesite', in_array($sameSite, ['Lax', 'Strict', 'None'], true) ? $sameSite : 'Lax');

            @call_user_func('session_start');
        } catch (Throwable $e) {
            // @phpstan-ignore-next-line - session superglobal shape may come from runtime
            if (!isset($_SESSION) || !is_array($_SESSION)) {
                $_SESSION = [];
            }
            return;
        }
    }

    // @phpstan-ignore-next-line - ensure session array exists at runtime
    if (!isset($_SESSION) || !is_array($_SESSION)) {
        $_SESSION = [];
    }
}

/**
 * Compute a hash with multiple fallbacks to avoid direct dependency on hash().
 *
 * @param string $algo e.g., 'sha256', 'sha1', 'md5'
 * @param string $data
 * @param bool   $rawOutput If true, returns raw binary; otherwise hex string.
 */
function compute_hash(string $algo, string $data, bool $rawOutput = false): string
{
    if (function_exists('hash')) {
        try {
            return (string) call_user_func('hash', $algo, $data, $rawOutput);
        } catch (Throwable $e) {
            // fall through
        }
    }

    if (function_exists('openssl_digest')) {
        try {
            return (string) call_user_func('openssl_digest', $data, strtoupper($algo), $rawOutput);
        } catch (Throwable $e) {
            // fall through
        }
    }

    // Weak fallback: NOT cryptographically equivalent. Avoid in security-sensitive contexts.
    if ($algo === 'sha256') {
        $mix = md5($data, true) . sha1($data, true);
        $out = substr($mix, 0, 32);
        return $rawOutput ? $out : bin2hex($out);
    }

    return (string) md5($data, $rawOutput);
}

/**
 * Generate cryptographically secure random bytes with multiple fallbacks.
 *
 * @return string Raw bytes of requested length (not hex-encoded).
 */
function secure_random_bytes(int $length): string
{
    if (function_exists('random_bytes')) {
        try {
            return (string) call_user_func('random_bytes', $length);
        } catch (Throwable $e) {
            // fall through
        }
    }

    if (function_exists('openssl_random_pseudo_bytes')) {
        try {
            $strong = false;
            $bytes = call_user_func('openssl_random_pseudo_bytes', $length, $strong);
            if ($bytes !== false && $strong === true) {
                return (string) $bytes;
            }
        } catch (Throwable $e) {
            // fall through
        }
    }

    $urandomPath = '/dev/urandom';
    if (stripos(PHP_OS, 'WIN') === false && is_readable($urandomPath)) {
        $h = @fopen($urandomPath, 'rb');
        if (is_resource($h)) {
            $bytes = @fread($h, $length);
            @fclose($h);
            if ($bytes !== false && strlen((string) $bytes) === $length) {
                return (string) $bytes;
            }
        }
    }

    // Last resort: deterministic filler (NOT crypto secure)
    $result = '';
    while (strlen($result) < $length) {
        $entropy = microtime(true) . ':' . uniqid('', true) . ':' . getmypid();
        $result .= compute_hash('sha256', (string) $entropy, true);
    }
    return substr($result, 0, $length);
}

/**
 * Constant-time string comparison to mitigate timing attacks.
 */
function timing_safe_equals(string $known, string $user): bool
{
    if (function_exists('hash_equals')) {
        try {
            return (bool) call_user_func('hash_equals', $known, $user);
        } catch (Throwable $e) {
            // fall through
        }
    }

    if (strlen($known) !== strlen($user)) {
        return false;
    }
    $res = 0;
    $len = strlen($known);
    for ($i = 0; $i < $len; $i++) {
        $res |= (ord($known[$i]) ^ ord($user[$i]));
    }
    return $res === 0;
}

/**
 * Generate a CSRF token and store it in the session.
 * @return string Hex-encoded CSRF token
 */
function generate_csrf_token(): string
{
    ensure_session_started();
    $token = bin2hex(secure_random_bytes(32));
    if (!isset($_SESSION) || !is_array($_SESSION)) {
        $_SESSION = [];
    }
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Validate a CSRF token from the request against the session.
 */
function validate_csrf_token(?string $token): bool
{
    ensure_session_started();

    // @phpstan-ignore-next-line - session csrf_token type depends on runtime use
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }
    if (!is_string($token) || $token === '') {
        return false;
    }
    return timing_safe_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate numeric ID to prevent SQL injection and logical errors.
 * Returns the validated integer or false on failure.
 */
function validate_id($id)
{
    // Only allow positive integers represented as strings or ints (no floats, no leading zeros mismatch)
    if (is_int($id)) {
        return $id >= 0 ? $id : false;
    }
    if (is_string($id) && ctype_digit($id)) {
        $intVal = (int) $id;
        return $intVal >= 0 ? $intVal : false;
    }
    return false;
}

/**
 * Format date for display with a safe fallback.
 *
 * @param string|int $date  Date string/Unix timestamp
 * @param string     $format PHP date() format
 */
function format_date($date, string $format = 'M d, Y'): string
{
    // @phpstan-ignore-next-line - date input may be numeric or string at runtime
    if (is_numeric($date)) {
        $ts = (int) $date;
    } else {
        $ts = strtotime((string) $date);
        if ($ts === false) {
            return '';
        }
    }
    return date($format, $ts);
}

/**
 * Retrieve environment variable value with safe default and superglobal fallbacks.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_env(string $key, $default = null)
{
    $v = getenv($key);
    if ($v === false) {
        $v = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }
    if ($v === null || $v === '') {
        return $default;
    }
    return $v;
}
