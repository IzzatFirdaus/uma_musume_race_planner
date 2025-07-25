<?php
require_once 'config.php';
$pdo = require_once 'db.php';

header('Content-Type: application/json');

// Parse incoming JSON payload
$data = json_decode(file_get_contents('php://input'), true);
$plan = $data['plan'] ?? null;

if (!$plan || !is_array($plan)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Missing or invalid plan data']);
  exit;
}

try {
  $pdo->beginTransaction();

  // Insert into `plans`
  $stmt = $pdo->prepare("
    INSERT INTO plans (
      plan_title, name, race_name, career_stage, class, time_of_day, month, turn_before,
      total_available_skill_points, acquire_skill, mood_id, condition_id, energy,
      goal, strategy_id, status, source, created_at, updated_at
    ) VALUES (
      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
    )
  ");
  $stmt->execute([
    $plan['plan_title'] ?? null,
    $plan['name'] ?? null,
    $plan['race_name'] ?? null,
    $plan['career_stage'] ?? null,
    $plan['class'] ?? null,
    $plan['time_of_day'] ?? null,
    $plan['month'] ?? null,
    $plan['turn_before'] ?? null,
    $plan['total_available_skill_points'] ?? 0,
    $plan['acquire_skill'] ?? 'NO',
    $plan['mood_id'] ?? null,
    $plan['condition_id'] ?? null,
    $plan['energy'] ?? null,
    $plan['goal'] ?? null,
    $plan['strategy_id'] ?? null,
    $plan['status'] ?? 'Planning',
    $plan['source'] ?? 'Import'
  ]);

  $planId = $pdo->lastInsertId();

  // Define related tables and expected columns
  $related = [
    'attributes' => ['attribute_name', 'value', 'grade'],
    'skills' => ['skill_name', 'sp_cost', 'acquired', 'tag', 'notes'],
    'terrain_grades' => ['terrain', 'grade'],
    'distance_grades' => ['distance', 'grade'],
    'style_grades' => ['style', 'grade'],
    'race_predictions' => ['race_name', 'venue', 'ground', 'distance', 'track_condition', 'direction', 'speed', 'stamina', 'power', 'guts', 'wit', 'comment'],
    'goals' => ['goal', 'result'],
    'turns' => ['turn_number', 'speed', 'stamina', 'power', 'guts', 'wit']
  ];

  foreach ($related as $table => $columns) {
    if (!empty($plan[$table]) && is_array($plan[$table])) {
      $placeholders = implode(',', array_fill(0, count($columns) + 1, '?')); // +1 for plan_id
      $colnames = implode(',', array_merge($columns, ['plan_id']));
      $stmt = $pdo->prepare("INSERT INTO `$table` ($colnames) VALUES ($placeholders)");

      foreach ($plan[$table] as $row) {
        $values = array_map(fn($col) => $row[$col] ?? null, $columns);
        $values[] = $planId;
        $stmt->execute($values);
      }
    }
  }

  // Log import event
  $pdo->prepare("INSERT INTO activity_log (description, icon_class) VALUES (?, ?)")
      ->execute(["Imported plan: {$plan['name']}", 'bi-cloud-arrow-down text-primary']);

  $pdo->commit();
  echo json_encode(['success' => true, 'new_plan_id' => $planId]);
} catch (Throwable $e) {
  $pdo->rollBack();
  error_log("Import error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Failed to import plan.']);
}
