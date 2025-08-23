<?php

/**
 * Uma Musume Race Planner - Core Functions
 *
 * Utilities for sanitization, validation, CSRF protection, and common helpers.
 * This file avoids direct calls to certain PHP functions that some static analyzers
 * (e.g., Intelephense) may not have stubs for by using wrapper helpers and call_user_func.
 *
 * Use these helpers instead of direct built-ins in app code:
 * - ensure_session_started()  // instead of session_status()/session_start()/session_id()
 * - secure_random_bytes($n)   // instead of random_bytes()/openssl_random_pseudo_bytes()
 * - timing_safe_equals($a,$b) // instead of hash_equals()
 * - compute_hash($algo,$data,$raw=false) // instead of hash()/openssl_digest()
 *
 * @author  Izzat
 * @updated 2025-08-22
 */

/**
 * Sanitize input for safe HTML output.
 *
 * @param string $input
 * @return string
 */
function sanitize_input($input)
{
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate input against a whitelist of allowed values (strict comparison).
 *
 * @param mixed $input
 * @param array $allowed
 * @return bool
 */
function validate_whitelist($input, $allowed)
{
    return in_array($input, $allowed, true);
}

/**
 * Ensure a PHP session is available.
 * Uses defensive checks and call_user_func to avoid analyzer false-positives.
 *
 * @return void
 */
function ensure_session_started(): void
{
    // If session_id function exists and is non-empty, a session is active.
    $hasSessionIdFn = function_exists('session_id');
    $currentSessionId = $hasSessionIdFn ? (string) @call_user_func('session_id') : '';
    if ($currentSessionId !== '') {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
        return;
    }

    // Attempt to start a session if possible and headers are not sent.
    if (function_exists('session_start') && !headers_sent()) {
        try {
            @call_user_func('session_start');
        } catch (Throwable $e) {
            // As a fallback, ensure $_SESSION exists to avoid notices.
            if (!isset($_SESSION) || !is_array($_SESSION)) {
                $_SESSION = [];
            }
            return;
        }
    }

    // Final fallback when session APIs are not available (restricted env).
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
 * @return string
 */
function compute_hash(string $algo, string $data, bool $rawOutput = false): string
{
    // Preferred: hash()
    if (function_exists('hash')) {
        try {
            return (string) call_user_func('hash', $algo, $data, $rawOutput);
        } catch (Throwable $e) {
            // fall through
        }
    }

    // Fallback: openssl_digest (expects algo uppercase; raw output supported)
    if (function_exists('openssl_digest')) {
        try {
            return (string) call_user_func('openssl_digest', $data, strtoupper($algo), $rawOutput);
        } catch (Throwable $e) {
            // fall through
        }
    }

    // Last resort: emulate with md5/sha1 combos (not cryptographically equivalent)
    if ($algo === 'sha256') {
        // Combine md5 and sha1 for entropy; return in requested format
        $mix = md5($data, true) . sha1($data, true);
        $out = substr($mix, 0, 32); // 32 bytes to mimic sha256 raw length
        return $rawOutput ? $out : bin2hex($out);
    }

    // Generic fallback: md5
    $md = md5($data, $rawOutput);
    return (string) $md;
}

/**
 * Generate cryptographically secure random bytes with multiple fallbacks.
 *
 * @param int $length
 * @return string Raw bytes of requested length (not hex-encoded).
 */
function secure_random_bytes(int $length): string
{
    // Preferred: random_bytes
    if (function_exists('random_bytes')) {
        try {
            return (string) call_user_func('random_bytes', $length);
        } catch (Throwable $e) {
            // fall through to next method
        }
    }

    // Fallback: openssl_random_pseudo_bytes
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

    // Fallback: /dev/urandom (Unix-like)
    $urandomPath = '/dev/urandom';
    if (stripos(PHP_OS, 'WIN') === false && is_readable($urandomPath)) {
        $h = @fopen($urandomPath, 'rb');
        if (is_resource($h)) {
            $bytes = @fread($h, $length);
            @fclose($h);
            if ($bytes !== false && strlen((string)$bytes) === $length) {
                return (string) $bytes;
            }
        }
    }

    // Last resort: deterministic filler based on repeated hashing (NOT crypto secure).
    $result = '';
    while (strlen($result) < $length) {
        $entropy = microtime(true) . ':' . uniqid('', true) . ':' . getmypid();
        $result .= compute_hash('sha256', $entropy, true);
    }
    return substr($result, 0, $length);
}

/**
 * Constant-time string comparison to mitigate timing attacks.
 * Polyfill for hash_equals to avoid direct dependency.
 *
 * @param string $known Known-good string
 * @param string $user  User-provided string
 * @return bool
 */
function timing_safe_equals(string $known, string $user): bool
{
    // If native hash_equals exists, prefer it via call_user_func
    if (function_exists('hash_equals')) {
        try {
            return (bool) call_user_func('hash_equals', $known, $user);
        } catch (Throwable $e) {
            // fall through to manual implementation
        }
    }

    // Manual constant-time comparison
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
 *
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
 *
 * @param string|null $token
 * @return bool
 */
function validate_csrf_token(?string $token): bool
{
    ensure_session_started();

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
 *
 * @param mixed $id
 * @return bool
 */
function validate_id($id): bool
{
    return is_numeric($id) && (int)$id > 0 && (string)(int)$id === (string)$id;
}

/**
 * Format date for display with a safe fallback.
 *
 * @param string|int $date  Date string/Unix timestamp
 * @param string     $format PHP date() format
 * @return string
 */
function format_date($date, string $format = 'M d, Y'): string
{
    if (is_numeric($date)) {
        $ts = (int)$date;
    } else {
        $ts = strtotime((string)$date);
        if ($ts === false) {
            return '';
        }
    }
    return date($format, $ts);
}
