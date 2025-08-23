<?php

require_once __DIR__ . '/../includes/env.php';
load_env();
$keys = [];
foreach (array_keys($_ENV) as $k) {
    if (stripos($k, 'DB') !== false) {
        $keys[$k] = $_ENV[$k];
    }
}
foreach (array_keys($_SERVER) as $k) {
    if (stripos($k, 'DB') !== false) {
        $keys[$k] = $keys[$k] ?? $_SERVER[$k];
    }
}
if (empty($keys)) {
    echo "<no DB keys found in \\$_ENV/\\_SERVER>" . PHP_EOL;
} else {
    foreach ($keys as $k => $v) {
        if (stripos($k, 'PASS') !== false || stripos($k, 'PASSWORD') !== false) {
            echo $k . ': SET (length=' . strlen((string)$v) . ')' . PHP_EOL;
        } else {
            echo $k . ': ' . ($v === null ? '<null>' : $v) . PHP_EOL;
        }
    }
}
