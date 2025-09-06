{{--
    This is a new, reusable partial for the plan form tabs.
    It expects a variable `$id_suffix` ('_inline' or '') to be passed for unique element IDs.
    It also expects data for dropdowns (e.g., $careerStageOptions) to be passed from the controller.
--}}

@props(['id_suffix' => ''])

<div>
    <ul class="nav nav-tabs" id="planTabs{{ $id_suffix }}" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab{{ $id_suffix }}" data-bs-toggle="tab" data-bs-target="#general{{ $id_suffix }}" type="button" role="tab">General</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="attributes-tab{{ $id_suffix }}" data-bs-toggle="tab" data-bs-target="#attributes{{ $id_suffix }}" type="button" role="tab">Attributes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="grades-tab{{ $id_suffix }}" data-bs-toggle="tab" data-bs-target="#grades{{ $id_suffix }}" type="button" role="tab">Aptitude Grades</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="skills-tab{{ $id_suffix }}" data-bs-toggle="tab" data-bs-target="#skills{{ $id_suffix }}" type="button" role="tab">Skills</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="predictions-tab{{ $id_suffix }}" data-bs-toggle="tab" data-bs-target="#predictions{{ $id_suffix }}" type="button" role="tab">Race Predictions</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="goals-tab{{ $id_suffix }}" data-bs-toggle="tab" data-bs-target="#goals{{ $id_suffix }}" type="button" role="tab">Goals</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="progress-chart-tab{{ $id_suffix }}" data-bs-toggle="tab" data-bs-target="#progress-chart{{ $id_suffix }}" type="button" role="tab">Progress Chart</button>
        </li>
    </ul>

    <div class="tab-content pt-3">
        {{-- General Tab --}}
        <div class="tab-pane fade show active" id="general{{ $id_suffix }}" role="tabpanel">
            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="plan_title{{ $id_suffix }}" class="form-label">Plan Title</label>
                    <input type="text" class="form-control" id="plan_title{{ $id_suffix }}" name="plan_title">
                    <input type="hidden" id="planId{{ $id_suffix }}" name="planId">
                </div>
                <div class="col-md-4">
                    <label for="modalTurnBefore{{ $id_suffix }}" class="form-label">Turn Before</label>
                    <input type="number" class="form-control" id="modalTurnBefore{{ $id_suffix }}" name="modalTurnBefore">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalName{{ $id_suffix }}" class="form-label">Trainee Name</label>
                    <input type="text" class="form-control" id="modalName{{ $id_suffix }}" name="modalName" required>
                </div>
                <div class="col-md-6">
                    <label for="modalRaceName{{ $id_suffix }}" class="form-label">Next Race Name</label>
                    <input type="text" class="form-control" id="modalRaceName{{ $id_suffix }}" name="modalRaceName">
                </div>
            </div>
            <div class="row mb-3">
                @livewire('trainee-image-handler')
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="modalCareerStage{{ $id_suffix }}" class="form-label">Career Stage</label>
                            <select class="form-select" id="modalCareerStage{{ $id_suffix }}" name="modalCareerStage">
                                <option value="" selected disabled>Select Stage</option>
                                @foreach ($careerStageOptions ?? [] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="modalClass{{ $id_suffix }}" class="form-label">Class</label>
                            <select class="form-select" id="modalClass{{ $id_suffix }}" name="modalClass">
                                <option value="" selected disabled>Select Class</option>
                                @foreach ($classOptions ?? [] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- ...other tabs (attributes, grades, skills, predictions, goals, progress chart) would be restored here as needed... --}}
    </div>
</div>
