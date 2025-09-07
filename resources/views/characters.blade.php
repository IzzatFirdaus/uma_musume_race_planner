@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 mb-3">🏇 Umamusume Roster</h1>
            <p class="lead">Meet the aspiring racehorses of Tracen Academy</p>
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

@push('styles')
<style>
.character-card-inner {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.character-card-inner:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.stat-mini {
    padding: 2px;
    border-radius: 4px;
    background: rgba(0,0,0,0.05);
}

.character-card.filtered {
    display: none;
}

@media (max-width: 576px) {
    .card-img-top {
        height: 150px !important;
    }

    .display-4 {
        font-size: 2rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const APP_BASE = @json(url('/'));
    const searchInput = document.getElementById('searchInput');
    const teamFilter = document.getElementById('teamFilter');
    const characterCards = document.querySelectorAll('.character-card');
    const noResults = document.getElementById('noResults');

    function filterCharacters() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedTeam = teamFilter.value.toLowerCase();
        let visibleCount = 0;

        characterCards.forEach(card => {
            const name = card.dataset.name;
            const team = card.dataset.team;
            const tags = card.dataset.tags;

            const matchesSearch = !searchTerm ||
                                name.includes(searchTerm) ||
                                team.includes(searchTerm) ||
                                tags.includes(searchTerm);

            const matchesTeam = !selectedTeam || team === selectedTeam ||
                              (selectedTeam === '' && team === '');

            if (matchesSearch && matchesTeam) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', filterCharacters);
    teamFilter.addEventListener('change', filterCharacters);

    // Character detail modal handler
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const characterId = this.dataset.characterId;
            const modalBody = document.getElementById('characterModalBody');

            // Show loading state
            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            try {
                const response = await fetch(`/api/v1/umamusume/${characterId}`);
                if (!response.ok) throw new Error('Failed to fetch character data');

                const character = await response.json();
                modalBody.innerHTML = generateCharacterDetailHTML(character);
            } catch (error) {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load character details. Please try again.
                    </div>
                `;
            }
        });
    });

    function generateCharacterDetailHTML(character) {
    const base = (APP_BASE || '').replace(/\/$/, '');
    const avatarPath = character.images?.avatar ? `${base}/${String(character.images.avatar).replace(/^\/+/, '')}` : `${base}/uploads/app_logo/uma_musume_race_planner_logo_64.ico`;
        return `
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <img src="${avatarPath}"
                         alt="${character.name}"
                         class="img-fluid rounded"
                         style="max-height: 300px;">

                    <div class="mt-3">
                        <h4 style="color: ${character.ui?.frame_color || '#6c757d'}">${character.name}</h4>
                        ${character.nickname ? `<p class="text-muted">"${character.nickname}"</p>` : ''}
                        ${character.team ? `<span class="badge" style="background-color: ${character.ui?.frame_color || '#6c757d'}">${character.team}</span>` : ''}
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Basic Info -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2">Basic Information</h6>
                        <div class="row">
                            <div class="col-6">
                                <strong>Birthday:</strong> ${character.birthday || 'Unknown'}<br>
                                <strong>Height:</strong> ${character.height_cm ? character.height_cm + ' cm' : 'Unknown'}<br>
                                <strong>Voice Actor:</strong> ${character.cv || 'Unknown'}
                            </div>
                            <div class="col-6">
                                <strong>Rarity:</strong> ${'⭐'.repeat(character.rarity || 1)}<br>
                                <strong>Release:</strong> ${character.release_batch || 'Unknown'}<br>
                                <strong>Weight:</strong> ${character.weight || 'Unknown'}
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    ${character.base_stats ? `
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Base Stats</h6>
                            <div class="row text-center">
                                <div class="col">
                                    <div class="stat-display">
                                        <div class="stat-icon text-primary">⚡</div>
                                        <div class="stat-value">${character.base_stats.speed || 0}</div>
                                        <div class="stat-label">Speed</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="stat-display">
                                        <div class="stat-icon text-success">🏃</div>
                                        <div class="stat-value">${character.base_stats.stamina || 0}</div>
                                        <div class="stat-label">Stamina</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="stat-display">
                                        <div class="stat-icon text-danger">💪</div>
                                        <div class="stat-value">${character.base_stats.power || 0}</div>
                                        <div class="stat-label">Power</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="stat-display">
                                        <div class="stat-icon text-warning">❤️</div>
                                        <div class="stat-value">${character.base_stats.guts || 0}</div>
                                        <div class="stat-label">Guts</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="stat-display">
                                        <div class="stat-icon text-info">🧠</div>
                                        <div class="stat-value">${character.base_stats.wisdom || 0}</div>
                                        <div class="stat-label">Wisdom</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ` : ''}

                    <!-- Unique Skill -->
                    ${character.unique_skill ? `
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Unique Skill</h6>
                            <div class="skill-card">
                                <strong>${character.unique_skill.name}</strong>
                                <p class="text-muted mb-0">${character.unique_skill.effect}</p>
                            </div>
                        </div>
                    ` : ''}

                    <!-- Aptitudes -->
                    ${character.aptitudes ? `
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Aptitudes</h6>
                            <div class="row">
                                ${character.aptitudes.terrain ? `
                                    <div class="col-md-6">
                                        <strong>Terrain:</strong><br>
                                        🌱 Turf: <span class="aptitude-grade grade-${character.aptitudes.terrain.turf || 'G'}">${character.aptitudes.terrain.turf || 'G'}</span><br>
                                        🏜️ Dirt: <span class="aptitude-grade grade-${character.aptitudes.terrain.dirt || 'G'}">${character.aptitudes.terrain.dirt || 'G'}</span>
                                    </div>
                                ` : ''}
                                ${character.aptitudes.distance ? `
                                    <div class="col-md-6">
                                        <strong>Distance:</strong><br>
                                        🏃‍♂️ Sprint: <span class="aptitude-grade grade-${character.aptitudes.distance.sprint || 'G'}">${character.aptitudes.distance.sprint || 'G'}</span><br>
                                        🏃 Mile: <span class="aptitude-grade grade-${character.aptitudes.distance.mile || 'G'}">${character.aptitudes.distance.mile || 'G'}</span><br>
                                        🏃‍♀️ Medium: <span class="aptitude-grade grade-${character.aptitudes.distance.medium || 'G'}">${character.aptitudes.distance.medium || 'G'}</span><br>
                                        🏃‍♂️ Long: <span class="aptitude-grade grade-${character.aptitudes.distance.long || 'G'}">${character.aptitudes.distance.long || 'G'}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    ` : ''}

                    <!-- Tags -->
                    ${character.tags && character.tags.length > 0 ? `
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Tags</h6>
                            <div>
                                ${character.tags.map(tag => `<span class="badge bg-secondary me-1 mb-1">${tag}</span>`).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>

            <!-- External Links -->
            ${character.links ? `
                <div class="mt-4 pt-3 border-top text-center">
                    <h6>External Resources</h6>
                    <div class="btn-group" role="group">
                        ${character.links.wiki ? `<a href="${character.links.wiki}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-external-link-alt me-1"></i>Wiki</a>` : ''}
                        ${character.links.game8 ? `<a href="${character.links.game8}" target="_blank" class="btn btn-outline-info btn-sm"><i class="fas fa-external-link-alt me-1"></i>Game8</a>` : ''}
                    </div>
                </div>
            ` : ''}
        `;
    }
});
</script>

<style>
.stat-display {
    padding: 10px;
    border-radius: 8px;
    background: rgba(0,0,0,0.05);
    margin-bottom: 10px;
}

.stat-icon {
    font-size: 1.5rem;
}

.stat-value {
    font-size: 1.25rem;
    font-weight: bold;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
}

.skill-card {
    padding: 10px;
    border-radius: 8px;
    background: rgba(0,0,0,0.05);
}

.aptitude-grade {
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 4px;
}

.grade-S { background-color: #d4edda; color: #155724; }
.grade-A { background-color: #cce5ff; color: #004085; }
.grade-B { background-color: #fff3cd; color: #856404; }
.grade-C { background-color: #f8d7da; color: #721c24; }
.grade-D, .grade-E, .grade-F, .grade-G { background-color: #f1f3f4; color: #495057; }
</style>
@endpush
