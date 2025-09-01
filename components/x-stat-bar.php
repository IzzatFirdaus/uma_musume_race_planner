<?php
// components/x-stat-bar.php
// Simple stat bar component
// Usage: include 'components/x-stat-bar.php'; with $label, $value, $max, $color variables set

if (!isset($label)) $label = 'Stat';
if (!isset($value)) $value = 0;
if (!isset($max)) $max = 100;
if (!isset($color)) $color = null; // optional override

$pct = $max > 0 ? round(($value / $max) * 100) : 0;

// Map common stat labels to CSS variables when color isn't passed
$statMap = [
  'speed' => '--color-stat-speed',
  'stamina' => '--color-stat-stamina',
  'power' => '--color-stat-power',
  'guts' => '--color-stat-guts',
  'wit' => '--color-stat-wit'
];

$statKey = strtolower(trim($label));
$cssColor = $color ? htmlspecialchars($color) : (isset($statMap[$statKey]) ? "var({$statMap[$statKey]})" : '#0d6efd');

?>
<div class="stat-bar-component mb-2" role="group" aria-label="<?= htmlspecialchars($label) ?>">
  <div class="d-flex justify-content-between align-items-center small mb-1">
    <div class="d-flex align-items-center gap-2">
      <span class="stat-icon" aria-hidden="true"><?php
        // small visual icons for familiar stats (non-essential, decorative)
        $icons = ['speed' => '⚡','stamina' => '🛡️','power' => '🔥','guts' => '💪','wit' => '🧠'];
        echo $icons[$statKey] ?? '';
      ?></span>
      <div class="stat-label fw-semibold"><?= htmlspecialchars($label) ?></div>
    </div>
    <div class="stat-value text-end fw-monospace"><?= htmlspecialchars($value) ?> / <?= htmlspecialchars($max) ?></div>
  </div>

  <div class="relative w-100" style="height: 0.75rem;">
    <div class="progress bg-light" style="height:100%; border-radius: 6px; overflow: hidden;">
      <div class="progress-fill" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $pct ?>%; background: <?= $cssColor ?>; height:100%; transition: width 280ms ease;"></div>
    </div>
  </div>
</div>
