<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\StorageMode;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Dashboard Header Banner Component
 *
 * Displays welcome message and quick actions including Local Data management.
 * Implements FR-7.10: Local Data management entry point.
 */
class HeaderBanner extends Component
{
    /**
     * Get the current user's name.
     */
    #[Computed]
    public function userName(): ?string
    {
        return Auth::user()?->name;
    }

    /**
     * Get total plans count.
     */
    #[Computed]
    public function totalPlans(): int
    {
        return Plan::count();
    }

    /**
     * Get local plans count for the badge.
     */
    #[Computed]
    public function localPlansCount(): int
    {
        return Plan::where('storage_mode', StorageMode::Local)->count();
    }

    /**
     * Check if user is authenticated.
     */
    #[Computed]
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    public function render()
    {
        return view('livewire.dashboard.header-banner');
    }
}
