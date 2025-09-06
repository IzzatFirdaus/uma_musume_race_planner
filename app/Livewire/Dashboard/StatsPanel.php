<?php

namespace App\Livewire\Dashboard;

use App\Models\Plan;
use Livewire\Component;

class StatsPanel extends Component
{
    /**
     * @var array<string, int>
     */
    protected $stats = [];

    public function getStats(): array
    {
        return $this->stats;
    }

    public function getStatsProperty()
    {
        return [
            'total_plans' => Plan::count(),
            'active_plans' => Plan::where('status', 'Active')->count(),
            'finished_plans' => Plan::where('status', 'Finished')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.stats-panel', [
            'stats' => $this->stats,
        ]);
    }
}
