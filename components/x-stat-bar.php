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
<div class="stat-bar-component mb-2 v8-stat-bar" role="group" aria-label="<?= htmlspecialchars($label) ?>" tabindex="0">
  <div class="d-flex justify-content-between align-items-center small mb-1">
    <div class="d-flex align-items-center gap-2">
      <span class="stat-icon" aria-hidden="true"><?php
        $icons = ['speed' => '\u26a1','stamina' => '\ud83d\udee1\ufe0f','power' => '\ud83d\udd25','guts' => '\ud83d\udcaa','wit' => '\ud83e\udde0'];
        echo $icons[$statKey] ?? '';
      ?></span>
      <div class="stat-label fw-semibold v8-gradient-text">
        <?= htmlspecialchars($label) ?>
      </div>
    </div>
    <div class="stat-value text-end fw-monospace" style="font-weight:700; color:var(--motif-primary, #28a745); text-shadow:0 1px 4px #fff;">
      <?= htmlspecialchars($value) ?> / <?= htmlspecialchars($max) ?>
    </div>
  </div>

  <div class="relative w-100" style="height: 0.75rem;">
    <div class="progress v8-gradient-bar" style="height:100%; border-radius: 8px; overflow: hidden; background: var(--gradient-stat-bar, linear-gradient(90deg, var(--color-stat-speed) 0%, var(--color-stat-stamina) 25%, var(--color-stat-power) 50%, var(--color-stat-guts) 75%, var(--color-stat-wit) 100%)); transition: box-shadow 0.3s;">
      <div class="progress-fill v8-tap-feedback" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?= $pct ?>%; background: <?= $cssColor ?>; height:100%; transition: width 280ms cubic-bezier(.4,0,.2,1); will-change:width;"></div>
    </div>
  </div>
</div>
