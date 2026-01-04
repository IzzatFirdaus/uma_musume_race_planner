<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Condition;
use App\Models\Mood;
use App\Models\Strategy;
use App\Models\Umamusume;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * FormTabs Component
 *
 * Tabbed form interface for plan editing with keyboard navigation.
 * Requirements: 4.2, 5.2, 29.1 - Tabbed interface with keyboard navigation
 * Requirements: 31.1, 31.2, 31.3, 32.1, 32.2 - Mood, condition, energy display
 */
class FormTabs extends Component
{
    public $id_suffix = '';

    // Whether the parent is in edit mode (passed from PlanDetailsPage)
    public $isEditMode = false;

    // Core plan fields (Livewire state)
    public $planId = null;

    public ?string $localUuid = null;

    public string $storageMode = 'account';

    public ?string $traineeImagePath = null;

    public $plan_title = '';

    public $turn_before = null;

    public $name = '';

    public $umamusume_id = null; // Character selector (Req 4.3)

    public $race_name = '';

    public $career_stage = '';

    public $class = '';

    public $goal = '';

    public $strategy_id = null;

    public $mood_id = null;

    public $condition_id = null;

    // Multiple conditions support (Req 31.3)
    public array $selected_conditions = [];

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

    // Attributes and grade arrays
    public $planAttributes = [];

    public $terrainGrades = [];

    public $distanceGrades = [];

    public $styleGrades = [];

    // Options for select fields
    public $careerStageOptions = [];

    public $classOptions = [];

    // Mood percentage mapping (Req 31.1, 31.2)
    public const MOOD_PERCENTAGES = [
        'GREAT' => '+4%',
        'GOOD' => '+2%',
        'NORMAL' => '0%',
        'BAD' => '-2%',
        'AWFUL' => '-4%',
    ];

    // Condition type mapping (Req 31.3, 31.4) - positive vs negative
    public const CONDITION_TYPES = [
        'CHARMING' => 'positive',
        'HOT TOPIC' => 'positive',
        'SPRING BUD' => 'positive',
        'MIGRAINE' => 'negative',
        'DRY SKIN' => 'negative',
        'INSOMNIA' => 'negative',
        'SLOW METABOLISM' => 'negative',
        'SLACKER' => 'negative',
        'UNDER THE WEATHER' => 'negative',
        'SUSPICIOUS CLOUDS' => 'negative',
        'N/A' => 'neutral',
    ];

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

    /**
     * Get character options for selector (Req 4.3).
     *
     * @return array<int, array{id: string, value: string, label: string, text: string}>
     */
    #[Computed]
    public function computedCharacterOptions(): array
    {
        return Umamusume::orderBy('name')->get()->map(fn ($uma) => [
            'id' => $uma->id,
            'value' => $uma->id,
            'label' => $uma->name,
            'text' => $uma->name,
        ])->toArray();
    }

    #[Computed]
    public function computedStrategyOptions(): array
    {
        return Strategy::all()->map(fn ($strategy) => [
            'id' => $strategy->id,
            'value' => $strategy->id,
            'label' => $strategy->label,
            'text' => $strategy->label,
        ])->toArray();
    }

    /**
     * Get mood options with percentage display (Req 31.1, 31.2).
     *
     * @return array<int, array{id: int, value: int, label: string, text: string, percentage: string}>
     */
    #[Computed]
    public function computedMoodOptions(): array
    {
        return Mood::all()->map(fn ($mood) => [
            'id' => $mood->id,
            'value' => $mood->id,
            'label' => $mood->label,
            'text' => $mood->label,
            'percentage' => self::MOOD_PERCENTAGES[\strtoupper($mood->label)] ?? '0%',
        ])->toArray();
    }

    /**
     * Get condition options with positive/negative type (Req 31.3, 31.4).
     *
     * @return array<int, array{id: int, value: int, label: string, text: string, type: string}>
     */
    #[Computed]
    public function computedConditionOptions(): array
    {
        return Condition::all()->map(fn ($condition) => [
            'id' => $condition->id,
            'value' => $condition->id,
            'label' => $condition->label,
            'text' => $condition->label,
            'type' => self::CONDITION_TYPES[\strtoupper($condition->label)] ?? 'neutral',
        ])->toArray();
    }

    /**
     * Get energy color class based on level (Req 32.1, 32.2).
     */
    #[Computed]
    public function energyColorClass(): string
    {
        $energy = (int) $this->energy;
        if ($energy > 50) {
            return 'bg-success'; // Green
        }
        if ($energy >= 30) {
            return 'bg-warning'; // Yellow
        }

        return 'bg-danger'; // Red
    }

    /**
     * Get energy warning message (Req 32.3).
     */
    #[Computed]
    public function energyWarning(): ?string
    {
        if ((int) $this->energy < 30) {
            return 'Warning: High training failure risk!';
        }

        return null;
    }

    #[On('formTabs:hydrate')]
    public function hydrateFromParent(array $data = []): void
    {
        // Map provided data keys to this component's public properties when present
        foreach (
            [
                'planId',
                'localUuid',
                'storageMode',
                'traineeImagePath',
                'plan_title',
                'turn_before',
                'name',
                'umamusume_id',
                'race_name',
                'career_stage',
                'class',
                'goal',
                'strategy_id',
                'mood_id',
                'condition_id',
                'energy',
                'race_day',
                'acquire_skill',
                'total_available_skill_points',
                'status',
                'time_of_day',
                'month',
                'source',
                'growth_rate_speed',
                'growth_rate_stamina',
                'growth_rate_power',
                'growth_rate_guts',
                'growth_rate_wit',
            ] as $key
        ) {
            if (\array_key_exists($key, $data)) {
                $this->{$key} = $data[$key];
            }
        }

        // Handle selected_conditions array
        if (isset($data['selected_conditions']) && \is_array($data['selected_conditions'])) {
            $this->selected_conditions = $data['selected_conditions'];
        }

        if (isset($data['skills']) && \is_array($data['skills'])) {
            $this->skills = $data['skills'];
        }
        if (isset($data['predictions']) && \is_array($data['predictions'])) {
            $this->predictions = $data['predictions'];
        }
        if (isset($data['goals']) && \is_array($data['goals'])) {
            $this->goals = $data['goals'];
        }
        if (isset($data['planAttributes']) && \is_array($data['planAttributes'])) {
            $this->planAttributes = $data['planAttributes'];
        }
        if (isset($data['terrainGrades']) && \is_array($data['terrainGrades'])) {
            $this->terrainGrades = $data['terrainGrades'];
        }
        if (isset($data['distanceGrades']) && \is_array($data['distanceGrades'])) {
            $this->distanceGrades = $data['distanceGrades'];
        }
        if (isset($data['styleGrades']) && \is_array($data['styleGrades'])) {
            $this->styleGrades = $data['styleGrades'];
        }
    }

    /**
     * Notify parent that form has been modified (Req 5.3, 76.4, 76.5).
     */
    public function updated(string $property): void
    {
        // Emit dirty state to parent component
        $this->dispatch('formTabs:dirty');
    }

    /**
     * Handle trainee image saved event from TraineeImageHandler component.
     * Requirements: 17.3 - Store image and associate with plan
     */
    #[On('trainee-image-saved')]
    public function onTraineeImageSaved(array $payload): void
    {
        $this->traineeImagePath = $payload['path'] ?? null;
        $this->dispatch('formTabs:dirty');
    }

    /**
     * Handle trainee image cleared event from TraineeImageHandler component.
     */
    #[On('trainee-image-cleared')]
    public function onTraineeImageCleared(): void
    {
        $this->traineeImagePath = null;
        $this->dispatch('formTabs:dirty');
    }

    /**
     * Emit the current state so a parent component can persist it.
     */
    #[On('formTabs:requestState')]
    public function emitState(): void
    {
        $payload = [
            'planId' => $this->planId,
            'localUuid' => $this->localUuid,
            'storageMode' => $this->storageMode,
            'traineeImagePath' => $this->traineeImagePath,
            'plan_title' => $this->plan_title,
            'turn_before' => $this->turn_before,
            'name' => $this->name,
            'umamusume_id' => $this->umamusume_id,
            'race_name' => $this->race_name,
            'career_stage' => $this->career_stage,
            'class' => $this->class,
            'goal' => $this->goal,
            'strategy_id' => $this->strategy_id,
            'mood_id' => $this->mood_id,
            'condition_id' => $this->condition_id,
            'selected_conditions' => $this->selected_conditions,
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
            'planAttributes' => $this->planAttributes,
            'terrainGrades' => $this->terrainGrades,
            'distanceGrades' => $this->distanceGrades,
            'styleGrades' => $this->styleGrades,
        ];

        $this->dispatch('formTabs:state', data: $payload);
    }

    // Skills handlers
    public function addSkill(): void
    {
        $this->skills[] = [
            'skill_reference_id' => null,
            'name' => '',
            'sp_cost' => null,
            'tier' => '',
            'type' => '',
            'status' => 'suggested',
            'turn_acquired' => null,
            'tag' => '',
            'notes' => '',
        ];
    }

    public function removeSkill(int $index): void
    {
        if (isset($this->skills[$index])) {
            array_splice($this->skills, $index, 1);
        }
    }

    /**
     * Handle skills state update from SkillsEditor child component.
     */
    #[On('skillsEditor:state')]
    public function handleSkillsState(array $skills): void
    {
        $this->skills = $skills;
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
            'characterOptions' => $this->computedCharacterOptions(),
            'strategyOptions' => $this->computedStrategyOptions(),
            'moodOptions' => $this->computedMoodOptions(),
            'conditionOptions' => $this->computedConditionOptions(),
            'energyColorClass' => $this->energyColorClass(),
            'energyWarning' => $this->energyWarning(),
            // Bind lists as well
            'skills' => $this->skills,
            'predictions' => $this->predictions,
            'goals' => $this->goals,
            'planAttributes' => $this->planAttributes,
            'terrainGrades' => $this->terrainGrades,
            'distanceGrades' => $this->distanceGrades,
            'styleGrades' => $this->styleGrades,
        ]);
    }
}
