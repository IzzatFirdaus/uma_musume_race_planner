// guide.js
// Handles guide page logic and accessibility enhancements
// Best Practices: ARIA roles, keyboard operability, clean event handling, and robust defaults.

/* eslint-env browser */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    // Add landmark-like semantics to guide sections
    const guideSections = document.querySelectorAll('.guide-section');
    guideSections.forEach((section, i) => {
      section.setAttribute('role', 'region');
      const label = section.dataset.label || section.getAttribute('aria-label') || `Guide Section ${i + 1}`;
      section.setAttribute('aria-label', label);
      // Ensure headings are focusable for quick navigation
      const h = section.querySelector('h1, h2, h3, h4, h5, h6');
      if (h && !h.hasAttribute('tabindex')) h.setAttribute('tabindex', '-1');
    });

    // Keyboard navigation for tabs (if present)
    const tabs = document.querySelectorAll('.guide-tab');
    tabs.forEach((tab) => {
      if (!tab.hasAttribute('tabindex')) tab.setAttribute('tabindex', '0');
      if (!tab.getAttribute('role')) tab.setAttribute('role', 'tab');

      tab.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          tab.click();
        }
        // Optional: Arrow key linear navigation between tabs
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
          e.preventDefault();
          const siblings = Array.from(document.querySelectorAll('.guide-tab'));
          const idx = siblings.indexOf(tab);
          const nextIdx = e.key === 'ArrowRight' ? (idx + 1) % siblings.length : (idx - 1 + siblings.length) % siblings.length;
          siblings[nextIdx]?.focus();
        }
      });
    });
  });
})();