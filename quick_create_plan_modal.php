<?php
// quick_create_plan_modal.php
// Assumes $careerStageOptions and $classOptions are provided from index.php; ensures safe defaults.
$careerStageOptions = $careerStageOptions ?? [];
$classOptions = $classOptions ?? [];

?>
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-describedby="createPlanModalDesc">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="createPlanModalLabel">Quick Create Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close dialog"></button>
            </div>

            <div id="createPlanModalDesc" class="visually-hidden">Create a new plan with minimum required details like trainee name, career stage, and class.</div>

            <form id="quickCreatePlanForm" novalidate autocomplete="off">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quick_trainee_name" class="form-label">Trainee Name</label>
                        <input type="text" class="form-control" id="quick_trainee_name" name="trainee_name" required maxlength="150" aria-describedby="traineeNameFeedback">
                        <div class="invalid-feedback" id="traineeNameFeedback">
                            Trainee Name is required.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quick_race_name" class="form-label">Next Race Name</label>
                        <input type="text" class="form-control" id="quick_race_name" name="race_name" maxlength="200">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_career_stage" class="form-label">Career Stage</label>
                            <select class="form-select" id="quick_career_stage" name="career_stage" required aria-describedby="careerStageFeedback">
                                <option value="" selected disabled>Select Stage</option>
                                <?php foreach ($careerStageOptions as $option) : ?>
                                    <option value="<?= htmlspecialchars((string) ($option['value'] ?? '')) ?>">
                                        <?= htmlspecialchars((string) ($option['text'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="careerStageFeedback">
                                Career Stage is required.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_traineeClass" class="form-label">Class</label>
                            <select class="form-select" id="quick_traineeClass" name="traineeClass" required aria-describedby="classFeedback">
                                <option value="" selected disabled>Select Class</option>
                                <?php foreach ($classOptions as $option) : ?>
                                    <option value="<?= htmlspecialchars((string) ($option['value'] ?? '')) ?>">
                                        <?= htmlspecialchars((string) ($option['text'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback" id="classFeedback">
                                Class is required.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-uma" id="quickCreateSubmitBtn">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$scriptBase = rtrim(preg_replace('~/public/?$~', '/', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/')), '/') . '/';
?>
<script defer src="<?= htmlspecialchars($scriptBase, ENT_QUOTES, 'UTF-8') ?>assets/js/quick_create_modal.js"></script>
