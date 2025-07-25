<?php
// get_stats.php
require_once 'config.php'; // Assumes config.php provides $conn (MySQLi connection)

header('Content-Type: application/json');

try {
  // Use MySQLi connection ($conn)
  $total = $conn->query("SELECT COUNT(*) FROM plans WHERE deleted_at IS NULL")->fetch_row()[0];
  $active = $conn->query("SELECT COUNT(*) FROM plans WHERE status = 'Active' AND deleted_at IS NULL")->fetch_row()[0];
  $finished = $conn->query("SELECT COUNT(*) FROM plans WHERE status = 'Finished' AND deleted_at IS NULL")->fetch_row()[0]; // Added finished plans stat
  $planning = $conn->query("SELECT COUNT(*) FROM plans WHERE status = 'Planning' AND deleted_at IS NULL")->fetch_row()[0]; // Added planning plans stat
  $unique = $conn->query("SELECT COUNT(DISTINCT name) FROM plans WHERE deleted_at IS NULL")->fetch_row()[0];
  $predictions = $conn->query("SELECT COUNT(*) FROM race_predictions")->fetch_row()[0];

  echo json_encode([
    'success' => true,
    'stats' => [
      'total_plans' => (int)$total,
      'active_plans' => (int)$active,
      'finished_plans' => (int)$finished, // Included in response
      'planning_plans' => (int)$planning, // Included in response
      'unique_trainees' => (int)$unique,
      'race_predictions' => (int)$predictions,
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>