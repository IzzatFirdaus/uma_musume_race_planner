<?php

declare(strict_types=1);

namespace App\Livewire\Common;

use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Confirm Modal Component
 *
 * Reusable confirmation dialog with focus trap.
 * Implements NFR-2.6: Focus trap for modals.
 */
class ConfirmModal extends Component
{
    public bool $show = false;

    public string $title = 'Confirm Action';

    public string $message = 'Are you sure you want to proceed?';

    public string $confirmText = 'Confirm';

    public string $cancelText = 'Cancel';

    public string $confirmClass = 'btn-primary';

    public string $icon = 'bi-question-circle';

    public string $confirmEvent = '';

    public array $confirmParams = [];

    #[On('show-confirm-modal')]
    public function showModal(
        string $title = 'Confirm Action',
        string $message = 'Are you sure you want to proceed?',
        string $confirmText = 'Confirm',
        string $cancelText = 'Cancel',
        string $confirmClass = 'btn-primary',
        string $icon = 'bi-question-circle',
        string $confirmEvent = '',
        array $confirmParams = []
    ): void {
        $this->title = $title;
        $this->message = $message;
        $this->confirmText = $confirmText;
        $this->cancelText = $cancelText;
        $this->confirmClass = $confirmClass;
        $this->icon = $icon;
        $this->confirmEvent = $confirmEvent;
        $this->confirmParams = $confirmParams;
        $this->show = true;
    }

    public function confirm(): void
    {
        if ($this->confirmEvent) {
            $this->dispatch($this->confirmEvent, ...$this->confirmParams);
        }
        $this->dispatch('confirm-modal-confirmed');
        $this->close();
    }

    public function close(): void
    {
        $this->show = false;
        $this->dispatch('confirm-modal-closed');
    }

    public function render()
    {
        return view('livewire.common.confirm-modal');
    }
}
