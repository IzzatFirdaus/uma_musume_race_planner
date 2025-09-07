document.addEventListener('DOMContentLoaded', function() {
    const APP_BASE = window.APP_BASE || '';
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
                        ${character.nickname ? `<p class="text-muted">\"${character.nickname}\"</p>` : ''}
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
