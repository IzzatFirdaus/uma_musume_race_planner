<?php

declare(strict_types=1);

namespace App\Services\Import;

use Illuminate\Support\Collection;

/**
 * Import Adapter Interface
 *
 * Contract for import adapters that handle different file formats.
 * Implements FR-6B.10: Architecture for additional import adapters.
 */
interface ImportAdapterInterface
{
    /**
     * Check if this adapter can handle the given content.
     */
    public function canHandle(string $content, ?string $filename = null): bool;

    /**
     * Parse the content and return structured data.
     *
     * @return array{
     *     plans: Collection,
     *     characters: Collection,
     *     errors: array,
     *     warnings: array,
     *     metadata: array
     * }
     */
    public function parse(string $content): array;

    /**
     * Validate the parsed data without importing.
     * Implements FR-6B.4: Dry-run validation with row-level error reporting.
     *
     * @return array{
     *     valid: bool,
     *     errors: array,
     *     warnings: array,
     *     summary: array
     * }
     */
    public function validate(array $parsedData): array;

    /**
     * Get the format name this adapter handles.
     */
    public function getFormatName(): string;

    /**
     * Get the supported file extensions.
     *
     * @return array<string>
     */
    public function getSupportedExtensions(): array;

    /**
     * Get field mapping for preview display.
     * Implements FR-6B.3: Preview mapping before import.
     *
     * @return array<string, string> Source field => Target field mapping
     */
    public function getFieldMapping(): array;

    /**
     * Get the schema version this adapter expects.
     */
    public function getExpectedSchemaVersion(): ?string;
}
