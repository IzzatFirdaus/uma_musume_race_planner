<?php

// phpstan-globals.php
// Provide simple typed globals for PHPStan analysis to reduce "variable might not be defined" notices.

/** @var PDO|null $pdo */
$pdo = $pdo ?? null;

/** @var \Psr\Log\LoggerInterface|null $log */
$log = $log ?? null;

// Generic globals often used by scripts
/** @var string|null $REQUEST_ID */
$REQUEST_ID = $REQUEST_ID ?? null;
