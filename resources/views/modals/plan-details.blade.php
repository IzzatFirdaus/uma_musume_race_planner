{{--
    This Blade partial is converted from the original plan_details_modal.php.
    It provides a full-featured modal for editing the details of a selected plan.
--}}
<div class="modal fade" id="planDetailsModal" tabindex="-1" aria-labelledby="planDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planDetailsModalLabel">Plan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="loading-overlay" id="planDetailsLoadingOverlay" style="display: none;">
                <div class="spinner-border text-uma" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            {{-- UPDATED: Removed the 'action' attribute. Form submission is handled by app.js. --}}
            <form id="planDetailsForm" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <div class="modal-body">
                    @include('plans.partials.form-tabs', ['id_suffix' => ''])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="exportPlanBtn">Copy to Clipboard</button>
                    <a href="#" id="downloadTxtLink" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-text"></i> Export as TXT</a>
                    <button type="submit" class="btn btn-uma">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
{{-- JavaScript logic specific to the Plan Details Modal --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const planDetailsModalElement = document.getElementById('planDetailsModal');
    if (!planDetailsModalElement) return;

    const chartTab = document.getElementById('progress-chart-tab');
    let growthChartInstance = null;

    // Helper to get CSS variable values for styling the chart
    function getCssVariableValue(variableName) {
        return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
    }

    /**
     * Renders the progress chart for the given plan ID inside the modal.
     * @param {number|string} planId The ID of the plan to fetch data for.
     */
    async function renderGrowthChart(planId) {
        if (!planId) return;

        const chartCanvas = document.getElementById('growthChart');
        const messageContainer = document.getElementById('growthChartMessage');

        // Always destroy the old instance to prevent stale charts or errors
        if (growthChartInstance) {
            growthChartInstance.destroy();
            growthChartInstance = null;
        }

        try {
            // Set chart-wide font styles to match the app's body styles
            Chart.defaults.font.family = getCssVariableValue('--bs-body-font-family');
            Chart.defaults.color = getCssVariableValue('--bs-secondary-color');

            const response = await fetch(`/api/v1/plans/${planId}/progress-chart`);
            const result = await response.json();

            // Check for a successful response with valid data
            if (result.success && Array.isArray(result.data) && result.data.length > 0) {
                chartCanvas.style.display = 'block';
                messageContainer.style.display = 'none';

                const turns = result.data;
                const ctx = chartCanvas.getContext('2d');

                growthChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: turns.map(t => `Turn ${t.turn}`),
                        datasets: [
                            { label: 'Speed', data: turns.map(t => t.speed), borderColor: getCssVariableValue('--stat-speed-color'), tension: 0.3 },
                            { label: 'Stamina', data: turns.map(t => t.stamina), borderColor: getCssVariableValue('--stat-stamina-color'), tension: 0.3 },
                            { label: 'Power', data: turns.map(t => t.power), borderColor: getCssVariableValue('--stat-power-color'), tension: 0.3 },
                            { label: 'Guts', data: turns.map(t => t.guts), borderColor: getCssVariableValue('--stat-guts-color'), tension: 0.3 },
                            { label: 'Wit', data: turns.map(t => t.wit), borderColor: getCssVariableValue('--stat-wit-color'), tension: 0.3 }
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
                chartCanvas.style.display = 'none';
                messageContainer.style.display = 'block';
                messageContainer.innerHTML = '<p class="text-muted fs-5">No progression data available.</p>';
            }
        } catch (error) {
            chartCanvas.style.display = 'none';
            messageContainer.style.display = 'block';
            messageContainer.innerHTML = '<p class="text-danger">Could not load chart data.</p>';
            console.error('Error loading modal chart:', error);
        }
    }

    // Listen for when the chart tab is shown to render the chart
    if (chartTab) {
        chartTab.addEventListener('shown.bs.tab', function() {
            const currentPlanId = document.getElementById('planId').value;
            renderGrowthChart(currentPlanId);
        });
    }

    // Add event listener for the "Export as TXT" button
    const downloadLink = document.getElementById('downloadTxtLink');
    if(downloadLink) {
        downloadLink.addEventListener('click', function(e) {
            e.preventDefault();
            const planId = document.getElementById('planId').value;
            if (!planId) return;

            const planTitle = document.getElementById('plan_title').value || 'plan';
            const safeFileName = planTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase();
            const fileName = `${safeFileName}_${planId}.txt`;

            this.href = `/plans/${planId}/export?format=txt`;
            this.download = fileName;
            this.target = '_blank';
            this.click();
        });
    }
});
</script>
@endpush
