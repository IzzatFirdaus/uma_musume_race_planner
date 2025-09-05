{{-- Converted from quick_create_plan_modal.php --}}
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="createPlanModalLabel">Quick Create Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="quickCreatePlanForm" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quick_trainee_name" class="form-label">Trainee Name</label>
                        <input type="text" class="form-control" id="quick_trainee_name" name="trainee_name" required aria-describedby="traineeNameFeedback">
                        <div class="invalid-feedback" id="traineeNameFeedback">
                            Trainee Name is required.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quick_race_name" class="form-label">Next Race Name</label>
                        <input type="text" class="form-control" id="quick_race_name" name="race_name">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_career_stage" class="form-label">Career Stage</label>
                            <select class="form-select" id="quick_career_stage" name="career_stage" required aria-describedby="careerStageFeedback">
                                <option value="" selected disabled>Select Stage</option>
                                {{-- UPDATED: PHP loop converted to Blade @foreach --}}
                                @foreach ($careerStageOptions ?? [] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="careerStageFeedback">
                                Career Stage is required.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_traineeClass" class="form-label">Class</label>
                            <select class="form-select" id="quick_traineeClass" name="traineeClass" required aria-describedby="classFeedback">
                                <option value="" selected disabled>Select Class</option>
                                {{-- UPDATED: PHP loop converted to Blade @foreach --}}
                                @foreach ($classOptions ?? [] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                @endforeach
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

@push('scripts')
<script>
    // This script handles the client-side validation for the quick create form.
    // The actual form submission is handled by the global event listener in app.js.
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('quickCreatePlanForm');
        if (!form) return;

        // Add Bootstrap's validation classes on submission attempt.
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // Reset validation state when the modal is hidden.
        const createPlanModal = document.getElementById('createPlanModal');
        if (createPlanModal) {
            createPlanModal.addEventListener('hidden.bs.modal', function() {
                form.classList.remove('was-validated');
                form.reset();
            });
        }
    });
</script>
@endpush
