<?php
// components/x-skill-card.php
// Visual card to preview a skill. Expected variables: $name, $sp, $acquired, $type_color
if (!isset($name)) $name = '';
if (!isset($sp)) $sp = 0;
if (!isset($acquired)) $acquired = false;
if (!isset($type_color)) $type_color = null; // allow CSS var fallback
?>
<div class="skill-card mb-2" role="group" aria-label="Skill: <?= htmlspecialchars($name) ?>">
  <div class="d-flex align-items-center p-2" style="border-left: 4px solid <?= $type_color ? htmlspecialchars($type_color) : 'var(--color-stat-speed)' ?>; background: linear-gradient(180deg, rgba(255,255,255,0.6), rgba(255,255,255,0.9)); border-radius: .5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
    <div class="me-3">
      <div class="fw-bold small mb-0 text-truncate" style="max-width:12rem"><?= htmlspecialchars($name) ?></div>
      <div class="text-muted small"><?= intval($sp) ?> SP <?= $acquired ? '<span class="text-success">• Acquired</span>' : '' ?></div>
    </div>
    <div class="ms-auto d-flex gap-2 align-items-center">
      <button type="button" class="btn btn-sm button-pill btn-view-skill" aria-label="View <?= htmlspecialchars($name) ?>">View</button>
    </div>
  </div>
</div>
