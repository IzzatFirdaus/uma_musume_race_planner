{{--
    Stat Meter Blade Component
    Displays a stat bar with official color, cap, and icon per Umamusume: Pretty Derby mechanics.
    Props:
        - stat: string (e.g. 'Speed', 'Stamina', 'Power', 'Guts', 'Wisdom')
        - value: int (current stat value)
        - cap: int (default 1200, absolute 2000)
--}}
@props([
    'stat',
    'value' => 0,
    'cap' => 1200,
    'absolute' => 2000
])

@php
    $statMap = [
        'Speed' => ['color' => 'var(--color-stat-speed)', 'icon' => '⚡'],
        'Stamina' => ['color' => 'var(--color-stat-stamina)', 'icon' => '🛡️'],
        'Power' => ['color' => 'var(--color-stat-power)', 'icon' => '🔥'],
        'Guts' => ['color' => 'var(--color-stat-guts)', 'icon' => '💪'],
        'Wisdom' => ['color' => 'var(--color-stat-wisdom)', 'icon' => '🧠'],
    ];
    $meta = $statMap[$stat] ?? $statMap['Speed'];
    $percent = min(100, ($value / $cap) * 100);
    $isOverCap = $value > $cap;
    $isOverAbsolute = $value > $absolute;
@endphp

<div
    class="d-flex align-items-center gap-2"
    role="progressbar"
    aria-label="{{ $stat }} progress"
    aria-valuenow="{{ $value }}"
    aria-valuemin="0"
    aria-valuemax="{{ $absolute }}"
    aria-valuetext="{{ $value }} of {{ $cap }}">
    <span class="stat-icon" style="color: {{ $meta['color'] }};">{{ $meta['icon'] }}</span>
    <span class="fw-bold">{{ strtoupper($stat) }}</span>
    <div class="flex-grow-1 mx-2 stat-bar" style="background: #e9ecef; height: 12px; border-radius: 6px; overflow: hidden;">
        <div class="bar-{{ strtolower($stat) }}" style="width: {{ $percent }}%; background: {{ $meta['color'] }}; height: 100%; border-radius: 6px;"></div>
    </div>
    <span class="fw-bold" style="color: {{ $meta['color'] }};">
        {{ $value }}
        <span class="small text-muted">/ {{ $cap }}</span>
        @if($isOverCap)
            <span class="badge bg-warning text-dark ms-1">Over Cap</span>
        @endif
        @if($isOverAbsolute)
            <span class="badge bg-danger ms-1">Max</span>
        @endif
    </span>
</div>
