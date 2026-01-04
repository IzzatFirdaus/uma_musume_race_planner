<div>
    {{-- QuickCreatePlan Modal - Task 7.1, 7.2 --}}
    {{-- Requirements: 2.1, 2.2, 2.3, 2.4, 2.6, 56.2, 56.3 --}}
    <div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true"
        wire:ignore.self x-data="quickCreatePlan()" x-on:show-create-plan-modal.window="openModal()"
        x-on:close-create-plan-modal.window="closeModal()"
        x-on:create-local-plan.window="handleLocalPlanCreate($event.detail)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-theme">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="createPlanModalLabel">
                        <i class="bi bi-plus-circle me-2" aria-hidden="true"></i>
                        Create New Plan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        data-testid="quick-create-close-btn"></button>
                </div>

                <form wire:submit.prevent="save" id="quickCreatePlanForm" novalidate>
                    <div class="modal-body">
                        {{-- Plan Title (Req 2.1, 2.4) --}}
                        <div class="mb-3">
                            <label for="quick_title" class="form-label">
                                Plan Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="quick_title" wire:model.live="title"
                                placeholder="e.g., Special Week's Training Plan" required
                                aria-describedby="titleFeedback" data-testid="quick-create-title-input">
                            @error('title')
                                <div class="invalid-feedback d-block" id="titleFeedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Character Selection (Req 2.2) --}}
                        <div class="mb-3">
                            <label for="quick_character" class="form-label">Character (Optional)</label>
                            <select class="form-select @error('characterId') is-invalid @enderror" id="quick_character"
                                wire:model.live="characterId" wire:change="selectCharacter($event.target.value)"
                                aria-describedby="characterFeedback" data-testid="quick-create-character-select">
                                <option value="">-- Select Character --</option>
                                @foreach ($characters as $character)
                                    <option value="{{ $character->id }}">
                                        {{ $character->name }}
                                        @if ($character->team)
                                            ({{ $character->team }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('characterId')
                                <div class="invalid-feedback d-block" id="characterFeedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Selecting a character will auto-populate growth rates and aptitudes.
                            </div>
                        </div>

                        {{-- Storage Mode Selector (Req 2.3, 56.3) --}}
                        <div class="mb-3">
                            <label class="form-label d-block">Storage Mode</label>
                            <div class="btn-group w-100" role="group" aria-label="Storage mode selection">
                                <input type="radio" class="btn-check" name="storageMode" id="storage_local"
                                    value="local" wire:model.live="storageMode" autocomplete="off"
                                    data-testid="quick-create-storage-local">
                                <label class="btn btn-outline-warning" for="storage_local"
                                    data-testid="quick-create-storage-local-label">
                                    <i class="bi bi-phone me-1" aria-hidden="true"></i>
                                    Local
                                </label>

                                <input type="radio" class="btn-check" name="storageMode" id="storage_account"
                                    value="account" wire:model.live="storageMode" autocomplete="off"
                                    {{ !$isAuthenticated ? 'disabled' : '' }}
                                    data-testid="quick-create-storage-account">
                                <label class="btn btn-outline-primary {{ !$isAuthenticated ? 'disabled' : '' }}"
                                    for="storage_account" data-testid="quick-create-storage-account-label">
                                    <i class="bi bi-cloud me-1" aria-hidden="true"></i>
                                    Account
                                </label>
                            </div>
                            @if (!$isAuthenticated)
                                <div class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <a href="{{ route('login') }}">Sign in</a> to save plans to your account.
                                </div>
                            @else
                                <div class="form-text">
                                    @if ($storageMode === 'local')
                                        <span class="text-warning">
                                            <i class="bi bi-phone me-1"></i>
                                            Plan will be stored in your browser's local storage.
                                        </span>
                                    @else
                                        <span class="text-primary">
                                            <i class="bi bi-cloud me-1"></i>
                                            Plan will be saved to your account.
                                        </span>
                                    @endif
                                </div>
                            @endif
                            @error('storageMode')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- Career Stage --}}
                            <div class="col-md-6 mb-3">
                                <label for="quick_career_stage" class="form-label">Career Stage</label>
                                <select class="form-select @error('careerStage') is-invalid @enderror"
                                    id="quick_career_stage" wire:model.live="careerStage" required
                                    aria-describedby="careerStageFeedback"
                                    data-testid="quick-create-career-stage-select">
                                    @foreach ($careerStageOptions as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                    @endforeach
                                </select>
                                @error('careerStage')
                                    <div class="invalid-feedback d-block" id="careerStageFeedback">{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Class --}}
                            <div class="col-md-6 mb-3">
                                <label for="quick_trainee_class" class="form-label">Class</label>
                                <select class="form-select @error('traineeClass') is-invalid @enderror"
                                    id="quick_trainee_class" wire:model.live="traineeClass" required
                                    aria-describedby="classFeedback" data-testid="quick-create-class-select">
                                    @foreach ($classOptions as $option)
                                        <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                    @endforeach
                                </select>
                                @error('traineeClass')
                                    <div class="invalid-feedback d-block" id="classFeedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            data-testid="quick-create-cancel-btn">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-uma" wire:loading.attr="disabled" wire:target="save"
                            data-testid="quick-create-submit-btn">
                            <span wire:loading.remove wire:target="save">
                                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                                Create Plan
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Creating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        Alpine.data('quickCreatePlan', () => ({
            modal: null,

            init() {
                // Initialize Bootstrap modal reference
                this.modal = new bootstrap.Modal(document.getElementById('createPlanModal'));
            },

            openModal() {
                if (this.modal) {
                    this.modal.show();
                }
            },

            closeModal() {
                if (this.modal) {
                    this.modal.hide();
                }
            },

            /**
             * Handle local plan creation (Req 56.2, 56.3)
             * Saves plan to localStorage and redirects to edit page
             */
            handleLocalPlanCreate(detail) {
                const {
                    planData,
                    redirectUrl
                } = detail[0] || detail;

                try {
                    // Import or use the LocalRunStorageService
                    if (typeof window.localRunStorage !== 'undefined') {
                        // Use the existing service
                        window.localRunStorage.create(planData);
                    } else {
                        // Fallback: Direct localStorage save with versioned schema
                        const storageKey = 'uma_local_runs';
                        let store = JSON.parse(localStorage.getItem(storageKey) || 'null');

                        if (!store) {
                            store = {
                                schema_version: '1.0',
                                runs: [],
                                last_modified: new Date().toISOString()
                            };
                        }

                        // Add timestamps if not present
                        planData.created_at = planData.created_at || new Date().toISOString();
                        planData.updated_at = new Date().toISOString();

                        store.runs.push(planData);
                        store.last_modified = new Date().toISOString();

                        localStorage.setItem(storageKey, JSON.stringify(store));
                    }

                    // Close modal
                    this.closeModal();

                    // Navigate to edit page
                    window.location.href = redirectUrl;
                } catch (error) {
                    console.error('Failed to create local plan:', error);

                    // Show error notification
                    if (typeof window.dispatchEvent === 'function') {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                type: 'error',
                                message: 'Failed to create local plan. Please try again.'
                            }
                        }));
                    }
                }
            }
        }));
    </script>
@endscript
