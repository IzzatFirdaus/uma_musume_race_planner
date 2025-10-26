@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200';

    $variantClasses = match($variant) {
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-500 disabled:bg-blue-400 disabled:cursor-not-allowed',
        'secondary' => 'bg-slate-200 text-slate-900 hover:bg-slate-300 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600 disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:cursor-not-allowed',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 dark:hover:bg-red-500 disabled:bg-red-400 disabled:cursor-not-allowed',
        'ghost' => 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed',
        default => 'bg-blue-600 text-white hover:bg-blue-700',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
        default => 'px-4 py-2 text-base',
    };

    $focusClasses = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:focus-visible:outline-blue-500 focus-visible:ring-0';

    $mergedClasses = "$baseClasses $variantClasses $sizeClasses $focusClasses";
@endphp

<button
    type="{{ $type }}"
    @disabled($disabled)
    {{ $attributes->merge(['class' => $mergedClasses, 'aria-disabled' => $disabled ? 'true' : 'false']) }}
>
    {{ $slot }}
</button>
