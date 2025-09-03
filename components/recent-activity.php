<!-- Recent Activity Panel -->
<div class="card recent-activity-card mb-4 shadow-sm v8-recent-activity">
  <div class="card-header fw-bold v8-gradient-text">Recent Activity</div>
  <div class="card-body" id="recentActivity">
    <ul class="list-group list-group-flush" role="list">

      <?php if (isset($activities) && !empty($activities)) :
            ?> // FIX: Changed $activities->rowCount() to !empty($activities)
          <?php foreach ($activities as $activity) : ?>
          <li class="list-group-item d-flex align-items-center v8-tap-feedback" role="listitem" tabindex="0">
            <i class="bi <?= htmlspecialchars((string) $activity['icon_class']) ?> me-2" aria-hidden="true"></i>

            <span><?= htmlspecialchars((string) $activity['description']) ?></span>

            <small class="text-muted ms-auto">
                <?= (new DateTime($activity['timestamp']))->format('M d, H:i') ?>
            </small>
          </li>
          <?php endforeach; ?>
      <?php else : ?>
        <li class="list-group-item text-muted text-center v8-tap-feedback" role="listitem">
          No recent activity.
        </li>
      <?php endif; ?>

    </ul>
  </div>
</div>
