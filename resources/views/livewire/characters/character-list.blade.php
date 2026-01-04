<div class="container py-5" data-testid="character-list-page">
    {{-- Roster Header Banner (matches dashboard) --}}
    <div class="card shadow-sm mb-4 rounded-4 border-0 header-banner-theme" data-testid="character-list-header">
        <div class="card-body text-center py-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-3">
                <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}" alt="App Logo"
                    class="logo"
                    style="height: 64px; width: 64px; border-radius: 16px; box-shadow: 0 2px 8px rgb(0 0 0 / 20%);">
                <div>
                    <h1 class="display-4 fw-bold mb-1">Umamusume Roster</h1>
                    <p class="lead mb-0">Meet the aspiring racehorses of Tracen Academy</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Controls --}}
    <div class="row mb-4 g-3" data-testid="character-filters">
        <div class="col-md-4">
            <label for="searchInput" class="form-label visually-hidden">Search characters</label>
            <div class="input-group">
                <span class="input-group-text" aria-hidden="true"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="searchInput" wire:model.live.debounce.300ms="search"
                    placeholder="Search by name, team, or tags..." data-testid="character-search-input"
                    aria-label="Search characters by name, team, or tags">
            </div>
        </div>
        <div class="col-md-2">
            <label for="teamFilter" class="form-label visually-hidden">Filter by team</label>
            <select class="form-select" id="teamFilter" wire:model.live="teamFilter" data-testid="character-team-filter"
                aria-label="Filter by team">
                <option value="">All Teams</option>
                @foreach ($teams as $team)
                    <option value="{{ $team }}">{{ $team }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="rarityFilter" class="form-label visually-hidden">Filter by rarity</label>
            <select class="form-select" id="rarityFilter" wire:model.live="rarityFilter"
                data-testid="character-rarity-filter" aria-label="Filter by rarity">
                <option value="">All Rarities</option>
                <option value="1">⭐ 1-Star</option>
                <option value="2">⭐⭐ 2-Star</option>
                <option value="3">⭐⭐⭐ 3-Star</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="distanceFilter" class="form-label visually-hidden">Filter by specialty distance</label>
            <select class="form-select" id="distanceFilter" wire:model.live="distanceFilter"
                data-testid="character-distance-filter" aria-label="Filter by specialty distance">
                <option value="">All Distances</option>
                <option value="sprint">Sprint (1000-1400m)</option>
                <option value="mile">Mile (1401-1800m)</option>
                <option value="medium">Medium (1801-2400m)</option>
                <option value="long">Long (2401-3600m)</option>
            </select>
        </div>
        <div class="col-md-2">
            @if ($search || $teamFilter || $rarityFilter || $distanceFilter)
                <button type="button" class="btn btn-outline-secondary w-100" wire:click="clearFilters"
                    data-testid="character-clear-filters">
                    <i class="bi bi-x-circle me-1"></i> Clear Filters
                </button>
            @endif
        </div>
    </div>

    {{-- Results Count --}}
    <div class="mb-3 text-muted" data-testid="character-count">
        Showing {{ $characters->count() }} character{{ $characters->count() !== 1 ? 's' : '' }}
    </div>

    {{-- Character Grid --}}
    <div class="row" id="characterGrid" data-testid="character-grid" role="list" aria-label="Character roster">
        @forelse($characters as $character)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 character-card" wire:key="character-{{ $character->id }}"
                data-testid="character-card-{{ $character->id }}" data-name="{{ strtolower($character->name) }}"
                data-team="{{ strtolower($character->team ?? '') }}"
                data-tags="{{ strtolower(implode(' ', (array) ($character->tags ?? []))) }}" role="listitem">
                <div class="card h-100 shadow-sm character-card-inner"
                    style="border-left: 4px solid {{ $character->ui['frame_color'] ?? '#6c757d' }}">
                    {{-- Character Image --}}
                    <div class="card-img-top position-relative overflow-hidden"
                        style="height: 200px; background: linear-gradient(135deg, {{ $character->ui['frame_color'] ?? '#6c757d' }}22, transparent);">
                        <img src="{{ asset($character->images['avatar'] ?? 'uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}"
                            alt="{{ $character->name }}"
                            class="img-fluid position-absolute top-50 start-50 translate-middle"
                            style="max-height: 180px; max-width: 90%; object-fit: contain;" loading="lazy"
                            onerror="this.src='{{ asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}'">

                        {{-- Rarity Stars --}}
                        <div class="position-absolute top-0 end-0 p-2"
                            aria-label="{{ $character->rarity ?? 1 }} star rarity">
                            @for ($i = 0; $i < ($character->rarity ?? 1); $i++)
                                <span class="text-warning" aria-hidden="true">⭐</span>
                            @endfor
                        </div>

                        {{-- Team Badge --}}
                        @if ($character->team)
                            <div class="position-absolute bottom-0 start-0 p-2">
                                <span class="badge"
                                    style="background-color: {{ $character->ui['frame_color'] ?? '#6c757d' }}">
                                    {{ $character->team }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Character Info --}}
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1" style="color: {{ $character->ui['frame_color'] ?? '#6c757d' }}">
                            {{ $character->name }}
                        </h5>
                        @if ($character->nickname)
                            <p class="card-text text-muted small mb-2">"{{ $character->nickname }}"</p>
                        @endif

                        <div class="mb-2">
                            <small class="text-muted">
                                <i class="bi bi-calendar-heart me-1"
                                    aria-hidden="true"></i>{{ $character->birthday }}
                                @if ($character->cv)
                                    | <i class="bi bi-mic me-1" aria-hidden="true"></i>{{ $character->cv }}
                                @endif
                            </small>
                        </div>

                        {{-- Tags --}}
                        @if ($character->tags && count((array) $character->tags) > 0)
                            <div class="mb-3">
                                @foreach (array_slice((array) $character->tags, 0, 3) as $tag)
                                    <span class="badge bg-secondary me-1 mb-1"
                                        style="font-size: 0.7em;">{{ ucfirst($tag) }}</span>
                                @endforeach
                                @if (count((array) $character->tags) > 3)
                                    <span class="badge bg-light text-dark me-1 mb-1" style="font-size: 0.7em;">
                                        +{{ count((array) $character->tags) - 3 }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        {{-- Stats Preview --}}
                        @if ($character->base_stats)
                            <div class="mb-3">
                                <small class="text-muted d-block mb-1">Base Stats</small>
                                <div class="row g-1 text-center">
                                    <div class="col">
                                        <div class="stat-mini" title="Speed">
                                            <small class="d-block text-primary" aria-hidden="true">⚡</small>
                                            <small class="fw-bold">{{ $character->base_stats['speed'] ?? 0 }}</small>
                                            <span class="visually-hidden">Speed:
                                                {{ $character->base_stats['speed'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="stat-mini" title="Stamina">
                                            <small class="d-block text-success" aria-hidden="true">🏃</small>
                                            <small
                                                class="fw-bold">{{ $character->base_stats['stamina'] ?? 0 }}</small>
                                            <span class="visually-hidden">Stamina:
                                                {{ $character->base_stats['stamina'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="stat-mini" title="Power">
                                            <small class="d-block text-danger" aria-hidden="true">💪</small>
                                            <small class="fw-bold">{{ $character->base_stats['power'] ?? 0 }}</small>
                                            <span class="visually-hidden">Power:
                                                {{ $character->base_stats['power'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="stat-mini" title="Guts">
                                            <small class="d-block text-warning" aria-hidden="true">❤️</small>
                                            <small class="fw-bold">{{ $character->base_stats['guts'] ?? 0 }}</small>
                                            <span class="visually-hidden">Guts:
                                                {{ $character->base_stats['guts'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="stat-mini" title="Wisdom">
                                            <small class="d-block text-info" aria-hidden="true">🧠</small>
                                            <small class="fw-bold">{{ $character->base_stats['wisdom'] ?? 0 }}</small>
                                            <span class="visually-hidden">Wisdom:
                                                {{ $character->base_stats['wisdom'] ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="mt-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1 view-details-btn"
                                wire:click="openCharacterModal('{{ $character->id }}')"
                                data-testid="character-view-details-{{ $character->id }}"
                                data-character-id="{{ $character->id }}"
                                aria-label="View details for {{ $character->name }}">
                                <i class="bi bi-eye me-1" aria-hidden="true"></i> Details
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm"
                                wire:click="selectForPlan('{{ $character->id }}')"
                                data-testid="character-select-{{ $character->id }}"
                                aria-label="Select {{ $character->name }} for plan creation"
                                title="Select for plan creation">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- No Results Message --}}
            <div class="col-12" data-testid="character-no-results" id="noResults">
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-search fs-1 mb-3 d-block" aria-hidden="true"></i>
                        <h4>No Umamusume Found</h4>
                        <p>Try adjusting your search or filter criteria.</p>
                        @if ($search || $teamFilter || $rarityFilter || $distanceFilter)
                            <button type="button" class="btn btn-primary" wire:click="clearFilters"
                                data-testid="character-clear-filters-empty">
                                Clear All Filters
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>


    {{-- Character Detail Modal --}}
    @if ($showDetailModal && $selectedCharacter)
        <div class="modal fade show d-block" id="characterModal" tabindex="-1" role="dialog"
            aria-labelledby="characterModalLabel" aria-modal="true" style="background-color: rgba(0,0,0,0.5);"
            wire:click.self="closeCharacterModal" data-testid="character-detail-modal">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header"
                        style="border-bottom-color: {{ $selectedCharacter['ui']['frame_color'] ?? '#6c757d' }};">
                        <h5 class="modal-title" id="characterModalLabel">
                            <span style="color: {{ $selectedCharacter['ui']['frame_color'] ?? '#6c757d' }}">
                                {{ $selectedCharacter['name'] }}
                            </span>
                            @if ($selectedCharacter['team'] ?? null)
                                <span class="badge ms-2"
                                    style="background-color: {{ $selectedCharacter['ui']['frame_color'] ?? '#6c757d' }}">
                                    {{ $selectedCharacter['team'] }}
                                </span>
                            @endif
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCharacterModal" aria-label="Close"
                            data-testid="character-modal-close"></button>
                    </div>
                    <div class="modal-body" id="characterModalBody">
                        <div class="row">
                            {{-- Character Image Column --}}
                            <div class="col-md-4 text-center mb-4">
                                <img src="{{ asset($selectedCharacter['images']['avatar'] ?? 'uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}"
                                    alt="{{ $selectedCharacter['name'] }}" class="img-fluid rounded"
                                    style="max-height: 300px;"
                                    onerror="this.src='{{ asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}'">

                                <div class="mt-3">
                                    @if ($selectedCharacter['nickname'] ?? null)
                                        <p class="text-muted fst-italic">"{{ $selectedCharacter['nickname'] }}"</p>
                                    @endif
                                    <div class="mb-2">
                                        @for ($i = 0; $i < ($selectedCharacter['rarity'] ?? 1); $i++)
                                            <span class="text-warning fs-5">⭐</span>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            {{-- Character Details Column --}}
                            <div class="col-md-8">
                                {{-- Basic Info --}}
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2"><i class="bi bi-person-badge me-2"></i>Basic
                                        Information</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Birthday:</strong>
                                                {{ $selectedCharacter['birthday'] ?? 'Unknown' }}</p>
                                            <p class="mb-1"><strong>Height:</strong>
                                                {{ $selectedCharacter['height_cm'] ? $selectedCharacter['height_cm'] . ' cm' : 'Unknown' }}
                                            </p>
                                            <p class="mb-1"><strong>Voice Actor:</strong>
                                                {{ $selectedCharacter['cv'] ?? 'Unknown' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Release:</strong>
                                                {{ $selectedCharacter['release_batch'] ?? 'Unknown' }}</p>
                                            <p class="mb-1"><strong>Weight:</strong>
                                                {{ $selectedCharacter['weight'] ?? 'Unknown' }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Base Stats --}}
                                @if ($selectedCharacter['base_stats'] ?? null)
                                    <div class="mb-4">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-bar-chart me-2"></i>Base Stats
                                        </h6>
                                        <div class="row text-center">
                                            @php
                                                $stats = [
                                                    'speed' => [
                                                        'icon' => '⚡',
                                                        'color' => 'primary',
                                                        'label' => 'Speed',
                                                    ],
                                                    'stamina' => [
                                                        'icon' => '🏃',
                                                        'color' => 'success',
                                                        'label' => 'Stamina',
                                                    ],
                                                    'power' => [
                                                        'icon' => '💪',
                                                        'color' => 'danger',
                                                        'label' => 'Power',
                                                    ],
                                                    'guts' => ['icon' => '❤️', 'color' => 'warning', 'label' => 'Guts'],
                                                    'wisdom' => [
                                                        'icon' => '🧠',
                                                        'color' => 'info',
                                                        'label' => 'Wisdom',
                                                    ],
                                                ];
                                            @endphp
                                            @foreach ($stats as $key => $stat)
                                                <div class="col">
                                                    <div class="stat-display p-2 rounded bg-light">
                                                        <div class="stat-icon text-{{ $stat['color'] }} fs-4">
                                                            {{ $stat['icon'] }}</div>
                                                        <div class="stat-value fs-5 fw-bold">
                                                            {{ $selectedCharacter['base_stats'][$key] ?? 0 }}</div>
                                                        <div class="stat-label small text-muted">{{ $stat['label'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Growth Rates --}}
                                @if ($selectedCharacter['growth_rates'] ?? null)
                                    <div class="mb-4">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-graph-up-arrow me-2"></i>Growth
                                            Rates</h6>
                                        <div class="row">
                                            @foreach ($selectedCharacter['growth_rates'] as $stat => $rate)
                                                <div class="col-auto mb-2">
                                                    <span
                                                        class="badge bg-{{ $rate > 0 ? 'success' : ($rate < 0 ? 'danger' : 'secondary') }}">
                                                        {{ ucfirst($stat) }}:
                                                        {{ $rate > 0 ? '+' : '' }}{{ $rate }}%
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Aptitudes --}}
                                @if ($selectedCharacter['aptitudes'] ?? null)
                                    <div class="mb-4">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-trophy me-2"></i>Aptitudes</h6>
                                        <div class="row">
                                            @if ($selectedCharacter['aptitudes']['terrain'] ?? null)
                                                <div class="col-md-4 mb-3">
                                                    <strong class="d-block mb-2">Terrain</strong>
                                                    <div class="d-flex flex-column gap-1">
                                                        <span>🌱 Turf: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['terrain']['turf'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['terrain']['turf'] ?? 'G' }}</span></span>
                                                        <span>🏜️ Dirt: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['terrain']['dirt'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['terrain']['dirt'] ?? 'G' }}</span></span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($selectedCharacter['aptitudes']['distance'] ?? null)
                                                <div class="col-md-4 mb-3">
                                                    <strong class="d-block mb-2">Distance</strong>
                                                    <div class="d-flex flex-column gap-1">
                                                        <span>🏃‍♂️ Sprint: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['distance']['sprint'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['distance']['sprint'] ?? 'G' }}</span></span>
                                                        <span>🏃 Mile: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['distance']['mile'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['distance']['mile'] ?? 'G' }}</span></span>
                                                        <span>🏃‍♀️ Medium: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['distance']['medium'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['distance']['medium'] ?? 'G' }}</span></span>
                                                        <span>🏃‍♂️ Long: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['distance']['long'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['distance']['long'] ?? 'G' }}</span></span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($selectedCharacter['aptitudes']['style'] ?? null)
                                                <div class="col-md-4 mb-3">
                                                    <strong class="d-block mb-2">Running Style</strong>
                                                    <div class="d-flex flex-column gap-1">
                                                        <span>🏁 Nige: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['style']['nige'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['style']['nige'] ?? 'G' }}</span></span>
                                                        <span>🏁 Senkou: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['style']['senkou'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['style']['senkou'] ?? 'G' }}</span></span>
                                                        <span>🏁 Sashi: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['style']['sashi'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['style']['sashi'] ?? 'G' }}</span></span>
                                                        <span>🏁 Oikomi: <span
                                                                class="badge aptitude-grade grade-{{ $selectedCharacter['aptitudes']['style']['oikomi'] ?? 'G' }}">{{ $selectedCharacter['aptitudes']['style']['oikomi'] ?? 'G' }}</span></span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Unique Skill --}}
                                @if ($selectedCharacter['unique_skill'] ?? null)
                                    <div class="mb-4">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-star me-2"></i>Unique Skill
                                        </h6>
                                        <div class="skill-card p-3 bg-light rounded">
                                            <strong>{{ $selectedCharacter['unique_skill']['name'] ?? 'Unknown' }}</strong>
                                            @if ($selectedCharacter['unique_skill']['effect'] ?? null)
                                                <p class="text-muted mb-0 mt-1">
                                                    {{ $selectedCharacter['unique_skill']['effect'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Tags --}}
                                @if (($selectedCharacter['tags'] ?? null) && count($selectedCharacter['tags']) > 0)
                                    <div class="mb-4">
                                        <h6 class="border-bottom pb-2"><i class="bi bi-tags me-2"></i>Tags</h6>
                                        <div>
                                            @foreach ($selectedCharacter['tags'] as $tag)
                                                <span class="badge bg-secondary me-1 mb-1">{{ ucfirst($tag) }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- External Links --}}
                        @if ($selectedCharacter['links'] ?? null)
                            <div class="mt-4 pt-3 border-top text-center">
                                <h6><i class="bi bi-link-45deg me-2"></i>External Resources</h6>
                                <div class="btn-group" role="group" aria-label="External links">
                                    @if ($selectedCharacter['links']['wiki'] ?? null)
                                        <a href="{{ $selectedCharacter['links']['wiki'] }}" target="_blank"
                                            rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>Wiki
                                        </a>
                                    @endif
                                    @if ($selectedCharacter['links']['game8'] ?? null)
                                        <a href="{{ $selectedCharacter['links']['game8'] }}" target="_blank"
                                            rel="noopener noreferrer" class="btn btn-outline-info btn-sm">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>Game8
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success"
                            wire:click="selectForPlan('{{ $selectedCharacter['id'] }}')"
                            data-testid="character-modal-select">
                            <i class="bi bi-plus-circle me-1"></i> Select for Plan
                        </button>
                        <button type="button" class="btn btn-secondary" wire:click="closeCharacterModal"
                            data-testid="character-modal-close-btn">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
    @vite(['resources/css/characters.css'])
@endpush
