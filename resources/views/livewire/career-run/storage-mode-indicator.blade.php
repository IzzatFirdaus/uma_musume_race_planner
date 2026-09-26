{{-- Storage Mode Indicator Component --}}
@if ($storageMode)
    <x-umamusume.storage-badge :mode="$storageMode" :show-label="$showLabel" :size="$size" data-testid="storage-mode-indicator" />
@endif
