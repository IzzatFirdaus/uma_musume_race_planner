<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\StorageMode;
use App\Models\Umamusume;
use App\Services\PlanService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

/**
 * QuickCreatePlan - Modal component for rapid plan creation (REQ-PLAN-1).
 */
class QuickCreatePlan extends Component
{
    public string $title = '';

    public ?string $characterId = null;

    public string $characterName = '';

    public string $careerStage = 'junior';

    public string $traineeClass = 'beginner';

    public string $storageMode = 'local';

    /** @var array<int, array{value: string, text: string}> */
    public array $careerStageOptions = [];

    /** @var array<int, array{value: string, text: string}> */
    public array $classOptions = [];

    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:1|max:255',
            'characterId' => 'required|string|exists:umamusume,id',
            'careerStage' => 'required|string|in:predebut,junior,classic,senior,finale',
            'traineeClass' => 'required|string|in:debut,maiden,beginner,bronze,silver,gold,platinum,legend',
            'storageMode' => [
                'required',
                'string',
                Rule::in(StorageMode::values()),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === StorageMode::Account->value && ! auth()->check()) {
                        $fail('You must be signed in to create account plans.');
                    }
                },
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Please enter a plan title.',
            'title.min' => 'Plan title cannot be empty.',
            'title.max' => 'Plan title cannot exceed 255 characters.',
            'characterId.required' => 'Please select a character.',
            'characterId.exists' => 'The selected character is invalid.',
            'careerStage.required' => 'Please select a career stage.',
            'traineeClass.required' => 'Please select a class.',
            'storageMode.required' => 'Please select a storage mode.',
        ];
    }

    public function mount(): void
    {
        $this->careerStageOptions = [
            ['value' => 'predebut', 'text' => 'Pre-Debut'],
            ['value' => 'junior', 'text' => 'Junior Year'],
            ['value' => 'classic', 'text' => 'Classic Year'],
            ['value' => 'senior', 'text' => 'Senior Year'],
            ['value' => 'finale', 'text' => 'Finale Season'],
        ];

        $this->classOptions = [
            ['value' => 'debut', 'text' => 'Debut'],
            ['value' => 'maiden', 'text' => 'Maiden'],
            ['value' => 'beginner', 'text' => 'Beginner'],
            ['value' => 'bronze', 'text' => 'Bronze'],
            ['value' => 'silver', 'text' => 'Silver'],
            ['value' => 'gold', 'text' => 'Gold'],
            ['value' => 'platinum', 'text' => 'Star'],
            ['value' => 'legend', 'text' => 'Legend'],
        ];

        $this->storageMode = StorageMode::Local->value;
    }

    #[On('open-create-plan-modal')]
    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    #[On('character-selected')]
    public function handleCharacterSelected(array $data): void
    {
        $this->characterId = $data['id'] ?? null;
        $this->characterName = $data['name'] ?? '';

        if ($this->title === '' && $this->characterName !== '') {
            $this->title = $this->characterName."'s Training Plan";
        }

        if (! $this->showModal) {
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function resetForm(): void
    {
        $this->title = '';
        $this->characterId = null;
        $this->characterName = '';
        $this->careerStage = 'junior';
        $this->traineeClass = 'beginner';
        $this->storageMode = StorageMode::Local->value;
        $this->resetValidation();
    }

    public function updatedCharacterId(?string $characterId): void
    {
        if ($characterId === null || $characterId === '') {
            $this->characterName = '';

            return;
        }

        $character = Umamusume::query()->find($characterId);

        if ($character === null) {
            $this->characterName = '';

            return;
        }

        $this->characterName = $character->name;

        if ($this->title === '') {
            $this->title = $character->name."'s Training Plan";
        }
    }

    #[Computed]
    public function characters(): \Illuminate\Database\Eloquent\Collection
    {
        return Umamusume::query()->orderBy('name')->get(['id', 'name', 'team', 'rarity']);
    }

    #[Computed]
    public function isAuthenticated(): bool
    {
        return auth()->check();
    }

    public function save(PlanService $planService): void
    {
        $this->title = trim($this->title);

        if ($this->title === '') {
            $this->addError('title', 'Plan title cannot be empty or whitespace only.');

            return;
        }

        $this->validate();

        try {
            $result = $planService->createQuickPlan([
                'title' => $this->title,
                'character_id' => (string) $this->characterId,
                'storage_mode' => $this->storageMode,
                'career_stage' => $this->careerStage,
                'class' => $this->traineeClass,
            ]);

            $this->closeModal();
            $this->dispatch('refreshPlans');

            if ($result['storage_mode'] === StorageMode::Local->value) {
                $this->dispatch('create-local-plan', [
                    'planData' => $result['local_payload'],
                    'redirectUrl' => $result['redirect_url'],
                ]);
                $this->dispatch('plan-created', message: 'Local plan created successfully!');

                return;
            }

            $this->dispatch('plan-created', message: 'Plan created successfully!');
            $this->redirect($result['redirect_url']);
        } catch (AuthorizationException $exception) {
            $this->addError('storageMode', $exception->getMessage());
        } catch (Throwable $exception) {
            $this->dispatch('toast', type: 'error', message: 'Failed to create plan. Please try again.');
            report($exception);
        }
    }

    public function render()
    {
        return view('livewire.quick-create-plan', [
            'careerStageOptions' => $this->careerStageOptions,
            'classOptions' => $this->classOptions,
            'characters' => $this->characters(),
            'isAuthenticated' => $this->isAuthenticated(),
        ]);
    }
}
