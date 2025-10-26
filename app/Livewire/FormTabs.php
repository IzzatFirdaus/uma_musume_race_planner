<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Strategy;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FormTabs extends Component
{
    public $id_suffix = '';

    // Core plan fields (Livewire state)
    public $planId = null;

    public $plan_title = '';

    public $turn_before = null;

    public $name = '';

    public $race_name = '';

    public $career_stage = '';

    public $class = '';

    public $goal = '';

    public $strategy_id = null;

    public $mood_id = null;

    public $condition_id = null;

    public $energy = 0;

    public $race_day = false;

    public $acquire_skill = false;

    public $total_available_skill_points = 0;

    public $status = 'Planning';

    public $time_of_day = '';

    public $month = '';

    public $source = '';

    public $growth_rate_speed = 0;

    public $growth_rate_stamina = 0;

    public $growth_rate_power = 0;

    public $growth_rate_guts = 0;

    public $growth_rate_wit = 0;

    // Lists
    public $skills = [];

    public $predictions = [];

    public $goals = [];

    // Options for select fields
    public $careerStageOptions = [];

    public $classOptions = [];

    public function mount($id_suffix = '', $planId = null): void
    {
        $this->id_suffix = $id_suffix;
        $this->planId = $planId;

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

        // Initialize defaults for lists
        if (empty($this->skills)) {
            $this->skills = [];
        }
        if (empty($this->predictions)) {
            $this->predictions = [];
        }
        if (empty($this->goals)) {
            $this->goals = [];
        }
    }

    #[Computed]
    public function computedStrategyOptions(): array
    {
        return Strategy::all()->map(function ($strategy) {
            return [
                'id' => $strategy->id,
                'value' => $strategy->id,
                'label' => $strategy->label,
                'text' => $strategy->label,
            ];
        })->toArray();
    }

    #[Computed]
    public function computedMoodOptions(): array
    {
        return Mood::all()->map(function ($mood) {
            return [
                'id' => $mood->id,
                'value' => $mood->id,
                'label' => $mood->label,
                'text' => $mood->label,
            ];
        })->toArray();
    }

    #[Computed]
    public function computedConditionOptions(): array
    {
        return Condition::all()->map(function ($condition) {
            return [
                'id' => $condition->id,
                'value' => $condition->id,
                'label' => $condition->label,
                'text' => $condition->label,
            ];
        })->toArray();
    }

    #[On('formTabs:hydrate')]
    public function hydrateFromParent(array $data = []): void
    {
        // Map provided data keys to this component's public properties when present
        foreach ([
            'planId', 'plan_title', 'turn_before', 'name', 'race_name', 'career_stage', 'class', 'goal', 'strategy_id',
            'mood_id', 'condition_id', 'energy', 'race_day', 'acquire_skill', 'total_available_skill_points', 'status',
            'time_of_day', 'month', 'source', 'growth_rate_speed', 'growth_rate_stamina', 'growth_rate_power',
            'growth_rate_guts', 'growth_rate_wit',
        ] as $key) {
            if (array_key_exists($key, $data)) {
                $this->{$key} = $data[$key];
            }
        }

        if (isset($data['skills']) && is_array($data['skills'])) {
            $this->skills = $data['skills'];
        }
        if (isset($data['predictions']) && is_array($data['predictions'])) {
            $this->predictions = $data['predictions'];
        }
        if (isset($data['goals']) && is_array($data['goals'])) {
            $this->goals = $data['goals'];
        }
    }

    /**
     * Emit the current state so a parent component can persist it.
     */
    #[On('formTabs:requestState')]
    public function emitState(): void
    {
        $payload = [
            'planId' => $this->planId,
            'plan_title' => $this->plan_title,
            'turn_before' => $this->turn_before,
            'name' => $this->name,
            'race_name' => $this->race_name,
            'career_stage' => $this->career_stage,
            'class' => $this->class,
            'goal' => $this->goal,
            'strategy_id' => $this->strategy_id,
            'mood_id' => $this->mood_id,
            'condition_id' => $this->condition_id,
            'energy' => $this->energy,
            'race_day' => $this->race_day,
            'acquire_skill' => $this->acquire_skill,
            'total_available_skill_points' => $this->total_available_skill_points,
            'status' => $this->status,
            'time_of_day' => $this->time_of_day,
            'month' => $this->month,
            'source' => $this->source,
            'growth_rate_speed' => $this->growth_rate_speed,
            'growth_rate_stamina' => $this->growth_rate_stamina,
            'growth_rate_power' => $this->growth_rate_power,
            'growth_rate_guts' => $this->growth_rate_guts,
            'growth_rate_wit' => $this->growth_rate_wit,
            'skills' => $this->skills,
            'predictions' => $this->predictions,
            'goals' => $this->goals,
        ];

        $this->dispatch('formTabs:state', data: $payload);
    }

    // Skills handlers
    public function addSkill(): void
    {
        $this->skills[] = [
            'name' => '',
            'tag' => '',
            'acquired' => false,
            'notes' => '',
        ];
    }

    public function removeSkill(int $index): void
    {
        if (isset($this->skills[$index])) {
            array_splice($this->skills, $index, 1);
        }
    }

    // Predictions handlers
    public function addPrediction(): void
    {
        $this->predictions[] = [
            'race_name' => '',
            'venue' => '',
            'ground' => '',
            'distance' => '',
            'speed' => '',
            'stamina' => '',
            'power' => '',
            'comment' => '',
        ];
    }

    public function removePrediction(int $index): void
    {
        if (isset($this->predictions[$index])) {
            array_splice($this->predictions, $index, 1);
        }
    }

    // Goals handlers
    public function addGoalRow(): void
    {
        $this->goals[] = [
            'goal' => '',
            'result' => '',
        ];
    }

    public function removeGoalRow(int $index): void
    {
        if (isset($this->goals[$index])) {
            array_splice($this->goals, $index, 1);
        }
    }

    public function render()
    {
        return view('livewire.form-tabs', [
            'id_suffix' => $this->id_suffix,
            'careerStageOptions' => $this->careerStageOptions,
            'classOptions' => $this->classOptions,
            'strategyOptions' => $this->computedStrategyOptions(),
            'moodOptions' => $this->computedMoodOptions(),
            'conditionOptions' => $this->computedConditionOptions(),
            // Bind lists as well
            'skills' => $this->skills,
            'predictions' => $this->predictions,
            'goals' => $this->goals,
        ]);
    }
}
