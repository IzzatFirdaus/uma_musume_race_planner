<?php
// components/x-skill-card.php
// Visual card to preview a skill. Expected variables: $name, $sp, $acquired, $type_color
if (!isset($name)) $name = '';
if (!isset($sp)) $sp = 0;
if (!isset($acquired)) $acquired = false;
if (!isset($type_color)) $type_color = '#0d6efd';
?>
<div class="skill-card card mb-2" style="border-left: 4px solid <?= htmlspecialchars($type_color) ?>;">
  <div class="card-body d-flex align-items-center justify-content-between p-2">
    <div>
      <div class="fw-bold small"><?= htmlspecialchars($name) ?></div>
      <div class="text-muted small">SP: <?= intval($sp) ?> <?= $acquired ? '<span class="text-success">(Acquired)</span>' : '' ?></div>
    </div>
    <div>
      <button type="button" class="btn btn-sm btn-outline-secondary btn-skill-card-action">View</button>
    </div>
  </div>
</div>
