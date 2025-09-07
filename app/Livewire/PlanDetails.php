<?php

namespace App\Livewire;

use App\Models\Plan;
use Livewire\Component;

class PlanDetails extends Component
{
    // Plan properties
    public $planId = null;

    public function getPlanId()
    {
        return $this->planId;
    }

    public $plan_title = '';

    public function getPlanTitle()
    {
        return $this->plan_title;
    }

    public $name = '';

    public function getName()
    {
        return $this->name;
    }

    public $career_stage = '';

    public function getCareerStage()
    {
        return $this->career_stage;
    }

    public $class = '';

    public function getClass()
    {
        return $this->class;
    }

    public $race_name = '';

    public function getRaceName()
    {
        return $this->race_name;
    }

    public $turn_before = 0;

    public function getTurnBefore()
    {
        return $this->turn_before;
    }

    public $goal = '';

    public function getGoal()
    {
        return $this->goal;
    }

    public $strategy_id = '';

    public function getStrategyId()
    {
        return $this->strategy_id;
    }

    public $mood_id = '';

    public function getMoodId()
    {
        return $this->mood_id;
    }

    public $condition_id = '';

    public function getConditionId()
    {
        return $this->condition_id;
    }

    public $energy = 0;

    public function getEnergy()
    {
        return $this->energy;
    }

    public $race_day = false;

    public function getRaceDay()
    {
        return $this->race_day;
    }

    public $acquire_skill = false;

    public function getAcquireSkill()
    {
        return $this->acquire_skill;
    }

    public $total_available_skill_points = 0;

    public function getTotalAvailableSkillPoints()
    {
        return $this->total_available_skill_points;
    }

    public $status = 'Planning';

    public function getStatus()
    {
        return $this->status;
    }

    public $time_of_day = '';

    public function getTimeOfDay()
    {
        return $this->time_of_day;
    }

    public $month = '';

    public function getMonth()
    {
        return $this->month;
    }

    public $source = '';

    public function getSource()
    {
        return $this->source;
    }

    public $growth_rate_speed = 0;

    public function getGrowthRateSpeed()
    {
        return $this->growth_rate_speed;
    }

    public $growth_rate_stamina = 0;

    public function getGrowthRateStamina()
    {
        return $this->growth_rate_stamina;
    }

    public $growth_rate_power = 0;

    public function getGrowthRatePower()
    {
        return $this->growth_rate_power;
    }

    public $growth_rate_guts = 0;

    public function getGrowthRateGuts()
    {
        return $this->growth_rate_guts;
    }

    public $growth_rate_wit = 0;

    public function getGrowthRateWit()
    {
        return $this->growth_rate_wit;
    }

    // Collections for related data
    public $planAttributes = [];

    public function getPlanAttributes()
    {
        return $this->planAttributes;
    }

    public $skills = [];

    public function getSkills()
    {
        return $this->skills;
    }

    public $racePredictions = [];

    public function getRacePredictions()
    {
        return $this->racePredictions;
    }

    public $goals = [];

    public function getGoals()
    {
        return $this->goals;
    }

    public $terrainGrades = [];

    public function getTerrainGrades()
    {
        return $this->terrainGrades;
    }

    public $distanceGrades = [];

    public function getDistanceGrades()
    {
        return $this->distanceGrades;
    }

    public $styleGrades = [];

    public function getStyleGrades()
    {
        return $this->styleGrades;
    }

    // UI state
    public $isLoading = false;

    public function getIsLoading()
    {
        return $this->isLoading;
    }

    protected $listeners = [
        'loadPlan' => 'loadPlan',
        'openPlanModal' => 'loadPlan',
        'openPlanEditModal' => 'loadPlan',
    ];

    public function mount($planId = null)
    {
        if ($planId) {
            $this->loadPlan($planId);
        }
    }

    public function loadPlan($planId)
    {
        $this->isLoading = true;

        try {
            $plan = Plan::with([
                'attributes',
                'skills.skillReference',
                'racePredictions',
                'goals',
                'terrainGrades',
                'distanceGrades',
                'styleGrades',
                'mood',
                'condition',
                'strategy',
            ])->findOrFail($planId);

            $this->planId = $plan->id;
            $this->plan_title = $plan->plan_title ?? '';
            $this->name = $plan->name ?? '';
            $this->career_stage = $plan->career_stage ?? '';
            $this->class = $plan->class ?? '';
            $this->race_name = $plan->race_name ?? '';
            $this->turn_before = $plan->turn_before ?? 0;
            $this->goal = $plan->goal ?? '';
            $this->strategy_id = $plan->strategy_id ?? '';
            $this->mood_id = $plan->mood_id ?? '';
            $this->condition_id = $plan->condition_id ?? '';
            $this->energy = $plan->energy ?? 0;
            $this->race_day = $plan->race_day === 'yes';
            $this->acquire_skill = $plan->acquire_skill === 'YES';
            $this->total_available_skill_points = $plan->total_available_skill_points ?? 0;
            $this->status = $plan->status ?? 'Planning';
            $this->time_of_day = $plan->time_of_day ?? '';
            $this->month = $plan->month ?? '';
            $this->source = $plan->source ?? '';
            $this->growth_rate_speed = $plan->growth_rate_speed ?? 0;
            $this->growth_rate_stamina = $plan->growth_rate_stamina ?? 0;
            $this->growth_rate_power = $plan->growth_rate_power ?? 0;
            $this->growth_rate_guts = $plan->growth_rate_guts ?? 0;
            $this->growth_rate_wit = $plan->growth_rate_wit ?? 0;

            // Load related data
            $this->planAttributes = $plan->attributes->toArray();
            $this->skills = $plan->skills->map(function ($skill) {
                return [
                    'skill_name' => $skill->skillReference->skill_name ?? '',
                    'sp_cost' => $skill->sp_cost ?? 0,
                    'acquired' => $skill->acquired ?? 'no',
                    'tag' => $skill->tag ?? '',
                    'notes' => $skill->notes ?? '',
                ];
            })->toArray();
            $this->racePredictions = $plan->racePredictions->toArray();
            $this->goals = $plan->goals->toArray();
            $this->terrainGrades = $plan->terrainGrades->toArray();
            $this->distanceGrades = $plan->distanceGrades->toArray();
            $this->styleGrades = $plan->styleGrades->toArray();

        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to load plan: '.$e->getMessage());
        }

        $this->isLoading = false;
    }

    /**
     * Validation rules for plan details.
     *
     * @var array<string, string>
     */
    protected array $rules = [
        'plan_title' => 'required|string|max:255',
        'career_stage' => 'required|string',
        'class' => 'required|string',
        'race_name' => 'nullable|string|max:255',
        'turn_before' => 'nullable|integer|min:0',
        'goal' => 'nullable|string|max:255',
        'strategy_id' => 'nullable|integer',
        'mood_id' => 'nullable|integer',
        'condition_id' => 'nullable|integer',
        'energy' => 'nullable|integer|min:0',
        'status' => 'required|string',
        'month' => 'nullable|string',
        'source' => 'nullable|string|max:255',
    ];

    /**
     * Custom error messages for validation.
     *
     * @var array<string, string>
     */
    protected array $messages = [
        'plan_title.required' => 'The plan title is required.',
        'career_stage.required' => 'Please select a career stage.',
        'class.required' => 'Please select a class.',
        'status.required' => 'Status is required.',
    ];

    /**
     * Save the plan details after validation.
     */
    public function save(): void
    {
        $this->validate($this->rules, $this->messages);

        try {
            // Save logic here (update or create Plan)
            // ...existing code...
            $this->dispatch('submitPlanForm', formId: 'planDetailsForm');
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to save plan: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.plan-details');
    }
}
