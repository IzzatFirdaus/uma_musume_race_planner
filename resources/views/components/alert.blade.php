@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $roleAttribute = $type === 'error' ? 'alert' : 'status';
    $ariaLive = $type === 'error' ? 'assertive' : 'polite';

    $baseClasses = 'rounded-lg p-4 mb-4';

    $typeClasses = match($type) {
        'success' => 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900 dark:border-green-700 dark:text-green-100',
        'error' => 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900 dark:border-red-700 dark:text-red-100',
        'warning' => 'bg-amber-50 border border-amber-200 text-amber-800 dark:bg-amber-900 dark:border-amber-700 dark:text-amber-100',
        'info' => 'bg-blue-50 border border-blue-200 text-blue-800 dark:bg-blue-900 dark:border-blue-700 dark:text-blue-100',
        default => 'bg-blue-50 border border-blue-200 text-blue-800',
    };

    $iconClasses = match($type) {
        'success' => 'text-green-600 dark:text-green-400',
        'error' => 'text-red-600 dark:text-red-400',
        'warning' => 'text-amber-600 dark:text-amber-400',
        'info' => 'text-blue-600 dark:text-blue-400',
        default => 'text-blue-600',
    };
@endphp

<div
    role="{{ $roleAttribute }}"
    aria-live="{{ $ariaLive }}"
    aria-atomic="true"
    {{ $attributes->merge(['class' => "$baseClasses $typeClasses"]) }}
>
    <div class="flex items-start gap-3">
        <!-- Icon -->
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $iconClasses }}" fill="currentColor" viewBox="0 0 20 20">
            @if ($type === 'success')
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            @elseif ($type === 'error')
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            @elseif ($type === 'warning')
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            @else
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            @endif
        </svg>

        <!-- Content -->
        <div class="flex-1">
            @if ($title)
                <h3 class="font-semibold mb-1">{{ $title }}</h3>
            @endif
            <div class="text-sm">
                {{ $slot }}
            </div>
        </div>

        <!-- Dismiss button -->
        @if ($dismissible)
            <button
                type="button"
                class="flex-shrink-0 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-current rounded p-1 hover:opacity-70 transition"
                @click="$el.closest('[role=\'alert\'], [role=\'status\']')?.remove()"
                aria-label="Dismiss alert"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>
</div>
