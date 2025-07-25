<?php
// get_plans.php
require_once 'config.php'; // Assumes config.php provides $conn (MySQLi connection)

header('Content-Type: application/json');

try {
  // Use MySQLi connection ($conn)
  $query = "
    SELECT p.*, m.label AS mood, s.label AS strategy
    FROM plans p
    LEFT JOIN moods m ON p.mood_id = m.id
    LEFT JOIN strategies s ON p.strategy_id = s.id
    WHERE p.deleted_at IS NULL
    ORDER BY p.updated_at DESC
  ";
  $result = $conn->query($query);

  if ($result) {
    $plans = [];
    while ($row = $result->fetch_assoc()) {
      $plans[] = $row;
    }
    echo json_encode(['success' => true, 'plans' => $plans]);
  } else {
    // Handle query error
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $conn->error]);
  }

} catch (Throwable $e) {
  // Catch any unexpected exceptions
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>