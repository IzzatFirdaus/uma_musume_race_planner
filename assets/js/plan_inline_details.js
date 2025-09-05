/* eslint-env browser */
/* assets/js/plan_inline_details.js
 * Enhances the inline plan details viewer:
 * - Listens for 'showPlanInline' to fetch and render all sections.
 * - Renders: General, Attributes, Aptitude Grades, Skills, Predictions, Goals.
 * - Renders the Progress Chart tab when shown.
 * - Adds Export as TXT and "Copy to Clipboard" handlers.
 * - Robust error handling, accessible messages, and reduced layout thrash (rAF).
 */

(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const API_BASE = (window.APP_CONFIG && window.APP_CONFIG.API_BASE) || window.APP_API_BASE || '/api';

        // DOM references
        const wrapper = document.getElementById('planInlineDetails');
        const loadingOverlay = document.getElementById('planInlineDetailsLoadingOverlay');
        const closeBtn = document.getElementById('closeInlineDetailsBtn');

        // General tab inputs
        const planIdInput = document.getElementById('planIdInline');
        const planTitleInput = document.getElementById('plan_title_inline');
        const turnBeforeInput = document.getElementById('modalTurnBefore_inline');
        const nameInput = document.getElementById('modalName_inline');
        const raceNameInput = document.getElementById('modalRaceName_inline');
        const careerStageSelect = document.getElementById('modalCareerStage_inline');
        const classSelect = document.getElementById('modalClass_inline');
        const statusSelect = document.getElementById('modalStatus_inline');
        const strategySelect = document.getElementById('modalStrategy_inline');
        const moodSelect = document.getElementById('modalMood_inline');
        const conditionSelect = document.getElementById('modalCondition_inline');
        const goalInput = document.getElementById('modalGoal_inline');
        const sourceInput = document.getElementById('modalSource_inline');
        const monthInput = document.getElementById('modalMonth_inline');
        const timeOfDayInput = document.getElementById('modalTimeOfDay_inline');
        const spInput = document.getElementById('skillPoints_inline');
        const acquireSkillSwitch = document.getElementById('acquireSkillSwitch_inline');
        const raceDaySwitch = document.getElementById('raceDaySwitch_inline');
        const energyRange = document.getElementById('energyRange_inline');
        const energyValue = document.getElementById('energyValue_inline');

        // Section containers
        const attrsContainer = document.getElementById('attributeSlidersContainerInline');
        const gradesContainer = document.getElementById('aptitudeGradesContainerInline');
        const skillsTableBody = document.querySelector('#skillsTableInline tbody');
        const predictionsTableBody = document.querySelector('#predictionsTableInline tbody');
        const goalsTableBody = document.querySelector('#goalsTableInline tbody');

        // Buttons
        const btnExportTxt = document.getElementById('downloadTxtInline');
        const btnCopyToClipboard = document.getElementById('exportPlanBtnInline');

        // Chart
        const chartTabInline = document.getElementById('progress-chart-tab-inline');
        let growthChartInstanceInline = null;

        // Last fetched dataset for clipboard export
        let lastFetchedData = null;

        // Helpers
        const css = (v, fb = '') => getComputedStyle(document.documentElement).getPropertyValue(v).trim() || fb;
        const escapeHTML = (str) =>
            String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

        async function fetchJson(url) {
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!resp.ok) {
                throw new Error(`HTTP ${resp.status}`);
            }
            const data = await resp.json();
            if (!data || data.success !== true) {
                throw new Error(data?.error || 'Unexpected API error.');
            }
            return data;
        }

        async function fetchAllSections(planId) {
            // Fetch plan core + all sections in parallel
            const endpoints = {
                plan: `${API_BASE}/plan.php?action=get&id=${encodeURIComponent(planId)}`,
                attributes: `${API_BASE}/plan_section.php?type=attributes&id=${encodeURIComponent(planId)}`,
                skills: `${API_BASE}/plan_section.php?type=skills&id=${encodeURIComponent(planId)}`,
                terrain_grades: `${API_BASE}/plan_section.php?type=terrain_grades&id=${encodeURIComponent(planId)}`,
                distance_grades: `${API_BASE}/plan_section.php?type=distance_grades&id=${encodeURIComponent(planId)}`,
                style_grades: `${API_BASE}/plan_section.php?type=style_grades&id=${encodeURIComponent(planId)}`,
                goals: `${API_BASE}/plan_section.php?type=goals&id=${encodeURIComponent(planId)}`,
                predictions: `${API_BASE}/plan_section.php?type=predictions&id=${encodeURIComponent(planId)}`
            };

            const [
                planRes,
                attrsRes,
                skillsRes,
                terrRes,
                distRes,
                styleRes,
                goalsRes,
                predsRes
            ] = await Promise.all([
                fetchJson(endpoints.plan),
                fetchJson(endpoints.attributes),
                fetchJson(endpoints.skills),
                fetchJson(endpoints.terrain_grades),
                fetchJson(endpoints.distance_grades),
                fetchJson(endpoints.style_grades),
                fetchJson(endpoints.goals),
                fetchJson(endpoints.predictions)
            ]);

            return {
                plan: planRes.plan || {},
                attributes: Array.isArray(attrsRes.attributes) ? attrsRes.attributes : [],
                skills: Array.isArray(skillsRes.skills) ? skillsRes.skills : [],
                terrain_grades: Array.isArray(terrRes.terrain_grades) ? terrRes.terrain_grades : [],
                distance_grades: Array.isArray(distRes.distance_grades) ? distRes.distance_grades : [],
                style_grades: Array.isArray(styleRes.style_grades) ? styleRes.style_grades : [],
                goals: Array.isArray(goalsRes.goals) ? goalsRes.goals : [],
                predictions: Array.isArray(predsRes.predictions) ? predsRes.predictions : []
            };
        }

        function showWrapper(show) {
            if (!wrapper) return;
            wrapper.style.display = show ? 'block' : 'none';
        }

        function setLoading(isLoading) {
            if (!loadingOverlay) return;
            loadingOverlay.style.display = isLoading ? 'flex' : 'none';
            if (isLoading) {
                loadingOverlay.setAttribute('aria-busy', 'true');
            } else {
                loadingOverlay.removeAttribute('aria-busy');
            }
        }

        function populateGeneralInfo(plan) {
            if (planIdInput) planIdInput.value = plan.id || '';
            if (planTitleInput) planTitleInput.value = plan.plan_title || '';
            if (turnBeforeInput) turnBeforeInput.value = Number.isFinite(+plan.turn_before) ? +plan.turn_before : 0;
            if (nameInput) nameInput.value = plan.name || '';
            if (raceNameInput) raceNameInput.value = plan.race_name || '';
            if (careerStageSelect && plan.career_stage) careerStageSelect.value = plan.career_stage;
            if (classSelect && plan.class) classSelect.value = plan.class;
            if (statusSelect && plan.status) statusSelect.value = plan.status;
            if (strategySelect && plan.strategy_id) strategySelect.value = String(plan.strategy_id);
            if (moodSelect && plan.mood_id) moodSelect.value = String(plan.mood_id);
            if (conditionSelect && plan.condition_id) conditionSelect.value = String(plan.condition_id);
            if (goalInput) goalInput.value = plan.goal || '';
            if (sourceInput) sourceInput.value = plan.source || '';
            if (monthInput) monthInput.value = plan.month || '';
            if (timeOfDayInput) timeOfDayInput.value = plan.time_of_day || '';
            if (spInput) spInput.value = Number.isFinite(+plan.total_available_skill_points) ? +plan.total_available_skill_points : 0;
            if (acquireSkillSwitch) acquireSkillSwitch.checked = String(plan.acquire_skill || '').toUpperCase() === 'YES';
            if (raceDaySwitch) raceDaySwitch.checked = String(plan.race_day || 'no').toLowerCase() === 'yes';
            if (energyRange) {
                const en = Number.isFinite(+plan.energy) ? +plan.energy : 0;
                energyRange.value = String(en);
                if (energyValue) energyValue.textContent = String(en);
            }
        }

        function renderAttributes(attributes) {
                        if (!attrsContainer) return;
                        attrsContainer.innerHTML = '';
                        if (!attributes.length) {
                                attrsContainer.innerHTML = `
                                    <div class="d-flex flex-column align-items-center justify-content-center p-4">
                                        <i class="bi bi-bar-chart-line" style="font-size:2.5rem;color:var(--color-muted);" aria-hidden="true"></i>
                                        <div class="mt-2 mb-1 fs-5 text-muted">No attributes available</div>
                                        <div class="mb-2 text-muted">This plan has no attribute data yet.</div>
                                    </div>
                                `;
                                return;
                        }
            // Render as compact list for view-only
            const list = document.createElement('div');
            list.className = 'row g-2';
            attributes.forEach((a) => {
                const col = document.createElement('div');
                col.className = 'col-md-6';
                col.innerHTML = `
                    <div class="p-2 border rounded">
                        <div class="d-flex justify-content-between">
                            <strong>${escapeHTML(a.attribute_name || '')}</strong>
                            <span class="badge bg-secondary">${escapeHTML(a.grade || 'G')}</span>
                        </div>
                        <div class="mt-1 text-muted">Value: ${escapeHTML(a.value ?? 0)}</div>
                    </div>
                `;
                list.appendChild(col);
            });
            attrsContainer.appendChild(list);
        }

        function renderGrades(terrainGrades, distanceGrades, styleGrades) {
            if (!gradesContainer) return;
            gradesContainer.innerHTML = '';
            const toMap = (arr, key) => {
                const m = new Map();
                (arr || []).forEach((it) => m.set(String(it[key] || ''), String(it.grade || 'G')));
                return m;
            };
            const terr = toMap(terrainGrades, 'terrain');
            const dist = toMap(distanceGrades, 'distance');
            const style = toMap(styleGrades, 'style');

            const sections = [
                { title: 'Terrain', keys: ['Turf', 'Dirt'], map: terr },
                { title: 'Distance', keys: ['Sprint', 'Mile', 'Medium', 'Long'], map: dist },
                { title: 'Style', keys: ['Front', 'Pace', 'Late', 'End'], map: style }
            ];

            sections.forEach((section) => {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-3';
                let html = `<div class="border rounded p-2 h-100">
                    <h6 class="mb-2">${escapeHTML(section.title)}</h6>
                    <div class="d-flex flex-column gap-1">`;
                section.keys.forEach((k) => {
                    const g = section.map.get(k) || 'G';
                    html += `
                        <div class="d-flex justify-content-between">
                            <span>${escapeHTML(k)}</span>
                            <span class="badge rounded-pill bg-primary">${escapeHTML(g)}</span>
                        </div>
                    `;
                });
                html += '</div></div>';
                col.innerHTML = html;
                gradesContainer.appendChild(col);
            });
        }

        function renderSkills(skills) {
                        if (!skillsTableBody) return;
                        skillsTableBody.innerHTML = '';
                        if (!skills.length) {
                                skillsTableBody.innerHTML = `
                                    <tr>
                                        <td colspan="6" class="text-center p-4">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="bi bi-lightbulb" style="font-size:2rem;color:var(--color-muted);" aria-hidden="true"></i>
                                                <div class="mt-2 mb-1 fs-6 text-muted">No skills available</div>
                                                <div class="mb-2 text-muted">This plan has no skills assigned yet.</div>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                return;
                        }
            skills.forEach((s) => {
                const tr = document.createElement('tr');
                const acquired = (String(s.acquired || '').toLowerCase() === 'yes') ? '✅' : '❌';
                tr.innerHTML = `
                    <td>${escapeHTML(s.skill_name || '')}</td>
                    <td>${escapeHTML(s.sp_cost ?? 'N/A')}</td>
                    <td class="text-center">${acquired}</td>
                    <td>${escapeHTML(s.skill_tag || s.tag || '')}</td>
                    <td>${escapeHTML(s.notes || '')}</td>
                    <td><span class="text-muted">—</span></td>
                `;
                skillsTableBody.appendChild(tr);
            });
        }

        function renderPredictions(predictions) {
                        if (!predictionsTableBody) return;
                        predictionsTableBody.innerHTML = '';
                        if (!predictions.length) {
                                predictionsTableBody.innerHTML = `
                                    <tr>
                                        <td colspan="13" class="text-center p-4">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="bi bi-search" style="font-size:2rem;color:var(--color-muted);" aria-hidden="true"></i>
                                                <div class="mt-2 mb-1 fs-6 text-muted">No predictions available</div>
                                                <div class="mb-2 text-muted">No race predictions have been generated for this plan.</div>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                return;
                        }
            predictions.forEach((p) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${escapeHTML(p.race_name || '')}</td>
                    <td>${escapeHTML(p.venue || '')}</td>
                    <td>${escapeHTML(p.ground || '')}</td>
                    <td>${escapeHTML(p.distance || '')}</td>
                    <td>${escapeHTML(p.track_condition || '')}</td>
                    <td>${escapeHTML(p.direction || '')}</td>
                    <td>${escapeHTML(p.speed ?? '')}</td>
                    <td>${escapeHTML(p.stamina ?? '')}</td>
                    <td>${escapeHTML(p.power ?? '')}</td>
                    <td>${escapeHTML(p.guts ?? '')}</td>
                    <td>${escapeHTML(p.wit ?? '')}</td>
                    <td>${escapeHTML(p.comment || '')}</td>
                    <td><span class="text-muted">—</span></td>
                `;
                predictionsTableBody.appendChild(tr);
            });
        }

        function renderGoals(goals) {
                        if (!goalsTableBody) return;
                        goalsTableBody.innerHTML = '';
                        if (!goals.length) {
                                goalsTableBody.innerHTML = `
                                    <tr>
                                        <td colspan="3" class="text-center p-4">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="bi bi-flag" style="font-size:2rem;color:var(--color-muted);" aria-hidden="true"></i>
                                                <div class="mt-2 mb-1 fs-6 text-muted">No goals available</div>
                                                <div class="mb-2 text-muted">No career goals have been set for this plan.</div>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                return;
                        }
            goals.forEach((g) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${escapeHTML(g.goal || '')}</td>
                    <td>${escapeHTML(g.result || '')}</td>
                    <td><span class="text-muted">—</span></td>
                `;
                goalsTableBody.appendChild(tr);
            });
        }

        // Existing inline chart logic, now adapted to be callable
        function getCssVariableValue(variableName) {
            return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
        }

        const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;

        async function renderGrowthChartInline(planId) {
            if (!planId) return;

            const chartCanvas = document.getElementById('growthChartInline');
            const messageContainer = document.getElementById('growthChartMessageInline');

            if (!chartCanvas || !messageContainer) return;

            if (growthChartInstanceInline) {
                try { growthChartInstanceInline.destroy(); } catch (_) {}
                growthChartInstanceInline = null;
            }

            try {
                // Chart.js global defaults based on CSS
                if (window.Chart) {
                    window.Chart.defaults.font.family = getCssVariableValue('--bs-body-font-family') || window.Chart.defaults.font.family;
                    window.Chart.defaults.color = getCssVariableValue('--bs-secondary-color') || window.Chart.defaults.color;
                }

                const response = await fetch(`${API_BASE}/progress.php?action=chart&plan_id=${encodeURIComponent(String(planId))}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (result?.success && Array.isArray(result.data) && result.data.length > 0) {
                    chartCanvas.style.display = 'block';
                    messageContainer.style.display = 'none';

                    const turns = result.data;
                    const ctx = chartCanvas.getContext('2d');

                    requestAnimationFrame(() => {
                        // eslint-disable-next-line no-undef
                        growthChartInstanceInline = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: turns.map(t => `Turn ${t.turn}`),
                                datasets: [
                                    { label: 'Speed',   data: turns.map(t => t.speed),   borderColor: getCssVariableValue('--stat-speed-color'),   pointBackgroundColor: getCssVariableValue('--stat-speed-color'),   tension: 0.3 },
                                    { label: 'Stamina', data: turns.map(t => t.stamina), borderColor: getCssVariableValue('--stat-stamina-color'), pointBackgroundColor: getCssVariableValue('--stat-stamina-color'), tension: 0.3 },
                                    { label: 'Power',   data: turns.map(t => t.power),   borderColor: getCssVariableValue('--stat-power-color'),   pointBackgroundColor: getCssVariableValue('--stat-power-color'),   tension: 0.3 },
                                    { label: 'Guts',    data: turns.map(t => t.guts),    borderColor: getCssVariableValue('--stat-guts-color'),    pointBackgroundColor: getCssVariableValue('--stat-guts-color'),    tension: 0.3 },
                                    { label: 'Wit',     data: turns.map(t => t.wit),     borderColor: getCssVariableValue('--stat-wit-color'),     pointBackgroundColor: getCssVariableValue('--stat-wit-color'),     tension: 0.3 }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: prefersReducedMotion ? false : { duration: 500 },
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { labels: { usePointStyle: true, color: getCssVariableValue('--bs-body-color'), padding: 20 } },
                                    tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.85)', titleFont: { size: 14, weight: 'bold' }, bodyFont: { size: 12 }, padding: 12, cornerRadius: 4, displayColors: true }
                                },
                                scales: {
                                    y: { beginAtZero: true, grid: { color: getCssVariableValue('--bs-border-color-translucent') } },
                                    x: { grid: { color: getCssVariableValue('--bs-border-color-translucent') } }
                                }
                            }
                        });
                    });
                                } else {
                                        chartCanvas.style.display = 'none';
                                        messageContainer.style.display = 'block';
                                        messageContainer.innerHTML = `
                                            <div class="d-flex flex-column align-items-center justify-content-center p-4">
                                                <i class="bi bi-graph-up" style="font-size:2.5rem;color:var(--color-muted);" aria-hidden="true"></i>
                                                <div class="mt-2 mb-1 fs-5 text-muted">No progression data available</div>
                                                <div class="mb-2 text-muted">This plan has no training progression chart yet.</div>
                                            </div>
                                        `;
                                }
            } catch (error) {
                chartCanvas.style.display = 'none';
                messageContainer.style.display = 'block';
                messageContainer.innerHTML = '<p class="text-danger mb-0">Could not load chart data. Please check the console for details.</p>';
                console.error('Error loading inline chart:', error);
            }
        }

        // When the inline chart tab is shown, render the chart for the current plan
        if (chartTabInline) {
            chartTabInline.addEventListener('shown.bs.tab', function () {
                const currentPlanId = document.getElementById('planIdInline')?.value;
                renderGrowthChartInline(currentPlanId);
            });
        }

        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                showWrapper(false);
            });
        }

        // Export as TXT (already wired in markup code; kept here if needed)
        if (btnExportTxt) {
            btnExportTxt.addEventListener('click', function (e) {
                // Handled in markup via anchor construction in component.
                // No-op here to avoid double behaviors.
            });
        }

        // Copy to Clipboard handler (uses window.copyPlanDetailsToClipboard)
        if (btnCopyToClipboard) {
            btnCopyToClipboard.addEventListener('click', function () {
                import('sweetalert2').then(Swal => {
                    if (!lastFetchedData) {
                        // Attempt lazy fetch if user clicks immediately
                        const pid = planIdInput?.value;
                        if (!pid) return;
                        setLoading(true);
                        fetchAllSections(pid)
                            .then((data) => {
                                lastFetchedData = data;
                                if (typeof window.copyPlanDetailsToClipboard === 'function') {
                                    window.copyPlanDetailsToClipboard(lastFetchedData);
                                } else {
                                    Swal.default.fire({
                                        title: 'Copy module not loaded.',
                                        text: 'Please try again.',
                                        icon: 'error',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                            })
                            .catch((err) => {
                                console.error('Clipboard fetch error (inline):', err);
                                Swal.default.fire({
                                    title: 'Failed to prepare clipboard content.',
                                    icon: 'error',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            })
                            .finally(() => setLoading(false));
                        return;
                    }
                    if (typeof window.copyPlanDetailsToClipboard === 'function') {
                        window.copyPlanDetailsToClipboard(lastFetchedData);
                    } else {
                        Swal.default.fire({
                            title: 'Copy module not loaded.',
                            text: 'Please try again.',
                            icon: 'error',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            });
        }

        // Main event wiring: Open inline details when dispatched
        document.addEventListener('showPlanInline', async (evt) => {
            const planId = evt?.detail?.planId;
            if (!planId) return;

            showWrapper(true);
            setLoading(true);

            try {
                // Fetch everything
                const data = await fetchAllSections(planId);

                // Populate general
                populateGeneralInfo(data.plan);

                // Render sections
                renderAttributes(data.attributes);
                renderGrades(data.terrain_grades, data.distance_grades, data.style_grades);
                renderSkills(data.skills);
                renderPredictions(data.predictions);
                renderGoals(data.goals);

                // Store for clipboard export
                lastFetchedData = data;

                // Lazily render chart if user goes to Chart tab
                // Or render once by default (optional). Here we defer to tab event.
            } catch (error) {
                console.error('Failed to load inline plan details:', error);
                // Show a simple error state
                attrsContainer.innerHTML = '<p class="text-danger">Failed to load plan data.</p>';
                gradesContainer.innerHTML = '';
                skillsTableBody.innerHTML = '<tr><td colspan="6" class="text-danger text-center">Failed to load.</td></tr>';
                predictionsTableBody.innerHTML = '<tr><td colspan="13" class="text-danger text-center">Failed to load.</td></tr>';
                goalsTableBody.innerHTML = '<tr><td colspan="3" class="text-danger text-center">Failed to load.</td></tr>';
            } finally {
                setLoading(false);
            }
        });
    });
})();