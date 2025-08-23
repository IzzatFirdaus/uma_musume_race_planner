<?php
// quick_create_plan_modal.php
// This file assumes $careerStageOptions and $classOptions are available from index.php

// Ensure these variables are available, provide empty arrays as a fallback
$careerStageOptions = $careerStageOptions ?? [];
$classOptions = $classOptions ?? [];

?>

<div class="modal fade" id="createPlanModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-semibold" id="createPlanModalLabel">Quick Create Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="quickCreatePlanForm" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quick_trainee_name" class="form-label">Trainee Name</label>
                        <input type="text" class="form-control" id="quick_trainee_name" name="trainee_name" required aria-describedby="traineeNameFeedback" aria-required="true">
                        <div class="invalid-feedback" id="traineeNameFeedback">
                            Trainee Name is required.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quick_race_name" class="form-label">Next Race Name</label>
                        <input type="text" class="form-control" id="quick_race_name" name="race_name">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_career_stage" class="form-label">Career Stage</label>
                            <select class="form-select" id="quick_career_stage" name="career_stage" required aria-describedby="careerStageFeedback" aria-required="true">
                                <option value="" selected disabled>Select Stage</option> <?php foreach ($careerStageOptions as $option) : ?>
                                    <option value="<?= htmlspecialchars((string) $option['value']) ?>">
                                        <?= htmlspecialchars((string) $option['text']) ?>
                                    </option>
                                                                                         <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="careerStageFeedback">
                                Career Stage is required.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_traineeClass" class="form-label">Class</label>
                            <select class="form-select" id="quick_traineeClass" name="traineeClass" required aria-describedby="classFeedback" aria-required="true">
                                <option value="" selected disabled>Select Class</option> <?php foreach ($classOptions as $option) : ?>
                                    <option value="<?= htmlspecialchars((string) $option['value']) ?>">
                                        <?= htmlspecialchars((string) $option['text']) ?>
                                    </option>
                                                                                         <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="classFeedback">
                                Class is required.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="quickCreateSubmitBtn" aria-label="Create plan">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('quickCreatePlanForm');
    const traineeNameInput = document.getElementById('quick_trainee_name');
    const careerStageSelect = document.getElementById('quick_career_stage');
    const traineeClassSelect = document.getElementById('quick_traineeClass');

    // Add Bootstrap's form validation classes on submission attempt
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault(); // Prevent default submission
            event.stopPropagation(); // Stop event propagation
            form.classList.add('was-validated'); // Add class to show validation feedback
        } else {
            // Form is valid, will be handled by index.php's event listener
            form.classList.remove('was-validated'); // Remove validation class if valid
        }
    }, false); // Use capture phase or default bubble phase is fine

    // Optional: Reset validation state when modal is hidden
    const createPlanModal = document.getElementById('createPlanModal');
    if (createPlanModal) {
        createPlanModal.addEventListener('hidden.bs.modal', function () {
            form.classList.remove('was-validated'); // Remove validation feedback
            form.reset(); // Reset form fields
            // Manually clear invalid states if needed for select elements
            traineeNameInput.classList.remove('is-invalid');
            careerStageSelect.classList.remove('is-invalid');
            traineeClassSelect.classList.remove('is-invalid');
            // Re-select default disabled option if present
            if (careerStageSelect.querySelector('option[value=""][disabled]')) {
                careerStageSelect.value = "";
            }
            if (traineeClassSelect.querySelector('option[value=""][disabled]')) {
                traineeClassSelect.value = "";
            }
        });
    }

    // Add event listeners for instant validation feedback as user types/selects
    traineeNameInput.addEventListener('input', function() {
        if (traineeNameInput.value.trim() !== '') {
            traineeNameInput.classList.remove('is-invalid');
        }
    });

    careerStageSelect.addEventListener('change', function() {
        if (careerStageSelect.value !== '') {
            careerStageSelect.classList.remove('is-invalid');
        }
    });

    traineeClassSelect.addEventListener('change', function() {
        if (traineeClassSelect.value !== '') {
            traineeClassSelect.classList.remove('is-invalid');
        }
    });
});
</script>
<script>
// Quick create modal focus trap
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('createPlanModal');
    if (!modalEl) return;

    let lastFocused = null;

    modalEl.addEventListener('show.bs.modal', () => {
        lastFocused = document.activeElement;
    });

    modalEl.addEventListener('shown.bs.modal', () => {
        const focusable = Array.from(modalEl.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')).filter(el => !el.hasAttribute('disabled'));
        if (focusable.length) focusable[0].focus();
        modalEl.addEventListener('keydown', trap);
    });

    modalEl.addEventListener('hidden.bs.modal', () => {
        modalEl.removeEventListener('keydown', trap);
        if (lastFocused && lastFocused.focus) lastFocused.focus();
    });

    function trap(e) {
        if (e.key !== 'Tab') return;
        const focusable = Array.from(modalEl.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])')).filter(el => !el.hasAttribute('disabled'));
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) {
                last.focus();
                e.preventDefault();
            }
        } else {
            if (document.activeElement === last) {
                first.focus();
                e.preventDefault();
            }
        }
    }
});
</script>