<?php

namespace App\Livewire;

use App\Models\Mood;
use App\Models\Strategy;
use App\Models\Condition;
use Livewire\Component;

class FormTabs extends Component
{
    public $id_suffix = '';
    // Options for select fields
    public $careerStageOptions = [];
    public $classOptions = [];
    public $strategyOptions = [];
    public $moodOptions = [];
    public $conditionOptions = [];

    public function mount($id_suffix = '')
    {
        $this->id_suffix = $id_suffix;
        
        // Load options for select fields
        $this->careerStageOptions = [
            ['value' => 'predebut', 'text' => 'Pre-debut'],
            ['value' => 'junior', 'text' => 'Junior'],
            ['value' => 'classic', 'text' => 'Classic'],
            ['value' => 'senior', 'text' => 'Senior'],
            ['value' => 'finale', 'text' => 'Finale'],
        ];
        
        $this->classOptions = [
            ['value' => 'debut', 'text' => 'Debut'],
            ['value' => 'maiden', 'text' => 'Maiden'],
            ['value' => 'beginner', 'text' => 'Beginner'],
            ['value' => 'bronze', 'text' => 'Bronze'],
            ['value' => 'silver', 'text' => 'Silver'],
            ['value' => 'gold', 'text' => 'Gold'],
            ['value' => 'platinum', 'text' => 'Platinum'],
            ['value' => 'star', 'text' => 'Star'],
            ['value' => 'legend', 'text' => 'Legend'],
        ];
        
        $this->strategyOptions = Strategy::all()->map(function($strategy) {
            return [
                'id' => $strategy->id,
                'value' => $strategy->id,
                'label' => $strategy->label,
                'text' => $strategy->label
            ];
        })->toArray();
        
        $this->moodOptions = Mood::all()->map(function($mood) {
            return [
                'id' => $mood->id,
                'value' => $mood->id,
                'label' => $mood->label,
                'text' => $mood->label
            ];
        })->toArray();
        
        $this->conditionOptions = Condition::all()->map(function($condition) {
            return [
                'id' => $condition->id,
                'value' => $condition->id,
                'label' => $condition->label,
                'text' => $condition->label
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.form-tabs', [
            'id_suffix' => $this->id_suffix,
            'careerStageOptions' => $this->careerStageOptions,
            'classOptions' => $this->classOptions,
            'strategyOptions' => $this->strategyOptions,
            'moodOptions' => $this->moodOptions,
            'conditionOptions' => $this->conditionOptions,
        ]);
    }
}
