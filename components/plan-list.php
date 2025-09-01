<?php

// components/plan-list.php
?>

<div class="card shadow-sm mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bi bi-card-checklist me-2"></i>
      Your Race Plans
    </h5>
    <button class="btn btn-sm btn-uma" id="createPlanBtn">
      <i class="bi bi-plus-circle me-1"></i> Create New
    </button>
  </div>

  <div class="card-body p-0">

    <div class="plan-filters p-3 border-bottom">
      <div class="btn-group" role="group" id="plan-filter-buttons">
        <button type="button" class="btn btn-sm btn-outline-secondary active" data-filter="all">All</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="Active">Active</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="Planning">Planning</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="Finished">Finished</button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-vcenter mb-0" id="planTable">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;"></th>
            <th>Name</th>
            <th>Status</th>
            <th>Next Race</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="planListBody"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const planTableBody = document.getElementById('planListBody');
  const filterButtonGroup = document.getElementById('plan-filter-buttons');
  let allPlans = [];

  // Early exit if not present (fail-safe for reusability on other pages)
  if (!planTableBody || !filterButtonGroup) return;

  // Inject dynamic CSS styles for stat bars and thumbnails
  const style = document.createElement('style');
  style.textContent = `
    .table-vcenter td { vertical-align: middle; }
    .plan-list-thumbnail-container { /* New container for image resizing */
      width: 50px;
      height: 50px;
      border-radius: .375rem; /* Match general border-radius */
      overflow: hidden; /* Crop images to container */
      flex-shrink: 0; /* Prevent container from shrinking */
      display: flex; /* For centering placeholder */
      align-items: center;
      justify-content: center;
    }
    .plan-list-thumbnail {
      width: 100%; /* Make image fill its container */
      height: 100%;
      object-fit: cover; /* Ensure image covers the area, cropping as necessary */
      display: block; /* Remove extra space below image */
    }
    .plan-list-placeholder {
      width: 100%; height: 100%; /* Fill container */
      display: flex; align-items: center; justify-content: center;
      background-color: #e9ecef; border-radius: .375rem;
      font-size: 1.75rem; color: #adb5bd;
    }
    /* Dark mode adjustments for placeholder */
    body.dark-mode .plan-list-placeholder {
      background-color: var(--color-input-bg-dark); /* Use a dark mode appropriate background */
      color: var(--color-text-muted-dark); /* Muted text color for the icon */
    }

    .plan-stat-bars { display: flex; gap: 2px; margin-top: 4px; height: 6px; }
    .stat-bar { width: 100%; height: 6px; background: #dee2e6; border-radius: 3px; overflow: hidden; }
    /* Dark mode adjustments for stat bar background */
    body.dark-mode .stat-bar {
        background-color: rgba(255, 255, 255, 0.18); /* Matches style.css */
    }
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
      <div class="stat-bar" title="${statName.charAt(0).toUpperCase() + statName.slice(1)}: ${value}">
        <div style="width: ${percent}%; height: 100%; background-color: ${statColors[statName]};"></div>
      </div>
    `;
  }

  /**
   * Render plans into the table
   * @param {Array<Object>} plansToRender Array of plan objects.
   */
  function renderPlanTable(plansToRender) {
    planTableBody.innerHTML = ''; // Clear existing

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
      // Ensure stats object is available, default to 0 if not present
      const stats = plan.stats || { speed: 0, stamina: 0, power: 0, guts: 0, wit: 0 };
      const statusClass = `bg-${(plan.status || '').toLowerCase()}`;
      const imageHtml = plan.trainee_image_path
        ? `<div class="plan-list-thumbnail-container"><img src="${plan.trainee_image_path}" class="plan-list-thumbnail" alt="Trainee"></div>`
        : `<div class="plan-list-thumbnail-container"><div class="plan-list-placeholder"><i class="bi bi-person-square"></i></div></div>`;

      row.innerHTML = `
        <td>${imageHtml}</td>
        <td>
          <strong>${plan.plan_title || 'Untitled Plan'}</strong>
          <div class="text-muted small">${plan.name || ''}</div>
          <div class="plan-stat-bars">
            <span class="d-inline-block fw-bold text-muted" style="color: var(--color-stat-speed) !important;">SPD</span> ${createMiniStatBar(stats.speed, 'speed')}
            <span class="d-inline-block fw-bold text-muted" style="color: var(--color-stat-stamina) !important;">STM</span> ${createMiniStatBar(stats.stamina, 'stamina')}
            <span class="d-inline-block fw-bold text-muted" style="color: var(--color-stat-power) !important;">PWR</span> ${createMiniStatBar(stats.power, 'power')}
            <span class="d-inline-block fw-bold text-muted" style="color: var(--color-stat-guts) !important;">GTS</span> ${createMiniStatBar(stats.guts, 'guts')}
            <span class="d-inline-block fw-bold text-muted" style="color: var(--color-stat-wit) !important;">WIT</span> ${createMiniStatBar(stats.wit, 'wit')}
          </div>
        </td>
        <td>
          <span class="badge ${statusClass} rounded-pill">${plan.status || ''}</span>
        </td>
        <td>${plan.race_name || ''}</td>
        <td>
          <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${plan.id}" title="Edit plan ${plan.plan_title}">
            <i class="bi bi-pencil-square" aria-hidden="true"></i>
            <span class="visually-hidden">Edit</span>
          </button>
          <button class="btn btn-sm btn-outline-info view-inline-btn" data-id="${plan.id}" title="View plan ${plan.plan_title}">
            <i class="bi bi-eye" aria-hidden="true"></i>
            <span class="visually-hidden">View</span>
          </button>
          <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${plan.id}" title="Delete plan ${plan.plan_title}">
            <i class="bi bi-trash" aria-hidden="true"></i>
            <span class="visually-hidden">Delete</span>
          </button>
        </td>
      `;
      planTableBody.appendChild(row);
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
        renderPlanTable(allPlans);
      } else {
        throw new Error(result.message || 'Unknown error');
      }
    } catch (error) {
      console.error("Failed to load plans:", error);
      planTableBody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-danger p-4">
            Error loading plans.
          </td>
        </tr>`;
    }
  }

  /**
   * Filter buttons logic
   */
  filterButtonGroup.addEventListener('click', e => {
    if (e.target.matches('button[data-filter]')) {
      const filter = e.target.dataset.filter;
      filterButtonGroup.querySelector('.active')?.classList.remove('active');
      e.target.classList.add('active');

      const filtered = filter === 'all'
        ? allPlans
        : allPlans.filter(p => p.status === filter);

      renderPlanTable(filtered);
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
