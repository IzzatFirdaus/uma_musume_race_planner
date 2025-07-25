<?php
// get_plan_predictions.php
require_once 'config.php';

$plan_id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM race_predictions WHERE plan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$result = $stmt->get_result();

$predictions = [];
while ($row = $result->fetch_assoc()) {
    $predictions[] = $row;
}

header('Content-Type: application/json');
echo json_encode($predictions);
?>
