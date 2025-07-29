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
            <th scope="col" style="width: 60px;"></th>
            <th scope="col">Name</th>
            <th scope="col">Status</th>
            <th scope="col">Next Race</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody id="planListBody">
            </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if these elements exist to avoid errors on other pages
    const planTableBody = document.getElementById('planListBody');
    const filterButtonGroup = document.getElementById('plan-filter-buttons');

    if (!planTableBody || !filterButtonGroup) {
        return; // Exit if the necessary elements for this script aren't on the page
    }

    // --- NEW: Add CSS for our new thumbnails ---
    const style = document.createElement('style');
    style.textContent = `
        .table-vcenter td {
            vertical-align: middle;
        }
        .plan-list-thumbnail, .plan-list-placeholder {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
        .plan-list-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e9ecef;
            border-radius: .375rem; /* Match Bootstrap's .rounded class */
            font-size: 1.75rem;
            color: #adb5bd;
        }
    `;
    document.head.appendChild(style);


    let allPlans = []; // This will store the master list of plans

    /**
     * Helper to create the HTML for a single mini stat bar.
     */
    function createMiniStatBar(value, statName) {
        const percent = (value / 1200) * 100; // Assuming max stat is 1200
        const statColors = {
            speed: 'var(--stat-speed-color)',
            stamina: 'var(--stat-stamina-color)',
            power: 'var(--stat-power-color)',
            guts: 'var(--stat-guts-color)',
            wit: 'var(--stat-wit-color)'
        };
        return `
            <div class="stat-bar" title="${statName.charAt(0).toUpperCase() + statName.slice(1)}: ${value}">
                <div style="width: ${percent}%; height: 100%; background-color: ${statColors[statName]};"></div>
            </div>
        `;
    }

    /**
     * Renders a filtered list of plans to the table.
     */
    function renderPlanTable(plansToRender) {
        planTableBody.innerHTML = ''; // Clear existing rows
        if (plansToRender.length === 0) {
            planTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted p-4">No matching plans found.</td></tr>';
            return;
        }

        plansToRender.forEach(plan => {
            const row = document.createElement('tr');
            
            // Assume the API provides a 'stats' object, default if not present
            const stats = plan.stats || { speed: 0, stamina: 0, power: 0, guts: 0, wit: 0 };
            const statusClass = `bg-${(plan.status || '').toLowerCase()}`;

            // --- NEW: Create image thumbnail or placeholder ---
            const imageHtml = plan.trainee_image_path
                ? `<img src="${plan.trainee_image_path}" class="plan-list-thumbnail rounded" alt="Trainee">`
                : `<div class="plan-list-placeholder"><i class="bi bi-person-square"></i></div>`;

            row.innerHTML = `
                <td>${imageHtml}</td>
                <td>
                    <strong>${plan.plan_title || 'Untitled Plan'}</strong>
                    <div class="text-muted small">${plan.name}</div>
                    <div class="plan-stat-bars">
                        ${createMiniStatBar(stats.speed, 'speed')}
                        ${createMiniStatBar(stats.stamina, 'stamina')}
                        ${createMiniStatBar(stats.power, 'power')}
                        ${createMiniStatBar(stats.guts, 'guts')}
                        ${createMiniStatBar(stats.wit, 'wit')}
                    </div>
                </td>
                <td><span class="badge ${statusClass} rounded-pill">${plan.status || ''}</span></td>
                <td>${plan.race_name || ''}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${plan.id}"><i class="bi bi-pencil-square"></i></button>
                  <button class="btn btn-sm btn-outline-info view-inline-btn" data-id="${plan.id}"><i class="bi bi-eye"></i></button>
                  <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${plan.id}"><i class="bi bi-trash"></i></button>
                </td>
            `;
            planTableBody.appendChild(row);
        });
    }

    /**
     * Fetches all plans from the API and triggers the initial render.
     */
    async function loadPlans() {
        try {
            // IMPORTANT: get_plans.php must be updated to return 'trainee_image_path' for each plan.
            const response = await fetch('get_plans.php');
            const result = await response.json();

            // The API must return 'stats' for each plan for the bars to work.
            // If not, you'll need to modify get_plans.php to join the attributes table.
            if (result.success) {
                allPlans = result.plans; 
                renderPlanTable(allPlans); 
            }
        } catch (error) {
            console.error("Failed to load plans:", error);
            planTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading plans.</td></tr>';
        }
    }

    // Event listener for the filter buttons
    filterButtonGroup.addEventListener('click', (event) => {
        if (event.target.matches('button')) {
            const filter = event.target.dataset.filter;
            
            // Update active button style
            filterButtonGroup.querySelector('.active').classList.remove('active');
            event.target.classList.add('active');

            if (filter === 'all') {
                renderPlanTable(allPlans);
            } else {
                const filteredPlans = allPlans.filter(plan => plan.status === filter);
                renderPlanTable(filteredPlans);
            }
        }
    });

    // --- NEW: Add listener to refresh the plan list after an update ---
    document.addEventListener('planUpdated', loadPlans);

    // Initial load of all plans
    loadPlans();
});
</script>