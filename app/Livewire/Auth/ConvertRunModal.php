<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Enums\StorageMode;
use App\Models\Plan;
use App\Services\DuplicateDetectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Convert Run Modal Component
 *
 * Modal for converting a single local run to account storage.
 * Implements FR-9C.1: "Convert to Account" action available on Local runs.
 */
class ConvertRunModal extends Component
{
    public bool $show = false;

    public ?int $planId = null;

    public bool $keepLocalCopy = false;

    public bool $hasDuplicate = false;

    public ?array $duplicateInfo = null;

    public string $duplicateAction = 'create'; // 'create' or 'cancel'

    #[On('open-convert-modal')]
    public function openModal(int $planId): void
    {
        $this->planId = $planId;
        $this->keepLocalCopy = false;
        $this->hasDuplicate = false;
        $this->duplicateInfo = null;
        $this->duplicateAction = 'create';

        // Check for duplicates
        $this->checkForDuplicates();

        $this->show = true;
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->planId = null;
    }

    #[Computed]
    public function plan(): ?Plan
    {
        return $this->planId ? Plan::find($this->planId) : null;
    }

    #[Computed]
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Check for duplicate plans in account.
     * Implements FR-9C.4: Duplicate detection.
     */
    protected function checkForDuplicates(): void
    {
        if (!$this->plan) {
            return;
        }

        // Check for similar plans (same title + character + created date)
        $existingPlan = Plan::where('storage_mode', StorageMode::Account)
            ->where('user_id', Auth::id())
            ->where('name', $this->plan->name)
            ->where('plan_title', $this->plan->plan_title)
            ->whereDate('created_at', $this->plan->created_at?->toDateString())
            ->first();

        if ($existingPlan) {
            $this->hasDuplicate = true;
            $this->duplicateInfo = [
                'id' => $existingPlan->id,
                'name' => $existingPlan->name,
                'title' => $existingPlan->plan_title,
                'created_at' => $existingPlan->created_at?->format('M j, Y'),
            ];
        }
    }

    /**
     * Convert the plan to account storage.
     * Implements FR-9C.1, FR-9C.2, FR-9C.7, FR-9C.8.
     */
    public function convert(): void
    {
        if (!$this->isAuthenticated || !$this->plan) {
            return;
        }

        // If duplicate exists and user chose cancel, close modal
        if ($this->hasDuplicate && $this->duplicateAction === 'cancel') {
            $this->closeModal();
            return;
        }

        $planName = $this->plan->name;

        // Update the plan to account storage
        $this->plan->update([
            'storage_mode' => StorageMode::Account,
            'user_id' => Auth::id(),
            'local_uuid' => $this->keepLocalCopy ? $this->plan->local_uuid : null,
        ]);

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => "Converted '{$planName}' to your account",
        ]);

        $this->dispatch('plan-converted', planId: $this->planId);
        $this->dispatch('refreshPlans');

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.auth.convert-run-modal');
    }
}
