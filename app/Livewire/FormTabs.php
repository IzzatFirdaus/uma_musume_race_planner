<?php

namespace App\Livewire;

use Livewire\Component;

class FormTabs extends Component
{
    public $id_suffix = '';

    public function mount($id_suffix = '')
    {
        $this->id_suffix = $id_suffix;
    }

    public function render()
    {
        return view('livewire.form-tabs', [
            'id_suffix' => $this->id_suffix
        ]);
    }
}
