// v8-ux.js — small helper to add tap-feedback and respect prefers-reduced-motion
(function(){
  'use strict';

  function addRipple(el, event){
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var rect = el.getBoundingClientRect();
    var ripple = document.createElement('span');
    ripple.className = 'v8-ripple';
    var size = Math.max(rect.width, rect.height) * 1.2;
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (event.clientX - rect.left - size/2) + 'px';
    ripple.style.top = (event.clientY - rect.top - size/2) + 'px';
    el.appendChild(ripple);
    window.setTimeout(function(){ ripple.remove(); }, 600);
  }

  // V9: Continuous icon animation for action bubbles
  function animateActionIcons() {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    document.querySelectorAll('.v9-action-bubble .v9-action-icon').forEach(function(icon) {
      icon.animate([
        { transform: 'scale(1) rotate(0deg)' },
        { transform: 'scale(1.08) rotate(8deg)' },
        { transform: 'scale(1) rotate(0deg)' }
      ], {
        duration: 1800,
        iterations: Infinity,
        easing: 'ease-in-out'
      });
    });
  }

  // V9: Status change animation (e.g., energy bar pulse)
  function animateStatusChanges() {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    document.querySelectorAll('.v9-energy-fill').forEach(function(bar) {
      bar.animate([
        { boxShadow: '0 0 0 0 #fd7e14' },
        { boxShadow: '0 0 12px 2px #fd7e14' },
        { boxShadow: '0 0 0 0 #fd7e14' }
      ], {
        duration: 1200,
        iterations: Infinity,
        easing: 'ease-in-out'
      });
    });
  }

  function bindTapFeedback(root){
    root = root || document;
    var elems = root.querySelectorAll('.v8-tap-feedback');
    elems.forEach(function(el){
      if (el.__v8Bound) return;
      el.__v8Bound = true;
      el.style.position = el.style.position || 'relative';
      el.style.overflow = 'hidden';
      el.addEventListener('pointerdown', function(e){
        addRipple(el, e);
      });
      el.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') {
          // simulate center ripple for keyboard activation
          addRipple(el, { clientX: el.getBoundingClientRect().left + el.offsetWidth/2, clientY: el.getBoundingClientRect().top + el.offsetHeight/2 });
        }
      });
    });
  }

  // Apply a training action: expose for other scripts to call
  function applyTrainingAction(type){
    try {
      // Dispatch the same custom event used by v9-action-row
      document.dispatchEvent(new CustomEvent('v9:action', { detail: { type } }));
    } catch(e){ console.warn('applyTrainingAction failed', e); }
  }

  // Make available globally for simple programmatic activation
  window.UX = window.UX || {};
  window.UX.applyTrainingAction = applyTrainingAction;

  document.addEventListener('DOMContentLoaded', function(){
    bindTapFeedback(document);

    // V9: Animate action icons and status changes
    animateActionIcons();
    animateStatusChanges();

    // Auto-enhance common export/copy controls so they get tap feedback
    ['#exportPlanBtn', '#downloadTxtLink', '#downloadCsvLink', '#addSkillBtn', '#addPredictionBtn', '#addGoalBtn'].forEach(function(sel){
      document.querySelectorAll(sel).forEach(function(el){
        if (!el.classList.contains('v8-tap-feedback')) el.classList.add('v8-tap-feedback');
      });
    });

    // MutationObserver to bind to dynamic content (e.g., modals, added skill rows)
    var mo = new MutationObserver(function(m){
      m.forEach(function(rec){
        if (rec.addedNodes && rec.addedNodes.length) bindTapFeedback(document);
      });
    });
    mo.observe(document.body, { childList: true, subtree: true });
  });

})();
