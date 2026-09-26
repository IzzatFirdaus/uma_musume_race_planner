<?php

declare(strict_types=1);

namespace App\Livewire\Plans;

use App\Enums\StorageMode;
use App\Services\LocalRunStorageService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Local Plan View Component
 *
 * Renders and edits a Local-mode career run — one persisted in the browser via
 * `window.localRunStorage` rather than in the database.
 * Routes: /plans/local/{uuid}, /plans/local/{uuid}/view, /plans/local/{uuid}/edit
 *
 * WHY THIS COMPONENT IS SHAPED THIS WAY
 *
 * A Livewire component executes on the server, and the server has no copy of a
 * Local run. It therefore cannot hydrate itself in `mount()`. The browser holds
 * the only authoritative copy, so the flow is:
 *
 *   1. mount() asks the client for the run by UUID.
 *   2. The client answers with `load-local-run`, which sets the public props.
 *   3. On save, the component validates server-side, then asks the client to
 *      write the payload back to localStorage.
 *
 * Server-side validation still runs on every save, because it is the only place
 * the domain rules (stat ranges, turn bounds, skill-state coupling) are encoded.
 * The client is never trusted; it is only the persistence layer.
 *
 * NOTE: because the run lives only in this browser, a different browser, a
 * cleared cache, or private-browsing exit means the data is gone. Converting to
 * an Account run is the only durable option.
 */
#[Layout('components.layout')]
class LocalPlanView extends Component
{
    /**
     * Injected, never public: a public typed property would be captured into
     * the Livewire payload on every request.
     */
    protected LocalRunStorageService $localRunStorage;

    public string $uuid = '';

    public bool $isEditMode = false;

    public string $storageMode = 'local';

    public bool $notFound = false;

    public bool $isLoading = true;

    public bool $isDirty = false;

    public bool $isSaving = false;

    /** Raw payload as held by the browser store. */
    public array $run = [];

    /** Flattened `career_run` fields, bound to the edit form. */
    public array $form = [];

    /**
     * @param  array<string, string>  $form
     */
    /**
     * @param  LocalRunStorageService  $localRunStorage  Injected by Livewire.
     */
    public function mount(string $uuid, LocalRunStorageService $localRunStorage): void
    {
        $this->localRunStorage = $localRunStorage;

        $this->uuid = $uuid;
        $this->storageMode = StorageMode::Local->value;
        $this->isEditMode = request()->route()?->getName() === 'plans.local.edit';

        // The browser owns the data. Ask for it; render() shows a loading state
        // until `load-local-run` arrives.
        $this->dispatch('load-local-run', uuid: $uuid);
    }

    /**
     * Receive the run from the client.
     *
     * @param  array<string, mixed>  $run
     */
    #[On('load-local-run')]
    public function loadRun(array $run): void
    {
        $this->isLoading = false;

        if ($run === [] || ($run['id'] ?? null) !== $this->uuid) {
            $this->notFound = true;
            $this->run = [];
            $this->form = [];

            return;
        }

        // Re-validate and migrate server-side so a hand-edited or stale
        // localStorage payload cannot bypass the schema.
        $migrated = $this->localRunStorage->migrateSchema($run);
        $validation = $this->localRunStorage->validateStructure($migrated);

        $this->run = $migrated;
        $this->form = $this->fieldsFromRun($migrated);
        $this->isDirty = false;

        if (! $validation['valid']) {
            $this->dispatch(
                'toast',
                type: 'warning',
                message: 'This Local run is incomplete: '.implode(' ', $validation['errors'])
            );
        }
    }

    /**
     * Persist the edited fields back to the browser store.
     */
    public function save(): void
    {
        if (! $this->isEditMode) {
            return;
        }

        $this->isSaving = true;
        $this->resetErrorBag();
        $this->validate();

        $payload = $this->run;
        $payload['career_run'] = array_merge($payload['career_run'] ?? [], $this->form);

        $validation = $this->localRunStorage->validateStructure($payload);

        if (! $validation['valid']) {
            $this->addError('run', implode(' ', $validation['errors']));
            $this->isSaving = false;

            return;
        }

        // The client performs the actual localStorage write.
        $this->dispatch('save-local-run', uuid: $this->uuid, run: $payload);
        $this->isSaving = false;
        $this->isDirty = false;
    }

    /**
     * Client confirms the write succeeded.
     */
    #[On('local-run-saved')]
    public function saved(): void
    {
        $this->isDirty = false;
        $this->dispatch('toast', type: 'success', message: 'Local career run saved.');
    }

    /**
     * Client reports the write failed, e.g. a storage quota error.
     */
    #[On('local-run-save-failed')]
    public function saveFailed(string $message = ''): void
    {
        $this->isDirty = true;
        $this->dispatch(
            'toast',
            type: 'error',
            message: $message !== '' ? $message : 'Could not save. Browser storage may be full.'
        );
    }

    /**
     * Delete this run from the browser store.
     */
    public function deleteRun(): void
    {
        $this->dispatch('delete-local-run', uuid: $this->uuid);
    }

    /**
     * Offer conversion to an Account run, which is the only durable path.
     */
    public function convert(): void
    {
        $this->dispatch('open-convert-modal', uuid: $this->uuid, run: $this->run);
    }

    /**
     * Refresh the browser copy, discarding unsaved edits.
     */
    public function reload(): void
    {
        $this->isLoading = true;
        $this->notFound = false;
        $this->dispatch('load-local-run', uuid: $this->uuid);
    }

    public function markDirty(): void
    {
        $this->isDirty = true;
    }

    /**
     * Validation rules for the editable `career_run` fields.
     *
     * These mirror the domain rules in App\Enums and the database constraints.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'form.plan_title' => ['nullable', 'string', 'max:255'],
            'form.name' => ['required', 'string', 'max:255'],
            'form.career_stage' => ['nullable', 'string', 'in:junior,classic,senior'],
            'form.status' => ['nullable', 'string', 'max:50'],
            'form.current_turn' => ['nullable', 'integer', 'min:1', 'max:78'],
            'form.total_sp_available' => ['nullable', 'integer', 'min:0'],
            'form.stamina_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'form.name.required' => 'The trainee name is required.',
            'form.current_turn.max' => 'A career is 78 turns; turn numbers must be between 1 and 78.',
            'form.stamina_percentage.max' => 'Stamina must be between 0 and 100.',
            'form.career_stage.in' => 'Career stage must be junior, classic, or senior.',
        ];
    }

    /**
     * @param  array<string, mixed>  $run
     * @return array<string, mixed>
     */
    private function fieldsFromRun(array $run): array
    {
        $career = $run['career_run'] ?? [];

        return [
            'plan_title' => $career['plan_title'] ?? null,
            'name' => $career['name'] ?? null,
            'scenario' => $career['scenario'] ?? 'URA',
            'career_stage' => $career['career_stage'] ?? null,
            'status' => $career['status'] ?? null,
            'class' => $career['class'] ?? null,
            'current_turn' => $career['current_turn'] ?? null,
            'current_race' => $career['current_race'] ?? null,
            'total_sp_available' => $career['total_sp_available'] ?? null,
            'stamina_percentage' => $career['stamina_percentage'] ?? null,
            'mood' => $career['mood'] ?? null,
            'conditions' => $career['conditions'] ?? null,
            'notes' => $career['notes'] ?? null,
        ];
    }

    public function render()
    {
        return view('livewire.plans.local-plan-view', [
            'statProgress' => $this->run['stat_progress'] ?? [],
            'skills' => $this->run['skills'] ?? [],
            'goals' => $this->run['goals'] ?? [],
            'racePredictions' => $this->run['race_predictions'] ?? [],
            'snapshots' => $this->run['snapshots'] ?? [],
            'characterName' => $this->run['character_name'] ?? null,
            'updatedAt' => $this->run['updated_at'] ?? null,
        ]);
    }
}
