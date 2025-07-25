<?php
// index.php
require_once 'config.php';

// Define options arrays based on constants.js for PHP-side rendering

// 🔸 Prediction Icon Mapping (derived from constants.js)
$predictionIcons = ["◎", "⦾", "○", "△", "X", "-"];

// 🔸 Career Stages (static from constants.js and ENUM in DB)
$careerStageOptions = [
  ['value' => 'predebut', 'text' => 'Pre-Debut'],
  ['value' => 'junior', 'text' => 'Junior Year'],
  ['value' => 'classic', 'text' => 'Classic Year'],
  ['value' => 'senior', 'text' => 'Senior Year'],
  ['value' => 'finale', 'text' => 'Finale Season'],
];

// 🔸 Class Ranks (static from constants.js and ENUM in DB)
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

// 🔸 Strategy Options (Fetched from DB for dynamic population)
$strategyOptions = [];
$result = $conn->query("SELECT id, label FROM strategies ORDER BY label");
while ($row = $result->fetch_assoc()) {
  $strategyOptions[] = ['value' => strtolower($row['label']), 'text' => $row['label']];
}

// 🔸 Mood Status Options (Fetched from DB for dynamic population)
$moodOptions = [];
$result = $conn->query("SELECT id, label FROM moods ORDER BY label");
while ($row = $result->fetch_assoc()) {
  $moodOptions[] = ['value' => strtolower($row['label']), 'text' => $row['label']];
}

// 🔸 Grade Options (Updated based on latest constants.js)
$attributeGradeOptions = [
  "S+",
  "S",
  "A+",
  "A",
  "B+",
  "B",
  "C+",
  "C",
  "D+",
  "D",
  "E+",
  "E",
  "F+",
  "F",
  "G+",
  "G"
];

// 🔸 Time of Day + Month (static from constants.js)
$timeOfDayOptions = ["Early", "Late"];
$monthOptions = [
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "May",
  "Jun",
  "Jul",
  "Aug",
  "Sep",
  "Oct",
  "Nov",
  "Dec"
];

// 🔸 Skill Tags Derived from skill_reference table
$skillTagOptions = [];
$result = $conn->query("SELECT DISTINCT tag, stat_type FROM skill_reference ORDER BY tag");
while ($row = $result->fetch_assoc()) {
  $skillTagOptions[] = $row;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get mood and strategy mappings from DB for submission processing
  $moods_map = [];
  $result = $conn->query("SELECT id, label FROM moods");
  while ($row = $result->fetch_assoc()) {
    $moods_map[$row['label']] = $row['id'];
  }

  $strategies_map = [];
  $result = $conn->query("SELECT id, label FROM strategies");
  while ($row = $result->fetch_assoc()) {
    $strategies_map[$row['label']] = $row['id'];
  }

  // Quick create form
  if (isset($_POST['trainee_name']) && isset($_POST['career_stage']) && isset($_POST['traineeClass']) && isset($_POST['race_name'])) {
    $trainee_name = $conn->real_escape_string($_POST['trainee_name']);
    $career_stage = $conn->real_escape_string($_POST['career_stage']);
    $class = $conn->real_escape_string($_POST['traineeClass']);
    $race_name = $conn->real_escape_string($_POST['race_name']);
    $status = 'Planning'; // Default status for quick create

    // Default mood and strategy IDs
    $mood_id = $moods_map['NORMAL'] ?? null;
    $strategy_id = $strategies_map['PACE'] ?? null;

    // Using a prepared statement for security
    $stmt = $conn->prepare("INSERT INTO plans (name, career_stage, class, race_name, mood_id, strategy_id, status)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiis", $trainee_name, $career_stage, $class, $race_name, $mood_id, $strategy_id, $status);
    $stmt->execute();
    $stmt->close();

    $plan_id = $conn->insert_id;
    $conn->query("INSERT INTO activity_log (description, icon_class)
                     VALUES ('New plan quick-created: " . $trainee_name . "', 'bi-person-plus')");

    // Set default attributes for the new plan
    $default_attributes = [
      ['SPEED', 0, 'G'],
      ['STAMINA', 0, 'G'],
      ['POWER', 0, 'G'],
      ['GUTS', 0, 'G'],
      ['WIT', 0, 'G']
    ];
    $stmt_attr = $conn->prepare("INSERT INTO attributes (plan_id, attribute_name, value, grade)
                                     VALUES (?, ?, ?, ?)");
    foreach ($default_attributes as $attr) {
      $attr_name = $attr[0];
      $attr_value = $attr[1];
      $attr_grade = $attr[2];
      $stmt_attr->bind_param("isis", $plan_id, $attr_name, $attr_value, $attr_grade);
      $stmt_attr->execute();
    }
    $stmt_attr->close();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
  }

  // Full plan form (Save/Update)
  if (isset($_POST['modalName'])) {
    $id = isset($_POST['planId']) ? (int)$_POST['planId'] : 0;
    $plan_title = $conn->real_escape_string($_POST['plan_title'] ?? 'Untitled Plan');
    $name = $conn->real_escape_string($_POST['modalName']);
    $career_stage = $conn->real_escape_string($_POST['modalCareerStage']);
    $class = $conn->real_escape_string($_POST['modalClass']);
    $race_name = $conn->real_escape_string($_POST['modalRaceName']);
    // NOTE: In uma_musume_planner.sql, 'turn_before' is VARCHAR(255).
    // It's recommended to change this to INT in your SQL schema if it always stores an integer.
    $turn_before = (int)$_POST['modalTurnBefore'];
    $goal = $conn->real_escape_string($_POST['modalGoal']);
    $strategy_label = $conn->real_escape_string($_POST['modalStrategy']);
    $mood_label = $conn->real_escape_string($_POST['modalMood']);
    $energy = (int)$_POST['energyRange'];
    $race_day = isset($_POST['raceDaySwitch']) ? 'yes' : 'no';
    $skill_points = (int)$_POST['skillPoints'];
    $status = $conn->real_escape_string($_POST['modalStatus']);
    $time_of_day = $conn->real_escape_string($_POST['modalTimeOfDay']);
    $month = $conn->real_escape_string($_POST['modalMonth']);
    $source = $conn->real_escape_string($_POST['modalSource'] ?? '');
    $growth_rate_speed = (int)$_POST['growthRateSpeed'];
    $growth_rate_stamina = (int)$_POST['growthRateStamina'];
    $growth_rate_power = (int)$_POST['growthRatePower'];
    $growth_rate_guts = (int)$_POST['growthRateGuts'];
    $growth_rate_wit = (int)$_POST['growthRateWit'];


    // Get mood and strategy IDs from labels
    $mood_id = $moods_map[strtoupper($mood_label)] ?? $moods_map['NORMAL'];
    $strategy_id = $strategies_map[strtoupper($strategy_label)] ?? $strategies_map['PACE'];

    // Handle attributes, skills, and predictions (assuming they are sent as JSON strings)
    $attributes_data = json_decode($_POST['attributes'], true);
    $skills_data = json_decode($_POST['skills'], true);
    $predictions_data = json_decode($_POST['predictions'], true);

    if ($id) {
      // Update existing plan
      $stmt_plan = $conn->prepare("UPDATE plans SET
                plan_title = ?,
                name = ?,
                career_stage = ?,
                class = ?,
                race_name = ?,
                turn_before = ?,
                goal = ?,
                strategy_id = ?,
                mood_id = ?,
                energy = ?,
                race_day = ?,
                total_available_skill_points = ?,
                status = ?,
                time_of_day = ?,
                month = ?,
                source = ?,
                growth_rate_speed = ?,
                growth_rate_stamina = ?,
                growth_rate_power = ?,
                growth_rate_guts = ?,
                growth_rate_wit = ?
                WHERE id = ?");
      $stmt_plan->bind_param(
        "sssssisiiisisssiiiiiii",
        $plan_title,
        $name,
        $career_stage,
        $class,
        $race_name,
        $turn_before,
        $goal,
        $strategy_id,
        $mood_id,
        $energy,
        $race_day,
        $skill_points,
        $status,
        $time_of_day,
        $month,
        $source,
        $growth_rate_speed,
        $growth_rate_stamina,
        $growth_rate_power,
        $growth_rate_guts,
        $growth_rate_wit,
        $id
      );
      $stmt_plan->execute();
      $stmt_plan->close();

      // Update attributes
      $stmt_attr = $conn->prepare("UPDATE attributes SET value = ?, grade = ? WHERE plan_id = ? AND attribute_name = ?");
      foreach ($attributes_data as $attr) {
        $attr_name = strtoupper($attr['attribute_name']);
        $value = (int)$attr['value'];
        $grade = $attr['grade'];
        $stmt_attr->bind_param("isis", $value, $grade, $id, $attr_name);
        $stmt_attr->execute();
      }
      $stmt_attr->close();

      // Delete and re-insert skills to handle changes
      $conn->query("DELETE FROM skills WHERE plan_id = $id"); // Direct query for delete is fine here
      if (!empty($skills_data)) {
        $stmt_skill = $conn->prepare("INSERT INTO skills (plan_id, skill_name, sp_cost, acquired, tag, notes)
                                              VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($skills_data as $skill) {
          $skill_name = $skill['skill_name'];
          $sp_cost = $skill['sp_cost'];
          $acquired = $skill['acquired'];
          $tag = $skill['tag'];
          $notes = $skill['notes'];
          $stmt_skill->bind_param("isisss", $id, $skill_name, $sp_cost, $acquired, $tag, $notes);
          $stmt_skill->execute();
        }
        $stmt_skill->close();
      }

      // Delete and re-insert predictions to handle changes
      $conn->query("DELETE FROM race_predictions WHERE plan_id = $id"); // Direct query for delete is fine here
      if (!empty($predictions_data)) {
        $stmt_pred = $conn->prepare("INSERT INTO race_predictions (plan_id, race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment)
                                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($predictions_data as $pred) {
          $race_name = $pred['race_name'];
          $venue = $pred['venue'];
          $ground = $pred['ground'];
          $distance = $pred['distance'];
          $track_condition = $pred['track_condition'];
          $direction = $pred['direction'];
          $speed = $pred['speed'];
          $stamina = $pred['stamina'];
          $power = $pred['power'];
          $guts = $pred['guts'];
          $wit = $pred['wit'];
          $comment = $pred['comment'];
          $stmt_pred->bind_param("issssssssssss", $id, $race_name, $venue, $ground, $distance, $track_condition, $direction, $speed, $stamina, $power, $guts, $wit, $comment);
          $stmt_pred->execute();
        }
        $stmt_pred->close();
      }

      $conn->query("INSERT INTO activity_log (description, icon_class)
                         VALUES ('Plan updated: $name', 'bi-arrow-repeat')");
    } else {
      // Create new plan
      $stmt_plan = $conn->prepare("INSERT INTO plans (
                plan_title, name, career_stage, class, race_name, turn_before, goal, strategy_id,
                mood_id, energy, race_day, total_available_skill_points, status, time_of_day, month, source,
                growth_rate_speed, growth_rate_stamina, growth_rate_power, growth_rate_guts, growth_rate_wit
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )");
      $stmt_plan->bind_param(
        "sssssisiiisisssiiiiii",
        $plan_title,
        $name,
        $career_stage,
        $class,
        $race_name,
        $turn_before,
        $goal,
        $strategy_id,
        $mood_id,
        $energy,
        $race_day,
        $skill_points,
        $status,
        $time_of_day,
        $month,
        $source,
        $growth_rate_speed,
        $growth_rate_stamina,
        $growth_rate_power,
        $growth_rate_guts,
        $growth_rate_wit
      );
      $stmt_plan->execute();
      $stmt_plan->close();

      $new_id = $conn->insert_id;

      // Insert attributes
      $stmt_attr = $conn->prepare("INSERT INTO attributes (plan_id, attribute_name, value, grade)
                                         VALUES (?, ?, ?, ?)");
      foreach ($attributes_data as $attr) {
        $attr_name = strtoupper($attr['attribute_name']);
        $value = (int)$attr['value'];
        $grade = $attr['grade'];
        $stmt_attr->bind_param("isis", $new_id, $attr_name, $value, $grade);
        $stmt_attr->execute();
      }
      $stmt_attr->close();

      // Insert skills
      if (!empty($skills_data)) {
        $stmt_skill = $conn->prepare("INSERT INTO skills (plan_id, skill_name, sp_cost, acquired, tag, notes)
                                              VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($skills_data as $skill) {
          $skill_name = $skill['skill_name'];
          $sp_cost = $skill['sp_cost'];
          $acquired = $skill['acquired'];
          $tag = $skill['tag'];
          $notes = $skill['notes'];
          $stmt_skill->bind_param("isisss", $new_id, $skill_name, $sp_cost, $acquired, $tag, $notes);
          $stmt_skill->execute();
        }
        $stmt_skill->close();
      }

      // Insert predictions
      if (!empty($predictions_data)) {
        $stmt_pred = $conn->prepare("INSERT INTO race_predictions (plan_id, race_name, venue, ground, distance, track_condition, direction, speed, stamina, power, guts, wit, comment)
                                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($predictions_data as $pred) {
          $race_name = $pred['race_name'];
          $venue = $pred['venue'];
          $ground = $pred['ground'];
          $distance = $pred['distance'];
          $track_condition = $pred['track_condition'];
          $direction = $pred['direction'];
          $speed = $pred['speed'];
          $stamina = $pred['stamina'];
          $power = $pred['power'];
          $guts = $pred['guts'];
          $wit = $pred['wit'];
          $comment = $pred['comment'];
          $stmt_pred->bind_param("issssssssssss", $new_id, $race_name, $venue, $ground, $distance, $track_condition, $direction, $speed, $stamina, $power, $guts, $wit, $comment);
          $stmt_pred->execute();
        }
        $stmt_pred->close();
      }

      $conn->query("INSERT INTO activity_log (description, icon_class)
                         VALUES ('New plan created: $name', 'bi-person-plus')");
    }

    // Respond with JSON for AJAX request
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
  }
}

// Handle delete action (AJAX friendly)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $id = (int)$_POST['delete_id'];
  // Soft delete using prepared statement for security
  $stmt_delete = $conn->prepare("UPDATE plans SET deleted_at = NOW() WHERE id = ?");
  $stmt_delete->bind_param("i", $id);
  $stmt_delete->execute();
  $stmt_delete->close();

  $conn->query("INSERT INTO activity_log (description, icon_class) VALUES ('Plan (ID: $id) soft-deleted', 'bi-trash')");
  header('Content-Type: application/json');
  echo json_encode(['success' => true]);
  exit();
}

// Fetch all plans with mood and strategy names (only non-deleted ones)
$plans_query = "
    SELECT p.*, m.label AS mood, s.label AS strategy
    FROM plans p
    LEFT JOIN moods m ON p.mood_id = m.id
    LEFT JOIN strategies s ON p.strategy_id = s.id
    WHERE p.deleted_at IS NULL
    ORDER BY p.updated_at DESC
";
$plans = $conn->query($plans_query);

// Count plans by status (only non-deleted ones)
$stats_query = "SELECT
    COUNT(*) AS total,
    SUM(status = 'Active') AS active,
    SUM(status = 'Planning') AS planning,
    SUM(status = 'Finished') AS finished
    FROM plans WHERE deleted_at IS NULL";
$stats = $conn->query($stats_query)->fetch_assoc();

// Fetch recent activities
$activities = $conn->query("SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Uma Musume Race Planner</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#">
        <i class="bi bi-speedometer2 me-2"></i>
        <span>Uma Musume Planner</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link active" href="#"><i class="bi bi-house-door me-1"></i> Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" id="newPlanBtn"><i class="bi bi-plus-circle me-1"></i> New Plan</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><i class="bi bi-book me-1"></i> Guide</a>
          </li>
          <li class="nav-item dark-mode-switch">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="darkModeToggle">
              <label class="form-check-label" for="darkModeToggle">Dark Mode</label>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="header-banner rounded-3 text-center mb-4">
      <div class="container">
        <h1 class="display-4 fw-bold"><i class="bi bi-speedometer2"></i> Uma Musume Race Planner</h1>
        <p class="lead">Plan, track, and optimize your horse girl's racing career</p>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Your Race Plans</span>
            <button class="btn btn-sm btn-uma" id="createPlanBtn" data-bs-toggle="modal" data-bs-target="#createPlanModal">
              <i class="bi bi-plus-circle"></i> Create New
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Career Stage</th>
                    <th>Class</th>
                    <th>Race</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($plans->num_rows > 0): ?>
                    <?php while ($plan = $plans->fetch_assoc()): ?>
                      <tr class="plan-card" data-id="<?= $plan['id'] ?>">
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="symbol symbol-500 me-3">
                              <i class="bi bi-person-circle fs-2 text-purple"></i>
                            </div>
                            <div>
                              <div class="fw-bold"><?= htmlspecialchars($plan['name']) ?></div>
                              <div class="text-muted fs-7"><?= htmlspecialchars($plan['race_name']) ?></div>
                            </div>
                          </div>
                        </td>
                        <td><?= ucfirst($plan['career_stage']) ?></td>
                        <td>
                          <?php
                          // PHP-side class badge styling based on constants.js logic
                          $classBadgeClass = '';
                          switch ($plan['class']) {
                            case 's+':
                            case 's':
                              $classBadgeClass = 'bg-primary';
                              break;
                            case 'a+':
                            case 'a':
                              $classBadgeClass = 'bg-success';
                              break;
                            case 'b+':
                            case 'b':
                              $classBadgeClass = 'bg-info';
                              break;
                            case 'c+':
                            case 'c':
                              $classBadgeClass = 'bg-warning text-dark';
                              break;
                            case 'd+':
                            case 'd':
                              $classBadgeClass = 'bg-orange'; // Assuming 'bg-orange' is defined in style.css
                              break;
                            case 'e+':
                            case 'e':
                              $classBadgeClass = 'bg-danger';
                              break;
                            case 'f+':
                            case 'f':
                              $classBadgeClass = 'bg-dark';
                              break;
                            case 'g+':
                            case 'g':
                              $classBadgeClass = 'bg-secondary';
                              break;
                            case 'debut':
                              $classBadgeClass = 'bg-light text-dark';
                              break;
                            case 'maiden':
                              $classBadgeClass = 'bg-light text-dark';
                              break;
                            case 'beginner':
                              $classBadgeClass = 'bg-light text-dark';
                              break;
                            case 'bronze':
                              $classBadgeClass = 'bg-bronze'; // Assuming 'bg-bronze' is defined
                              break;
                            case 'silver':
                              $classBadgeClass = 'bg-silver'; // Assuming 'bg-silver' is defined
                              break;
                            case 'gold':
                              $classBadgeClass = 'bg-gold'; // Assuming 'bg-gold' is defined
                              break;
                            case 'platinum':
                              $classBadgeClass = 'bg-platinum'; // Assuming 'bg-platinum' is defined
                              break;
                            case 'star':
                              $classBadgeClass = 'bg-star'; // Assuming 'bg-star' is defined
                              break;
                            case 'legend':
                              $classBadgeClass = 'bg-legend'; // Assuming 'bg-legend' is defined
                              break;
                            default:
                              $classBadgeClass = 'bg-muted'; // Assuming 'bg-muted' is defined
                              break;
                          }
                          ?>
                          <span class="badge <?= $classBadgeClass ?>"><?= strtoupper($plan['class']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($plan['race_name']) ?></td>
                        <td>
                          <?php
                          $statusClass = '';
                          switch ($plan['status']) {
                            case 'Active':
                              $statusClass = 'bg-success';
                              break;
                            case 'Planning':
                              $statusClass = 'bg-warning text-dark'; // Changed to match constants.js
                              break;
                            case 'Finished':
                              $statusClass = 'bg-primary'; // Changed to match constants.js
                              break;
                            case 'Draft':
                              $statusClass = 'bg-secondary'; // Changed to match constants.js
                              break;
                            case 'Abandoned':
                              $statusClass = 'bg-danger'; // Changed to match constants.js
                              break;
                            default:
                              $statusClass = 'bg-secondary'; // Default to secondary for unknown
                              break;
                          }
                          ?>
                          <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($plan['status']) ?></span>
                        </td>
                        <td>
                          <button class="btn btn-sm btn-outline-primary edit-btn" data-id="<?= $plan['id'] ?>" data-bs-toggle="modal" data-bs-target="#planDetailsModal">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $plan['id'] ?>">
                            <i class="bi bi-trash"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center text-muted">No plans created yet. Click "Create New" to start!</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-header">
            <span>Quick Stats</span>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-around text-center">
              <div>
                <div class="fs-1 fw-bold"><?= $stats['total'] ?></div>
                <div class="text-muted">Plans</div>
              </div>
              <div>
                <div class="fs-1 fw-bold"><?= $stats['active'] ?></div>
                <div class="text-muted">Active</div>
              </div>
              <div>
                <div class="fs-1 fw-bold"><?= $stats['finished'] ?></div>
                <div class="text-muted">Finished</div>
              </div>
            </div>
          </div>
        </div>
        <div class="card mb-4">
          <div class="card-header">
            <span>Recent Activities</span>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush">
              <?php if ($activities->num_rows > 0): ?>
                <?php while ($activity = $activities->fetch_assoc()): ?>
                  <li class="list-group-item d-flex align-items-center">
                    <i class="bi <?= htmlspecialchars($activity['icon_class']) ?> me-2 text-uma"></i>
                    <?= htmlspecialchars($activity['description']) ?>
                    <small class="text-muted ms-auto"><?= date("M d, H:i", strtotime($activity['timestamp'])) ?></small>
                  </li>
                <?php endwhile; ?>
              <?php else: ?>
                <li class="list-group-item text-muted text-center">No recent activities.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require_once 'quick_create_plan_modal.php'; ?>
  <?php require_once 'plan_details_modal.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Dark Mode Toggle Logic
      const darkModeToggle = document.getElementById('darkModeToggle');
      const body = document.body;

      function setDarkMode(isDarkMode) {
        if (isDarkMode) {
          body.classList.add('dark-mode');
          localStorage.setItem('darkMode', 'enabled');
          darkModeToggle.checked = true;
        } else {
          body.classList.remove('dark-mode');
          localStorage.setItem('darkMode', 'disabled');
          darkModeToggle.checked = false;
        }
      }

      // Check for saved dark mode preference or system preference on load
      const savedDarkMode = localStorage.getItem('darkMode');
      if (savedDarkMode === 'enabled') {
        setDarkMode(true);
      } else if (savedDarkMode === 'disabled') {
        setDarkMode(false);
      } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        setDarkMode(true);
      }

      darkModeToggle.addEventListener('change', function() {
        setDarkMode(this.checked);
      });

      // Initialize planDetailsModal once when the DOM is ready
      const planDetailsModalElement = document.getElementById('planDetailsModal');
      const planDetailsModal = new bootstrap.Modal(planDetailsModalElement);

      // Quick Create Modal setup: Initialize it once too.
      const quickCreatePlanModalElement = document.getElementById('createPlanModal');
      quickCreatePlanModalElement.addEventListener('show.bs.modal', function() {
        document.getElementById('quickCreatePlanForm').reset();
      });

      // Ensure showMessageBox is defined and accessible
      function showMessageBox(message, type = 'success') {
        const messageBox = document.getElementById('messageBox');
        const messageText = document.getElementById('messageText');
        messageText.textContent = message;
        messageBox.className = `message-box show bg-${type}`;
        setTimeout(() => {
          messageBox.classList.remove('show');
        }, 3000);
      }

      // Helper functions for rendering attributes, skills, predictions
      function renderAttributes(attributes) {
        const attributeGrid = document.getElementById('attributeGrid');
        attributeGrid.innerHTML = '';
        const attributeGradeOptions = <?php echo json_encode($attributeGradeOptions); ?>;
        attributes.forEach(attr => {
          const attrCol = document.createElement('div');
          attrCol.className = 'col-md-4';
          attrCol.innerHTML = `
            <label for="attr-${attr.attribute_name}" class="form-label">${attr.attribute_name}</label>
            <div class="input-group mb-3">
              <input type="number" class="form-control attribute-value-input"
                     id="attr-${attr.attribute_name}"
                     data-attribute-name="${attr.attribute_name}"
                     value="${attr.value}" min="0" max="1200">
              <select class="form-select attribute-grade-input"
                      data-attribute-name="${attr.attribute_name}">
                ${attributeGradeOptions.map(grade => `<option value="${grade}" ${grade === attr.grade ? 'selected' : ''}>${grade}</option>`).join('')}
              </select>
            </div>
          `;
          attributeGrid.appendChild(attrCol);
        });
      }

      function renderSkills(skills) {
        const skillsTableBody = document.getElementById('skillsTable').querySelector('tbody');
        skillsTableBody.innerHTML = '';
        skills.forEach(skill => {
          skillsTableBody.appendChild(createSkillRow(skill));
        });
      }

      function renderPredictions(predictions) {
        const predictionsTableBody = document.getElementById('predictionsTable').querySelector('tbody');
        predictionsTableBody.innerHTML = '';
        predictions.forEach(prediction => {
          predictionsTableBody.appendChild(createPredictionRow(prediction));
        });
      }

      // Your existing code for handling the 'edit-plan-btn' click
      document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-plan-btn') || event.target.closest('.edit-plan-btn')) {
          const button = event.target.closest('.edit-plan-btn');
          const planId = button.dataset.id;
          // Show loading overlay
          document.getElementById('planDetailsLoadingOverlay').classList.add('show');

          // Fetch plan details
          fetch(`fetch_plan_details.php?id=${planId}`)
            .then(response => response.json())
            .then(data => {
              if (data.error) {
                showMessageBox(data.error, 'danger');
                return;
              }

              // Populate the form fields with fetched data
              document.getElementById('planId').value = data.id;
              document.getElementById('planDetailsModalLabel').textContent = `Plan Details: ${data.plan_title}`;
              document.getElementById('plan_title').value = data.plan_title;
              document.getElementById('modalName').value = data.name;
              document.getElementById('modalCareerStage').value = data.career_stage;
              document.getElementById('modalClass').value = data.class;
              document.getElementById('modalRaceName').value = data.race_name;
              document.getElementById('modalTurnBefore').value = data.turn_before;
              document.getElementById('modalGoal').value = data.goal;
              document.getElementById('modalStrategy').value = data.strategy_label ? data.strategy_label.toLowerCase() : '';
              document.getElementById('modalMood').value = data.mood_label ? data.mood_label.toLowerCase() : '';
              document.getElementById('energyRange').value = data.energy;
              document.getElementById('energyValue').textContent = data.energy; // Update range display
              document.getElementById('raceDaySwitch').checked = data.race_day === 'yes';
              document.getElementById('skillPoints').value = data.total_available_skill_points;
              document.getElementById('modalStatus').value = data.status;
              document.getElementById('modalTimeOfDay').value = data.time_of_day;
              document.getElementById('modalMonth').value = data.month;
              document.getElementById('modalSource').value = data.source || '';
              document.getElementById('growthRateSpeed').value = data.growth_rate_speed || 0;
              document.getElementById('growthRateStamina').value = data.growth_rate_stamina || 0;
              document.getElementById('growthRatePower').value = data.growth_rate_power || 0;
              document.getElementById('growthRateGuts').value = data.growth_rate_guts || 0;
              document.getElementById('growthRateWit').value = data.growth_rate_wit || 0;

              // Populate attributes, skills, and predictions
              renderAttributes(data.attributes);
              renderSkills(data.skills);
              renderPredictions(data.race_predictions);

              // Hide loading overlay
              document.getElementById('planDetailsLoadingOverlay').classList.remove('show');

              // Show the modal using the single instance
              planDetailsModal.show();
            })
            .catch(error => {
              console.error('Error fetching plan details:', error);
              showMessageBox('Error loading plan details.', 'danger');
              document.getElementById('planDetailsLoadingOverlay').classList.remove('show');
            });
        }

        // ... (rest of your existing event listeners for delete, export, etc.)
      });

      // Create Plan Button in Navbar and Main Content
      document.getElementById('newPlanBtn').addEventListener('click', function() {
        const createPlanModal = new bootstrap.Modal(document.getElementById('createPlanModal'));
        createPlanModal.show();
      });

      // Quick Create Plan Form Submission
      document.getElementById('createPlanModal').querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('index.php', {
            method: 'POST',
            body: formData
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              console.error('Failed to quick create plan:', data.error);
              alert('Failed to quick create plan. Please check console for details.');
            }
          })
          .catch(error => {
            console.error('Error during quick plan creation:', error);
            alert('An error occurred while quick creating the plan.');
          });
      });

      // Edit Button Logic to open planDetailsModal
      document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
          const planId = this.dataset.id;
          const planDetailsModalElement = document.getElementById('planDetailsModal');
          const planDetailsLoadingOverlay = document.getElementById('planDetailsLoadingOverlay');
          const planDetailsForm = document.getElementById('planDetailsForm');

          planDetailsLoadingOverlay.style.display = 'flex'; // Show loading overlay

          // Clear previous form data
          planDetailsForm.reset();
          document.getElementById('attributeGrid').innerHTML = '';
          document.getElementById('skillsTable').querySelector('tbody').innerHTML = '';
          document.getElementById('predictionsTable').querySelector('tbody').innerHTML = '';


          fetch(`fetch_plan_details.php?id=${planId}`)
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
              }
              return response.json();
            })
            .then(data => {
              if (data.success) {
                const plan = data.plan;
                document.getElementById('planId').value = plan.id;
                document.getElementById('plan_title').value = plan.plan_title || '';
                document.getElementById('modalName').value = plan.name;
                document.getElementById('modalCareerStage').value = plan.career_stage;
                document.getElementById('modalClass').value = plan.class;
                document.getElementById('modalRaceName').value = plan.race_name || '';
                document.getElementById('modalStrategy').value = plan.strategy_label.toLowerCase();
                document.getElementById('modalMood').value = plan.mood_label.toLowerCase();
                document.getElementById('modalStatus').value = plan.status;
                document.getElementById('modalGoal').value = plan.goal || '';
                document.getElementById('modalSource').value = plan.source || '';
                document.getElementById('modalTimeOfDay').value = plan.time_of_day || '';
                document.getElementById('modalMonth').value = plan.month || '';
                document.getElementById('modalTurnBefore').value = plan.turn_before || 0;
                document.getElementById('skillPoints').value = plan.total_available_skill_points || 0;
                document.getElementById('raceDaySwitch').checked = plan.race_day === 'yes';
                document.getElementById('energyRange').value = plan.energy || 0;
                updateProgressBar(parseInt(plan.energy || 0));

                document.getElementById('growthRateSpeed').value = plan.growth_rate_speed || 0;
                document.getElementById('growthRateStamina').value = plan.growth_rate_stamina || 0;
                document.getElementById('growthRatePower').value = plan.growth_rate_power || 0;
                document.getElementById('growthRateGuts').value = plan.growth_rate_guts || 0;
                document.getElementById('growthRateWit').value = plan.growth_rate_wit || 0;


                // Populate Attributes
                const attributeGrid = document.getElementById('attributeGrid');
                attributeGrid.innerHTML = ''; // Clear existing
                const attributeGradeOptions = <?php echo json_encode($attributeGradeOptions); ?>;

                plan.attributes.forEach(attr => {
                  const attrCol = document.createElement('div');
                  attrCol.className = 'col-md-4';
                  attrCol.innerHTML = `
                                  <label for="attr-${attr.attribute_name}" class="form-label">${attr.attribute_name}</label>
                                  <div class="input-group mb-3">
                                      <input type="number" class="form-control attribute-value-input"
                                             id="attr-${attr.attribute_name}"
                                             data-attribute-name="${attr.attribute_name}"
                                             value="${attr.value}" min="0" max="1200">
                                      <select class="form-select attribute-grade-input"
                                              data-attribute-name="${attr.attribute_name}">
                                          ${attributeGradeOptions.map(grade => `<option value="${grade}" ${grade === attr.grade ? 'selected' : ''}>${grade}</option>`).join('')}
                                      </select>
                                  </div>
                              `;
                  attributeGrid.appendChild(attrCol);
                });

                // Populate Skills
                const skillsTableBody = document.getElementById('skillsTable').querySelector('tbody');
                skillsTableBody.innerHTML = ''; // Clear existing
                plan.skills.forEach(skill => {
                  skillsTableBody.appendChild(createSkillRow(skill));
                });

                // Populate Predictions
                const predictionsTableBody = document.getElementById('predictionsTable').querySelector('tbody');
                predictionsTableBody.innerHTML = ''; // Clear existing
                plan.predictions.forEach(prediction => {
                  predictionsTableBody.appendChild(createPredictionRow(prediction));
                });


                const planDetailsModal = new bootstrap.Modal(planDetailsModalElement);
                planDetailsModal.show();
              } else {
                console.error('Failed to fetch plan details:', data.error);
                alert('Failed to fetch plan details. Please check console for details.');
              }
            })
            .catch(error => {
              console.error('Error fetching plan details:', error);
              alert('An error occurred while fetching plan details.');
            })
            .finally(() => {
              planDetailsLoadingOverlay.style.display = 'none'; // Hide loading overlay
            });
        });
      });

      // Handle planDetailsForm submission (Save Changes)
      document.getElementById('planDetailsForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Collect attributes data
        const attributes = [];
        document.querySelectorAll('#attributeGrid .attribute-value-input').forEach(input => {
          const name = input.dataset.attributeName;
          const value = input.value;
          const grade = input.closest('.input-group').querySelector('.attribute-grade-input').value;
          attributes.push({
            attribute_name: name,
            value: value,
            grade: grade
          });
        });
        formData.append('attributes', JSON.stringify(attributes));

        // Collect skills data
        const skills = [];
        document.querySelectorAll('#skillsTable tbody tr').forEach(row => {
          const skillName = row.querySelector('.skill-name-input').value;
          const spCost = row.querySelector('.skill-sp-cost-input').value;
          const acquired = row.querySelector('.skill-acquired-checkbox').checked ? 'yes' : 'no';
          const tag = row.querySelector('.skill-tag-select').value;
          const notes = row.querySelector('.skill-notes-input').value;

          if (skillName) { // Only add if skill name is not empty
            skills.push({
              skill_name: skillName,
              sp_cost: spCost,
              acquired: acquired,
              tag: tag,
              notes: notes
            });
          }
        });
        formData.append('skills', JSON.stringify(skills));

        // Collect predictions data
        const predictions = [];
        document.querySelectorAll('#predictionsTable tbody tr').forEach(row => {
          const raceName = row.querySelector('.prediction-race-name-input').value;
          const venue = row.querySelector('.prediction-venue-input').value;
          const ground = row.querySelector('.prediction-ground-input').value;
          const distance = row.querySelector('.prediction-distance-input').value;
          const track_condition = row.querySelector('.prediction-track-condition-input').value;
          const direction = row.querySelector('.prediction-direction-input').value;
          const speed = row.querySelector('.prediction-speed-input').value;
          const stamina = row.querySelector('.prediction-stamina-input').value;
          const power = row.querySelector('.prediction-power-input').value;
          const guts = row.querySelector('.prediction-guts-input').value;
          const wit = row.querySelector('.prediction-wit-input').value;
          const comment = row.querySelector('.prediction-comment-input').value;

          if (raceName) { // Only add if race name is not empty
            predictions.push({
              race_name: raceName,
              venue: venue,
              ground: ground,
              distance: distance,
              track_condition: track_condition,
              direction: direction,
              speed: speed,
              stamina: stamina,
              power: power,
              guts: guts,
              wit: wit,
              comment: comment
            });
          }
        });
        formData.append('predictions', JSON.stringify(predictions));

        fetch('index.php', {
            method: 'POST',
            body: formData
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              console.error('Failed to save the plan:', data.error);
              alert('Failed to save the plan. Please check console for details.');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the plan.');
          });
      });

      // Delete button logic
      document.querySelectorAll('.delete-btn').forEach(button => {
          button.addEventListener('click', function() {
              const planId = this.dataset.id;
              if (confirm('Are you sure you want to delete this plan?')) {
                const formData = new FormData();
                formData.append('delete_id', planId);

                fetch('index.php', {
                    method: 'POST',
                    body: formData
                  })
                  .then(response => {
                    if (!response.ok) {
                      throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                  })
                  .then(data => {
                    if (data.success) {
                      location.reload();
                    } else {
                      console.error('Failed to delete plan:', data.error);
                      alert('Failed to delete plan. Please check console for details.');
                    }
                  })
              .catch(error => {
                console.error('Error deleting plan:', error);
                alert('An error occurred while deleting the plan.');
              });
            }
          });
      });

    // Energy Range Progress Bar Update
    document.getElementById('energyRange').addEventListener('input', function() {
      updateProgressBar(this.value);
    });

    function updateProgressBar(value) {
      const progressBar = document.getElementById('energyProgress');
      progressBar.style.width = value + '%';
      if (value < 25) {
        progressBar.className = 'progress-bar bg-danger';
      } else if (value < 50) {
        progressBar.className = 'progress-bar bg-warning';
      } else if (value < 75) {
        progressBar.className = 'progress-bar bg-info';
      } else {
        progressBar.className = 'progress-bar bg-success';
      }
    }

    // Initial progress bar update
    updateProgressBar(document.getElementById('energyRange').value);

    // --- Dynamic Row Creation Functions ---

    // Skill Row Creation
    document.getElementById('addSkillBtn').addEventListener('click', function() {
      const skillsTableBody = document.getElementById('skillsTable').querySelector('tbody');
      skillsTableBody.appendChild(createSkillRow());
    });

    // Remove skill button logic (using event delegation)
    document.getElementById('skillsTable').addEventListener('click', function(e) {
      if (e.target.closest('.remove-skill-btn')) {
        e.target.closest('tr').remove();
      }
    });

    function createSkillRow(skill = {}) {
      const row = document.createElement('tr');
      // PHP variable to JS for skill tags
      const skillTagOptions = <?php echo json_encode($skillTagOptions); ?>;

      row.innerHTML = `
              <td><input type="text" class="form-control form-control-sm skill-name-input" value="${skill.skill_name || ''}"></td>
              <td><input type="number" class="form-control form-control-sm skill-sp-cost-input" value="${skill.sp_cost || ''}"></td>
              <td class="text-center">
                  <input type="checkbox" class="form-check-input skill-acquired-checkbox" ${skill.acquired === 'yes' ? 'checked' : ''}>
              </td>
              <td>
                  <select class="form-select form-select-sm skill-tag-select">
                      <option value="">Select Tag</option>
                      ${skillTagOptions.map(opt => `<option value="${opt.tag}" ${opt.tag === skill.tag ? 'selected' : ''}>${opt.tag}</option>`).join('')}
                  </select>
              </td>
              <td><input type="text" class="form-control form-control-sm skill-notes-input" value="${skill.notes || ''}"></td>
              <td>
                  <button type="button" class="btn btn-danger btn-sm remove-skill-btn">
                      <i class="bi bi-x-circle"></i>
                  </button>
              </td>
          `;
      return row;
    }

    // Prediction Row Creation
    document.getElementById('addPredictionBtn').addEventListener('click', function() {
      const predictionsTableBody = document.getElementById('predictionsTable').querySelector('tbody');
      predictionsTableBody.appendChild(createPredictionRow());
    });

    // Remove prediction button logic (using event delegation)
    document.getElementById('predictionsTable').addEventListener('click', function(e) {
      if (e.target.closest('.remove-prediction-btn')) {
        e.target.closest('tr').remove();
      }
    });

    function createPredictionRow(prediction = {}) {
      const row = document.createElement('tr');
      // PHP variable to JS for prediction icons
      const predictionIcons = <?php echo json_encode($predictionIcons); ?>;

      row.innerHTML = `
              <td><input type="text" class="form-control form-control-sm prediction-race-name-input" value="${prediction.race_name || ''}"></td>
              <td><input type="text" class="form-control form-control-sm prediction-venue-input" value="${prediction.venue || ''}"></td>
              <td><input type="text" class="form-control form-control-sm prediction-ground-input" value="${prediction.ground || ''}"></td>
              <td><input type="text" class="form-control form-control-sm prediction-distance-input" value="${prediction.distance || ''}"></td>
              <td><input type="text" class="form-control form-control-sm prediction-track-condition-input" value="${prediction.track_condition || ''}"></td>
              <td><input type="text" class="form-control form-control-sm prediction-direction-input" value="${prediction.direction || ''}"></td>
              <td>
                  <select class="form-select form-select-sm prediction-speed-input">
                      ${predictionIcons.map(icon => `<option value="${icon}" ${icon === prediction.speed ? 'selected' : ''}>${icon}</option>`).join('')}
                  </select>
              </td>
              <td>
                  <select class="form-select form-select-sm prediction-stamina-input">
                      ${predictionIcons.map(icon => `<option value="${icon}" ${icon === prediction.stamina ? 'selected' : ''}>${icon}</option>`).join('')}
                  </select>
              </td>
              <td>
                  <select class="form-select form-select-sm prediction-power-input">
                      ${predictionIcons.map(icon => `<option value="${icon}" ${icon === prediction.power ? 'selected' : ''}>${icon}</option>`).join('')}
                  </select>
              </td>
              <td>
                  <select class="form-select form-select-sm prediction-guts-input">
                      ${predictionIcons.map(icon => `<option value="${icon}" ${icon === prediction.guts ? 'selected' : ''}>${icon}</option>`).join('')}
                  </select>
              </td>
              <td>
                  <select class="form-select form-select-sm prediction-wit-input">
                      ${predictionIcons.map(icon => `<option value="${icon}" ${icon === prediction.wit ? 'selected' : ''}>${icon}</option>`).join('')}
                  </select>
              </td>
              <td><input type="text" class="form-control form-control-sm prediction-comment-input" value="${prediction.comment || ''}"></td>
              <td>
                  <button type="button" class="btn btn-danger btn-sm remove-prediction-btn">
                      <i class="bi bi-x-circle"></i>
                  </button>
              </td>
          `;
      return row;
    }
    // --- Export Functionality ---
    document.getElementById('exportPlanBtn').addEventListener('click', function() {
      const planId = document.getElementById('planId').value;
      if (planId) {
        exportPlanToClipboard(planId);
      } else {
        showMessageBox('Please select a plan to export.');
      }
    });

    async function exportPlanToClipboard(planId) {
      planDetailsLoadingOverlay.style.display = 'flex'; // Show loading overlay
      try {
        const response = await fetch(`export_plan_data.php?id=${planId}`);
        const planData = await response.json();

        if (planData.error) {
          showMessageBox(`Error exporting plan: ${planData.error}`);
          return;
        }

        let exportText = "";

        // PLAN section
        exportText += `PLAN\n`;
        exportText += `0\n`; // As per example
        exportText += `TURN BEFORE \t${planData.turn_before || ''}\n`;
        exportText += `RACE\n`;
        exportText += `*Start at n turns and ends as 1 turn, including Rest with the Training.\n`;
        exportText += `*Training for one stat per turn including Rest, Infirmary & Recreation.\n`;
        exportText += `Name\n`;
        exportText += `Career Stage\n`;
        exportText += `Class\n`;
        exportText += `${planData.name || ''}\n`;
        exportText += `${planData.career_stage ? planData.career_stage.toUpperCase() + ' SEASON' : ''}\n\n`; // Example: FINALE SEASON
        exportText += `${planData.class ? planData.class.toUpperCase() : ''}\n`;

        // Attributes section
        exportText += `Attribute\tValue (x/1200)\tGrade\n`;
        const attributeMap = {};
        planData.attributes.forEach(attr => {
          attributeMap[attr.attribute_name.toUpperCase()] = attr;
        });
        ['SPEED', 'STAMINA', 'POWER', 'GUTS', 'WIT'].forEach(attrName => {
          const attr = attributeMap[attrName] || {
            value: 0,
            grade: 'G'
          };
          exportText += `${attrName}\t${attr.value}\t${attr.grade}\n`;
        });

        // Skills section
        exportText += `\nSkill Name\tSP Cost\tAcquired (✅/❌)\tNotes\n`;
        planData.skills.forEach(skill => {
          const acquiredStatus = skill.acquired === 'yes' ? '✅' : '❌';
          const notes = skill.notes ? skill.notes : '';
          exportText += `${skill.skill_name}\t${skill.sp_cost}\t${acquiredStatus}\t${notes}\n`;
        });

        // Total Available Skill Points
        exportText += `TOTAL AVAILABLE SKILL POINTS\n`;
        exportText += `${planData.total_available_skill_points || 0}\n`;
        exportText += `ACQUIRE SKILL?\n`;
        exportText += `${planData.acquire_skill ? planData.acquire_skill.toUpperCase() : 'NO'}\n`;

        // Conditions, Mood, Energy, Race Day, Goal, Strategy
        exportText += `CONDITIONS\n`;
        exportText += `${planData.condition_label || 'N/A'}\n`; // Assuming condition_label is fetched
        exportText += `MOOD\n`;
        exportText += `${planData.mood_label || 'N/A'}\n`; // Assuming mood_label is fetched
        exportText += `ENERGY (n/100%)\n`;
        exportText += `${planData.energy || 0}\n`;
        exportText += `RACE DAY?\n`;
        exportText += `${planData.race_day ? planData.race_day.toUpperCase() : 'NO'}\n`;
        exportText += `GOAL\n`;
        exportText += `${planData.plan_goal || ''}\n`; // Using plan_goal from main plan table
        exportText += `STRATEGY\n`;
        exportText += `${planData.strategy_label ? planData.strategy_label.toUpperCase() : 'N/A'}\n`; // Assuming strategy_label is fetched
        exportText += `*Energy does not affect race performance in Uma Musume. It only matters for career mode activities like training and events.\n`;

        // Growth Rate
        exportText += `GROWTH RATE (%)\n`;
        exportText += `SPEED\n`;
        exportText += `STAMINA\n`;
        exportText += `POWER\n`;
        exportText += `GUTS\n`;
        exportText += `WIT\n`;
        exportText += `${planData.growth_rate_speed || 0}\n`;
        exportText += `${planData.growth_rate_stamina || 0}\n`;
        exportText += `${planData.growth_rate_power || 0}\n`;
        exportText += `${planData.growth_rate_guts || 0}\n`;
        exportText += `${planData.growth_rate_wit || 0}\n`;

        // Terrain, Distance, Style Grades
        exportText += `Terrain\tGrade\n`;
        planData.terrain_grades.forEach(tg => {
          exportText += `${tg.terrain}\t${tg.grade}\n`;
        });
        exportText += `Distance\tGrade\n`;
        planData.distance_grades.forEach(dg => {
          exportText += `${dg.distance}\t${dg.grade}\n`;
        });
        exportText += `Style\tGrade\n`;
        planData.style_grades.forEach(sg => {
          exportText += `${sg.style}\t${sg.grade}\n`;
        });

        // Race Day Predictions
        exportText += `\nRACE DAY PREDICTIONS:\n`;
        planData.race_predictions.forEach(pred => {
          exportText += `${pred.race_name || ''}\n`;
          exportText += `${pred.venue || ''}\n`;
          exportText += `${pred.ground || ''}\n`;
          exportText += `${pred.distance || ''}\n`;
          exportText += `${pred.track_condition || ''}\n`;
          exportText += `${pred.direction || ''}\n`;
          exportText += `SPEED\nSTAMINA\nPOWER\nGUTS\nWIT\n`;
          exportText += `${pred.speed}\n${pred.stamina}\n${pred.power}\n${pred.guts}\n${pred.wit}\n`;
          exportText += `"${pred.comment || ''}"\n\n`; // Comment in quotes
        });

        // Goals (from goals table)
        exportText += `GOALS\n`;
        planData.goals.forEach(goal => {
          exportText += `${goal.goal ? goal.goal.toUpperCase() : ''}\n`;
          exportText += `${goal.result ? goal.result.toUpperCase() : ''}\n`;
        });

        // Copy to clipboard
        await navigator.clipboard.writeText(exportText);
        showMessageBox('Plan data copied to clipboard!');

      } catch (error) {
        console.error("Error exporting plan:", error);
        showMessageBox('Failed to export plan data. Please try again.');
      } finally {
        planDetailsLoadingOverlay.style.display = 'none'; // Hide loading overlay
      }
    }
    });
  </script>
  <div id="messageBox" class="message-box">
    <span id="messageText"></span>
  </div>
</body>

</html>