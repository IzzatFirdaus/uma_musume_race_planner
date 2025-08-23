<?php
// plan_details_modal.php
// This file relies on PHP variables being available from index.php
require_once __DIR__ . '/includes/logger.php';

// Ensure these variables are available from index.php, provide empty arrays as a fallback
$careerStageOptions = $careerStageOptions ?? [];
$classOptions = $classOptions ?? [];
$strategyOptions = $strategyOptions ?? [];
$moodOptions = $moodOptions ?? [];
$conditionOptions = $conditionOptions ?? [];
?>

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
            <form id="planDetailsForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="planTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                data-bs-target="#general" type="button" role="tab" aria-controls="general"
                                aria-selected="true">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attributes-tab" data-bs-toggle="tab"
                                data-bs-target="#attributes" type="button" role="tab" aria-controls="attributes"
                                aria-selected="false">Attributes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades"
                                type="button" role="tab" aria-controls="grades" aria-selected="false">Aptitude
                                Grades</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills"
                                type="button" role="tab" aria-controls="skills"
                                aria-selected="false">Skills</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="predictions-tab" data-bs-toggle="tab"
                                data-bs-target="#predictions" type="button" role="tab" aria-controls="predictions"
                                aria-selected="false">Race Predictions</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="goals-tab" data-bs-toggle="tab" data-bs-target="#goals"
                                type="button" role="tab" aria-controls="goals" aria-selected="false">Goals</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="progress-chart-tab" data-bs-toggle="tab"
                                data-bs-target="#progress-chart" type="button" role="tab"
                                aria-controls="progress-chart" aria-selected="false">Progress Chart</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="general" role="tabpanel"
                            aria-labelledby="general-tab">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="plan_title" class="form-label">Plan Title</label>
                                    <input type="text" class="form-control" id="plan_title" name="plan_title">
                                    <input type="hidden" id="planId" name="planId">
                                </div>
                                <div class="col-md-4">
                                    <label for="modalTurnBefore" class="form-label">Turn Before</label>
                                    <input type="number" class="form-control" id="modalTurnBefore"
                                        name="modalTurnBefore">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="modalName" class="form-label">Trainee Name</label>
                                    <input type="text" class="form-control" id="modalName" name="modalName"
                                        required>
                                </div>
                                <div class="col-md-6">
                                    <label for="modalRaceName" class="form-label">Next Race Name</label>
                                    <input type="text" class="form-control" id="modalRaceName"
                                        name="modalRaceName">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <?php $id_suffix = ''; // Use no suffix for the modal?>
                                <?php include __DIR__ . '/components/trainee_image_handler.php'; ?>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label for="modalCareerStage" class="form-label">Career Stage</label>
                                            <select class="form-select" id="modalCareerStage" name="modalCareerStage"
                                                required>
                                                <?php foreach ($careerStageOptions as $option) : ?>
                                                <option value="<?php echo htmlspecialchars((string) ($option['value'] ?? '')); ?>"><?php echo htmlspecialchars((string) ($option['text'] ?? '')); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="modalClass" class="form-label">Class</label>
                                            <select class="form-select" id="modalClass" name="modalClass" required>
                                                <?php foreach ($classOptions as $option) : ?>
                                                <option value="<?php echo htmlspecialchars((string) ($option['value'] ?? '')); ?>"><?php echo htmlspecialchars((string) ($option['text'] ?? '')); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <label for="modalStatus" class="form-label">Status</label>
                                            <select class="form-select" id="modalStatus" name="modalStatus">
                                                <option value="Planning">Planning</option>
                                                <option value="Active">Active</option>
                                                <option value="Finished">Finished</option>
                                                <option value="Draft">Draft</option>
                                                <option value="Abandoned">Abandoned</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="modalStrategy" class="form-label">Strategy</label>
                                    <select class="form-select" id="modalStrategy" name="modalStrategy">
                                        <?php foreach ($strategyOptions as $option) : ?>
                                        <option value="<?php echo htmlspecialchars((string) ($option['id'] ?? '')); ?>"><?php echo htmlspecialchars((string) ($option['label'] ?? '')); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="modalMood" class="form-label">Mood</label>
                                    <select class="form-select" id="modalMood" name="modalMood">
                                        <?php foreach ($moodOptions as $option) : ?>
                                        <option value="<?php echo htmlspecialchars((string) ($option['id'] ?? '')); ?>"><?php echo htmlspecialchars((string) ($option['label'] ?? '')); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="modalCondition" class="form-label">Condition</label>
                                    <select class="form-select" id="modalCondition" name="modalCondition">
                                        <?php foreach ($conditionOptions as $option) : ?>
                                        <option value="<?php echo htmlspecialchars((string) ($option['id'] ?? '')); ?>"><?php echo htmlspecialchars((string) ($option['label'] ?? '')); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="modalGoal" class="form-label">Goal</label>
                                    <input type="text" class="form-control" id="modalGoal" name="modalGoal">
                                </div>
                                <div class="col-md-6">
                                    <label for="modalSource" class="form-label">Source</label>
                                    <input type="text" class="form-control" id="modalSource" name="modalSource">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="modalMonth" class="form-label">Month</label>
                                    <input type="text" class="form-control" id="modalMonth" name="modalMonth"
                                        placeholder="e.g., July">
                                </div>
                                <div class="col-md-6">
                                    <label for="modalTimeOfDay" class="form-label">Time of Day</label>
                                    <input type="text" class="form-control" id="modalTimeOfDay"
                                        name="modalTimeOfDay" placeholder="e.g., Early / Late">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="skillPoints" class="form-label">Total SP</label>
                                    <input type="number" class="form-control" id="skillPoints" name="skillPoints"
                                        value="0">
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="acquireSkillSwitch"
                                            name="acquireSkillSwitch" value="YES">
                                        <label class="form-check-label" for="acquireSkillSwitch">Acquire
                                            Skill?</label>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="raceDaySwitch"
                                            name="raceDaySwitch">
                                        <label class="form-check-label" for="raceDaySwitch">Race Day?</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="energyRange" class="form-label">Energy (<span
                                            id="energyValue">0</span>/100%)</label>
                                    <input type="range" class="form-range" min="0" max="100"
                                        id="energyRange" name="energyRange">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <h6>Growth Rates (%)</h6>
                                <div class="col">
                                    <label for="growthRateSpeed" class="form-label">Speed</label>
                                    <input type="number" class="form-control" id="growthRateSpeed"
                                        name="growthRateSpeed" value="0">
                                </div>
                                <div class="col">
                                    <label for="growthRateStamina" class="form-label">Stamina</label>
                                    <input type="number" class="form-control" id="growthRateStamina"
                                        name="growthRateStamina" value="0">
                                </div>
                                <div class="col">
                                    <label for="growthRatePower" class="form-label">Power</label>
                                    <input type="number" class="form-control" id="growthRatePower"
                                        name="growthRatePower" value="0">
                                </div>
                                <div class="col">
                                    <label for="growthRateGuts" class="form-label">Guts</label>
                                    <input type="number" class="form-control" id="growthRateGuts"
                                        name="growthRateGuts" value="0">
                                </div>
                                <div class="col">
                                    <label for="growthRateWit" class="form-label">Wit</label>
                                    <input type="number" class="form-control" id="growthRateWit"
                                        name="growthRateWit" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="attributes" role="tabpanel" aria-labelledby="attributes-tab">
                            <div id="attributeSlidersContainer">
                            </div>
                        </div>

                        <div class="tab-pane fade" id="grades" role="tabpanel" aria-labelledby="grades-tab">
                            <div class="row" id="aptitudeGradesContainer"></div>
                        </div>

                        <div class="tab-pane fade" id="skills" role="tabpanel" aria-labelledby="skills-tab">
                            <div class="table-responsive">
                                <table class="table table-sm" id="skillsTable">
                                    <thead>
                                        <tr>
                                            <th>Skill Name</th>
                                            <th>SP Cost</th>
                                            <th class="text-center">Acquired</th>
                                            <th>Tag</th>
                                            <th>Notes</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-uma w-100 mt-2" id="addSkillBtn">Add Skill</button>
                        </div>

                        <div class="tab-pane fade" id="predictions" role="tabpanel"
                            aria-labelledby="predictions-tab">
                            <div class="table-responsive">
                                <table class="table table-sm" id="predictionsTable">
                                    <thead>
                                        <tr>
                                            <th>Race</th>
                                            <th>Venue</th>
                                            <th>Ground</th>
                                            <th>Distance</th>
                                            <th>Track</th>
                                            <th>Direction</th>
                                            <th>Speed</th>
                                            <th>Stamina</th>
                                            <th>Power</th>
                                            <th>Guts</th>
                                            <th>Wit</th>
                                            <th>Comment</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-uma w-100 mt-2" id="addPredictionBtn">Add
                                Prediction</button>
                        </div>

                        <div class="tab-pane fade" id="goals" role="tabpanel" aria-labelledby="goals-tab">
                            <div class="table-responsive">
                                <table class="table table-sm" id="goalsTable">
                                    <thead>
                                        <tr>
                                            <th>Goal</th>
                                            <th>Result</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-uma w-100 mt-2" id="addGoalBtn">Add Goal</button>
                        </div>

                        <div class="tab-pane fade" id="progress-chart" role="tabpanel" aria-labelledby="progress-chart-tab">
                            <div class="chart-container" style="position: relative; height: 400px;">
                                <canvas id="growthChart"></canvas>
                                <div id="growthChartMessage" class="text-center p-5 h-100 d-flex justify-content-center align-items-center" style="display: none;">
                                    <p class="text-muted fs-5">No progression data available for this plan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const planDetailsModalElement = document.getElementById('planDetailsModal');
    if (!planDetailsModalElement) return;

    const chartTab = document.getElementById('progress-chart-tab');
    let growthChartInstance = null;

    // Helper function to get computed CSS variable values
    function getCssVariableValue(variableName) {
        return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
    }

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
            // Set chart-wide font styles to match your app's body styles
            Chart.defaults.font.family = getCssVariableValue('--bs-body-font-family');
            Chart.defaults.color = getCssVariableValue('--bs-secondary-color');

            const response = await fetch(`${window.APP_API_BASE}/progress.php?action=chart&plan_id=${planId}`);
            const result = await response.json();

            // UPDATED: Robust check for data
            if (result.success && Array.isArray(result.data) && result.data.length > 0) {
                // We have data: show canvas, hide message
                chartCanvas.style.display = 'block';
                messageContainer.style.display = 'none';
                
                const turns = result.data;
                const ctx = chartCanvas.getContext('2d');

                growthChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: turns.map(t => `Turn ${t.turn}`),
                        datasets: [{
                                label: 'Speed',
                                data: turns.map(t => t.speed),
                                borderColor: getCssVariableValue('--stat-speed-color'),
                                pointBackgroundColor: getCssVariableValue('--stat-speed-color'),
                                tension: 0.3
                            },
                            {
                                label: 'Stamina',
                                data: turns.map(t => t.stamina),
                                borderColor: getCssVariableValue('--stat-stamina-color'),
                                pointBackgroundColor: getCssVariableValue('--stat-stamina-color'),
                                tension: 0.3
                            },
                            {
                                label: 'Power',
                                data: turns.map(t => t.power),
                                borderColor: getCssVariableValue('--stat-power-color'),
                                pointBackgroundColor: getCssVariableValue('--stat-power-color'),
                                tension: 0.3
                            },
                            {
                                label: 'Guts',
                                data: turns.map(t => t.guts),
                                borderColor: getCssVariableValue('--stat-guts-color'),
                                pointBackgroundColor: getCssVariableValue('--stat-guts-color'),
                                tension: 0.3
                            },
                            {
                                label: 'Wit',
                                data: turns.map(t => t.wit),
                                borderColor: getCssVariableValue('--stat-wit-color'),
                                pointBackgroundColor: getCssVariableValue('--stat-wit-color'),
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
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
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: getCssVariableValue('--bs-border-color-translucent')
                                }
                            },
                            x: {
                                grid: {
                                    color: getCssVariableValue('--bs-border-color-translucent')
                                }
                            }
                        }
                    }
                });
            } else {
                // No data: hide canvas, show message
                chartCanvas.style.display = 'none';
                messageContainer.style.display = 'block';
                // Also reset the message text in case it was changed to an error
                messageContainer.innerHTML = '<p class="text-muted fs-5">No progression data available for this plan.</p>';
            }
        } catch (error) {
            // Error state: hide canvas, show an error message
            chartCanvas.style.display = 'none';
            messageContainer.style.display = 'block';
            messageContainer.innerHTML = '<p class="text-danger">Could not load chart data. Please check the console for details.</p>';
            console.error('Error loading chart:', error);
        }
    }

    // Listen for when the chart tab is shown and render the chart
    chartTab.addEventListener('shown.bs.tab', function() {
        const currentPlanId = document.getElementById('planId').value;
        renderGrowthChart(currentPlanId);
    });

    document.getElementById('downloadTxtLink').addEventListener('click', function(e) {
        e.preventDefault();
        const planId = document.getElementById('planId').value;
        if (!planId) return;

        const planTitle = document.getElementById('plan_title').value || 'plan';
        const safeFileName = planTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        
        const fileName = `${safeFileName}_${planId}.txt`;
        const link = document.createElement('a');
        link.href = `export_plan_data.php?id=${planId}&format=txt`;
        link.download = fileName;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
</script>