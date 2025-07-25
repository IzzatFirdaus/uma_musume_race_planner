<?php
// get_plan_skills.php
require_once 'config.php';

$plan_id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM skills WHERE plan_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$result = $stmt->get_result();

$skills = [];
while ($row = $result->fetch_assoc()) {
    $skills[] = $row;
}

header('Content-Type: application/json');
echo json_encode($skills);
?>
