<?php
// plan_details_modal.php
// This file assumes the following variables are available from index.php:
// $predictionIcons, $careerStageOptions, $classOptions, $strategyOptions,
// $moodOptions, $attributeGradeOptions, $timeOfDayOptions, $monthOptions
// Do not include config.php here, as it will be included in index.php
?>

<div class="modal fade" id="planDetailsModal" tabindex="-1" aria-labelledby="planDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planDetailsModalLabel">Plan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="loading-overlay" id="planDetailsLoadingOverlay">
                <div class="spinner-border text-uma" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <form id="planDetailsForm" action="index.php" method="post">
                <div class="modal-body">
                    <input type="hidden" id="planId" name="planId">
                    <div class="tab-content pt-3">
                        <ul class="nav nav-tabs" id="planTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">General</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="attributes-tab" data-bs-toggle="tab" data-bs-target="#attributes" type="button" role="tab" aria-controls="attributes" aria-selected="false">Attributes</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills" type="button" role="tab" aria-controls="skills" aria-selected="false">Skills</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="predictions-tab" data-bs-toggle="tab" data-bs-target="#predictions" type="button" role="tab" aria-controls="predictions" aria-selected="false">Race Predictions</button>
                            </li>
                        </ul>
                        <div class="tab-content pt-3">
                            <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="plan_title" class="form-label">Plan Title</label>
                                        <input type="text" class="form-control" id="plan_title" name="plan_title">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modalName" class="form-label">Trainee Name</label>
                                        <input type="text" class="form-control" id="modalName" name="modalName" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="modalCareerStage" class="form-label">Career Stage</label>
                                        <select class="form-select" id="modalCareerStage" name="modalCareerStage" required>
                                            <?php foreach ($careerStageOptions as $option) : ?>
                                                <option value="<?php echo $option['value']; ?>"><?php echo $option['text']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalClass" class="form-label">Class</label>
                                        <select class="form-select" id="modalClass" name="modalClass" required>
                                            <?php foreach ($classOptions as $option) : ?>
                                                <option value="<?php echo $option['value']; ?>"><?php echo $option['text']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalRaceName" class="form-label">Next Race Name</label>
                                        <input type="text" class="form-control" id="modalRaceName" name="modalRaceName">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="modalStrategy" class="form-label">Strategy</label>
                                        <select class="form-select" id="modalStrategy" name="modalStrategy">
                                            <?php foreach ($strategyOptions as $option) : ?>
                                                <option value="<?php echo $option['value']; ?>"><?php echo $option['text']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalMood" class="form-label">Mood</label>
                                        <select class="form-select" id="modalMood" name="modalMood">
                                            <?php foreach ($moodOptions as $option) : ?>
                                                <option value="<?php echo $option['value']; ?>"><?php echo $option['text']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
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
                                    <div class="col-md-4">
                                        <label for="modalTimeOfDay" class="form-label">Time of Day</label>
                                        <select class="form-select" id="modalTimeOfDay" name="modalTimeOfDay">
                                            <?php foreach ($timeOfDayOptions as $option) : ?>
                                                <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalMonth" class="form-label">Month</label>
                                        <select class="form-select" id="modalMonth" name="modalMonth">
                                            <?php foreach ($monthOptions as $option) : ?>
                                                <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalTurnBefore" class="form-label">Turn Before</label>
                                        <input type="number" class="form-control" id="modalTurnBefore" name="modalTurnBefore">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="skillPoints" class="form-label">Total Available Skill Points</label>
                                        <input type="number" class="form-control" id="skillPoints" name="skillPoints" value="0">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="raceDaySwitch" name="raceDaySwitch">
                                            <label class="form-check-label" for="raceDaySwitch">Race Day?</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="energyRange" class="form-label">Energy</label>
                                        <input type="range" class="form-range" min="0" max="100" id="energyRange" name="energyRange">
                                        <div class="progress" style="height: 5px;">
                                            <div id="energyProgress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <h6>Growth Rates</h6>
                                    <div class="col">
                                        <label for="growthRateSpeed" class="form-label">Speed %</label>
                                        <input type="number" class="form-control" id="growthRateSpeed" name="growthRateSpeed" value="0">
                                    </div>
                                    <div class="col">
                                        <label for="growthRateStamina" class="form-label">Stamina %</label>
                                        <input type="number" class="form-control" id="growthRateStamina" name="growthRateStamina" value="0">
                                    </div>
                                    <div class="col">
                                        <label for="growthRatePower" class="form-label">Power %</label>
                                        <input type="number" class="form-control" id="growthRatePower" name="growthRatePower" value="0">
                                    </div>
                                    <div class="col">
                                        <label for="growthRateGuts" class="form-label">Guts %</label>
                                        <input type="number" class="form-control" id="growthRateGuts" name="growthRateGuts" value="0">
                                    </div>
                                    <div class="col">
                                        <label for="growthRateWit" class="form-label">Wit %</label>
                                        <input type="number" class="form-control" id="growthRateWit" name="growthRateWit" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="attributes" role="tabpanel" aria-labelledby="attributes-tab">
                                <div class="row g-3" id="attributeGrid">
                                </div>
                                <input type="hidden" id="attributesJson" name="attributes">
                            </div>

                            <div class="tab-pane fade" id="skills" role="tabpanel" aria-labelledby="skills-tab">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="skillsTable">
                                        <thead>
                                            <tr>
                                                <th>Skill Name</th>
                                                <th>SP Cost</th>
                                                <th>Acquired</th>
                                                <th>Notes</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-uma w-100" id="addSkillBtn">Add Skill</button>
                                <input type="hidden" id="skillsJson" name="skills">
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
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-uma w-100" id="addPredictionBtn">Add Prediction</button>
                                <input type="hidden" id="predictionsJson" name="predictions">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="exportPlanBtn">Export Plan</button>
                    <button type="submit" class="btn btn-uma">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>