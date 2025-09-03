<!-- Quick Stats Panel -->
<div class="card mb-4 shadow-sm v8-plan-card">
  <div class="card-header fw-bold v8-gradient-header v8-gradient-text">Quick Stats</div>
  <div class="card-body">
    <?php if (isset($stats['speed']) || isset($stats['stamina']) || isset($stats['power']) || isset($stats['guts']) || isset($stats['wit'])): ?>
      <!-- VERSION 9: Horizontal stat panel with color coding and bonuses -->
      <div class="d-flex flex-row justify-content-between align-items-center gap-3 flex-wrap v9-stat-panel">
        <div class="v9-stat-bar speed text-center">
          <span class="v9-action-icon">⚡</span>
          <span class="fw-bold" style="color:var(--color-speed)">Speed</span>
          <span class="fs-5 fw-bold" style="color:var(--color-speed)"><?php echo htmlspecialchars($stats['speed'] ?? 0); ?></span>
          <?php if (!empty($stats['speed_bonus'])): ?><span class="badge bg-success ms-1">+<?php echo htmlspecialchars($stats['speed_bonus']); ?></span><?php endif; ?>
        </div>
        <div class="v9-stat-bar stamina text-center">
          <span class="v9-action-icon">🛡️</span>
          <span class="fw-bold" style="color:var(--color-stamina)">Stamina</span>
          <span class="fs-5 fw-bold" style="color:var(--color-stamina)"><?php echo htmlspecialchars($stats['stamina'] ?? 0); ?></span>
          <?php if (!empty($stats['stamina_bonus'])): ?><span class="badge bg-success ms-1">+<?php echo htmlspecialchars($stats['stamina_bonus']); ?></span><?php endif; ?>
        </div>
        <div class="v9-stat-bar power text-center">
          <span class="v9-action-icon">🔥</span>
          <span class="fw-bold" style="color:var(--color-power)">Power</span>
          <span class="fs-5 fw-bold" style="color:var(--color-power)"><?php echo htmlspecialchars($stats['power'] ?? 0); ?></span>
          <?php if (!empty($stats['power_bonus'])): ?><span class="badge bg-success ms-1">+<?php echo htmlspecialchars($stats['power_bonus']); ?></span><?php endif; ?>
        </div>
        <div class="v9-stat-bar guts text-center">
          <span class="v9-action-icon">💪</span>
          <span class="fw-bold" style="color:var(--color-guts)">Guts</span>
          <span class="fs-5 fw-bold" style="color:var(--color-guts)"><?php echo htmlspecialchars($stats['guts'] ?? 0); ?></span>
          <?php if (!empty($stats['guts_bonus'])): ?><span class="badge bg-success ms-1">+<?php echo htmlspecialchars($stats['guts_bonus']); ?></span><?php endif; ?>
        </div>
        <div class="v9-stat-bar wit text-center">
          <span class="v9-action-icon">🧠</span>
          <span class="fw-bold" style="color:var(--color-wit)">Wit</span>
          <span class="fs-5 fw-bold" style="color:var(--color-wit)"><?php echo htmlspecialchars($stats['wit'] ?? 0); ?></span>
          <?php if (!empty($stats['wit_bonus'])): ?><span class="badge bg-success ms-1">+<?php echo htmlspecialchars($stats['wit_bonus']); ?></span><?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <!-- Fallback: original quick counts -->
      <div class="d-flex justify-content-around text-center flex-wrap gap-3">
        <div>
          <div id="statsPlans" class="fs-1 fw-bold quick-stats-number v8-gradient-text">
            <?php echo htmlspecialchars($stats['total_plans'] ?? 0); ?>
          </div>
          <div class="text-muted">Plans</div>
        </div>
        <div>
          <div id="statsActive" class="fs-1 fw-bold quick-stats-number text-success v8-gradient-text">
            <?php echo htmlspecialchars($stats['active_plans'] ?? 0); ?>
          </div>
          <div class="text-muted">Active</div>
        </div>
        <div>
          <div id="statsFinished" class="fs-1 fw-bold quick-stats-number text-primary v8-gradient-text">
            <?php echo htmlspecialchars($stats['finished_plans'] ?? 0); ?>
          </div>
          <div class="text-muted">Finished</div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
