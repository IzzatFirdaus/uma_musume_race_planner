<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class PlanDetailsPage extends Component
{
    // Plan properties
    public $planId = null;

    public $isEditMode = false; // Track if we're in edit mode

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
    public $planAttributes = [];

    public $skills = [];

    public $racePredictions = [];

    public $goals = [];

    public $terrainGrades = [];

    public $distanceGrades = [];

    public $styleGrades = [];

    // UI state
    public $isLoading = false;

    public function mount($planId): void
    {
        $this->planId = $planId;

        // Determine mode based on current route
        $currentRoute = request()->route()->getName();
        $this->isEditMode = ($currentRoute === 'plans.edit');

        $this->loadPlan($this->planId);
    }

    public function loadPlan($planId)
    {
        $this->isLoading = true;
        try {
            $plan = \App\Models\Plan::with([
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
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to load plan: '.$e->getMessage());
        }
        $this->isLoading = false;
    }

    public function save()
    {
        // Prevent saving in view mode
        if (! $this->isEditMode) {
            $this->dispatch('show-error', message: 'Cannot save changes in view mode.');

            return;
        }

        try {
            $plan = \App\Models\Plan::findOrFail($this->planId);

            // Update plan with current data
            $plan->update([
                'plan_title' => $this->plan_title,
                'name' => $this->name,
                'career_stage' => $this->career_stage,
                'class' => $this->class,
                'race_name' => $this->race_name,
                'turn_before' => $this->turn_before,
                'goal' => $this->goal,
                'strategy_id' => $this->strategy_id,
                'mood_id' => $this->mood_id,
                'condition_id' => $this->condition_id,
                'energy' => $this->energy,
                'race_day' => $this->race_day ? 'yes' : 'no',
                'acquire_skill' => $this->acquire_skill ? 'YES' : 'NO',
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
            ]);

            // Log the activity
            \App\Models\ActivityLog::create([
                'description' => "Updated plan: {$this->name}",
                'icon_class' => 'bi-pencil',
                'timestamp' => now(),
            ]);

            $this->dispatch('plan-saved', message: 'Plan saved successfully!');
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to save plan: '.$e->getMessage());
        }
    }

    public function title(): string
    {
        $mode = $this->isEditMode ? 'Edit' : 'View';

        return $this->plan_title ? "{$mode} Plan: {$this->plan_title}" : "{$mode} Plan Details";
    }

    public function render()
    {
        return view('livewire.dashboard.plan-details-page');
    }
}
