<?php

declare(strict_types=1);

namespace App\Services\Import;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * JSON Import Adapter
 *
 * Handles JSON format imports (primary format from uma-run-tracker).
 * Implements FR-6B.8: Support JSON import as primary format.
 */
class JsonImportAdapter implements ImportAdapterInterface
{
    /**
     * Expected schema version.
     */
    private const EXPECTED_SCHEMA_VERSION = '1.0.0';

    /**
     * Field mapping from source to target.
     */
    private const FIELD_MAPPING = [
        // Plan fields
        'plan_title' => 'plan_title',
        'title' => 'plan_title',
        'name' => 'name',
        'character_name' => 'name',
        'career_stage' => 'career_stage',
        'class' => 'class',
        'uma_class' => 'class',
        'status' => 'status',
        'scenario' => 'scenario',
        'current_turn' => 'turn_before',
        'turn' => 'turn_before',
        'turn_before' => 'turn_before',
        'race_name' => 'race_name',
        'current_race' => 'race_name',
        'total_sp_available' => 'total_available_skill_points',
        'sp_available' => 'total_available_skill_points',
        'sp_balance' => 'total_available_skill_points',
        'stamina_percentage' => 'stamina_percentage',
        'stamina_pct' => 'stamina_percentage',
        'energy' => 'stamina_percentage',
        // Growth rates
        'growth_rate_speed' => 'growth_rate_speed',
        'growth_speed' => 'growth_rate_speed',
        'growth_rate_stamina' => 'growth_rate_stamina',
        'growth_stamina' => 'growth_rate_stamina',
        'growth_rate_power' => 'growth_rate_power',
        'growth_power' => 'growth_rate_power',
        'growth_rate_guts' => 'growth_rate_guts',
        'growth_guts' => 'growth_rate_guts',
        'growth_rate_wit' => 'growth_rate_wit',
        'growth_wit' => 'growth_rate_wit',
    ];

    /**
     * {@inheritdoc}
     */
    public function canHandle(string $content, ?string $filename = null): bool
    {
        // Check extension first
        if ($filename !== null) {
            $ext = \strtolower(\pathinfo($filename, PATHINFO_EXTENSION));
            if ($ext !== 'json') {
                return false;
            }
        }

        // Validate JSON structure
        if (!FormatDetector::isJson($content)) {
            return false;
        }

        $data = \json_decode($content, true);
        if ($data === null) {
            return false;
        }

        // Check for expected structure
        return isset($data['plan']) ||
            isset($data['plans']) ||
            isset($data['career_run']) ||
            isset($data['schema_version']);
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

        $data = \json_decode($content, true);
        if ($data === null) {
            return [
                'plans' => $plans,
                'characters' => $characters,
                'errors' => [['row' => 0, 'message' => 'Invalid JSON: ' . \json_last_error_msg()]],
                'warnings' => [],
                'metadata' => [],
            ];
        }

        // Extract metadata
        $metadata = [
            'schema_version' => $data['schema_version'] ?? null,
            'exported_at' => $data['exported_at'] ?? null,
            'source_format' => 'json',
        ];

        // Check schema version
        if (isset($data['schema_version']) && $data['schema_version'] !== self::EXPECTED_SCHEMA_VERSION) {
            $warnings[] = [
                'row' => 0,
                'message' => "Schema version mismatch: expected " . self::EXPECTED_SCHEMA_VERSION . ", got {$data['schema_version']}",
            ];
        }

        // Parse single plan
        if (isset($data['plan'])) {
            $parsed = $this->parsePlan($data, 1);
            if ($parsed['plan'] !== null) {
                $plans->push($parsed['plan']);
            }
            $errors = \array_merge($errors, $parsed['errors']);
            $warnings = \array_merge($warnings, $parsed['warnings']);
        }

        // Parse multiple plans
        if (isset($data['plans']) && \is_array($data['plans'])) {
            foreach ($data['plans'] as $index => $planData) {
                $parsed = $this->parsePlan($planData, $index + 1);
                if ($parsed['plan'] !== null) {
                    $plans->push($parsed['plan']);
                }
                $errors = \array_merge($errors, $parsed['errors']);
                $warnings = \array_merge($warnings, $parsed['warnings']);
            }
        }

        return [
            'plans' => $plans,
            'characters' => $characters,
            'errors' => $errors,
            'warnings' => $warnings,
            'metadata' => $metadata,
        ];
    }

    /**
     * Parse a single plan from JSON data.
     */
    private function parsePlan(array $data, int $rowNumber): array
    {
        $errors = [];
        $warnings = [];

        $planData = $data['plan'] ?? $data;

        // Map fields
        $plan = [
            'plan_title' => $this->extractField($planData, ['title', 'plan_title']),
            'name' => $this->extractField($planData, ['name', 'character_name']),
            'career_stage' => $this->extractField($planData, ['career_stage', 'year']),
            'class' => $this->extractField($planData, ['class', 'uma_class']),
            'status' => $this->extractField($planData, ['status']),
            'scenario' => $this->extractField($planData, ['scenario']) ?? 'URA',
            'turn_before' => $this->extractField($planData, ['current_turn', 'turn', 'turn_before']),
            'race_name' => $this->extractField($planData, ['race_name', 'current_race']),
            'total_available_skill_points' => $this->extractField($planData, ['total_sp_available', 'sp_available', 'sp_balance']),
            'stamina_percentage' => $this->extractField($planData, ['stamina_percentage', 'stamina_pct', 'energy']),
            'growth_rate_speed' => $this->extractField($planData, ['growth_rates.speed', 'growth_rate_speed', 'growth_speed']),
            'growth_rate_stamina' => $this->extractField($planData, ['growth_rates.stamina', 'growth_rate_stamina', 'growth_stamina']),
            'growth_rate_power' => $this->extractField($planData, ['growth_rates.power', 'growth_rate_power', 'growth_power']),
            'growth_rate_guts' => $this->extractField($planData, ['growth_rates.guts', 'growth_rate_guts', 'growth_guts']),
            'growth_rate_wit' => $this->extractField($planData, ['growth_rates.wit', 'growth_rate_wit', 'growth_wit']),
            'created_at' => $this->extractField($planData, ['created_at']),
            'updated_at' => $this->extractField($planData, ['updated_at']),
        ];

        // Validate required fields
        if (empty($plan['plan_title']) && empty($plan['name'])) {
            $errors[] = [
                'row' => $rowNumber,
                'field' => 'title/name',
                'message' => 'Plan must have a title or character name',
            ];
        }

        // Parse related data
        $plan['attributes'] = $this->parseAttributes($data['attributes'] ?? []);
        $plan['skills'] = $this->parseSkills($data['skills'] ?? []);
        $plan['goals'] = $this->parseGoals($data['goals'] ?? []);
        $plan['race_predictions'] = $this->parseRacePredictions($data['race_predictions'] ?? []);
        $plan['stat_progress'] = $this->parseStatProgress($data['stat_progress'] ?? []);
        $plan['snapshots'] = $this->parseSnapshots($data['snapshots'] ?? []);

        return [
            'plan' => empty($errors) ? $plan : null,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Extract a field value from data using multiple possible keys.
     */
    private function extractField(array $data, array $keys): mixed
    {
        foreach ($keys as $key) {
            // Support dot notation
            if (\str_contains($key, '.')) {
                $value = data_get($data, $key);
                if ($value !== null) {
                    return $value;
                }
            } elseif (isset($data[$key])) {
                return $data[$key];
            }
        }
        return null;
    }

    /**
     * Parse attributes array.
     */
    private function parseAttributes(array $attributes): array
    {
        return \array_map(fn($attr) => [
            'attribute_name' => $attr['name'] ?? $attr['attribute_name'] ?? null,
            'value' => $attr['value'] ?? null,
            'grade' => $attr['grade'] ?? null,
        ], $attributes);
    }

    /**
     * Parse skills array.
     */
    private function parseSkills(array $skills): array
    {
        return \array_map(fn($skill) => [
            'skill_name' => $skill['name'] ?? $skill['skill_name'] ?? null,
            'status' => $skill['status'] ?? 'acquired',
            'turn_acquired' => $skill['turn_acquired'] ?? null,
            'sp_cost' => $skill['sp_cost'] ?? null,
            'notes' => $skill['notes'] ?? null,
        ], $skills);
    }

    /**
     * Parse goals array.
     */
    private function parseGoals(array $goals): array
    {
        return \array_map(fn($goal) => [
            'goal' => $goal['description'] ?? $goal['goal'] ?? null,
            'result' => $goal['result'] ?? $goal['achieved'] ?? false,
        ], $goals);
    }

    /**
     * Parse race predictions array.
     */
    private function parseRacePredictions(array $predictions): array
    {
        return \array_map(fn($race) => [
            'race_name' => $race['race_name'] ?? null,
            'distance_category' => $race['distance_category'] ?? null,
            'track_type' => $race['track_type'] ?? null,
            'venue' => $race['venue'] ?? null,
            'predicted_pos' => $race['predicted_pos'] ?? null,
            'actual_pos' => $race['actual_pos'] ?? null,
            'sort_order' => $race['sort_order'] ?? null,
        ], $predictions);
    }

    /**
     * Parse stat progress array.
     */
    private function parseStatProgress(array $progress): array
    {
        return \array_map(fn($turn) => [
            'turn_number' => $turn['turn_number'] ?? $turn['turn'] ?? null,
            'speed' => $turn['speed'] ?? null,
            'stamina' => $turn['stamina'] ?? null,
            'power' => $turn['power'] ?? null,
            'guts' => $turn['guts'] ?? null,
            'wit' => $turn['wit'] ?? null,
        ], $progress);
    }

    /**
     * Parse snapshots array.
     */
    private function parseSnapshots(array $snapshots): array
    {
        return \array_map(fn($snapshot) => [
            'turn_number' => $snapshot['turn_number'] ?? null,
            'race_name' => $snapshot['race_name'] ?? null,
            'speed' => $snapshot['speed'] ?? null,
            'stamina' => $snapshot['stamina'] ?? null,
            'power' => $snapshot['power'] ?? null,
            'guts' => $snapshot['guts'] ?? null,
            'wit' => $snapshot['wit'] ?? null,
            'created_at' => $snapshot['created_at'] ?? null,
        ], $snapshots);
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

        // Validate skills
        foreach ($plan['skills'] ?? [] as $skillIndex => $skill) {
            if ($skill['status'] === 'acquired' && empty($skill['turn_acquired'])) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => "skills[$skillIndex].turn_acquired",
                    'message' => 'Turn acquired is required for acquired skills',
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
        return 'JSON (Uma Musume Planner)';
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedExtensions(): array
    {
        return ['json'];
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
        return self::EXPECTED_SCHEMA_VERSION;
    }
}
