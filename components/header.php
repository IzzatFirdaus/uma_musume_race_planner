<?php

// Header component including title, subtitle, and theme color injection
$theme_color = getenv('APP_THEME_COLOR') ?: '#6f42c1'; // Fallback to default purple
?>

<link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_32.ico" sizes="32x32">
<link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
<link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
<link rel="apple-touch-icon" href="uploads/app_logo/uma_musume_race_planner_logo_256.png">

<!-- Google Fonts: M PLUS Rounded 1c -->
<link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;700&display=swap" rel="stylesheet">
<style>
  html, body {
    font-family: 'M PLUS Rounded 1c', 'Figtree', 'Segoe UI', 'Tahoma', 'Verdana', sans-serif;
  }
</style>


<style>
  :root {
    --app-theme-color: <?= htmlspecialchars($theme_color); ?>;
  }
  .header-banner {
    background: linear-gradient(90deg, var(--app-theme-color) 0%, #fff 100%);
    color: #222;
    box-shadow: 0 2px 16px rgba(0,0,0,0.08);
    padding: 2rem 0 1rem 0;
    margin-bottom: 2rem;
  }
  .header-banner .logo {
    width: 48px; height: 48px; margin-right: 0.5rem; vertical-align: middle;
  }
  .header-banner h1 {
    font-size: 2.5rem;
    font-weight: 700;
    letter-spacing: -1px;
    margin-bottom: 0.5rem;
  }
  .header-banner p.lead {
    font-size: 1.25rem;
    color: #555;
    margin-bottom: 0;
  }
</style>

<div class="header-banner rounded-3 text-center mb-4">
  <div class="container">
    <h1 class="display-4 fw-bold">
      <img src="uploads/app_logo/uma_musume_race_planner_logo_128.png" alt="Uma Musume Race Planner Logo" class="logo">
      Uma Musume Race Planner
    </h1>
    <p class="lead">Plan, track, and optimize your umamusume's racing career</p>
      <!-- VERSION 9: Energy/Motivation Gauge -->
      <?php $energy = $energy ?? 80; // Example value, replace with actual data ?>
  <div class="v9-energy-gauge mt-3" role="progressbar" aria-label="Energy/Motivation" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= max(0,min(100,$energy)) ?>" tabindex="0">
        <div class="fw-bold mb-1" style="color:var(--color-text-dark)">Energy</div>
        <div class="v9-energy-bar" style="width: 100%; height: 18px; border-radius: 12px; background: var(--gradient-energy-full); box-shadow: 0 2px 8px rgba(253,126,20,0.08); position: relative;">
          <div class="v9-energy-fill" style="width:<?= max(0,min(100,$energy)) ?>%; height: 100%; border-radius: 12px; background: var(--gradient-energy-full); transition: width 0.6s cubic-bezier(.4,0,.2,1); position: absolute; left: 0; top: 0;" aria-hidden="true"></div>
          <div class="v9-energy-label fw-bold" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%); color: #fff; text-shadow: 0 1px 4px #fd7e14; font-size: 1rem;" aria-hidden="false">
            <span aria-hidden="true"><?= max(0,min(100,$energy)) ?>%</span>
            <span class="visually-hidden">Energy level <?= max(0,min(100,$energy)) ?> percent</span>
          </div>
        </div>
      </div>
  </div>
</div>

<!-- V8: M PLUS Rounded 1c font + motif CSS variables and V8 UX script -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;700;900&display=swap" rel="stylesheet">
<style>
:root {
  --motif-primary: <?= htmlspecialchars(getenv('APP_THEME_COLOR') ?: '#2f9d46') ?>;
  --motif-accent: #ff7aa2;
  --motif-bg: #ffffff;
  --gradient-button: linear-gradient(90deg, var(--motif-primary), #7bd389);
  --gradient-card: linear-gradient(180deg, rgba(255,255,255,0.85), rgba(250,250,250,0.95));
}
body { font-family: 'M PLUS Rounded 1c', Figtree, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
</style>
<!-- v8-ux.js is loaded once in index.php -->

<!-- Load skill rows manager (lightweight) -->
<script src="js/skill-rows.js"></script>
<!-- Chart.js for progress chart and a small wrapper -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="js/progress-chart.js"></script>
<!-- Bootstrap Icons (used across the UI for action buttons and badges) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
