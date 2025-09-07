<?php

namespace App\Livewire;

use Livewire\Component;

class QuickCreatePlan extends Component
{
    public $careerStageOptions = [];

    public $classOptions = [];

    public function mount()
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

    public function render()
    {
        return view('livewire.quick-create-plan', [
            'careerStageOptions' => $this->careerStageOptions,
            'classOptions' => $this->classOptions,
        ]);
    }
}
