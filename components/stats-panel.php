<?php

// components/stats-panel.php
?>
<!-- Quick Stats Panel -->
<div class="card mb-4 shadow-sm" role="region" aria-label="Quick Stats">
  <div class="card-header fw-bold">Quick Stats</div>
  <div class="card-body">
    <div class="d-flex justify-content-around text-center flex-wrap gap-3" role="list" aria-label="Stats summary">
      <!-- Total Plans -->
      <div role="listitem">
        <div id="statsPlans" class="fs-1 fw-bold quick-stats-number">
          <?= htmlspecialchars((string)($stats['total_plans'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="text-muted">Plans</div>
      </div>

      <!-- Active Plans -->
      <div role="listitem">
        <div id="statsActive" class="fs-1 fw-bold quick-stats-number text-success">
          <?= htmlspecialchars((string)($stats['active_plans'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="text-muted">Active</div>
      </div>

      <!-- Finished Plans -->
      <div role="listitem">
        <div id="statsFinished" class="fs-1 fw-bold quick-stats-number text-primary">
          <?= htmlspecialchars((string)($stats['finished_plans'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="text-muted">Finished</div>
      </div>
    </div>
    <div class="mt-3">
      <canvas id="statsChart" aria-label="Plans breakdown chart" role="img"></canvas>
    </div>
  </div>
</div>
