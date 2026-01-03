<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Convert Local Run Service
 *
 * Handles conversion of local runs to account (database) storage.
 * Implements FR-9C: Convert Local Runs to Account (Claim Flow).
 */
class ConvertLocalRunService
{
    private DuplicateDetectionService $duplicateDetection;
    private ActivityLogService $activityLog;

    public function __construct(
        DuplicateDetectionService $duplicateDetection,
        ActivityLogService $activityLog
    ) {
        $this->duplicateDetection = $duplicateDetection;
        $this->activityLog = $activityLog;
    }

    /**
     * Convert a single local run to account storage.
     * Implements FR-9C.1, FR-9C.2: Convert action creates DB CareerRun + all related data.
     *
     * @return array{
     *     success: bool,
     *     plan_id: ?int,
     *     error: ?string,
     *     duplicate: ?array
     * }
     */
    public function convert(array $localRunData, User $user, array $options = []): array
    {
        $skipDuplicateCheck = $options['skip_duplicate_check'] ?? false;
        $keepLocalCopy = $options['keep_local_copy'] ?? false;

        // Validate structure
        $validation = $this->validateLocalRunData($localRunData);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'plan_id' => null,
                'error' => 'Invalid local run data: ' . \implode(', ', $validation['errors']),
                'duplicate' => null,
            ];
        }

        // Check for duplicates
        if (!$skipDuplicateCheck) {
            $duplicate = $this->duplicateDetection->findDuplicate($localRunData, $user);
            if ($duplicate !== null) {
                return [
                    'success' => false,
                    'plan_id' => null,
                    'error' => null,
                    'duplicate' => [
                        'existing_id' => $duplicate->id,
                        'existing_title' => $duplicate->plan_title,
                        'reason' => $this->duplicateDetection->getDuplicateReason($localRunData, $duplicate),
                    ],
                ];
            }
        }

        DB::beginTransaction();

        try {
            $plan = $this->createPlanFromLocalData($localRunData, $user);

            // Log the conversion
            $this->activityLog->log(
                'convert_local_run',
                "Converted local run '{$plan->plan_title}' to account",
                $plan,
                $user,
                [
                    'local_uuid' => $localRunData['id'] ?? null,
                    'keep_local_copy' => $keepLocalCopy,
                ]
            );

            DB::commit();

            return [
                'success' => true,
                'plan_id' => $plan->id,
                'error' => null,
                'duplicate' => null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'plan_id' => null,
                'error' => 'Conversion failed: ' . $e->getMessage(),
                'duplicate' => null,
            ];
        }
    }

    /**
     * Bulk convert multiple local runs to account storage.
     * Implements FR-9C.6: Bulk convert supported from Local Data page/modal.
     *
     * @return array{
     *     success: bool,
     *     converted: int,
     *     skipped: int,
     *     failed: int,
     *     results: array
     * }
     */
    public function bulkConvert(array $localRuns, User $user, array $options = []): array
    {
        $converted = 0;
        $skipped = 0;
        $failed = 0;
        $results = [];

        foreach ($localRuns as $index => $localRunData) {
            $result = $this->convert($localRunData, $user, $options);

            if ($result['success']) {
                $converted++;
                $results[] = [
                    'index' => $index,
                    'local_id' => $localRunData['id'] ?? null,
                    'status' => 'converted',
                    'plan_id' => $result['plan_id'],
                ];
            } elseif ($result['duplicate'] !== null) {
                $skipped++;
                $results[] = [
                    'index' => $index,
                    'local_id' => $localRunData['id'] ?? null,
                    'status' => 'skipped_duplicate',
                    'duplicate' => $result['duplicate'],
                ];
            } else {
                $failed++;
                $results[] = [
                    'index' => $index,
                    'local_id' => $localRunData['id'] ?? null,
                    'status' => 'failed',
                    'error' => $result['error'],
                ];
            }
        }

        return [
            'success' => $failed === 0,
            'converted' => $converted,
            'skipped' => $skipped,
            'failed' => $failed,
            'results' => $results,
        ];
    }

    /**
     * Create a Plan model from local run data.
     * Implements FR-9C.3: Conversion preserves timestamps, turn ordering, skill statuses.
     */
    private function createPlanFromLocalData(array $localRunData, User $user): Plan
    {
        $careerRun = $localRunData['career_run'] ?? [];

        $plan = Plan::create([
            'user_id' => $user->id,
            'plan_title' => $careerRun['plan_title'] ?? $careerRun['name'] ?? 'Converted Plan',
            'name' => $careerRun['name'] ?? null,
            'career_stage' => $careerRun['career_stage'] ?? null,
            'class' => $careerRun['class'] ?? null,
            'status' => $careerRun['status'] ?? 'ongoing',
            'scenario' => $careerRun['scenario'] ?? 'URA',
            'turn_before' => $careerRun['current_turn'] ?? null,
            'race_name' => $careerRun['current_race'] ?? null,
            'total_available_skill_points' => $careerRun['total_sp_available'] ?? null,
            'stamina_percentage' => $careerRun['stamina_percentage'] ?? null,
            'storage_mode' => 'account',
            'local_uuid' => $localRunData['id'] ?? null,
            // Preserve original timestamps
            'created_at' => $this->parseTimestamp($localRunData['created_at'] ?? null),
            'updated_at' => now(),
        ]);

        // Create related records
        $this->createRelatedRecords($plan, $localRunData);

        return $plan;
    }

    /**
     * Create related records for a converted plan.
     */
    private function createRelatedRecords(Plan $plan, array $localRunData): void
    {
        // Create stat progress (turns) - preserve ordering
        foreach ($localRunData['stat_progress'] ?? [] as $turn) {
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

        // Create skills - preserve status and turn_acquired
        foreach ($localRunData['skills'] ?? [] as $skill) {
            if (!empty($skill['skill_name']) || !empty($skill['skill_reference_id'])) {
                $plan->skills()->create([
                    'skill_reference_id' => $skill['skill_reference_id'] ?? null,
                    'status' => $skill['status'] ?? 'acquired',
                    'turn_acquired' => $skill['turn_acquired'] ?? null,
                    'sp_cost' => $skill['sp_cost'] ?? null,
                    'notes' => $skill['notes'] ?? null,
                ]);
            }
        }

        // Create goals
        foreach ($localRunData['goals'] ?? [] as $goal) {
            if (!empty($goal['goal'])) {
                $plan->goals()->create([
                    'goal' => $goal['goal'],
                    'result' => $goal['result'] ?? false,
                ]);
            }
        }

        // Create race predictions - preserve sort order
        foreach ($localRunData['race_predictions'] ?? [] as $index => $race) {
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

        // Create snapshots
        foreach ($localRunData['snapshots'] ?? [] as $snapshot) {
            if (isset($snapshot['turn_number'])) {
                $plan->careerSnapshots()->create([
                    'turn_number' => $snapshot['turn_number'],
                    'race_name' => $snapshot['race_name'] ?? null,
                    'speed' => $snapshot['speed'] ?? null,
                    'stamina' => $snapshot['stamina'] ?? null,
                    'power' => $snapshot['power'] ?? null,
                    'guts' => $snapshot['guts'] ?? null,
                    'wit' => $snapshot['wit'] ?? null,
                    'created_at' => $this->parseTimestamp($snapshot['created_at'] ?? null),
                ]);
            }
        }
    }

    /**
     * Validate local run data structure.
     *
     * @return array{valid: bool, errors: array}
     */
    private function validateLocalRunData(array $data): array
    {
        $errors = [];

        if (!isset($data['career_run'])) {
            $errors[] = 'Missing career_run data';
        }

        if (isset($data['career_run']) && !\is_array($data['career_run'])) {
            $errors[] = 'career_run must be an array';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Parse timestamp from various formats.
     */
    private function parseTimestamp(?string $timestamp): ?\DateTime
    {
        if ($timestamp === null) {
            return null;
        }

        try {
            return new \DateTime($timestamp);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Prepare backup data before conversion.
     * Implements FR-9C.9: "Download Backup" action available before conversion.
     */
    public function prepareBackup(array $localRunData): array
    {
        return [
            'schema_version' => $localRunData['schema_version'] ?? '1.0.0',
            'backup_created_at' => now()->toIso8601String(),
            'backup_reason' => 'pre_conversion',
            'data' => $localRunData,
        ];
    }
}
