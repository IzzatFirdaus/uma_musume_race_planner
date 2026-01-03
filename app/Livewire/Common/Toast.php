<?php

declare(strict_types=1);

namespace App\Livewire\Common;

use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Toast Notification Component
 *
 * Displays toast notifications with aria-live announcements.
 * Implements FR-13.4: Toast notifications with aria-live.
 */
class Toast extends Component
{
    public array $toasts = [];

    public int $duration = 5000; // Default 5 seconds

    #[On('toast')]
    public function addToast(string $type = 'info', string $message = '', int $duration = 0): void
    {
        $id = uniqid('toast_');

        $this->toasts[] = [
            'id' => $id,
            'type' => $type,
            'message' => $message,
            'duration' => $duration > 0 ? $duration : $this->duration,
            'icon' => $this->getIcon($type),
            'class' => $this->getClass($type),
        ];

        // Auto-dismiss after duration
        $this->dispatch('toast-added', id: $id, duration: $duration > 0 ? $duration : $this->duration);
    }

    public function removeToast(string $id): void
    {
        $this->toasts = array_filter($this->toasts, fn($toast) => $toast['id'] !== $id);
    }

    protected function getIcon(string $type): string
    {
        return match ($type) {
            'success' => 'bi-check-circle-fill',
            'error', 'danger' => 'bi-x-circle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            default => 'bi-info-circle-fill',
        };
    }

    protected function getClass(string $type): string
    {
        return match ($type) {
            'success' => 'bg-success text-white',
            'error', 'danger' => 'bg-danger text-white',
            'warning' => 'bg-warning text-dark',
            default => 'bg-info text-white',
        };
    }

    public function render()
    {
        return view('livewire.common.toast');
    }
}
