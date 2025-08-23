<!-- Recent Activity Panel -->
<div class="card recent-activity-card mb-4 shadow-sm" role="region" aria-label="Recent Activity">
  <div class="card-header font-semibold">Recent Activity</div>
  <div class="card-body" id="recentActivity">
    <ul class="list-group list-group-flush" role="list">

      <?php if (isset($activities) && !empty($activities)) :
          ?> // FIX: Changed $activities->rowCount() to !empty($activities)
          <?php foreach ($activities as $activity) : ?>
          <li class="list-group-item d-flex align-items-center" role="listitem">
            <i class="bi <?= htmlspecialchars((string) $activity['icon_class']) ?> me-2" aria-hidden="true"></i>
            <span class="small"><?= htmlspecialchars((string) $activity['description']) ?></span>

            <small class="text-muted small ms-auto">
                <time datetime="<?= htmlspecialchars((string) $activity['timestamp']) ?>"><?= (new DateTime($activity['timestamp']))->format('M d, H:i') ?></time>
            </small>
          </li>
          <?php endforeach; ?>
      <?php else : ?>
        <li class="list-group-item text-muted text-center" role="listitem">
          No recent activity.
        </li>
      <?php endif; ?>

    </ul>
  </div>
</div>
