<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Plan;
use Livewire\Attributes\On;
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
    public $planAttributes = [];

    public $skills = [];

    public $racePredictions = [];

    public $goals = [];

    public $terrainGrades = [];

    public $distanceGrades = [];

    public $styleGrades = [];

    // UI state
    public $isLoading = false;

    public $isVisible = false;

    // Converted to attribute-based listeners (#[On])

    #[On('loadPlanInline')]
    #[On('openPlanInline')]
    public function loadPlan($planId): void
    {
        $this->isLoading = true;
        $this->isVisible = true;

        // Dispatch JavaScript event to hide the plan list card
        $this->dispatch('hidePlanListCard');

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
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to load plan: '.$e->getMessage());
        }

        $this->isLoading = false;

        // Hydrate FormTabs with current state
        $this->dispatch('formTabs:hydrate', data: [
            'planId' => $this->planId,
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
            'predictions' => $this->racePredictions,
            'goals' => $this->goals,
        ]);
    }

    public function closePlan(): void
    {
        $this->isVisible = false;
        $this->reset();

        // Dispatch JavaScript event to show the plan list card
        $this->dispatch('showPlanListCard');
    }

    public function save(): void
    {
        // Ask child FormTabs for its current state; receive via receiveFormTabsState
        $this->dispatch('formTabs:requestState');
    }

    /**
     * Receive state from FormTabs and persist to database.
     */
    #[On('formTabs:state')]
    public function receiveFormTabsState(array $data): void
    {
        try {
            $plan = Plan::findOrFail($this->planId);

            // Normalize booleans to DB expectations
            $raceDay = ! empty($data['race_day']);
            $acquireSkill = ! empty($data['acquire_skill']);

            $plan->update([
                'plan_title' => $data['plan_title'] ?? $plan->plan_title,
                'name' => $data['name'] ?? $plan->name,
                'career_stage' => $data['career_stage'] ?? $plan->career_stage,
                'class' => $data['class'] ?? $plan->class,
                'race_name' => $data['race_name'] ?? $plan->race_name,
                'turn_before' => $data['turn_before'] ?? $plan->turn_before,
                'goal' => $data['goal'] ?? $plan->goal,
                'strategy_id' => $data['strategy_id'] ?? $plan->strategy_id,
                'mood_id' => $data['mood_id'] ?? $plan->mood_id,
                'condition_id' => $data['condition_id'] ?? $plan->condition_id,
                'energy' => $data['energy'] ?? $plan->energy,
                'race_day' => $raceDay ? 'yes' : 'no',
                'acquire_skill' => $acquireSkill ? 'YES' : 'NO',
                'total_available_skill_points' => $data['total_available_skill_points'] ?? $plan->total_available_skill_points,
                'status' => $data['status'] ?? $plan->status,
                'time_of_day' => $data['time_of_day'] ?? $plan->time_of_day,
                'month' => $data['month'] ?? $plan->month,
                'source' => $data['source'] ?? $plan->source,
                'growth_rate_speed' => $data['growth_rate_speed'] ?? $plan->growth_rate_speed,
                'growth_rate_stamina' => $data['growth_rate_stamina'] ?? $plan->growth_rate_stamina,
                'growth_rate_power' => $data['growth_rate_power'] ?? $plan->growth_rate_power,
                'growth_rate_guts' => $data['growth_rate_guts'] ?? $plan->growth_rate_guts,
                'growth_rate_wit' => $data['growth_rate_wit'] ?? $plan->growth_rate_wit,
            ]);

            // Persist related lists
            // Skills: we only have free-text name/tag/acquired/notes here. Map to SkillReference if possible, else create placeholder reference.
            if (isset($data['skills']) && is_array($data['skills'])) {
                // For simplicity, replace all rows
                $plan->skills()->delete();
                foreach ($data['skills'] as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $name = trim((string) ($row['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }

                    $ref = \App\Models\SkillReference::firstOrCreate(['skill_name' => $name]);
                    $plan->skills()->create([
                        'skill_reference_id' => $ref->id,
                        'sp_cost' => $row['sp_cost'] ?? null,
                        'acquired' => ! empty($row['acquired']) ? 'yes' : 'no',
                        'tag' => $row['tag'] ?? null,
                        'notes' => $row['notes'] ?? null,
                    ]);
                }
            }

            if (isset($data['predictions']) && is_array($data['predictions'])) {
                $plan->racePredictions()->delete();
                foreach ($data['predictions'] as $p) {
                    if (! is_array($p)) {
                        continue;
                    }
                    $plan->racePredictions()->create([
                        'race_name' => $p['race_name'] ?? null,
                        'venue' => $p['venue'] ?? null,
                        'ground' => $p['ground'] ?? null,
                        'distance' => $p['distance'] ?? null,
                        'speed' => (string) ($p['speed'] ?? ''),
                        'stamina' => (string) ($p['stamina'] ?? ''),
                        'power' => (string) ($p['power'] ?? ''),
                        'guts' => (string) ($p['guts'] ?? ''),
                        'wit' => (string) ($p['wit'] ?? ''),
                        'comment' => $p['comment'] ?? null,
                    ]);
                }
            }

            if (isset($data['goals']) && is_array($data['goals'])) {
                $plan->goals()->delete();
                foreach ($data['goals'] as $g) {
                    if (! is_array($g)) {
                        continue;
                    }
                    $goalText = trim((string) ($g['goal'] ?? ''));
                    $resultText = trim((string) ($g['result'] ?? ''));
                    if ($goalText === '' && $resultText === '') {
                        continue;
                    }
                    $plan->goals()->create([
                        'goal' => $goalText ? $goalText : null,
                        'result' => $resultText ? $resultText : '',
                    ]);
                }
            }

            // Refresh visible state
            $this->loadPlan($plan->id);

            $this->dispatch('plan-saved', message: 'Plan saved successfully!');
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to save plan: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.dashboard.plan-inline-details');
    }
}
