// V8 A11y + Micro-interactions
// - Modal focus management (initial focus + return focus)
// - Optional focus trap safeguard
// - Reduced-motion-safe ripple feedback for interactive elements

(function () {
  'use strict';

  // Utility: collect tabbable elements within a container
  function getTabbables(container) {
    if (!container) return [];
    const selector = [
      'a[href]',
      'button:not([disabled])',
      'input:not([disabled]):not([type="hidden"])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      '[tabindex]:not([tabindex="-1"])'
    ].join(',');
    return Array.from(container.querySelectorAll(selector))
      .filter(el => !el.hasAttribute('disabled') && el.getAttribute('aria-hidden') !== 'true' && el.offsetParent !== null);
  }

  // Return focus to the element that triggered the modal
  const triggerMap = new WeakMap();

  document.addEventListener('show.bs.modal', function (ev) {
    const modal = ev.target;
    // Store the triggering element for return focus
    const trigger = ev.relatedTarget || document.activeElement;
    if (modal) triggerMap.set(modal, trigger);
  });

  document.addEventListener('shown.bs.modal', function (ev) {
    const modal = ev.target;
    if (!modal) return;

    // Preferred initial focus: element referenced by aria-labelledby (usually the title)
    const labelledby = modal.getAttribute('aria-labelledby');
    if (labelledby) {
      const titleEl = document.getElementById(labelledby);
      if (titleEl) {
        // Ensure focusable
        if (!titleEl.hasAttribute('tabindex')) titleEl.setAttribute('tabindex', '-1');
        titleEl.focus({ preventScroll: false });
        return;
      }
    }

    // Otherwise, focus first tabbable element, or the close button
    const dialog = modal.querySelector('.modal-content') || modal;
    const tabbables = getTabbables(dialog);
    if (tabbables.length) {
      tabbables[0].focus({ preventScroll: false });
    } else {
      const closeBtn = modal.querySelector('[data-bs-dismiss="modal"]');
      if (closeBtn) closeBtn.focus({ preventScroll: false });
    }
  });

  document.addEventListener('hidden.bs.modal', function (ev) {
    const modal = ev.target;
    const trigger = triggerMap.get(modal);
    if (trigger && typeof trigger.focus === 'function') {
      try { trigger.focus({ preventScroll: true }); } catch (_) { /* noop */ }
    }
    triggerMap.delete(modal);
  });

  // Focus trap safeguard (Bootstrap traps focus, this is just a safety net)
  document.addEventListener('keydown', function (ev) {
    if (ev.key !== 'Tab') return;
    const openModal = document.querySelector('.modal.show');
    if (!openModal) return;
    const dialog = openModal.querySelector('.modal-content') || openModal;
    const tabbables = getTabbables(dialog);
    if (!tabbables.length) return;

    const first = tabbables[0];
    const last = tabbables[tabbables.length - 1];
    const active = document.activeElement;
    if (ev.shiftKey && active === first) {
      last.focus();
      ev.preventDefault();
    } else if (!ev.shiftKey && active === last) {
      first.focus();
      ev.preventDefault();
    }
  });

  // Ripple feedback (reduced-motion safe)
  const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!prefersReduced) {
    document.addEventListener('click', function (ev) {
      const btn = ev.target.closest('.btn, .list-group-item-action');
      if (!btn) return;

      const rect = btn.getBoundingClientRect();
      const diameter = Math.max(rect.width, rect.height);
      const x = ev.clientX - rect.left - diameter / 2;
      const y = ev.clientY - rect.top - diameter / 2;

      const ripple = document.createElement('span');
      ripple.style.position = 'absolute';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      ripple.style.width = ripple.style.height = diameter + 'px';
      ripple.style.borderRadius = '50%';
      ripple.style.pointerEvents = 'none';
      ripple.style.background = 'currentColor';
      ripple.style.opacity = '0.25';
      ripple.style.transform = 'scale(0)';
      ripple.style.transition = 'transform 450ms ease-out, opacity 600ms ease-out';

      const computed = window.getComputedStyle(btn);
      const originalPos = computed.position;
      if (originalPos === 'static') btn.style.position = 'relative';
      btn.style.overflow = 'hidden';
      btn.appendChild(ripple);

      requestAnimationFrame(() => {
        ripple.style.transform = 'scale(1)';
        ripple.style.opacity = '0';
      });

      setTimeout(() => {
        ripple.remove();
        // Do not revert overflow/position; minimal side-effect safer across frameworks
      }, 650);
    });
  }
})();
