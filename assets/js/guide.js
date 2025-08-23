// guide.js
// Handles guide page logic and accessibility enhancements

document.addEventListener('DOMContentLoaded', function () {
  // Example: Add ARIA attributes to guide sections
  const guideSections = document.querySelectorAll('.guide-section');
  guideSections.forEach(section => {
    section.setAttribute('role', 'region');
    section.setAttribute('aria-label', section.dataset.label || 'Guide Section');
  });

  // Add keyboard navigation for guide tabs (if any)
  const guideTabs = document.querySelectorAll('.guide-tab');
  guideTabs.forEach(tab => {
    tab.setAttribute('tabindex', '0');
    tab.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        tab.click();
      }
    });
  });
});
