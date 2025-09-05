/**
 * js/main.js
 *
 * This is the main application script. It initializes all UI components,
 * handles user interactions, and communicates with the Laravel API.
 */

// --- UPDATED: Import all necessary modules ---
import { attachAutosuggest } from './autosuggest.js';
import { initializeSkillManagement } from './skill_management.js';
import { escapeHtml } from './utils.js';

// --- Global variables ---
let messageBoxModalInstance;
let growthChartInstance = null;
let growthChartInstanceInline = null;
let currentPlanData = {}; // Single source for currently loaded plan data

// --- Icon Configuration ---
const statIcons = {
    speed: { class: 'bi bi-lightning-charge-fill', colorClass: 'text-speed-blue' },
    stamina: { class: 'bi bi-heart-fill', colorClass: 'text-stamina-red' },
    power: { class: 'bi bi-arm-flex', colorClass: 'text-power-orange' },
    guts: { class: 'bi bi-fire', colorClass: 'text-guts-magenta' },
    wit: { class: 'bi bi-mortarboard-fill', colorClass: 'text-wit-green' }
};


// --- Core Application Logic ---

document.addEventListener('DOMContentLoaded', function() {
    // --- Initialize UI components and variables ---
    const body = document.body;
    const darkModeToggle = document.getElementById('darkModeToggle');
    messageBoxModalInstance = new bootstrap.Modal(document.getElementById('messageBoxModal'));

    // --- Initialize Dynamic Tables ---
    initializeSkillManagement('skillsTable', 'addSkillBtn');
    initializeSkillManagement('skillsTableInline', 'addSkillBtnInline');
    // Note: The logic for adding/removing other rows (goals, predictions) is in setupGlobalEventListeners

    // --- Initial Data Rendering (from Blade) ---
    // The DashboardController now passes initial data to the view, removing the need for initial fetch calls.
    if (window.plannerData) {
        renderPlanTable(window.plannerData.plans || []);
        renderStats(window.plannerData.stats || {});
        renderRecentActivity(window.plannerData.activities || []);
    }

    // --- Dark Mode Setup ---
    setupDarkMode(body, darkModeToggle);

    // --- Main Event Listener for all clicks ---
    setupGlobalEventListeners();

    // --- Handle opening plans from URL on page load ---
    handleUrlParameters();

    // --- Attach Autosuggest ---
    attachAutosuggest(document.getElementById('modalName'), 'name');
    attachAutosuggest(document.getElementById('modalName_inline'), 'name');
    attachAutosuggest(document.getElementById('modalRaceName'), 'race_name');
    attachAutosuggest(document.getElementById('modalRaceName_inline'), 'race_name');
    attachAutosuggest(document.getElementById('modalGoal'), 'goal');
    attachAutosuggest(document.getElementById('modalGoal_inline'), 'goal');
});

// -----------------------------------------------------------------------------
// SECTION: SETUP AND INITIALIZATION
// -----------------------------------------------------------------------------

/**
 * Sets up and manages the dark mode functionality.
 * @param {HTMLElement} body The body element.
 * @param {HTMLInputElement} darkModeToggle The toggle switch element.
 */
function setupDarkMode(body, darkModeToggle) {
    const setDarkMode = (isDarkMode) => {
        body.classList.toggle('dark-mode', isDarkMode);
        localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');
        if (darkModeToggle) darkModeToggle.checked = isDarkMode;
    };
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode === 'enabled') {
        setDarkMode(true);
    } else if (savedDarkMode === 'disabled') {
        setDarkMode(false);
    } else if (window.matchMedia?.('(prefers-color-scheme: dark)').matches) {
        setDarkMode(true);
    }
    darkModeToggle?.addEventListener('change', () => setDarkMode(darkModeToggle.checked));
}

/**
 * Handles all delegated click events for the application.
 */
function setupGlobalEventListeners() {
    document.addEventListener('click', async (event) => {
        const target = event.target;
        const planId = target.closest('[data-id]')?.dataset.id;

        // Edit/View Buttons
        if (target.closest('.edit-btn')) await fetchAndPopulatePlan(planId, false);
        if (target.closest('.view-inline-btn')) await fetchAndPopulatePlan(planId, true);

        // Delete Button
        if (target.closest('.delete-btn')) {
            await handleDeletePlan(planId);
        }

        // Add/Remove Table Row Buttons
        if (target.closest('#addPredictionBtn, #addPredictionBtnInline')) {
            const isInline = target.closest('#addPredictionBtnInline');
            const tableBody = document.querySelector(isInline ? '#predictionsTableInline tbody' : '#predictionsTable tbody');
            tableBody.appendChild(createPredictionRow());
        }
        if (target.closest('.remove-prediction-btn')) {
            target.closest('tr').remove();
        }
        if (target.closest('#addGoalBtn, #addGoalBtnInline')) {
            const isInline = target.closest('#addGoalBtnInline');
            const tableBody = document.querySelector(isInline ? '#goalsTableInline tbody' : '#goalsTable tbody');
            tableBody.appendChild(createGoalRow());
        }
        if (target.closest('.remove-goal-btn')) {
            target.closest('tr').remove();
        }


        // UI Interaction Buttons
        if (target.closest('#closeInlineDetailsBtn')) {
            document.getElementById('planInlineDetails').style.display = 'none';
            document.getElementById('mainContent').style.display = 'flex';
            updateUrlWithPlan(null);
        }
        if (target.closest('#exportPlanBtn, #exportPlanBtnInline')) {
            if (currentPlanData.data?.id) {
                copyPlanDetailsToClipboard(currentPlanData.data);
            } else {
                showMessageBox('No plan data loaded to export.', 'warning');
            }
        }
    });

    // Form submission handlers
    document.getElementById('planDetailsForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('planDetailsFormInline').addEventListener('submit', handleFormSubmit);
    document.getElementById('quickCreatePlanForm').addEventListener('submit', handleFormSubmit);


    // Listen for custom events to refresh data
    document.addEventListener('planUpdated', refreshDashboardData);

    // Modal close event
    document.getElementById('planDetailsModal')?.addEventListener('hidden.bs.modal', () => {
        updateUrlWithPlan(null);
    });

    // Chart tab shown events
    document.getElementById('progress-chart-tab')?.addEventListener('shown.bs.tab', (e) => {
        const planId = document.getElementById('planId').value;
        renderGrowthChart(planId, false);
    });
    document.getElementById('progress-chart-tab-inline')?.addEventListener('shown.bs.tab', (e) => {
        const planId = document.getElementById('planId_inline').value;
        renderGrowthChart(planId, true);
    });
}

/**
 * Checks URL parameters on page load to open a specific plan.
 */
function handleUrlParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    const planIdFromUrl = urlParams.get('plan_id');
    const viewPlanIdFromUrl = urlParams.get('view_plan_id');

    if (planIdFromUrl) {
        fetchAndPopulatePlan(planIdFromUrl, false);
    } else if (viewPlanIdFromUrl) {
        fetchAndPopulatePlan(viewPlanIdFromUrl, true);
    }
}


// -----------------------------------------------------------------------------
// SECTION: API AND DATA HANDLING
// -----------------------------------------------------------------------------

/**
 * Fetches all data for a single plan from the API and populates the UI.
 * @param {number|string} planId The ID of the plan to fetch.
 * @param {boolean} isInlineView True if rendering in the inline view, false for the modal.
 */
async function fetchAndPopulatePlan(planId, isInlineView) {
    const loadingOverlay = document.getElementById(isInlineView ? 'planInlineDetailsLoadingOverlay' : 'planDetailsLoadingOverlay');
    const formElement = document.getElementById(isInlineView ? 'planDetailsFormInline' : 'planDetailsForm');

    loadingOverlay.style.display = 'flex';
    formElement.reset();
    resetFormTabs(isInlineView);

    try {
        // --- UPDATED: Simplified to a single, efficient API call ---
        const response = await fetch(`/api/v1/plans/${planId}`);
        if (!response.ok) throw new Error(`Network response was not ok (status: ${response.status})`);

        const result = await response.json();
        currentPlanData = result; // Store the fetched data globally

        // Populate the form with the retrieved data
        populateForm(result.data, isInlineView);

        // Show the correct view (modal or inline)
        if (isInlineView) {
            document.getElementById('mainContent').style.display = 'none';
            document.getElementById('planInlineDetails').style.display = 'block';
        } else {
            const planDetailsModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('planDetailsModal'));
            planDetailsModal.show();
        }
        updateUrlWithPlan(planId, isInlineView);

    } catch (error) {
        console.error("Error fetching plan details:", error);
        showMessageBox(`Error fetching plan details: ${error.message}`, 'danger');
    } finally {
        loadingOverlay.style.display = 'none';
    }
}


/**
 * Handles the submission logic for all plan forms (create, modal, inline).
 * @param {Event} e The form submission event.
 */
async function handleFormSubmit(e) {
    e.preventDefault();
    const formElement = e.target;
    const isInline = formElement.id.includes('Inline');
    const loadingOverlay = document.getElementById(isInline ? 'planInlineDetailsLoadingOverlay' : 'planDetailsLoadingOverlay');
    if(loadingOverlay) loadingOverlay.style.display = 'flex';

    try {
        const formData = new FormData(formElement);
        const planId = formData.get('planId');

        // --- UPDATED: API submission logic ---
        const url = planId ? `/api/v1/plans/${planId}` : '/api/v1/plans';
        const method = 'POST'; // Use POST for both, but spoof PUT for updates
        if (planId) formData.append('_method', 'PUT');

        const response = await fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json' // Important for Laravel validation responses
            }
        });

        const result = await response.json();

        if (!response.ok) {
            // Handle Laravel validation errors
            if (response.status === 422 && result.errors) {
                const errorMessages = Object.values(result.errors).flat().join('\n');
                throw new Error(errorMessages);
            }
            throw new Error(result.message || 'An unknown error occurred.');
        }

        showMessageBox('Plan saved successfully!');
        document.dispatchEvent(new CustomEvent('planUpdated'));

        // Close the modal or inline view
        if (formElement.id === 'quickCreatePlanForm') {
            bootstrap.Modal.getInstance(document.getElementById('createPlanModal'))?.hide();
        } else if (isInline) {
            document.getElementById('closeInlineDetailsBtn').click();
        } else {
            bootstrap.Modal.getInstance(document.getElementById('planDetailsModal'))?.hide();
        }

    } catch (error) {
        showMessageBox(`Error saving plan: ${error.message}`, 'danger');
    } finally {
        if(loadingOverlay) loadingOverlay.style.display = 'none';
    }
}

/**
 * Deletes a plan after confirmation.
 * @param {string|number} planId The ID of the plan to delete.
 */
async function handleDeletePlan(planId) {
    if (confirm('Are you sure you want to delete this plan?')) {
        try {
            const response = await fetch(`/api/v1/plans/${planId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            if (!response.ok) throw new Error('Server responded with an error.');
            showMessageBox('Plan deleted successfully!');
            document.dispatchEvent(new CustomEvent('planUpdated'));
        } catch (error) {
            showMessageBox(`Error: ${error.message}`, 'danger');
        }
    }
}

/**
 * Fetches fresh dashboard data from the API and re-renders the components.
 */
async function refreshDashboardData() {
    try {
        const [plansRes, statsRes, activityRes] = await Promise.all([
            fetch('/api/v1/plans/list'),
            fetch('/api/v1/dashboard/stats'),
            fetch('/api/v1/dashboard/activities')
        ]);
        const plans = await plansRes.json();
        const stats = await statsRes.json();
        const activities = await activityRes.json();

        renderPlanTable(plans.data);
        renderStats(stats.data);
        renderRecentActivity(activities.data);

    } catch (error) {
        console.error("Failed to refresh dashboard data:", error);
        showMessageBox("Could not refresh dashboard data.", "danger");
    }
}


// -----------------------------------------------------------------------------
// SECTION: UI RENDERING AND HELPERS
// -----------------------------------------------------------------------------

/**
 * Populates all form fields from the fetched plan data object.
 * @param {object} data The plan data object from the API.
 * @param {boolean} isInline True if rendering in the inline view.
 */
function populateForm(data, isInline) {
    const suffix = isInline ? '_inline' : '';

    // Populate all simple form fields
    document.getElementById(`planId${suffix}`).value = data.id || '';
    document.getElementById(isInline ? 'planInlineDetailsLabel' : 'planDetailsModalLabel').textContent = `Plan Details: ${data.plan_title || 'Untitled'}`;
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


    // Render complex nested data
    renderAttributes(data.attributes || [], isInline);
    renderAptitudeGrades(data, isInline);
    renderSkills(data.skills || [], isInline);
    renderPredictions(data.predictions || [], isInline);
    renderGoals(data.goals || [], isInline);
}


/**
 * Renders the main table of plans.
 * @param {Array} plans Array of plan objects.
 */
function renderPlanTable(plans) {
    const tableBody = document.getElementById('plan-list-body');
    if (!tableBody) return;
    tableBody.innerHTML = ''; // Clear existing rows
    if (!plans || plans.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No plans found.</td></tr>';
        return;
    }
    plans.forEach(plan => {
        const statusClass = plan.status === 'Active' ? 'text-bg-success' : (plan.status === 'Finished' ? 'text-bg-secondary' : 'text-bg-warning');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(plan.plan_title)}</td>
            <td>${escapeHtml(plan.name)}</td>
            <td><span class="badge ${statusClass}">${escapeHtml(plan.status)}</span></td>
            <td>${new Date(plan.updated_at).toLocaleDateString()}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-info view-inline-btn" data-id="${plan.id}" title="View Inline"><i class="bi bi-layout-text-sidebar-reverse"></i></button>
                <button class="btn btn-sm btn-primary edit-btn" data-id="${plan.id}" title="Edit in Modal"><i class="bi bi-pencil-square"></i></button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="${plan.id}" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

/**
 * Renders the dashboard statistics.
 * @param {object} stats Object containing stat counts.
 */
function renderStats(stats) {
    document.getElementById('statsPlans').textContent = stats.total_plans || 0;
    document.getElementById('statsActive').textContent = stats.active_plans || 0;
    document.getElementById('statsFinished').textContent = stats.finished_plans || 0;
}

/**
 * Renders the recent activity list.
 * @param {Array} activities Array of activity objects.
 */
function renderRecentActivity(activities) {
    const recentActivityBody = document.getElementById('recentActivity');
    if (!recentActivityBody) return;
    recentActivityBody.innerHTML = '';
    if (activities && activities.length > 0) {
        const ul = document.createElement('ul');
        ul.className = 'list-group list-group-flush';
        activities.forEach(activity => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex align-items-center small';
            const timestamp = new Date(activity.timestamp).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            li.innerHTML = `<i class="bi ${activity.icon_class || 'bi-info-circle'} me-2"></i>${activity.description}<small class="text-muted ms-auto flex-shrink-0">${timestamp}</small>`;
            ul.appendChild(li);
        });
        recentActivityBody.appendChild(ul);
    } else {
        recentActivityBody.innerHTML = '<div class="list-group-item text-center text-muted">No recent activity.</div>';
    }
}


/**
 * Renders the attribute sliders in the form.
 * @param {Array} attributes The list of attribute objects.
 * @param {boolean} isInline True if for the inline view.
 */
function renderAttributes(attributes, isInline) {
    const containerId = isInline ? 'attributeSlidersContainerInline' : 'attributeSlidersContainer';
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';
    const defaultAttributeNames = ['speed', 'stamina', 'power', 'guts', 'wit'];
    defaultAttributeNames.forEach(attrName => {
        const attr = attributes.find(a => a.attribute_name.toLowerCase() === attrName) || { value: 0 };
        const div = document.createElement('div');
        div.className = 'col-6 col-md-4 col-lg mb-3';
        const iconInfo = statIcons[attrName];
        div.innerHTML = `
            <div class="d-flex align-items-center mb-1">
                <i class="${iconInfo.class} stat-icon ${iconInfo.colorClass} me-2"></i>
                <label class="form-label mb-0 small">${attrName.charAt(0).toUpperCase() + attrName.slice(1)}</label>
            </div>
            <input type="number" class="form-control form-control-sm" name="attributes[${attrName}]" min="0" max="1200" value="${attr.value}" data-stat="${attrName}">`;
        container.appendChild(div);
    });
}

/**
 * Renders the aptitude grade selectors in the form.
 * @param {object} data The full plan data object from the API.
 * @param {boolean} isInline True if for the inline view.
 */
function renderAptitudeGrades(data, isInline) {
    const containerId = isInline ? 'aptitudeGradesContainerInline' : 'aptitudeGradesContainer';
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';

    const gradesData = {
        terrain_grades: data.terrain_grades || [],
        distance_grades: data.distance_grades || [],
        style_grades: data.style_grades || [],
    };
    const gradeTypes = [
        { title: 'Terrain', data: gradesData.terrain_grades, key: 'terrain', defaults: ['Turf', 'Dirt'] },
        { title: 'Distance', data: gradesData.distance_grades, key: 'distance', defaults: ['Sprint', 'Mile', 'Medium', 'Long'] },
        { title: 'Style', data: gradesData.style_grades, key: 'style', defaults: ['Front', 'Pace', 'Late', 'End'] }
    ];
    const gradeOptions = window.plannerData?.attributeGradeOptions || ['S', 'A', 'B', 'C', 'D', 'E', 'F', 'G'];

    gradeTypes.forEach(type => {
        const col = document.createElement('div');
        col.className = 'col-md-4';
        let content = `<h6>${type.title}</h6>`;
        const gradeMap = new Map(type.data.map(item => [item[type.key], item.grade]));

        type.defaults.forEach(itemKey => {
            const currentGrade = gradeMap.get(itemKey) || 'G';
            const optionsHtml = gradeOptions.map(grade => `<option value="${grade}" ${grade === currentGrade ? 'selected' : ''}>${grade}</option>`).join('');
            content += `
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label col-form-label-sm">${itemKey}</label>
                    <div class="col-sm-8">
                        <select class="form-select form-select-sm" name="${type.key}Grades[${itemKey}]">${optionsHtml}</select>
                    </div>
                </div>`;
        });
        col.innerHTML = content;
        container.appendChild(col);
    });
}

/**
 * Renders the skills table.
 * @param {Array} skills The list of skill objects.
 * @param {boolean} isInline True if for the inline view.
 */
function renderSkills(skills, isInline) {
    const tableId = isInline ? 'skillsTableInline' : 'skillsTable';
    const skillsTableBody = document.getElementById(tableId)?.querySelector('tbody');
    if (!skillsTableBody) return;
    skillsTableBody.innerHTML = '';
    if(skills.length > 0) {
        skills.forEach(skill => {
            // The initializeSkillManagement module provides the function to create a row
            const newRow = window.createSkillRow(skill);
            skillsTableBody.appendChild(newRow);
        });
    }
    // Re-attach autosuggest to any newly created inputs
    skillsTableBody.querySelectorAll('.skill-name-input').forEach(input => {
        attachAutosuggest(input, 'skill_name', (selected) => {
             const parentRow = input.closest('tr');
             if(parentRow) {
                parentRow.querySelector('.skill-notes-input').value = selected.description || '';
                parentRow.querySelector('.skill-tag').value = selected.tag || '';
             }
        });
    });
}

/**
 * Renders the predictions table.
 * @param {Array} predictions The list of prediction objects.
 * @param {boolean} isInline True if for the inline view.
 */
function renderPredictions(predictions, isInline) {
    const tableId = isInline ? 'predictionsTableInline' : 'predictionsTable';
    const tableBody = document.getElementById(tableId)?.querySelector('tbody');
    if (!tableBody) return;
    tableBody.innerHTML = '';
    if(predictions.length > 0) {
        predictions.forEach(p => tableBody.appendChild(createPredictionRow(p)));
    }
}

/**
 * Renders the goals table.
 * @param {Array} goals The list of goal objects.
 * @param {boolean} isInline True if for the inline view.
 */
function renderGoals(goals, isInline) {
    const tableId = isInline ? 'goalsTableInline' : 'goalsTable';
    const tableBody = document.getElementById(tableId)?.querySelector('tbody');
    if (!tableBody) return;
    tableBody.innerHTML = '';
    if(goals.length > 0) {
        goals.forEach(g => tableBody.appendChild(createGoalRow(g)));
    }
}

/**
 * Renders the progress chart.
 * @param {string|number} planId The ID of the plan.
 * @param {boolean} isInline True if for the inline view.
 */
async function renderGrowthChart(planId, isInline) {
    if (!planId) return;

    const chartCanvas = document.getElementById(isInline ? 'growthChartInline' : 'growthChart');
    const messageContainer = document.getElementById(isInline ? 'growthChartMessageInline' : 'growthChartMessage');
    let chartInstance = isInline ? growthChartInstanceInline : growthChartInstance;

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    try {
        const response = await fetch(`/api/v1/plans/${planId}/progress`);
        const result = await response.json();

        if (result.data && Array.isArray(result.data) && result.data.length > 0) {
            chartCanvas.style.display = 'block';
            messageContainer.style.display = 'none';
            const turns = result.data;
            const ctx = chartCanvas.getContext('2d');
            const getCssVar = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();

            const newChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: turns.map(t => `T${t.turn}`),
                    datasets: [
                        { label: 'Speed', data: turns.map(t => t.speed), borderColor: getCssVar('--stat-speed-color'), tension: 0.3, borderWidth: 2 },
                        { label: 'Stamina', data: turns.map(t => t.stamina), borderColor: getCssVar('--stat-stamina-color'), tension: 0.3, borderWidth: 2 },
                        { label: 'Power', data: turns.map(t => t.power), borderColor: getCssVar('--stat-power-color'), tension: 0.3, borderWidth: 2 },
                        { label: 'Guts', data: turns.map(t => t.guts), borderColor: getCssVar('--stat-guts-color'), tension: 0.3, borderWidth: 2 },
                        { label: 'Wit', data: turns.map(t => t.wit), borderColor: getCssVar('--stat-wit-color'), tension: 0.3, borderWidth: 2 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { usePointStyle: true, color: getCssVar('--bs-body-color') } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: getCssVar('--bs-border-color-translucent') } },
                        x: { grid: { color: getCssVar('--bs-border-color-translucent') } }
                    }
                }
            });

            if (isInline) { growthChartInstanceInline = newChart; }
            else { growthChartInstance = newChart; }
        } else {
            chartCanvas.style.display = 'none';
            messageContainer.style.display = 'block';
            messageContainer.innerHTML = '<p class="text-muted text-center mt-4">No progression data available.</p>';
        }
    } catch (error) {
        chartCanvas.style.display = 'none';
        messageContainer.style.display = 'block';
        messageContainer.innerHTML = '<p class="text-danger text-center mt-4">Could not load chart data.</p>';
        console.error('Error loading chart:', error);
    }
}

/**
 * Creates a new HTML table row for the Predictions table.
 * @param {object} [prediction={}] Optional data to populate the row.
 * @returns {HTMLTableRowElement}
 */
function createPredictionRow(prediction = {}) {
    const row = document.createElement('tr');
    const icons = window.plannerData?.predictionIcons || ['▲', '●', '◯', '△', '✕'];
    const createSelect = (name, selectedValue) => {
        const options = icons.map(icon => `<option value="${icon}" ${icon === selectedValue ? 'selected' : ''}>${icon}</option>`).join('');
        return `<select class="form-select form-select-sm" name="predictions[][${name}]">${options}</select>`;
    };
    row.innerHTML = `
        <td><input type="text" class="form-control form-control-sm" name="predictions[][race_name]" value="${escapeHtml(prediction.race_name || '')}"></td>
        <td><input type="text" class="form-control form-control-sm" name="predictions[][venue]" value="${escapeHtml(prediction.venue || '')}"></td>
        <td><input type="text" class="form-control form-control-sm" name="predictions[][ground]" value="${escapeHtml(prediction.ground || '')}"></td>
        <td><input type="text" class="form-control form-control-sm" name="predictions[][distance]" value="${escapeHtml(prediction.distance || '')}"></td>
        <td>${createSelect('speed', prediction.speed)}</td>
        <td>${createSelect('stamina', prediction.stamina)}</td>
        <td>${createSelect('power', prediction.power)}</td>
        <td><input type="text" class="form-control form-control-sm" name="predictions[][comment]" value="${escapeHtml(prediction.comment || '')}"></td>
        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-prediction-btn p-0 px-1"><i class="bi bi-x-circle"></i></button></td>`;
    return row;
}

/**
 * Creates a new HTML table row for the Goals table.
 * @param {object} [goal={}] Optional data to populate the row.
 * @returns {HTMLTableRowElement}
 */
function createGoalRow(goal = {}) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="text" class="form-control form-control-sm" name="goals[][goal]" value="${escapeHtml(goal.goal || '')}"></td>
        <td><input type="text" class="form-control form-control-sm" name="goals[][result]" value="${escapeHtml(goal.result || '')}"></td>
        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-goal-btn p-0 px-1"><i class="bi bi-x-circle"></i></button></td>`;
    return row;
}

/**
 * Updates the browser URL to reflect the currently viewed plan.
 * @param {string|null} planId The ID of the plan, or null to clear.
 * @param {boolean} isInline True if the inline view is active.
 */
function updateUrlWithPlan(planId, isInline = false) {
    const url = new URL(window.location);
    url.searchParams.delete('plan_id');
    url.searchParams.delete('view_plan_id');
    if (planId) {
        url.searchParams.set(isInline ? 'view_plan_id' : 'plan_id', planId);
    }
    history.pushState({}, '', url);
}

/**
 * Resets the tabs in the plan details form to the default state.
 * @param {boolean} isInline True if resetting the inline form.
 */
function resetFormTabs(isInline) {
    const containerId = isInline ? 'planInlineDetails' : 'planDetailsModal';
    const container = document.getElementById(containerId);
    if (!container) return;

    container.querySelectorAll('.nav-link').forEach(btn => btn.classList.remove('active'));
    container.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));

    const generalTabBtn = container.querySelector('.nav-link[href*="general"]');
    generalTabBtn?.classList.add('active');
    const generalTabPaneId = generalTabBtn?.getAttribute('href');
    if (generalTabPaneId) {
        document.querySelector(generalTabPaneId)?.classList.add('show', 'active');
    }
}

/**
 * Shows a Bootstrap modal as a temporary message box.
 * @param {string} message The message to display.
 * @param {string} type The alert type ('success', 'danger', 'warning', 'info').
 */
function showMessageBox(message, type = 'success') {
    if (!messageBoxModalInstance) return;
    const messageBoxBody = document.getElementById('messageBoxBody');
    messageBoxBody.textContent = message;
    messageBoxBody.className = `modal-body text-center alert alert-${type} mb-0`;
    messageBoxModalInstance.show();
    setTimeout(() => messageBoxModalInstance.hide(), 3000);
}
