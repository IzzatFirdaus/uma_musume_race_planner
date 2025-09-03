<?php
// components/x-skill-card.php
// Visual card to preview a skill. Expected variables: $name, $sp, $acquired, $type_color
if (!isset($name)) $name = '';
if (!isset($sp)) $sp = 0;
if (!isset($acquired)) $acquired = false;
if (!isset($type_color)) $type_color = null; // allow CSS var fallback
?>
<div class="skill-card mb-2 v8-skill-card" role="group" aria-label="Skill: <?= htmlspecialchars($name) ?>" tabindex="0">
  <div class="d-flex align-items-center p-2 v8-gradient-skill" style="border-left: 6px solid <?= $type_color ? htmlspecialchars($type_color) : 'var(--motif-primary, #28a745)' ?>; border-radius: .75rem; box-shadow: 0 4px 16px rgba(40,167,69,0.08); transition: box-shadow 0.3s, transform 0.2s;">
    <div class="me-3">
      <div class="fw-bold small mb-0 text-truncate v8-gradient-text" style="max-width:12rem;">
        <?= htmlspecialchars($name) ?>
        <?php if ($acquired): ?><span class="new-badge" aria-label="Acquired">NEW</span><?php endif; ?>
      </div>
      <div class="text-muted small"><?= intval($sp) ?> SP <?= $acquired ? '<span class="text-success">• Acquired</span>' : '' ?></div>
    </div>
    <div class="ms-auto d-flex gap-2 align-items-center">
      <button type="button" class="btn btn-sm button-pill v8-animated-pill v8-tap-feedback btn-view-skill" aria-label="View <?= htmlspecialchars($name) ?>">View</button>
    </div>
  </div>
</div>
