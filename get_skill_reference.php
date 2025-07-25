<?php
// get_skill_reference.php
require_once 'config.php';

// Select both skill_name and tag
$sql = "SELECT skill_name, tag FROM skill_reference ORDER BY skill_name";
$result = $conn->query($sql);

$skills = [];
while ($row = $result->fetch_assoc()) {
    $skills[] = $row;
}

header('Content-Type: application/json');
echo json_encode($skills);
?>
