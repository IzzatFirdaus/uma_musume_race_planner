<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Plan;
use App\Services\StatProgressService;
use Livewire\Component;
use Throwable;

/**
 * TurnTracker - Component for logging turn-by-turn stat progress (REQ-18).
 */
class TurnTracker extends Component
{
    public Plan $plan;

    public int $speed = 0;

    public int $stamina = 0;

    public int $power = 0;

    public int $guts = 0;

    public int $wit = 0;

    public int $staminaPercentage = 100;

    public bool $showRecentTurns = false;

    protected function rules(): array
    {
        return [
            'speed' => 'required|integer|min:0|max:1200',
            'stamina' => 'required|integer|min:0|max:1200',
            'power' => 'required|integer|min:0|max:1200',
            'guts' => 'required|integer|min:0|max:1200',
            'wit' => 'required|integer|min:0|max:1200',
            'staminaPercentage' => 'required|integer|min:0|max:100',
        ];
    }

    protected function messages(): array
    {
        return [
            'speed.required' => 'Speed is required.',
            'speed.min' => 'Speed cannot be negative.',
            'speed.max' => 'Speed cannot exceed 1200.',
            'stamina.required' => 'Stamina is required.',
            'stamina.min' => 'Stamina cannot be negative.',
            'stamina.max' => 'Stamina cannot exceed 1200.',
            'power.required' => 'Power is required.',
            'power.min' => 'Power cannot be negative.',
            'power.max' => 'Power cannot exceed 1200.',
            'guts.required' => 'Guts is required.',
            'guts.min' => 'Guts cannot be negative.',
            'guts.max' => 'Guts cannot exceed 1200.',
            'wit.required' => 'Wit is required.',
            'wit.min' => 'Wit cannot be negative.',
            'wit.max' => 'Wit cannot exceed 1200.',
            'staminaPercentage.required' => 'Stamina percentage is required.',
            'staminaPercentage.min' => 'Stamina percentage cannot be negative.',
            'staminaPercentage.max' => 'Stamina percentage cannot exceed 100.',
        ];
    }

    public function mount(Plan $plan): void
    {
        $this->plan = $plan;
        $this->staminaPercentage = 100;
    }

    public function logTurn(StatProgressService $statProgressService): void
    {
        $this->validate();

        try {
            $statProgressService->logTurn($this->plan, [
                'speed' => $this->speed,
                'stamina' => $this->stamina,
                'power' => $this->power,
                'guts' => $this->guts,
                'wit' => $this->wit,
                'stamina_percentage' => $this->staminaPercentage,
            ]);

            $this->dispatch('turn-logged', [
                'turn_number' => $statProgressService->getNextTurnNumber($this->plan) - 1,
                'stats' => [
                    'speed' => $this->speed,
                    'stamina' => $this->stamina,
                    'power' => $this->power,
                    'guts' => $this->guts,
                    'wit' => $this->wit,
                    'stamina_percentage' => $this->staminaPercentage,
                ],
            ]);

            $this->dispatch('toast', type: 'success', message: 'Turn logged successfully!');

            $this->reset(['speed', 'stamina', 'power', 'guts', 'wit']);
            $this->staminaPercentage = 100;
        } catch (Throwable $exception) {
            $this->dispatch('toast', type: 'error', message: 'Failed to log turn. Please try again.');
            report($exception);
        }
    }

    public function render()
    {
        return view('livewire.turn-tracker');
    }
}
