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
      quickCreateForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(quickCreateForm);
        const data = Object.fromEntries(formData.entries());
        
        try {
          const response = await fetch(`${window.APP_API_BASE}/plan.php?action=create`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
          });
          
          const result = await response.json();
          
          if (result.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('createPlanModal'));
            if (modal) modal.hide();
            
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
            alert('Failed to create plan: ' + (result.message || 'Unknown error'));
          }
          
        } catch (error) {
          console.error('Error creating plan:', error);
          alert('Failed to create plan. Please try again.');
        }
      });
    }
    
  });
})();
