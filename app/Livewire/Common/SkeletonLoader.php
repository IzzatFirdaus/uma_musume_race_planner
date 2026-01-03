<?php

declare(strict_types=1);

namespace App\Livewire\Common;

use Livewire\Component;

/**
 * Skeleton Loader Component
 *
 * Displays skeleton loading placeholders for lazy-loaded content.
 * Implements NFR-1.6: Skeleton loaders during async operations.
 */
class SkeletonLoader extends Component
{
    public string $type = 'text'; // text, card, table, avatar, button

    public int $lines = 3;

    public string $width = '100%';

    public string $height = 'auto';

    public function mount(
        string $type = 'text',
        int $lines = 3,
        string $width = '100%',
        string $height = 'auto'
    ): void {
        $this->type = $type;
        $this->lines = $lines;
        $this->width = $width;
        $this->height = $height;
    }

    public function render()
    {
        return view('livewire.common.skeleton-loader');
    }
}
