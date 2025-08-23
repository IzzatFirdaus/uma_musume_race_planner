<?php
// components/plan-list.php
// Compute base path for assets whether included from /public or root
$isPublic = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/public/') !== false;
$base = $isPublic ? '../' : '';
$baseEsc = htmlspecialchars($base, ENT_QUOTES, 'UTF-8');
?>
<div class="card shadow-sm mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">
      <i class="bi bi-card-checklist me-2" aria-hidden="true"></i>
      Your Race Plans
    </h5>
    <button class="btn btn-sm btn-uma" id="createPlanBtn" aria-label="Create new plan">
      <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Create New
    </button>
  </div>

  <div class="card-body p-0">

    <div class="plan-filters p-3 border-bottom">
      <div class="btn-group" role="group" id="plan-filter-buttons" aria-label="Filter plans by status">
        <button type="button" class="btn btn-sm btn-outline-secondary active" data-filter="all" aria-pressed="true">All</button>
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
<script src="<?= $baseEsc ?>assets/js/plan_list.js" defer></script>