<?php

$theme_color = getenv('APP_THEME_COLOR') ?: '#6f42c1';
$isPublic = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/public/') !== false;
$base = $isPublic ? '../' : '';
$baseEsc = htmlspecialchars($base, ENT_QUOTES, 'UTF-8');
$themeColorEsc = htmlspecialchars($theme_color, ENT_QUOTES, 'UTF-8');
?>
<link rel="icon" href="<?= $baseEsc ?>assets/favicon.ico" sizes="32x32">
<link rel="icon" href="<?= $baseEsc ?>assets/images/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
<link rel="icon" href="<?= $baseEsc ?>assets/images/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
<link rel="apple-touch-icon" href="<?= $baseEsc ?>assets/images/app_logo/uma_musume_race_planner_logo_256.png">

<style>
  :root { --app-theme-color: <?= $themeColorEsc ?>; }
</style>

<div class="header-banner rounded-3 text-center mb-4" role="banner" aria-label="Application header">
  <div class="container">
    <h1 class="display-4 fw-bold">
      <img src="<?= $baseEsc ?>assets/images/app_logo/uma_musume_race_planner_logo_128.png"
           alt="Uma Musume Race Planner Logo" class="logo" width="80" height="80" decoding="async">
      Uma Musume Race Planner
    </h1>
    <p class="lead">Plan, track, and optimize your umamusume's racing career</p>
  </div>
</div>
