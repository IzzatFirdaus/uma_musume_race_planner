{{-- Loading Overlay Partial - Reusable loading spinner with theme support --}}
@php
    $overlayId = $overlayId ?? 'loadingOverlay';
    $message = $message ?? 'Loading...';
    $visible = $visible ?? false;
@endphp

<div id="{{ $overlayId }}"
    class="loading-overlay position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 loading-overlay-theme"
    style="z-index: 9999; {{ $visible ? 'display: flex;' : 'display: none;' }}"
    role="dialog"
    aria-labelledby="{{ $overlayId }}Label"
    aria-hidden="{{ $visible ? 'false' : 'true' }}"
    aria-modal="{{ $visible ? 'true' : 'false' }}"
    tabindex="-1">

    <div class="text-center text-white">
       <div class="spinner-border mb-3"
           style="width: 3rem; height: 3rem;"
           role="status"
           aria-hidden="true"
           aria-label="Loading indicator">
       </div>
        <div id="{{ $overlayId }}Label" class="h5 mb-0">{{ $message }}</div>
    </div>
</div>

@push('styles')
<style>
    .loading-overlay-theme {
        backdrop-filter: blur(2px);
    }

    .loading-overlay .spinner-border {
        color: #fff;
    }

    /* Dark mode considerations */
    body.dark-mode .loading-overlay-theme {
        background-color: rgba(0, 0, 0, 0.7);
    }
</style>
@endpush
