<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Plan;
use Livewire\Attributes\Computed;
use Livewire\Component;

class StatsPanel extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'total_plans' => Plan::count(),
            'active_plans' => Plan::where('status', 'Active')->count(),
            'finished_plans' => Plan::where('status', 'Finished')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.stats-panel');
    }
}
