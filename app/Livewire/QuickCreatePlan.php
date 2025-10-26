<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

class QuickCreatePlan extends Component
{
    public $trainee_name = '';

    public $race_name = '';

    public $career_stage = '';

    public $traineeClass = '';

    public $careerStageOptions = [];

    public $classOptions = [];

    public function mount(): void
    {
        $this->careerStageOptions = [
            ['value' => 'predebut', 'text' => 'Pre-Debut'],
            ['value' => 'junior', 'text' => 'Junior Year'],
            ['value' => 'classic', 'text' => 'Classic Year'],
            ['value' => 'senior', 'text' => 'Senior Year'],
            ['value' => 'finale', 'text' => 'Finale Season'],
        ];

        $this->classOptions = [
            ['value' => 'debut', 'text' => 'Debut'],
            ['value' => 'maiden', 'text' => 'Maiden'],
            ['value' => 'beginner', 'text' => 'Beginner'],
            ['value' => 'bronze', 'text' => 'Bronze'],
            ['value' => 'silver', 'text' => 'Silver'],
            ['value' => 'gold', 'text' => 'Gold'],
            ['value' => 'platinum', 'text' => 'Star'],
            ['value' => 'legend', 'text' => 'Legend'],
        ];
    }

    public function save(): void
    {
        $this->validate([
            'trainee_name' => 'required|string|max:255',
            'race_name' => 'nullable|string|max:255',
            'career_stage' => 'required|string',
            'traineeClass' => 'required|string',
        ]);

        \App\Models\Plan::create([
            'user_id' => 1, // Public / guest user
            'name' => $this->trainee_name,
            'plan_title' => $this->trainee_name."'s New Plan",
            'race_name' => $this->race_name,
            'career_stage' => $this->career_stage,
            'class' => $this->traineeClass,
            'status' => 'Planning',
        ]);

        $this->trainee_name = '';
        $this->race_name = '';
        $this->career_stage = '';
        $this->traineeClass = '';

        // Notify frontend listeners (Livewire + vanilla JS) to update UI
        $this->dispatch('plan-created', ['message' => 'Plan created successfully!']);
        $this->dispatch('plan-updated', ['message' => 'Plan created successfully!']);
        $this->dispatch('refreshPlans');
    }

    public function render()
    {
        return view('livewire.quick-create-plan', [
            'careerStageOptions' => $this->careerStageOptions,
            'classOptions' => $this->classOptions,
        ]);
    }
}
