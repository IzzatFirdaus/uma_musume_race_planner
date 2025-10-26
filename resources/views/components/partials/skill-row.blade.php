{{--
    Skill Row Blade Component
    Displays a skill selector row with official name, SP cost, type, and activation text.
    Props:
        - skills: array (official skills list)
        - row: array (current skill row data)
        - index: int (row index)
--}}
@props([
    'skills' => [],
    'row' => [],
    'index' => 0
])

@php
    $types = [
        'Unique' => 'bg-warning',
        'Speed' => 'bg-info',
        'Acceleration' => 'bg-primary',
        'Recovery' => 'bg-success',
        'Debuff' => 'bg-danger',
        'Other' => 'bg-secondary',
    ];
    $type = $row['type'] ?? 'Other';
    $badge = $types[$type] ?? 'bg-secondary';
@endphp
<tr wire:key="skill-{{ $index }}">
    <td>
        <select
            class="form-select form-select-sm"
            wire:model.lazy="skills.{{ $index }}.name"
            aria-label="Select skill for row {{ $index + 1 }}">
            <option value="">Select Skill</option>
            @foreach($skills as $skill)
                <option value="{{ $skill['name'] }}" data-cost="{{ $skill['cost'] }}" data-type="{{ $skill['type'] }}">
                    {{ $skill['name'] }} ({{ $skill['cost'] }} SP)
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <span class="badge {{ $badge }}">{{ $type }}</span>
    </td>
    <td class="text-center">
        <input
            type="checkbox"
            class="form-check-input"
            wire:model.live="skills.{{ $index }}.acquired"
            aria-label="Mark skill as acquired for row {{ $index + 1 }}">
    </td>
    <td>
        <input
            type="text"
            class="form-control form-control-sm"
            placeholder="Activation Conditions"
            wire:model.lazy="skills.{{ $index }}.conditions"
            aria-label="Activation conditions for skill row {{ $index + 1 }}">
    </td>
    <td>
        <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            title="Remove"
            aria-label="Remove skill row {{ $index + 1 }}"
            wire:click="removeSkill({{ $index }})">
            <i class="bi bi-x"></i>
        </button>
    </td>
</tr>
