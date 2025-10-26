{{--
    Training Year Blade Component
    Displays per-year training, turns, and events for Umamusume plans.
    Props:
        - year: int (training year)
        - turns: array (turn data)
        - events: array (event data)
--}}
@props([
    'year',
    'turns' => [],
    'events' => []
])

<div class="card mb-3" role="region" aria-labelledby="training-year-{{ $year }}-label">
    <div class="card-header d-flex align-items-center">
        <span id="training-year-{{ $year }}-label" class="fw-bold me-2">📅 Year {{ $year }}</span>
        <span class="badge bg-secondary">{{ count($turns) }} Turns</span>
    </div>
    <div class="card-body">
        <ul class="list-group mb-2">
            @foreach($turns as $turn)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>Turn {{ $turn['number'] }}: {{ $turn['desc'] }}</span>
                    @if(isset($turn['motivation']))
                        <span class="badge" style="background: var(--color-motivation-{{ strtolower($turn['motivation']) }}); color: #fff;">
                            {{ $turn['motivation'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
        <div>
            <span class="fw-bold">Events:</span>
            <ul class="list-group">
                @forelse($events as $event)
                    <li class="list-group-item">{{ $event }}</li>
                @empty
                    <li class="list-group-item text-muted">No events recorded.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
