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

  document.addEventListener('DOMContentLoaded', function(){
    bindTapFeedback(document);

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
