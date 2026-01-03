<?php

declare(strict_types=1);

namespace App\Livewire\LocalData;

use App\Enums\StorageMode;
use App\Models\Plan;
use App\Services\ConvertLocalRunService;
use App\Services\ExportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Local Data Manager Page Component
 *
 * Manages locally stored plans - view, export, import, convert to account.
 * Implements FR-9D.1 through FR-9D.6.
 */
#[Layout('components.layout')]
#[Title('Local Data Management')]
class Manager extends Component
{
    use WithPagination;

    public bool $showDeleteConfirmation = false;

    public bool $showBulkConvertModal = false;

    public ?string $selectedPlanId = null;

    public string $searchQuery = '';

    /**
     * Get local plans with pagination.
     */
    #[Computed]
    public function localPlans()
    {
        $query = Plan::where('storage_mode', StorageMode::Local)
            ->orderBy('updated_at', 'desc');

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchQuery}%")
                    ->orWhere('plan_title', 'like', "%{$this->searchQuery}%");
            });
        }

        return $query->paginate(10);
    }

    /**
     * Get total local plans count.
     */
    #[Computed]
    public function totalLocalPlans(): int
    {
        return Plan::where('storage_mode', StorageMode::Local)->count();
    }

    /**
     * Get approximate storage used (rough estimate).
     */
    #[Computed]
    public function approximateStorageUsed(): string
    {
        $count = $this->totalLocalPlans;
        // Rough estimate: ~2KB per plan on average
        $bytes = $count * 2048;

        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }

    /**
     * Check if user is authenticated.
     */
    #[Computed]
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Export all local runs as JSON.
     * Implements FR-9D.3.
     */
    public function exportAllAsJson(): void
    {
        $plans = Plan::where('storage_mode', StorageMode::Local)
            ->with(['skills', 'goals', 'turns', 'racePredictions', 'careerSnapshots'])
            ->get();

        $exportData = [
            'schema_version' => '1.0.0',
            'exported_at' => now()->toIso8601String(),
            'plans' => $plans->toArray(),
        ];

        $this->dispatch('download-json', [
            'filename' => 'uma-planner-local-data-' . now()->format('Y-m-d') . '.json',
            'data' => json_encode($exportData, JSON_PRETTY_PRINT),
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Exported {$plans->count()} local plan(s) to JSON",
        ]);
    }

    /**
     * Show delete all confirmation modal.
     * Implements FR-9D.5.
     */
    public function confirmDeleteAll(): void
    {
        $this->showDeleteConfirmation = true;
    }

    /**
     * Cancel delete all.
     */
    public function cancelDeleteAll(): void
    {
        $this->showDeleteConfirmation = false;
    }

    /**
     * Delete all local runs.
     * Implements FR-9D.5.
     */
    public function deleteAllLocalRuns(): void
    {
        $count = Plan::where('storage_mode', StorageMode::Local)->count();

        Plan::where('storage_mode', StorageMode::Local)->delete();

        $this->showDeleteConfirmation = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Deleted {$count} local plan(s)",
        ]);

        unset($this->localPlans);
        unset($this->totalLocalPlans);
    }

    /**
     * Show bulk convert modal.
     * Implements FR-9D.6.
     */
    public function showBulkConvert(): void
    {
        if (!$this->isAuthenticated) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => 'Please sign in to convert local plans to your account',
            ]);
            return;
        }

        $this->showBulkConvertModal = true;
    }

    /**
     * Cancel bulk convert.
     */
    public function cancelBulkConvert(): void
    {
        $this->showBulkConvertModal = false;
    }

    /**
     * Convert all local runs to account.
     * Implements FR-9D.6.
     */
    public function convertAllToAccount(): void
    {
        if (!$this->isAuthenticated) {
            return;
        }

        $userId = Auth::id();
        $converted = 0;

        Plan::where('storage_mode', StorageMode::Local)
            ->chunk(50, function ($plans) use ($userId, &$converted) {
                foreach ($plans as $plan) {
                    $plan->update([
                        'storage_mode' => StorageMode::Account,
                        'user_id' => $userId,
                        'local_uuid' => null,
                    ]);
                    $converted++;
                }
            });

        $this->showBulkConvertModal = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Converted {$converted} plan(s) to your account",
        ]);

        unset($this->localPlans);
        unset($this->totalLocalPlans);
    }

    /**
     * Delete a single local plan.
     */
    public function deletePlan(int $planId): void
    {
        $plan = Plan::where('id', $planId)
            ->where('storage_mode', StorageMode::Local)
            ->first();

        if ($plan) {
            $plan->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Deleted plan: {$plan->name}",
            ]);

            unset($this->localPlans);
            unset($this->totalLocalPlans);
        }
    }

    /**
     * Convert a single plan to account.
     */
    public function convertToAccount(int $planId): void
    {
        if (!$this->isAuthenticated) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => 'Please sign in to convert plans to your account',
            ]);
            return;
        }

        $plan = Plan::where('id', $planId)
            ->where('storage_mode', StorageMode::Local)
            ->first();

        if ($plan) {
            $plan->update([
                'storage_mode' => StorageMode::Account,
                'user_id' => Auth::id(),
                'local_uuid' => null,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Converted plan to account: {$plan->name}",
            ]);

            unset($this->localPlans);
            unset($this->totalLocalPlans);
        }
    }

    /**
     * Update search and reset pagination.
     */
    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.local-data.manager');
    }
}
