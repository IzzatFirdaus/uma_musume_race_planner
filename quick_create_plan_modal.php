<?php
// quick_create_plan_modal.php
// This file assumes $careerStageOptions and $classOptions are available from index.php
// Do not include config.php here, as it will be included in index.php
?>

<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPlanModalLabel">Quick Create Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="trainee_name" class="form-label">Trainee Name</label>
                        <input type="text" class="form-control" id="trainee_name" name="trainee_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="race_name" class="form-label">Next Race Name</label>
                        <input type="text" class="form-control" id="race_name" name="race_name">
                    </div>
                    <div class="mb-3">
                        <label for="career_stage" class="form-label">Career Stage</label>
                        <select class="form-select" id="career_stage" name="career_stage" required>
                            <?php foreach ($careerStageOptions as $option) : ?>
                                <option value="<?php echo $option['value']; ?>"><?php echo $option['text']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="traineeClass" class="form-label">Class</label>
                        <select class="form-select" id="traineeClass" name="traineeClass" required>
                            <?php foreach ($classOptions as $option) : ?>
                                <option value="<?php echo $option['value']; ?>"><?php echo $option['text']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-uma">Create Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>