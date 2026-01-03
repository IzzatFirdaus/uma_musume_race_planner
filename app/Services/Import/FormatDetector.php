<?php

declare(strict_types=1);

namespace App\Services\Import;

/**
 * Format Detector Service
 *
 * Auto-detects import file formats.
 * Implements FR-6B.2: Upload file → detect legacy format automatically.
 */
class FormatDetector
{
    /**
     * Registered adapters.
     *
     * @var array<ImportAdapterInterface>
     */
    private array $adapters = [];

    /**
     * Register an import adapter.
     */
    public function registerAdapter(ImportAdapterInterface $adapter): self
    {
        $this->adapters[] = $adapter;
        return $this;
    }

    /**
     * Detect the format of the given content.
     *
     * @return array{
     *     detected: bool,
     *     adapter: ?ImportAdapterInterface,
     *     format: ?string,
     *     confidence: string,
     *     alternatives: array
     * }
     */
    public function detect(string $content, ?string $filename = null): array
    {
        $matches = [];

        foreach ($this->adapters as $adapter) {
            if ($adapter->canHandle($content, $filename)) {
                $matches[] = [
                    'adapter' => $adapter,
                    'format' => $adapter->getFormatName(),
                ];
            }
        }

        if (empty($matches)) {
            return [
                'detected' => false,
                'adapter' => null,
                'format' => null,
                'confidence' => 'none',
                'alternatives' => [],
            ];
        }

        // Primary match is the first one
        $primary = $matches[0];
        $alternatives = \array_slice($matches, 1);

        return [
            'detected' => true,
            'adapter' => $primary['adapter'],
            'format' => $primary['format'],
            'confidence' => \count($matches) === 1 ? 'high' : 'medium',
            'alternatives' => \array_map(fn($m) => $m['format'], $alternatives),
        ];
    }

    /**
     * Detect format by file extension.
     */
    public function detectByExtension(string $filename): ?ImportAdapterInterface
    {
        $extension = \strtolower(\pathinfo($filename, PATHINFO_EXTENSION));

        foreach ($this->adapters as $adapter) {
            if (\in_array($extension, $adapter->getSupportedExtensions(), true)) {
                return $adapter;
            }
        }

        return null;
    }

    /**
     * Get all registered adapters.
     *
     * @return array<ImportAdapterInterface>
     */
    public function getAdapters(): array
    {
        return $this->adapters;
    }

    /**
     * Get all supported formats.
     *
     * @return array<string>
     */
    public function getSupportedFormats(): array
    {
        return \array_map(
            fn(ImportAdapterInterface $adapter) => $adapter->getFormatName(),
            $this->adapters
        );
    }

    /**
     * Get all supported extensions.
     *
     * @return array<string>
     */
    public function getSupportedExtensions(): array
    {
        $extensions = [];
        foreach ($this->adapters as $adapter) {
            $extensions = \array_merge($extensions, $adapter->getSupportedExtensions());
        }
        return \array_unique($extensions);
    }

    /**
     * Check if content appears to be JSON.
     */
    public static function isJson(string $content): bool
    {
        $trimmed = \trim($content);
        if (empty($trimmed)) {
            return false;
        }

        $firstChar = $trimmed[0];
        if ($firstChar !== '{' && $firstChar !== '[') {
            return false;
        }

        \json_decode($content);
        return \json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Check if content appears to be CSV.
     */
    public static function isCsv(string $content): bool
    {
        $lines = \explode("\n", \trim($content));
        if (\count($lines) < 2) {
            return false;
        }

        // Check if first line has consistent delimiters
        $firstLine = $lines[0];
        $commaCount = \substr_count($firstLine, ',');
        $tabCount = \substr_count($firstLine, "\t");

        // Must have at least one delimiter
        if ($commaCount === 0 && $tabCount === 0) {
            return false;
        }

        // Check second line has similar structure
        $secondLine = $lines[1] ?? '';
        $secondCommaCount = \substr_count($secondLine, ',');
        $secondTabCount = \substr_count($secondLine, "\t");

        // Delimiter counts should be similar
        return ($commaCount > 0 && \abs($commaCount - $secondCommaCount) <= 1) ||
            ($tabCount > 0 && \abs($tabCount - $secondTabCount) <= 1);
    }
}
