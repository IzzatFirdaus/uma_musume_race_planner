@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <!-- Roster Header Banner (matches dashboard) -->
        <div class="card shadow-sm mb-4 rounded-4 border-0 header-banner-theme">
            <div class="card-body text-center py-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-3">
                    <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}" alt="App Logo" class="logo" style="height: 64px; width: 64px; border-radius: 16px; box-shadow: 0 2px 8px rgb(0 0 0 / 20%);">
                    <div>
                        <h1 class="display-4 fw-bold mb-1">Umamusume Roster</h1>
                        <p class="lead mb-0">Meet the aspiring racehorses of Tracen Academy</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by name, team, or tags...">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select" id="teamFilter">
                    <option value="">All Teams</option>
                    <option value="Spica">Team Spica</option>
                    <option value="Canopus">Team Canopus</option>
                    <option value="Rigil">Team Rigil</option>
                    <option value="Sirius">Team Sirius</option>
                    <option value="">No Team</option>
                </select>
            </div>
        </div>

        <!-- Character Grid -->
        <div class="row" id="characterGrid">
            @foreach($umamusume as $character)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 character-card"
                     data-name="{{ strtolower($character->name) }}"
                     data-team="{{ strtolower($character->team ?? '') }}"
                     data-tags="{{ strtolower(implode(' ', (array) ($character->tags ?? []))) }}">
                    <div class="card h-100 shadow-sm character-card-inner" style="border-left: 4px solid {{ $character->ui['frame_color'] ?? '#6c757d' }}">
                        <!-- Character Image -->
                        <div class="card-img-top position-relative overflow-hidden" style="height: 200px; background: linear-gradient(135deg, {{ $character->ui['frame_color'] ?? '#6c757d' }}22, transparent);">
                            <img src="{{ asset($character->images['avatar'] ?? 'uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}"
                                 alt="{{ $character->name }}"
                                 class="img-fluid position-absolute top-50 start-50 translate-middle"
                                 style="max-height: 180px; max-width: 90%; object-fit: contain;"
                                 onerror="this.src='{{ asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}'">

                            <!-- Rarity Stars -->
                            <div class="position-absolute top-0 end-0 p-2">
                                @for($i = 0; $i < ($character->rarity ?? 1); $i++)
                                    <span class="text-warning">⭐</span>
                                @endfor
                            </div>

                            <!-- Team Badge -->
                            @if($character->team)
                                <div class="position-absolute bottom-0 start-0 p-2">
                                    <span class="badge" style="background-color: {{ $character->ui['frame_color'] ?? '#6c757d' }}">{{ $character->team }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Character Info -->
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1" style="color: {{ $character->ui['frame_color'] ?? '#6c757d' }}">{{ $character->name }}</h5>
                            @if($character->nickname)
                                <p class="card-text text-muted small mb-2">"{{ $character->nickname }}"</p>
                            @endif

                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-birthday-cake me-1"></i>{{ $character->birthday }}
                                    @if($character->cv)
                                        | <i class="fas fa-microphone me-1"></i>{{ $character->cv }}
                                    @endif
                                </small>
                            </div>

                            <!-- Tags -->
                            @if($character->tags && count((array) $character->tags) > 0)
                                <div class="mb-3">
                                    @foreach(array_slice((array) $character->tags, 0, 3) as $tag)
                                        <span class="badge bg-secondary me-1 mb-1" style="font-size: 0.7em;">{{ ucfirst($tag) }}</span>
                                    @endforeach
                                    @if(count((array) $character->tags) > 3)
                                        <span class="badge bg-light text-dark me-1 mb-1" style="font-size: 0.7em;">+{{ count((array) $character->tags) - 3 }}</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Stats Preview -->
                            @if($character->base_stats)
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Base Stats</small>
                                    <div class="row g-1 text-center">
                                        <div class="col">
                                            <div class="stat-mini" title="Speed">
                                                <small class="d-block text-primary">⚡</small>
                                                <small class="fw-bold">{{ $character->base_stats['speed'] ?? 0 }}</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="stat-mini" title="Stamina">
                                                <small class="d-block text-success">🏃</small>
                                                <small class="fw-bold">{{ $character->base_stats['stamina'] ?? 0 }}</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="stat-mini" title="Power">
                                                <small class="d-block text-danger">💪</small>
                                                <small class="fw-bold">{{ $character->base_stats['power'] ?? 0 }}</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="stat-mini" title="Guts">
                                                <small class="d-block text-warning">❤️</small>
                                                <small class="fw-bold">{{ $character->base_stats['guts'] ?? 0 }}</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="stat-mini" title="Wisdom">
                                                <small class="d-block text-info">🧠</small>
                                                <small class="fw-bold">{{ $character->base_stats['wisdom'] ?? 0 }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- View Details Button -->
                            <div class="mt-auto">
                                <button class="btn btn-outline-primary btn-sm w-100 view-details-btn"
                                        data-character-id="{{ $character->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#characterModal">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="text-center py-5" style="display: none;">
            <div class="text-muted">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h4>No Umamusume Found</h4>
                <p>Try adjusting your search or filter criteria.</p>
            </div>
        </div>
    </div>

    <!-- Character Detail Modal -->
    <div class="modal fade" id="characterModal" tabindex="-1" aria-labelledby="characterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="characterModalLabel">Character Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="characterModalBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@vite(['resources/css/characters.css'])

<script>window.APP_BASE = @json(url('/'));</script>
@vite(['resources/js/characters.js'])
