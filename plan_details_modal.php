<?php
// plan_details_modal.php
// Relies on PHP variables from index.php. Avoids closing PHP tag in includes to prevent stray output.
require_once __DIR__ . '/includes/logger.php';

// Ensure variables exist
$careerStageOptions = $careerStageOptions ?? [];
$classOptions = $classOptions ?? [];
$strategyOptions = $strategyOptions ?? [];
$moodOptions = $moodOptions ?? [];
$conditionOptions = $conditionOptions ?? [];
?>
<div class="modal fade" id="planDetailsModal" tabindex="-1" aria-labelledby="planDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="dialog" aria-modal="true" aria-describedby="planDetailsModalDesc">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planDetailsModalLabel">Plan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close dialog"></button>
            </div>

            <div id="planDetailsModalDesc" class="visually-hidden">View and edit detailed information of a plan including attributes, grades, skills, and predictions.</div>

            <div class="loading-overlay" id="planDetailsLoadingOverlay" style="display: none;">
                <div class="spinner-border text-uma" role="status" aria-live="polite" aria-label="Loading plan details">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <form id="planDetailsForm" method="POST" enctype="multipart/form-data" autocomplete="off" novalidate>
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
                                    <input type="text" class="form-control" id="plan_title" name="plan_title" maxlength="200" autocomplete="off" inputmode="text">
                                    <input type="hidden" id="planId" name="planId">
                                </div>
                                <div class="col-md-4">
                                    <label for="modalTurnBefore" class="form-label">Turn Before</label>
                                    <input type="number" class="form-control" id="modalTurnBefore"
                                        name="modalTurnBefore" min="0" max="999" inputmode="numeric" autocomplete="off">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="modalName" class="form-label">Trainee Name</label>
                                    <input type="text" class="form-control" id="modalName" name="modalName"
                                        required maxlength="150" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label for="modalRaceName" class="form-label">Next Race Name</label>
                                    <input type="text" class="form-control" id="modalRaceName"
                                        name="modalRaceName" maxlength="200" autocomplete="off">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <?php $id_suffix = ''; ?>
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
                                    <input type="text" class="form-control" id="modalGoal" name="modalGoal" maxlength="200" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label for="modalSource" class="form-label">Source</label>
                                    <input type="text" class="form-control" id="modalSource" name="modalSource" maxlength="200" autocomplete="off">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="modalMonth" class="form-label">Month</label>
                                    <input type="text" class="form-control" id="modalMonth" name="modalMonth"
                                        placeholder="e.g., July" maxlength="20" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label for="modalTimeOfDay" class="form-label">Time of Day</label>
                                    <input type="text" class="form-control" id="modalTimeOfDay"
                                        name="modalTimeOfDay" placeholder="e.g., Early / Late" maxlength="20" autocomplete="off">
                                </div>
                            </div>
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="skillPoints" class="form-label">Total SP</label>
                                    <input type="number" class="form-control" id="skillPoints" name="skillPoints" value="0" min="0" max="100000" inputmode="numeric" autocomplete="off">
                                </div>
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="acquireSkillSwitch"
                                            name="acquireSkillSwitch" value="YES">
                                        <label class="form-check-label" for="acquireSkillSwitch">Acquire Skill?</label>
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
                                    <label for="energyRange" class="form-label">Energy (<span id="energyValue">0</span>/100%)</label>
                                    <input type="range" class="form-range" min="0" max="100"
                                        id="energyRange" name="energyRange" aria-valuemin="0" aria-valuemax="100" aria-describedby="energyValue">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <h6>Growth Rates (%)</h6>
                                <div class="col">
                                    <label for="growthRateSpeed" class="form-label">Speed</label>
                                    <input type="number" class="form-control" id="growthRateSpeed"
                                        name="growthRateSpeed" value="0" min="-100" max="100" inputmode="numeric" autocomplete="off">
                                </div>
                                <div class="col">
                                    <label for="growthRateStamina" class="form-label">Stamina</label>
                                    <input type="number" class="form-control" id="growthRateStamina"
                                        name="growthRateStamina" value="0" min="-100" max="100" inputmode="numeric" autocomplete="off">
                                </div>
                                <div class="col">
                                    <label for="growthRatePower" class="form-label">Power</label>
                                    <input type="number" class="form-control" id="growthRatePower"
                                        name="growthRatePower" value="0" min="-100" max="100" inputmode="numeric" autocomplete="off">
                                </div>
                                <div class="col">
                                    <label for="growthRateGuts" class="form-label">Guts</label>
                                    <input type="number" class="form-control" id="growthRateGuts"
                                        name="growthRateGuts" value="0" min="-100" max="100" inputmode="numeric" autocomplete="off">
                                </div>
                                <div class="col">
                                    <label for="growthRateWit" class="form-label">Wit</label>
                                    <input type="number" class="form-control" id="growthRateWit"
                                        name="growthRateWit" value="0" min="-100" max="100" inputmode="numeric" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="attributes" role="tabpanel" aria-labelledby="attributes-tab">
                            <div id="attributeSlidersContainer"></div>
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

                        <div class="tab-pane fade" id="predictions" role="tabpanel" aria-labelledby="predictions-tab">
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
                            <button type="button" class="btn btn-uma w-100 mt-2" id="addPredictionBtn">Add Prediction</button>
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
                                <canvas id="growthChart" aria-label="Growth chart over turns" role="img"></canvas>
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
                        <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                        Export as TXT
                    </a>
                    <button type="submit" class="btn btn-uma">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- inline script moved to assets/js/plan_details_modal.js -->
<?php
// Externalize inline script for modal behaviors
$scriptBase = rtrim(preg_replace('~/public/?$~', '/', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/')), '/') . '/';
?>
<script defer src="<?= htmlspecialchars($scriptBase, ENT_QUOTES, 'UTF-8') ?>assets/js/plan_details_modal.js"></script>
