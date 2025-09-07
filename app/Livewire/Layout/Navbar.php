<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use Livewire\Component;

class Navbar extends Component
{
    public function render()
    {
        return view('livewire.layout.navbar');
    }

    /**
     * Return component data as JSON (stub).
     */
    public function toJSON(): array
    {
        return [];
    }
}
