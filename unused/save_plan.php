<?php
require_once 'config.php';
$pdo = require_once 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

try {
  $pdo->beginTransaction();

  $isNew = empty($data['id']);
  $now = date('Y-m-d H:i:s');

  if ($isNew) {
    $stmt = $pdo->prepare("INSERT INTO plans (plan_title, name, race_name, career_stage, class, month, turn_before, total_available_skill_points, acquire_skill, mood_id, condition_id, energy, goal, strategy_id, status, created_at, updated_at)
      VALUES (:plan_title, :name, :race_name, :career_stage, :class, :month, :turn_before, :total_available_skill_points, :acquire_skill, :mood_id, :condition_id, :energy, :goal, :strategy_id, :status, :created_at, :updated_at)");
  } else {
    $stmt = $pdo->prepare("UPDATE plans SET plan_title=:plan_title, name=:name, race_name=:race_name, career_stage=:career_stage, class=:class, month=:month, turn_before=:turn_before, total_available_skill_points=:total_available_skill_points, acquire_skill=:acquire_skill, mood_id=:mood_id, condition_id=:condition_id, energy=:energy, goal=:goal, strategy_id=:strategy_id, status=:status, updated_at=:updated_at WHERE id=:id");
    $stmt->bindParam(':id', $data['id']);
  }

  foreach (['plan_title','name','race_name','career_stage','class','month','turn_before','total_available_skill_points','acquire_skill','mood_id','condition_id','energy','goal','strategy_id','status'] as $key) {
    $stmt->bindValue(":$key", $data[$key] ?? null);
  }
  $stmt->bindValue(':created_at', $now);
  $stmt->bindValue(':updated_at', $now);
  $stmt->execute();

  $planId = $isNew ? $pdo->lastInsertId() : $data['id'];

  // Clean up related data
  $pdo->prepare("DELETE FROM attributes WHERE plan_id=?")->execute([$planId]);
  $pdo->prepare("DELETE FROM skills WHERE plan_id=?")->execute([$planId]);

  // Re-insert attributes
  if (!empty($data['attributes'])) {
    $stmt = $pdo->prepare("INSERT INTO attributes (plan_id, attribute_name, value, grade) VALUES (?, ?, ?, ?)");
    foreach ($data['attributes'] as $attr) {
      $stmt->execute([$planId, $attr['attribute_name'], $attr['value'], $attr['grade']]);
    }
  }

  // Re-insert skills
  if (!empty($data['skills'])) {
    $stmt = $pdo->prepare("INSERT INTO skills (plan_id, skill_name, sp_cost, acquired, tag, notes) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($data['skills'] as $s) {
      $stmt->execute([$planId, $s['skill_name'], $s['sp_cost'], $s['acquired'], $s['tag'], $s['notes']]);
    }
  }

  // Log activity
  $pdo->prepare("INSERT INTO activity_log (description, icon_class) VALUES (?, ?)")
      ->execute([$isNew ? "Created plan: {$data['name']}" : "Updated plan: {$data['name']}", $isNew ? 'bi-plus-circle text-success' : 'bi-pencil text-warning']);

  $pdo->commit();
  echo json_encode(['success' => true, 'plan_id' => $planId]);
} catch (Throwable $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
