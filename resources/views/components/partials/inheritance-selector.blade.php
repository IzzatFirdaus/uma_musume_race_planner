{{--
    Inheritance Selector Blade Component
    Displays parent selection and inherited stats/spark counts per Umamusume mechanics.
    Props:
        - parents: array (available parent options)
        - selected: array (selected parent data)
        - sparks: int (number of 3-star sparks)
--}}
@props([
    'parents' => [],
    'selected' => [],
    'sparks' => 0
])

<div class="row g-2 align-items-center">
    <div class="col-md-6">
        <label class="form-label" for="parent1-select">Parent 1</label>
        <select id="parent1-select" class="form-select" wire:model.lazy="selected.parent1" aria-label="Parent 1 select">
            <option value="">Select Parent</option>
            @foreach($parents as $parent)
                <option value="{{ $parent['id'] }}">{{ $parent['name'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="parent2-select">Parent 2</label>
        <select id="parent2-select" class="form-select" wire:model.lazy="selected.parent2" aria-label="Parent 2 select">
            <option value="">Select Parent</option>
            @foreach($parents as $parent)
                <option value="{{ $parent['id'] }}">{{ $parent['name'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 mt-2">
        <span class="fw-bold">Sparks:</span>
        <span class="badge bg-info">{{ $sparks }} × 3★</span>
        <span class="ms-2">(+{{ $sparks * 21 }} stat from legacy parents)</span>
    </div>
</div>
