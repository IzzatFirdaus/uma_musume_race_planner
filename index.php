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
  <?php require_once 'components/navbar.php'; ?>

  <div class="container">
    <div class="header-banner rounded-3 text-center mb-4">
      <div class="container">
        <h1 class="display-4 fw-bold"><i class="bi bi-speedometer2"></i> Uma Musume Race Planner</h1>
        <p class="lead">Plan, track, and optimize your horse girl's racing career</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <?php include 'components/plan-list.php'; ?>
      </div>
      <div class="col-lg-4">
        <?php include 'components/stats-panel.php'; ?>
        <?php include 'components/recent-activity.php'; ?>
      </div>
    </div>
  </div>

  <?php require_once 'quick_create_plan_modal.php'; ?>
  <?php require_once 'plan_details_modal.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // --- MODAL AND GLOBAL ELEMENT INITIALIZATION ---
      const body = document.body;
      const darkModeToggle = document.getElementById('darkModeToggle');
      const planDetailsModalElement = document.getElementById('planDetailsModal');
      const planDetailsModal = new bootstrap.Modal(planDetailsModalElement);
      const quickCreateModalElement = document.getElementById('createPlanModal');
      const quickCreateModal = new bootstrap.Modal(quickCreateModalElement);
      const messageBoxModalInstance = new bootstrap.Modal(document.getElementById('messageBoxModal'));

      // --- DARK MODE LOGIC ---
      function setDarkMode(isDarkMode) {
        body.classList.toggle('dark-mode', isDarkMode);
        localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
        if (darkModeToggle) darkModeToggle.checked = isDarkMode;
      }

      const savedDarkMode = localStorage.getItem('darkMode');
      if (savedDarkMode === 'enabled') {
        setDarkMode(true);
      } else if (savedDarkMode === 'disabled') {
        setDarkMode(false);
      } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        setDarkMode(true);
      }

      if (darkModeToggle) {
          darkModeToggle.addEventListener('change', () => setDarkMode(darkModeToggle.checked));
      }

      // --- MESSAGE BOX HELPER ---
      function showMessageBox(message, type = 'success') {
        const messageBoxBody = document.getElementById('messageBoxBody');
        messageBoxBody.textContent = message;
        messageBoxBody.className = `modal-body text-center alert alert-${type} mb-0`;
        messageBoxModalInstance.show();
        setTimeout(() => messageBoxModalInstance.hide(), 3000);
      }
      
      // --- DYNAMIC ROW CREATION HELPERS ---
      function createSkillRow(skill = {}) {
        const row = document.createElement('tr');
        // Ensure skillTagOptions is accessible in this scope, if not defined globally.
        // For now, it's defined in PHP and encoded to JS in the renderSkills function,
        // so we can pass it here or fetch it if createSkillRow is called directly.
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
      
      function createPredictionRow(prediction = {}) {
          const row = document.createElement('tr');
          const predictionIcons = <?php echo json_encode($predictionIcons); ?>;
          const createSelect = (name, selectedValue) => `
              <select class="form-select form-select-sm prediction-${name}-input">
                  ${predictionIcons.map(icon => `<option value="${icon}" ${icon === selectedValue ? 'selected' : ''}>${icon}</option>`).join('')}
              </select>`;
      
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

      // --- RENDER FUNCTIONS FOR MODAL ---
      function renderAttributes(attributes) {
        const attributeGrid = document.getElementById('attributeGrid');
        attributeGrid.innerHTML = '';
        const grades = <?php echo json_encode($attributeGradeOptions); ?>;
        attributes.forEach(attr => {
          const div = document.createElement('div');
          div.className = 'col-md-4';
          const optionsHtml = grades.map(grade => `<option value="${grade}" ${grade === attr.grade ? 'selected' : ''}>${grade}</option>`).join('');
          div.innerHTML = `
            <label class="form-label" for="attr-${attr.attribute_name}">${attr.attribute_name}</label>
            <div class="input-group mb-3">
                <input type="number" class="form-control attribute-value-input" id="attr-${attr.attribute_name}" 
                       data-attribute-name="${attr.attribute_name}" value="${attr.value}" min="0" max="1200">
                <select class="form-select attribute-grade-input" data-attribute-name="${attr.attribute_name}">
                    ${optionsHtml}
                </select>
            </div>`;
          attributeGrid.appendChild(div);
        });
      }

      function renderSkills(skills) {
        const skillsTableBody = document.getElementById('skillsTable').querySelector('tbody');
        skillsTableBody.innerHTML = '';
        skills.forEach(skill => skillsTableBody.appendChild(createSkillRow(skill)));
      }

      function renderPredictions(predictions) {
        const predictionsTableBody = document.getElementById('predictionsTable').querySelector('tbody');
        predictionsTableBody.innerHTML = '';
        predictions.forEach(prediction => predictionsTableBody.appendChild(createPredictionRow(prediction)));
      }

      // --- DYNAMIC UI UPDATE FUNCTIONS ---
      function updatePlanList() {
          fetch('get_plans.php')
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      const planListBody = document.getElementById('planListBody');
                      planListBody.innerHTML = ''; // Clear current list

                      if (data.plans.length > 0) {
                          data.plans.forEach(plan => {
                              const row = document.createElement('tr');
                              // Dynamically apply status badge class
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
                                  <td>
                                      <strong>${plan.plan_title || 'Untitled Plan'}</strong><br>
                                      <small class="text-muted">${plan.name}</small>
                                  </td>
                                  <td>${plan.career_stage ? plan.career_stage.charAt(0).toUpperCase() + plan.career_stage.slice(1) : ''}</td>
                                  <td>${plan.class ? plan.class.charAt(0).toUpperCase() + plan.class.slice(1) : ''}</td>
                                  <td>${plan.race_name || ''}</td>
                                  <td>
                                      <span class="badge ${statusClass} rounded-pill">
                                          ${plan.status || ''}
                                      </span>
                                  </td>
                                  <td>
                                    <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${plan.id}">
                                      <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${plan.id}">
                                      <i class="bi bi-trash"></i>
                                    </button>
                                  </td>
                              `;
                              planListBody.appendChild(row);
                          });
                      } else {
                          planListBody.innerHTML = `
                              <tr>
                                  <td colspan="6" class="text-center text-muted p-4">
                                      No plans found. Click "Create New" to get started!
                                  </td>
                              </tr>
                          `;
                      }
                      updateStats(); // Also update stats after plan list changes
                      updateRecentActivity(); // Update recent activity
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
                      document.getElementById('statsFinished').textContent = data.stats.finished_plans; // Now using 'finished_plans'
                  } else {
                      throw new Error(data.error || 'Failed to fetch stats.');
                  }
              })
              .catch(error => showMessageBox(`Error updating stats: ${error.message}`, 'danger'));
      }

      function updateRecentActivity() {
          fetch('get_activities.php') // Assuming a new get_activities.php endpoint exists
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      const recentActivityBody = document.getElementById('recentActivity');
                      recentActivityBody.innerHTML = ''; // Clear current activity

                      if (data.activities && data.activities.length > 0) {
                          const ul = document.createElement('ul');
                          ul.className = 'list-group list-group-flush';
                          data.activities.forEach(activity => {
                              const li = document.createElement('li');
                              li.className = 'list-group-item d-flex align-items-center';
                              const timestamp = new Date(activity.timestamp).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
                              li.innerHTML = `
                                  <i class="bi ${activity.icon_class || 'bi-info-circle'} me-2"></i>
                                  ${activity.description}
                                  <small class="text-muted ms-auto">${timestamp}</small>
                              `;
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
        const newPlanBtn = target.closest('#newPlanBtn, #createPlanBtn'); // Both buttons open quick create

        // Handle EDIT Plan Button
        if (editBtn) {
          const planId = editBtn.dataset.id;
          document.getElementById('planDetailsLoadingOverlay').style.display = 'flex';
          document.getElementById('planDetailsForm').reset(); // Clear form on load
          
          // Reset active tab to General
          document.getElementById('general-tab').classList.add('active');
          document.getElementById('general').classList.add('show', 'active');
          document.getElementById('attributes-tab').classList.remove('active');
          document.getElementById('attributes').classList.remove('show', 'active');
          document.getElementById('skills-tab').classList.remove('active');
          document.getElementById('skills').classList.remove('show', 'active');
          document.getElementById('predictions-tab').classList.remove('active');
          document.getElementById('predictions').classList.remove('show', 'active');


          // Fetch all related data concurrently
          Promise.all([
              fetch(`fetch_plan_details.php?id=${planId}`).then(res => res.json()),
              fetch(`get_plan_attributes.php?id=${planId}`).then(res => res.json()),
              fetch(`get_plan_skills.php?id=${planId}`).then(res => res.json()),
              fetch(`get_plan_predictions.php?id=${planId}`).then(res => res.json()),
              fetch(`get_plan_goals.php?id=${planId}`).then(res => res.json()),
              fetch(`get_plan_terrain_grades.php?id=${planId}`).then(res => res.json()),
              fetch(`get_plan_distance_grades.php?id=${planId}`).then(res => res.json()),
              fetch(`get_plan_style_grades.php?id=${planId}`).then(res => res.json()),
              fetch(`get_plan_turns.php?id=${planId}`).then(res => res.json())
          ])
            .then(([
                mainPlanResponse,
                attributesResponse,
                skillsResponse,
                predictionsResponse,
                goalsResponse,
                terrainGradesResponse,
                distanceGradesResponse,
                styleGradesResponse,
                turnsResponse
            ]) => {
                // Check for errors in any response
                if (mainPlanResponse.error) throw new Error(mainPlanResponse.error);
                if (attributesResponse.error) throw new Error(attributesResponse.error);
                if (skillsResponse.error) throw new Error(skillsResponse.error);
                if (predictionsResponse.error) throw new Error(predictionsResponse.error);
                if (goalsResponse.error) throw new Error(goalsResponse.error);
                if (terrainGradesResponse.error) throw new Error(terrainGradesResponse.error);
                if (distanceGradesResponse.error) throw new Error(distanceGradesResponse.error);
                if (styleGradesResponse.error) throw new Error(styleGradesResponse.error);
                if (turnsResponse.error) throw new Error(turnsResponse.error);

                const data = mainPlanResponse.plan; // Main plan data is now nested under 'plan'

                // Populate form fields (from main plan data)
                document.getElementById('planId').value = data.id || '';
                document.getElementById('planDetailsModalLabel').textContent = `Plan Details: ${data.plan_title || 'Untitled'}`;
                document.getElementById('plan_title').value = data.plan_title || '';
                document.getElementById('modalName').value = data.name || '';
                document.getElementById('modalCareerStage').value = data.career_stage || '';
                document.getElementById('modalClass').value = data.class || '';
                document.getElementById('modalRaceName').value = data.race_name || '';
                document.getElementById('modalTurnBefore').value = data.turn_before || 0;
                document.getElementById('modalGoal').value = data.goal || '';
                document.getElementById('modalStrategy').value = data.strategy_id || '';
                document.getElementById('modalMood').value = data.mood_id || '';
                document.getElementById('modalCondition').value = data.condition_id || '';
                document.getElementById('energyRange').value = data.energy || 0;
                document.getElementById('energyValue').textContent = data.energy || 0;
                document.getElementById('raceDaySwitch').checked = data.race_day === 'yes';
                document.getElementById('acquireSkillSwitch').checked = data.acquire_skill === 'YES';
                document.getElementById('skillPoints').value = data.total_available_skill_points || 0;
                document.getElementById('modalStatus').value = data.status || 'Planning';
                document.getElementById('modalTimeOfDay').value = data.time_of_day || '';
                document.getElementById('modalMonth').value = data.month || '';
                document.getElementById('modalSource').value = data.source || '';
                document.getElementById('growthRateSpeed').value = data.growth_rate_speed || 0;
                document.getElementById('growthRateStamina').value = data.growth_rate_stamina || 0;
                document.getElementById('growthRatePower').value = data.growth_rate_power || 0;
                document.getElementById('growthRateGuts').value = data.growth_rate_guts || 0;
                document.getElementById('growthRateWit').value = data.growth_rate_wit || 0;
                
                // Render dynamic content (from separate API calls)
                renderAttributes(attributesResponse.attributes || []);
                renderSkills(skillsResponse.skills || []);
                renderPredictions(predictionsResponse.predictions || []);
                // Add rendering for goals, terrain_grades, distance_grades, style_grades, turns if they have UI elements
                // E.g., if you add a Goals tab: renderGoals(goalsResponse.goals || []);

                planDetailsModal.show();
            })
            .catch(error => showMessageBox(`Error fetching plan details: ${error.message}`, 'danger'))
            .finally(() => {
              document.getElementById('planDetailsLoadingOverlay').style.display = 'none';
            });
        }

        // Handle DELETE Plan Button
        if (deleteBtn) {
          const planId = deleteBtn.dataset.id;
          if (confirm('Are you sure you want to delete this plan?')) {
            const formData = new FormData();
            formData.append('delete_id', planId);
            fetch('handle_plan_crud.php', { method: 'POST', body: formData })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                    showMessageBox('Plan deleted successfully!', 'success');
                    updatePlanList(); // Update UI instead of full reload
                }
                else throw new Error(data.error || 'Failed to delete plan.');
              })
              .catch(error => showMessageBox(`Error: ${error.message}`, 'danger'));
          }
        }

        // Handle NEW Plan Button
        if (newPlanBtn) {
          document.getElementById('quickCreatePlanForm').reset();
          quickCreateModal.show();
        }

        // Handle Add/Remove buttons inside the modal
        if (target.closest('#addSkillBtn')) {
            document.querySelector('#skillsTable tbody').appendChild(createSkillRow());
        }
        if (target.closest('.remove-skill-btn')) {
            target.closest('tr').remove();
        }
        if (target.closest('#addPredictionBtn')) {
            document.querySelector('#predictionsTable tbody').appendChild(createPredictionRow());
        }
        if (target.closest('.remove-prediction-btn')) {
            target.closest('tr').remove();
        }
      });

      // --- FORM SUBMISSION LISTENERS ---
      function handleFormSubmit(formId, url) {
        document.getElementById(formId).addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);

          if (formId === 'planDetailsForm') {
            const attributes = Array.from(document.querySelectorAll('#attributeGrid .attribute-value-input')).map(input => ({
              attribute_name: input.dataset.attributeName,
              value: parseInt(input.value) || 0,
              grade: input.closest('.input-group').querySelector('.attribute-grade-input').value
            }));
            formData.append('attributes', JSON.stringify(attributes));

            const skills = Array.from(document.querySelectorAll('#skillsTable tbody tr')).map(row => ({
                skill_name: row.querySelector('.skill-name-input').value,
                sp_cost: parseInt(row.querySelector('.skill-sp-cost-input').value) || 0,
                acquired: row.querySelector('.skill-acquired-checkbox').checked ? 'yes' : 'no',
                tag: row.querySelector('.skill-tag-select').value,
                notes: row.querySelector('.skill-notes-input').value
            }));
            formData.append('skills', JSON.stringify(skills));

            const predictions = Array.from(document.querySelectorAll('#predictionsTable tbody tr')).map(row => ({
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
            formData.append('predictions', JSON.stringify(predictions));
            // Add other dynamic data collection here (goals, terrain_grades, etc.) if they are added to the modal
          }

          fetch(url, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showMessageBox('Plan saved successfully!', 'success');
                if (formId === 'planDetailsForm') {
                    planDetailsModal.hide();
                } else if (formId === 'quickCreatePlanForm') {
                    quickCreateModal.hide();
                }
                updatePlanList(); // Update UI instead of full reload
              }
              else throw new Error(data.error || 'An unknown error occurred.');
            })
            .catch(error => showMessageBox(`Error: ${error.message}`, 'danger'));
        });
      }
      
      handleFormSubmit('quickCreatePlanForm', 'handle_plan_crud.php');
      handleFormSubmit('planDetailsForm', 'handle_plan_crud.php');

      // Initial UI updates on page load
      updatePlanList(); // This also triggers updateStats and updateRecentActivity

      // Energy range display update
      document.getElementById('energyRange').addEventListener('input', function() {
        document.getElementById('energyValue').textContent = this.value;
      });

      // Export Plan Button
      document.getElementById('exportPlanBtn').addEventListener('click', function() {
          const planId = document.getElementById('planId').value;
          if (planId) {
              // Construct a detailed text representation of the plan
              const planTitle = document.getElementById('plan_title').value;
              const traineeName = document.getElementById('modalName').value;
              const careerStage = document.getElementById('modalCareerStage').value;
              const planStatus = document.getElementById('modalStatus').value;

              let exportText = `--- Plan Details: ${planTitle || 'Untitled Plan'} ---\n`;
              exportText += `Trainee: ${traineeName || 'N/A'}\n`;
              exportText += `Career Stage: ${careerStage || 'N/A'}\n`;
              exportText += `Status: ${planStatus || 'N/A'}\n\n`;

              // General Tab Data
              exportText += "GENERAL INFORMATION:\n";
              exportText += `  Race Name: ${document.getElementById('modalRaceName').value || 'N/A'}\n`;
              exportText += `  Turn Before: ${document.getElementById('modalTurnBefore').value || 'N/A'}\n`;
              exportText += `  Goal: ${document.getElementById('modalGoal').value || 'N/A'}\n`;
              exportText += `  Strategy: ${document.getElementById('modalStrategy').selectedOptions[0].text || 'N/A'}\n`;
              exportText += `  Mood: ${document.getElementById('modalMood').selectedOptions[0].text || 'N/A'}\n`;
              exportText += `  Condition: ${document.getElementById('modalCondition').selectedOptions[0].text || 'N/A'}\n`;
              exportText += `  Energy: ${document.getElementById('energyRange').value || 'N/A'}%\n`;
              exportText += `  Race Day: ${document.getElementById('raceDaySwitch').checked ? 'Yes' : 'No'}\n`;
              exportText += `  Acquire Skill: ${document.getElementById('acquireSkillSwitch').checked ? 'Yes' : 'No'}\n`;
              exportText += `  Total SP: ${document.getElementById('skillPoints').value || 'N/A'}\n`;
              exportText += `  Time of Day: ${document.getElementById('modalTimeOfDay').value || 'N/A'}\n`;
              exportText += `  Month: ${document.getElementById('modalMonth').value || 'N/A'}\n`;
              exportText += `  Source: ${document.getElementById('modalSource').value || 'N/A'}\n`;
              exportText += `  Growth Rates (S/St/P/G/W): ${document.getElementById('growthRateSpeed').value}% / ${document.getElementById('growthRateStamina').value}% / ${document.getElementById('growthRatePower').value}% / ${document.getElementById('growthRateGuts').value}% / ${document.getElementById('growthRateWit').value}%\n\n`;

              // Attributes Tab Data
              const attributes = Array.from(document.querySelectorAll('#attributeGrid .attribute-value-input')).map(input => ({
                  attribute_name: input.dataset.attributeName,
                  value: input.value,
                  grade: input.closest('.input-group').querySelector('.attribute-grade-input').value
              }));
              if (attributes.length > 0) {
                  exportText += "ATTRIBUTES:\n";
                  attributes.forEach(attr => {
                      exportText += `  ${attr.attribute_name}: ${attr.value} (${attr.grade})\n`;
                  });
                  exportText += "\n";
              }

              // Skills Tab Data
              const skills = Array.from(document.querySelectorAll('#skillsTable tbody tr')).map(row => ({
                  skill_name: row.querySelector('.skill-name-input').value,
                  sp_cost: row.querySelector('.skill-sp-cost-input').value,
                  acquired: row.querySelector('.skill-acquired-checkbox').checked ? 'Yes' : 'No',
                  tag: row.querySelector('.skill-tag-select').value,
                  notes: row.querySelector('.skill-notes-input').value
              }));
              if (skills.length > 0) {
                  exportText += "SKILLS:\n";
                  skills.forEach(skill => {
                      exportText += `  - ${skill.skill_name} (SP: ${skill.sp_cost}, Acquired: ${skill.acquired}, Tag: ${skill.tag}, Notes: ${skill.notes})\n`;
                  });
                  exportText += "\n";
              }

              // Race Predictions Tab Data
              const predictions = Array.from(document.querySelectorAll('#predictionsTable tbody tr')).map(row => ({
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
              if (predictions.length > 0) {
                  exportText += "RACE PREDICTIONS:\n";
                  predictions.forEach(pred => {
                      exportText += `  - Race: ${pred.race_name} (${pred.venue}, ${pred.distance})\n`;
                      exportText += `    Ground: ${pred.ground}, Track: ${pred.track_condition}, Direction: ${pred.direction}\n`;
                      exportText += `    Stats: S:${pred.speed} St:${pred.stamina} P:${pred.power} G:${pred.guts} W:${pred.wit}\n`;
                      exportText += `    Comment: ${pred.comment}\n`;
                  });
                  exportText += "\n";
              }

              // Create a Blob and download it
              const blob = new Blob([exportText], { type: 'text/plain;charset=utf-8' });
              const link = document.createElement('a');
              link.href = URL.createObjectURL(blob);
              link.download = `${planTitle || 'Untitled_Plan'}.txt`;
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
              URL.revokeObjectURL(link.href);

              showMessageBox('Plan exported successfully!', 'success');
          } else {
              showMessageBox('No plan selected to export.', 'warning');
          }
      });
    });
  </script>

  <div class="modal fade" id="messageBoxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center alert alert-success mb-0" id="messageBoxBody"></div>
      </div>
    </div>
  </div>
</body>

</html>