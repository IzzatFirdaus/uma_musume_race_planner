<?php

// scripts/db_check.php
// Simple DB connectivity check for local dev. Returns DB_OK on success or DB_FAIL: <message> on failure.

try {
    $pdo = require __DIR__ . '/../includes/db.php';
    echo "DB_OK\n";
    exit(0);
} catch (Throwable $e) {
    // Print the exception message for diagnostics (do not use in public production pages)
    echo "DB_FAIL: " . $e->getMessage() . "\n";
    exit(1);
}
