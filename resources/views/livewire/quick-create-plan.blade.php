<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-theme">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="createPlanModalLabel">
                    <i class="bi bi-plus-circle me-2" aria-hidden="true"></i>
                    Quick Create Plan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form wire:submit.prevent="save" id="quickCreatePlanForm" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quick_trainee_name" class="form-label">Trainee Name</label>
                        <input type="text" class="form-control" id="quick_trainee_name" name="trainee_name" wire:model.defer="trainee_name" required aria-describedby="traineeNameFeedback">
                        @error('trainee_name')
                            <div class="invalid-feedback d-block" id="traineeNameFeedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="quick_race_name" class="form-label">Next Race Name</label>
                        <input type="text" class="form-control" id="quick_race_name" name="race_name" wire:model.defer="race_name">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_career_stage" class="form-label">Career Stage</label>
                            <select class="form-select" id="quick_career_stage" name="career_stage" wire:model.defer="career_stage" required aria-describedby="careerStageFeedback">
                                <option value="" selected disabled>Select Stage</option>
                                @foreach ($careerStageOptions ?? [] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                @endforeach
                            </select>
                            @error('career_stage')
                                <div class="invalid-feedback d-block" id="careerStageFeedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_traineeClass" class="form-label">Class</label>
                            <select class="form-select" id="quick_traineeClass" name="traineeClass" wire:model.defer="traineeClass" required aria-describedby="classFeedback">
                                <option value="" selected disabled>Select Class</option>
                                @foreach ($classOptions ?? [] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                @endforeach
                            </select>
                            @error('traineeClass')
                                <div class="invalid-feedback d-block" id="classFeedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-uma" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check-lg me-1"></i>
                            Create Plan
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
