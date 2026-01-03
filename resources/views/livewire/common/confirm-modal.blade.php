{{-- Confirm Modal Component --}}
{{-- Implements NFR-2.6: Focus trap for modals --}}
@if ($show)
    <div x-data="confirmModalFocusTrap()" x-init="init()" x-on:keydown.escape.window="$wire.close()"
        class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="confirmModalTitle" aria-modal="true"
        data-testid="confirm-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" x-ref="modalContent">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="confirmModalTitle">
                        <i class="bi {{ $icon }}" aria-hidden="true"></i>
                        {{ $title }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="close" aria-label="Close"
                        data-testid="confirm-modal-close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{!! $message !!}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="close"
                        data-testid="confirm-modal-cancel">
                        {{ $cancelText }}
                    </button>
                    <button type="button" class="btn {{ $confirmClass }}" wire:click="confirm"
                        wire:loading.attr="disabled" data-testid="confirm-modal-confirm" x-ref="confirmBtn">
                        <span wire:loading.remove wire:target="confirm">{{ $confirmText }}</span>
                        <span wire:loading wire:target="confirm">
                            <span class="spinner-border spinner-border-sm me-1" role="status"
                                aria-hidden="true"></span>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif

@script
    <script>
        Alpine.data('confirmModalFocusTrap', () => ({
            focusableElements: [],
            firstFocusable: null,
            lastFocusable: null,

            init() {
                this.$nextTick(() => {
                    this.setupFocusTrap();
                    // Focus the confirm button initially
                    if (this.$refs.confirmBtn) {
                        this.$refs.confirmBtn.focus();
                    }
                });
            },

            setupFocusTrap() {
                const modal = this.$refs.modalContent;
                if (!modal) return;

                this.focusableElements = modal.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );

                if (this.focusableElements.length > 0) {
                    this.firstFocusable = this.focusableElements[0];
                    this.lastFocusable = this.focusableElements[this.focusableElements.length - 1];
                }

                // Handle tab key for focus trap
                modal.addEventListener('keydown', (e) => {
                    if (e.key !== 'Tab') return;

                    if (e.shiftKey) {
                        // Shift + Tab
                        if (document.activeElement === this.firstFocusable) {
                            e.preventDefault();
                            this.lastFocusable?.focus();
                        }
                    } else {
                        // Tab
                        if (document.activeElement === this.lastFocusable) {
                            e.preventDefault();
                            this.firstFocusable?.focus();
                        }
                    }
                });
            }
        }));
    </script>
@endscript
