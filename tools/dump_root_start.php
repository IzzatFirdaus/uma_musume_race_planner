<?php

declare(strict_types=1);

$url = 'http://127.0.0.1:8000/';
$c = @file_get_contents($url);
if ($c === false) {
    echo "ERROR: could not fetch {$url}\n";
    exit(1);
}
$bytes = substr($c, 0, 64);
echo 'First 64 bytes (hex): '.bin2hex($bytes).PHP_EOL;
echo "---FIRST 400 CHARS---\n".substr($c, 0, 400).PHP_EOL;
