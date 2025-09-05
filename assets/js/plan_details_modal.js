// assets/js/plan_details_modal.js
// Extracted from plan_details_modal.php inline script
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const planDetailsModalElement = document.getElementById('planDetailsModal');
        if (!planDetailsModalElement) {
            return;
        }

        const API_BASE = (window.APP_CONFIG && window.APP_CONFIG.API_BASE) || window.APP_API_BASE || '/api';

        const chartTab = document.getElementById('progress-chart-tab');
        let growthChartInstance = null;

        function getCssVariableValue(name) {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        }

        async function renderGrowthChart(planId) {
            if (!planId) {
                return;
            }

            const chartCanvas = document.getElementById('growthChart');
            const messageContainer = document.getElementById('growthChartMessage');
            if (!chartCanvas || !messageContainer) {
                return;
            }

            if (growthChartInstance) {
                try {
                    growthChartInstance.destroy();
                } catch (e) {
                    /* ignore */
                }
                growthChartInstance = null;
            }

            try {
                Chart.defaults.font.family = getCssVariableValue('--bs-body-font-family');
                Chart.defaults.color = getCssVariableValue('--bs-secondary-color');

                const base = API_BASE;
                const resp = await fetch(`${base}/progress.php?action=chart&plan_id=${encodeURIComponent(planId)}`);
                const result = await resp.json();

                if (result.success && Array.isArray(result.data) && result.data.length > 0) {
                    chartCanvas.style.display = 'block';
                    messageContainer.style.display = 'none';

                    const turns = result.data;
                    const ctx = chartCanvas.getContext('2d');

                    // Defer chart construction to the next animation frame to avoid forcing layout during synchronous JS
                    window.requestAnimationFrame(() => {
                        // eslint-disable-next-line no-undef
                        growthChartInstance = new Chart(ctx, {
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
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: {
                                        labels: {
                                            usePointStyle: true,
                                            color: getCssVariableValue('--bs-body-color'),
                                            padding: 20
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                                        titleFont: { size: 14, weight: 'bold' },
                                        bodyFont: { size: 12 },
                                        padding: 12,
                                        cornerRadius: 4,
                                        displayColors: true
                                    }
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
                messageContainer.innerHTML = '<p class="text-danger">Could not load chart data. Please check the console for details.</p>';
                // Log to console for debugging in browser
                console.error('Error loading chart:', error);
            }
        }

        if (chartTab) {
            chartTab.addEventListener('shown.bs.tab', function () {
                const currentPlanId = document.getElementById('planId').value;
                // Defer heavy layout/Chart reads slightly to allow CSS to be applied
                requestAnimationFrame(() => renderGrowthChart(currentPlanId));
            });
        }

        const downloadTxtLink = document.getElementById('downloadTxtLink');
        if (downloadTxtLink) {
            downloadTxtLink.addEventListener('click', function (e) {
                e.preventDefault();
                const planId = document.getElementById('planId').value;
                if (!planId) {
                    return;
                }

                const planTitle = document.getElementById('plan_title').value || 'plan';
                const safeFileName = planTitle.replace(/[^a-z0-9_-]/gi, '_').toLowerCase();
                const fileName = `${safeFileName}_${planId}.txt`;

                const link = document.createElement('a');
                link.href = `export_plan_data.php?id=${encodeURIComponent(planId)}&format=txt`;
                link.download = fileName;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
        // Global cleanup: sometimes a leftover .modal-backdrop remains (e.g. when modals
        // are shown/hidden using fallback code paths). Ensure backdrops are removed
        // and the body state is restored when any modal finishes hiding.
        document.addEventListener('hidden.bs.modal', function () {
            try {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(b => b.parentNode && b.parentNode.removeChild(b));
                // Remove modal-open in case it wasn't cleared
                document.body.classList.remove('modal-open');
                // Clear inline padding-right added by Bootstrap when scrollbar hidden
                if (document.body.style && document.body.style.paddingRight) {
                    document.body.style.paddingRight = '';
                }
            } catch (e) {
                // Swallow errors - best-effort cleanup only
                console.warn('Error cleaning up modal backdrop:', e);
            }
        });

        // Fallback: if a modal close button is clicked but the Bootstrap event isn't emitted,
        // run the same cleanup shortly after click to remove stray backdrops.
        document.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('[data-bs-dismiss="modal"], .btn-close')) {
                setTimeout(() => {
                    try {
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(b => b.parentNode && b.parentNode.removeChild(b));
                        document.body.classList.remove('modal-open');
                        if (document.body.style && document.body.style.paddingRight) document.body.style.paddingRight = '';
                    } catch (err) {
                        // ignore
                    }
                }, 60);
            }
        });

        // ---- Copy to Clipboard support for the modal ----
        const btnCopyToClipboard = document.getElementById('exportPlanBtn');

        async function fetchJson(url) {
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const data = await resp.json();
            if (!data || data.success !== true) throw new Error(data?.error || 'Unexpected API error.');
            return data;
        }

        async function fetchAllForClipboard(planId) {
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

        if (btnCopyToClipboard) {
            btnCopyToClipboard.addEventListener('click', async () => {
                import('sweetalert2').then(Swal => {
                    const planId = document.getElementById('planId')?.value;
                    if (!planId) return;
                    fetchAllForClipboard(planId)
                        .then(allData => {
                            if (typeof window.copyPlanDetailsToClipboard === 'function') {
                                window.copyPlanDetailsToClipboard(allData);
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
                        .catch(err => {
                            console.error('Failed to copy plan details (modal):', err);
                            Swal.default.fire({
                                title: 'Failed to prepare plan content for clipboard.',
                                icon: 'error',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        });
                });
            });
        }
    });

})();