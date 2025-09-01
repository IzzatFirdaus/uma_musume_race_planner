<?php

// Navbar component with active link highlighting and persistent dark mode toggle

$current_page = basename($_SERVER['PHP_SELF']); // Determine current file for active link

// Optional: read initial dark mode setting from cookie or default to auto
$darkModeSetting = $_COOKIE['darkMode'] ?? 'auto';
$isDarkModeChecked = ($darkModeSetting === 'enabled') ? 'checked' : '';
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="uploads/app_logo/uma_musume_race_planner_logo_64.ico" alt="Logo" style="height: 24px; margin-right: 8px;">
      <span>Uma Musume Planner</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>" href="index.php">
            <i class="bi bi-house-door me-1"></i> Home
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#" id="newPlanBtn">
            <i class="bi bi-plus-circle me-1"></i> New Plan
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'guide.php' ? 'active' : '' ?>" href="guide.php">
            <i class="bi bi-book me-1"></i> Guide
          </a>
        </li>

        <li class="nav-item d-flex align-items-center ms-lg-3">
          <div class="form-check form-switch text-light">
            <input class="form-check-input" type="checkbox" id="darkModeToggle" <?= $isDarkModeChecked ?>>
            <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>
