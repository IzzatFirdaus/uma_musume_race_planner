<div class="card mb-4">
  <div class="card-header">Quick Stats</div>
  <div class="card-body">
    <div class="d-flex justify-content-around text-center">
      <div>
        <div class="fs-1 fw-bold quick-stats-number" id="statsPlans"><?= htmlspecialchars($stats['total_plans'] ?? 0) ?></div>
        <div class="text-muted">Plans</div>
      </div>
      <div>
        <div class="fs-1 fw-bold quick-stats-number" id="statsActive"><?= htmlspecialchars($stats['active_plans'] ?? 0) ?></div>
        <div class="text-muted">Active</div>
      </div>
      <div>
        <div class="fs-1 fw-bold quick-stats-number" id="statsFinished"><?= htmlspecialchars($stats['finished_plans'] ?? 0) ?></div>
        <div class="text-muted">Finished</div>
      </div>
    </div>
  </div>
</div>