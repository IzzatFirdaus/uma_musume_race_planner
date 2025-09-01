<?php
// components/x-skill-row.php
// Skill row partial used inside plan forms or modals.
// Expected variables before include:
// - $index (int) row index
// - $skill_name (string) initial skill name
// - $sp (int) skill point cost
// - $acquired (bool) whether the skill is already acquired

if (!isset($index)) $index = 0;
if (!isset($skill_name)) $skill_name = '';
if (!isset($sp)) $sp = 0;
if (!isset($acquired)) $acquired = false;

$rowId = 'skill-row-' . intval($index);
?>
<div class="skill-row d-flex align-items-center mb-2" data-index="<?= intval($index) ?>" id="<?= htmlspecialchars($rowId) ?>">
  <input type="hidden" name="skills[<?= intval($index) ?>][skill_name]" value="<?= htmlspecialchars($skill_name) ?>" class="skill-name-hidden">
  <div class="me-2" style="flex:1 1 40%;">
    <input type="text" name="skills[<?= intval($index) ?>][skill_name_input]" class="form-control form-control-sm skill-name-input" placeholder="Skill name" value="<?= htmlspecialchars($skill_name) ?>" data-autosuggest="skill" aria-label="Skill name" aria-describedby="skill-name-help-<?= intval($index) ?>">
    <div id="skill-name-help-<?= intval($index) ?>" class="form-text small">Start typing to search skills.</div>
  </div>
  <div class="me-2" style="width:90px;">
    <input type="number" name="skills[<?= intval($index) ?>][sp_cost]" class="form-control form-control-sm skill-sp-input" min="0" value="<?= intval($sp) ?>" aria-label="SP cost">
  </div>
  <div class="me-2" style="width:120px;">
    <input type="text" name="skills[<?= intval($index) ?>][tag]" class="form-control form-control-sm skill-tag-input" placeholder="Tag" aria-label="Skill tag">
  </div>
  <div class="me-2" style="flex:1 1 30%;">
    <input type="text" name="skills[<?= intval($index) ?>][notes]" class="form-control form-control-sm skill-notes-input" placeholder="Notes" aria-label="Notes">
  </div>
  <div class="form-check form-switch me-2">
    <input class="form-check-input skill-acquired-toggle" type="checkbox" role="switch" <?= $acquired ? 'checked' : '' ?> aria-label="Acquired" aria-checked="<?= $acquired ? 'true' : 'false' ?>">
    <input type="hidden" name="skills[<?= intval($index) ?>][acquired]" value="<?= $acquired ? 'yes' : 'no' ?>" class="skill-acquired-input">
  </div>
  <button type="button" class="btn btn-sm btn-outline-danger btn-skill-remove" aria-label="Remove skill">&times;</button>
</div>
