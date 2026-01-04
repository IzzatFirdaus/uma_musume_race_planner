<?php

declare(strict_types=1);

namespace App\Livewire\LocalData;

use App\Enums\StorageMode;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Local Data Manager Page Component
 *
 * Manages locally stored plans in browser localStorage.
 * Implements Requirements 56B.1-56B.8:
 * - 56B.1: Access from Dashboard or navigation
 * - 56B.2: Display all Local_Runs with storage info
 * - 56B.3: Export All action
 * - 56B.4: Import action with conflict resolution
 * - 56B.5: Purge All with double-confirmation
 * - 56B.6: Convert All to Account (authenticated)
 * - 56B.7: Display storage statistics
 * - 56B.8: Selective export
 */
#[Layout('components.layout')]
#[Title('Local Data Management')]
class Manager extends Component
{
    // Modal states
    public bool $showPurgeStep1 = false;

    public bool $showPurgeStep2 = false;

    public bool $showBulkConvertModal = false;

    public bool $showImportModal = false;

    public bool $showConvertResultsModal = false;

    // Import state
    public string $conflictResolution = 'skip';

    public array $importPreview = [];

    public bool $importInProgress = false;

    // Convert results
    public array $convertResults = [
        'converted' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    // Purge confirmation text
    public string $purgeConfirmText = '';

    // Search query for filtering local runs
    public string $searchQuery = '';

    // Selected runs for selective export
    public array $selectedRuns = [];

    // Storage stats (populated from client-side)
    public int $storageUsed = 0;

    public int $storageAvailable = 5242880; // 5MB default

    public int $runCount = 0;

    public int $percentUsed = 0;

    public bool $isNearQuota = false;

    /**
     * Check if user is authenticated.
     */
    #[Computed]
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Get the current user ID.
     */
    #[Computed]
    public function userId(): ?int
    {
        return Auth::id();
    }

    /**
     * Format bytes to human-readable string.
     */
    public function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2).' '.$sizes[$i];
    }

    /**
     * Update storage stats from client-side.
     * Called via Alpine.js when localStorage data changes.
     */
    #[On('storage-stats-updated')]
    public function updateStorageStats(int $used, int $available, int $count, int $percent, bool $nearQuota): void
    {
        $this->storageUsed = $used;
        $this->storageAvailable = $available;
        $this->runCount = $count;
        $this->percentUsed = $percent;
        $this->isNearQuota = $nearQuota;
    }

    /**
     * Show first step of purge confirmation (Req 56B.5).
     */
    public function showPurgeConfirmation(): void
    {
        $this->showPurgeStep1 = true;
        $this->showPurgeStep2 = false;
        $this->purgeConfirmText = '';
    }

    /**
     * Proceed to second step of purge confirmation.
     */
    public function proceedToPurgeStep2(): void
    {
        $this->showPurgeStep1 = false;
        $this->showPurgeStep2 = true;
    }

    /**
     * Cancel purge operation.
     */
    public function cancelPurge(): void
    {
        $this->showPurgeStep1 = false;
        $this->showPurgeStep2 = false;
        $this->purgeConfirmText = '';
    }

    /**
     * Execute purge after double-confirmation (Req 56B.5).
     */
    public function executePurge(): void
    {
        if (strtoupper($this->purgeConfirmText) !== 'DELETE') {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Please type DELETE to confirm',
            ]);

            return;
        }

        // Dispatch event to client-side to clear localStorage
        $this->dispatch('execute-purge-local-storage');

        $this->showPurgeStep2 = false;
        $this->purgeConfirmText = '';
    }

    /**
     * Handle purge completion from client-side.
     */
    #[On('purge-completed')]
    public function handlePurgeCompleted(int $count): void
    {
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Deleted {$count} local plan(s)",
        ]);

        // Reset stats
        $this->runCount = 0;
        $this->storageUsed = 0;
        $this->percentUsed = 0;
        $this->isNearQuota = false;
    }

    /**
     * Show bulk convert modal (Req 56B.6).
     */
    public function showBulkConvert(): void
    {
        if (! $this->isAuthenticated) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => 'Please sign in to convert local plans to your account',
            ]);

            return;
        }

        $this->showBulkConvertModal = true;
        $this->convertResults = ['converted' => 0, 'failed' => 0, 'errors' => []];
    }

    /**
     * Cancel bulk convert.
     */
    public function cancelBulkConvert(): void
    {
        $this->showBulkConvertModal = false;
    }

    /**
     * Execute bulk convert - dispatches to client to get data.
     */
    public function executeBulkConvert(): void
    {
        if (! $this->isAuthenticated) {
            return;
        }

        // Dispatch event to client-side to get all local runs
        $this->dispatch('get-local-runs-for-convert');
    }

    /**
     * Receive local runs from client and convert them to account.
     */
    #[On('convert-local-runs')]
    public function convertLocalRuns(array $runs): void
    {
        if (! $this->isAuthenticated) {
            return;
        }

        $userId = Auth::id();
        $converted = 0;
        $failed = 0;
        $errors = [];
        $convertedUuids = [];

        foreach ($runs as $runData) {
            try {
                // Create new plan in database
                $plan = Plan::create([
                    'user_id' => $userId,
                    'storage_mode' => StorageMode::Account,
                    'local_uuid' => $runData['uuid'] ?? null,
                    'plan_title' => $runData['title'] ?? 'Untitled Plan',
                    'name' => $runData['character_name'] ?? '',
                    'status' => ucfirst($runData['status'] ?? 'in_progress'),
                    'career_stage' => $runData['career_stage'] ?? 'junior',
                    'current_turn' => $runData['current_turn'] ?? 1,
                    'speed' => $runData['speed'] ?? 0,
                    'stamina' => $runData['stamina'] ?? 0,
                    'power' => $runData['power'] ?? 0,
                    'guts' => $runData['guts'] ?? 0,
                    'wit' => $runData['wit'] ?? 0,
                    'mood' => $runData['mood'] ?? 'normal',
                    'energy' => $runData['energy'] ?? 100,
                    'notes' => $runData['notes'] ?? '',
                ]);

                $convertedUuids[] = $runData['uuid'];
                $converted++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = ($runData['title'] ?? 'Unknown').': '.$e->getMessage();
                Log::error('Failed to convert local run', [
                    'uuid' => $runData['uuid'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->convertResults = [
            'converted' => $converted,
            'failed' => $failed,
            'errors' => $errors,
        ];

        // Tell client to remove converted runs from localStorage
        if (count($convertedUuids) > 0) {
            $this->dispatch('remove-converted-runs', ['uuids' => $convertedUuids]);
        }

        $this->showBulkConvertModal = false;
        $this->showConvertResultsModal = true;
    }

    /**
     * Close convert results modal.
     */
    public function closeConvertResults(): void
    {
        $this->showConvertResultsModal = false;
    }

    /**
     * Show import modal (Req 56B.4).
     */
    public function showImport(): void
    {
        $this->showImportModal = true;
        $this->importPreview = [];
        $this->conflictResolution = 'skip';
    }

    /**
     * Cancel import.
     */
    public function cancelImport(): void
    {
        $this->showImportModal = false;
        $this->importPreview = [];
    }

    /**
     * Execute import with selected conflict resolution.
     */
    public function executeImport(): void
    {
        // Dispatch to client-side to execute import with conflict resolution
        $this->dispatch('execute-import', ['conflictResolution' => $this->conflictResolution]);
    }

    /**
     * Handle import completion from client-side.
     */
    #[On('import-completed')]
    public function handleImportCompleted(int $imported, int $skipped, array $errors): void
    {
        $this->showImportModal = false;

        if (count($errors) > 0) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => "Imported {$imported} plan(s), skipped {$skipped}, errors: ".count($errors),
            ]);
        } else {
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Imported {$imported} plan(s), skipped {$skipped}",
            ]);
        }
    }

    /**
     * Toggle selection of a run for selective export.
     */
    public function toggleRunSelection(string $uuid): void
    {
        if (in_array($uuid, $this->selectedRuns)) {
            $this->selectedRuns = array_values(array_diff($this->selectedRuns, [$uuid]));
        } else {
            $this->selectedRuns[] = $uuid;
        }
    }

    /**
     * Select all runs.
     */
    public function selectAllRuns(): void
    {
        $this->dispatch('select-all-runs');
    }

    /**
     * Deselect all runs.
     */
    public function deselectAllRuns(): void
    {
        $this->selectedRuns = [];
    }

    /**
     * Update selected runs from client.
     */
    #[On('update-selected-runs')]
    public function updateSelectedRuns(array $uuids): void
    {
        $this->selectedRuns = $uuids;
    }

    /**
     * Export selected runs (Req 56B.8).
     */
    public function exportSelected(): void
    {
        if (empty($this->selectedRuns)) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => 'Please select at least one plan to export',
            ]);

            return;
        }

        $this->dispatch('export-selected-runs', ['uuids' => $this->selectedRuns]);
    }

    /**
     * Export all runs (Req 56B.3).
     */
    public function exportAll(): void
    {
        $this->dispatch('export-all-runs');
    }

    /**
     * Handle export completion.
     */
    #[On('export-completed')]
    public function handleExportCompleted(int $count): void
    {
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Exported {$count} plan(s) to JSON",
        ]);
    }

    /**
     * Delete a single local run.
     */
    public function deleteRun(string $uuid): void
    {
        $this->dispatch('delete-local-run', ['uuid' => $uuid]);
    }

    /**
     * Handle single run deletion completion.
     */
    #[On('run-deleted')]
    public function handleRunDeleted(string $title): void
    {
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Deleted plan: {$title}",
        ]);

        // Remove from selected if it was selected
        $this->selectedRuns = array_values(array_filter($this->selectedRuns, fn ($uuid) => $uuid !== $title));
    }

    /**
     * Convert a single run to account.
     */
    public function convertSingleRun(string $uuid): void
    {
        if (! $this->isAuthenticated) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => 'Please sign in to convert plans to your account',
            ]);

            return;
        }

        $this->dispatch('get-single-run-for-convert', ['uuid' => $uuid]);
    }

    /**
     * Receive single run from client and convert to account.
     */
    #[On('convert-single-run')]
    public function convertSingleRunData(array $runData): void
    {
        if (! $this->isAuthenticated) {
            return;
        }

        try {
            $userId = Auth::id();

            Plan::create([
                'user_id' => $userId,
                'storage_mode' => StorageMode::Account,
                'local_uuid' => $runData['uuid'] ?? null,
                'plan_title' => $runData['title'] ?? 'Untitled Plan',
                'name' => $runData['character_name'] ?? '',
                'status' => ucfirst($runData['status'] ?? 'in_progress'),
                'career_stage' => $runData['career_stage'] ?? 'junior',
                'current_turn' => $runData['current_turn'] ?? 1,
                'speed' => $runData['speed'] ?? 0,
                'stamina' => $runData['stamina'] ?? 0,
                'power' => $runData['power'] ?? 0,
                'guts' => $runData['guts'] ?? 0,
                'wit' => $runData['wit'] ?? 0,
                'mood' => $runData['mood'] ?? 'normal',
                'energy' => $runData['energy'] ?? 100,
                'notes' => $runData['notes'] ?? '',
            ]);

            // Remove from localStorage
            $this->dispatch('remove-converted-runs', ['uuids' => [$runData['uuid']]]);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Converted plan to account: '.($runData['title'] ?? 'Untitled'),
            ]);
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Failed to convert plan: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.local-data.manager');
    }
}
