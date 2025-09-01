<?php
// components/x-stat-bar.php
// Simple stat bar component
// Usage: include 'components/x-stat-bar.php'; with $label, $value, $max, $color variables set

if (!isset($label)) $label = 'Stat';
if (!isset($value)) $value = 0;
if (!isset($max)) $max = 100;
if (!isset($color)) $color = '#0d6efd'; // default bootstrap blue

$pct = $max > 0 ? round(($value / $max) * 100) : 0;
?>
<div class="stat-bar mb-2" aria-label="<?= htmlspecialchars($label) ?>: <?= htmlspecialchars($value) ?> of <?= htmlspecialchars($max) ?>">
  <div class="d-flex justify-content-between small mb-1">
    <div class="stat-label"><?= htmlspecialchars($label) ?></div>
    <div class="stat-value"><?= htmlspecialchars($value) ?></div>
  </div>
  <div class="progress" style="height:10px; background:#eee; border-radius:8px;">
    <div class="progress-bar" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $pct ?>%; background: <?= htmlspecialchars($color) ?>; border-radius:8px;"></div>
  </div>
</div>
