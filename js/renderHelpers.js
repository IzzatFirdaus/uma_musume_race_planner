import { formatDateTime } from './utils.js';

// Convert status to Bootstrap badge class
function badgeClass(status) {
  return {
    Planning: 'warning',
    Active: 'success',
    Finished: 'primary',
    Draft: 'secondary',
    Abandoned: 'danger'
  }[status] || 'secondary';
}

// Render a single plan table row
export function renderPlanRow(plan) {
  return `
    <tr data-id="${plan.id}">
      <td>${plan.name}</td>
      <td>${plan.career_stage || ''}</td>
      <td>${plan.class?.toUpperCase() || ''}</td>
      <td>${plan.race_name}</td>
      <td><span class="badge bg-${badgeClass(plan.status)}">${plan.status}</span></td>
      <td>
        <button class="btn btn-sm btn-outline-primary view-btn" data-id="${plan.id}"><i class="bi bi-eye"></i></button>
        <button class="btn btn-sm btn-outline-warning edit-btn" data-id="${plan.id}"><i class="bi bi-pencil"></i></button>
        <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${plan.id}"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`;
}

// Render stats into stats panel
export function renderStats(stats = {}) {
  Object.entries(stats).forEach(([key, value]) => {
    const el = document.querySelector(`[data-stat="${key}"]`);
    if (el) el.textContent = value;
  });
}

// Render recent activity list
export function renderActivity(logs = []) {
  const container = document.getElementById('recentActivityList');
  if (!container) return;

  container.innerHTML = logs.map(log => `
    <div class="list-group-item d-flex align-items-start gap-2">
      <i class="bi ${log.icon_class} text-muted fs-4 mt-1"></i>
      <div>
        <p class="mb-1 small text-muted">${formatDateTime(log.timestamp)}</p>
        <p class="mb-0">${log.description}</p>
      </div>
    </div>
  `).join('');
}
