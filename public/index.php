<?php
// public/index.php
require_once __DIR__ . '/../includes/logger.php'; // Include the logger
$pdo = require __DIR__ . '/../includes/db.php'; // Use the new PDO connector
$log = $log ?? (require __DIR__ . '/../includes/logger.php'); // Ensure logger is available

// Define options arrays for PHP-side rendering
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
$attributeGradeOptions = [
    'S+', 'S', 'A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'E+', 'E', 'F+', 'F', 'G+', 'G',
];
$timeOfDayOptions = ['Early', 'Late'];
$monthOptions = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
];

// Fetch dynamic options from the database using PDO with error handling
try {
    $strategyOptions = $pdo->query('SELECT id, label FROM strategies ORDER BY label')->fetchAll();
} catch (Exception $e) {
    $log->error('Failed to fetch strategy options in index.php', ['message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e]);
    $strategyOptions = [];
}
try {
    $moodOptions = $pdo->query('SELECT id, label FROM moods')->fetchAll();
} catch (Exception $e) {
    $log->error('Failed to fetch mood options in index.php', ['message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e]);
    $moodOptions = [];
}
try {
    $conditionOptions = $pdo->query('SELECT id, label FROM conditions')->fetchAll();
} catch (Exception $e) {
    $log->error('Failed to fetch condition options in index.php', ['message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e]);
    $conditionOptions = [];
}
try {
    $skillTagOptions = $pdo->query('SELECT DISTINCT tag, stat_type FROM skill_reference ORDER BY tag')->fetchAll();
} catch (Exception $e) {
    $log->error('Failed to fetch skill tag options in index.php', ['message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e]);
    $skillTagOptions = [];
}

// Fetch all plans for the initial server-side render of plan-list.php
$plans_query = '
    SELECT p.*, m.label AS mood, s.label AS strategy
    FROM plans p
    LEFT JOIN moods m ON p.mood_id = m.id
    LEFT JOIN strategies s ON p.strategy_id = s.id
    WHERE p.deleted_at IS NULL
    ORDER BY p.updated_at DESC
';
try {
    $plans = $pdo->query($plans_query);
} catch (Exception $e) {
    $log->error('Failed to fetch plan list in index.php', ['message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e]);
    $plans = new PDOStatement();
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
    $stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);
    foreach ($stats as $key => $value) {
        $stats[$key] = (int)$value;
    }
} catch (Exception $e) {
    $log->error('Failed to fetch stats in index.php', ['message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e]);
    $stats = ['total_plans' => 0, 'active_plans' => 0, 'planning_plans' => 0, 'finished_plans' => 0, 'unique_trainees' => 0];
}

// Fetch recent activities
try {
    $activities = $pdo->query('SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 3');
} catch (Exception $e) {
    $log->error('Failed to fetch activities in index.php', ['message' => method_exists($e, 'getMessage') ? $e->getMessage() : $e]);
    $activities = new PDOStatement();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Uma Musume Race Planner</title>
  
  <!-- MYDS Typography Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  
  <!-- Bootstrap (maintained for compatibility) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Favicons -->
  <link rel="icon" href="/uma_musume_race_planner/assets/favicon.ico" sizes="32x32">
  <link rel="icon" href="/uma_musume_race_planner/assets/images/app_logo/uma_musume_race_planner_logo_128.png" sizes="128x128">
  <link rel="icon" href="/uma_musume_race_planner/assets/images/app_logo/uma_musume_race_planner_logo_256.png" sizes="256x256">
  <link rel="apple-touch-icon" href="/uma_musume_race_planner/assets/images/app_logo/uma_musume_race_planner_logo_256.png">

  <!-- MYDS Base Styles -->
  <link rel="stylesheet" href="/uma_musume_race_planner/assets/css/myds-base.css">
  <!-- Original styles (for compatibility) -->
  <link rel="stylesheet" href="/uma_musume_race_planner/assets/css/style.css">
</head>
<body>
  <?php require_once __DIR__ . '/../components/header.php'; ?>
  <?php require_once __DIR__ . '/../components/navbar.php'; ?>

  <div class="container" id="mainContent">
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <?php require_once __DIR__ . '/../components/copy_to_clipboard.php'; ?>
  <script src="/uma_musume_race_planner/assets/js/config.js"></script>
  <script src="/uma_musume_race_planner/assets/js/app.js"></script>
  <script src="/uma_musume_race_planner/assets/js/autosuggest.js"></script>
  <!-- The rest of the JS for modal, dynamic UI, and event handlers is unchanged and included as needed -->
</body>
</html>