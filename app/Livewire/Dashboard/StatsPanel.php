<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\StorageMode;
use App\Models\Plan;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Dashboard Stats Panel Component
 *
 * Displays quick statistics including storage mode breakdown.
 * Implements FR-7.11: Dashboard stats panel shows counts by storage mode.
 */
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

    /**
     * Get storage mode breakdown stats.
     * Implements FR-7.11: Show counts by storage mode (Local: X, Account: Y).
     *
     * @return array<string, int>
     */
    #[Computed]
    public function storageModeStats(): array
    {
        return [
            'local' => Plan::where('storage_mode', StorageMode::Local)->count(),
            'account' => Plan::where('storage_mode', StorageMode::Account)->count(),
        ];
    }

    /**
     * Get the percentage of local vs account plans.
     *
     * @return array<string, float>
     */
    #[Computed]
    public function storageModePercentages(): array
    {
        $total = $this->stats['total_plans'];

        if ($total === 0) {
            return ['local' => 0, 'account' => 0];
        }

        return [
            'local' => round(($this->storageModeStats['local'] / $total) * 100, 1),
            'account' => round(($this->storageModeStats['account'] / $total) * 100, 1),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.stats-panel');
    }
}
