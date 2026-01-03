{{-- Dirty State Warning Component --}}
{{-- Implements FR-7B.3: Dirty-state warning before close --}}
<div x-data="dirtyStateHandler(@js($isDirty))" data-testid="dirty-state-warning">
    {{-- Dirty indicator (optional visual cue) --}}
    @if ($isDirty)
        <div class="position-fixed bottom-0 start-0 m-3 z-3" data-testid="unsaved-indicator">
            <span class="badge bg-warning text-dark d-flex align-items-center gap-1 px-3 py-2">
                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                Unsaved changes
            </span>
        </div>
    @endif

    {{-- Warning Modal --}}
    @if ($showWarning)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="dirtyWarningTitle"
            aria-modal="true" data-testid="dirty-warning-modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title d-flex align-items-center gap-2" id="dirtyWarningTitle">
                            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                            Unsaved Changes
                        </h5>
                        <button type="button" class="btn-close" wire:click="cancelNavigation"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">
                            You have unsaved changes. Are you sure you want to leave? Your changes will be lost.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelNavigation"
                            data-testid="dirty-warning-stay">
                            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                            Stay on Page
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="discardAndProceed"
                            data-testid="dirty-warning-discard">
                            <i class="bi bi-x-circle me-1" aria-hidden="true"></i>
                            Discard Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>

@script
    <script>
        Alpine.data('dirtyStateHandler', (initialDirty) => ({
            isDirty: initialDirty,

            init() {
                // Listen for form changes
                this.$watch('isDirty', (value) => {
                    if (value) {
                        // Add beforeunload listener
                        window.addEventListener('beforeunload', this.handleBeforeUnload);
                    } else {
                        window.removeEventListener('beforeunload', this.handleBeforeUnload);
                    }
                });

                // Initial setup
                if (this.isDirty) {
                    window.addEventListener('beforeunload', this.handleBeforeUnload);
                }

                // Listen for Livewire events
                Livewire.on('form-dirty', () => {
                    this.isDirty = true;
                });

                Livewire.on('form-clean', () => {
                    this.isDirty = false;
                });
            },

            handleBeforeUnload(e) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            },

            destroy() {
                window.removeEventListener('beforeunload', this.handleBeforeUnload);
            }
        }));
    </script>
@endscript
