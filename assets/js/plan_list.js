/* eslint-env browser */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const planTableBody = document.getElementById('planListBody');
    const filterButtonGroup = document.getElementById('plan-filter-buttons');
    let allPlans = [];
    if (!planTableBody || !filterButtonGroup) return;

    const style = document.createElement('style');
    style.textContent = `
      .table-vcenter td { vertical-align: middle; }
      .plan-list-thumbnail-container {
        width: 50px; height: 50px; border-radius: .375rem; overflow: hidden; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
      }
      .plan-list-thumbnail { width: 100%; height: 100%; object-fit: cover; display: block; }
      .plan-list-placeholder {
        width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
        background-color: var(--color-surface-muted); border-radius: .375rem;
        font-size: 1.5rem; color: var(--color-muted);
      }
      .plan-stat-bars { display: flex; gap: 6px; margin-top: 4px; height: 6px; align-items: center; }
      .stat-bar { width: 100%; height: 6px; background: #dee2e6; border-radius: 3px; overflow: hidden; }
      [data-theme="dark"] .stat-bar, .dark-mode .stat-bar { background-color: rgba(255, 255, 255, 0.18); }
    `;
    document.head.appendChild(style);

    const escapeHTML = (str) => String(str ?? '')
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    function createMiniStatBar(value, statName) {
      const v = Number.isFinite(Number(value)) ? Number(value) : 0;
      const percent = Math.min((v / 1200) * 100, 100);
      const statColors = {
        speed: 'var(--stat-speed-color)',
        stamina: 'var(--stat-stamina-color)',
        power: 'var(--stat-power-color)',
        guts: 'var(--stat-guts-color)',
        wit: 'var(--stat-wit-color)'
      };
      const label = statName.charAt(0).toUpperCase() + statName.slice(1);
      return `
        <span class="d-inline-block fw-bold text-muted" style="color: ${statColors[statName]} !important;" aria-hidden="true">${label.slice(0,3).toUpperCase()}</span>
        <div class="stat-bar" title="${label}: ${v}">
          <div style="width: ${percent}%; height: 100%; background-color: ${statColors[statName]};"></div>
        </div>
      `;
    }

    function renderPlanTable(plansToRender) {
      planTableBody.innerHTML = '';
      if (!plansToRender.length) {
        planTableBody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center text-muted p-4">
              No matching plans found.
            </td>
          </tr>`;
        return;
      }

      plansToRender.forEach(plan => {
        const row = document.createElement('tr');
        const title = escapeHTML(plan?.plan_title || 'Untitled Plan');
        const name = escapeHTML(plan?.name || '');
        const raceName = escapeHTML(plan?.race_name || '');
        const status = escapeHTML(plan?.status || '');
        const statusClass = `bg-${status.toLowerCase()}`;
        const imgPath = String(plan?.trainee_image_path || '');
        const stats = plan?.stats || { speed: 0, stamina: 0, power: 0, guts: 0, wit: 0 };
        const imageHtml = imgPath
          ? `<div class="plan-list-thumbnail-container"><img src="${escapeHTML(imgPath)}" class="plan-list-thumbnail" alt="Trainee image"></div>`
          : `<div class="plan-list-thumbnail-container"><div class="plan-list-placeholder"><i class="bi bi-person-square" aria-hidden="true"></i><span class="visually-hidden">No image</span></div></div>`;

        row.innerHTML = `
          <td>${imageHtml}</td>
          <td>
            <strong>${title}</strong>
            <div class="text-muted small">${name}</div>
            <div class="plan-stat-bars" aria-hidden="true">
              ${createMiniStatBar(stats.speed, 'speed')}
              ${createMiniStatBar(stats.stamina, 'stamina')}
              ${createMiniStatBar(stats.power, 'power')}
              ${createMiniStatBar(stats.guts, 'guts')}
              ${createMiniStatBar(stats.wit, 'wit')}
            </div>
          </td>
          <td>
            <span class="badge ${statusClass} rounded-pill">${status}</span>
          </td>
          <td>${raceName}</td>
          <td>
            <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${escapeHTML(plan.id)}" aria-label="Edit plan">
              <i class="bi bi-pencil-square" aria-hidden="true"></i>
            </button>
            <button class="btn btn-sm btn-outline-info view-inline-btn" data-id="${escapeHTML(plan.id)}" aria-label="View plan details inline">
              <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${escapeHTML(plan.id)}" aria-label="Delete plan">
              <i class="bi bi-trash" aria-hidden="true"></i>
            </button>
          </td>
        `;
        planTableBody.appendChild(row);
      });
    }

    async function loadPlans() {
      try {
        const res = await fetch(`${window.APP_API_BASE}/plan.php?action=list`, { headers: { 'Accept': 'application/json' } });
        const result = await res.json();
        if (result?.success) {
          allPlans = Array.isArray(result.plans) ? result.plans : [];
          renderPlanTable(allPlans);
        } else {
          throw new Error(result?.message || 'Unknown error');
        }
      } catch (error) {
        console.error('Failed to load plans:', error);
        planTableBody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center text-danger p-4">
              Error loading plans.
            </td>
          </tr>`;
      }
    }

    filterButtonGroup.addEventListener('click', e => {
      const btn = e.target.closest('button[data-filter]');
      if (!btn) return;
      const filter = btn.dataset.filter;
      filterButtonGroup.querySelector('.active')?.classList.remove('active');
      filterButtonGroup.querySelector('[aria-pressed="true"]')?.setAttribute('aria-pressed', 'false');
      btn.classList.add('active');
      btn.setAttribute('aria-pressed', 'true');
      const filtered = filter === 'all' ? allPlans : allPlans.filter(p => p?.status === filter);
      renderPlanTable(filtered);
    });

    // Event delegation for dynamically created plan action buttons
    planTableBody.addEventListener('click', e => {
      const editBtn = e.target.closest('.edit-btn');
      const viewBtn = e.target.closest('.view-inline-btn');
      const deleteBtn = e.target.closest('.delete-btn');

      if (editBtn) {
        const planId = editBtn.dataset.id;
        if (planId) {
          // Trigger the plan details modal for editing
          const modal = new bootstrap.Modal(document.getElementById('planDetailsModal'));
          // Load plan data and show modal
          loadPlanForEdit(planId).then(() => modal.show());
        }
      } else if (viewBtn) {
        const planId = viewBtn.dataset.id;
        if (planId) {
          // Trigger inline details view
          document.dispatchEvent(new CustomEvent('showPlanInline', { detail: { planId } }));
        }
      } else if (deleteBtn) {
        const planId = deleteBtn.dataset.id;
        if (planId && confirm('Are you sure you want to delete this plan?')) {
          deletePlan(planId);
        }
      }
    });

    async function loadPlanForEdit(planId) {
      try {
        const response = await fetch(`${window.APP_API_BASE}/plan.php?action=get&id=${encodeURIComponent(planId)}`);
        const result = await response.json();
        if (result.success) {
          // Populate modal form fields
          document.getElementById('planId').value = result.plan.id;
          document.getElementById('plan_title').value = result.plan.plan_title || '';
          // Trigger modal content update event
          document.dispatchEvent(new CustomEvent('planDataLoaded', { detail: result.plan }));
        }
      } catch (error) {
        console.error('Failed to load plan for editing:', error);
      }
    }

    async function deletePlan(planId) {
      try {
        const response = await fetch(`${window.APP_API_BASE}/plan.php?action=delete&id=${encodeURIComponent(planId)}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
        });
        const result = await response.json();
        if (result.success) {
          // Reload plans list
          loadPlans();
          // Show success message
          const messageBox = document.getElementById('messageBoxModal');
          if (messageBox) {
            document.getElementById('messageBoxBody').textContent = 'Plan deleted successfully';
            new bootstrap.Modal(messageBox).show();
          }
        } else {
          alert('Failed to delete plan: ' + (result.message || 'Unknown error'));
        }
      } catch (error) {
        console.error('Failed to delete plan:', error);
        alert('Failed to delete plan. Please try again.');
      }
    }

    document.addEventListener('planUpdated', loadPlans);
    
    // Handle Create New Plan button
    const createPlanBtn = document.getElementById('createPlanBtn');
    if (createPlanBtn) {
      createPlanBtn.addEventListener('click', () => {
        const modal = new bootstrap.Modal(document.getElementById('createPlanModal'));
        modal.show();
      });
    }
    
    loadPlans();
  });
})();
