<?php

declare(strict_types=1);

// pages/guide.php (location assumed). Compute a robust web base for assets and links.
// Derive the base path by removing trailing '/public' from the current script directory.
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$baseWeb = preg_replace('~/public/?$~', '/', $scriptDir);
if ($baseWeb === '') {
    $baseWeb = '/';
}
$baseWeb = rtrim($baseWeb, '/') . '/';
// Versioned CSS for cache-busting
$cssFile = __DIR__ . '/../assets/css/style.css';
$css_v = file_exists($cssFile) ? (string) filemtime($cssFile) : (string) time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Guide - Uma Musume Race Planner</title>

  <!-- Bootstrap 5 + Icons CDN (latest stable) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Google Fonts: Figtree -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700&display=swap" rel="stylesheet" />

  <!-- Custom CSS from /assets -->
  <link rel="stylesheet" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/css/style.css?v=<?= htmlspecialchars($css_v, ENT_QUOTES, 'UTF-8') ?>" />

  <!-- Favicons -->
  <link rel="icon" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/favicon.ico" sizes="32x32">
  <link rel="icon" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
  <link rel="icon" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/app_logo/uma_musume_race_planner_logo_256.png">
</head>
<body>
  <!-- Skip link for accessibility -->
  <a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-sm btn-outline-secondary" href="#mainContent">Skip to main content</a>

  <?php require_once __DIR__ . '/../components/navbar.php'; ?>

  <!-- Secondary sticky guide nav -->
  <nav class="sticky-top py-2 guide-sticky-nav" role="navigation" aria-label="Guide section navigation">
    <div class="container">
      <ul class="nav nav-pills justify-content-center">
        <li class="nav-item"><a class="nav-link" href="#welcome">Welcome</a></li>
        <li class="nav-item"><a class="nav-link" href="#dashboard">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="#create-edit">Plan Editor</a></li>
        <li class="nav-item"><a class="nav-link" href="#ai-help">AI Assistant</a></li>
        <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="#glossary">Glossary</a></li>
      </ul>
    </div>
  </nav>

  <main class="container my-4" id="mainContent" role="main" tabindex="-1">
    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <div class="card shadow-sm">
      <div class="card-header">
        <h1 class="h3 mb-0"><i class="bi bi-book-fill me-2" aria-hidden="true"></i>Application Guide</h1>
      </div>
      <div class="card-body p-lg-5">

        <section class="mb-5 p-4 p-md-5 rounded shadow-sm section-highlight guide-section" id="welcome" tabindex="-1" aria-labelledby="welcome-heading">
          <h2 id="welcome-heading">Welcome to the Race Planner!</h2>
          <p class="lead">This planner helps guide your favorite Umamusume to victory! Track everything from early training to URA Finals performance. Save your plans and iterate with AI help, screenshots, and stat charts.</p>
        </section>

        <section class="mb-5 guide-section" id="dashboard" tabindex="-1" aria-labelledby="dashboard-heading">
          <h3 id="dashboard-heading">Getting Started: Your Dashboard</h3>
          <ul>
            <li><strong>Race Plans:</strong> View saved plans with thumbnails and statuses.</li>
            <li><strong>Filters:</strong> Filter by Planning, Active, Draft, and more.</li>
            <li><strong>Stats Summary:</strong> See total plan count, active training, and completions.</li>
            <li><strong>Recent Activity:</strong> Timeline of your last 5 edits or creations.</li>
          </ul>
          <figure class="text-center mt-4">
            <?php
              $dashboardScreenshot = "Homepage.png";
$safeDashboardScreenshot = htmlspecialchars(basename($dashboardScreenshot), ENT_QUOTES, 'UTF-8');
?>
            <img src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/screenshot/<?= $safeDashboardScreenshot ?>" class="img-fluid shadow-sm rounded" alt="Dashboard Screenshot" loading="lazy" decoding="async">
            <figcaption class="text-muted small mt-2">Dashboard layout example</figcaption>
          </figure>
        </section>

        <section class="mb-5 guide-section" id="create-edit" tabindex="-1" aria-labelledby="create-edit-heading">
          <h3 id="create-edit-heading">Creating and Editing Plans</h3>

          <h5>1. Quick Create</h5>
          <ol>
            <li>Click <strong>"Create New"</strong> or <strong>"New Plan"</strong>.</li>
            <li>Fill in the trainee name, career stage, and race info.</li>
            <li>Click <strong>"Create Plan"</strong>. It appears instantly on your dashboard.</li>
          </ol>

          <h5 class="mt-4">2. Editing a Plan</h5>
          <ul>
            <li><strong>General / Attributes:</strong> Update status, energy, time of day, or mood.</li>
            <li><strong>Skills:</strong> Autocomplete search + inline skill editing with notes.</li>
            <li><strong>Progress Chart:</strong> Graph your stat growth across turns.</li>
            <li><strong>Upload Image:</strong> Add trainee thumbnail with image upload support.</li>
          </ul>

          <div class="row mt-4">
            <div class="col-md-6 mb-3">
              <?php
    $generalScreenshot = "001_GENERAL Edit Plan.png";
$safeGeneralScreenshot = htmlspecialchars(basename($generalScreenshot), ENT_QUOTES, 'UTF-8');
?>
              <img src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/screenshot/<?= $safeGeneralScreenshot ?>" class="img-fluid rounded shadow-sm" alt="General Tab" loading="lazy" decoding="async">
            </div>
            <div class="col-md-6 mb-3">
              <?php
  $skillsScreenshot = "004_SKILLS Edit Plan.png";
$safeSkillsScreenshot = htmlspecialchars(basename($skillsScreenshot), ENT_QUOTES, 'UTF-8');
?>
              <img src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/screenshot/<?= $safeSkillsScreenshot ?>" class="img-fluid rounded shadow-sm" alt="Skills Tab" loading="lazy" decoding="async">
            </div>
          </div>

          <p>Always click <strong>"Save Changes"</strong> before closing the plan!</p>
        </section>

        <section class="mb-5 guide-section" id="ai-help" tabindex="-1" aria-labelledby="ai-help-heading">
          <h3 id="ai-help-heading">Using with AI Assistants</h3>
          <p>Use tools like ChatGPT or Gemini to brainstorm training strategies:</p>
          <ol>
            <li><strong>Ask:</strong> "Give me a plan for Daiwa Scarlet with long-distance stamina focus."</li>
            <li><strong>Input:</strong> Enter suggestions into your plan manually.</li>
            <li><strong>Progress:</strong> Track turn-by-turn stats in the chart tab.</li>
            <li><strong>Coach:</strong> Use <strong>Export Plan</strong> to copy a clean summary and ask your AI assistant what to do next.</li>
          </ol>
        </section>

        <section class="mb-5 guide-section" id="faq" tabindex="-1" aria-labelledby="faq-heading">
          <h3 id="faq-heading">Frequently Asked Questions</h3>
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq1Heading">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                  What is a “Turn Before” value?
                </button>
              </h2>
              <div id="faq1" class="accordion-collapse collapse show" aria-labelledby="faq1Heading" data-bs-parent="#faqAccordion">
                <div class="accordion-body">It shows how many training turns remain before the next scheduled race, helping you pace training goals effectively.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq2Heading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                  Can I plan for more than one Uma at a time?
                </button>
              </h2>
              <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faq2Heading" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Yes. Each plan is stored separately and can track a different trainee and strategy.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq3Heading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                  Does this tool work on mobile?
                </button>
              </h2>
              <div id="faq3" class="accordion-collapse collapse" aria-labelledby="faq3Heading" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Yes! The planner is fully responsive and optimized for touch controls and smaller screens.</div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="faq4Heading">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                  Can I share my plan with others?
                </button>
              </h2>
              <div id="faq4" class="accordion-collapse collapse" aria-labelledby="faq4Heading" data-bs-parent="#faqAccordion">
                <div class="accordion-body">Yes. Use "Export Plan" to copy your data as plain text and share it via message, Discord, or AI platforms.</div>
              </div>
            </div>
          </div>
        </section>

        <section class="mb-5 guide-section" id="glossary" tabindex="-1" aria-labelledby="glossary-heading">
          <h3 id="glossary-heading">Glossary of Terms</h3>
          <dl class="row">
            <dt class="col-sm-3">SP</dt>
            <dd class="col-sm-9">Skill Points — earned from training or races. Used to purchase skills.</dd>

            <dt class="col-sm-3">Aptitude</dt>
            <dd class="col-sm-9">Grade rating for track (Turf/Dirt), distance, or strategy (Front, Pace, etc).</dd>

            <dt class="col-sm-3">Wit</dt>
            <dd class="col-sm-9">Also known as Intelligence. Improves skill activation rate and positioning decisions.</dd>

            <dt class="col-sm-3">Guts</dt>
            <dd class="col-sm-9">Represents endurance and comeback power during the final sprint.</dd>

            <dt class="col-sm-3">Support Cards</dt>
            <dd class="col-sm-9">In-game cards that influence growth rate and skill acquisition.</dd>

            <dt class="col-sm-3">Skill Tag</dt>
            <dd class="col-sm-9">A category label (Start Dash, Recovery, Debuff, etc) that helps group skills.</dd>
          </dl>
        </section>

        <section class="text-center text-muted small">
          <p>Inspired by <strong>Uma Musume Pretty Derby</strong>. Explore the official platforms:</p>
          <p>
            <a href="https://umamusume.jp/" target="_blank" rel="noopener noreferrer">🇯🇵 Japanese Site</a> |
            <a href="https://umamusume.com/" target="_blank" rel="noopener noreferrer">🌍 English Global</a> |
            <a href="https://store.steampowered.com/app/3224770/Umamusume_Pretty_Derby/" target="_blank" rel="noopener noreferrer">🎮 Steam</a>
          </p>
          <p>
            <a href="https://x.com/umamusume_eng?lang=en" target="_blank" rel="noopener noreferrer"><i class="bi bi-twitter-x" aria-hidden="true"></i> X</a> |
            <a href="https://www.facebook.com/umamusume.eng" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook" aria-hidden="true"></i> Facebook</a> |
            <a href="https://www.youtube.com/@umamusume_eng" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube" aria-hidden="true"></i> YouTube</a> |
            <a href="https://discord.com/invite/umamusume-eng" target="_blank" rel="noopener noreferrer"><i class="bi bi-discord" aria-hidden="true"></i> Discord</a>
          </p>
        </section>
      </div>
    </div>
  </main>

  <?php require_once __DIR__ . '/../components/footer.php'; ?>

  <!-- JS (use defer to improve performance). Theme is handled centrally by assets/js/app.js -->
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/config.js"></script>
  <!-- SweetAlert2 CDN for notifications (required by plan_list.js, plan_details_modal.js, etc.) -->
  <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/app.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/guide.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/guide_inline.js"></script>
</body>
</html>
