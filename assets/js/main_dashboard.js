// Main dashboard event handlers
// Handles form submissions and global interactions for the index page

(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {

      // Handle navbar "New Plan" button
        const newPlanBtn = document.getElementById('newPlanBtn');
        if (newPlanBtn) {
            newPlanBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const modal = new bootstrap.Modal(document.getElementById('createPlanModal'));
                modal.show();
            });
        }

      // Handle Quick Create Plan form submission
        const quickCreateForm = document.getElementById('quickCreatePlanForm');
        if (quickCreateForm) {
            quickCreateForm.addEventListener('submit', async(e) => {
                e.preventDefault();

                const formData = new FormData(quickCreateForm);
                const data = Object.fromEntries(formData.entries());

                try {
                    const base = (window.APP_CONFIG && window.APP_CONFIG.API_BASE) || window.APP_API_BASE || '/api';
                    const response = await fetch(`${base}/plan.php?action=create`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();
                    window.lastPlanCreateResult = result;
                    window.lastPlanCreateError = null;

                    if (result.success) {
                          // Close modal
                          const modal = bootstrap.Modal.getInstance(document.getElementById('createPlanModal'));
                        if (modal) {
                            modal.hide();
                        }

                          // Show success message
                          const messageBox = document.getElementById('messageBoxModal');
                        if (messageBox) {
                            document.getElementById('messageBoxBody').textContent = 'Plan created successfully!';
                            new bootstrap.Modal(messageBox).show();
                        }

                        // Trigger plan list refresh
                        document.dispatchEvent(new CustomEvent('planUpdated'));

                        // Reset form
                        quickCreateForm.reset();
                        quickCreateForm.classList.remove('was-validated');
                    } else {
                        window.lastPlanCreateError = result.message || 'Unknown error';
                        import('sweetalert2').then(Swal => {
                            Swal.default.fire({
                                title: 'Error',
                                text: 'Failed to create plan: ' + (result.message || 'Unknown error'),
                                icon: 'error',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        });
                    }
                } catch (error) {
                    window.lastPlanCreateError = error?.message || error;
                    console.error('Error creating plan:', error);
                    import('sweetalert2').then(Swal => {
                        Swal.default.fire({
                            title: 'Error',
                            text: 'Failed to create plan. Please try again.',
                            icon: 'error',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                }
            });
        }

    });
})();
