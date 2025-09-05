<?php

// components/stats-panel.php
?>

<!-- Quick Stats Panel (MYDS compliant) -->
<section class="card mb-4 shadow-sm" aria-labelledby="quickStatsTitle">
  <header class="card-header fw-bold" id="quickStatsTitle">Quick Stats</header>
  <div class="card-body">
  <ul class="d-flex justify-content-around text-center flex-wrap gap-3 list-unstyled"
    aria-label="Stats summary">
      <!-- Total Plans -->
      <li>
        <span id="statsPlans" class="fs-1 fw-bold quick-stats-number" aria-label="Total plans">
          <?= htmlspecialchars((string)($stats['total_plans'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <span class="quick-stats-label" aria-label="Plans label">Plans</span>
      </li>

      <!-- Active Plans -->
      <li>
        <span id="statsActive" class="fs-1 fw-bold quick-stats-number text-success" aria-label="Active plans">
          <?= htmlspecialchars((string)($stats['active_plans'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <span class="quick-stats-label" aria-label="Active label">Active</span>
      </li>

      <!-- Finished Plans -->
      <li>
        <span id="statsFinished" class="fs-1 fw-bold quick-stats-number text-primary" aria-label="Finished plans">
          <?= htmlspecialchars((string)($stats['finished_plans'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <span class="quick-stats-label" aria-label="Finished label">Finished</span>
      </li>
    </ul>
    <div class="mt-3">
      <canvas id="statsChart" aria-label="Plans breakdown chart" role="img"></canvas>
    </div>
  </div>
</section>
