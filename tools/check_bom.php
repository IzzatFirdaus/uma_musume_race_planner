<?php

declare(strict_types=1);

// tools/check_bom.php
// Scans project PHP and Blade files for UTF-8 BOM and trailing closing tags or leading whitespace.
$root = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$exts = ['php', 'blade.php'];
$offenders = [];
foreach ($rii as $file) {
    if ($file->isDir()) {
        continue;
    }
    $path = $file->getPathname();
    $lower = strtolower($path);
    $match = false;
    foreach ($exts as $ext) {
        if (substr($lower, -strlen($ext)) === $ext) {
            $match = true;
            break;
        }
    }
    if (! $match) {
        continue;
    }
    $contents = @file_get_contents($path);
    if ($contents === false) {
        continue;
    }
    // Check for BOM
    $startsWithBOM = (substr($contents, 0, 3) === "\xEF\xBB\xBF");
    // Check for leading whitespace/newlines before first <!doctype or <html or <?php
    $trimmedLeft = ltrim($contents, "\n\r\t \x0B\x00\xEF\xBB\xBF");
    $leading = substr($contents, 0, strlen($contents) - strlen($trimmedLeft));
    $leadingHasPrintable = preg_match('/[\S]/', $leading) === 1;
    // Check for closing PHP tag at end with trailing whitespace
    $endsWithClosingAndWhitespace = false;
    if (preg_match('/\?>\s+$/', $contents)) {
        $endsWithClosingAndWhitespace = true;
    }

    if ($startsWithBOM || $leadingHasPrintable || $endsWithClosingAndWhitespace) {
        $offenders[] = [
            'path' => $path,
            'bom' => $startsWithBOM,
            'leading' => $leading !== '' ? rawurlencode($leading) : '',
            'endsWithClosingAndWhitespace' => $endsWithClosingAndWhitespace,
        ];
    }
}
if (empty($offenders)) {
    echo "No BOM or leading/trailing-output offenders found.\n";
    exit(0);
}
echo "Found potential offenders:\n";
foreach ($offenders as $o) {
    echo "- {$o['path']}\n";
    echo '    BOM: '.($o['bom'] ? 'YES' : 'NO')."\n";
    echo '    endsWithClosingAndWhitespace: '.($o['endsWithClosingAndWhitespace'] ? 'YES' : 'NO')."\n";
    if ($o['leading'] !== '') {
        $lead = rawurldecode($o['leading']);
        $leadPreview = substr($lead, 0, 100);
        echo '    Leading bytes (preview, may include non-printables): '.bin2hex($leadPreview)."\n";
    }
}
