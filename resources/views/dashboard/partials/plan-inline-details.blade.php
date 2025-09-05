{{--
    Inline card view for editing the full details of a selected plan.
    No direct image/background usage here, but ensure any referenced partials
    (like form-tabs) also use `asset()` for uploaded images.
--}}
<div id="planInlineDetails" class="card mb-4" style="display: none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0" id="planInlineDetailsLabel">Plan Details</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="closeInlineDetailsBtn">
            <i class="bi bi-x"></i> Close
        </button>
    </div>
    <div class="loading-overlay" id="planInlineDetailsLoadingOverlay" style="display: none;">
        <div class="spinner-border text-uma" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    {{-- This form would submit to a Laravel route for updating the plan --}}
    <form id="planDetailsFormInline" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="card-body">
            {{-- Ensure form-tabs partial uses asset() for any uploaded images --}}
            @include('plans.partials.form-tabs', ['id_suffix' => '_inline'])
        </div>
        <div class="card-footer d-flex justify-content-end">
            <button type="button" class="btn btn-outline-secondary me-2" id="downloadTxtInline">
                <i class="bi bi-file-earmark-text"></i> Export as TXT
            </button>
            <button type="button" class="btn btn-info me-2" id="exportPlanBtnInline">Copy to Clipboard</button>
            <button type="submit" class="btn btn-uma">Save Changes</button>
        </div>
    </form>
</div>

@push('scripts')
{{-- JavaScript logic specific to the Inline Plan Details view --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    let growthChartInstanceInline = null;
    const chartTabInline = document.getElementById('progress-chart-tab-inline');
    const txtBtnInline = document.getElementById('downloadTxtInline');

    // Helper to get CSS variable values for chart styling
    function getCssVariableValue(variableName) {
        return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
    }

    /**
     * Renders the progress chart for the given plan ID inside the inline view.
     * @param {number|string} planId The ID of the plan to fetch data for.
     */
    async function renderGrowthChartInline(planId) {
        if (!planId) return;

        const chartCanvas = document.getElementById('growthChartInline');
        const messageContainer = document.getElementById('growthChartMessageInline');

        // Always destroy the old instance to prevent stale tooltips and data
        if (growthChartInstanceInline) {
            growthChartInstanceInline.destroy();
            growthChartInstanceInline = null;
        }

        try {
            // Set chart-wide font styles to match app's body styles
            Chart.defaults.font.family = getCssVariableValue('--bs-body-font-family');
            Chart.defaults.color = getCssVariableValue('--bs-secondary-color');

            // --- UPDATED: Fetch from a Laravel API route ---
            const response = await fetch(`/api/v1/plans/${planId}/progress-chart`);
            const result = await response.json();

            // Check for a successful response with valid data
            if (result.success && Array.isArray(result.data) && result.data.length > 0) {
                chartCanvas.style.display = 'block';
                messageContainer.style.display = 'none';

                const turns = result.data;
                const ctx = chartCanvas.getContext('2d');

                growthChartInstanceInline = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: turns.map(t => `Turn ${t.turn}`),
                        datasets: [{
                                label: 'Speed',
                                data: turns.map(t => t.speed),
                                borderColor: getCssVariableValue('--stat-speed-color'),
                                tension: 0.3
                            },
                            {
                                label: 'Stamina',
                                data: turns.map(t => t.stamina),
                                borderColor: getCssVariableValue('--stat-stamina-color'),
                                tension: 0.3
                            },
                            {
                                label: 'Power',
                                data: turns.map(t => t.power),
                                borderColor: getCssVariableValue('--stat-power-color'),
                                tension: 0.3
                            },
                            {
                                label: 'Guts',
                                data: turns.map(t => t.guts),
                                borderColor: getCssVariableValue('--stat-guts-color'),
                                tension: 0.3
                            },
                            {
                                label: 'Wit',
                                data: turns.map(t => t.wit),
                                borderColor: getCssVariableValue('--stat-wit-color'),
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { labels: { usePointStyle: true, color: getCssVariableValue('--bs-body-color') } },
                            tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.85)', cornerRadius: 4 }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: getCssVariableValue('--bs-border-color-translucent') } },
                            x: { grid: { color: getCssVariableValue('--bs-border-color-translucent') } }
                        }
                    }
                });
            } else {
                // If no data, hide the chart and show a message
                chartCanvas.style.display = 'none';
                messageContainer.style.display = 'block';
                messageContainer.innerHTML = '<p class="text-muted fs-5">No progression data available for this plan.</p>';
            }
        } catch (error) {
            chartCanvas.style.display = 'none';
            messageContainer.style.display = 'block';
            messageContainer.innerHTML = '<p class="text-danger">Could not load chart data.</p>';
            console.error('Error loading inline chart:', error);
        }
    }

    // Add event listener to render the chart when its tab is shown
    if (chartTabInline) {
        chartTabInline.addEventListener('shown.bs.tab', function() {
            const currentPlanId = document.getElementById('planId_inline').value;
            renderGrowthChartInline(currentPlanId);
        });
    }

    // Add event listener for the "Export as TXT" button
    if (txtBtnInline) {
        txtBtnInline.addEventListener('click', function() {
            const planId = document.getElementById('planId_inline')?.value;
            if (!planId) return;

            const planTitle = document.getElementById('plan_title_inline').value || 'plan';
            const safeFileName = planTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase();
            const fileName = `${safeFileName}_${planId}.txt`;

            const link = document.createElement('a');
            // --- UPDATED: Point to a Laravel route for the export ---
            link.href = `/plans/${planId}/export?format=txt`;
            link.download = fileName;
            link.target = '_blank'; // Open in a new tab
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
</script>
@endpush
