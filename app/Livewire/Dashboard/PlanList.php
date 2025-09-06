<?php

namespace App\Livewire\Dashboard;

use App\Models\ActivityLog;
use App\Models\Plan;
use Livewire\Component;

class PlanList extends Component
{
    public string $currentFilter = 'all';

    public function setFilter(string $filter): void
    {
        $this->currentFilter = $filter;
        // Force component re-render
        $this->dispatch('refreshPlans');
    }

    public function viewPlan(int $planId): void
    {
        // Dispatch an event to open the plan details modal
        $this->dispatch('openPlanModal', planId: $planId);
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
                'timestamp' => now()
            ]);

            // Dispatch success event for SweetAlert2
            $this->dispatch('plan-deleted', ['message' => "Plan '{$planName}' deleted successfully!"]);

        } catch (\Exception $e) {
            // Dispatch error event for SweetAlert2
            $this->dispatch('plan-error', ['message' => 'Failed to delete plan: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $query = Plan::with([
            'attributes' => fn ($query) => $query->whereIn('attribute_name', ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT']),
            'mood',
            'condition',
            'strategy'
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
            'plans' => $plans,
            'counts' => $counts,
        ]);
    }
}
