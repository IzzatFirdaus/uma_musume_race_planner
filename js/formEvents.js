import { fetchJSON } from "./utils.js";
import { API } from "./constants.js";
import { loadPlans, populatePlanModal } from "./script.js";

// Handle Quick Plan Submission
document.getElementById("quickPlanForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const form = e.target;
  const payload = Object.fromEntries(new FormData(form));
  await fetchJSON(API.SAVE_PLAN, payload, "POST");
  form.reset();
  loadPlans();
});

// Handle Full Plan Submission
document.getElementById("planForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const form = e.target;
  const payload = Object.fromEntries(new FormData(form));
  await fetchJSON(API.SAVE_PLAN, payload, "POST");
  bootstrap.Modal.getInstance(document.getElementById("planModal")).hide();
  loadPlans();
});

// Delete Plan
document.body.addEventListener("click", async (e) => {
  const btn = e.target.closest(".delete-btn");
  if (!btn) return;
  const id = btn.dataset.id;
  if (!confirm("Permanently delete this plan?")) return;
  await fetchJSON(API.DELETE_PLAN, { id }, "POST");
  loadPlans();
});

// Edit Plan
document.body.addEventListener("click", async (e) => {
  const btn = e.target.closest(".edit-btn");
  if (!btn) return;
  const id = btn.dataset.id;
  const result = await fetchJSON(`${API.GET_PLAN_DETAILS}?id=${id}`);
  populatePlanModal(result.plan, true);
});

// View Plan
document.body.addEventListener("click", async (e) => {
  const btn = e.target.closest(".view-btn");
  if (!btn) return;
  const id = btn.dataset.id;
  const result = await fetchJSON(`${API.GET_PLAN_DETAILS}?id=${id}`);
  const planDetails = document.getElementById("planDetails");
  if (planDetails) {
    planDetails.style.display = "block";
    planDetails.querySelector(".card-body").innerText = JSON.stringify(result.plan, null, 2); // You can format this nicely later
  }
});

// Close Details Panel
document.getElementById("closeDetailsBtn")?.addEventListener("click", () => {
  document.getElementById("planDetails").style.display = "none";
});
