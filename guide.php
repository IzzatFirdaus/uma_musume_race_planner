<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Guide - Uma Musume Race Planner</title>
  <link href="//cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_32.ico" sizes="32x32">
  <link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
  <link rel="icon" href="uploads/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
  <link rel="apple-touch-icon" href="uploads/app_logo/uma_musume_race_planner_logo_256.png">
</head>
<body>
  <?php require_once __DIR__ . '/components/navbar.php'; ?>

  <nav class="sticky-top py-2 guide-sticky-nav">
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

  <main class="container my-4">
    <?php require_once __DIR__ . '/components/header.php'; ?>

    <div class="card shadow-sm">
      <div class="card-header">
        <h1 class="h3 mb-0"><i class="bi bi-book-fill me-2"></i>Application Guide</h1>
      </div>
      <div class="card-body p-lg-5">

        <section class="mb-5 p-4 p-md-5 rounded shadow-sm section-highlight" id="welcome">
          <h2>Welcome to the Race Planner!</h2>
          <p class="lead">This planner helps guide your favorite Umamusume to victory! Track everything from early training to URA Finals performance. Save your plans and iterate with AI help, screenshots, and stat charts.</p>
        </section>

        <section class="mb-5" id="dashboard">
          <h3>Getting Started: Your Dashboard</h3>
          <ul>
            <li><strong>Race Plans:</strong> View saved plans with thumbnails and statuses.</li>
            <li><strong>Filters:</strong> Filter by Planning, Active, Draft, and more.</li>
            <li><strong>Stats Summary:</strong> See total plan count, active training, and completions.</li>
            <li><strong>Recent Activity:</strong> Timeline of your last 5 edits or creations.</li>
          </ul>
          <figure class="text-center mt-4">
            <?php
              $dashboardScreenshot = "Homepage.png";
  $safeDashboardScreenshot = htmlspecialchars(basename($dashboardScreenshot));
  ?>
            <img src="assets/screenshots/<?= $safeDashboardScreenshot ?>" class="img-fluid shadow-sm rounded" alt="Dashboard Screenshot" loading="lazy">
            <figcaption class="text-muted small mt-2">Dashboard layout example</figcaption>
          </figure>
        </section>

        <section class="mb-5" id="create-edit">
          <h3>Creating and Editing Plans</h3>

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
  $safeGeneralScreenshot = htmlspecialchars(basename($generalScreenshot));
  ?>
              <img src="screenshot/<?= $safeGeneralScreenshot ?>" class="img-fluid rounded shadow-sm" alt="General Tab" loading="lazy">
              <img src="assets/screenshots/<?= $safeGeneralScreenshot ?>" class="img-fluid rounded shadow-sm" alt="General Tab" loading="lazy">
            </div>
            <div class="col-md-6 mb-3">
              <?php
  $skillsScreenshot = "004_SKILLS Edit Plan.png";
  $safeSkillsScreenshot = htmlspecialchars(basename($skillsScreenshot));
  ?>
              <img src="screenshot/<?= $safeSkillsScreenshot ?>" class="img-fluid rounded shadow-sm" alt="Skills Tab" loading="lazy">
              <img src="assets/screenshots/<?= $safeSkillsScreenshot ?>" class="img-fluid rounded shadow-sm" alt="Skills Tab" loading="lazy">
            </div>
          </div>

          <p>Always click <strong>"Save Changes"</strong> before closing the plan!</p>
        </section>

        <section class="mb-5" id="ai-help">
          <h3>Using with AI Assistants</h3>
          <p>Use tools like ChatGPT or Gemini to brainstorm training strategies:</p>
          <ol>
            <li><strong>Ask:</strong> "Give me a plan for Daiwa Scarlet with long-distance stamina focus."</li>
            <li><strong>Input:</strong> Enter suggestions into your plan manually.</li>
            <li><strong>Progress:</strong> Track turn-by-turn stats in the chart tab.</li>
            <li><strong>Coach:</strong> Use <strong>Export Plan</strong> to copy a clean summary and ask your AI assistant what to do next.</li>
          </ol>
        </section>

        <section class="mb-5" id="faq">
          <h3>Frequently Asked Questions</h3>
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

        <section class="mb-5" id="glossary">
          <h3>Glossary of Terms</h3>
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
            <a href="https://x.com/umamusume_eng?lang=en" target="_blank" rel="noopener noreferrer"><i class="bi bi-twitter-x"></i> X</a> |
            <a href="https://www.facebook.com/umamusume.eng" target="_blank" rel="noopener noreferrer"><i class="bi bi-facebook"></i> Facebook</a> |
            <a href="https://www.youtube.com/@umamusume_eng" target="_blank" rel="noopener noreferrer"><i class="bi bi-youtube"></i> YouTube</a> |
            <a href="https://discord.com/invite/umamusume-eng" target="_blank" rel="noopener noreferrer"><i class="bi bi-discord"></i> Discord</a>
          </p>
        </section>

      </div>
    </div>
  </main>
  <?php require_once __DIR__ . '/components/footer.php'; ?>

  <script src="//cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const body = document.body;
      const darkModeToggle = document.getElementById('darkModeToggle');

      // Enable dark mode based on localStorage or system preference
      function setDarkMode(isDark) {
        body.classList.toggle('dark-mode', isDark);
        if (darkModeToggle) {
          darkModeToggle.checked = isDark;
          localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
        }
      }

      // Initial dark mode setup
      const savedDarkMode = localStorage.getItem('darkMode');
      const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (savedDarkMode === 'enabled') {
        setDarkMode(true);
      } else if (savedDarkMode === 'disabled') {
        setDarkMode(false);
      } else if (savedDarkMode === null) {
        setDarkMode(systemPrefersDark);
      }

      if (darkModeToggle) {
        darkModeToggle.addEventListener('change', () => {
          setDarkMode(darkModeToggle.checked);
        });
      }

      // Sticky nav offset calculation
      const rootStyles = getComputedStyle(document.documentElement);
      let mainNavbarHeight = parseFloat(rootStyles.getPropertyValue('--main-navbar-height')) || 0;
      let guideNavbarHeight = parseFloat(rootStyles.getPropertyValue('--guide-navbar-height')) || 0;
      let totalStickyHeight = mainNavbarHeight + guideNavbarHeight;
      const actualMainNavbar = document.querySelector('.navbar');
      const actualGuideNavbar = document.querySelector('.guide-sticky-nav');

      if (actualMainNavbar) {
        mainNavbarHeight = actualMainNavbar.offsetHeight;
      }
      if (actualGuideNavbar) {
        guideNavbarHeight = actualGuideNavbar.offsetHeight;
      }
      totalStickyHeight = mainNavbarHeight + guideNavbarHeight;

      // Smooth scrolling for guide nav links
      document.querySelectorAll('.guide-sticky-nav .nav-link').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          document.querySelectorAll('.guide-sticky-nav .nav-link').forEach(link => link.classList.remove('active'));
          this.classList.add('active');
          const targetId = this.getAttribute('href');
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
            const offsetPosition = elementPosition - totalStickyHeight;
            window.scrollTo({
              top: offsetPosition,
              behavior: "smooth"
            });
            // Accessibility: Focus Management
            targetElement.focus({ preventScroll: true });
          }
        });
      });

      // Highlight active nav link on scroll (debounced)
      const sections = document.querySelectorAll('section[id]');
      const guideNavLinks = document.querySelectorAll('.guide-sticky-nav .nav-link');
      let scrollTimeout;
      function highlightNavOnScroll() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
          let currentActiveSectionId = null;
          sections.forEach(section => {
            const sectionRect = section.getBoundingClientRect();
            if (sectionRect.top <= totalStickyHeight + 20 && sectionRect.bottom > totalStickyHeight + 20) {
              currentActiveSectionId = section.getAttribute('id');
            }
          });
          guideNavLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentActiveSectionId) {
              link.classList.add('active');
            }
          });
        }, 100);
      }
      window.addEventListener('scroll', highlightNavOnScroll);
      highlightNavOnScroll();

      // Accordion keyboard support for FAQ
      const accordionButtons = document.querySelectorAll('#faqAccordion .accordion-button');
      accordionButtons.forEach(button => {
        button.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
          }
          if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextButton = this.closest('.accordion-item').nextElementSibling?.querySelector('.accordion-button');
            if (nextButton) nextButton.focus();
          } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevButton = this.closest('.accordion-item').previousElementSibling?.querySelector('.accordion-button');
            if (prevButton) prevButton.focus();
          }
        });
      });
    });
  </script>
</body>
</html>
