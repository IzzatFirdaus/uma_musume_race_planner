(function () {
  'use strict';

  function init() {
    const form = document.getElementById('quickCreatePlanForm');
    if (!form) return;
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
    }, false);

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
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
