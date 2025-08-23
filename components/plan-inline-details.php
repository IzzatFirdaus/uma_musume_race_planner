<?php
// components/plan-inline-details.php
// This file relies on PHP variables being available from index.php
// Adjusted version with improved comments, accessibility, and minor logic optimizations

// Ensure these variables are available, provide empty arrays as a fallback
$careerStageOptions = $careerStageOptions ?? [];
$classOptions = $classOptions ?? [];
$strategyOptions = $strategyOptions ?? [];
$moodOptions = $moodOptions ?? [];
$conditionOptions = $conditionOptions ?? [];

// Compute base path for assets whether included from /public or root
$isPublic = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/public/') !== false;
$base = $isPublic ? '../' : '';
$baseEsc = htmlspecialchars($base, ENT_QUOTES, 'UTF-8');
?>

<div id="planInlineDetails" class="card mb-4" style="display: none;">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0" id="planInlineDetailsLabel">Plan Details</h5>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="closeInlineDetailsBtn" aria-label="Close plan details">
      <i class="bi bi-x" aria-hidden="true"></i> Close
    </button>
  </div>

  <!-- Loading overlay for AJAX fetches -->
  <div class="loading-overlay" id="planInlineDetailsLoadingOverlay" style="display: none;" role="status" aria-live="polite" aria-busy="true">
    <div class="spinner-border text-uma" role="status" aria-label="Loading">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <form id="planDetailsFormInline" enctype="multipart/form-data" novalidate>
    <div class="card-body">
      <!-- Tab navigation for plan sections -->
      <ul class="nav nav-tabs" id="planTabsInline" role="tablist" aria-label="Plan sections">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="general-tab-inline" data-bs-toggle="tab"
            data-bs-target="#general-inline" type="button" role="tab" aria-controls="general-inline"
            aria-selected="true">General</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="attributes-tab-inline" data-bs-toggle="tab"
            data-bs-target="#attributes-inline" type="button" role="tab"
            aria-controls="attributes-inline" aria-selected="false">Attributes</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="grades-tab-inline" data-bs-toggle="tab" data-bs-target="#grades-inline"
            type="button" role="tab" aria-controls="grades-inline" aria-selected="false">Aptitude Grades</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="skills-tab-inline" data-bs-toggle="tab" data-bs-target="#skills-inline"
            type="button" role="tab" aria-controls="skills-inline" aria-selected="false">Skills</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="predictions-tab-inline" data-bs-toggle="tab"
            data-bs-target="#predictions-inline" type="button" role="tab"
            aria-controls="predictions-inline" aria-selected="false">Race Predictions</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="goals-tab-inline" data-bs-toggle="tab" data-bs-target="#goals-inline"
            type="button" role="tab" aria-controls="goals-inline" aria-selected="false">Goals</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="progress-chart-tab-inline" data-bs-toggle="tab"
            data-bs-target="#progress-chart-inline" type="button" role="tab"
            aria-controls="progress-chart-inline" aria-selected="false">Progress Chart</button>
        </li>
      </ul>

      <!-- Tab content panels -->
      <div class="tab-content pt-3">
        <!-- General Plan Info -->
        <div class="tab-pane fade show active" id="general-inline" role="tabpanel" aria-labelledby="general-tab-inline">
          <div class="row mb-3">
            <div class="col-md-8">
              <label for="plan_title_inline" class="form-label">Plan Title</label>
              <input type="text" class="form-control" id="plan_title_inline" name="plan_title" autocomplete="off" inputmode="text">
              <input type="hidden" id="planIdInline" name="planId">
            </div>
            <div class="col-md-4">
              <label for="modalTurnBefore_inline" class="form-label">Turn Before</label>
              <input type="number" class="form-control" id="modalTurnBefore_inline" name="modalTurnBefore" inputmode="numeric" min="0">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="modalName_inline" class="form-label">Trainee Name</label>
              <input type="text" class="form-control" id="modalName_inline" name="modalName" required autocomplete="name">
            </div>
            <div class="col-md-6">
              <label for="modalRaceName_inline" class="form-label">Next Race Name</label>
              <input type="text" class="form-control" id="modalRaceName_inline" name="modalRaceName" autocomplete="off">
            </div>
          </div>
          <div class="row mb-3">
            <?php $id_suffix = '_inline'; // Use '_inline' suffix for this view?>
            <?php include __DIR__ . '/trainee_image_handler.php'; ?>
            <div class="col-md-6">
              <div class="row">
                <div class="col-md-8">
                  <label for="modalCareerStage_inline" class="form-label">Career Stage</label>
                  <select class="form-select" id="modalCareerStage_inline" name="modalCareerStage" required>
                    <?php foreach ($careerStageOptions as $option) : ?>
                      <option value="<?= htmlspecialchars((string)$option['value']) ?>">
                        <?= htmlspecialchars((string)$option['text']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="modalClass_inline" class="form-label">Class</label>
                  <select class="form-select" id="modalClass_inline" name="modalClass" required>
                    <?php foreach ($classOptions as $option) : ?>
                      <option value="<?= htmlspecialchars((string)$option['value']) ?>">
                        <?= htmlspecialchars((string)$option['text']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-md-12">
                  <label for="modalStatus_inline" class="form-label">Status</label>
                  <select class="form-select" id="modalStatus_inline" name="modalStatus">
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
              <label for="modalStrategy_inline" class="form-label">Strategy</label>
              <select class="form-select" id="modalStrategy_inline" name="modalStrategy">
                <?php foreach ($strategyOptions as $option) : ?>
                  <option value="<?= htmlspecialchars((string)$option['id']) ?>">
                    <?= htmlspecialchars((string)$option['label']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label for="modalMood_inline" class="form-label">Mood</label>
              <select class="form-select" id="modalMood_inline" name="modalMood">
                <?php foreach ($moodOptions as $option) : ?>
                  <option value="<?= htmlspecialchars((string)$option['id']) ?>">
                    <?= htmlspecialchars((string)$option['label']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label for="modalCondition_inline" class="form-label">Condition</label>
              <select class="form-select" id="modalCondition_inline" name="modalCondition">
                <?php foreach ($conditionOptions as $option) : ?>
                  <option value="<?= htmlspecialchars((string)$option['id']) ?>">
                    <?= htmlspecialchars((string)$option['label']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="modalGoal_inline" class="form-label">Goal</label>
              <input type="text" class="form-control" id="modalGoal_inline" name="modalGoal" autocomplete="off">
            </div>
            <div class="col-md-6">
              <label for="modalSource_inline" class="form-label">Source</label>
              <input type="text" class="form-control" id="modalSource_inline" name="modalSource" autocomplete="url" inputmode="url" placeholder="https://example.com">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="modalMonth_inline" class="form-label">Month</label>
              <input type="text" class="form-control" id="modalMonth_inline" name="modalMonth" placeholder="e.g., July" autocomplete="off">
            </div>
            <div class="col-md-6">
              <label for="modalTimeOfDay_inline" class="form-label">Time of Day</label>
              <input type="text" class="form-control" id="modalTimeOfDay_inline" name="modalTimeOfDay" placeholder="e.g., Early / Late" autocomplete="off">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="skillPoints_inline" class="form-label">Total SP</label>
              <input type="number" class="form-control" id="skillPoints_inline" name="skillPoints" value="0" min="0" inputmode="numeric">
            </div>
            <div class="col-md-4 d-flex align-items-center">
              <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" id="acquireSkillSwitch_inline" name="acquireSkillSwitch" value="YES">
                <label class="form-check-label" for="acquireSkillSwitch_inline">Acquire Skill?</label>
              </div>
            </div>
            <div class="col-md-4 d-flex align-items-center">
              <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" id="raceDaySwitch_inline" name="raceDaySwitch">
                <label class="form-check-label" for="raceDaySwitch_inline">Race Day?</label>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-12">
              <label for="energyRange_inline" class="form-label">
                Energy (<span id="energyValue_inline">0</span>/100%)
              </label>
              <input type="range" class="form-range" min="0" max="100" id="energyRange_inline" name="energyRange" aria-describedby="energyValue_inline">
            </div>
          </div>
          <div class="row mb-3">
            <h6>Growth Rates (%)</h6>
            <div class="col">
              <label for="growthRateSpeed_inline" class="form-label">Speed</label>
              <input type="number" class="form-control" id="growthRateSpeed_inline" name="growthRateSpeed" value="0" min="0" inputmode="numeric">
            </div>
            <div class="col">
              <label for="growthRateStamina_inline" class="form-label">Stamina</label>
              <input type="number" class="form-control" id="growthRateStamina_inline" name="growthRateStamina" value="0" min="0" inputmode="numeric">
            </div>
            <div class="col">
              <label for="growthRatePower_inline" class="form-label">Power</label>
              <input type="number" class="form-control" id="growthRatePower_inline" name="growthRatePower" value="0" min="0" inputmode="numeric">
            </div>
            <div class="col">
              <label for="growthRateGuts_inline" class="form-label">Guts</label>
              <input type="number" class="form-control" id="growthRateGuts_inline" name="growthRateGuts" value="0" min="0" inputmode="numeric">
            </div>
            <div class="col">
              <label for="growthRateWit_inline" class="form-label">Wit</label>
              <input type="number" class="form-control" id="growthRateWit_inline" name="growthRateWit" value="0" min="0" inputmode="numeric">
            </div>
          </div>
        </div>

        <!-- Attributes Tab -->
        <div class="tab-pane fade" id="attributes-inline" role="tabpanel" aria-labelledby="attributes-tab-inline">
          <div id="attributeSlidersContainerInline">
            <!-- Dynamically populated by JS -->
          </div>
        </div>

        <!-- Grades Tab -->
        <div class="tab-pane fade" id="grades-inline" role="tabpanel" aria-labelledby="grades-tab-inline">
          <div class="row" id="aptitudeGradesContainerInline">
            <!-- Dynamically populated by JS -->
          </div>
        </div>

        <!-- Skills Tab -->
        <div class="tab-pane fade" id="skills-inline" role="tabpanel" aria-labelledby="skills-tab-inline">
          <div class="table-responsive">
            <table class="table table-sm" id="skillsTableInline">
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
          <button type="button" class="btn btn-uma w-100 mt-2" id="addSkillBtnInline">Add Skill</button>
        </div>

        <!-- Predictions Tab -->
        <div class="tab-pane fade" id="predictions-inline" role="tabpanel" aria-labelledby="predictions-tab-inline">
          <div class="table-responsive">
            <table class="table table-sm" id="predictionsTableInline">
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
          <button type="button" class="btn btn-uma w-100 mt-2" id="addPredictionBtnInline">Add Prediction</button>
        </div>

        <!-- Goals Tab -->
        <div class="tab-pane fade" id="goals-inline" role="tabpanel" aria-labelledby="goals-tab-inline">
          <div class="table-responsive">
            <table class="table table-sm" id="goalsTableInline">
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
          <button type="button" class="btn btn-uma w-100 mt-2" id="addGoalBtnInline">Add Goal</button>
        </div>

        <!-- Progress Chart Tab -->
        <div class="tab-pane fade" id="progress-chart-inline" role="tabpanel" aria-labelledby="progress-chart-tab-inline">
          <div class="chart-container" style="position: relative; height: 400px;">
            <canvas id="growthChartInline" role="img" aria-label="Plan stat progression chart"></canvas>
            <div id="growthChartMessageInline" class="text-center p-5 h-100 d-flex justify-content-center align-items-center" style="display: none;">
              <p class="text-muted fs-5 mb-0">No progression data available for this plan.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer actions -->
    <div class="card-footer d-flex justify-content-end gap-2">
      <button type="button" class="btn btn-outline-secondary" id="downloadTxtInline" aria-label="Export plan as text file">
        <i class="bi bi-file-earmark-text" aria-hidden="true"></i> Export as TXT
      </button>
      <button type="button" class="btn btn-info" id="exportPlanBtnInline" aria-label="Copy formatted plan to clipboard">Copy to Clipboard</button>
      <button type="submit" class="btn btn-uma">Save Changes</button>
    </div>
  </form>
</div>
<script src="<?= $baseEsc ?>assets/js/plan_inline_details.js" defer></script>
