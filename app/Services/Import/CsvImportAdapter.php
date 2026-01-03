<?php

declare(strict_types=1);

namespace App\Services\Import;

use Illuminate\Support\Collection;

/**
 * CSV Import Adapter
 *
 * Handles CSV format imports (secondary format).
 * Implements FR-6B.9: Support CSV import as secondary format.
 */
class CsvImportAdapter implements ImportAdapterInterface
{
    /**
     * Field mapping from CSV headers to target fields.
     */
    private const FIELD_MAPPING = [
        // Plan fields (case-insensitive matching)
        'plan title' => 'plan_title',
        'title' => 'plan_title',
        'character name' => 'name',
        'character' => 'name',
        'name' => 'name',
        'career stage' => 'career_stage',
        'year' => 'career_stage',
        'class' => 'class',
        'uma class' => 'class',
        'status' => 'status',
        'scenario' => 'scenario',
        'current turn' => 'turn_before',
        'turn' => 'turn_before',
        'race name' => 'race_name',
        'race' => 'race_name',
        'sp available' => 'total_available_skill_points',
        'sp balance' => 'total_available_skill_points',
        'sp' => 'total_available_skill_points',
        'stamina %' => 'stamina_percentage',
        'stamina percentage' => 'stamina_percentage',
        'stamina' => 'stamina_percentage',
        'mood' => 'mood',
        'condition' => 'condition',
        'strategy' => 'strategy',
        'created at' => 'created_at',
        'created' => 'created_at',
    ];

    /**
     * Detected delimiter.
     */
    private string $delimiter = ',';

    /**
     * {@inheritdoc}
     */
    public function canHandle(string $content, ?string $filename = null): bool
    {
        // Check extension first
        if ($filename !== null) {
            $ext = \strtolower(\pathinfo($filename, PATHINFO_EXTENSION));
            if (!\in_array($ext, ['csv', 'tsv', 'txt'], true)) {
                return false;
            }
        }

        return FormatDetector::isCsv($content);
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $content): array
    {
        $errors = [];
        $warnings = [];
        $plans = new Collection();
        $characters = new Collection();

        // Detect delimiter
        $this->delimiter = $this->detectDelimiter($content);

        // Remove BOM if present
        $content = $this->removeBom($content);

        // Parse CSV
        $lines = \explode("\n", $content);
        if (\count($lines) < 2) {
            return [
                'plans' => $plans,
                'characters' => $characters,
                'errors' => [['row' => 0, 'message' => 'CSV must have at least a header row and one data row']],
                'warnings' => [],
                'metadata' => ['source_format' => 'csv'],
            ];
        }

        // Parse header
        $headers = $this->parseCsvLine($lines[0]);
        $headerMap = $this->mapHeaders($headers);

        if (empty($headerMap)) {
            $warnings[] = [
                'row' => 1,
                'message' => 'No recognized headers found. Using positional mapping.',
            ];
        }

        // Parse data rows
        for ($i = 1; $i < \count($lines); $i++) {
            $line = \trim($lines[$i]);
            if (empty($line)) {
                continue;
            }

            $values = $this->parseCsvLine($line);
            $parsed = $this->parseRow($values, $headerMap, $headers, $i + 1);

            if ($parsed['plan'] !== null) {
                $plans->push($parsed['plan']);
            }
            $errors = \array_merge($errors, $parsed['errors']);
            $warnings = \array_merge($warnings, $parsed['warnings']);
        }

        return [
            'plans' => $plans,
            'characters' => $characters,
            'errors' => $errors,
            'warnings' => $warnings,
            'metadata' => [
                'source_format' => 'csv',
                'delimiter' => $this->delimiter,
                'row_count' => \count($lines) - 1,
            ],
        ];
    }

    /**
     * Parse a single CSV row.
     */
    private function parseRow(array $values, array $headerMap, array $headers, int $rowNumber): array
    {
        $errors = [];
        $warnings = [];

        $plan = [
            'plan_title' => null,
            'name' => null,
            'career_stage' => null,
            'class' => null,
            'status' => null,
            'scenario' => 'URA',
            'turn_before' => null,
            'race_name' => null,
            'total_available_skill_points' => null,
            'stamina_percentage' => null,
            'attributes' => [],
            'skills' => [],
            'goals' => [],
            'race_predictions' => [],
            'stat_progress' => [],
            'snapshots' => [],
        ];

        // Map values using header mapping
        foreach ($headerMap as $index => $targetField) {
            if (isset($values[$index])) {
                $value = \trim($values[$index]);
                if ($value !== '') {
                    $plan[$targetField] = $this->castValue($targetField, $value);
                }
            }
        }

        // Validate required fields
        if (empty($plan['plan_title']) && empty($plan['name'])) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'title/name',
                'message' => 'Row must have a title or character name',
            ];
        }

        return [
            'plan' => empty($errors) ? $plan : null,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Map CSV headers to target fields.
     *
     * @return array<int, string> Index => target field name
     */
    private function mapHeaders(array $headers): array
    {
        $map = [];

        foreach ($headers as $index => $header) {
            $normalized = \strtolower(\trim($header));

            if (isset(self::FIELD_MAPPING[$normalized])) {
                $map[$index] = self::FIELD_MAPPING[$normalized];
            }
        }

        return $map;
    }

    /**
     * Cast value to appropriate type based on field.
     */
    private function castValue(string $field, string $value): mixed
    {
        return match ($field) {
            'turn_before', 'total_available_skill_points' => \is_numeric($value) ? (int) $value : $value,
            'stamina_percentage' => \is_numeric($value) ? (float) $value : $value,
            default => $value,
        };
    }

    /**
     * Detect the delimiter used in the CSV.
     */
    private function detectDelimiter(string $content): string
    {
        $firstLine = \strtok($content, "\n");
        if ($firstLine === false) {
            return ',';
        }

        $commaCount = \substr_count($firstLine, ',');
        $tabCount = \substr_count($firstLine, "\t");
        $semicolonCount = \substr_count($firstLine, ';');

        if ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            return "\t";
        }
        if ($semicolonCount > $commaCount) {
            return ';';
        }

        return ',';
    }

    /**
     * Parse a single CSV line respecting quotes.
     *
     * @return array<string>
     */
    private function parseCsvLine(string $line): array
    {
        $result = [];
        $current = '';
        $inQuotes = false;
        $length = \strlen($line);

        for ($i = 0; $i < $length; $i++) {
            $char = $line[$i];

            if ($char === '"') {
                if ($inQuotes && isset($line[$i + 1]) && $line[$i + 1] === '"') {
                    // Escaped quote
                    $current .= '"';
                    $i++;
                } else {
                    $inQuotes = !$inQuotes;
                }
            } elseif ($char === $this->delimiter && !$inQuotes) {
                $result[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }

        $result[] = $current;

        return $result;
    }

    /**
     * Remove UTF-8 BOM from content.
     */
    private function removeBom(string $content): string
    {
        $bom = "\xEF\xBB\xBF";
        if (\str_starts_with($content, $bom)) {
            return \substr($content, 3);
        }
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $parsedData): array
    {
        $errors = [];
        $warnings = [];
        $validCount = 0;
        $invalidCount = 0;

        foreach ($parsedData['plans'] as $index => $plan) {
            $planErrors = $this->validatePlan($plan, $index + 1);
            if (empty($planErrors)) {
                $validCount++;
            } else {
                $invalidCount++;
                $errors = \array_merge($errors, $planErrors);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => \array_merge($parsedData['warnings'] ?? [], $warnings),
            'summary' => [
                'total' => $parsedData['plans']->count(),
                'valid' => $validCount,
                'invalid' => $invalidCount,
            ],
        ];
    }

    /**
     * Validate a single plan.
     */
    private function validatePlan(array $plan, int $rowNumber): array
    {
        $errors = [];

        // Required fields
        if (empty($plan['plan_title']) && empty($plan['name'])) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'title/name',
                'message' => 'Plan must have a title or character name',
            ];
        }

        // Validate turn number
        if (isset($plan['turn_before']) && !\is_numeric($plan['turn_before'])) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'turn_before',
                'message' => 'Turn must be a number',
            ];
        }

        // Validate SP
        if (isset($plan['total_available_skill_points']) && !\is_numeric($plan['total_available_skill_points'])) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'total_available_skill_points',
                'message' => 'SP must be a number',
            ];
        }

        // Validate stamina percentage
        if (isset($plan['stamina_percentage'])) {
            $stamina = $plan['stamina_percentage'];
            if (!\is_numeric($stamina) || $stamina < 0 || $stamina > 100) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => 'stamina_percentage',
                    'message' => 'Stamina percentage must be between 0 and 100',
                ];
            }
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatName(): string
    {
        return 'CSV (Comma-Separated Values)';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedExtensions(): array
    {
        return ['csv', 'tsv', 'txt'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldMapping(): array
    {
        return self::FIELD_MAPPING;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpectedSchemaVersion(): ?string
    {
        return null; // CSV doesn't have schema versioning
    }
}
