<?php
// components/x-card.php
// Simple plan card used in plan list or dashboard
// Expected variables: $plan_id, $title, $subtitle, $tags (array)
if (!isset($plan_id)) $plan_id = 0;
if (!isset($title)) $title = 'Untitled';
if (!isset($subtitle)) $subtitle = '';
if (!isset($tags)) $tags = [];
?>
<div class="x-plan-card card mb-3" data-plan-id="<?= intval($plan_id) ?>" role="article" aria-labelledby="plan-title-<?= intval($plan_id) ?>">
  <div class="card-body d-flex flex-column flex-md-row align-items-start gap-3">
    <div class="flex-grow-1">
      <h5 id="plan-title-<?= intval($plan_id) ?>" class="mb-1" style="font-weight:700;">🏇 <?= htmlspecialchars($title) ?></h5>
      <p class="text-muted small mb-2"><?= htmlspecialchars($subtitle) ?></p>
      <?php if (!empty($tags)): ?>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($tags as $t): ?>
          <span class="badge rounded-pill" style="background: rgba(0,0,0,0.03); color: inherit; padding: .35rem .6rem; font-size: .75rem;"><?= htmlspecialchars($t) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="text-md-end ms-auto">
      <div class="mb-2 small text-muted">Turn: <strong>5/70</strong> | SP: <strong>320</strong></div>
      <a href="?plan_id=<?= intval($plan_id) ?>" class="btn btn-sm button-pill">Open</a>
    </div>
  </div>
</div>
