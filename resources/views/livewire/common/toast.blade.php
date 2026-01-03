{{-- Toast Notification Component --}}
{{-- Implements FR-13.4: Toast notifications with aria-live --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;" data-testid="toast-container">
    {{-- Aria-live region for screen reader announcements --}}
    <div class="visually-hidden" role="status" aria-live="polite" aria-atomic="true" id="toast-announcer">
        @foreach ($toasts as $toast)
            {{ $toast['message'] }}
        @endforeach
    </div>

    @foreach ($toasts as $toast)
        <div x-data="{ show: true }" x-init="setTimeout(() => { show = false;
            $wire.removeToast('{{ $toast['id'] }}') }, {{ $toast['duration'] }})" x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-full"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-full"
            class="toast show align-items-center border-0 {{ $toast['class'] }}" role="alert" aria-live="assertive"
            aria-atomic="true" wire:key="toast-{{ $toast['id'] }}" data-testid="toast-{{ $toast['id'] }}">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi {{ $toast['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $toast['message'] }}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    x-on:click="show = false; $wire.removeToast('{{ $toast['id'] }}')"
                    aria-label="Close toast notification"></button>
            </div>
        </div>
    @endforeach
</div>

@script
    <script>
        // Listen for toast-added events to handle auto-dismiss
        Livewire.on('toast-added', (data) => {
            // The auto-dismiss is handled by Alpine.js x-init
        });
    </script>
@endscript
