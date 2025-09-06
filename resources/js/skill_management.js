/**
 * js/skill_management.js
 *
 * Handles the dynamic UI for adding and removing skill rows in the plan forms.
 * This file is an ES6 module.
 */

// --- UPDATED: Import dependencies explicitly ---
import { attachAutosuggest } from "./autosuggest.js";
import { escapeHtml } from "./utils.js";

/**
 * Initializes the skill management UI for a specific table.
 * @param {string} tableId The ID of the skills table.
 * @param {string} addBtnId The ID of the "Add Skill" button.
 */
export function initializeSkillManagement(tableId, addBtnId) {
    const addSkillBtn = document.getElementById(addBtnId);
    const skillsTableBody = document.querySelector(`#${tableId} tbody`);

    if (!addSkillBtn || !skillsTableBody) {
        // Fail silently if the elements aren't on the current page.
        return;
    }

    addSkillBtn.addEventListener("click", () => {
        addSkillRow();
    });

    // Add event listener for removing rows using event delegation.
    skillsTableBody.addEventListener("click", (e) => {
        if (e.target.closest(".remove-skill-btn")) {
            e.target.closest("tr").remove();
        }
    });

    /**
     * Adds a new row to the skills table.
     * @param {object} skill The skill data to populate the row with.
     */
    function addSkillRow(
        skill = {
            skill_name: "",
            sp_cost: "",
            acquired: "no",
            tag: "",
            notes: "",
        },
    ) {
        const newRow = skillsTableBody.insertRow();
        const uniqueId = `skill_${Date.now()}`; // Unique name for form inputs

        // The HTML for a new row.
        newRow.innerHTML = `
            <td>
                <input type="text" name="skills[${uniqueId}][name]" class="form-control skill-name-input" id="skillName_${uniqueId}" value="${escapeHtml(skill.skill_name)}" placeholder="Type skill name...">
            </td>
            <td>
                <input type="number" name="skills[${uniqueId}][sp_cost]" class="form-control" value="${escapeHtml(skill.sp_cost)}">
            </td>
            <td class="text-center">
                <input type="checkbox" name="skills[${uniqueId}][acquired]" class="form-check-input" value="yes" ${skill.acquired === "yes" ? "checked" : ""}>
            </td>
            <td>
                <input type="text" name="skills[${uniqueId}][tag]" class="form-control skill-tag" value="${escapeHtml(skill.tag)}" readonly>
            </td>
            <td>
                <input type="text" name="skills[${uniqueId}][notes]" class="form-control skill-notes" value="${escapeHtml(skill.notes)}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-skill-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        const skillNameInput = newRow.querySelector(".skill-name-input");
        const notesInput = newRow.querySelector(".skill-notes");
        const tagInput = newRow.querySelector(".skill-tag");

        // Attach autosuggest to the newly created input field.
        attachAutosuggest(skillNameInput, "skill_name", (selectedSkill) => {
            // This callback runs when a user selects a suggestion.
            notesInput.value = selectedSkill.description || "";
            tagInput.value = selectedSkill.tag || "";
        });
    }
}
