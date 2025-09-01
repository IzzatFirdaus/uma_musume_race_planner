// js/skill-rows.js
// Minimal manager for skill rows: add, remove, and serialize into a hidden input.
(function () {
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  function serializeRows(container) {
    const rows = qsa('.skill-row', container);
    const out = rows.map(r => {
      const name = qs('.skill-name-input', r).value.trim();
      const sp = parseInt(qs('.skill-sp-input', r).value, 10) || 0;
      const acquired = qs('.skill-acquired-toggle', r).checked ? 'yes' : 'no';
      const notesEl = qs('.skill-notes-input', r);
      const tagEl = qs('.skill-tag-input', r);
      const notes = notesEl ? notesEl.value.trim() : '';
      const tag = tagEl ? tagEl.value.trim() : '';
      return { skill_name: name, sp_cost: sp, acquired: acquired, tag: tag, notes: notes };
    }).filter(s => s.skill_name.length > 0);
    // find or create hidden input named 'skills' to match handle_plan_crud.php
    let hidden = qs('input[name="skills"]', container);
    if (!hidden) {
      hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'skills';
      container.appendChild(hidden);
    }
    hidden.value = JSON.stringify(out);
    return hidden.value;
  }

  function init(container) {
    if (!container) return;
    const addBtn = qs('[data-skill-add]', container);
    const list = qs('[data-skill-list]', container);

    function bindRowButtons(row) {
      const removeBtn = qs('.btn-skill-remove', row);
      const acquiredToggle = qs('.skill-acquired-toggle', row);
      if (removeBtn) removeBtn.addEventListener('click', function () { row.remove(); serializeRows(container); });
        if (acquiredToggle) acquiredToggle.addEventListener('change', function () { qs('.skill-acquired-input', row).value = this.checked ? 'yes' : 'no'; serializeRows(container); });
      // autosuggest integration placeholder: if a global `autosuggest` exists, attach to name input
      const nameInput = qs('.skill-name-input', row);
      if (window.autosuggest && typeof window.autosuggest.attach === 'function') {
        try { window.autosuggest.attach(nameInput, 'skill'); } catch (e) { /* ignore */ }
      }
        // update hidden on input change
        ['input', 'change'].forEach(ev => nameInput.addEventListener(ev, () => {
          // copy visible input into hidden canonical field for server-side name
          const hiddenName = qs('.skill-name-hidden', row);
          if (hiddenName) hiddenName.value = nameInput.value.trim();
          serializeRows(container);
        }));
        const spInput = qs('.skill-sp-input', row);
        if (spInput) spInput.addEventListener('input', () => serializeRows(container));
        const tagInput = qs('.skill-tag-input', row);
        if (tagInput) tagInput.addEventListener('input', () => serializeRows(container));
        const notesInput = qs('.skill-notes-input', row);
        if (notesInput) notesInput.addEventListener('input', () => serializeRows(container));

        // keyboard support: Enter on notes adds a new row if this is the last row
        if (notesInput) notesInput.addEventListener('keydown', function (ev) {
          if (ev.key === 'Enter') {
            ev.preventDefault();
            const containerList = row.parentElement;
            const rows = qsa('.skill-row', containerList);
            if (rows[rows.length - 1] === row) {
              const addBtnLocal = container.querySelector('[data-skill-add]');
              if (addBtnLocal) addBtnLocal.click();
              // focus newly added row's name input
              setTimeout(() => {
                const newRow = containerList.querySelector('.skill-row:last-child');
                if (newRow) {
                  const newInput = newRow.querySelector('.skill-name-input');
                  if (newInput) newInput.focus();
                }
              }, 50);
            }
          }
        });

        // Accessibility: ARIA attributes for row and controls
        row.setAttribute('role', 'row');
        row.setAttribute('aria-label', 'Skill row');
        if (removeBtn) {
          removeBtn.setAttribute('aria-label', 'Remove skill row');
          removeBtn.setAttribute('tabindex', '0');
        }
        if (acquiredToggle) {
          acquiredToggle.setAttribute('aria-label', 'Skill acquired toggle');
        }
        if (nameInput) {
          nameInput.setAttribute('aria-label', 'Skill name');
        }
        if (spInput) {
          spInput.setAttribute('aria-label', 'Skill SP cost');
        }
        if (tagInput) {
          tagInput.setAttribute('aria-label', 'Skill tag');
        }
        if (notesInput) {
          notesInput.setAttribute('aria-label', 'Skill notes');
        }
        // Announce row add/remove for screen readers
        if (!qs('#skill-row-live-region')) {
          const liveRegion = document.createElement('div');
          liveRegion.id = 'skill-row-live-region';
          liveRegion.className = 'visually-hidden';
          liveRegion.setAttribute('aria-live', 'polite');
          liveRegion.setAttribute('role', 'status');
          container.appendChild(liveRegion);
        }
        if (removeBtn) {
          removeBtn.addEventListener('click', function () {
            const liveRegion = qs('#skill-row-live-region', container);
            if (liveRegion) liveRegion.textContent = 'Skill row removed.';
          });
        }
        if (addBtn) {
          addBtn.addEventListener('click', function () {
            const liveRegion = qs('#skill-row-live-region', container);
            if (liveRegion) liveRegion.textContent = 'Skill row added.';
          });
        }
    }

    if (addBtn && list) {
      addBtn.addEventListener('click', function () {
        const index = list.children.length;
        // fetch template row
        const template = qs('[data-skill-template]', container);
        if (!template) return;
        const clone = template.cloneNode(true);
        clone.removeAttribute('data-skill-template');
        clone.style.display = '';
        // update name attributes/indexes
        qsa('input,button', clone).forEach(el => {
          if (el.name) el.name = el.name.replace(/\[\d+\]/, '[' + index + ']');
        });
        list.appendChild(clone);
        bindRowButtons(clone);
        serializeRows(container);
        // focus the new row name input
        setTimeout(() => {
          const newRow = list.querySelector('.skill-row:last-child');
          if (newRow) {
            const input = newRow.querySelector('.skill-name-input');
            if (input) input.focus();
          }
        }, 40);
      });
    }

    // bind initial rows
    qsa('.skill-row', list).forEach(bindRowButtons);
    // serialize initial
    serializeRows(container);
  }

  // Auto-init any container with data-skill-manager attribute
  document.addEventListener('DOMContentLoaded', function () {
    qsa('[data-skill-manager]').forEach(init);
  });

  // expose for manual init
  window.SkillRows = { init, serialize: serializeRows };
})();
