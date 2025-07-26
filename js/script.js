import { fetchJSON } from './utils.js';
import { API } from './constants.js';
import { renderPlanRow, renderStats, renderActivity } from './renderHelpers.js';

// Load and render plans
export async function loadPlans(filters = {})
{
    const url = new URL(API.GET_PLANS, window.location.origin);
    Object.entries(filters).forEach(([key, val]) => val && url.searchParams.set(key, val));
    const data = await fetchJSON(url);

    const tbody = document.getElementById("planListBody");
    if (tbody) {
        tbody.innerHTML = (data ? .plans || []).map(renderPlanRow).join("");
    }

    renderStats(data ? .stats || {});
    renderActivity(data ? .activity || []);
}

// Show plan modal
export function populatePlanModal(plan = {}, isEdit = false)
{
    const modalTitle = document.getElementById("modalTitle");
    modalTitle.textContent = isEdit ? "Edit Plan" : "View Plan";

  // TODO: fill form sections: attributes, skills, etc.
  // You may consider dynamically calling `renderPlanToForm(plan);`

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById("planModal"));
    modal.show();
}

// On DOM ready
document.addEventListener("DOMContentLoaded", () => {
    loadPlans();

    document.getElementById("filterForm") ? .addEventListener("submit", async(e) => {
        e.preventDefault();
        const filters = Object.fromEntries(new FormData(e.target));
        await loadPlans(filters);
    });

  document.getElementById("createPlanBtn") ? .addEventListener("click", () => {
        populatePlanModal({}, false);
    });
});
