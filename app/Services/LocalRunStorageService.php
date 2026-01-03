<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Local Run Storage Service
 *
 * Provides server-side utilities for local storage operations.
 * The actual storage is handled client-side (localStorage/IndexedDB).
 * Implements FR-9B.9: LocalRunStorageService interface abstracts storage backend.
 */
class LocalRunStorageService
{
    /**
     * Current schema version for local storage.
     */
    public const SCHEMA_VERSION = '1.0.0';

    /**
     * Generate a new UUID for a local run.
     * Implements FR-9B.2: Local runs have permanent UUIDs.
     */
    public function generateUuid(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Get the current schema version.
     * Implements FR-9B.7: Local storage schema is versioned.
     */
    public function getSchemaVersion(): string
    {
        return self::SCHEMA_VERSION;
    }

    /**
     * Create an empty local run structure.
     */
    public function createEmptyRun(?string $uuid = null): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'id' => $uuid ?? $this->generateUuid(),
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'career_run' => [
                'plan_title' => null,
                'name' => null,
                'scenario' => 'URA',
                'career_stage' => null,
                'status' => 'ongoing',
                'class' => null,
                'current_turn' => 1,
                'current_race' => null,
                'total_sp_available' => 0,
                'stamina_percentage' => 100,
                'mood' => null,
                'conditions' => null,
                'notes' => null,
            ],
            'stat_progress' => [],
            'skills' => [],
            'goals' => [],
            'race_predictions' => [],
            'snapshots' => [],
            'activity_log' => [],
        ];
    }

    /**
     * Validate local run data structure.
     *
     * @return array{valid: bool, errors: array}
     */
    public function validateStructure(array $data): array
    {
        $errors = [];

        // Check required top-level fields
        if (!isset($data['id'])) {
            $errors[] = 'Missing required field: id';
        }

        if (!isset($data['schema_version'])) {
            $errors[] = 'Missing required field: schema_version';
        }

        if (!isset($data['career_run'])) {
            $errors[] = 'Missing required field: career_run';
        }

        // Validate UUID format
        if (isset($data['id']) && !Str::isUuid($data['id'])) {
            $errors[] = 'Invalid UUID format for id';
        }

        // Validate career_run structure
        if (isset($data['career_run']) && !\is_array($data['career_run'])) {
            $errors[] = 'career_run must be an array';
        }

        // Validate arrays
        foreach (['stat_progress', 'skills', 'goals', 'race_predictions', 'snapshots', 'activity_log'] as $field) {
            if (isset($data[$field]) && !\is_array($data[$field])) {
                $errors[] = "{$field} must be an array";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Migrate local run data to current schema version.
     * Implements FR-9B.8: System migrates older local schema versions forward on load.
     */
    public function migrateSchema(array $data): array
    {
        $version = $data['schema_version'] ?? '0.0.0';

        // Apply migrations based on version
        if (\version_compare($version, '1.0.0', '<')) {
            $data = $this->migrateToV1($data);
        }

        // Update schema version
        $data['schema_version'] = self::SCHEMA_VERSION;
        $data['updated_at'] = now()->toIso8601String();

        return $data;
    }

    /**
     * Migrate data to v1.0.0 schema.
     */
    private function migrateToV1(array $data): array
    {
        // Ensure all required fields exist
        $data['id'] = $data['id'] ?? $this->generateUuid();
        $data['created_at'] = $data['created_at'] ?? now()->toIso8601String();
        $data['updated_at'] = $data['updated_at'] ?? now()->toIso8601String();

        // Migrate career_run field names
        if (isset($data['career_run'])) {
            $run = &$data['career_run'];

            // Rename fields if using old names
            if (isset($run['total_sp']) && !isset($run['total_sp_available'])) {
                $run['total_sp_available'] = $run['total_sp'];
                unset($run['total_sp']);
            }

            if (isset($run['stamina_pct']) && !isset($run['stamina_percentage'])) {
                $run['stamina_percentage'] = $run['stamina_pct'];
                unset($run['stamina_pct']);
            }

            if (isset($run['turn']) && !isset($run['current_turn'])) {
                $run['current_turn'] = $run['turn'];
                unset($run['turn']);
            }
        }

        // Ensure arrays exist
        $data['stat_progress'] = $data['stat_progress'] ?? [];
        $data['skills'] = $data['skills'] ?? [];
        $data['goals'] = $data['goals'] ?? [];
        $data['race_predictions'] = $data['race_predictions'] ?? [];
        $data['snapshots'] = $data['snapshots'] ?? [];
        $data['activity_log'] = $data['activity_log'] ?? [];

        return $data;
    }

    /**
     * Prepare local run data for export.
     * Implements FR-9B.4: Export local data as JSON backup file.
     */
    public function prepareForExport(array $data): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'exported_at' => now()->toIso8601String(),
            'data' => $data,
        ];
    }

    /**
     * Prepare multiple local runs for bulk export.
     */
    public function prepareBulkExport(array $runs): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'exported_at' => now()->toIso8601String(),
            'count' => \count($runs),
            'runs' => $runs,
        ];
    }

    /**
     * Calculate approximate storage size for a local run.
     */
    public function calculateStorageSize(array $data): int
    {
        return \strlen(\json_encode($data) ?: '');
    }

    /**
     * Format storage size for display.
     */
    public function formatStorageSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < \count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return \round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get storage quota warning threshold (5MB default).
     * Implements FR-9B.6: Storage quota warning when approaching browser limits.
     */
    public function getQuotaWarningThreshold(): int
    {
        return 5 * 1024 * 1024; // 5MB
    }

    /**
     * Check if storage is approaching quota.
     */
    public function isApproachingQuota(int $currentSize, ?int $threshold = null): bool
    {
        $threshold = $threshold ?? $this->getQuotaWarningThreshold();
        return $currentSize >= $threshold;
    }

    /**
     * Add activity log entry to local run.
     * Implements FR-8.5: Local runs activity log stored separately in localStorage.
     */
    public function addActivityLogEntry(array &$data, string $action, string $description, array $metadata = []): void
    {
        $data['activity_log'][] = [
            'id' => $this->generateUuid(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now()->toIso8601String(),
        ];

        $data['updated_at'] = now()->toIso8601String();
    }
}
