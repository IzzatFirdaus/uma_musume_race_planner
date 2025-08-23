<?php

require_once __DIR__ . '/../includes/env.php';
load_env();
$g = getenv('DB_PASS');
if ($g === false) {
    echo "getenv: <false>" . PHP_EOL;
} else {
    echo "getenv: SET (len=" . strlen($g) . ")" . PHP_EOL;
}
echo "_ENV: ";
var_export($_ENV['DB_PASS'] ?? null);
echo PHP_EOL;
echo "_SERVER: ";
var_export($_SERVER['DB_PASS'] ?? null);
echo PHP_EOL;
