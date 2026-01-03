<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ImportTarget;
use App\Models\Plan;
use App\Models\User;
use App\Services\Import\CsvImportAdapter;
use App\Services\Import\FormatDetector;
use App\Services\Import\ImportAdapterInterface;
use App\Services\Import\JsonImportAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Import Service Class
 *
 * Handles data import from various formats with preview, validation, and execution.
 * Implements FR-6B: Import System requirements.
 */
class ImportService
{
    private FormatDetector $formatDetector;

    public function __construct()
    {
        $this->formatDetector = new FormatDetector();
        $this->registerDefaultAdapters();
    }

    /**
     * Register default import adapters.
     */
    private function registerDefaultAdapters(): void
    {
        $this->formatDetector->registerAdapter(new JsonImportAdapter());
        $this->formatDetector->registerAdapter(new CsvImportAdapter());
    }

    /**
     * Register a custom import adapter.
     */
    public function registerAdapter(ImportAdapterInterface $adapter): self
    {
        $this->formatDetector->registerAdapter($adapter);
        return $this;
    }

    /**
     * Detect the format of uploaded content.
     * Implements FR-6B.2: Upload file → detect legacy format automatically.
     *
     * @return array{
     *     detected: bool,
     *     format: ?string,
     *     confidence: string,
     *     alternatives: array
     * }
     */
    public function detectFormat(string $content, ?string $filename = null): array
    {
        $result = $this->formatDetector->detect($content, $filename);

        return [
            'detected' => $result['detected'],
            'format' => $result['format'],
            'confidence' => $result['confidence'],
            'alternatives' => $result['alternatives'],
        ];
    }

    /**
     * Parse content and return preview data.
     * Implements FR-6B.3: Preview mapping before import.
     *
     * @return array{
     *     success: bool,
     *     format: ?string,
     *     plans: Collection,
     *     characters: Collection,
     *     field_mapping: array,
     *     errors: array,
     *     warnings: array,
     *     metadata: array
     * }
     */
    public function preview(string $content, ?string $filename = null): array
    {
        $detection = $this->formatDetector->detect($content, $filename);

        if (!$detection['detected'] || $detection['adapter'] === null) {
            return [
                'success' => false,
                'format' => null,
                'plans' => new Collection(),
                'characters' => new Collection(),
                'field_mapping' => [],
                'errors' => [['row' => 0, 'message' => 'Unable to detect file format']],
                'warnings' => [],
                'metadata' => [],
            ];
        }

        /** @var ImportAdapterInterface $adapter */
        $adapter = $detection['adapter'];
        $parsed = $adapter->parse($content);

        return [
            'success' => empty($parsed['errors']),
            'format' => $adapter->getFormatName(),
            'plans' => $parsed['plans'],
            'characters' => $parsed['characters'],
            'field_mapping' => $adapter->getFieldMapping(),
            'errors' => $parsed['errors'],
            'warnings' => $parsed['warnings'],
            'metadata' => $parsed['metadata'],
        ];
    }

    /**
     * Validate parsed data without importing (dry-run).
     * Implements FR-6B.4: Dry-run validation with row-level error reporting.
     *
     * @return array{
     *     valid: bool,
     *     errors: array,
     *     warnings: array,
     *     summary: array,
     *     duplicates: array
     * }
     */
    public function validate(
        string $content,
        ?string $filename = null,
        ImportTarget $target = ImportTarget::Local,
        ?User $user = null
    ): array {
        $preview = $this->preview($content, $filename);

        if (!$preview['success']) {
            return [
                'valid' => false,
                'errors' => $preview['errors'],
                'warnings' => $preview['warnings'],
                'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0],
                'duplicates' => [],
            ];
        }

        // Get adapter for validation
        $detection = $this->formatDetector->detect($content, $filename);
        /** @var ImportAdapterInterface $adapter */
        $adapter = $detection['adapter'];

        $validation = $adapter->validate([
            'plans' => $preview['plans'],
            'warnings' => $preview['warnings'],
        ]);

        // Check for duplicates if importing to account
        $duplicates = [];
        if ($target === ImportTarget::Account && $user !== null) {
            $duplicates = $this->detectDuplicates($preview['plans'], $user);
        }

        return [
            'valid' => $validation['valid'] && empty($duplicates),
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings'],
            'summary' => $validation['summary'],
            'duplicates' => $duplicates,
        ];
    }

    /**
     * Execute the import.
     * Implements FR-6B.5: Confirm import → execute → show results report.
     *
     * @return array{
     *     success: bool,
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     errors: array,
     *     imported_ids: array
     * }
     */
    public function import(
        string $content,
        ?string $filename = null,
        ImportTarget $target = ImportTarget::Local,
        ?User $user = null,
        array $options = []
    ): array {
        $preview = $this->preview($content, $filename);

        if (!$preview['success']) {
            return [
                'success' => false,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => $preview['errors'],
                'imported_ids' => [],
            ];
        }

        // Validate target requirements
        if ($target === ImportTarget::Account && $user === null) {
            return [
                'success' => false,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => [['row' => 0, 'message' => 'Account import requires authentication']],
                'imported_ids' => [],
            ];
        }

        // Execute import based on target
        if ($target === ImportTarget::Local) {
            return $this->importToLocal($preview['plans'], $options);
        }

        return $this->importToAccount($preview['plans'], $user, $options);
    }

    /**
     * Import plans to local storage (returns data for client-side storage).
     * Implements FR-6B.12: Importing into Local shall not require network calls.
     */
    private function importToLocal(Collection $plans, array $options): array
    {
        $created = 0;
        $importedIds = [];

        foreach ($plans as $plan) {
            $uuid = Str::uuid()->toString();
            $importedIds[] = [
                'type' => 'local',
                'id' => $uuid,
                'data' => $this->prepareLocalPlanData($plan, $uuid),
            ];
            $created++;
        }

        return [
            'success' => true,
            'created' => $created,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'imported_ids' => $importedIds,
        ];
    }

    /**
     * Import plans to account (database).
     * Implements FR-6B.13: Importing into Account uses DB transactions + validation.
     */
    private function importToAccount(Collection $plans, User $user, array $options): array
    {
        $created = 0;
        $skipped = 0;
        $errors = [];
        $importedIds = [];
        $skipDuplicates = $options['skip_duplicates'] ?? false;

        DB::beginTransaction();

        try {
            foreach ($plans as $index => $planData) {
                // Check for duplicates
                if ($skipDuplicates && $this->isDuplicate($planData, $user)) {
                    $skipped++;
                    continue;
                }

                try {
                    $plan = $this->createPlanFromData($planData, $user);
                    $importedIds[] = [
                        'type' => 'account',
                        'id' => $plan->id,
                    ];
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $index + 1,
                        'message' => 'Failed to create plan: ' . $e->getMessage(),
                    ];
                }
            }

            if (empty($errors)) {
                DB::commit();
            } else {
                DB::rollBack();
                return [
                    'success' => false,
                    'created' => 0,
                    'updated' => 0,
                    'skipped' => $skipped,
                    'errors' => $errors,
                    'imported_ids' => [],
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => [['row' => 0, 'message' => 'Import failed: ' . $e->getMessage()]],
                'imported_ids' => [],
            ];
        }

        return [
            'success' => true,
            'created' => $created,
            'updated' => 0,
            'skipped' => $skipped,
            'errors' => $errors,
            'imported_ids' => $importedIds,
        ];
    }

    /**
     * Prepare plan data for local storage.
     */
    private function prepareLocalPlanData(array $planData, string $uuid): array
    {
        return [
            'schema_version' => '1.0.0',
            'id' => $uuid,
            'created_at' => $planData['created_at'] ?? now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'career_run' => [
                'plan_title' => $planData['plan_title'] ?? null,
                'name' => $planData['name'] ?? null,
                'scenario' => $planData['scenario'] ?? 'URA',
                'career_stage' => $planData['career_stage'] ?? null,
                'status' => $planData['status'] ?? 'ongoing',
                'class' => $planData['class'] ?? null,
                'current_turn' => $planData['turn_before'] ?? null,
                'current_race' => $planData['race_name'] ?? null,
                'total_sp_available' => $planData['total_available_skill_points'] ?? null,
                'stamina_percentage' => $planData['stamina_percentage'] ?? null,
            ],
            'stat_progress' => $planData['stat_progress'] ?? [],
            'skills' => $planData['skills'] ?? [],
            'goals' => $planData['goals'] ?? [],
            'race_predictions' => $planData['race_predictions'] ?? [],
            'snapshots' => $planData['snapshots'] ?? [],
            'activity_log' => [],
        ];
    }

    /**
     * Create a Plan model from parsed data.
     */
    private function createPlanFromData(array $planData, User $user): Plan
    {
        $plan = Plan::create([
            'user_id' => $user->id,
            'plan_title' => $planData['plan_title'] ?? $planData['name'] ?? 'Imported Plan',
            'name' => $planData['name'] ?? null,
            'career_stage' => $planData['career_stage'] ?? null,
            'class' => $planData['class'] ?? null,
            'status' => $planData['status'] ?? 'ongoing',
            'scenario' => $planData['scenario'] ?? 'URA',
            'turn_before' => $planData['turn_before'] ?? null,
            'race_name' => $planData['race_name'] ?? null,
            'total_available_skill_points' => $planData['total_available_skill_points'] ?? null,
            'stamina_percentage' => $planData['stamina_percentage'] ?? null,
            'growth_rate_speed' => $planData['growth_rate_speed'] ?? null,
            'growth_rate_stamina' => $planData['growth_rate_stamina'] ?? null,
            'growth_rate_power' => $planData['growth_rate_power'] ?? null,
            'growth_rate_guts' => $planData['growth_rate_guts'] ?? null,
            'growth_rate_wit' => $planData['growth_rate_wit'] ?? null,
            'storage_mode' => 'account',
        ]);

        // Create related records
        $this->createRelatedRecords($plan, $planData);

        return $plan;
    }

    /**
     * Create related records for a plan.
     */
    private function createRelatedRecords(Plan $plan, array $planData): void
    {
        // Create attributes
        foreach ($planData['attributes'] ?? [] as $attr) {
            if (!empty($attr['attribute_name'])) {
                $plan->attributes()->create([
                    'attribute_name' => $attr['attribute_name'],
                    'value' => $attr['value'] ?? null,
                    'grade' => $attr['grade'] ?? null,
                ]);
            }
        }

        // Create goals
        foreach ($planData['goals'] ?? [] as $goal) {
            if (!empty($goal['goal'])) {
                $plan->goals()->create([
                    'goal' => $goal['goal'],
                    'result' => $goal['result'] ?? false,
                ]);
            }
        }

        // Create race predictions
        foreach ($planData['race_predictions'] ?? [] as $index => $race) {
            if (!empty($race['race_name'])) {
                $plan->racePredictions()->create([
                    'race_name' => $race['race_name'],
                    'distance_category' => $race['distance_category'] ?? null,
                    'track_type' => $race['track_type'] ?? null,
                    'venue' => $race['venue'] ?? null,
                    'predicted_pos' => $race['predicted_pos'] ?? null,
                    'actual_pos' => $race['actual_pos'] ?? null,
                    'sort_order' => $race['sort_order'] ?? $index,
                ]);
            }
        }

        // Create stat progress (turns)
        foreach ($planData['stat_progress'] ?? [] as $turn) {
            if (isset($turn['turn_number'])) {
                $plan->turns()->create([
                    'turn_number' => $turn['turn_number'],
                    'speed' => $turn['speed'] ?? null,
                    'stamina' => $turn['stamina'] ?? null,
                    'power' => $turn['power'] ?? null,
                    'guts' => $turn['guts'] ?? null,
                    'wit' => $turn['wit'] ?? null,
                ]);
            }
        }

        // Create snapshots
        foreach ($planData['snapshots'] ?? [] as $snapshot) {
            if (isset($snapshot['turn_number'])) {
                $plan->careerSnapshots()->create([
                    'turn_number' => $snapshot['turn_number'],
                    'race_name' => $snapshot['race_name'] ?? null,
                    'speed' => $snapshot['speed'] ?? null,
                    'stamina' => $snapshot['stamina'] ?? null,
                    'power' => $snapshot['power'] ?? null,
                    'guts' => $snapshot['guts'] ?? null,
                    'wit' => $snapshot['wit'] ?? null,
                ]);
            }
        }
    }

    /**
     * Detect duplicate plans.
     * Implements FR-6B.7: Duplicate detection with warning.
     *
     * @return array<array{plan_index: int, existing_id: int, reason: string}>
     */
    public function detectDuplicates(Collection $plans, User $user): array
    {
        $duplicates = [];

        foreach ($plans as $index => $planData) {
            $existing = $this->findExistingPlan($planData, $user);
            if ($existing !== null) {
                $duplicates[] = [
                    'plan_index' => $index,
                    'existing_id' => $existing->id,
                    'reason' => $this->getDuplicateReason($planData, $existing),
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Check if a plan is a duplicate.
     */
    private function isDuplicate(array $planData, User $user): bool
    {
        return $this->findExistingPlan($planData, $user) !== null;
    }

    /**
     * Find an existing plan that matches the import data.
     */
    private function findExistingPlan(array $planData, User $user): ?Plan
    {
        $query = Plan::where('user_id', $user->id);

        // Match by title and character name
        if (!empty($planData['plan_title'])) {
            $query->where('plan_title', $planData['plan_title']);
        }

        if (!empty($planData['name'])) {
            $query->where('name', $planData['name']);
        }

        // Match by created date if available
        if (!empty($planData['created_at'])) {
            $query->whereDate('created_at', $planData['created_at']);
        }

        return $query->first();
    }

    /**
     * Get the reason why a plan is considered a duplicate.
     */
    private function getDuplicateReason(array $planData, Plan $existing): string
    {
        $reasons = [];

        if (!empty($planData['plan_title']) && $planData['plan_title'] === $existing->plan_title) {
            $reasons[] = 'same title';
        }

        if (!empty($planData['name']) && $planData['name'] === $existing->name) {
            $reasons[] = 'same character';
        }

        return 'Matches existing plan: ' . \implode(', ', $reasons);
    }

    /**
     * Generate error report as CSV.
     * Implements FR-6B.6: Downloadable error report as CSV.
     */
    public function generateErrorReport(array $errors): string
    {
        $lines = [];
        $lines[] = "Row,Field,Message";

        foreach ($errors as $error) {
            $row = $error['row'] ?? '';
            $field = $error['field'] ?? '';
            $message = $error['message'] ?? '';

            // Escape CSV values
            $message = '"' . \str_replace('"', '""', $message) . '"';

            $lines[] = "{$row},{$field},{$message}";
        }

        return \implode("\n", $lines);
    }

    /**
     * Get supported formats.
     *
     * @return array<string>
     */
    public function getSupportedFormats(): array
    {
        return $this->formatDetector->getSupportedFormats();
    }

    /**
     * Get supported file extensions.
     *
     * @return array<string>
     */
    public function getSupportedExtensions(): array
    {
        return $this->formatDetector->getSupportedExtensions();
    }
}
