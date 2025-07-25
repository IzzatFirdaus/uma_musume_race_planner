<?php
require_once 'config.php';
$pdo = require_once 'db.php';

header('Content-Type: application/json');

$planId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($planId <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Missing or invalid plan ID']);
  exit;
}

try {
  // Get core plan
  $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
  $stmt->execute([$planId]);
  $planData = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$planData) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Plan not found']);
    exit;
  }

  // Remove unnecessary fields (optional)
  unset($planData['id'], $planData['deleted_at'], $planData['created_at'], $planData['updated_at']);

  // Related tables
  $related = [
    'attributes',
    'skills',
    'terrain_grades',
    'distance_grades',
    'style_grades',
    'race_predictions',
    'goals',
    'turns'
  ];

  foreach ($related as $table) {
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE plan_id = ?");
    $stmt->execute([$planId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Optionally strip `id` and `plan_id` fields for portability
    foreach ($rows as &$row) {
      unset($row['id'], $row['plan_id']);
    }

    $planData[$table] = $rows;
  }

  echo json_encode([
    'success' => true,
    'plan' => $planData,
    'filename' => 'plan_export_' . $planId . '.json'
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  error_log("Export failed: " . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Failed to export plan']);
}
