<?php

declare(strict_types=1);

namespace App\Livewire\Common;

use Livewire\Component;

/**
 * Dark Mode Toggle Component
 *
 * Toggle button for dark/light mode with localStorage persistence.
 * Implements FR-7.1: Dark/light mode toggle with persistence.
 */
class DarkModeToggle extends Component
{
    public string $mode = 'light';

    public function mount(): void
    {
        // Mode is set via JavaScript from localStorage/system preference
        $this->mode = 'light';
    }

    /**
     * Toggle dark mode.
     */
    public function toggle(): void
    {
        $this->mode = $this->mode === 'dark' ? 'light' : 'dark';
        $this->dispatch('dark-mode-changed', mode: $this->mode);
    }

    /**
     * Set mode explicitly.
     */
    public function setMode(string $mode): void
    {
        $this->mode = in_array($mode, ['dark', 'light']) ? $mode : 'light';
        $this->dispatch('dark-mode-changed', mode: $this->mode);
    }

    public function render()
    {
        return view('livewire.common.dark-mode-toggle');
    }
}
