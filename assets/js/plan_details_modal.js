// assets/js/plan_details_modal.js
// Extracted from plan_details_modal.php inline script
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const planDetailsModalElement = document.getElementById('planDetailsModal');
        if (!planDetailsModalElement) {
            return;
        }

        const chartTab = document.getElementById('progress-chart-tab');
        let growthChartInstance = null;

        function getCssVariableValue(name)
        {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        }

        async function renderGrowthChart(planId)
        {
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
                    growthChartInstance.destroy(); } catch (e) {
                                /* ignore */ }
                    growthChartInstance = null;
            }

            try {
                Chart.defaults.font.family = getCssVariableValue('--bs-body-font-family');
                Chart.defaults.color = getCssVariableValue('--bs-secondary-color');

                const resp = await fetch(`${window.APP_API_BASE} / progress.php ? action = chart & plan_id = ${encodeURIComponent(planId)}`);
                const result = await resp.json();

                if (result.success && Array.isArray(result.data) && result.data.length > 0) {
                    chartCanvas.style.display = 'block';
                    messageContainer.style.display = 'none';

                    const turns = result.data;
                    const ctx = chartCanvas.getContext('2d');

                  // Defer chart construction to the next animation frame to avoid forcing layout during synchronous JS
                    window.requestAnimationFrame(() => {
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
                    messageContainer.innerHTML = '<p class="text-muted fs-5">No progression data available for this plan.</p>';
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
                link.href = `export_plan_data.php ? id = ${encodeURIComponent(planId)} & format = txt`;
                link.download = fileName;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
    });

})();
