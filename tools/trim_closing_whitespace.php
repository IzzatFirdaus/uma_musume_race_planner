<?php

declare(strict_types=1);

$files = [
    __DIR__.'/../vendor/barryvdh/laravel-ide-helper/resources/views/helper.php',
    __DIR__.'/../vendor/symfony/error-handler/Resources/views/trace.html.php',
];
foreach ($files as $f) {
    if (! file_exists($f)) {
        echo "Missing: {$f}\n";

        continue;
    }
    $c = file_get_contents($f);
    $new = preg_replace('/\?>\s+$/', '?>', $c);
    if ($new !== $c) {
        file_put_contents($f, $new);
        echo "Trimmed trailing whitespace after closing tag: {$f}\n";
    } else {
        echo "No change needed: {$f}\n";
    }
}
