<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class RecentActivity extends Component
{
    protected array $activities = [];

    public function getActivities(): array
    {
        return $this->activities;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.dashboard.recent-activity', [
            'activities' => $this->activities,
        ]);
    }
}
