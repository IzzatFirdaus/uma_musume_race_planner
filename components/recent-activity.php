<?php
// components/recent-activity.php
?>
<!-- Recent Activity Panel -->
<div class="card recent-activity-card mb-4 shadow-sm" role="region" aria-label="Recent Activity">
  <div class="card-header fw-bold">Recent Activity</div>
  <div class="card-body" id="recentActivity">
    <ul class="list-group list-group-flush">
      <?php if (!empty($activities ?? [])) : ?>
            <?php foreach ($activities as $activity) :
                $desc = htmlspecialchars((string)($activity['description'] ?? ''), ENT_QUOTES, 'UTF-8');
                $icon = htmlspecialchars((string)($activity['icon_class'] ?? ''), ENT_QUOTES, 'UTF-8');
                $tsRaw = (string)($activity['timestamp'] ?? '');
                try {
                    $dt = new DateTime($tsRaw);
                    $display = $dt->format('M d, H:i');
                    $iso = $dt->format(DateTime::ATOM);
                } catch (Throwable $e) {
                    $display = htmlspecialchars($tsRaw, ENT_QUOTES, 'UTF-8');
                    $iso = '';
                }
                ?>
          <li class="list-group-item d-flex align-items-center">
            <i class="bi <?= $icon ?> me-2" aria-hidden="true"></i>
            <span><?= $desc ?></span>
            <small class="text-muted ms-auto">
              <time datetime="<?= htmlspecialchars($iso, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($display, ENT_QUOTES, 'UTF-8') ?>
              </time>
            </small>
          </li>
            <?php endforeach; ?>
      <?php else : ?>
        <li class="list-group-item text-muted text-center">
          No recent activity.
        </li>
      <?php endif; ?>
    </ul>
  </div>
</div>
