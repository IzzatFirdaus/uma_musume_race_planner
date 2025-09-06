
/**
 * js/main.js
 *
 * This is the main application script. It initializes all UI components,
 * handles user interactions, and communicates with the Laravel API.
 */

// --- UPDATED: Import all necessary modules ---
import { attachAutosuggest } from "./autosuggest.js";
import { initializeSkillManagement } from "./skill_management.js";
import { escapeHtml } from "./utils.js";

// --- Global variables ---
let messageBoxModalInstance;
let growthChartInstance = null;
let growthChartInstanceInline = null;
let currentPlanData = {}; // Single source for currently loaded plan data

// --- API base path detection (subdirectory aware) ---
const APP_PUBLIC_PATH = (document.querySelector('meta[name="app-public-path"]')?.content || '').replace(/\/$/, '') || '';
const apiUrl = (path) => `${APP_PUBLIC_PATH}${path}`;

// --- Icon Configuration ---
const statIcons = {
    speed: {
        class: "bi bi-lightning-charge-fill",
        colorClass: "text-speed-blue",
    },
    stamina: { class: "bi bi-heart-fill", colorClass: "text-stamina-red" },
    power: { class: "bi bi-arm-flex", colorClass: "text-power-orange" },
    guts: { class: "bi bi-fire", colorClass: "text-guts-magenta" },
    wit: { class: "bi bi-mortarboard-fill", colorClass: "text-wit-green" },
};

// --- Core Application Logic ---

document.addEventListener("DOMContentLoaded", function () {
    // --- Initialize UI components and variables ---
    const body = document.body;
    const darkModeToggle = document.getElementById("darkModeToggle");
    messageBoxModalInstance = new bootstrap.Modal(
        document.getElementById("messageBoxModal"),
    );

    // --- Initialize Dynamic Tables ---
    initializeSkillManagement("skillsTable", "addSkillBtn");
    initializeSkillManagement("skillsTableInline", "addSkillBtnInline");
    // Note: The logic for adding/removing other rows (goals, predictions) is in setupGlobalEventListeners

    // --- Initial Data Rendering (from Blade) ---
    // The DashboardController now passes initial data to the view, removing the need for initial fetch calls.
    if (window.plannerData) {
        // Let Livewire render the plan rows; only hydrate stats & activities here
        renderStats(window.plannerData.stats || {});
        renderRecentActivity(window.plannerData.activities || []);
    }

    // --- Dark Mode Setup ---
    setupDarkMode(body, darkModeToggle);

    // --- Main Event Listener for all clicks ---
    setupGlobalEventListeners();

    // Open Quick Create modal when Create button clicked
    const createBtn = document.getElementById("createPlanBtn");
    if (createBtn) {
        createBtn.addEventListener("click", () => {
            const modalEl = document.getElementById("createPlanModal");
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });
    }

    // Listen for Livewire events to open modal/inline views
    document.addEventListener("livewire:init", () => {
        // Handle plan modal and inline opening
        Livewire.on("openPlanModal", ({ planId }) => {
            // Dispatch the event to load plan data in modal
            Livewire.dispatch('loadPlan', { planId: planId });
            // Show the modal
            const modalEl = document.getElementById('planDetailsModal');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });
        
        Livewire.on("openPlanEditModal", ({ planId }) => {
            // Dispatch the event to load plan data in modal
            Livewire.dispatch('loadPlan', { planId: planId });
            // Show the modal
            const modalEl = document.getElementById('planDetailsModal');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        });
        
        // Handle inline plan opening - dispatch to PlanInlineDetails component
        Livewire.on("openPlanInline", ({ planId }) => {
            // The component should be available on the page, let's dispatch to it
            Livewire.dispatch('loadPlanInline', { planId: planId });
        });
        });
        
        // Listen for form submission events from Livewire components
        Livewire.on("submitPlanForm", ({ formId }) => {
            const form = document.getElementById(formId);
            if (form) {
                // Trigger the existing form submission handler
                const submitEvent = new Event('submit', { bubbles: true, cancelable: true });
                form.dispatchEvent(submitEvent);
            }
        });

        // Listen for error messages
        Livewire.on("show-error", ({ message }) => {
            showMessageBox(message, 'danger');
        });

        // Listen for showing plan list card
        Livewire.on("showPlanListCard", () => {
            const listCard = document.getElementById("planListCard");
            if (listCard) {
                listCard.style.display = "block";
            }
        });

        // Listen for hiding plan list card
        Livewire.on("hidePlanListCard", () => {
            const listCard = document.getElementById("planListCard");
            if (listCard) {
                listCard.style.display = "none";
            }
        });
        // Ensure that if Livewire re-renders the form after we populate it,
        // we re-apply the populated data. This prevents Livewire DOM updates
        // from clearing fields the client just wrote.
        try {
            if (Livewire.hook) {
                Livewire.hook('message.processed', () => {
                    if (!currentPlanData || !currentPlanData.data) return;
                    const plan = currentPlanData.data;
                    // If modal is visible, repopulate modal fields
                    const modalEl = document.getElementById('planDetailsModal');
                    if (modalEl && modalEl.classList.contains('show')) {
                        populateForm(plan, false);
                    }
                    // If inline details are visible, repopulate inline fields
                    const inlineEl = document.getElementById('planInlineDetails');
                    if (inlineEl && inlineEl.classList.contains('d-block')) {
                        populateForm(plan, true);
                    }
                });
            }
        } catch (e) {
            // Non-fatal: if Livewire hook isn't available or errors, ignore.
            console.debug('Livewire hook registration skipped or failed', e);
        }

    });

    // --- Handle opening plans from URL on page load ---
    handleUrlParameters();

    // --- Attach Autosuggest (guard if element not present on this page) ---
    const elModalName = document.getElementById("modalName");
    if (elModalName) attachAutosuggest(elModalName, "name");
    const elModalNameInline = document.getElementById("modalName_inline");
    if (elModalNameInline) attachAutosuggest(elModalNameInline, "name");
    const elRaceName = document.getElementById("modalRaceName");
    if (elRaceName) attachAutosuggest(elRaceName, "race_name");
    const elRaceNameInline = document.getElementById("modalRaceName_inline");
    if (elRaceNameInline) attachAutosuggest(elRaceNameInline, "race_name");
    const elGoal = document.getElementById("modalGoal");
    if (elGoal) attachAutosuggest(elGoal, "goal");
    const elGoalInline = document.getElementById("modalGoal_inline");
    if (elGoalInline) attachAutosuggest(elGoalInline, "goal");
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
        body.classList.toggle("dark-mode", isDarkMode);
        localStorage.setItem("darkMode", isDarkMode ? "enabled" : "disabled");
        if (darkModeToggle) darkModeToggle.checked = isDarkMode;
    };
    const savedDarkMode = localStorage.getItem("darkMode");
    if (savedDarkMode === "enabled") {
        setDarkMode(true);
    } else if (savedDarkMode === "disabled") {
        setDarkMode(false);
    } else if (window.matchMedia?.("(prefers-color-scheme: dark)").matches) {
        setDarkMode(true);
    }
    darkModeToggle?.addEventListener("change", () =>
        setDarkMode(darkModeToggle.checked),
    );
}

/**
 * Handles all delegated click events for the application.
 */
function setupGlobalEventListeners() {
    document.addEventListener("click", async (event) => {
        const target = event.target;
        // Ignore clicks inside SweetAlert2 container or while overlay is active
        if (document.querySelector(".swal2-container")) {
            return;
        }
        const planId = target.closest("[data-id]")?.dataset.id;

        // Edit/View Buttons - let Livewire handle these
        if (target.closest(".edit-btn")) {
            // The Livewire component will handle this via wire:click
            return;
        }
        if (target.closest(".view-inline-btn")) {
            // The Livewire component will handle this via wire:click  
            return;
        }

        // Delete Button
        if (target.closest(".delete-btn")) {
            await handleDeletePlan(planId);
        }

        // Add/Remove Table Row Buttons
        if (target.closest("#addPredictionBtn, #addPredictionBtnInline")) {
            const isInline = target.closest("#addPredictionBtnInline");
            const tableBody = document.querySelector(
                isInline
                    ? "#predictionsTableInline tbody"
                    : "#predictionsTable tbody",
            );
            tableBody.appendChild(createPredictionRow());
        }
        if (target.closest(".remove-prediction-btn")) {
            target.closest("tr").remove();
        }
        if (target.closest("#addGoalBtn, #addGoalBtnInline")) {
            const isInline = target.closest("#addGoalBtnInline");
            const tableBody = document.querySelector(
                isInline ? "#goalsTableInline tbody" : "#goalsTable tbody",
            );
            tableBody.appendChild(createGoalRow());
        }
        if (target.closest(".remove-goal-btn")) {
            target.closest("tr").remove();
        }

        // UI Interaction Buttons
        if (target.closest("#closeInlineDetailsBtn")) {
            // This is now handled by Livewire wire:click="closePlan", so we just need to update URL
            updateUrlWithPlan(null);
        }
        if (target.closest("#exportPlanBtn, #exportPlanBtnInline")) {
            if (currentPlanData.data?.id) {
                copyPlanDetailsToClipboard(currentPlanData.data);
            } else {
                showMessageBox("No plan data loaded to export.", "warning");
            }
        }

        // Fallback close handler for modal if Bootstrap is not yet available
        if (
            target.closest(
                '#planDetailsModal [data-bs-dismiss="modal"], #planDetailsModal .btn-secondary',
            )
        ) {
            const modalEl = document.getElementById("planDetailsModal");
            if (modalEl) {
                // Try Bootstrap first
                try {
                    window.bootstrap?.Modal?.getOrCreateInstance(
                        modalEl,
                    )?.hide();
                } catch (_) {}
                // Force-hide regardless
                modalEl.classList.remove("show");
                modalEl.style.display = "none";
                modalEl.setAttribute("aria-hidden", "true");
                modalEl.removeAttribute("aria-modal");
                document.body.classList.remove("modal-open");
                document
                    .querySelectorAll(".modal-backdrop")
                    .forEach((el) => el.remove());
            }
        }
    });

    // Form submission handlers
    const formModal = document.getElementById("planDetailsForm");
    formModal?.addEventListener("submit", handleFormSubmit);
    const formInline = document.getElementById("planDetailsFormInline");
    formInline?.addEventListener("submit", handleFormSubmit);
    const formQuick = document.getElementById("quickCreatePlanForm");
    formQuick?.addEventListener("submit", handleFormSubmit);

    // Listen for custom events to refresh data
    document.addEventListener("planUpdated", refreshDashboardData);

    // Modal close event
    document
        .getElementById("planDetailsModal")
        ?.addEventListener("hidden.bs.modal", () => {
            updateUrlWithPlan(null);
        });

    // Chart tab shown events
    document
        .getElementById("progress-chart-tab")
        ?.addEventListener("shown.bs.tab", (e) => {
            const planId = document.getElementById("planId").value;
            renderGrowthChart(planId, false);
        });
    document
        .getElementById("progress-chart-tab_inline")
        ?.addEventListener("shown.bs.tab", (e) => {
            const planId = document.getElementById("planId_inline").value;
            renderGrowthChart(planId, true);
        });
}

/**
 * Checks URL parameters on page load to open a specific plan.
 */
function handleUrlParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    const planIdFromUrl = urlParams.get("plan_id");
    const viewPlanIdFromUrl = urlParams.get("view_plan_id");

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
    const loadingOverlay = document.getElementById(
        isInlineView
            ? "planInlineDetailsLoadingOverlay"
            : "planDetailsLoadingOverlay",
    );
    console.debug("fetchAndPopulatePlan called", { planId, isInlineView });
    if (!planId) {
        console.warn("fetchAndPopulatePlan: no planId provided, aborting");
        return;
    }
    const formElement = document.getElementById(
        isInlineView ? "planDetailsFormInline" : "planDetailsForm",
    );
    if (loadingOverlay) loadingOverlay.style.display = "flex";
    formElement?.reset();
    resetFormTabs(isInlineView);

    try {
        // --- UPDATED: Simplified to a single, efficient API call ---
        const response = await fetch(
            apiUrl(`/api/v1/plans/${encodeURIComponent(planId)}`),
        );
        if (!response.ok)
            throw new Error(
                `Network response was not ok (status: ${response.status})`,
            );

        const result = await response.json();
        console.debug("fetchAndPopulatePlan: fetched result", result);
        // Controller returns the plan object directly, not wrapped in { data }
        currentPlanData = { data: result };

        // Populate the form with the retrieved data
        // Normalize some backend keys to be resilient to naming differences
        // (older API/Controllers used 'racePredictions' or 'race_predictions')
        if (!result.predictions && (result.racePredictions || result.race_predictions)) {
            result.predictions = result.racePredictions || result.race_predictions;
        }
        populateForm(result, isInlineView);

        // Show the correct view (modal or inline)
        if (isInlineView) {
            const mainContent = document.getElementById("mainContent");
            const inlineDetails = document.getElementById("planInlineDetails");
            const listCard = document.getElementById("planListCard");
            console.debug("fetchAndPopulatePlan: toggling inline UI", {
                mainContentExists: !!mainContent,
                inlineExists: !!inlineDetails,
                listCardExists: !!listCard,
            });
            // Only hide the plan list card to keep sidebar visible
            if (listCard) listCard.style.display = "none";
            if (inlineDetails) {
                inlineDetails.style.removeProperty("display");
                inlineDetails.classList.add("d-block");
            }
        } else {
            showModalById("planDetailsModal");
        }
        updateUrlWithPlan(planId, isInlineView);
    } catch (error) {
        console.error("Error fetching plan details:", error);
        showMessageBox(
            `Error fetching plan details: ${error.message}`,
            "danger",
        );
    } finally {
        if (loadingOverlay) loadingOverlay.style.display = "none";
    }
}

/**
 * Handles the submission logic for all plan forms (create, modal, inline).
 * @param {Event} e The form submission event.
 */
async function handleFormSubmit(e) {
    e.preventDefault();
    const formElement = e.target;
    const isInline = formElement.id.includes("Inline");
    const loadingOverlay = document.getElementById(
        isInline
            ? "planInlineDetailsLoadingOverlay"
            : "planDetailsLoadingOverlay",
    );
    if (loadingOverlay) loadingOverlay.style.display = "flex";

    try {
        const formData = new FormData(formElement);
        const planId = formData.get("planId");

        // --- UPDATED: API submission logic ---
        const url = planId
            ? apiUrl(`/api/v1/plans/${planId}`)
            : apiUrl('/api/v1/plans');
        const method = "POST"; // Use POST for both, but spoof PUT for updates
        if (planId) formData.append("_method", "PUT");

        const response = await fetch(url, {
            method: method,
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json" // Important for Laravel validation responses
            },
        });

        const result = await response.json();

        if (!response.ok) {
            // Handle Laravel validation errors
            if (response.status === 422 && result.errors) {
                const errorMessages = Object.values(result.errors)
                    .flat()
                    .join("\n");
                throw new Error(errorMessages);
            }
            throw new Error(result.message || "An unknown error occurred.");
        }

        showMessageBox("Plan saved successfully!");
        document.dispatchEvent(new CustomEvent("planUpdated"));

        // Close the modal or inline view
        if (formElement.id === "quickCreatePlanForm") {
            bootstrap.Modal.getInstance(
                document.getElementById("createPlanModal"),
            )?.hide();
        } else if (isInline) {
            document.getElementById("closeInlineDetailsBtn").click();
        } else {
            bootstrap.Modal.getInstance(
                document.getElementById("planDetailsModal"),
            )?.hide();
        }
    } catch (error) {
        showMessageBox(`Error saving plan: ${error.message}`, "danger");
    } finally {
        if (loadingOverlay) loadingOverlay.style.display = "none";
    }
}

/**
 * Deletes a plan after confirmation.
 * @param {string|number} planId The ID of the plan to delete.
 */
async function handleDeletePlan(planId) {
    const doDelete = async () => {
        try {
            const response = await fetch(apiUrl(`/api/v1/plans/${planId}`), {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json"
                }
            });
            if (!response.ok)
                throw new Error("Server responded with an error.");
            if (window.Swal) {
                await window.Swal.fire({
                    title: "Deleted!",
                    text: "Plan deleted successfully.",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false,
                });
            } else {
                showMessageBox("Plan deleted successfully!");
            }
            // Optimistically remove the row from the current list
            const tableBody = document.getElementById("plan-list-body");
            if (tableBody) {
                const rows = Array.from(tableBody.querySelectorAll("tr"));
                for (const r of rows) {
                    if (r.querySelector(`button[data-id="${planId}"]`)) {
                        r.remove();
                        break;
                    }
                }
            }
            // Trigger a background refresh of the dashboard data
            document.dispatchEvent(new CustomEvent("planUpdated"));
        } catch (error) {
            if (window.Swal) {
                window.Swal.fire({
                    title: "Error",
                    text: error.message,
                    icon: "error",
                });
            } else {
                showMessageBox(`Error: ${error.message}`, "danger");
            }
        }
    };

    if (window.Swal) {
        const { isConfirmed } = await window.Swal.fire({
            title: "Are you sure?",
            text: "This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!",
        });
        if (isConfirmed) await doDelete();
    } else if (confirm("Are you sure you want to delete this plan?")) {
        await doDelete();
    }
}

/**
 * Fetches fresh dashboard data from the API and re-renders the components.
 */
async function refreshDashboardData() {
    try {
        const [plansRes, statsRes, activityRes] = await Promise.all([
            fetch(apiUrl('/api/v1/plans')),
            fetch(apiUrl('/api/v1/dashboard/stats')),
            fetch(apiUrl('/api/v1/dashboard/activities')),
        ]);
        const plans = await plansRes.json(); // array
        const stats = await statsRes.json(); // object
        const activities = await activityRes.json(); // array

        renderPlanTable(plans);
        renderStats(stats);
        renderRecentActivity(activities);
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
    const suffix = isInline ? "_inline" : "";

    const setValue = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val;
    };
    const setText = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };
    const setChecked = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.checked = val;
    };

    setValue(`planId${suffix}`, data.id || "");
    setText(
        isInline ? "planInlineDetailsLabel" : "planDetailsModalLabel",
        `Plan Details: ${data.plan_title || "Untitled"}`,
    );
    setValue(`plan_title${suffix}`, data.plan_title || "");
    setValue(`modalName${suffix}`, data.name || "");
    setValue(`modalCareerStage${suffix}`, data.career_stage || "");
    setValue(`modalClass${suffix}`, data.class || "");
    setValue(`modalRaceName${suffix}`, data.race_name || "");
    setValue(`modalTurnBefore${suffix}`, data.turn_before || 0);
    setValue(`modalGoal${suffix}`, data.goal || "");
    setValue(`modalStrategy${suffix}`, data.strategy_id || "");
    setValue(`modalMood${suffix}`, data.mood_id || "");
    setValue(`modalCondition${suffix}`, data.condition_id || "");
    const energyRange = document.getElementById(`energyRange${suffix}`);
    if (energyRange) {
        energyRange.value = data.energy || 0;
        setText(`energyValue${suffix}`, energyRange.value);
    }
    setChecked(`raceDaySwitch${suffix}`, data.race_day === "yes");
    setChecked(`acquireSkillSwitch${suffix}`, data.acquire_skill === "YES");
    setValue(`skillPoints${suffix}`, data.total_available_skill_points || 0);
    setValue(`modalStatus${suffix}`, data.status || "Planning");
    setValue(`modalTimeOfDay${suffix}`, data.time_of_day || "");
    setValue(`modalMonth${suffix}`, data.month || "");
    setValue(`modalSource${suffix}`, data.source || "");
    setValue(`growthRateSpeed${suffix}`, data.growth_rate_speed || 0);
    setValue(`growthRateStamina${suffix}`, data.growth_rate_stamina || 0);
    setValue(`growthRatePower${suffix}`, data.growth_rate_power || 0);
    setValue(`growthRateGuts${suffix}`, data.growth_rate_guts || 0);
    setValue(`growthRateWit${suffix}`, data.growth_rate_wit || 0);

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
    const tableBody = document.getElementById("plan-list-body");
    if (!tableBody) return;
    tableBody.innerHTML = ""; // Clear existing rows
    if (!plans || plans.length === 0) {
        tableBody.innerHTML =
            '<tr><td colspan="5" class="text-center text-muted">No plans found.</td></tr>';
        return;
    }
    plans.forEach((plan) => {
        const statusClass =
            plan.status === "Active"
                ? "text-bg-success"
                : plan.status === "Finished"
                  ? "text-bg-secondary"
                  : "text-bg-warning";
        const row = document.createElement("tr");
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
    document.getElementById("statsPlans").textContent = stats.total_plans || 0;
    document.getElementById("statsActive").textContent =
        stats.active_plans || 0;
    document.getElementById("statsFinished").textContent =
        stats.finished_plans || 0;
}

/**
 * Renders the recent activity list.
 * @param {Array} activities Array of activity objects.
 */
function renderRecentActivity(activities) {
    const recentActivityBody = document.getElementById("recentActivity");
    if (!recentActivityBody) return;
    recentActivityBody.innerHTML = "";
    if (activities && activities.length > 0) {
        const ul = document.createElement("ul");
        ul.className = "list-group list-group-flush";
        activities.forEach((activity) => {
            const li = document.createElement("li");
            li.className = "list-group-item d-flex align-items-center small";
            const timestamp = new Date(activity.timestamp).toLocaleString(
                "en-US",
                {
                    month: "short",
                    day: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                },
            );
            li.innerHTML = `<i class="bi ${activity.icon_class || "bi-info-circle"} me-2"></i>${activity.description}<small class="text-muted ms-auto flex-shrink-0">${timestamp}</small>`;
            ul.appendChild(li);
        });
        recentActivityBody.appendChild(ul);
    } else {
        recentActivityBody.innerHTML =
            '<div class="list-group-item text-center text-muted">No recent activity.</div>';
    }
}

/**
 * Renders attributes tab (no-op if containers are absent).
 */
function renderAttributes(attributes, isInline) {
    // Containers may not be present in current Blade; safely no-op.
    const suffix = isInline ? "_inline" : "";
    const container =
        document.getElementById(`attributes${suffix}`) ||
        document.getElementById(`attributesContainer${suffix}`);
    if (!container) return;
    // TODO: Implement when markup is finalized.
}

/**
 * Renders aptitude grades tab (no-op if containers are absent).
 */
function renderAptitudeGrades(planData, isInline) {
    const suffix = isInline ? "_inline" : "";
    const container =
        document.getElementById(`grades${suffix}`) ||
        document.getElementById(`gradesContainer${suffix}`);
    if (!container) return;
    // TODO: Implement when markup is finalized.
}

/**
 * Renders the skills table.
 * @param {Array} skills The list of skill objects.
 * @param {boolean} isInline True if for the inline view.
 */
function renderSkills(skills, isInline) {
    const tableId = isInline ? "skillsTableInline" : "skillsTable";
    const skillsTableBody = document
        .getElementById(tableId)
        ?.querySelector("tbody");
    if (!skillsTableBody) return;
    skillsTableBody.innerHTML = "";
    if (skills.length > 0) {
        skills.forEach((skill) => {
            // The initializeSkillManagement module provides the function to create a row
            const newRow = window.createSkillRow(skill);
            skillsTableBody.appendChild(newRow);
        });
    }
    // Re-attach autosuggest to any newly created inputs
    skillsTableBody.querySelectorAll(".skill-name-input").forEach((input) => {
        attachAutosuggest(input, "skill_name", (selected) => {
            const parentRow = input.closest("tr");
            if (parentRow) {
                parentRow.querySelector(".skill-notes-input").value =
                    selected.description || "";
                parentRow.querySelector(".skill-tag").value =
                    selected.tag || "";
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
    const tableId = isInline ? "predictionsTableInline" : "predictionsTable";
    const tableBody = document.getElementById(tableId)?.querySelector("tbody");
    if (!tableBody) return;
    tableBody.innerHTML = "";
    if (predictions.length > 0) {
        predictions.forEach((p) =>
            tableBody.appendChild(createPredictionRow(p)),
        );
    }
}

/**
 * Renders the goals table.
 * @param {Array} goals The list of goal objects.
 * @param {boolean} isInline True if for the inline view.
 */
function renderGoals(goals, isInline) {
    const tableId = isInline ? "goalsTableInline" : "goalsTable";
    const tableBody = document.getElementById(tableId)?.querySelector("tbody");
    if (!tableBody) return;
    tableBody.innerHTML = "";
    if (goals.length > 0) {
        goals.forEach((g) => tableBody.appendChild(createGoalRow(g)));
    }
}

/**
 * Renders the progress chart.
 * @param {string|number} planId The ID of the plan.
 * @param {boolean} isInline True if for the inline view.
 */
async function renderGrowthChart(planId, isInline) {
    if (!planId) return;

    const chartCanvas = document.getElementById(
        isInline ? "growthChartInline" : "growthChart",
    );
    const messageContainer = document.getElementById(
        isInline ? "growthChartMessageInline" : "growthChartMessage",
    );
    let chartInstance = isInline
        ? growthChartInstanceInline
        : growthChartInstance;

    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    try {
        const response = await fetch(apiUrl(`/api/v1/plans/${planId}/progress-chart`));
        const result = await response.json();

        if (
            result.data &&
            Array.isArray(result.data) &&
            result.data.length > 0
        ) {
            chartCanvas.style.display = "block";
            messageContainer.style.display = "none";
            const turns = result.data;
            const ctx = chartCanvas.getContext("2d");
            const getCssVar = (v) =>
                getComputedStyle(document.documentElement)
                    .getPropertyValue(v)
                    .trim();

            const newChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: turns.map((t) => `T${t.turn}`),
                    datasets: [
                        {
                            label: "Speed",
                            data: turns.map((t) => t.speed),
                            borderColor: getCssVar("--stat-speed-color"),
                            tension: 0.3,
                            borderWidth: 2,
                        },
                        {
                            label: "Stamina",
                            data: turns.map((t) => t.stamina),
                            borderColor: getCssVar("--stat-stamina-color"),
                            tension: 0.3,
                            borderWidth: 2,
                        },
                        {
                            label: "Power",
                            data: turns.map((t) => t.power),
                            borderColor: getCssVar("--stat-power-color"),
                            tension: 0.3,
                            borderWidth: 2,
                        },
                        {
                            label: "Guts",
                            data: turns.map((t) => t.guts),
                            borderColor: getCssVar("--stat-guts-color"),
                            tension: 0.3,
                            borderWidth: 2,
                        },
                        {
                            label: "Wit",
                            data: turns.map((t) => t.wit),
                            borderColor: getCssVar("--stat-wit-color"),
                            tension: 0.3,
                            borderWidth: 2,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                usePointStyle: true,
                                color: getCssVar("--bs-body-color"),
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: getCssVar(
                                    "--bs-border-color-translucent",
                                ),
                            },
                        },
                        x: {
                            grid: {
                                color: getCssVar(
                                    "--bs-border-color-translucent",
                                ),
                            },
                        },
                    },
                },
            });

            if (isInline) {
                growthChartInstanceInline = newChart;
            } else {
                growthChartInstance = newChart;
            }
        } else {
            chartCanvas.style.display = "none";
            messageContainer.style.display = "block";
            messageContainer.innerHTML =
                '<p class="text-muted text-center mt-4">No progression data available.</p>';
        }
    } catch (error) {
        chartCanvas.style.display = "none";
        messageContainer.style.display = "block";
        messageContainer.innerHTML =
            '<p class="text-danger text-center mt-4">Could not load chart data.</p>';
        console.error("Error loading chart:", error);
    }
}

/**
 * Creates a new HTML table row for the Predictions table.
 * @param {object} [prediction={}] Optional data to populate the row.
 * @returns {HTMLTableRowElement}
 */
function createPredictionRow(prediction = {}) {
    const row = document.createElement("tr");
    const icons = window.plannerData?.predictionIcons || [
        "▲",
        "●",
        "◯",
        "△",
        "✕",
    ];
    const createSelect = (name, selectedValue) => {
        const options = icons
            .map(
                (icon) =>
                    `<option value="${icon}" ${icon === selectedValue ? "selected" : ""}>${icon}</option>`,
            )
            .join("");
        return `<select class="form-select form-select-sm" name="predictions[][${name}]">${options}</select>`;
    };
    row.innerHTML = `
        <td><input type="text" class="form-control form-control-sm" name="predictions[][race_name]" value="${escapeHtml(prediction.race_name || "")}"></td>
        <td><input type="text" class="form-control form-control-sm" name="predictions[][venue]" value="${escapeHtml(prediction.venue || "")}"></td>
        <td><input type="text" class="form-control form-control-sm" name="predictions[][ground]" value="${escapeHtml(prediction.ground || "")}"></td>
        <td><input type="text" class="form-control form-control-sm" name="predictions[][distance]" value="${escapeHtml(prediction.distance || "")}"></td>
        <td>${createSelect("speed", prediction.speed)}</td>
        <td>${createSelect("stamina", prediction.stamina)}</td>
        <td>${createSelect("power", prediction.power)}</td>
        <td><input type="text" class="form-control form-control-sm" name="predictions[][comment]" value="${escapeHtml(prediction.comment || "")}"></td>
        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-prediction-btn p-0 px-1"><i class="bi bi-x-circle"></i></button></td>`;
    return row;
}

/**
 * Creates a new HTML table row for the Goals table.
 * @param {object} [goal={}] Optional data to populate the row.
 * @returns {HTMLTableRowElement}
 */
function createGoalRow(goal = {}) {
    const row = document.createElement("tr");
    row.innerHTML = `
        <td><input type="text" class="form-control form-control-sm" name="goals[][goal]" value="${escapeHtml(goal.goal || "")}"></td>
        <td><input type="text" class="form-control form-control-sm" name="goals[][result]" value="${escapeHtml(goal.result || "")}"></td>
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
    url.searchParams.delete("plan_id");
    url.searchParams.delete("view_plan_id");
    if (planId) {
        url.searchParams.set(isInline ? "view_plan_id" : "plan_id", planId);
    }
    history.pushState({}, "", url);
}

/**
 * Resets the tabs in the plan details form to the default state.
 * @param {boolean} isInline True if resetting the inline form.
 */
function resetFormTabs(isInline) {
    const containerId = isInline ? "planInlineDetails" : "planDetailsModal";
    const container = document.getElementById(containerId);
    if (!container) return;

    container
        .querySelectorAll(".nav-link")
        .forEach((btn) => btn.classList.remove("active"));
    container
        .querySelectorAll(".tab-pane")
        .forEach((pane) => pane.classList.remove("show", "active"));

    // Support both anchor-based and button-based Bootstrap tab toggles
    let generalTabBtn = container.querySelector('.nav-link[href*="general"]');
    if (!generalTabBtn) {
        generalTabBtn = container.querySelector('.nav-link[data-bs-target*="general"]');
    }
    generalTabBtn?.classList.add("active");
    let generalTabPaneId = generalTabBtn?.getAttribute("href");
    if (!generalTabPaneId) {
        generalTabPaneId = generalTabBtn?.getAttribute("data-bs-target");
    }
    if (generalTabPaneId) {
        document.querySelector(generalTabPaneId)?.classList.add("show", "active");
    }
}

/**
 * Shows a Bootstrap modal as a temporary message box.
 * @param {string} message The message to display.
 * @param {string} type The alert type ('success', 'danger', 'warning', 'info').
 */
/**
 * Shows a SweetAlert2 popup as a temporary message box.
 * @param {string} message The message to display.
 * @param {string} type The alert type ('success', 'danger', 'warning', 'info').
 */
function showMessageBox(message, type = "success") {
    if (window.Swal) {
        let icon = type;
        // Map Bootstrap alert types to SweetAlert2 icons
        if (type === "danger") icon = "error";
        if (type === "info") icon = "info";
        if (type === "warning") icon = "warning";
        if (type === "success") icon = "success";
        Swal.fire({
            text: message,
            icon: icon,
            timer: 3000,
            showConfirmButton: false,
            position: 'top',
            toast: true
        });
    }
}

// Expose a subset of helpers for inline scripts if needed
window.UmaPlanner = {
    fetchAndPopulatePlan,
    handleDeletePlan,
};

// Utility: robustly open a modal by id, even if bootstrap is not fully initialized yet
function showModalById(id) {
    const el = document.getElementById(id);
    if (!el) return;
    if (window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
    }
    // Force visible state regardless of Bootstrap presence
    el.classList.add("show");
    el.style.display = "block";
    el.removeAttribute("aria-hidden");
    if (!document.querySelector(".modal-backdrop")) {
        const backdrop = document.createElement("div");
        backdrop.className = "modal-backdrop fade show";
        backdrop.setAttribute("data-temp-backdrop", "true");
        document.body.appendChild(backdrop);
    }
    document.body.classList.add("modal-open");
}
