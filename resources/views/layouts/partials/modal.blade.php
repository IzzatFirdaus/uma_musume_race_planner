{{-- Modal Partial - Reusable modal template with accessibility --}}
@php
    $modalId = $modalId ?? 'genericModal';
    $modalTitle = $modalTitle ?? 'Modal';
    $modalSize = $modalSize ?? 'modal-lg'; // modal-sm, modal-lg, modal-xl
    $modalCentered = $modalCentered ?? true;
    $modalScrollable = $modalScrollable ?? false;
    $staticBackdrop = $staticBackdrop ?? false;
@endphp

<div class="modal fade"
     id="{{ $modalId }}"
     tabindex="-1"
     role="dialog"
     aria-labelledby="{{ $modalId }}Label"
     aria-hidden="true"
     @if($staticBackdrop) data-bs-backdrop="static" @endif>

    <div class="modal-dialog {{ $modalSize }} @if($modalCentered) modal-dialog-centered @endif @if($modalScrollable) modal-dialog-scrollable @endif"
         role="document">

        <div class="modal-content modal-theme">
            <div class="modal-header border-bottom modal-header-theme">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    @if(isset($modalIcon))
                        <i class="bi {{ $modalIcon }} me-2" aria-hidden="true"></i>
                    @endif
                    {{ $modalTitle }}
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close modal">
                </button>
            </div>

            <div class="modal-body modal-body-theme">
                    @if(isset($slot))
                        {!! $slot !!}
                    @else
                        <p class="text-muted">Modal content will be loaded here...</p>
                    @endif
            </div>

            @if(isset($modalFooter) || isset($modalActions))
                <div class="modal-footer border-top modal-footer-theme">
                        @if(isset($modalFooter))
                            {!! $modalFooter !!}
                        @endif

                        @if(isset($modalActions))
                            {!! $modalActions !!}
                        @else
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .modal-theme {
        --bs-modal-border-radius: 1rem;
    }

    .modal-header-theme {
        background: var(--bs-light);
    }

    .modal-body-theme {
        background: var(--bs-body-bg);
    }

    .modal-footer-theme {
        background: var(--bs-light);
    }

    /* Dark mode support */
    body.dark-mode .modal-header-theme,
    body.dark-mode .modal-footer-theme {
        background: var(--bs-dark);
        border-color: var(--bs-border-color-translucent);
    }

    body.dark-mode .modal-body-theme {
        background: var(--bs-dark);
        color: var(--bs-light);
    }
</style>
@endpush
