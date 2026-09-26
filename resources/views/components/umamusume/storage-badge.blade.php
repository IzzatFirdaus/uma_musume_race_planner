@props([
    'mode' => 'local',
    'showLabel' => true,
    'size' => 'sm',
])

@php
    use App\Enums\StorageMode;

    $storageMode = $mode instanceof StorageMode ? $mode : StorageMode::from((string) $mode);

    $sizeClasses = match ($size) {
        'lg' => 'px-3 py-1.5 text-sm',
        'md' => 'px-2.5 py-1 text-xs',
        default => 'px-2 py-0.5 text-xs',
    };

    $modeClasses = match ($storageMode) {
        StorageMode::Local => 'bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-900/50 dark:text-blue-200 dark:border-blue-700',
        StorageMode::Account => 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/50 dark:text-green-200 dark:border-green-700',
    };
@endphp

<span
    {{ $attributes->merge([
        'class' => "inline-flex items-center gap-1 rounded-full font-medium {$sizeClasses} {$modeClasses}",
        'title' => $storageMode->description(),
        'data-testid' => 'storage-badge-' . $storageMode->value,
        'role' => 'status',
        'aria-label' => 'Storage mode: ' . $storageMode->label(),
    ]) }}
>
    @if ($storageMode === StorageMode::Local)
        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
    @else
        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
        </svg>
    @endif

    @if ($showLabel)
        <span>{{ $storageMode->label() }}</span>
    @endif
</span>
