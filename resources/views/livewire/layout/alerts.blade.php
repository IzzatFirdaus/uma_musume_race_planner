{{-- Livewire Layout Alerts Component - Global alert system with theme support --}}
<div>
    @if($alerts && count($alerts) > 0)
        <div class="alert-container position-fixed top-0 start-50 translate-middle-x"
             style="z-index: 1050; margin-top: 1rem;">
            @foreach($alerts as $index => $alert)
                <div class="alert alert-{{ $alert['type'] ?? 'info' }} alert-dismissible fade show mb-2 shadow-sm alert-theme"
                     role="alert"
                     wire:key="alert-{{ $index }}"
                     x-data="{ show: true }"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95">

                    <div class="d-flex align-items-center">
                        {{-- Alert icon --}}
                        <div class="flex-shrink-0 me-2">
                            @switch($alert['type'] ?? 'info')
                                @case('success')
                                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                                    @break
                                @case('error')
                                @case('danger')
                                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                    @break
                                @case('warning')
                                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                                    @break
                                @default
                                    <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                            @endswitch
                        </div>

                        {{-- Alert content --}}
                        <div class="flex-grow-1">
                            @if(isset($alert['title']))
                                <div class="fw-bold">{{ $alert['title'] }}</div>
                            @endif
                            <div>{{ $alert['message'] }}</div>
                        </div>
                    </div>

                    {{-- Dismiss button --}}
                    <button type="button"
                            class="btn-close"
                            wire:click="removeAlert({{ $index }})"
                            aria-label="Close alert"
                            @click="show = false"></button>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Auto-dismiss alerts after timeout --}}
    @if($alerts && count($alerts) > 0)
        <div x-data="{
            init() {
                this.alerts.forEach((alert, index) => {
                    if (alert.timeout && alert.timeout > 0) {
                        setTimeout(() => {
                            $wire.removeAlert(index);
                        }, alert.timeout);
                    }
                });
            },
            alerts: @js($alerts)
        }"></div>
    @endif
</div>

@push('styles')
<style>
    .alert-theme {
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 0.75rem;
        min-width: 300px;
        max-width: 500px;
    }

    .alert-container {
        pointer-events: none;
    }

    .alert-container .alert {
        pointer-events: auto;
    }

    /* Dark mode adjustments */
    body.dark-mode .alert-theme {
        background: rgba(var(--bs-dark-rgb), 0.9);
        border-color: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush
