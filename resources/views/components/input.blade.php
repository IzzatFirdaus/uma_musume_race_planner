@props([
    'type' => 'text',
    'id' => null,
    'name' => null,
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $errorId = $error ? "{$id}-error" : null;
    $baseClasses = 'block w-full px-4 py-2 border rounded-lg font-normal text-base transition-colors duration-200';
    $normalClasses = 'border-slate-300 bg-white text-slate-900 placeholder-slate-500 hover:border-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-1 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:placeholder-slate-400 dark:hover:border-slate-500 dark:focus-visible:ring-blue-400';
    $errorClasses = $error
        ? 'border-red-500 bg-red-50 text-slate-900 dark:bg-red-900 dark:border-red-400 focus-visible:ring-red-500 dark:focus-visible:ring-red-400'
        : $normalClasses;

    $disabledClasses = $disabled ? 'bg-slate-100 text-slate-500 cursor-not-allowed dark:bg-slate-700 dark:text-slate-400' : '';

    $mergedClasses = "$baseClasses $errorClasses $disabledClasses";
@endphp

<div class="mb-4">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
            {{ $label }}
            @if ($required)
                <span aria-label="required" class="text-red-600 dark:text-red-400">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $id }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        value="{{ old($name, $value) }}"
        @required($required)
        @disabled($disabled)
        aria-describedby="{{ $errorId }}"
        aria-invalid="{{ $error ? 'true' : 'false' }}"
        {{ $attributes->merge(['class' => $mergedClasses]) }}
    />

    @if ($error)
        <div id="{{ $errorId }}" role="alert" class="mt-1 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18.101 12.93a1 1 0 00-1.401-1.401L10 14.596 4.3 9.396a1 1 0 10-1.4 1.401l6.1 6.2a1 1 0 001.401 0l8.7-8.8z" clip-rule="evenodd" />
            </svg>
            {{ $error }}
        </div>
    @endif
</div>
