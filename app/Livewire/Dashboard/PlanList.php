<?php

namespace App\Livewire\Dashboard;

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

        return view('livewire.dashboard.plan-list', [
            'plans' => $plans,
        ]);
    }
}
