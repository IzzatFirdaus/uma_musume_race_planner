<?php

declare(strict_types=1);

namespace App\Livewire\Skills;

use App\Enums\SkillStatus;
use App\Models\Plan;
use App\Models\Skill;
use App\Services\SkillService;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Skill Editor Component
 *
 * Manages skill list with 3-state status, turn tracking, and SP totals.
 * Implements REQ-SKILL-1.2, REQ-SKILL-1.3, REQ-SKILL-1.4, REQ-SKILL-1.5.
 */
class SkillEditor extends Component
{
    public Plan $plan;

    public array $skills = [];

    public array $sp = [
        'acquired' => 0,
        'suggested' => 0,
        'skipped' => 0,
        'total_planned' => 0,
        'total_all' => 0,
    ];

    protected function rules(): array
    {
        return [
            'skills.*.status' => ['required', 'string', Rule::in(SkillStatus::values())],
            'skills.*.turn_acquired' => [
                'nullable',
                'integer',
                'min:1',
                'max:78',
                'required_if:skills.*.status,acquired',
            ],
            'skills.*.sp_cost' => ['nullable', 'integer', 'min:0'],
            'skills.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'skills.*.status.required' => 'Status is required.',
            'skills.*.turn_acquired.required_if' => 'Turn acquired is required when status is Acquired.',
            'skills.*.turn_acquired.min' => 'Turn must be at least 1.',
            'skills.*.turn_acquired.max' => 'Turn cannot exceed 78.',
        ];
    }

    public function mount(Plan $plan, SkillService $skillService): void
    {
        $this->plan = $plan;
        $this->loadSkills($skillService);
        $this->calculateSpTotals($skillService);
    }

    /**
     * Load skills from the plan.
     */
    public function loadSkills(SkillService $skillService): void
    {
        $skills = $skillService->getForPlan($this->plan);

        $this->skills = $skills->map(function (Skill $skill) {
            return [
                'id' => $skill->id,
                'skill_reference_id' => $skill->skill_reference_id,
                'name' => $skill->skillReference->skill_name ?? 'Unknown',
                'name_jp' => $skill->skillReference->name_jp ?? null,
                'status' => $skill->status->value,
                'turn_acquired' => $skill->turn_acquired,
                'sp_cost' => $skill->sp_cost,
                'notes' => $skill->notes,
                'tag' => $skill->skillReference->tag ?? null,
            ];
        })->toArray();
    }

    /**
     * Add a new skill row.
     */
    public function addSkill(): void
    {
        $this->skills[] = [
            'id' => null,
            'skill_reference_id' => null,
            'name' => '',
            'name_jp' => null,
            'status' => SkillStatus::Suggested->value,
            'turn_acquired' => null,
            'sp_cost' => null,
            'notes' => null,
            'tag' => null,
        ];
    }

    /**
     * Remove a skill row.
     */
    public function removeSkill(int $index): void
    {
        $skill = $this->skills[$index] ?? null;

        if ($skill && $skill['id']) {
            // Remove from database
            Skill::find($skill['id'])?->delete();
        }

        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);

        $this->dispatch('toast', type: 'success', message: 'Skill removed.');
    }

    /**
     * Update skill status.
     */
    public function updateStatus(int $index, string $status): void
    {
        $this->skills[$index]['status'] = $status;

        // Clear turn_acquired if not acquired
        if ($status !== SkillStatus::Acquired->value) {
            $this->skills[$index]['turn_acquired'] = null;
        }
    }

    /**
     * Update turn acquired.
     */
    public function updateTurnAcquired(int $index, ?int $turn): void
    {
        $this->skills[$index]['turn_acquired'] = $turn;
    }

    /**
     * Handle skill selected from search.
     */
    #[\Livewire\Attributes\On('skill-selected')]
    public function handleSkillSelected(array $skill): void
    {
        // Add to skills array
        $this->skills[] = [
            'id' => null,
            'skill_reference_id' => $skill['id'],
            'name' => $skill['name'],
            'name_jp' => $skill['name_jp'] ?? null,
            'status' => SkillStatus::Suggested->value,
            'turn_acquired' => null,
            'sp_cost' => null,
            'notes' => null,
            'tag' => $skill['tag'] ?? null,
        ];

        $this->dispatch('toast', type: 'success', message: 'Skill added.');
    }

    /**
     * Save all skills.
     */
    public function save(SkillService $skillService): void
    {
        $this->validate();

        try {
            $skillService->syncForPlan($this->plan, $this->skills);
            $this->loadSkills($skillService);
            $this->calculateSpTotals($skillService);

            $this->dispatch('toast', type: 'success', message: 'Skills saved successfully!');
        } catch (\Throwable $exception) {
            $this->dispatch('toast', type: 'error', message: 'Failed to save skills. Please try again.');
            report($exception);
        }
    }

    /**
     * Calculate SP totals.
     */
    public function calculateSpTotals(SkillService $skillService): void
    {
        $this->sp = $skillService->calculateSpTotals($this->plan);
    }

    public function render()
    {
        return view('livewire.skills.skill-editor');
    }
}
