<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'single_quote' => true,
        'array_syntax' => ['syntax' => 'short'],
        // You can add more rules here, for example:
        'no_unused_imports' => true, // Removes unused use statements
        'trailing_comma_in_multiline' => true, // Adds trailing commas in multiline arrays
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            // Scan the entire project directory
            ->in(__DIR__)
            // Exclude folders you don't want to format
            ->exclude('vendor')
    );
