<?php

// config.php - Pure configuration, no database connections or setup here.

// Your application already uses getenv() which is loaded via db.php's load_env() function.
// It's generally best to define/load these from .env and then fetch with getenv().

// Example of fetching environment variables for use in the application.
// Note: DB_HOST, DB_NAME, DB_USER, DB_PASS are primarily consumed by db.php.
// This section is more for demonstrating how other app-level configs would be handled.

// Database credentials are best handled directly in db.php using getenv()
// and should not have empty string fallbacks here for security-critical values.
// The primary fallback logic for DB credentials belongs in db.php.

// For other application-specific configurations:
define('APP_NAME', getenv('APP_NAME') ?: 'Uma Musume Planner');

// CRITICAL IMPROVEMENT 2: Add validation for APP_THEME_COLOR
$appThemeColor = getenv('APP_THEME_COLOR');
if ($appThemeColor && preg_match('/^#[a-f0-9]{6}$/i', $appThemeColor)) {
    // If it's a valid 6-digit hex color, use it.
    define('THEME_COLOR', $appThemeColor);
} else {
    // Fallback to a safe default if not set or invalid.
    define('THEME_COLOR', '#7d2b8b'); // A default primary color for the app
}


// NO DATABASE CONNECTION, TABLE CREATION, DATA SEEDING, OR die()/exit() STATEMENTS.
// These actions should be handled by dedicated setup/migration scripts or within db.php's PDO connection logic.

// Always omit the closing PHP tag in files containing only PHP code.
