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