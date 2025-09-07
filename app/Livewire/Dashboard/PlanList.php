<?php

namespace App\Livewire\Dashboard;

use App\Models\ActivityLog;
use App\Models\Plan;
use Livewire\Component;

class PlanList extends Component
{
    // Called by wire:click="setFilter('...')" in Blade
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
    public string $currentFilter = 'all';

    public function filterPlansByStatus(string $filter): void
    {
        $this->currentFilter = $filter;
        $this->dispatch('refreshPlans');
    }

    public function viewPlan(int $planId): void
    {
        // Dispatch an event to open the inline plan details view
        $this->dispatch('openPlanInline', planId: $planId);
    }

    public function editPlan(int $planId): void
    {
        // Dispatch an event to open the plan edit modal
        $this->dispatch('openPlanEditModal', planId: $planId);
    }

    public function deletePlan($id)
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
        $query = Plan::with([
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

        $plans = $query->get();

        // Calculate counts to avoid repeated queries in the view
        $counts = [
            'total' => Plan::count(),
            'active' => Plan::where('status', 'Active')->count(),
            'planning' => Plan::where('status', 'Planning')->count(),
            'finished' => Plan::where('status', 'Finished')->count(),
        ];

        return view('livewire.dashboard.plan-list', [
            // Use authentic Umamusume: Pretty Derby global server terminology in UI
            'plans' => $plans,
            'counts' => $counts,
        ]);
    }
}
