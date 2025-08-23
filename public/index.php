<?php

declare(strict_types=1);

// public/index.php

// Bootstrap logger early (non-fatal if unavailable)
try {
    $log = require __DIR__ . '/../includes/logger.php';
} catch (Throwable $e) {
// Fallback: minimal logger shim
    $log = new class () {
        public function error($msg, array $context = []): void
        {
            error_log($msg . ' ' . json_encode($context));
        }
        public function info($msg, array $context = []): void
        {
            error_log($msg . ' ' . json_encode($context));
        }
    };
}

// Connect to database (db.php may throw; handle gracefully)
try {
/** @var PDO $pdo */
    $pdo = require __DIR__ . '/../includes/db.php';
} catch (Throwable $e) {
    $log->error('DB bootstrap failure', ['message' => $e->getMessage()]);
// Render a friendly error page while keeping 200 to avoid leaking infra details
    http_response_code(200);
    ?>
    <!DOCTYPE html>
    <html lang="en"><head>
      <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Uma Musume Race Planner</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light">
      <div class="container py-5">
        <div class="alert alert-danger shadow-sm">
          <h4 class="alert-heading">Service temporarily unavailable</h4>
          <p>We are unable to connect to the database at the moment. Please try again later.</p>
        </div>
      </div>
    </body></html>
    <?php
    exit;
}

// Define static options for PHP-side rendering
$predictionIcons = ['◎', '⦾', '○', '△', 'X', '-'];
$careerStageOptions = [
    ['value' => 'predebut', 'text' => 'Pre-Debut'],
    ['value' => 'junior', 'text' => 'Junior Year'],
    ['value' => 'classic', 'text' => 'Classic Year'],
    ['value' => 'senior', 'text' => 'Senior Year'],
    ['value' => 'finale', 'text' => 'Finale Season'],
];
$classOptions = [
    ['value' => 'debut', 'text' => 'Debut'],
    ['value' => 'maiden', 'text' => 'Maiden'],
    ['value' => 'beginner', 'text' => 'Beginner'],
    ['value' => 'bronze', 'text' => 'Bronze'],
    ['value' => 'silver', 'text' => 'Silver'],
    ['value' => 'gold', 'text' => 'Gold'],
    ['value' => 'platinum', 'text' => 'Platinum'],
    ['value' => 'star', 'text' => 'Star'],
    ['value' => 'legend', 'text' => 'Legend'],
];
$attributeGradeOptions = ['S+', 'S', 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'E+', 'E', 'F+', 'F', 'G+', 'G'];
$timeOfDayOptions = ['Early', 'Late'];
$monthOptions = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
// Fetch dynamic options from the database using PDO with error handling
try {
    $strategyOptions = $pdo->query('SELECT id, label FROM strategies ORDER BY label')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $log->error('Failed to fetch strategy options in index.php', ['message' => $e->getMessage()]);
    $strategyOptions = [];
}
try {
    $moodOptions = $pdo->query('SELECT id, label FROM moods ORDER BY label')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $log->error('Failed to fetch mood options in index.php', ['message' => $e->getMessage()]);
    $moodOptions = [];
}
try {
    $conditionOptions = $pdo->query('SELECT id, label FROM conditions ORDER BY label')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $log->error('Failed to fetch condition options in index.php', ['message' => $e->getMessage()]);
    $conditionOptions = [];
}
try {
    $skillTagOptions = $pdo->query('SELECT DISTINCT tag, stat_type FROM skill_reference ORDER BY tag')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $log->error('Failed to fetch skill tag options in index.php', ['message' => $e->getMessage()]);
    $skillTagOptions = [];
}

// Although plan list is rendered via JS, keep this query if needed by server-side components.
// Gracefully handle failures by defaulting to an empty array.
$plans_query = '
    SELECT p.*, m.label AS mood, s.label AS strategy
    FROM plans p
    LEFT JOIN moods m ON p.mood_id = m.id
    LEFT JOIN strategies s ON p.strategy_id = s.id
    WHERE p.deleted_at IS NULL
    ORDER BY p.updated_at DESC
';
try {
    $plans = $pdo->query($plans_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $log->error('Failed to fetch plan list in index.php', ['message' => $e->getMessage()]);
    $plans = [];
}

// Count plans by status (only non-deleted ones)
$stats_query = "SELECT
    COUNT(*) AS total_plans,
    SUM(status = 'Active') AS active_plans,
    SUM(status = 'Planning') AS planning_plans,
    SUM(status = 'Finished') AS finished_plans,
    COUNT(DISTINCT name) AS unique_trainees
    FROM plans WHERE deleted_at IS NULL";
try {
    $stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC) ?: [];
    foreach ($stats as $key => $value) {
        $stats[$key] = (int) $value;
    }
} catch (Throwable $e) {
    $log->error('Failed to fetch stats in index.php', ['message' => $e->getMessage()]);
    $stats = ['total_plans' => 0, 'active_plans' => 0, 'planning_plans' => 0, 'finished_plans' => 0, 'unique_trainees' => 0];
}

// Fetch recent activities (limit 3), materialize into an array for safe iteration
try {
    $activities = $pdo->query('SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $log->error('Failed to fetch activities in index.php', ['message' => $e->getMessage()]);
    $activities = [];
}

// Compute robust base path for web assets:
// Remove trailing '/public' from SCRIPT_NAME directory to get app root base.
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Uma Musume Race Planner</title>

  <!-- CSS libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;700&display=swap" rel="stylesheet">

  <!-- Charts (required by app.js for Stats) - use non-minified to match available source maps and avoid 404s -->
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

  <!-- Favicons (paths adjusted based on computed base) -->
  <link rel="icon" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/favicon.ico" sizes="32x32">
  <link rel="icon" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
  <link rel="icon" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/app_logo/uma_musume_race_planner_logo_256.png">

  <!-- App stylesheet -->
  <link rel="stylesheet" href="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/css/style.css?v=<?= htmlspecialchars($css_v, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
  <!-- Skip link -->
  <a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-sm btn-outline-secondary" href="#mainContent">Skip to main content</a>

  <?php require_once __DIR__ . '/../components/navbar.php'; ?>

  <div class="container">
    <div class="header-banner rounded-3 text-center mb-4" role="banner" aria-label="Application header">
      <div class="container">
        <h1 class="display-4 fw-bold d-flex align-items-center justify-content-center gap-2">
          <img
            src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/images/app_logo/uma_musume_race_planner_logo_128.png"
            alt="Uma Musume Race Planner Logo"
            class="logo app-logo"
            width="80"
            height="80"
            decoding="async"
          >
          Uma Musume Race Planner
        </h1>
        <p class="lead">Plan, track, and optimize your umamusume's racing career</p>
      </div>
    </div>

    <div id="mainContent" class="row" role="main" tabindex="-1">
      <div class="col-lg-8">
        <?php include __DIR__ . '/../components/plan-list.php'; ?>
      </div>
      <div class="col-lg-4">
        <?php include __DIR__ . '/../components/stats-panel.php'; ?>
        <?php include __DIR__ . '/../components/recent-activity.php'; ?>
      </div>
    </div>

    <?php require_once __DIR__ . '/../components/plan-inline-details.php'; ?>
  </div>

  <?php require_once __DIR__ . '/../quick_create_plan_modal.php'; ?>
  <?php require_once __DIR__ . '/../plan_details_modal.php'; ?>
  <?php require_once __DIR__ . '/../components/footer.php'; ?>

  <div class="modal fade" id="messageBoxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-body text-center alert alert-success mb-0" id="messageBoxBody"></div>
      </div>
    </div>
  </div>

  <!-- JS (defer for performance); app.js centralizes theme + charts; config first -->
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/config.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/app.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/autosuggest.js"></script>
  <!-- Page-specific modules for dashboard functionality -->
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/dashboard.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/plan_list.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/quick_create_modal.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/plan_details_modal.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/plan_inline_details.js"></script>
  <script defer src="<?= htmlspecialchars($baseWeb, ENT_QUOTES, 'UTF-8') ?>assets/js/main_dashboard.js"></script>
</body>
</html>
