<?php

// scripts/check_images.php
$base = __DIR__ . '/../assets/images/app_bg/';
$files = [
    'uma_musume_race_planner_bg_dark_1028x1536.png',
    'uma_musume_race_planner_bg_dark_1536x1028.png',
    'uma_musume_race_planner_bg_light_1028x1536.png',
    'uma_musume_race_planner_bg_light_1536x1028.png',
];
// Also check the tmp_download if present
$tmp = __DIR__ . '/../tmp_download.png';
if (file_exists($tmp)) {
    $files[] = basename($tmp);
}
foreach ($files as $f) {
    $path = $base . $f;
    echo "File: $f\n";
    if (!file_exists($path)) {
        echo "  MISSING\n\n";
        continue;
    }
    $size = filesize($path);
    $mod = date('c', filemtime($path));
    echo "  Path: $path\n  Size: $size bytes\n  Modified: $mod\n";
    $info = @getimagesize($path);
    if ($info === false) {
        echo "  getimagesize: FAILED\n";
        // Try reading first bytes
        $h = fopen($path, 'rb');
        if ($h) {
            $bytes = fread($h, 64);
            fclose($h);
            $hex = substr(bin2hex($bytes), 0, 200);
            echo "  First bytes (hex prefix): $hex\n";
        }
    } else {
        list($w, $h, $type, $attr) = $info + [null,null,null,null];
        $mime = $info['mime'] ?? 'unknown';
        echo "  getimagesize: OK - {$w}x{$h}, mime={$mime}\n";
    }
    echo "\n";
}
