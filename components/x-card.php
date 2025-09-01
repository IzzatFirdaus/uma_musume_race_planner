<?php
// components/x-card.php
// Simple plan card used in plan list or dashboard
// Expected variables: $plan_id, $title, $subtitle, $tags (array)
if (!isset($plan_id)) $plan_id = 0;
if (!isset($title)) $title = 'Untitled';
if (!isset($subtitle)) $subtitle = '';
if (!isset($tags)) $tags = [];
?>
<div class="x-card card mb-3" data-plan-id="<?= intval($plan_id) ?>">
  <div class="card-body d-flex align-items-center justify-content-between">
    <div>
      <h5 class="card-title mb-1"><?= htmlspecialchars($title) ?></h5>
      <p class="card-subtitle text-muted mb-0"><?= htmlspecialchars($subtitle) ?></p>
      <?php if (!empty($tags)): ?>
      <div class="mt-2">
        <?php foreach ($tags as $t): ?>
          <span class="badge rounded-pill bg-secondary me-1"><?= htmlspecialchars($t) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div class="text-end">
      <a href="?plan_id=<?= intval($plan_id) ?>" class="btn btn-sm btn-outline-primary">Open</a>
    </div>
  </div>
</div>
