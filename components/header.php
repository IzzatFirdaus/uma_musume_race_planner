<?php

// Header component including title, subtitle, and theme color injection
$theme_color = getenv('APP_THEME_COLOR') ?: '#6f42c1'; // Fallback to default purple
?>

<link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_32.ico" sizes="32x32">
<link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
<link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
<link rel="apple-touch-icon" href="uploads/app_logo/uma_musume_race_planner_logo_256.png">


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
  </div>
</div>

<!-- Load skill rows manager (lightweight) -->
<script src="js/skill-rows.js"></script>
<!-- Chart.js for progress chart and a small wrapper -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="js/progress-chart.js"></script>
<!-- Bootstrap Icons (used across the UI for action buttons and badges) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
