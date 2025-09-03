<!-- Quick Stats Panel -->
<div class="card mb-4 shadow-sm v8-plan-card">
  <div class="card-header fw-bold v8-gradient-header v8-gradient-text">Quick Stats</div>
  <div class="card-body">
    <div class="d-flex justify-content-around text-center flex-wrap gap-3">

      <!-- Total Plans -->
      <div>
        <div id="statsPlans" class="fs-1 fw-bold quick-stats-number v8-gradient-text">
          <?= htmlspecialchars($stats['total_plans'] ?? 0) ?>
        </div>
        <div class="text-muted">Plans</div>
      </div>

      <!-- Active Plans -->
      <div>
        <div id="statsActive" class="fs-1 fw-bold quick-stats-number text-success v8-gradient-text">
          <?= htmlspecialchars($stats['active_plans'] ?? 0) ?>
        </div>
        <div class="text-muted">Active</div>
      </div>

      <!-- Finished Plans -->
      <div>
        <div id="statsFinished" class="fs-1 fw-bold quick-stats-number text-primary v8-gradient-text">
          <?= htmlspecialchars($stats['finished_plans'] ?? 0) ?>
        </div>
        <div class="text-muted">Finished</div>
      </div>

    </div>
  </div>
</div>
