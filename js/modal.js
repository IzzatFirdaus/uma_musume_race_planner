import { fetchJSON, on } from "./utils.js";
import { API } from "./constants.js";
import { populatePlanModal } from "./script.js";

// View or Edit from table buttons
on(".view-btn, .edit-btn", "click", async (e, btn) => {
  const id = btn.dataset.id;
  const isEdit = btn.classList.contains("edit-btn");
  try {
    const response = await fetchJSON(`${API.GET_PLAN_DETAILS}?id=${id}`);
    populatePlanModal(response.plan, isEdit);
  } catch (err) {
    alert("Failed to load plan details.");
    console.error(err);
  }
});
