<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\StorageMode;
use App\Models\Plan;
use App\Models\Umamusume;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * QuickCreatePlan - Modal component for rapid plan creation
 *
 * Implements Requirements 2.1, 2.2, 2.3, 56.3:
 * - Quick create modal with title, character, storage mode fields
 * - Storage mode selector (Local/Account based on auth)
 * - Form submission with validation
 * - Local: Generate UUID, save to localStorage, navigate to /plans/local/{uuid}/edit
 * - Account: POST to server, navigate to /plans/{id}/edit
 */
class QuickCreatePlan extends Component
{
    // Form fields
    public string $title = '';

    public ?string $characterId = null;

    public string $characterName = '';

    public string $careerStage = 'junior';

    public string $traineeClass = 'beginner';

    public string $storageMode = 'local'; // Default to local for anonymous users

    // Options for dropdowns
    public array $careerStageOptions = [];

    public array $classOptions = [];

    // Modal state
    public bool $showModal = false;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:1|max:255',
            'characterId' => 'nullable|string|exists:umamusume,id',
            'characterName' => 'nullable|string|max:255',
            'careerStage' => 'required|string|in:predebut,junior,classic,senior,finale',
            'traineeClass' => 'required|string|in:debut,maiden,beginner,bronze,silver,gold,platinum,legend',
            'storageMode' => 'required|string|in:local,account',
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Please enter a plan title.',
            'title.min' => 'Plan title cannot be empty.',
            'title.max' => 'Plan title cannot exceed 255 characters.',
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

        // Default storage mode based on authentication status
        $this->storageMode = auth()->check() ? 'account' : 'local';
    }

    /**
     * Open the modal (Req 2.1)
     */
    #[On('open-create-plan-modal')]
    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->dispatch('show-create-plan-modal');
    }

    /**
     * Handle character selection from CharacterList page (Req 11.6)
     * Auto-populate growth rates and default aptitudes when a character is selected
     */
    #[On('character-selected')]
    public function handleCharacterSelected(array $data): void
    {
        $this->characterId = $data['id'] ?? null;
        $this->characterName = $data['name'] ?? '';

        // Auto-populate title if empty
        if (empty($this->title) && ! empty($this->characterName)) {
            $this->title = $this->characterName . "'s Training Plan";
        }

        // Open the modal if not already open
        if (! $this->showModal) {
            $this->showModal = true;
            $this->dispatch('show-create-plan-modal');
        }
    }

    /**
     * Close the modal (Req 2.5)
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->dispatch('close-create-plan-modal');
    }

    /**
     * Reset form to default values
     */
    public function resetForm(): void
    {
        $this->title = '';
        $this->characterId = null;
        $this->characterName = '';
        $this->careerStage = 'junior';
        $this->traineeClass = 'beginner';
        $this->storageMode = auth()->check() ? 'account' : 'local';
        $this->resetValidation();
    }

    /**
     * Handle character selection from autocomplete
     */
    public function selectCharacter(?string $characterId): void
    {
        $this->characterId = $characterId;

        if ($characterId) {
            $character = Umamusume::find($characterId);
            if ($character) {
                $this->characterName = $character->name;
                // Auto-populate title if empty
                if (empty($this->title)) {
                    $this->title = $character->name . "'s Training Plan";
                }
            }
        } else {
            $this->characterName = '';
        }
    }

    /**
     * Get available characters for autocomplete
     */
    #[Computed]
    public function characters(): \Illuminate\Database\Eloquent\Collection
    {
        return Umamusume::orderBy('name')->get(['id', 'name', 'team', 'rarity']);
    }

    /**
     * Check if user is authenticated (for storage mode options)
     */
    #[Computed]
    public function isAuthenticated(): bool
    {
        return auth()->check();
    }

    /**
     * Save the plan (Req 2.3, 2.6, 56.2, 56.3)
     */
    public function save(): void
    {
        // Validate title is not just whitespace (Req 2.4)
        $this->title = trim($this->title);
        if (empty($this->title)) {
            $this->addError('title', 'Plan title cannot be empty or whitespace only.');

            return;
        }

        $this->validate();

        if ($this->storageMode === 'local') {
            $this->createLocalPlan();
        } else {
            $this->createAccountPlan();
        }
    }

    /**
     * Create a local plan (stored in localStorage via JS)
     * Req 56.2, 56.3: Generate UUID, dispatch to JS for localStorage save
     */
    protected function createLocalPlan(): void
    {
        $uuid = (string) Str::uuid();

        // Prepare plan data for localStorage
        $planData = [
            'uuid' => $uuid,
            'storage_mode' => 'local',
            'title' => $this->title,
            'character_id' => $this->characterId,
            'character_name' => $this->characterName ?: $this->title,
            'career_stage' => $this->careerStage,
            'class' => $this->traineeClass,
            'status' => 'in_progress',
            'current_turn' => 1,
            // Default stats
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
            // Default growth rates (can be populated from character)
            'speed_growth' => 0,
            'stamina_growth' => 0,
            'power_growth' => 0,
            'guts_growth' => 0,
            'wit_growth' => 0,
            // Default aptitudes
            'turf_aptitude' => 'A',
            'dirt_aptitude' => 'A',
            'sprint_aptitude' => 'A',
            'mile_aptitude' => 'A',
            'medium_aptitude' => 'A',
            'long_aptitude' => 'A',
            'nige_aptitude' => 'A',
            'senkou_aptitude' => 'A',
            'sashi_aptitude' => 'A',
            'oikomi_aptitude' => 'A',
            // Status fields
            'mood' => 'normal',
            'conditions' => [],
            'energy' => 100,
            'total_sp_available' => 0,
            'stamina_percentage' => 100,
            // Empty relations
            'skills' => [],
            'turns' => [],
            'goals' => [],
            'race_predictions' => [],
            'support_cards' => [],
            'snapshots' => [],
            // Metadata
            'strategy' => null,
            'notes' => '',
            'image_path' => null,
        ];

        // If character selected, populate growth rates and aptitudes
        if ($this->characterId) {
            $character = Umamusume::find($this->characterId);
            if ($character) {
                if ($character->growth_rates) {
                    $rates = $character->growth_rates;
                    $planData['speed_growth'] = $rates['speed'] ?? 0;
                    $planData['stamina_growth'] = $rates['stamina'] ?? 0;
                    $planData['power_growth'] = $rates['power'] ?? 0;
                    $planData['guts_growth'] = $rates['guts'] ?? 0;
                    $planData['wit_growth'] = $rates['wit'] ?? 0;
                }
                if ($character->aptitudes) {
                    $apt = $character->aptitudes;
                    // Extract from nested structure: terrain, distance, style
                    $planData['turf_aptitude'] = $apt['terrain']['turf'] ?? 'A';
                    $planData['dirt_aptitude'] = $apt['terrain']['dirt'] ?? 'A';
                    $planData['sprint_aptitude'] = $apt['distance']['sprint'] ?? 'A';
                    $planData['mile_aptitude'] = $apt['distance']['mile'] ?? 'A';
                    $planData['medium_aptitude'] = $apt['distance']['medium'] ?? 'A';
                    $planData['long_aptitude'] = $apt['distance']['long'] ?? 'A';
                    $planData['nige_aptitude'] = $apt['style']['nige'] ?? 'A';
                    $planData['senkou_aptitude'] = $apt['style']['senkou'] ?? 'A';
                    $planData['sashi_aptitude'] = $apt['style']['sashi'] ?? 'A';
                    $planData['oikomi_aptitude'] = $apt['style']['oikomi'] ?? 'A';
                }
            }
        }

        // Dispatch event to save in localStorage and navigate
        $this->dispatch('create-local-plan', [
            'planData' => $planData,
            'redirectUrl' => "/plans/local/{$uuid}/edit",
        ]);

        // Close modal and show success
        $this->closeModal();
        $this->dispatch('plan-created', ['message' => 'Local plan created successfully!']);
        $this->dispatch('refreshPlans');
    }

    /**
     * Create an account plan (stored in database)
     * Req 2.3, 2.6: POST to server, navigate to /plans/{id}/edit
     */
    protected function createAccountPlan(): void
    {
        // Create plan in database
        $plan = Plan::create([
            'user_id' => auth()->id() ?? 1, // Default to guest user if not authenticated
            'name' => $this->characterName ?: $this->title,
            'plan_title' => $this->title,
            'career_stage' => $this->careerStage,
            'class' => $this->traineeClass,
            'status' => 'Planning',
            'storage_mode' => StorageMode::Account,
            'acquire_skill' => 'NO',
        ]);

        // Close modal
        $this->closeModal();

        // Dispatch success event
        $this->dispatch('plan-created', ['message' => 'Plan created successfully!']);
        $this->dispatch('refreshPlans');

        // Redirect to edit page
        $this->redirect(route('plans.edit', $plan->id));
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
