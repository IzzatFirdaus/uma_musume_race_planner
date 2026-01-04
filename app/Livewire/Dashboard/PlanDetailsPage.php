<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Plan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Plan Details Page Component
 *
 * Handles viewing and editing of Account runs (database-stored plans).
 * Routes: /plans/{id}, /plans/{id}/view, /plans/{id}/edit
 *
 * Requirements: 4.1, 4.2 - Plan viewing with tabbed interface
 * Requirements: 5.3, 76.4, 76.5 - Dirty state tracking
 */
#[Layout('components.layout')]
class PlanDetailsPage extends Component
{
    // Plan properties
    public $planId = null;

    public bool $isEditMode = false; // Track if we're in edit mode

    public string $storageMode = 'account'; // Storage mode indicator

    // Dirty state tracking (Req 5.3, 76.4, 76.5)
    public bool $isDirty = false;

    public array $originalState = [];

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

    public $notFound = false;

    /**
     * Mount the component with plan ID.
     * Determines view/edit mode based on current route.
     *
     * @param  int|string  $planId  The plan ID (numeric for account runs)
     */
    public function mount($planId): void
    {
        $this->planId = $planId;

        // Determine mode based on current route
        $currentRoute = request()->route()->getName();
        $this->isEditMode = ($currentRoute === 'plans.edit');
        $this->storageMode = 'account';

        $this->loadPlan($this->planId);
    }

    public function loadPlan($planId): void
    {
        $this->isLoading = true;
        $this->notFound = false;

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
            $this->storageMode = $plan->storage_mode?->value ?? 'account';
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
            $this->skills = $plan->skills->map(fn ($skill) => [
                'skill_name' => $skill->skillReference->skill_name ?? '',
                'sp_cost' => $skill->sp_cost ?? 0,
                'acquired' => $skill->acquired ?? 'no',
                'tag' => $skill->tag ?? '',
                'notes' => $skill->notes ?? '',
            ])->toArray();
            $this->racePredictions = $plan->racePredictions->toArray();
            $this->goals = $plan->goals->toArray();
            $this->terrainGrades = $plan->terrainGrades->toArray();
            $this->distanceGrades = $plan->distanceGrades->toArray();

            // Store original state for dirty tracking (Req 5.3, 76.4, 76.5)
            $this->originalState = $this->captureCurrentState();
            $this->isDirty = false;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->notFound = true;
            $this->isLoading = false;

            return;
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: 'Failed to load plan: '.$e->getMessage());
        }
        $this->isLoading = false;

        // Hydrate the FormTabs child component with the loaded state so tabs show data
        // Use dispatch to trigger Livewire server-side listeners on other components.
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
            'planAttributes' => $this->planAttributes,
            'terrainGrades' => $this->terrainGrades,
            'distanceGrades' => $this->distanceGrades,
            'styleGrades' => $this->styleGrades,
        ]);
    }

    /**
     * Capture current state for dirty tracking comparison.
     */
    private function captureCurrentState(): array
    {
        return [
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
        ];
    }

    /**
     * Mark the form as dirty when FormTabs reports changes.
     */
    #[On('formTabs:dirty')]
    public function markDirty(): void
    {
        $this->isDirty = true;
    }

    /**
     * Restore draft data from localStorage (Req 57.2).
     * Called from JavaScript when user chooses to restore a draft.
     */
    #[On('restore-draft')]
    public function restoreDraft(array $formData): void
    {
        // Hydrate FormTabs with the draft data
        $this->dispatch('formTabs:hydrate', data: $formData);
        $this->isDirty = true;
    }

    public function save(): void
    {
        // Prevent saving in view mode
        if (! $this->isEditMode) {
            $this->dispatch('show-error', message: 'Cannot save changes in view mode.');

            return;
        }

        // Ask the child FormTabs component for its current state; it will emit
        // 'formTabs:state' which this component will receive and persist.
        $this->dispatch('formTabs:requestState');
    }

    /**
     * Reset dirty state after successful save.
     * Also clears drafts (Req 57.6).
     */
    #[On('plan-saved')]
    public function onPlanSaved(): void
    {
        $this->isDirty = false;
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
            if (isset($data['skills']) && is_array($data['skills'])) {
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

            if (isset($data['planAttributes']) && is_array($data['planAttributes'])) {
                $plan->attributes()->delete();
                $attrs = [];
                foreach ($data['planAttributes'] as $a) {
                    if (! is_array($a)) {
                        continue;
                    }
                    $name = trim((string) ($a['attribute_name'] ?? $a['attribute_name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }
                    $attrs[] = [
                        'attribute_name' => $name,
                        'value' => isset($a['value']) ? (int) $a['value'] : 0,
                        'grade' => $a['grade'] ?? null,
                    ];
                }
                if (count($attrs) > 0) {
                    $plan->attributes()->createMany($attrs);
                }
            }

            if (isset($data['terrainGrades']) && is_array($data['terrainGrades'])) {
                $plan->terrainGrades()->delete();
                $tg = [];
                foreach ($data['terrainGrades'] as $t) {
                    if (! is_array($t) || empty($t['terrain'])) {
                        continue;
                    }
                    $tg[] = [
                        'terrain' => $t['terrain'],
                        'grade' => $t['grade'] ?? null,
                    ];
                }
                if (count($tg) > 0) {
                    $plan->terrainGrades()->createMany($tg);
                }
            }

            if (isset($data['distanceGrades']) && is_array($data['distanceGrades'])) {
                $plan->distanceGrades()->delete();
                $dg = [];
                foreach ($data['distanceGrades'] as $d) {
                    if (! is_array($d) || empty($d['distance'])) {
                        continue;
                    }
                    $dg[] = [
                        'distance' => $d['distance'],
                        'grade' => $d['grade'] ?? null,
                    ];
                }
                if (count($dg) > 0) {
                    $plan->distanceGrades()->createMany($dg);
                }
            }

            if (isset($data['styleGrades']) && is_array($data['styleGrades'])) {
                $plan->styleGrades()->delete();
                $sg = [];
                foreach ($data['styleGrades'] as $s) {
                    if (! is_array($s) || empty($s['style'])) {
                        continue;
                    }
                    $sg[] = [
                        'style' => $s['style'],
                        'grade' => $s['grade'] ?? null,
                    ];
                }
                if (count($sg) > 0) {
                    $plan->styleGrades()->createMany($sg);
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

            // Reset dirty state after successful save
            $this->isDirty = false;

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
