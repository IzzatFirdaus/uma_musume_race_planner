@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg motion-safe:transition-colors motion-safe:duration-200 motion-reduce:transition-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900';

    $variantClasses = match($variant) {
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 dark:hover:bg-blue-500 disabled:bg-blue-400 disabled:cursor-not-allowed',
        'secondary' => 'bg-slate-200 text-slate-900 hover:bg-slate-300 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600 disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:cursor-not-allowed',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 dark:hover:bg-red-500 disabled:bg-red-400 disabled:cursor-not-allowed',
        'ghost' => 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed',
        default => 'bg-blue-600 text-white hover:bg-blue-700',
    };

    $sizeClasses = match($size) {
        'sm' => 'min-h-10 px-3 py-1.5 text-sm',
        'md' => 'min-h-11 px-4 py-2 text-base',
        'lg' => 'min-h-12 px-6 py-3 text-lg',
        default => 'min-h-11 px-4 py-2 text-base',
    };

    $mergedClasses = "$baseClasses $variantClasses $sizeClasses";
@endphp

<button
    type="{{ $type }}"
    @disabled($disabled)
    {{ $attributes->merge(['class' => $mergedClasses, 'aria-disabled' => $disabled ? 'true' : 'false']) }}
>
    {{ $slot }}
</button>
