<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\StorageMode;
use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\Strategy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PlanList extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    // Status filter: 'all', 'Active', 'Planning', 'Finished'
    public string $currentFilter = 'all';

    // Storage mode filter: 'all', 'local', 'account' (Req 56.4)
    public string $storageModeFilter = 'all';

    // Strategy filter: null or strategy_id (Req 3.1)
    public ?int $strategyFilter = null;

    protected string $paginationTheme = 'bootstrap';

    // Called by wire:click="setFilter('...')" in Blade
    #[On('filterPlansByStatus')]
    public function setFilter(string $filter): void
    {
        $this->filterPlansByStatus($filter);
    }

    public function filterPlansByStatus(string $filter): void
    {
        $this->currentFilter = $filter;
        $this->resetPage();
    }

    /**
     * Set storage mode filter (Req 56.4)
     */
    public function setStorageModeFilter(string $mode): void
    {
        $this->storageModeFilter = $mode;
        $this->resetPage();
    }

    /**
     * Set strategy filter (Req 3.1)
     */
    public function setStrategyFilter(?int $strategyId): void
    {
        $this->strategyFilter = $strategyId;
        $this->resetPage();
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->currentFilter = 'all';
        $this->storageModeFilter = 'all';
        $this->strategyFilter = null;
        $this->resetPage();
    }

    #[On('refreshPlans')]
    public function refreshPlans(): void
    {
        // Re-render with latest data and ensure we return to page 1
        $this->resetPage();
    }

    #[On('plan-updated')]
    public function onPlanUpdated(): void
    {
        $this->resetPage();
    }

    public function viewPlan(int $planId): void
    {
        // Redirect to the new Plan View page (read-only mode)
        $this->redirect(route('plans.view', $planId));
    }

    public function editPlan(int $planId): void
    {
        // Redirect to the new Plan Edit page (edit mode)
        $this->redirect(route('plans.edit', $planId));
    }

    /**
     * Duplicate a plan (Req 3.4)
     */
    #[On('duplicatePlan')]
    public function duplicatePlan(int $id): void
    {
        try {
            $plan = Plan::findOrFail($id);
            $newPlan = $plan->replicate();
            $newPlan->name = $plan->name.' (Copy)';
            $newPlan->status = 'Planning';
            $newPlan->save();

            // Log the duplication
            ActivityLog::create([
                'description' => "Duplicated plan: {$plan->name}",
                'icon_class' => 'bi-copy',
                'timestamp' => now(),
            ]);

            $this->dispatch('plan-duplicated', ['message' => "Plan '{$plan->name}' duplicated successfully!"]);
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('plan-error', ['message' => 'Failed to duplicate plan: '.$e->getMessage()]);
        }
    }

    #[On('deletePlan')]
    public function deletePlan(int $id): void
    {
        try {
            $plan = Plan::findOrFail($id);
            $planName = $plan->name;

            $plan->delete();

            // Log the deletion
            ActivityLog::create([
                'description' => "Deleted plan: {$planName}",
                'icon_class' => 'bi-trash',
                'timestamp' => now(),
            ]);

            // Dispatch success event for SweetAlert2
            $this->dispatch('plan-deleted', ['message' => "Plan '{$planName}' deleted successfully!"]);
        } catch (\Exception $e) {
            // Dispatch error event for SweetAlert2
            $this->dispatch('plan-error', ['message' => 'Failed to delete plan: '.$e->getMessage()]);
        }
    }

    /**
     * Get all strategies for filter dropdown (Req 3.1)
     */
    #[Computed]
    public function strategies(): \Illuminate\Database\Eloquent\Collection
    {
        return Strategy::orderBy('label')->get();
    }

    /**
     * Get storage mode counts for filter badges (Req 56.4)
     */
    #[Computed]
    public function storageCounts(): array
    {
        return [
            'all' => Plan::count(),
            'local' => Plan::where('storage_mode', StorageMode::Local)->count(),
            'account' => Plan::where('storage_mode', StorageMode::Account)->count(),
        ];
    }

    /**
     * Check if any filters are active
     */
    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->currentFilter !== 'all'
            || $this->storageModeFilter !== 'all'
            || $this->strategyFilter !== null;
    }

    public function render()
    {
        $query = Plan::query()
            ->with([
                'attributes' => fn ($query) => $query->whereIn('attribute_name', ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT']),
                'mood',
                'condition',
                'strategy',
            ])->orderByDesc('id');

        // Apply status filter
        if ($this->currentFilter !== 'all') {
            $query->where('status', $this->currentFilter);
        }

        // Apply storage mode filter (Req 56.4)
        if ($this->storageModeFilter !== 'all') {
            $query->where('storage_mode', $this->storageModeFilter);
        }

        // Apply strategy filter (Req 3.1)
        if ($this->strategyFilter !== null) {
            $query->where('strategy_id', $this->strategyFilter);
        }

        $plans = $query->paginate(10);

        // If there are no plans, create a single seeded plan for non-production
        // environments so E2E test runs (Playwright) can rely on a predictable
        // initial state. We avoid auto-creating sample data in production.
        if ($plans->count() === 0 && ! app()->environment('production') && ! $this->hasActiveFilters) {
            Plan::factory()->create([
                'career_stage' => 'junior',
                'class' => 'beginner',
                'status' => 'Planning',
                'acquire_skill' => 'NO',
            ]);

            // Re-run the query after seeding
            $plans = $query->paginate(10);
        }

        return view('livewire.dashboard.plan-list', [
            'plans' => $plans,
            'counts' => $this->planCounts(),
            'storageCounts' => $this->storageCounts(),
            'strategies' => $this->strategies(),
            'hasActiveFilters' => $this->hasActiveFilters,
        ]);
    }

    #[Computed]
    public function planCounts(): array
    {
        return [
            'total' => Plan::count(),
            'active' => Plan::where('status', 'Active')->count(),
            'planning' => Plan::where('status', 'Planning')->count(),
            'finished' => Plan::where('status', 'Finished')->count(),
        ];
    }
}
