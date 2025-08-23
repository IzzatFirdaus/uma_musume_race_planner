<?php

// Navbar component with active link highlighting and persistent dark mode toggle
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
$isPublic = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/public/') !== false;
$base = $isPublic ? '../' : '';
$baseEsc = htmlspecialchars($base, ENT_QUOTES, 'UTF-8');
// Maintain backward compatibility with cookie, but app.js uses localStorage.
// Only used to set initial aria-checked state.
$darkModeSetting = $_COOKIE['darkMode'] ?? 'auto';
$isDarkModeChecked = ($darkModeSetting === 'enabled') ? 'true' : 'false';
?>
<nav class="navbar navbar-expand-lg sticky-top" role="navigation" aria-label="Main navigation">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="<?= $isPublic ? 'index.php' : 'index.php' ?>">
      <img src="<?= $baseEsc ?>assets/images/app_logo/uma_musume_race_planner_logo_64.ico"
           alt="Uma Musume Planner Logo" style="height: 24px; margin-right: 8px;" width="24" height="24">
      <span>Uma Musume Planner</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon" aria-hidden="true"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>"
             href="index.php" <?= $current_page === 'index.php' ? 'aria-current="page"' : '' ?>>
            <i class="bi bi-house-door me-1" aria-hidden="true"></i> Home
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#" id="newPlanBtn" aria-label="Create a new plan">
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> New Plan
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= $current_page === 'guide.php' ? 'active' : '' ?>"
             href="guide.php" <?= $current_page === 'guide.php' ? 'aria-current="page"' : '' ?>>
            <i class="bi bi-book me-1" aria-hidden="true"></i> Guide
          </a>
        </li>

        <li class="nav-item ms-lg-3">
          <!-- Theme toggle (consumed by app.js as #darkModeToggle) -->
          <button id="darkModeToggle"
                  class="btn btn-outline-secondary"
                  type="button"
                  role="switch"
                  aria-checked="<?= $isDarkModeChecked ?>"
                  aria-label="Toggle dark mode"
                  title="Toggle dark mode">
            <span class="theme-icon" aria-hidden="true">🌙</span>
            <span class="visually-hidden">Toggle dark mode</span>
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>
