<?php

declare(strict_types=1);

namespace App\Livewire\CareerRun;

use App\Enums\StorageMode;
use Livewire\Component;

/**
 * Storage Mode Indicator Component
 *
 * Displays a badge indicating whether a plan is stored locally or in account.
 * Implements FR-7.8: Storage Mode badge on each run.
 */
class StorageModeIndicator extends Component
{
    public ?StorageMode $storageMode = null;

    public bool $showLabel = true;

    public string $size = 'sm'; // sm, md, lg

    public function mount(?StorageMode $storageMode = null, bool $showLabel = true, string $size = 'sm'): void
    {
        $this->storageMode = $storageMode;
        $this->showLabel = $showLabel;
        $this->size = $size;
    }

    public function render()
    {
        return view('livewire.career-run.storage-mode-indicator');
    }
}
