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

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="modalGoal{{ $id_suffix }}" class="form-label">Primary Goal</label>
                    <input type="text" class="form-control" id="modalGoal{{ $id_suffix }}" name="modalGoal">
                </div>
                <div class="col-md-4">
                    <label for="modalStrategy{{ $id_suffix }}" class="form-label">Strategy</label>
                    <select class="form-select" id="modalStrategy{{ $id_suffix }}" name="modalStrategy">
                        <option value="">Select Strategy</option>
                        @foreach (($strategyOptions ?? []) as $opt)
                            <option value="{{ $opt['id'] ?? $opt['value'] ?? '' }}">{{ $opt['label'] ?? $opt['text'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="modalMood{{ $id_suffix }}" class="form-label">Mood</label>
                    <select class="form-select" id="modalMood{{ $id_suffix }}" name="modalMood">
                        <option value="">Select Mood</option>
                        @foreach (($moodOptions ?? []) as $opt)
                            <option value="{{ $opt['id'] ?? $opt['value'] ?? '' }}">{{ $opt['label'] ?? $opt['text'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="modalCondition{{ $id_suffix }}" class="form-label">Condition</label>
                    <select class="form-select" id="modalCondition{{ $id_suffix }}" name="modalCondition">
                        <option value="">Select Condition</option>
                        @foreach (($conditionOptions ?? []) as $opt)
                            <option value="{{ $opt['id'] ?? $opt['value'] ?? '' }}">{{ $opt['label'] ?? $opt['text'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Energy</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" min="0" max="100" step="1" class="form-range" id="energyRange{{ $id_suffix }}" name="energyRange">
                        <span class="badge bg-secondary" id="energyValue{{ $id_suffix }}">0</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="raceDaySwitch{{ $id_suffix }}" name="raceDaySwitch">
                        <label class="form-check-label" for="raceDaySwitch{{ $id_suffix }}">Race Day</label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="acquireSkillSwitch{{ $id_suffix }}" name="acquireSkillSwitch">
                        <label class="form-check-label" for="acquireSkillSwitch{{ $id_suffix }}">Acquire Skill</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="skillPoints{{ $id_suffix }}" class="form-label">Skill Points</label>
                    <input type="number" class="form-control" id="skillPoints{{ $id_suffix }}" name="skillPoints">
                </div>
                <div class="col-md-3">
                    <label for="modalStatus{{ $id_suffix }}" class="form-label">Status</label>
                    <select class="form-select" id="modalStatus{{ $id_suffix }}" name="modalStatus">
                        <option value="Planning">Planning</option>
                        <option value="Active">Active</option>
                        <option value="Finished">Finished</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="modalTimeOfDay{{ $id_suffix }}" class="form-label">Time of Day</label>
                    <input type="text" class="form-control" id="modalTimeOfDay{{ $id_suffix }}" name="modalTimeOfDay" placeholder="e.g. Morning, Noon, Evening">
                </div>
                <div class="col-md-3">
                    <label for="modalMonth{{ $id_suffix }}" class="form-label">Month</label>
                    <input type="text" class="form-control" id="modalMonth{{ $id_suffix }}" name="modalMonth" placeholder="e.g. January">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalSource{{ $id_suffix }}" class="form-label">Source</label>
                    <input type="text" class="form-control" id="modalSource{{ $id_suffix }}" name="modalSource">
                </div>
                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-12"><label class="form-label">Growth Rates</label></div>
                        <div class="col-6">
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Speed</span>
                                <input type="number" class="form-control" id="growthRateSpeed{{ $id_suffix }}" name="growthRateSpeed">
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Power</span>
                                <input type="number" class="form-control" id="growthRatePower{{ $id_suffix }}" name="growthRatePower">
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Wit</span>
                                <input type="number" class="form-control" id="growthRateWit{{ $id_suffix }}" name="growthRateWit">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Stamina</span>
                                <input type="number" class="form-control" id="growthRateStamina{{ $id_suffix }}" name="growthRateStamina">
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Guts</span>
                                <input type="number" class="form-control" id="growthRateGuts{{ $id_suffix }}" name="growthRateGuts">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attributes Tab --}}
        <div class="tab-pane fade" id="attributes{{ $id_suffix }}" role="tabpanel">
            <div id="attributesContainer{{ $id_suffix }}" class="row g-3"></div>
        </div>

        {{-- Aptitude Grades Tab --}}
        <div class="tab-pane fade" id="grades{{ $id_suffix }}" role="tabpanel">
            <div id="gradesContainer{{ $id_suffix }}" class="row g-3"></div>
        </div>

        {{-- Skills Tab --}}
        <div class="tab-pane fade" id="skills{{ $id_suffix }}" role="tabpanel">
            <div class="d-flex justify-content-end mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="addSkillBtn{{ $id_suffix === '_inline' ? 'Inline' : '' }}">Add Skill</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm" id="skillsTable{{ $id_suffix === '_inline' ? 'Inline' : '' }}">
                    <thead>
                        <tr>
                            <th style="width: 30%">Name</th>
                            <th style="width: 15%">Tag</th>
                            <th style="width: 10%">Acquired</th>
                            <th>Notes</th>
                            <th style="width: 40px"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Race Predictions Tab --}}
        <div class="tab-pane fade" id="predictions{{ $id_suffix }}" role="tabpanel">
            <div class="d-flex justify-content-end mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="addPredictionBtn{{ $id_suffix === '_inline' ? 'Inline' : '' }}">Add Prediction</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm" id="predictionsTable{{ $id_suffix === '_inline' ? 'Inline' : '' }}">
                    <thead>
                        <tr>
                            <th>Race Name</th>
                            <th>Venue</th>
                            <th>Ground</th>
                            <th>Distance</th>
                            <th>Speed</th>
                            <th>Stamina</th>
                            <th>Power</th>
                            <th>Comment</th>
                            <th style="width: 40px"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Goals Tab --}}
        <div class="tab-pane fade" id="goals{{ $id_suffix }}" role="tabpanel">
            <div class="d-flex justify-content-end mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="addGoalBtn{{ $id_suffix === '_inline' ? 'Inline' : '' }}">Add Goal</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm" id="goalsTable{{ $id_suffix === '_inline' ? 'Inline' : '' }}">
                    <thead>
                        <tr>
                            <th>Goal</th>
                            <th>Result</th>
                            <th style="width: 40px"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        {{-- Progress Chart Tab --}}
        <div class="tab-pane fade" id="progress-chart{{ $id_suffix }}" role="tabpanel">
            <div class="position-relative" style="height: 320px;">
                <canvas id="growthChart{{ $id_suffix === '_inline' ? 'Inline' : '' }}"></canvas>
                <div id="growthChartMessage{{ $id_suffix === '_inline' ? 'Inline' : '' }}" class="text-center text-muted mt-3"></div>
            </div>
        </div>
    </div>
</div>
