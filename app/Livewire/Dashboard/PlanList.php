<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\ActivityLog;
use App\Models\Plan;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PlanList extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Called by wire:click="setFilter('...')" in Blade
    public function mount(): void
    {
        $this->planCounts = $this->planCounts();
    }
    public function setFilter(string $filter): void
    {
        $this->filterPlansByStatus($filter);
    }

    // Career Mode: 72 half-month turns, training, racing, rest, recreation, skill acquisition, mood, energy, fans, goals, rebirth/veteran unlocks.
    // Support cards and veteran system: Trainers select support cards and two veteran Umamusume for stat/skill boosts per Career.
    // Skills: Use official categories (Speed, Acceleration, Recovery, Passive, Debuff, Starting Gate, Lane Change, Observation) and activation conditions (style, distance, position, stamina, timing).
    // Skill activation probability is influenced by Wit stat.
    // Daily reset: 12:00 AM JST (global server matches JP schedule).
    // Gacha: Paid Carats, Scout banners, Goddess Statues for star piece exchange, as per global mechanics.
    // Platform: iOS, Android, PC (Steam), cross-platform link, global events match JP.
    // Resource usage: Steam ~11 GB, Mobile ~6 GB.
    // UI and logic must use authentic terms: “Career Mode”, “Skill Points (SP)”, “Mood”, “Energy”, “Fans”, “Support Cards”, “Veteran”, “Rebirth”, “Scouts”, “Goddess Statue”.
    /**
     * Current filter for plan status
     * @var string
     */
    public string $currentFilter = 'all';

    /**
     * Plan counts for dashboard summary (status breakdown, etc.)
     * @var array<string, int>
     */
    public array $planCounts = [];

    public function filterPlansByStatus(string $filter): void
    {
        $this->currentFilter = $filter;
        $this->dispatch('refreshPlans');
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

    public function render()
    {
        $query = Plan::query()
            ->with([
                // Use official stat names and relationships for Umamusume: Pretty Derby global server
                'attributes' => fn ($query) => $query->whereIn('attribute_name', ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT']),
                'mood',
                'condition',
                'strategy',
                // 'supportCards' and 'veteranUmaMusume' removed due to missing model/relationship
            ])->latest();

        if ($this->currentFilter !== 'all') {
            $query->where('status', $this->currentFilter);
        }

        $plans = $query->paginate(10);

        return view('livewire.dashboard.plan-list', [
            // Use authentic Umamusume: Pretty Derby global server terminology in UI
            'plans' => $plans,
            'counts' => $this->planCounts,
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
