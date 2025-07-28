<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
// use Rector\PHPUnit\Set\PHPUnitSetList; // Uncomment if you use PHPUnit specific rules

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/.', // Include the root directory
        __DIR__ . '/components', // Include the components directory
    ]);

    // Optional: Exclude specific files or directories from Rector's processing
    $rectorConfig->skip([
        __DIR__ . '/vendor/*', // Exclude vendor directory
        __DIR__ . '/js/*', // Exclude JavaScript files
        __DIR__ . '/css/*', // Exclude CSS files
        __DIR__ . '/*.sql', // Exclude SQL dump file
        // Add specific files from your deprecated list if you *never* want Rector to touch them
        __DIR__ . '/components/deprecated-component.php', // Example
        // If index.php is a simple entry point that doesn't need refactoring:
        // __DIR__ . '/index.php',
    ]);

    // Define the sets of rules you want to apply
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::PHP_81, // Adjust to your target PHP version
        // SetList::PHP_82,
        // SetList::EARLY_RETURN,
        // PHPUnitSetList::PHPUNIT_100, // Example: PHPUnit 10 specific rules
    ]);

    // Adjust for parallel processing if you have a large codebase
    // $rectorConfig->parallel();
};