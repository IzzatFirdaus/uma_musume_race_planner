<?php
$current_page = basename($_SERVER['PHP_SELF']);
$darkModeSetting = $_COOKIE['darkMode'] ?? 'auto';
$isDarkModeChecked = ($darkModeSetting === 'enabled') ? 'checked' : '';
?>

<!-- V8 Sidebar for desktop -->
<aside class="d-none d-md-block position-fixed top-0 start-0 vh-100 bg-white border-end shadow-sm v8-sidebar" style="width: 220px; z-index: 1040;" role="navigation" aria-label="Main menu">
  <nav class="nav flex-column py-4" aria-label="Main menu">
    <a class="nav-link py-3 px-4 fw-bold d-flex align-items-center v8-animated-pill v8-tap-feedback <?= $current_page === 'index.php' ? 'active' : '' ?>" href="index.php" tabindex="0" <?= $current_page === 'index.php' ? 'aria-current="page"' : '' ?>>
      <i class="bi bi-house-door me-2" aria-hidden="true"></i> Home
    </a>
    <a class="nav-link py-3 px-4 d-flex align-items-center v8-animated-pill v8-tap-feedback" href="#" id="newPlanBtn" tabindex="0">
      <i class="bi bi-plus-circle me-2" aria-hidden="true"></i> New Plan
    </a>
    <a class="nav-link py-3 px-4 d-flex align-items-center v8-animated-pill v8-tap-feedback <?= $current_page === 'guide.php' ? 'active' : '' ?>" href="guide.php" tabindex="0" <?= $current_page === 'guide.php' ? 'aria-current="page"' : '' ?>>
      <i class="bi bi-book me-2" aria-hidden="true"></i> Guide
    </a>
    <div class="mt-4 px-4">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="darkModeToggleSidebar" <?= $isDarkModeChecked ?> aria-label="Toggle dark mode">
        <label class="form-check-label" for="darkModeToggleSidebar">Dark Mode</label>
      </div>
    </div>
  </nav>
</aside>

<!-- V8 Navbar for mobile -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top d-md-none v8-navbar-mobile" style="background: linear-gradient(90deg, var(--motif-primary, #6f42c1) 0%, var(--motif-accent, #fd7e14) 100%);" role="navigation" aria-label="Mobile navigation">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="uploads/app_logo/uma_musume_race_planner_logo_64.ico" alt="Logo" style="height: 24px; margin-right: 8px;">
      <span>Uma Musume Planner</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavMobile" aria-controls="navbarNavMobile" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavMobile">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link v8-animated-pill v8-tap-feedback <?= $current_page === 'index.php' ? 'active' : '' ?>" href="index.php" tabindex="0" <?= $current_page === 'index.php' ? 'aria-current="page"' : '' ?>>
            <i class="bi bi-house-door me-1" aria-hidden="true"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link v8-animated-pill v8-tap-feedback" href="#" id="newPlanBtnMobile" tabindex="0">
            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> New Plan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link v8-animated-pill v8-tap-feedback <?= $current_page === 'guide.php' ? 'active' : '' ?>" href="guide.php" tabindex="0" <?= $current_page === 'guide.php' ? 'aria-current="page"' : '' ?>>
            <i class="bi bi-book me-1" aria-hidden="true"></i> Guide
          </a>
        </li>
        <li class="nav-item d-flex align-items-center ms-lg-3">
          <div class="form-check form-switch text-light">
            <input class="form-check-input" type="checkbox" id="darkModeToggleMobile" <?= $isDarkModeChecked ?> aria-label="Toggle dark mode">
            <label class="form-check-label" for="darkModeToggleMobile">Dark Mode</label>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>

<style>
  /* V8 sidebar styles */
  aside[aria-label="Main menu"], aside.nav {
    background: linear-gradient(180deg, var(--motif-primary, #6f42c1) 0%, #fd7e14 100%);
    color: #fff;
    min-width: 220px;
    font-family: 'Figtree', 'M PLUS Rounded 1c', system-ui, sans-serif;
    box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  }
  aside .nav-link {
    color: #fff;
    font-size: 1.1rem;
    border-radius: 0.75rem;
    margin-bottom: 0.5rem;
    outline: none;
    transition: background 0.2s, color 0.2s;
  }
  aside .nav-link.active, aside .nav-link:focus {
    background: rgba(255,255,255,0.12);
    color: #fff;
    box-shadow: 0 0 0 2px #fff;
  }
  aside .nav-link:focus-visible {
    outline: 2px solid #fff;
    outline-offset: 2px;
  }
  aside .form-check-input {
    accent-color: var(--motif-primary, #6f42c1);
  }
  @media (max-width: 767.98px) {
    aside[aria-label="Main menu"] { display: none !important; }
  }
</style>

<script>
// Sync dark mode toggle between sidebar and mobile navbar
document.addEventListener('DOMContentLoaded', function() {
  const sidebarToggle = document.getElementById('darkModeToggleSidebar');
  const mobileToggle = document.getElementById('darkModeToggleMobile');
  const mainToggle = document.getElementById('darkModeToggle');
  function setAllDarkModeToggles(checked) {
    [sidebarToggle, mobileToggle, mainToggle].forEach(t => { if (t) t.checked = checked; });
    localStorage.setItem('darkMode', checked ? 'enabled' : 'disabled');
    document.body.classList.toggle('dark-mode', checked);
  }
  [sidebarToggle, mobileToggle, mainToggle].forEach(t => {
    if (t) {
      t.addEventListener('change', function() {
        setAllDarkModeToggles(t.checked);
      });
    }
  });
});
</script>
