{{-- Livewire Layout Modals Component - Global modal system for notifications and interactions --}}
<div>
    {{-- Global Message Modal --}}
    <div class="modal fade"
         id="globalMessageModal"
         tabindex="-1"
         role="dialog"
         aria-labelledby="globalMessageModalLabel"
         aria-hidden="true"
         wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-theme">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="globalMessageModalLabel">
                        @if($messageModal['icon'] ?? null)
                            <i class="bi {{ $messageModal['icon'] }} me-2" aria-hidden="true"></i>
                        @endif
                        {{ $messageModal['title'] ?? 'Notification' }}
                    </h5>
                    <button type="button"
                            class="btn-close"
                            wire:click="closeMessageModal"
                            aria-label="Close modal">
                    </button>
                </div>
                <div class="modal-body">
                    @if($messageModal['message'] ?? null)
                        <p class="mb-0">{{ $messageModal['message'] }}</p>
                    @endif
                </div>
                @if($messageModal['actions'] ?? null)
                    <div class="modal-footer border-0 pt-0">
                        @foreach($messageModal['actions'] as $action)
                            <button type="button"
                                    class="btn {{ $action['class'] ?? 'btn-primary' }}"
                                    wire:click="{{ $action['action'] ?? 'closeMessageModal' }}"
                                    @if($action['dismiss'] ?? true) data-bs-dismiss="modal" @endif>
                                @if($action['icon'] ?? null)
                                    <i class="bi {{ $action['icon'] }} me-1" aria-hidden="true"></i>
                                @endif
                                {{ $action['text'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <div class="modal fade"
         id="confirmationModal"
         tabindex="-1"
         role="dialog"
         aria-labelledby="confirmationModalLabel"
         aria-hidden="true"
         wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content modal-theme">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="confirmationModalLabel">
                        <i class="bi bi-question-circle me-2 text-warning" aria-hidden="true"></i>
                        {{ $confirmationModal['title'] ?? 'Confirm Action' }}
                    </h5>
                    <button type="button"
                            class="btn-close"
                            wire:click="closeConfirmationModal"
                            aria-label="Close modal">
                    </button>
                </div>
                <div class="modal-body">
                    @if($confirmationModal['message'] ?? null)
                        <p class="mb-0">{{ $confirmationModal['message'] }}</p>
                    @endif

                    @if($confirmationModal['details'] ?? null)
                        <div class="mt-2 small text-muted">
                            {{ $confirmationModal['details'] }}
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button"
                            class="btn btn-secondary"
                            wire:click="closeConfirmationModal">
                        Cancel
                    </button>
                    <button type="button"
                            class="btn btn-danger"
                            wire:click="confirmAction">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                        {{ $confirmationModal['confirmText'] ?? 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Modal --}}
        {{-- Loading Modal --}}
    <div class="modal fade"
         id="loadingModal"
         tabindex="-1"
         role="dialog"
         aria-labelledby="loadingModalLabel"
         aria-hidden="true"
         data-bs-backdrop="static"
         data-bs-keyboard="false"
         wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content modal-theme border-0">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary mb-3"
                         style="width: 3rem; height: 3rem;"
                         role="status"
                         aria-hidden="true">
                    </div>
                    <div id="loadingModalLabel" class="h5 mb-0">
                        {{ $loadingModal['message'] ?? 'Loading...' }}
                    </div>
                    @if($loadingModal['details'] ?? null)
                        <div class="mt-2 small text-muted">
                            {{ $loadingModal['details'] }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Create Plan Modal --}}
    <livewire:quick-create-plan />
</div>
</div>

@push('styles')
<style>
    .modal-theme {
        background: rgba(var(--bs-body-bg-rgb), 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(var(--bs-border-color-rgb), 0.3);
        border-radius: 1rem;
    }

    .modal-theme .modal-header,
    .modal-theme .modal-footer {
        background: transparent;
    }

    /* Dark mode support */
    body.dark-mode .modal-theme {
        background: rgba(var(--bs-dark-rgb), 0.95);
        color: var(--bs-light);
        border-color: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    // Listen for modal events
    Livewire.on('show-message-modal', () => {
        new bootstrap.Modal(document.getElementById('globalMessageModal')).show();
    });

    Livewire.on('show-confirmation-modal', () => {
        new bootstrap.Modal(document.getElementById('confirmationModal')).show();
    });

    Livewire.on('show-loading-modal', () => {
        new bootstrap.Modal(document.getElementById('loadingModal')).show();
    });

    // Listen for quick create plan modal event
    Livewire.on('open-create-plan-modal', () => {
        new bootstrap.Modal(document.getElementById('createPlanModal')).show();
    });
    Livewire.on('plan-created', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('createPlanModal'));
        if (modal) modal.hide();
    // Trigger a DOM event so our JS can refresh the dashboard list
    document.dispatchEvent(new CustomEvent('planUpdated'));
    });

    // Close modal events
    Livewire.on('close-message-modal', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('globalMessageModal'));
        if (modal) modal.hide();
    });

    Livewire.on('close-confirmation-modal', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmationModal'));
        if (modal) modal.hide();
    });

    Livewire.on('close-loading-modal', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('loadingModal'));
        if (modal) modal.hide();
    });
});
</script>
@endpush
