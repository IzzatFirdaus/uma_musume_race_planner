{{-- Convert to Account Button Component --}}
{{-- Implements FR-7.9: "Convert to Account" action on Local runs --}}
@if ($this->isVisible)
    @php
        $btnSize = match ($size) {
            'lg' => 'btn-lg',
            'md' => '',
            default => 'btn-sm',
        };
    @endphp
    <button type="button" class="btn btn-outline-success {{ $btnSize }} d-inline-flex align-items-center gap-1"
        wire:click="openConvertModal" wire:loading.attr="disabled"
        title="Convert to Account - Sync this plan across your devices"
        data-testid="convert-to-account-btn-{{ $planId }}">
        <i class="bi bi-cloud-upload" aria-hidden="true"></i>
        @if ($showLabel)
            <span>Convert to Account</span>
        @endif
        <span wire:loading wire:target="openConvertModal" class="spinner-border spinner-border-sm ms-1" role="status"
            aria-hidden="true"></span>
    </button>
@elseif (!$this->isAuthenticated && $this->plan?->storage_mode?->value === 'local')
    {{-- Show disabled button with sign-in hint for unauthenticated users --}}
    <button type="button"
        class="btn btn-outline-secondary {{ $btnSize ?? 'btn-sm' }} d-inline-flex align-items-center gap-1" disabled
        title="Sign in to convert this plan to your account"
        data-testid="convert-to-account-btn-disabled-{{ $planId }}">
        <i class="bi bi-cloud-upload" aria-hidden="true"></i>
        @if ($showLabel)
            <span>Convert to Account</span>
        @endif
        <i class="bi bi-lock ms-1" aria-hidden="true"></i>
    </button>
@endif
