<?php

declare(strict_types=1);

namespace App\Livewire\CareerRun;

use App\Enums\StorageMode;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Convert to Account Button Component
 *
 * Button to convert a local plan to account storage.
 * Implements FR-7.9: "Convert to Account" action on Local runs.
 */
class ConvertToAccountButton extends Component
{
    public int $planId;

    public string $size = 'sm'; // sm, md, lg

    public bool $showLabel = true;

    public function mount(int $planId, string $size = 'sm', bool $showLabel = true): void
    {
        $this->planId = $planId;
        $this->size = $size;
        $this->showLabel = $showLabel;
    }

    #[Computed]
    public function plan(): ?Plan
    {
        return Plan::find($this->planId);
    }

    #[Computed]
    public function isVisible(): bool
    {
        // Only visible when authenticated and plan is local
        return Auth::check()
            && $this->plan
            && $this->plan->storage_mode === StorageMode::Local;
    }

    #[Computed]
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Open the convert modal.
     */
    public function openConvertModal(): void
    {
        if (!$this->isAuthenticated) {
            $this->dispatch('toast', [
                'type' => 'warning',
                'message' => 'Please sign in to convert plans to your account',
            ]);
            return;
        }

        $this->dispatch('open-convert-modal', planId: $this->planId);
    }

    /**
     * Quick convert without modal (for simple cases).
     */
    public function convertNow(): void
    {
        if (!$this->isAuthenticated || !$this->plan) {
            return;
        }

        $this->plan->update([
            'storage_mode' => StorageMode::Account,
            'user_id' => Auth::id(),
            'local_uuid' => null,
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Converted '{$this->plan->name}' to your account",
        ]);

        $this->dispatch('plan-converted', planId: $this->planId);
    }

    public function render()
    {
        return view('livewire.career-run.convert-to-account-button');
    }
}
