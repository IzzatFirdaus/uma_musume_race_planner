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
    $moodOptions = $pdo->query('SELECT id, label FROM moods ORDER BY label')->fetchAll();
} catch (PDOException $e) {
    $log->error('Failed to fetch mood options in index.php', ['message' => $e->getMessage()]);
    $moodOptions = [];
}

try {
    $conditionOptions = $pdo->query('SELECT id, label FROM conditions ORDER BY label')->fetchAll();
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


// Fetch all plans with mood and strategy names (only non-deleted ones)
$plans_query = '
    SELECT p.*, m.label AS mood, s.label AS strategy
    FROM plans p
    LEFT JOIN moods m ON p.mood_id = m.id
    LEFT JOIN strategies s ON p.strategy_id = s.id
    WHERE p.deleted_at IS NULL
    ORDER BY p.updated_at DESC
';
try {
    $plans = $pdo->query($plans_query); // PDO query returns a statement object to iterate over
} catch (PDOException $e) {
    $log->error('Failed to fetch plan list in index.php', ['message' => $e->getMessage()]);
    $plans = new PDOStatement(); // Create a dummy empty statement to prevent errors in plan-list.php
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
    $stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC); // fetch() is used for a single row
    // Ensure all stats are integers
    foreach ($stats as $key => $value) {
        $stats[$key] = (int)$value;
    }
} catch (PDOException $e) {
    $log->error('Failed to fetch stats in index.php', ['message' => $e->getMessage()]);
    $stats = ['total_plans' => 0, 'active_plans' => 0, 'planning_plans' => 0, 'finished_plans' => 0, 'unique_trainees' => 0]; // Default values
}


// Fetch recent activities
try {
    $activities = $pdo->query('SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 3');
} catch (PDOException $e) {
    $log->error('Failed to fetch activities in index.php', ['message' => $e->getMessage()]);
    $activities = new PDOStatement(); // Create a dummy empty statement
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
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php require_once __DIR__ . '/components/navbar.php'; ?>

  <div class="container">
    <div class="header-banner rounded-3 text-center mb-4">
      <div class="container">
        <h1 class="display-4 fw-bold"><i class="bi bi-speedometer2"></i> Uma Musume Race Planner</h1>
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
  <script>
    // --- GLOBAL HELPERS AND VARIABLES ---
    let messageBoxModalInstance;

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
            <td><input type="text" class="form-control form-control-sm skill-name-input" value="${skill.skill_name || ''}"></td>
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
        const gridId = isInline ? 'attributeGridInline' : 'attributeGrid';
        const attributeGrid = document.getElementById(gridId);
        attributeGrid.innerHTML = '';
        const grades = <?php echo json_encode($attributeGradeOptions); ?>;
        attributes.forEach(attr => {
          const div = document.createElement('div');
          div.className = 'col-md-4';
          const optionsHtml = grades.map(grade => `<option value="${grade}" ${grade === attr.grade ? 'selected' : ''}>${grade}</option>`).join('');
          div.innerHTML = `
            <label class="form-label" for="attr-${attr.attribute_name}${isInline ? '-inline' : ''}">${attr.attribute_name}</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control attribute-value-input" id="attr-${attr.attribute_name}${isInline ? '-inline' : ''}" data-attribute-name="${attr.attribute_name}" value="${attr.value}" min="0" max="1200">
                <select class="form-select attribute-grade-input" data-attribute-name="${attr.attribute_name}">${optionsHtml}</select>
            </div>`;
          attributeGrid.appendChild(div);
        });
      }

      function renderModalSkills(skills, isInline) {
        const tableId = isInline ? 'skillsTableInline' : 'skillsTable';
        const skillsTableBody = document.getElementById(tableId).querySelector('tbody');
        skillsTableBody.innerHTML = '';
        skills.forEach(skill => skillsTableBody.appendChild(createModalSkillRow(skill)));
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
        container.innerHTML = '';

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
      function updatePlanList() {
          fetch('get_plans.php')
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      const planListBody = document.getElementById('planListBody');
                      planListBody.innerHTML = ''; 
                      if (data.plans.length > 0) {
                          data.plans.forEach(plan => {
                              const row = document.createElement('tr');
                              let statusClass = '';
                              switch(plan.status) {
                                  case 'Planning': statusClass = 'bg-planning'; break;
                                  case 'Active': statusClass = 'bg-active'; break;
                                  case 'Finished': statusClass = 'bg-finished'; break;
                                  case 'Draft': statusClass = 'bg-draft'; break;
                                  case 'Abandoned': statusClass = 'bg-abandoned'; break;
                                  default: statusClass = 'bg-secondary'; break;
                              }
                              row.innerHTML = `
                                  <td><strong>${plan.plan_title || 'Untitled Plan'}</strong><br><small class="text-muted">${plan.name}</small></td>
                                  <td>${plan.career_stage ? plan.career_stage.charAt(0).toUpperCase() + plan.career_stage.slice(1) : ''}</td>
                                  <td>${plan.class ? plan.class.charAt(0).toUpperCase() + plan.class.slice(1) : ''}</td>
                                  <td>${plan.race_name || ''}</td>
                                  <td><span class="badge ${statusClass} rounded-pill">${plan.status || ''}</span></td>
                                  <td>
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${plan.id}"><i class="bi bi-pencil-square"></i> Edit</button>
                                    <button class="btn btn-sm btn-outline-info view-inline-btn me-1" data-id="${plan.id}"><i class="bi bi-eye"></i> View Details</button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${plan.id}"><i class="bi bi-trash"></i></button>
                                  </td>
                              `;
                              planListBody.appendChild(row);
                          });
                      } else {
                          planListBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted p-4">No plans found. Click "Create New" to get started!</td></tr>`;
                      }
                      updateStats(); 
                      updateRecentActivity();
                  } else {
                      throw new Error(data.error || 'Failed to fetch plans.');
                  }
              })
              .catch(error => showMessageBox(`Error updating plan list: ${error.message}`, 'danger'));
      }

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
      document.addEventListener('click', function(event) {
        const target = event.target;
        const editBtn = target.closest('.edit-btn');
        const deleteBtn = target.closest('.delete-btn');
        const newPlanBtn = target.closest('#newPlanBtn, #createPlanBtn');
        const viewInlineBtn = target.closest('.view-inline-btn');

        async function fetchAndPopulatePlan(planId, isInlineView) {
            const loadingOverlay = isInlineView ? planInlineDetailsLoadingOverlay : document.getElementById('planDetailsLoadingOverlay');
            const formElement = isInlineView ? document.getElementById('planDetailsFormInline') : document.getElementById('planDetailsForm');
            const planDetailsLabel = isInlineView ? document.getElementById('planInlineDetailsLabel') : document.getElementById('planDetailsModalLabel');
            
            loadingOverlay.style.display = 'flex';
            formElement.reset();
            
            const tabsContainerId = isInlineView ? 'planTabsInline' : 'planTabs';
            document.querySelectorAll(`#${tabsContainerId} .nav-link`).forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll(`#${tabsContainerId} ~ .tab-content .tab-pane`).forEach(pane => pane.classList.remove('show', 'active'));
            const generalTabBtn = isInlineView ? document.getElementById('general-tab-inline') : document.getElementById('general-tab');
            generalTabBtn.classList.add('active');
            const generalTabPane = isInlineView ? document.getElementById('general-inline') : document.getElementById('general');
            generalTabPane.classList.add('show', 'active');

            try {
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
                
                document.getElementById(`planId${isInlineView ? 'Inline' : ''}`).value = data.id || '';
                planDetailsLabel.textContent = `Plan Details: ${data.plan_title || 'Untitled'}`;
                document.getElementById(`plan_title${suffix}`).value = data.plan_title || '';
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

        if (editBtn) { fetchAndPopulatePlan(editBtn.dataset.id, false); }
        if (viewInlineBtn) { fetchAndPopulatePlan(viewInlineBtn.dataset.id, true); }

        if (deleteBtn) {
          const planId = deleteBtn.dataset.id;
          if (confirm('Are you sure you want to delete this plan?')) {
            const formData = new FormData();
            formData.append('delete_id', planId);
            fetch('handle_plan_crud.php', { method: 'POST', body: formData })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                    showMessageBox('Plan deleted successfully!');
                    updatePlanList();
                } else { throw new Error(data.error || 'Failed to delete plan.'); }
              })
              .catch(error => showMessageBox(`Error: ${error.message}`, 'danger'));
          }
        }

        if (newPlanBtn) {
          document.getElementById('quickCreatePlanForm').reset();
          quickCreateModal.show();
        }

        const addSkillBtn = target.closest('#addSkillBtn, #addSkillBtnInline');
        if (addSkillBtn) {
            const tableId = addSkillBtn.id.includes('Inline') ? 'skillsTableInline' : 'skillsTable';
            document.querySelector(`#${tableId} tbody`).appendChild(createModalSkillRow());
        }
        const removeBtn = target.closest('.remove-skill-btn, .remove-prediction-btn, .remove-goal-btn');
        if (removeBtn) {
            removeBtn.closest('tr').remove();
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

      // --- FORM SUBMISSION LISTENERS ---
      function handleFormSubmit(formId, url) {
        document.getElementById(formId).addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);
          
          if (formId === 'planDetailsForm' || formId === 'planDetailsFormInline') {
              const isInline = formId.includes('Inline');
              const suffix = isInline ? 'Inline' : '';
              
              const gradeSelector = `#aptitudeGradesContainer${suffix} .aptitude-grade-select`;
              let terrainGradesData = [];
              let distanceGradesData = [];
              let styleGradesData = [];
              document.querySelectorAll(gradeSelector).forEach(select => {
                  const gradeType = select.dataset.gradeType;
                  const itemKey = select.dataset.itemKey;
                  const grade = select.value;
                  if (gradeType === 'terrain') { terrainGradesData.push({ terrain: itemKey, grade: grade }); } 
                  else if (gradeType === 'distance') { distanceGradesData.push({ distance: itemKey, grade: grade }); } 
                  else if (gradeType === 'style') { styleGradesData.push({ style: itemKey, grade: grade }); }
              });
              formData.append('terrainGrades', JSON.stringify(terrainGradesData));
              formData.append('distanceGrades', JSON.stringify(distanceGradesData));
              formData.append('styleGrades', JSON.stringify(styleGradesData));
          
              const attributesData = Array.from(document.querySelectorAll(`#attributeGrid${suffix} .attribute-value-input`)).map(input => ({
                  attribute_name: input.dataset.attributeName,
                  value: parseInt(input.value) || 0,
                  grade: input.closest('.input-group').querySelector('.attribute-grade-input').value
              }));
              formData.append('attributes', JSON.stringify(attributesData));
              
              const skillsData = Array.from(document.querySelectorAll(`#skillsTable${suffix} tbody tr`)).map(row => ({
                  id: row.dataset.id || null,
                  skill_name: row.querySelector('.skill-name-input').value,
                  sp_cost: row.querySelector('.skill-sp-cost-input').value,
                  acquired: row.querySelector('.skill-acquired-checkbox').checked ? 'yes' : 'no',
                  tag: row.querySelector('.skill-tag-select').value,
                  notes: row.querySelector('.skill-notes-input').value
              }));
              formData.append('skills', JSON.stringify(skillsData));

              const predictionsData = Array.from(document.querySelectorAll(`#predictionsTable${suffix} tbody tr`)).map(row => ({
                  id: row.dataset.id || null,
                  race_name: row.children[0].querySelector('input').value,
                  venue: row.children[1].querySelector('input').value,
                  ground: row.children[2].querySelector('input').value,
                  distance: row.children[3].querySelector('input').value,
                  track_condition: row.children[4].querySelector('input').value,
                  direction: row.children[5].querySelector('input').value,
                  speed: row.children[6].querySelector('select').value,
                  stamina: row.children[7].querySelector('select').value,
                  power: row.children[8].querySelector('select').value,
                  guts: row.children[9].querySelector('select').value,
                  wit: row.children[10].querySelector('select').value,
                  comment: row.children[11].querySelector('input').value
              }));
              formData.append('predictions', JSON.stringify(predictionsData));

              const goalsData = Array.from(document.querySelectorAll(`#goalsTable${suffix} tbody tr`)).map(row => ({
                  id: row.dataset.id || null,
                  goal: row.querySelector('.goal-input').value,
                  result: row.querySelector('.result-input').value
              }));
              formData.append('goals', JSON.stringify(goalsData));
          }

          fetch(url, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showMessageBox('Plan saved successfully!');
                if (formId === 'planDetailsForm') { planDetailsModal.hide(); } 
                else if (formId === 'planDetailsFormInline') { closeInlineDetailsBtn.click(); } 
                else if (formId === 'quickCreatePlanForm') { quickCreateModal.hide(); }
                updatePlanList();
              } else { throw new Error(data.error || 'An unknown error occurred.'); }
            })
            .catch(error => showMessageBox(`Error: ${error.message}`, 'danger'));
        });
      }
      
      handleFormSubmit('quickCreatePlanForm', 'handle_plan_crud.php');
      handleFormSubmit('planDetailsForm', 'handle_plan_crud.php');
      handleFormSubmit('planDetailsFormInline', 'handle_plan_crud.php'); 

      updatePlanList();

      document.getElementById('energyRange').addEventListener('input', function() { document.getElementById('energyValue').textContent = this.value; });
      document.getElementById('energyRange_inline').addEventListener('input', function() { document.getElementById('energyValue_inline').textContent = this.value; });

      document.getElementById('exportPlanBtn').addEventListener('click', () => {
          if (document.getElementById('planId').value) { copyPlanDetailsToClipboard(currentModalPlanData); } 
          else { showMessageBox('No plan selected to export.', 'warning'); }
      });
      document.getElementById('exportPlanBtnInline').addEventListener('click', () => {
          if (document.getElementById('planIdInline').value) { copyPlanDetailsToClipboard(currentInlinePlanData); } 
          else { showMessageBox('No plan selected to export.', 'warning'); }
      });
    });
  </script>

</body>
</html>