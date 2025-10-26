{{--
    Motivation Indicator Partial
    Displays the current motivation level with official color codes and multipliers.
    Follows Umamusume: Pretty Derby mechanics and accessibility requirements.
--}}

@props(['motivation' => '1.00'])

@php
    $motivationLevels = [
        '1.04' => ['label' => 'Great', 'color' => 'var(--color-motivation-great)', 'desc' => '+4% stats'],
        '1.02' => ['label' => 'Good', 'color' => 'var(--color-motivation-good)', 'desc' => '+2% stats'],
        '1.00' => ['label' => 'Normal', 'color' => 'var(--color-motivation-normal)', 'desc' => '±0% stats'],
        '0.98' => ['label' => 'Bad', 'color' => 'var(--color-motivation-bad)', 'desc' => '-2% stats'],
        '0.96' => ['label' => 'Awful', 'color' => 'var(--color-motivation-awful)', 'desc' => '-4% stats'],
    ];
    $current = $motivationLevels[$motivation] ?? $motivationLevels['1.00'];
@endphp

<div class="d-flex align-items-center gap-2" role="status" aria-label="Motivation Level">
    <span class="fw-bold" style="color: {{ $current['color'] }};">
        {{ $current['label'] }}
    </span>
    <span class="badge" style="background: {{ $current['color'] }}; color: #fff;">
        {{ $current['desc'] }}
    </span>
</div>
