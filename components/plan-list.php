<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Your Race Plans</span>
    <button class="btn btn-sm btn-uma" id="createPlanBtn">
      <i class="bi bi-plus-circle"></i> Create New
    </button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover" id="planTable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Career Stage</th>
            <th>Class</th>
            <th>Race</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="planListBody">
          <!-- Plan rows rendered via JS -->
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include_once 'components/plan-details.php'; ?>
