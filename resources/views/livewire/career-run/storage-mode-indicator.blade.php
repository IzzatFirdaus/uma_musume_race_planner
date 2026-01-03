{{-- Storage Mode Indicator Component --}}
{{-- Implements FR-7.8: Storage Mode badge on each run --}}
@if ($storageMode)
    @php
        $sizeClasses = match ($size) {
            'lg' => 'px-3 py-2',
            'md' => 'px-2 py-1',
            default => 'px-2 py-1',
        };
        $fontSize = match ($size) {
            'lg' => '0.85rem',
            'md' => '0.75rem',
            default => '0.65rem',
        };
    @endphp
    <span
        class="badge {{ $storageMode->value === 'local' ? 'bg-warning text-dark' : 'bg-primary text-white' }} rounded-pill d-inline-flex align-items-center gap-1 {{ $sizeClasses }}"
        style="font-size: {{ $fontSize }};" title="{{ $storageMode->description() }}"
        data-testid="storage-mode-indicator" role="status" aria-label="Storage mode: {{ $storageMode->label() }}">
        <i class="bi {{ $storageMode->iconClass() }}" aria-hidden="true"></i>
        @if ($showLabel)
            <span>{{ $storageMode->label() }}</span>
        @endif
    </span>
@endif
