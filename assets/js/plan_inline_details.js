/* eslint-env browser */
(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        let growthChartInstanceInline = null;
        const chartTabInline = document.getElementById('progress-chart-tab-inline');

        function getCssVariableValue(variableName)
        {
            return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
        }

    const prefersReducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;

        async function renderGrowthChartInline(planId)
        {
            if (!planId) {
                return;
            }
            const chartCanvas = document.getElementById('growthChartInline');
            const messageContainer = document.getElementById('growthChartMessageInline');
            if (growthChartInstanceInline) {
                try {
                    growthChartInstanceInline.destroy(); } catch (_) {
                    }
                    growthChartInstanceInline = null;
            }
            try {
                Chart.defaults.font.family = getCssVariableValue('--bs-body-font-family') || Chart.defaults.font.family;
                Chart.defaults.color = getCssVariableValue('--bs-secondary-color') || Chart.defaults.color;
                const base = (window.APP_CONFIG && window.APP_CONFIG.API_BASE) || window.APP_API_BASE || '/api';
                const response = await fetch(`${base}/progress.php?action=chart&plan_id=${encodeURIComponent(String(planId))}`, { headers : { 'Accept' : 'application/json' } });
                const result = await response.json();
                if (result?.success && Array.isArray(result.data) && result.data.length > 0) {
                    chartCanvas.style.display = 'block';
                    messageContainer.style.display = 'none';
                    const turns = result.data;
                    const ctx = chartCanvas.getContext('2d');
                    // Defer reading CSS variables and chart construction to rAF to avoid layout forcing
                    requestAnimationFrame(() => {
                        growthChartInstanceInline = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: turns.map(t => `Turn ${t.turn}`),
                                datasets: [
                                { label: 'Speed', data: turns.map(t => t.speed), borderColor: getCssVariableValue('--stat-speed-color'), pointBackgroundColor: getCssVariableValue('--stat-speed-color'), tension: 0.3 },
                                { label: 'Stamina', data: turns.map(t => t.stamina), borderColor: getCssVariableValue('--stat-stamina-color'), pointBackgroundColor: getCssVariableValue('--stat-stamina-color'), tension: 0.3 },
                                { label: 'Power', data: turns.map(t => t.power), borderColor: getCssVariableValue('--stat-power-color'), pointBackgroundColor: getCssVariableValue('--stat-power-color'), tension: 0.3 },
                                { label: 'Guts', data: turns.map(t => t.guts), borderColor: getCssVariableValue('--stat-guts-color'), pointBackgroundColor: getCssVariableValue('--stat-guts-color'), tension: 0.3 },
                                { label: 'Wit', data: turns.map(t => t.wit), borderColor: getCssVariableValue('--stat-wit-color'), pointBackgroundColor: getCssVariableValue('--stat-wit-color'), tension: 0.3 }
                                  ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: prefersReducedMotion ? false : { duration : 500 },
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
                    messageContainer.innerHTML = '<p class="text-muted fs-5 mb-0">No progression data available for this plan.</p>';
                }
            } catch (error) {
                chartCanvas.style.display = 'none';
                messageContainer.style.display = 'block';
                messageContainer.innerHTML = '<p class="text-danger mb-0">Could not load chart data. Please check the console for details.</p>';
                console.error('Error loading inline chart:', error);
            }
        }

        if (chartTabInline) {
            chartTabInline.addEventListener('shown.bs.tab', function () {
                const currentPlanId = document.getElementById('planIdInline')?.value;
                renderGrowthChartInline(currentPlanId);
            });
        }

        const txtBtn = document.getElementById('downloadTxtInline');
        if (txtBtn) {
            txtBtn.addEventListener('click', function () {
                const planId = document.getElementById('planIdInline')?.value;
                if (!planId) {
                    return;
                }
                const planTitle = document.getElementById('plan_title_inline')?.value || 'plan';
                const safeFileName = String(planTitle).replace(/[^a-z0-9_-]/gi, '_').toLowerCase();
                const fileName = `${safeFileName}_${planId}.txt`;
                const link = document.createElement('a');
                link.href = `export_plan_data.php?id=${encodeURIComponent(String(planId))}&format=txt`;
                link.download = fileName;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        }
    });
})();
