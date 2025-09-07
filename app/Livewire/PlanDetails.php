<?php

namespace App\Livewire;

use App\Models\Plan;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PlanDetails extends Component
{
    // Plan properties
    public $planId = null;

    #[Validate('required|string|max:255')]
    public $plan_title = '';

    public $name = '';

    #[Validate('required|string')]
    public $career_stage = '';

    #[Validate('required|string')]
    public $class = '';

    #[Validate('nullable|string|max:255')]
    public $race_name = '';

    #[Validate('nullable|integer|min:0')]
    public $turn_before = 0;

    #[Validate('nullable|string|max:255')]
    public $goal = '';

    public $strategy_id = '';

    public $mood_id = '';

    public $condition_id = '';

    public $energy = 0;

    public $race_day = false;

    public $acquire_skill = false;

    public $total_available_skill_points = 0;

    #[Validate('required|string')]
    public $status = 'Planning';

    public $time_of_day = '';

    public $month = '';

    public $source = '';

    public $growth_rate_speed = 0;

    public $growth_rate_stamina = 0;

    public $growth_rate_power = 0;

    public $growth_rate_guts = 0;

    public $growth_rate_wit = 0;

    // Collections for related data
    public $planAttributes = [];

    public $skills = [];

    public $racePredictions = [];

    public $goals = [];

    public $terrainGrades = [];

    public $distanceGrades = [];

    public $styleGrades = [];

    // UI state
    public $isLoading = false;

    #[On('loadPlan')]
    #[On('openPlanModal')]
    #[On('openPlanEditModal')]
    public function mount($planId = null)
    {
        if ($planId) {
            $this->loadPlan($planId);
        }
    }

    #[On('loadPlan')]
    #[On('openPlanModal')]
    #[On('openPlanEditModal')]
    public function loadPlan($planId)
    {
        $this->isLoading = true;
        try {
            $plan = $this->fetchPlanWithRelations($planId);
            $this->assignPlanProperties($plan);
        } catch (\Exception $e) {
            $this->dispatch('show-error', ['message' => 'Failed to load plan: '.$e->getMessage()]);
        }
        $this->isLoading = false;
    }

    private function fetchPlanWithRelations($planId)
    {
        return Plan::with([
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
    }

    private function assignPlanProperties(Plan $plan): void
    {
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
    }

    /**
     * Custom error messages for validation.
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
        $this->validate();

        try {
            // Save logic here (update or create Plan)
            // ...existing code...
            $this->dispatch('submitPlanForm', formId: 'planDetailsForm');
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to save plan: '.$e->getMessage());
        }
    }

    /**
     * Render the component view.
     */
    public function render()
    {
        return view('livewire.plan-details');
    }
}
