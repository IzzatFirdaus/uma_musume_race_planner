<?php

namespace App\Livewire\Dashboard;

use App\Models\Plan;
use Livewire\Component;

class PlanInlineDetails extends Component
{
    // Plan properties
    public $planId = null;
    public $plan_title = '';
    public $name = '';
    public $career_stage = '';
    public $class = '';
    public $race_name = '';
    public $turn_before = 0;
    public $goal = '';
    public $strategy_id = '';
    public $mood_id = '';
    public $condition_id = '';
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

    // Collections for related data
    public $attributes = [];
    public $skills = [];
    public $racePredictions = [];
    public $goals = [];
    public $terrainGrades = [];
    public $distanceGrades = [];
    public $styleGrades = [];

    // UI state
    public $isLoading = false;
    public $isVisible = false;

    protected $listeners = [
        'loadPlanInline' => 'loadPlan',
        'openPlanInline' => 'loadPlan'
    ];

    public function loadPlan($planId)
    {
        $this->isLoading = true;
        $this->isVisible = true;
        
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
                'strategy'
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
            $this->attributes = $plan->attributes->toArray();
            $this->skills = $plan->skills->map(function($skill) {
                return [
                    'skill_name' => $skill->skillReference->skill_name ?? '',
                    'sp_cost' => $skill->sp_cost ?? 0,
                    'acquired' => $skill->acquired ?? 'no',
                    'tag' => $skill->tag ?? '',
                    'notes' => $skill->notes ?? ''
                ];
            })->toArray();
            $this->racePredictions = $plan->racePredictions->toArray();
            $this->goals = $plan->goals->toArray();
            $this->terrainGrades = $plan->terrainGrades->toArray();
            $this->distanceGrades = $plan->distanceGrades->toArray();
            $this->styleGrades = $plan->styleGrades->toArray();

        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to load plan: ' . $e->getMessage());
        }
        
        $this->isLoading = false;
    }

    public function closePlan()
    {
        $this->isVisible = false;
        $this->reset();
    }

    public function save()
    {
        // For now, just emit an event to let main.js handle the submission
        // This maintains compatibility with the existing JavaScript submission handler
        $this->dispatch('submitPlanForm', formId: 'planDetailsFormInline');
    }

    public function render()
    {
        return view('livewire.dashboard.plan-inline-details');
    }
}
