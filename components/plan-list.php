<?php

// components/plan-list.php
?>

<div id="planListContainer">
  <div class="card shadow-sm mb-4" aria-labelledby="plans-heading">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 id="plans-heading" class="mb-0">
        <i class="bi bi-card-checklist me-2" aria-hidden="true"></i>
        Your Race Plans
      </h5>
      <button class="btn btn-sm button-pill" id="createPlanBtn" aria-label="Create new plan">
        <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Create New
      </button>
    </div>

    <div class="card-body p-3">
      <div class="plan-filters mb-3" role="tablist" aria-label="Plan filters">
        <div id="plan-filter-buttons" class="d-flex" role="group">
          <button type="button" class="tab-pill active" data-filter="all" aria-pressed="true">All</button>
          <button type="button" class="tab-pill" data-filter="Active" aria-pressed="false">Active</button>
          <button type="button" class="tab-pill" data-filter="Planning" aria-pressed="false">Planning</button>
          <button type="button" class="tab-pill" data-filter="Finished" aria-pressed="false">Finished</button>
        </div>
      </div>

      <div id="planGrid" class="card-grid" aria-live="polite">
        <!-- Plan cards will be injected here by JS -->
      </div>

      <div id="planListEmpty" class="text-center text-muted py-4" style="display:none;">
        No matching plans found.
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const planGrid = document.getElementById('planGrid');
  const planListEmpty = document.getElementById('planListEmpty');
  const filterButtonGroup = document.getElementById('plan-filter-buttons');
  let allPlans = [];

  // Early exit if not present (fail-safe for reusability on other pages)
  if (!planGrid || !filterButtonGroup) return;

  // Inject dynamic CSS styles for stat bars and thumbnails
  const style = document.createElement('style');
  style.textContent = `
    .plan-list-thumbnail-container { width: 64px; height: 64px; border-radius: .5rem; overflow: hidden; flex-shrink: 0; display:flex; align-items:center; justify-content:center; }
    .plan-list-thumbnail { width:100%; height:100%; object-fit:cover; display:block; }
    .plan-list-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background-color:#e9ecef; border-radius:.5rem; font-size:1.25rem; color:#adb5bd; }
    body.dark-mode .plan-list-placeholder { background-color: rgba(255,255,255,0.04); color: var(--color-text-muted-dark); }
    .plan-stat-bars { display:flex; gap:.5rem; margin-top:.5rem; align-items:center; }
    .card-grid { display:grid; grid-template-columns:1fr; gap:1rem; }
    @media(min-width:768px){ .card-grid{ grid-template-columns:1fr 1fr; } }
    .plan-card { padding:1rem; display:flex; gap:1rem; align-items:center; background: #fff; border-radius:.75rem; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    .plan-card .meta { flex:1; }
    .plan-card .actions { display:flex; gap:.5rem; }
    .tab-pill { border-radius: 999px; background:#f6f6fa; color:#333; padding:.35rem .75rem; font-weight:500; margin-right:.5rem; border: none; }
    .tab-pill[aria-pressed="true"]{ background: var(--color-stat-speed); color:#fff; }
  `;
  document.head.appendChild(style);

  /**
   * Creates a mini progress bar based on the stat value
   * @param {number} value The stat value (0-1200).
   * @param {string} statName The name of the stat (e.g., 'speed', 'stamina').
   * @returns {string} HTML string for the stat bar.
   */
  function createMiniStatBar(value, statName) {
    const percent = Math.min((value / 1200) * 100, 100);
    // Directly use CSS variables defined in style.css for consistency
    const statColors = {
      speed: 'var(--color-stat-speed)',    // Updated to --color-stat-speed
      stamina: 'var(--color-stat-stamina)', // Updated to --color-stat-stamina
      power: 'var(--color-stat-power)',    // Updated to --color-stat-power
      guts: 'var(--color-stat-guts)',      // Updated to --color-stat-guts
      wit: 'var(--color-stat-wit)'        // Updated to --color-stat-wit
    };
    return `
      <div class="stat-mini" title="${statName.charAt(0).toUpperCase() + statName.slice(1)}: ${value}" style="flex:1;">
        <div style="width:100%; height:8px; background:#eee; border-radius:6px; overflow:hidden;"><div style="width:${percent}%; height:100%; background:${statColors[statName]}; transition:width 260ms ease;"></div></div>
      </div>
    `;
  }

  /**
   * Render plans into the table
   * @param {Array<Object>} plansToRender Array of plan objects.
   */
  function renderPlanCards(plansToRender) {
    planGrid.innerHTML = '';
    if (!plansToRender.length) {
      planListEmpty.style.display = 'block';
      return;
    }
    planListEmpty.style.display = 'none';

    plansToRender.forEach(plan => {
      const stats = plan.stats || { speed: 0, stamina: 0, power: 0, guts: 0, wit: 0 };
      const card = document.createElement('article');
      card.className = 'plan-card';
      card.setAttribute('role', 'article');
      card.setAttribute('aria-labelledby', `plan-title-${plan.id}`);

      const imageHtml = plan.trainee_image_path
        ? `<div class="plan-list-thumbnail-container"><img src="${plan.trainee_image_path}" class="plan-list-thumbnail" alt="Trainee"></div>`
        : `<div class="plan-list-thumbnail-container"><div class="plan-list-placeholder"><i class="bi bi-person-square" aria-hidden="true"></i></div></div>`;

      card.innerHTML = `
        ${imageHtml}
        <div class="meta">
          <h4 id="plan-title-${plan.id}" class="mb-1" style="font-weight:700; font-size:1.05rem;">🏇 ${plan.plan_title || 'Untitled Plan'}</h4>
          <div class="text-muted small">${plan.name || ''}</div>
          <div class="plan-stat-bars" aria-hidden="true">
            <div style="min-width:36px; font-size:11px; color:var(--color-stat-speed);">SPD</div> ${createMiniStatBar(stats.speed, 'speed')}
            <div style="min-width:36px; font-size:11px; color:var(--color-stat-stamina);">STM</div> ${createMiniStatBar(stats.stamina, 'stamina')}
            <div style="min-width:36px; font-size:11px; color:var(--color-stat-power);">PWR</div> ${createMiniStatBar(stats.power, 'power')}
            <div style="min-width:36px; font-size:11px; color:var(--color-stat-guts);">GTS</div> ${createMiniStatBar(stats.guts, 'guts')}
            <div style="min-width:36px; font-size:11px; color:var(--color-stat-wit);">WIT</div> ${createMiniStatBar(stats.wit, 'wit')}
          </div>
          <div class="mt-2 small text-muted">${plan.race_name || ''}</div>
        </div>
        <div class="actions" style="margin-left:auto;">
          <div class="mb-2 small text-muted text-end">${plan.status || ''}</div>
          <div>
            <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${plan.id}" title="Edit plan ${plan.plan_title}"><i class="bi bi-pencil-square" aria-hidden="true"></i><span class="visually-hidden">Edit</span></button>
            <button class="btn btn-sm btn-outline-info view-inline-btn" data-id="${plan.id}" title="View plan ${plan.plan_title}"><i class="bi bi-eye" aria-hidden="true"></i><span class="visually-hidden">View</span></button>
            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${plan.id}" title="Delete plan ${plan.plan_title}"><i class="bi bi-trash" aria-hidden="true"></i><span class="visually-hidden">Delete</span></button>
          </div>
        </div>
      `;

      planGrid.appendChild(card);
    });
  }

  /**
   * Load plans from backend
   */
  async function loadPlans() {
    try {
      const res = await fetch('get_plans.php');
      const result = await res.json();

      if (result.success) {
        allPlans = result.plans;
        renderPlanCards(allPlans);
      } else {
        throw new Error(result.message || 'Unknown error');
      }
    } catch (error) {
      console.error("Failed to load plans:", error);
      planGrid.innerHTML = '';
      planListEmpty.style.display = 'block';
      planListEmpty.textContent = 'Error loading plans.';
    }
  }

  /**
   * Filter buttons logic
   */
  filterButtonGroup.addEventListener('click', e => {
    if (e.target.matches('button[data-filter]')) {
      const filter = e.target.dataset.filter;
      // toggle aria-pressed for accessibility
      filterButtonGroup.querySelectorAll('button[data-filter]').forEach(b => { b.classList.remove('active'); b.setAttribute('aria-pressed','false'); });
      e.target.classList.add('active');
      e.target.setAttribute('aria-pressed','true');

      const filtered = filter === 'all' ? allPlans : allPlans.filter(p => p.status === filter);
      renderPlanCards(filtered);
    }
  });

  /**
   * Listen for external updates (like save/delete)
   */
  document.addEventListener('planUpdated', loadPlans);

  // Initial fetch
  loadPlans();
});
</script>
