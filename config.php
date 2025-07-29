<?php

// config.php - Pure configuration, no database connections or setup here.

// Your application already uses getenv() which is loaded via db.php's load_env() function.
// It's generally best to define/load these from .env and then fetch with getenv().
// If you must define them here as constants (less flexible for envs), do so:
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'uma_musume_planner');

// NO DATABASE CONNECTION, TABLE CREATION, DATA SEEDING, OR die()/exit() STATEMENTS.
// These actions should be handled by dedicated setup/migration scripts or within db.php's PDO connection logic.

// Always omit the closing PHP tag in files containing only PHP code.
