<?php

declare(strict_types=1);

namespace App\Livewire\Plans;

use App\Models\Plan;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Race Snapshot Component
 *
 * Displays the per-turn career snapshots for a plan and allows an owner to
 * capture a new one. Implements REQ 77.3 and 77.4: snapshots are listed newest
 * first, and the count is capped so a long career cannot grow unbounded.
 *
 * The "Create Snapshot" action (REQ 77.1) lives in the parent predictions table
 * and reaches this component through the `open-snapshot-modal` browser event,
 * which carries the prediction id and race name to prefill the form.
 */
class RaceSnapshot extends Component
{
    /**
     * Maximum snapshots retained per plan.
     */
    public const MAX_SNAPSHOTS = 20;

    /**
     * Upper bound for a single stat value. 1200 is the total career cap.
     */
    private const STAT_MAX = 1200;

    public int $planId;

    public bool $isEditMode = false;

    public bool $showModal = false;

    public ?int $racePredictionId = null;

    public string $raceName = '';

    public int $turnNumber = 1;

    /**
     * @var array{speed: int, stamina: int, power: int, guts: int, wit: int}
     */
    public array $stats = [
        'speed' => 0,
        'stamina' => 0,
        'power' => 0,
        'guts' => 0,
        'wit' => 0,
    ];

    public int $staminaPercentage = 100;

    public int $totalSpAvailable = 0;

    public string $notes = '';

    /**
     * @var array<string, string>
     */
    protected $listeners = [
        'open-snapshot-modal' => 'openModal',
    ];

    protected function rules(): array
    {
        return [
            'turnNumber' => ['required', 'integer', 'min:1', 'max:78'],
            'raceName' => ['nullable', 'string', 'max:255'],
            'stats.speed' => ['required', 'integer', 'min:0', 'max:'.self::STAT_MAX],
            'stats.stamina' => ['required', 'integer', 'min:0', 'max:'.self::STAT_MAX],
            'stats.power' => ['required', 'integer', 'min:0', 'max:'.self::STAT_MAX],
            'stats.guts' => ['required', 'integer', 'min:0', 'max:'.self::STAT_MAX],
            'stats.wit' => ['required', 'integer', 'min:0', 'max:'.self::STAT_MAX],
            'staminaPercentage' => ['required', 'integer', 'min:0', 'max:100'],
            'totalSpAvailable' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'turnNumber.min' => 'Turn must be at least 1.',
            'turnNumber.max' => 'Turn cannot exceed 78.',
            'stats.*.max' => 'A stat cannot exceed 1200.',
            'staminaPercentage.min' => 'Stamina must be between 0 and 100%.',
            'staminaPercentage.max' => 'Stamina must be between 0 and 100%.',
        ];
    }

    public function mount(int $planId, bool $isEditMode = false): void
    {
        $this->planId = $planId;
        $this->isEditMode = $isEditMode;

        Gate::authorize('view', $this->plan());
    }

    /**
     * Resolve and authorize-read the parent plan.
     */
    private function plan(): Plan
    {
        return Plan::query()->findOrFail($this->planId);
    }

    /**
     * Open the create form, prefilled from the prediction row that dispatched the event.
     */
    public function openModal(?int $racePredictionId = null, string $raceName = ''): void
    {
        Gate::authorize('update', $this->plan());

        $this->racePredictionId = $racePredictionId;
        $this->raceName = $raceName;
        $this->showModal = true;

        $this->resetValidation();
    }

    public function closeModal(): void
    {
        $this->showModal = false;

        $this->resetValidation();
    }

    /**
     * Persist a new snapshot, then close the form.
     */
    public function save(): void
    {
        Gate::authorize('update', $this->plan());

        $this->validate();

        $plan = $this->plan();

        $existing = $plan->careerSnapshots()->count();

        if ($existing >= self::MAX_SNAPSHOTS) {
            $this->addError('turnNumber', 'A plan can hold at most '.self::MAX_SNAPSHOTS.' snapshots.');

            return;
        }

        $plan->careerSnapshots()->create([
            'race_prediction_id' => $this->racePredictionId,
            'turn_number' => $this->turnNumber,
            'race_name' => $this->raceName !== '' ? $this->raceName : null,
            'speed' => $this->stats['speed'],
            'stamina' => $this->stats['stamina'],
            'power' => $this->stats['power'],
            'guts' => $this->stats['guts'],
            'wit' => $this->stats['wit'],
            'stamina_percentage' => $this->staminaPercentage,
            'total_sp_available' => $this->totalSpAvailable,
            'notes' => $this->notes !== '' ? $this->notes : null,
        ]);

        $this->closeModal();
        $this->dispatch('snapshot-created');
    }

    /**
     * Delete a snapshot belonging to this plan.
     */
    public function delete(int $snapshotId): void
    {
        Gate::authorize('update', $this->plan());

        $plan = $this->plan();

        $plan->careerSnapshots()
            ->whereKey($snapshotId)
            ->delete();

        $this->dispatch('snapshot-deleted');
    }

    public function render()
    {
        $snapshots = $this->plan()
            ->careerSnapshots()
            ->orderByDesc('turn_number')
            ->orderByDesc('id')
            ->get();

        return view('livewire.plans.race-snapshot', [
            'snapshots' => $snapshots,
            'remainingSlots' => max(0, self::MAX_SNAPSHOTS - $snapshots->count()),
        ]);
    }
}
