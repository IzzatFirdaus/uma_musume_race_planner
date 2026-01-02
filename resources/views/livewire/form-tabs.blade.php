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

    <div class="tab-content pt-3" wire:ignore.self>
        {{-- General Tab --}}
        <div class="tab-pane fade show active" id="general{{ $id_suffix }}" role="tabpanel">
            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="plan_title{{ $id_suffix }}" class="form-label">Plan Title</label>
                    <input type="text" class="form-control" id="plan_title{{ $id_suffix }}" name="plan_title" wire:model.lazy="plan_title" {{ $isEditMode ? '' : 'readonly' }}>
                    <input type="hidden" id="planId{{ $id_suffix }}" name="planId" wire:model.defer="planId" aria-hidden="true" aria-label="plan id">
                </div>
                <div class="col-md-4">
                    <label for="modalTurnBefore{{ $id_suffix }}" class="form-label">Turn Before</label>
                    <input type="number" class="form-control" id="modalTurnBefore{{ $id_suffix }}" name="modalTurnBefore" wire:model.lazy="turn_before" {{ $isEditMode ? '' : 'readonly' }}>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalName{{ $id_suffix }}" class="form-label">Trainee Name</label>
                    <input type="text" class="form-control" id="modalName{{ $id_suffix }}" name="modalName" wire:model.lazy="name" required {{ $isEditMode ? '' : 'readonly' }}>
                </div>
                <div class="col-md-6">
                    <label for="modalRaceName{{ $id_suffix }}" class="form-label">Next Race Name</label>
                    <input type="text" class="form-control" id="modalRaceName{{ $id_suffix }}" name="modalRaceName" wire:model.lazy="race_name" {{ $isEditMode ? '' : 'readonly' }}>
                </div>
            </div>

            <div class="row mb-3">
                <livewire:trainee-image-handler />
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="modalCareerStage{{ $id_suffix }}" class="form-label">Career Stage</label>
                            <select class="form-select" id="modalCareerStage{{ $id_suffix }}" name="modalCareerStage" wire:model.defer="career_stage" {{ $isEditMode ? '' : 'disabled' }}>
                                <option value="" selected disabled>Select Stage</option>
                                @foreach ($careerStageOptions ?? [] as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="modalClass{{ $id_suffix }}" class="form-label">Class</label>
                            <select class="form-select" id="modalClass{{ $id_suffix }}" name="modalClass" wire:model.defer="class" {{ $isEditMode ? '' : 'disabled' }}>
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
                    <input type="text" class="form-control" id="modalGoal{{ $id_suffix }}" name="modalGoal" wire:model.lazy="goal">
                </div>
                <div class="col-md-4">
                    <label for="modalStrategy{{ $id_suffix }}" class="form-label">Strategy</label>
                    <select class="form-select" id="modalStrategy{{ $id_suffix }}" name="modalStrategy" wire:model.defer="strategy_id" {{ $isEditMode ? '' : 'disabled' }}>
                        <option value="">Select Strategy</option>
                        @foreach (($strategyOptions ?? []) as $opt)
                            <option value="{{ $opt['id'] ?? $opt['value'] ?? '' }}">{{ $opt['label'] ?? $opt['text'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="modalMood{{ $id_suffix }}" class="form-label">Mood</label>
                    <select class="form-select" id="modalMood{{ $id_suffix }}" name="modalMood" wire:model.defer="mood_id" {{ $isEditMode ? '' : 'disabled' }}>
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
                    <select class="form-select" id="modalCondition{{ $id_suffix }}" name="modalCondition" wire:model.defer="condition_id" {{ $isEditMode ? '' : 'disabled' }}>
                        <option value="">Select Condition</option>
                        @foreach (($conditionOptions ?? []) as $opt)
                            <option value="{{ $opt['id'] ?? $opt['value'] ?? '' }}">{{ $opt['label'] ?? $opt['text'] ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="energyRange{{ $id_suffix }}">Energy</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" min="0" max="100" step="1" class="form-range" id="energyRange{{ $id_suffix }}" name="energyRange" wire:model.live="energy" {{ $isEditMode ? '' : 'disabled' }}>
                        <span class="badge bg-secondary" id="energyValue{{ $id_suffix }}">{{ (int) ($energy ?? 0) }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="raceDaySwitch{{ $id_suffix }}" name="raceDaySwitch" wire:model.live="race_day" {{ $isEditMode ? '' : 'disabled' }}>
                        <label class="form-check-label" for="raceDaySwitch{{ $id_suffix }}">Race Day</label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="acquireSkillSwitch{{ $id_suffix }}" name="acquireSkillSwitch" wire:model.live="acquire_skill" {{ $isEditMode ? '' : 'disabled' }}>
                        <label class="form-check-label" for="acquireSkillSwitch{{ $id_suffix }}">Acquire Skill</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="skillPoints{{ $id_suffix }}" class="form-label">Skill Points</label>
                    <input type="number" class="form-control" id="skillPoints{{ $id_suffix }}" name="skillPoints" wire:model.lazy="total_available_skill_points" {{ $isEditMode ? '' : 'readonly' }}>
                </div>
                <div class="col-md-3">
                    <label for="modalStatus{{ $id_suffix }}" class="form-label">Status</label>
                    <select class="form-select" id="modalStatus{{ $id_suffix }}" name="modalStatus" wire:model.defer="status" {{ $isEditMode ? '' : 'disabled' }}>
                        <option value="Planning">Planning</option>
                        <option value="Active">Active</option>
                        <option value="Finished">Finished</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="modalTimeOfDay{{ $id_suffix }}" class="form-label">Time of Day</label>
                    <input type="text" class="form-control" id="modalTimeOfDay{{ $id_suffix }}" name="modalTimeOfDay" placeholder="e.g. Morning, Noon, Evening" wire:model.lazy="time_of_day" {{ $isEditMode ? '' : 'readonly' }}>
                </div>
                <div class="col-md-3">
                    <label for="modalMonth{{ $id_suffix }}" class="form-label">Month</label>
                    <input type="text" class="form-control" id="modalMonth{{ $id_suffix }}" name="modalMonth" placeholder="e.g. January" wire:model.lazy="month" {{ $isEditMode ? '' : 'readonly' }}>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalSource{{ $id_suffix }}" class="form-label">Source</label>
                    <input type="text" class="form-control" id="modalSource{{ $id_suffix }}" name="modalSource" wire:model.lazy="source" {{ $isEditMode ? '' : 'readonly' }}>
                </div>
                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-12"><label class="form-label">Growth Rates</label></div>
                        <div class="col-6">
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Speed</span>
                                <input type="number" class="form-control" id="growthRateSpeed{{ $id_suffix }}" name="growthRateSpeed" wire:model.lazy="growth_rate_speed" aria-label="Speed growth rate">
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Power</span>
                                <input type="number" class="form-control" id="growthRatePower{{ $id_suffix }}" name="growthRatePower" wire:model.lazy="growth_rate_power" aria-label="Power growth rate">
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Wit</span>
                                <input type="number" class="form-control" id="growthRateWit{{ $id_suffix }}" name="growthRateWit" wire:model.lazy="growth_rate_wit" aria-label="Wit growth rate">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Stamina</span>
                                <input type="number" class="form-control" id="growthRateStamina{{ $id_suffix }}" name="growthRateStamina" wire:model.lazy="growth_rate_stamina" aria-label="Stamina growth rate">
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">Guts</span>
                                <input type="number" class="form-control" id="growthRateGuts{{ $id_suffix }}" name="growthRateGuts" wire:model.lazy="growth_rate_guts" aria-label="Guts growth rate">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attributes Tab --}}
        <div class="tab-pane fade" id="attributes{{ $id_suffix }}" role="tabpanel">
                <div id="attributesContainer{{ $id_suffix }}" class="row g-3">
                    @if(count($planAttributes ?? []) > 0)
                        @foreach($planAttributes as $i => $attr)
                            <div class="col-md-6 col-lg-4" wire:key="attribute-{{ $i }}">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $attr['attribute_name'] ?? 'Attribute' }}</h6>
                                        <div class="mb-2">
                                            <label class="form-label">Value</label>
                                            <input type="number" class="form-control" wire:model.lazy="planAttributes.{{ $i }}.value">
                                        </div>
                                        <div>
                                            <label class="form-label">Grade</label>
                                            <select class="form-select" wire:model.lazy="planAttributes.{{ $i }}.grade">
                                                <option value="G">G</option>
                                                <option value="F">F</option>
                                                <option value="E">E</option>
                                                <option value="D">D</option>
                                                <option value="C">C</option>
                                                <option value="B">B</option>
                                                <option value="A">A</option>
                                                <option value="S">S</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center text-muted">No attributes available</div>
                    @endif
                </div>
        </div>

        {{-- Aptitude Grades Tab --}}
        <div class="tab-pane fade" id="grades{{ $id_suffix }}" role="tabpanel">
                <div id="gradesContainer{{ $id_suffix }}" class="row g-3">
                    <div class="col-md-4">
                        <h6>Terrain Grades</h6>
                        @if(count($terrainGrades ?? []) > 0)
                            @foreach($terrainGrades as $i => $t)
                                <div class="d-flex align-items-center gap-2 mb-2" wire:key="terrain-{{ $i }}">
                                    <div class="flex-grow-1">{{ $t['terrain'] ?? '' }}</div>
                                    <div style="width: 110px">
                                        <select class="form-select form-select-sm" wire:model.lazy="terrainGrades.{{ $i }}.grade">
                                            <option value="">-</option>
                                            <option value="G">G</option>
                                            <option value="F">F</option>
                                            <option value="E">E</option>
                                            <option value="D">D</option>
                                            <option value="C">C</option>
                                            <option value="B">B</option>
                                            <option value="A">A</option>
                                            <option value="S">S</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted">No terrain grades configured</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6>Distance Grades</h6>
                        @if(count($distanceGrades ?? []) > 0)
                            @foreach($distanceGrades as $i => $d)
                                <div class="d-flex align-items-center gap-2 mb-2" wire:key="distance-{{ $i }}">
                                    <div class="flex-grow-1">{{ $d['distance'] ?? '' }}</div>
                                    <div style="width: 110px">
                                        <select class="form-select form-select-sm" wire:model.lazy="distanceGrades.{{ $i }}.grade">
                                            <option value="">-</option>
                                            <option value="G">G</option>
                                            <option value="F">F</option>
                                            <option value="E">E</option>
                                            <option value="D">D</option>
                                            <option value="C">C</option>
                                            <option value="B">B</option>
                                            <option value="A">A</option>
                                            <option value="S">S</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted">No distance grades configured</div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6>Style Grades</h6>
                        @if(count($styleGrades ?? []) > 0)
                            @foreach($styleGrades as $i => $s)
                                <div class="d-flex align-items-center gap-2 mb-2" wire:key="style-{{ $i }}">
                                    <div class="flex-grow-1">{{ $s['style'] ?? '' }}</div>
                                    <div style="width: 110px">
                                        <select class="form-select form-select-sm" wire:model.lazy="styleGrades.{{ $i }}.grade">
                                            <option value="">-</option>
                                            <option value="G">G</option>
                                            <option value="F">F</option>
                                            <option value="E">E</option>
                                            <option value="D">D</option>
                                            <option value="C">C</option>
                                            <option value="B">B</option>
                                            <option value="A">A</option>
                                            <option value="S">S</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted">No style grades configured</div>
                        @endif
                    </div>
                </div>
        </div>

        {{-- Skills Tab --}}
        <div class="tab-pane fade" id="skills{{ $id_suffix }}" role="tabpanel">
            <div class="d-flex justify-content-end mb-2">
                @if($isEditMode)
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addSkill">Add Skill</button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Skill Name</th>
                            <th>SP Cost</th>
                            <th>Acquired</th>
                            <th>Tag</th>
                            <th>Notes</th>
                            <th style="width: 40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($skills ?? []) as $i => $row)
                        <tr wire:key="skill-{{ $i }}">
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Skill name" wire:model.lazy="skills.{{ $i }}.name" {{ $isEditMode ? '' : 'readonly' }}>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" placeholder="SP Cost" wire:model.lazy="skills.{{ $i }}.sp_cost" {{ $isEditMode ? '' : 'readonly' }}>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" wire:model.live="skills.{{ $i }}.acquired" {{ $isEditMode ? '' : 'disabled' }}>
                            </td>
                            <td>
                                <select class="form-select form-select-sm" wire:model.lazy="skills.{{ $i }}.tag" {{ $isEditMode ? '' : 'disabled' }}>
                                    <option value="">Select Tag</option>
                                    <option value="accel">Accel</option>
                                    <option value="speed">Speed</option>
                                    <option value="recovery">Recovery</option>
                                    <option value="passive">Passive</option>
                                    <option value="debuff">Debuff</option>
                                    <option value="unique">Unique</option>
                                    <option value="buff">Buff</option>
                                    <option value="stamina">Stamina</option>
                                    <option value="position">Position</option>
                                    <option value="tactic">Tactic</option>
                                    <option value="condition">Condition</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" placeholder="Notes" wire:model.lazy="skills.{{ $i }}.notes" {{ $isEditMode ? '' : 'readonly' }}>
                            </td>
                            <td>
                                @if($isEditMode)
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" wire:click="removeSkill({{ $i }})">
                                        <i class="bi bi-x"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center">No skills yet. Click "Add Skill" to begin.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Race Predictions Tab --}}
        <div class="tab-pane fade" id="predictions{{ $id_suffix }}" role="tabpanel">
            <div class="d-flex justify-content-end mb-2">
                @if($isEditMode)
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addPrediction">Add Prediction</button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Race</th>
                            <th>Venue</th>
                            <th>Ground</th>
                            <th>Distance</th>
                            <th>Track</th>
                            <th>Direction</th>
                            <th>Speed</th>
                            <th>Stamina</th>
                            <th>Power</th>
                            <th>Guts</th>
                            <th>Wit</th>
                            <th>Comment</th>
                            <th style="width: 40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($predictions ?? []) as $i => $p)
                        <tr wire:key="prediction-{{ $i }}">
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.race_name" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.venue" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.ground" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.distance" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.track" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.direction" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="number" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.speed" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="number" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.stamina" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="number" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.power" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="number" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.guts" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="number" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.wit" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="predictions.{{ $i }}.comment" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td>
                                @if($isEditMode)
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removePrediction({{ $i }})" title="Remove">
                                        <i class="bi bi-x"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-muted text-center">No predictions yet. Click "Add Prediction" to begin.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Goals Tab --}}
        <div class="tab-pane fade" id="goals{{ $id_suffix }}" role="tabpanel">
            <div class="d-flex justify-content-end mb-2">
                @if($isEditMode)
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addGoalRow">Add Goal</button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Goal</th>
                            <th>Result</th>
                            <th style="width: 40px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($goals ?? []) as $i => $g)
                        <tr wire:key="goal-{{ $i }}">
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="goals.{{ $i }}.goal" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td><input type="text" class="form-control form-control-sm" wire:model.lazy="goals.{{ $i }}.result" {{ $isEditMode ? '' : 'readonly' }}></td>
                            <td>
                                @if($isEditMode)
                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeGoalRow({{ $i }})" title="Remove">
                                        <i class="bi bi-x"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-muted text-center">No goals yet. Click "Add Goal" to begin.</td>
                        </tr>
                        @endforelse
                    </tbody>
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
