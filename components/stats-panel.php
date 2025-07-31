<!-- Quick Stats Panel -->
<div class="card mb-4 shadow-sm">
  <div class="card-header fw-bold">Quick Stats</div>
  <div class="card-body">
    <div class="d-flex justify-content-around text-center flex-wrap gap-3">

      <!-- Total Plans -->
      <div>
        <div id="statsPlans" class="fs-1 fw-bold quick-stats-number">
          <?= htmlspecialchars($stats['total_plans'] ?? 0) ?>
        </div>
        <div class="text-muted">Plans</div>
      </div>

      <!-- Active Plans -->
      <div>
        <div id="statsActive" class="fs-1 fw-bold quick-stats-number text-success">
          <?= htmlspecialchars($stats['active_plans'] ?? 0) ?>
        </div>
        <div class="text-muted">Active</div>
      </div>

      <!-- Finished Plans -->
      <div>
        <div id="statsFinished" class="fs-1 fw-bold quick-stats-number text-primary">
          <?= htmlspecialchars($stats['finished_plans'] ?? 0) ?>
        </div>
        <div class="text-muted">Finished</div>
      </div>

    </div>
  </div>
</div>