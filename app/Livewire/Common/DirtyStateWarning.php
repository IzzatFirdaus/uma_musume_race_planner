<?php

declare(strict_types=1);

namespace App\Livewire\Common;

use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Dirty State Warning Component
 *
 * Warns users about unsaved changes before navigation.
 * Implements FR-7B.3: Dirty-state warning before close.
 */
class DirtyStateWarning extends Component
{
    public bool $isDirty = false;

    public bool $showWarning = false;

    public string $pendingAction = '';

    public array $pendingParams = [];

    /**
     * Mark form as dirty (has unsaved changes).
     */
    #[On('form-dirty')]
    public function markDirty(): void
    {
        $this->isDirty = true;
    }

    /**
     * Mark form as clean (no unsaved changes).
     */
    #[On('form-clean')]
    public function markClean(): void
    {
        $this->isDirty = false;
    }

    /**
     * Check if navigation should be blocked.
     */
    #[On('check-dirty-state')]
    public function checkDirtyState(string $action = '', array $params = []): void
    {
        if ($this->isDirty) {
            $this->pendingAction = $action;
            $this->pendingParams = $params;
            $this->showWarning = true;
        } else {
            // No unsaved changes, proceed with action
            if ($action) {
                $this->dispatch($action, ...$params);
            }
        }
    }

    /**
     * Discard changes and proceed.
     */
    public function discardAndProceed(): void
    {
        $this->isDirty = false;
        $this->showWarning = false;

        if ($this->pendingAction) {
            $this->dispatch($this->pendingAction, ...$this->pendingParams);
        }

        $this->pendingAction = '';
        $this->pendingParams = [];
    }

    /**
     * Cancel and stay on page.
     */
    public function cancelNavigation(): void
    {
        $this->showWarning = false;
        $this->pendingAction = '';
        $this->pendingParams = [];
    }

    public function render()
    {
        return view('livewire.common.dirty-state-warning');
    }
}
