<?php
// get_plan_attributes.php
require_once 'config.php';

$plan_id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM attributes WHERE plan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$result = $stmt->get_result();

$attributes = [];
while ($row = $result->fetch_assoc()) {
    $attributes[] = $row;
}

header('Content-Type: application/json');
echo json_encode($attributes);
?>