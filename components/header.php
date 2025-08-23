<?php
$theme_color = getenv('APP_THEME_COLOR') ?: '#6f42c1';
$isPublic = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/public/') !== false;
$base = $isPublic ? '../' : '';
?>
<!-- MYDS Typography Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<!-- Favicons -->
<link rel="icon" href="<?= $base ?>assets/favicon.ico" sizes="32x32">
<link rel="icon" href="<?= $base ?>assets/images/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
<link rel="icon" href="<?= $base ?>assets/images/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
<link rel="apple-touch-icon" href="<?= $base ?>assets/images/app_logo/uma_musume_race_planner_logo_256.png">

<!-- MYDS Base Styles -->
<link rel="stylesheet" href="<?= $base ?>assets/css/myds-base.css">

<!-- Theme Variables -->
<style>
  :root {
    --app-theme-color: <?= htmlspecialchars($theme_color); ?>;
  }
</style>
<!-- Skip link for keyboard users -->
<a class="skip-link" href="#mainContent">Skip to main content</a>

<header class="header-banner rounded-3 text-center mb-4" role="banner" aria-label="Site header">
  <div class="container">
    <h1 id="siteTitle" class="display-4 fw-bold site-title">
      <img src="<?= $base ?>assets/images/app_logo/uma_musume_race_planner_logo_128.png" alt="Uma Musume Race Planner Logo" class="logo">
      <span class="site-title-text">Uma Musume Race Planner</span>
    </h1>
    <p class="lead site-description">Plan, track, and optimize your umamusume's racing career</p>
  </div>
</header>