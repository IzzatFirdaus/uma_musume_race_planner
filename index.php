<?php
// index.php
require_once __DIR__ . '/includes/logger.php'; // Include the logger

$pdo = require __DIR__ . '/includes/db.php'; // Use the new PDO connector
$log = $log ?? (require __DIR__ . '/includes/logger.php'); // Ensure logger is available for this file

// Define options arrays for PHP-side rendering
$predictionIcons = ['◎', '⦾', '○', '△', 'X', '-'];
$careerStageOptions = [
    ['value' => 'predebut', 'text' => 'Pre-Debut'],
    ['value' => 'junior', 'text' => 'Junior Year'],
    ['value' => 'classic', 'text' => 'Classic Year'],
    ['value' => 'senior', 'text' => 'Senior Year'],
    ['value' => 'finale', 'text' => 'Finale Season'],
];
$classOptions = [
    ['value' => 'debut', 'text' => 'Debut'],
    ['value' => 'maiden', 'text' => 'Maiden'],
    ['value' => 'beginner', 'text' => 'Beginner'],
    ['value' => 'bronze', 'text' => 'Bronze'],
    ['value' => 'silver', 'text' => 'Silver'],
    ['value' => 'gold', 'text' => 'Gold'],
    ['value' => 'platinum', 'text' => 'Platinum'],
    ['value' => 'star', 'text' => 'Star'],
    ['value' => 'legend', 'text' => 'Legend'],
];
$attributeGradeOptions = [
    'S+', 'S', 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'E+', 'E', 'F+', 'F', 'G+', 'G',
];
$timeOfDayOptions = ['Early', 'Late'];
$monthOptions = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
];


// Fetch dynamic options from the database using PDO with error handling
try {
    $strategyOptions = $pdo->query('SELECT id, label FROM strategies ORDER BY label')->fetchAll();
} catch (PDOException $e) {
    $log->error('Failed to fetch strategy options in index.php', ['message' => $e->getMessage()]);
    $strategyOptions = [];
}

try {
    $moodOptions = $pdo->query('SELECT id, label FROM moods')->fetchAll();
} catch (PDOException $e) {
    $log->error('Failed to fetch mood options in index.php', ['message' => $e->getMessage()]);
    $moodOptions = [];
}

try {
    $conditionOptions = $pdo->query('SELECT id, label FROM conditions')->fetchAll();
} catch (PDOException $e) {
    $log->error('Failed to fetch condition options in index.php', ['message' => $e->getMessage()]);
    $conditionOptions = [];
}

try {
    $skillTagOptions = $pdo->query('SELECT DISTINCT tag, stat_type FROM skill_reference ORDER BY tag')->fetchAll();
} catch (PDOException $e) {
    $log->error('Failed to fetch skill tag options in index.php', ['message' => $e->getMessage()]);
    $skillTagOptions = [];
}


// Fetch all plans for the initial server-side render of plan-list.php
$plans_query = '
    SELECT p.*, m.label AS mood, s.label AS strategy
    FROM plans p
    LEFT JOIN moods m ON p.mood_id = m.id
    LEFT JOIN strategies s ON p.strategy_id = s.id
    WHERE p.deleted_at IS NULL
    ORDER BY p.updated_at DESC
';
try {
    $plans = $pdo->query($plans_query);
} catch (PDOException $e) {
    $log->error('Failed to fetch plan list in index.php', ['message' => $e->getMessage()]);
    $plans = new PDOStatement();
}


// Count plans by status (only non-deleted ones)
$stats_query = "SELECT
    COUNT(*) AS total_plans,
    SUM(status = 'Active') AS active_plans,
    SUM(status = 'Planning') AS planning_plans,
    SUM(status = 'Finished') AS finished_plans,
    COUNT(DISTINCT name) AS unique_trainees
    FROM plans WHERE deleted_at IS NULL";
try {
    $stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);
    foreach ($stats as $key => $value) {
        $stats[$key] = (int)$value;
    }
} catch (PDOException $e) {
    $log->error('Failed to fetch stats in index.php', ['message' => $e->getMessage()]);
    $stats = ['total_plans' => 0, 'active_plans' => 0, 'planning_plans' => 0, 'finished_plans' => 0, 'unique_trainees' => 0];
}


// Fetch recent activities
try {
    $activities = $pdo->query('SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 3');
} catch (PDOException $e) {
    $log->error('Failed to fetch activities in index.php', ['message' => $e->getMessage()]);
    $activities = new PDOStatement();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Uma Musume Race Planner</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;700&display=swap" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_32.ico" sizes="32x32">
  <link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
  <link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
  <link rel="apple-touch-icon" href="uploads/app_logo/uma_musume_race_planner_logo_256.png">

  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php require_once __DIR__ . '/components/navbar.php'; ?>

  <div class="container">
    <div class="header-banner rounded-3 text-center mb-4">
      <div class="container">
        <h1 class="display-4 fw-bold">
          <img src="uploads/app_logo/uma_musume_race_planner_logo_128.png" alt="Uma Musume Race Planner Logo" class="logo">
          Uma Musume Race Planner
        </h1>
        <p class="lead">Plan, track, and optimize your umamusume's racing career</p>
      </div>
    </div>

    <div id="mainContent" class="row">
      <div class="col-lg-8">
        <?php include __DIR__ . '/components/plan-list.php'; ?>
      </div>
      <div class="col-lg-4">
        <?php include __DIR__ . '/components/stats-panel.php'; ?>
        <?php include __DIR__ . '/components/recent-activity.php'; ?>
      </div>
    </div>

    <?php require_once __DIR__ . '/components/plan-inline-details.php'; ?>

  </div>

  <?php require_once __DIR__ . '/quick_create_plan_modal.php'; ?>
  <?php require_once __DIR__ . '/plan_details_modal.php'; ?>

  <?php require_once __DIR__ . '/components/footer.php'; ?>

  <div class="modal fade" id="messageBoxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center alert alert-success mb-0" id="messageBoxBody"></div>
      </div>
    </div>
  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <?php require_once __DIR__ . '/components/copy_to_clipboard.php'; ?>
  <script src="js/autosuggest.js"></script>

<script>
    // --- V3.0: GLOBAL HELPERS AND VARIABLES ---
    let messageBoxModalInstance;

    const statIcons = {
        speed:   { class: 'bi bi-lightning-charge-fill', colorClass: 'text-speed-blue' },
        stamina: { class: 'bi bi-heart-fill', colorClass: 'text-stamina-red' },
        power:   { class: 'bi bi-arm-flex', colorClass: 'text-power-orange' },
        guts:    { class: 'bi bi-fire', colorClass: 'text-guts-magenta' },
        wit:     { class: 'bi bi-mortarboard-fill', colorClass: 'text-wit-green' }
    };

    function showMessageBox(message, type = 'success') {
        if (!messageBoxModalInstance) return;
        const messageBoxBody = document.getElementById('messageBoxBody');
        messageBoxBody.textContent = message;
        messageBoxBody.className = `modal-body text-center alert alert-${type} mb-0`;
        messageBoxModalInstance.show();
        setTimeout(() => messageBoxModalInstance.hide(), 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
      // --- MODAL AND GLOBAL ELEMENT INITIALIZATION ---
      const body = document.body;
      const darkModeToggle = document.getElementById('darkModeToggle');
      const planDetailsModalElement = document.getElementById('planDetailsModal');
      const planDetailsModal = new bootstrap.Modal(planDetailsModalElement);
      const quickCreateModalElement = document.getElementById('createPlanModal');
      const quickCreateModal = new bootstrap.Modal(quickCreateModalElement);
      
      messageBoxModalInstance = new bootstrap.Modal(document.getElementById('messageBoxModal'));

      const mainContentDiv = document.getElementById('mainContent');
      const planInlineDetailsDiv = document.getElementById('planInlineDetails');
      const closeInlineDetailsBtn = document.getElementById('closeInlineDetailsBtn');
      const planInlineDetailsLoadingOverlay = document.getElementById('planInlineDetailsLoadingOverlay');

      let currentModalPlanData = {};
      let currentInlinePlanData = {};


      // --- DARK MODE LOGIC ---
      function setDarkMode(isDarkMode) {
        body.classList.toggle('dark-mode', isDarkMode);
        localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
        if (darkModeToggle) darkModeToggle.checked = isDarkMode;
      }

      const savedDarkMode = localStorage.getItem('darkMode');
      if (savedDarkMode === 'enabled') { setDarkMode(true); } 
      else if (savedDarkMode === 'disabled') { setDarkMode(false); } 
      else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) { setDarkMode(true); }

      if (darkModeToggle) {
          darkModeToggle.addEventListener('change', () => setDarkMode(darkModeToggle.checked));
      }
      
      // --- DYNAMIC ROW/ELEMENT CREATION HELPERS ---
      function createModalSkillRow(skill = {}) {
        const row = document.createElement('tr');
        const skillTagOptions = <?php echo json_encode($skillTagOptions); ?>;
        row.innerHTML = `
            <td class="autocomplete-container"><input type="text" class="form-control form-control-sm skill-name-input" value="${skill.skill_name || ''}"></td>
            <td><input type="number" class="form-control form-control-sm skill-sp-cost-input" value="${skill.sp_cost || 0}" min="0"></td>
            <td class="text-center"><input type="checkbox" class="form-check-input skill-acquired-checkbox" ${skill.acquired === 'yes' ? 'checked' : ''}></td>
            <td>
                <select class="form-select form-select-sm skill-tag-select">
                    <option value="">Select Tag</option>
                    ${skillTagOptions.map(opt => `<option value="${opt.tag}" ${opt.tag === skill.tag ? 'selected' : ''}>${opt.tag}</option>`).join('')}
                </select>
            </td>
            <td><input type="text" class="form-control form-control-sm skill-notes-input" value="${skill.notes || ''}"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-skill-btn"><i class="bi bi-x-circle"></i></button></td>
        `;
        return row;
      }
      
      function createModalPredictionRow(prediction = {}) {
          const row = document.createElement('tr');
          const predictionIcons = <?php echo json_encode($predictionIcons); ?>;
          const createSelect = (name, selectedValue) => `<select class="form-select form-select-sm prediction-${name}-input">${predictionIcons.map(icon => `<option value="${icon}" ${icon === selectedValue ? 'selected' : ''}>${icon}</option>`).join('')}</select>`;
          row.innerHTML = `
              <td><input type="text" class="form-control form-control-sm" value="${prediction.race_name || ''}"></td>
              <td><input type="text" class="form-control form-control-sm" value="${prediction.venue || ''}"></td>
              <td><input type="text" class="form-control form-control-sm" value="${prediction.ground || ''}"></td>
              <td><input type="text" class="form-control form-control-sm" value="${prediction.distance || ''}"></td>
              <td><input type="text" class="form-control form-control-sm" value="${prediction.track_condition || ''}"></td>
              <td><input type="text" class="form-control form-control-sm" value="${prediction.direction || ''}"></td>
              <td>${createSelect('speed', prediction.speed)}</td>
              <td>${createSelect('stamina', prediction.stamina)}</td>
              <td>${createSelect('power', prediction.power)}</td>
              <td>${createSelect('guts', prediction.guts)}</td>
              <td>${createSelect('wit', prediction.wit)}</td>
              <td><input type="text" class="form-control form-control-sm" value="${prediction.comment || ''}"></td>
              <td><button type="button" class="btn btn-danger btn-sm remove-prediction-btn"><i class="bi bi-x-circle"></i></button></td>
          `;
        return row;
      }

      function createModalGoalRow(goal = {}) {
          const row = document.createElement('tr');
          row.dataset.id = goal.id || '';
          row.innerHTML = `
              <td><input type="text" class="form-control form-control-sm goal-input" value="${goal.goal || ''}"></td>
              <td><input type="text" class="form-control form-control-sm result-input" value="${goal.result || ''}"></td>
              <td><button type="button" class="btn btn-danger btn-sm remove-goal-btn"><i class="bi bi-x-circle"></i></button></td>
          `;
          return row;
      }
      
      // --- RENDER FUNCTIONS ---
    function renderModalAttributes(attributes, isInline) {
        const containerId = isInline ? 'attributeSlidersContainerInline' : 'attributeSlidersContainer';
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';
        const defaultAttributes = ['speed', 'stamina', 'power', 'guts', 'wit'];

        defaultAttributes.forEach(attrName => {
            const attr = attributes.find(a => a.attribute_name.toLowerCase() === attrName) || { value: 0 };
            const div = document.createElement('div');
            div.className = 'col-6 col-md-4 col-lg-2 mb-3';

            const iconInfo = statIcons[attrName];

            div.innerHTML = `
                <div class="d-flex align-items-center mb-1">
                    <i class="${iconInfo.class} stat-icon ${iconInfo.colorClass} me-2"></i>
                    <label class="form-label mb-0">${attrName.charAt(0).toUpperCase() + attrName.slice(1)}</label>
                </div>
                <input type="number" class="form-control" min="0" max="1200" value="${attr.value}" data-stat="${attrName}">
            `;
            container.appendChild(div);
        });
        container.classList.add('row', 'g-3', 'justify-content-center');
    }

      function renderModalSkills(skills, isInline) {
        const tableId = isInline ? 'skillsTableInline' : 'skillsTable';
        const skillsTableBody = document.getElementById(tableId).querySelector('tbody');
        skillsTableBody.innerHTML = '';
        skills.forEach(skill => skillsTableBody.appendChild(createModalSkillRow(skill)));

        skillsTableBody.querySelectorAll('.skill-name-input').forEach(input => {
            attachAutosuggest(input, 'skill_name', function(skill) {
                const parentRow = input.closest('tr');
                if (parentRow) {
                    let infoBox = parentRow.nextElementSibling;
                    if (!infoBox || !infoBox.classList.contains('skill-context-row')) {
                        infoBox = document.createElement('tr');
                        infoBox.className = 'skill-context-row';
                        infoBox.innerHTML = `<td colspan="6" class="skill-context-info"></td>`;
                        parentRow.after(infoBox);
                    }
                    infoBox.querySelector('.skill-context-info').textContent = skill.description || 'No description available.';
                }
            });
        });
      }

      function renderModalPredictions(predictions, isInline) {
        const tableId = isInline ? 'predictionsTableInline' : 'predictionsTable';
        const predictionsTableBody = document.getElementById(tableId).querySelector('tbody');
        predictionsTableBody.innerHTML = '';
        predictions.forEach(prediction => predictionsTableBody.appendChild(createModalPredictionRow(prediction)));
      }

      function renderModalGoals(goals, isInline) {
          const tableId = isInline ? 'goalsTableInline' : 'goalsTable';
          const goalsTableBody = document.getElementById(tableId).querySelector('tbody');
          goalsTableBody.innerHTML = '';
          goals.forEach(goal => goalsTableBody.appendChild(createModalGoalRow(goal)));
      }

      function renderAptitudeGrades(gradesData, isInline) {
        const containerId = isInline ? 'aptitudeGradesContainerInline' : 'aptitudeGradesContainer';
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = '';
        container.classList.add('row', 'g-3');

        const gradeTypes = [
            { title: 'Terrain', data: gradesData.terrain_grades || [], key: 'terrain' },
            { title: 'Distance', data: gradesData.distance_grades || [], key: 'distance' },
            { title: 'Style', data: gradesData.style_grades || [], key: 'style' }
        ];

        const gradeOptions = <?php echo json_encode($attributeGradeOptions); ?>;
        
        gradeTypes.forEach(type => {
            const col = document.createElement('div');
            col.className = 'col-md-4';
            let content = `<h5 class="mt-2">${type.title}</h5>`;
            
            const gradeMap = new Map(type.data.map(item => [item[type.key], item.grade]));

            const defaultGrades = {
                terrain: ['Turf', 'Dirt'],
                distance: ['Sprint', 'Mile', 'Medium', 'Long'],
                style: ['Front', 'Pace', 'Late', 'End']
            };

            defaultGrades[type.key].forEach(itemKey => {
                const currentGrade = gradeMap.get(itemKey) || 'G';
                const optionsHtml = gradeOptions.map(grade => `<option value="${grade}" ${grade === currentGrade ? 'selected' : ''}>${grade}</option>`).join('');
                content += `
                    <div class="mb-2 row align-items-center">
                        <label class="col-sm-4 col-form-label">${itemKey}</label>
                        <div class="col-sm-8">
                            <select class="form-select form-select-sm aptitude-grade-select" data-grade-type="${type.key}" data-item-key="${itemKey}">${optionsHtml}</select>
                        </div>
                    </div>
                `;
            });
            col.innerHTML = content;
            container.appendChild(col);
        });
      }

      // --- DYNAMIC UI UPDATE FUNCTIONS ---
      function updateStats() {
          fetch('get_stats.php')
              .then(response => response.json())
              .then(data => {
                  if (data.success && data.stats) {
                      document.getElementById('statsPlans').textContent = data.stats.total_plans;
                      document.getElementById('statsActive').textContent = data.stats.active_plans;
                      document.getElementById('statsFinished').textContent = data.stats.finished_plans;
                  } else {
                      throw new Error(data.error || 'Failed to fetch stats.');
                  }
              })
              .catch(error => showMessageBox(`Error updating stats: ${error.message}`, 'danger'));
      }

      function updateRecentActivity() {
          fetch('get_activities.php')
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      const recentActivityBody = document.getElementById('recentActivity');
                      recentActivityBody.innerHTML = '';
                      if (data.activities && data.activities.length > 0) {
                          const ul = document.createElement('ul');
                          ul.className = 'list-group list-group-flush';
                          data.activities.forEach(activity => {
                              const li = document.createElement('li');
                              li.className = 'list-group-item d-flex align-items-center';
                              const timestamp = new Date(activity.timestamp).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                              li.innerHTML = `<i class="bi ${activity.icon_class || 'bi-info-circle'} me-2"></i> ${activity.description} <small class="text-muted ms-auto">${timestamp}</small>`;
                              ul.appendChild(li);
                          });
                          recentActivityBody.appendChild(ul);
                      } else {
                          recentActivityBody.innerHTML = '<ul class="list-group list-group-flush"><li class="list-group-item text-muted text-center">No recent activity.</li></ul>';
                      }
                  } else {
                      throw new Error(data.error || 'Failed to fetch activities.');
                  }
              })
              .catch(error => showMessageBox(`Error updating recent activity: ${error.message}`, 'danger'));
      }

      // --- MAIN EVENT LISTENERS (DELEGATED) ---
      document.addEventListener('click', async function(event) {
        const target = event.target;
        const editBtn = target.closest('.edit-btn');
        const deleteBtn = target.closest('.delete-btn');
        const newPlanBtn = target.closest('#newPlanBtn, #createPlanBtn');
        const viewInlineBtn = target.closest('.view-inline-btn');
        const exportPlanModalBtn = target.closest('#exportPlanBtn');
        const exportPlanInlineBtn = target.closest('#exportPlanBtnInline');

        // *** UPDATED fetchAndPopulatePlan FUNCTION ***
        async function fetchAndPopulatePlan(planId, isInlineView) {
            const loadingOverlay = isInlineView ? planInlineDetailsLoadingOverlay : document.getElementById('planDetailsLoadingOverlay');
            const formElement = isInlineView ? document.getElementById('planDetailsFormInline') : document.getElementById('planDetailsForm');
            const planDetailsLabel = isInlineView ? document.getElementById('planInlineDetailsLabel') : document.getElementById('planDetailsModalLabel');
            
            loadingOverlay.style.display = 'flex';
            formElement.reset();
            
            // Reset tabs to the first one
            const tabsContainerId = isInlineView ? 'planTabsInline' : 'planTabs';
            document.querySelectorAll(`#${tabsContainerId} .nav-link`).forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll(`#${tabsContainerId} ~ .tab-content .tab-pane`).forEach(pane => pane.classList.remove('show', 'active'));
            const generalTabBtn = isInlineView ? document.getElementById('general-tab-inline') : document.getElementById('general-tab');
            generalTabBtn.classList.add('active');
            const generalTabPane = isInlineView ? document.getElementById('general-inline') : document.getElementById('general');
            generalTabPane.classList.add('show', 'active');

            try {
                // Fetch all data concurrently
                const responses = await Promise.all([
                    fetch(`fetch_plan_details.php?id=${planId}`).then(res => res.json()),
                    fetch(`get_plan_attributes.php?id=${planId}`).then(res => res.json()),
                    fetch(`get_plan_skills.php?id=${planId}`).then(res => res.json()),
                    fetch(`get_plan_predictions.php?id=${planId}`).then(res => res.json()),
                    fetch(`get_plan_goals.php?id=${planId}`).then(res => res.json()),
                    fetch(`get_plan_terrain_grades.php?id=${planId}`).then(res => res.json()),
                    fetch(`get_plan_distance_grades.php?id=${planId}`).then(res => res.json()),
                    fetch(`get_plan_style_grades.php?id=${planId}`).then(res => res.json()),
                    fetch(`get_plan_turns.php?id=${planId}`).then(res => res.json())
                ]);

                const allData = {};
                responses.forEach(res => {
                    if (res.error) throw new Error(res.error);
                    Object.assign(allData, res);
                });
                
                if (isInlineView) { currentInlinePlanData = allData; } 
                else { currentModalPlanData = allData; }

                const data = allData.plan;
                const suffix = isInlineView ? '_inline' : '';
                
                // Populate all form fields
                document.getElementById(`planId${isInlineView ? 'Inline' : ''}`).value = data.id || '';
                planDetailsLabel.textContent = `Plan Details: ${data.plan_title || 'Untitled'}`;
                const planTitleEl = document.getElementById(`plan_title${suffix}`);
                if (planTitleEl) planTitleEl.value = data.plan_title || '';
                document.getElementById(`modalName${suffix}`).value = data.name || '';
                document.getElementById(`modalCareerStage${suffix}`).value = data.career_stage || '';
                document.getElementById(`modalClass${suffix}`).value = data.class || '';
                document.getElementById(`modalRaceName${suffix}`).value = data.race_name || '';
                document.getElementById(`modalTurnBefore${suffix}`).value = data.turn_before || 0;
                document.getElementById(`modalGoal${suffix}`).value = data.goal || '';
                document.getElementById(`modalStrategy${suffix}`).value = data.strategy_id || '';
                document.getElementById(`modalMood${suffix}`).value = data.mood_id || '';
                document.getElementById(`modalCondition${suffix}`).value = data.condition_id || '';
                const energyRange = document.getElementById(`energyRange${suffix}`);
                energyRange.value = data.energy || 0;
                document.getElementById(`energyValue${suffix}`).textContent = energyRange.value;
                document.getElementById(`raceDaySwitch${suffix}`).checked = data.race_day === 'yes';
                document.getElementById(`acquireSkillSwitch${suffix}`).checked = data.acquire_skill === 'YES';
                document.getElementById(`skillPoints${suffix}`).value = data.total_available_skill_points || 0;
                document.getElementById(`modalStatus${suffix}`).value = data.status || 'Planning';
                document.getElementById(`modalTimeOfDay${suffix}`).value = data.time_of_day || '';
                document.getElementById(`modalMonth${suffix}`).value = data.month || '';
                document.getElementById(`modalSource${suffix}`).value = data.source || '';
                document.getElementById(`growthRateSpeed${suffix}`).value = data.growth_rate_speed || 0;
                document.getElementById(`growthRateStamina${suffix}`).value = data.growth_rate_stamina || 0;
                document.getElementById(`growthRatePower${suffix}`).value = data.growth_rate_power || 0;
                document.getElementById(`growthRateGuts${suffix}`).value = data.growth_rate_guts || 0;
                document.getElementById(`growthRateWit${suffix}`).value = data.growth_rate_wit || 0;
                
                // Render related data tables/sections
                const gradesData = {
                    terrain_grades: allData.terrain_grades,
                    distance_grades: allData.distance_grades,
                    style_grades: allData.style_grades,
                };
                renderModalAttributes(allData.attributes || [], isInlineView);
                renderAptitudeGrades(gradesData, isInlineView);
                renderModalSkills(allData.skills || [], isInlineView);
                renderModalPredictions(allData.predictions || [], isInlineView);
                renderModalGoals(allData.goals || [], isInlineView);

                // *** NEW: Directly update the image handler UI ***
                const previewImg = document.getElementById(`traineeImagePreview${suffix}`);
                const previewContainer = document.getElementById(`traineeImagePreviewContainer${suffix}`);
                const clearBtn = document.getElementById(`clearTraineeImageBtn${suffix}`);
                const existingPathInput = document.getElementById(`existingTraineeImagePath${suffix}`);
                const clearFlagInput = document.getElementById(`clearTraineeImageFlag${suffix}`);
                const uploadInput = document.getElementById(`traineeImageUpload${suffix}`);

                if (data.trainee_image_path) {
                    previewImg.src = data.trainee_image_path;
                    previewContainer.style.display = 'block';
                    clearBtn.style.display = 'inline-block';
                    existingPathInput.value = data.trainee_image_path;
                } else {
                    previewContainer.style.display = 'none';
                    clearBtn.style.display = 'none';
                    existingPathInput.value = '';
                }
                clearFlagInput.value = '0';
                uploadInput.value = '';

                if (isInlineView) {
                    mainContentDiv.style.display = 'none';
                    planInlineDetailsDiv.style.display = 'block';
                } else {
                    planDetailsModal.show();
                }
            } catch (error) {
                showMessageBox(`Error fetching plan details: ${error.message}`, 'danger');
            } finally {
                loadingOverlay.style.display = 'none';
            }
        }
        
        if (editBtn) { await fetchAndPopulatePlan(editBtn.dataset.id, false); }
        if (viewInlineBtn) { await fetchAndPopulatePlan(viewInlineBtn.dataset.id, true); }

        if (deleteBtn) {
          const planId = deleteBtn.dataset.id;
          if (confirm('Are you sure you want to delete this plan?')) {
            const formData = new FormData();
            formData.append('delete_id', planId);
            fetch('handle_plan_crud.php', { method: 'POST', body: formData })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                    document.dispatchEvent(new CustomEvent('planUpdated'));
                    showMessageBox('Plan deleted successfully!');
                } else { throw new Error(data.error || 'Failed to delete plan.'); }
              })
              .catch(error => showMessageBox(`Error: ${error.message}`, 'danger'));
          }
        }

        if (newPlanBtn) {
          document.getElementById('quickCreatePlanForm').reset();
          quickCreateModal.show();
        }

        if (exportPlanModalBtn) {
            if (Object.keys(currentModalPlanData).length > 0) {
                copyPlanDetailsToClipboard(currentModalPlanData);
            } else {
                showMessageBox('No plan data loaded in the modal to export.', 'warning');
            }
        }

        if (exportPlanInlineBtn) {
            if (Object.keys(currentInlinePlanData).length > 0) {
                copyPlanDetailsToClipboard(currentInlinePlanData);
            } else {
                showMessageBox('No plan data loaded to export.', 'warning');
            }
        }

        const addSkillBtn = target.closest('#addSkillBtn, #addSkillBtnInline');
        if (addSkillBtn) {
            const tableId = addSkillBtn.id.includes('Inline') ? 'skillsTableInline' : 'skillsTable';
            const skillsTableBody = document.querySelector(`#${tableId} tbody`);
            const newRow = createModalSkillRow();
            skillsTableBody.appendChild(newRow);
            const newSkillInput = newRow.querySelector('.skill-name-input');
            if (newSkillInput) {
                attachAutosuggest(newSkillInput, 'skill_name', function(skill) {
                    const parentRow = newSkillInput.closest('tr');
                    if (parentRow) {
                        let infoBox = parentRow.nextElementSibling;
                        if (!infoBox || !infoBox.classList.contains('skill-context-row')) {
                            infoBox = document.createElement('tr');
                            infoBox.className = 'skill-context-row';
                            infoBox.innerHTML = `<td colspan="6" class="skill-context-info"></td>`;
                            parentRow.after(infoBox);
                        }
                        infoBox.querySelector('.skill-context-info').textContent = skill.description || 'No description available.';
                    }
                });
            }
        }

        const removeBtn = target.closest('.remove-skill-btn, .remove-prediction-btn, .remove-goal-btn');
        if (removeBtn) {
            const parentTr = removeBtn.closest('tr');
            if (parentTr && parentTr.querySelector('.skill-name-input')) {
                const nextSibling = parentTr.nextElementSibling;
                if (nextSibling && nextSibling.classList.contains('skill-context-row')) {
                    nextSibling.remove();
                }
            }
            parentTr.remove();
        }

        const addPredictionBtn = target.closest('#addPredictionBtn, #addPredictionBtnInline');
        if (addPredictionBtn) {
            const tableId = addPredictionBtn.id.includes('Inline') ? 'predictionsTableInline' : 'predictionsTable';
            document.querySelector(`#${tableId} tbody`).appendChild(createModalPredictionRow());
        }

        const addGoalBtn = target.closest('#addGoalBtn, #addGoalBtnInline');
        if (addGoalBtn) {
            const tableId = addGoalBtn.id.includes('Inline') ? 'goalsTableInline' : 'goalsTable';
            document.querySelector(`#${tableId} tbody`).appendChild(createModalGoalRow());
        }
      });

      closeInlineDetailsBtn.addEventListener('click', () => {
          planInlineDetailsDiv.style.display = 'none';
          mainContentDiv.style.display = 'flex';
      });

      // --- INITIAL AUTOSUGGEST ATTACHMENTS ---
      attachAutosuggest(document.getElementById('modalName'), 'name');
      attachAutosuggest(document.getElementById('modalName_inline'), 'name');
      attachAutosuggest(document.getElementById('modalRaceName'), 'race_name');
      attachAutosuggest(document.getElementById('modalRaceName_inline'), 'race_name');
      attachAutosuggest(document.getElementById('modalGoal'), 'goal');
      attachAutosuggest(document.getElementById('modalGoal_inline'), 'goal');

      // --- AJAX FORM SUBMISSION HANDLER ---
      async function handleFormSubmit(formElement) {
          const isInline = formElement.id.includes('Inline');
          const loadingOverlay = isInline ? planInlineDetailsLoadingOverlay : document.getElementById('planDetailsLoadingOverlay');
          loadingOverlay.style.display = 'flex';
          try {
              const formData = new FormData(formElement);
              const currentData = isInline ? currentInlinePlanData : currentModalPlanData;
              const gatherDataForSubmission = (containerSelector, dataExtractor) => {
                  const items = [];
                  document.querySelectorAll(containerSelector).forEach(row => {
                      const item = dataExtractor(row);
                      if (item) items.push(item);
                  });
                  return items;
              };
              const attributesData = gatherDataForSubmission(`#attributeSlidersContainer${isInline ? 'Inline' : ''} input[type="number"]`, el => ({
                  attribute_name: el.dataset.stat.toUpperCase(), value: el.value, grade: 'G'
              }));
              formData.append('attributes', JSON.stringify(attributesData));
              const terrainGrades = gatherDataForSubmission(`#aptitudeGradesContainer${isInline ? 'Inline' : ''} select[data-grade-type="terrain"]`, el => ({ terrain: el.dataset.itemKey, grade: el.value }));
              formData.append('terrainGrades', JSON.stringify(terrainGrades));
              const distanceGrades = gatherDataForSubmission(`#aptitudeGradesContainer${isInline ? 'Inline' : ''} select[data-grade-type="distance"]`, el => ({ distance: el.dataset.itemKey, grade: el.value }));
              formData.append('distanceGrades', JSON.stringify(distanceGrades));
              const styleGrades = gatherDataForSubmission(`#aptitudeGradesContainer${isInline ? 'Inline' : ''} select[data-grade-type="style"]`, el => ({ style: el.dataset.itemKey, grade: el.value }));
              formData.append('styleGrades', JSON.stringify(styleGrades));
              const skillsData = gatherDataForSubmission(`#skillsTable${isInline ? 'Inline' : ''} tbody tr`, tr => {
                  if (tr.classList.contains('skill-context-row')) return null;
                  return {
                      skill_name: tr.querySelector('.skill-name-input')?.value.trim(),
                      sp_cost: tr.querySelector('.skill-sp-cost-input')?.value,
                      acquired: tr.querySelector('.skill-acquired-checkbox')?.checked ? 'yes' : 'no',
                      tag: tr.querySelector('.skill-tag-select')?.value,
                      notes: tr.querySelector('.skill-notes-input')?.value.trim()
                  };
              });
              formData.append('skills', JSON.stringify(skillsData));
              const predictionsData = gatherDataForSubmission(`#predictionsTable${isInline ? 'Inline' : ''} tbody tr`, tr => ({
                  race_name: tr.cells[0].querySelector('input').value.trim(), venue: tr.cells[1].querySelector('input').value.trim(), ground: tr.cells[2].querySelector('input').value.trim(), distance: tr.cells[3].querySelector('input').value.trim(), track_condition: tr.cells[4].querySelector('input').value.trim(), direction: tr.cells[5].querySelector('input').value.trim(), speed: tr.cells[6].querySelector('select').value, stamina: tr.cells[7].querySelector('select').value, power: tr.cells[8].querySelector('select').value, guts: tr.cells[9].querySelector('select').value, wit: tr.cells[10].querySelector('select').value, comment: tr.cells[11].querySelector('input').value.trim()
              }));
              formData.append('predictions', JSON.stringify(predictionsData));
              const goalsData = gatherDataForSubmission(`#goalsTable${isInline ? 'Inline' : ''} tbody tr`, tr => ({
                  goal: tr.querySelector('.goal-input')?.value.trim(), result: tr.querySelector('.result-input')?.value.trim()
              }));
              formData.append('goals', JSON.stringify(goalsData));
              formData.append('turns', JSON.stringify(currentData.turns || []));
              const response = await fetch('handle_plan_crud.php', { method: 'POST', body: formData });
              const result = await response.json();
              if (result.success) {
                  if (result.debug_output) {
                      console.warn("Server produced debug output:", result.debug_output);
                  }
                  showMessageBox('Plan saved successfully!');
                  document.dispatchEvent(new CustomEvent('planUpdated'));
                  if (isInline) {
                      closeInlineDetailsBtn.click();
                  } else {
                      planDetailsModal.hide();
                  }
              } else {
                  error.result = result;
                  throw new Error(result.error || 'An unknown error occurred.');
              }
          } catch (error) {
              console.error("An error occurred. Full server response:", error.result || error);
              showMessageBox(`Error saving plan: ${error.message}`, 'danger');
          } finally {
              loadingOverlay.style.display = 'none';
          }
      }
      
      document.getElementById('planDetailsForm').addEventListener('submit', function(event) {
          event.preventDefault();
          handleFormSubmit(this);
      });
      document.getElementById('planDetailsFormInline').addEventListener('submit', function(event) {
          event.preventDefault();
          handleFormSubmit(this);
      });

      // --- UI REFRESH LISTENER ---
      document.addEventListener('planUpdated', function() {
          fetch('components/plan-list.php')
              .then(response => response.text())
              .then(html => {
                  const planListContainer = document.getElementById('planListContainer');
                  if(planListContainer) planListContainer.innerHTML = html;
              })
              .catch(error => console.error('Failed to refresh plan list:', error));
          updateStats();
          updateRecentActivity();
      });

      // Initial load
      updateStats();
      updateRecentActivity();
    });
</script>
</body>
</html>
