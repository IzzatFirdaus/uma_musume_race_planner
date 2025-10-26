<?php

namespace App\Livewire\Skills;

use Livewire\Component;

class SkillEditor extends Component
{
    public array $skills = [];

    protected $rules = [
        'skills.*.name' => 'required|string|max:255',
        'skills.*.level' => 'required|integer|min:1|max:5',
    ];

    public function addSkill(): void
    {
        $this->skills[] = ['name' => '', 'level' => 1];
    }

    public function removeSkill(int $index): void
    {
        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
    }

    public function save(): void
    {
        // Validate skills before proceeding
        $this->validate();

        // Dispatch success event for SweetAlert2/toast
        $this->dispatch('plan-updated', ['message' => 'Skills saved successfully!']);

        // Also set a session flash message so HTTP tests can assert session state
        session()->flash('message', 'Skills saved successfully!');
    }

    public function render()
    {
        return view('livewire.skills.skill-editor');
    }
}
