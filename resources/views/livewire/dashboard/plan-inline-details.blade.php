
<div>
    {{-- Be like water. --}}
    {{--
        Inline card view for editing the full details of a selected plan.
        No direct image/background usage here, but ensure any referenced partials
        (like form-tabs) also use `asset()` for uploaded images.
    --}}
    <div id="planInlineDetails" class="card mb-4" style="{{ $isVisible ? 'display: block;' : 'display: none;' }}">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" id="planInlineDetailsLabel">
                @if($plan_title)
                    Plan Details: {{ $plan_title }}
                @else
                    Plan Details
                @endif
            </h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="closePlan">
                <i class="bi bi-x"></i> Close
            </button>
        </div>
        
        @if($isLoading)
        <div class="loading-overlay" id="planInlineDetailsLoadingOverlay" style="display: flex;">
            <div class="spinner-border text-uma" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        @endif
        
        {{-- This form would submit to a Laravel route for updating the plan --}}
        <form id="planDetailsFormInline" enctype="multipart/form-data" wire:submit.prevent="save">
            @method('PUT')
            @csrf
            <input type="hidden" wire:model="planId" id="planIdInline" name="planId">
            
            <div class="card-body">
                {{-- Form Tabs --}}
                <div>
                    <ul class="nav nav-tabs" id="planTabsInline" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab-inline" data-bs-toggle="tab" data-bs-target="#general-inline" type="button" role="tab">General</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attributes-tab-inline" data-bs-toggle="tab" data-bs-target="#attributes-inline" type="button" role="tab">Attributes</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="grades-tab-inline" data-bs-toggle="tab" data-bs-target="#grades-inline" type="button" role="tab">Aptitude Grades</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="skills-tab-inline" data-bs-toggle="tab" data-bs-target="#skills-inline" type="button" role="tab">Skills</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="predictions-tab-inline" data-bs-toggle="tab" data-bs-target="#predictions-inline" type="button" role="tab">Race Predictions</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="goals-tab-inline" data-bs-toggle="tab" data-bs-target="#goals-inline" type="button" role="tab">Goals</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="progress-chart-tab-inline" data-bs-toggle="tab" data-bs-target="#progress-chart-inline" type="button" role="tab">Progress Chart</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3">
                        {{-- General Tab --}}
                        <div class="tab-pane fade show active" id="general-inline" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="plan_title_inline" class="form-label">Plan Title</label>
                                    <input type="text" class="form-control" id="plan_title_inline" name="plan_title" wire:model="plan_title">
                                </div>
                                <div class="col-md-4">
                                    <label for="modalTurnBefore_inline" class="form-label">Turn Before</label>
                                    <input type="number" class="form-control" id="modalTurnBefore_inline" name="modalTurnBefore" wire:model="turn_before">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="modalName_inline" class="form-label">Trainee Name</label>
                                    <input type="text" class="form-control" id="modalName_inline" name="modalName" wire:model="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="modalRaceName_inline" class="form-label">Next Race Name</label>
                                    <input type="text" class="form-control" id="modalRaceName_inline" name="modalRaceName" wire:model="race_name">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label for="modalCareerStage_inline" class="form-label">Career Stage</label>
                                            <select class="form-select" id="modalCareerStage_inline" name="modalCareerStage" wire:model="career_stage">
                                                <option value="" disabled>Select Stage</option>
                                                <option value="predebut">Pre-debut</option>
                                                <option value="junior">Junior</option>
                                                <option value="classic">Classic</option>
                                                <option value="senior">Senior</option>
                                                <option value="finale">Finale</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="modalClass_inline" class="form-label">Class</label>
                                            <select class="form-select" id="modalClass_inline" name="modalClass" wire:model="class">
                                                <option value="" disabled>Select Class</option>
                                                <option value="debut">Debut</option>
                                                <option value="maiden">Maiden</option>
                                                <option value="beginner">Beginner</option>
                                                <option value="bronze">Bronze</option>
                                                <option value="silver">Silver</option>
                                                <option value="gold">Gold</option>
                                                <option value="platinum">Platinum</option>
                                                <option value="star">Star</option>
                                                <option value="legend">Legend</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="modalGoal_inline" class="form-label">Primary Goal</label>
                                    <input type="text" class="form-control" id="modalGoal_inline" name="modalGoal" wire:model="goal">
                                </div>
                                <div class="col-md-4">
                                    <label for="modalStrategy_inline" class="form-label">Strategy</label>
                                    <select class="form-select" id="modalStrategy_inline" name="modalStrategy" wire:model="strategy_id">
                                        <option value="">Select Strategy</option>
                                        @php
                                            $strategies = \App\Models\Strategy::all();
                                        @endphp
                                        @foreach ($strategies as $strategy)
                                            <option value="{{ $strategy->id }}">{{ $strategy->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="modalMood_inline" class="form-label">Mood</label>
                                    <select class="form-select" id="modalMood_inline" name="modalMood" wire:model="mood_id">
                                        <option value="">Select Mood</option>
                                        @php
                                            $moods = \App\Models\Mood::all();
                                        @endphp
                                        @foreach ($moods as $mood)
                                            <option value="{{ $mood->id }}">{{ $mood->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="modalCondition_inline" class="form-label">Condition</label>
                                    <select class="form-select" id="modalCondition_inline" name="modalCondition" wire:model="condition_id">
                                        <option value="">Select Condition</option>
                                        @php
                                            $conditions = \App\Models\Condition::all();
                                        @endphp
                                        @foreach ($conditions as $condition)
                                            <option value="{{ $condition->id }}">{{ $condition->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Energy</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="range" min="0" max="100" step="1" class="form-range" 
                                               id="energyRange_inline" name="energyRange" wire:model.live="energy">
                                        <span class="badge bg-secondary" id="energyValue_inline">{{ $energy }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="raceDaySwitch_inline" 
                                               name="raceDaySwitch" wire:model="race_day">
                                        <label class="form-check-label" for="raceDaySwitch_inline">Race Day</label>
                                    </div>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="acquireSkillSwitch_inline" 
                                               name="acquireSkillSwitch" wire:model="acquire_skill">
                                        <label class="form-check-label" for="acquireSkillSwitch_inline">Acquire Skill</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="skillPoints_inline" class="form-label">Skill Points</label>
                                    <input type="number" class="form-control" id="skillPoints_inline" 
                                           name="skillPoints" wire:model="total_available_skill_points">
                                </div>
                                <div class="col-md-3">
                                    <label for="modalStatus_inline" class="form-label">Status</label>
                                    <select class="form-select" id="modalStatus_inline" name="modalStatus" wire:model="status">
                                        <option value="Planning">Planning</option>
                                        <option value="Active">Active</option>
                                        <option value="Finished">Finished</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="modalTimeOfDay_inline" class="form-label">Time of Day</label>
                                    <input type="text" class="form-control" id="modalTimeOfDay_inline" 
                                           name="modalTimeOfDay" wire:model="time_of_day" placeholder="e.g. Morning, Noon, Evening">
                                </div>
                                <div class="col-md-3">
                                    <label for="modalMonth_inline" class="form-label">Month</label>
                                    <input type="text" class="form-control" id="modalMonth_inline" 
                                           name="modalMonth" wire:model="month" placeholder="e.g. January">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="modalSource_inline" class="form-label">Source</label>
                                    <input type="text" class="form-control" id="modalSource_inline" 
                                           name="modalSource" wire:model="source">
                                </div>
                                <div class="col-md-6">
                                    <div class="row g-2">
                                        <div class="col-12"><label class="form-label">Growth Rates</label></div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm mb-2">
                                                <span class="input-group-text">Speed</span>
                                                <input type="number" class="form-control" id="growthRateSpeed_inline" 
                                                       name="growthRateSpeed" wire:model="growth_rate_speed">
                                            </div>
                                            <div class="input-group input-group-sm mb-2">
                                                <span class="input-group-text">Power</span>
                                                <input type="number" class="form-control" id="growthRatePower_inline" 
                                                       name="growthRatePower" wire:model="growth_rate_power">
                                            </div>
                                            <div class="input-group input-group-sm mb-2">
                                                <span class="input-group-text">Wit</span>
                                                <input type="number" class="form-control" id="growthRateWit_inline" 
                                                       name="growthRateWit" wire:model="growth_rate_wit">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm mb-2">
                                                <span class="input-group-text">Stamina</span>
                                                <input type="number" class="form-control" id="growthRateStamina_inline" 
                                                       name="growthRateStamina" wire:model="growth_rate_stamina">
                                            </div>
                                            <div class="input-group input-group-sm mb-2">
                                                <span class="input-group-text">Guts</span>
                                                <input type="number" class="form-control" id="growthRateGuts_inline" 
                                                       name="growthRateGuts" wire:model="growth_rate_guts">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Attributes Tab --}}
                        <div class="tab-pane fade" id="attributes-inline" role="tabpanel">
                            <div id="attributesContainer_inline" class="row g-3">
                                @if(count($attributes) > 0)
                                    @foreach($attributes as $index => $attribute)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title">{{ $attribute['attribute_name'] ?? 'Unknown' }}</h6>
                                                    <div class="mb-2">
                                                        <label class="form-label">Value</label>
                                                        <input type="number" class="form-control" 
                                                               wire:model="attributes.{{ $index }}.value" 
                                                               value="{{ $attribute['value'] ?? 0 }}">
                                                    </div>
                                                    <div>
                                                        <label class="form-label">Grade</label>
                                                        <select class="form-select" 
                                                                wire:model="attributes.{{ $index }}.grade">
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
                                    <div class="col-12 text-center text-muted">
                                        <p>No attributes data available</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Aptitude Grades Tab --}}
                        <div class="tab-pane fade" id="grades-inline" role="tabpanel">
                            <div id="gradesContainer_inline" class="row g-3">
                                <div class="col-12">
                                    <p class="text-muted">Aptitude grades functionality will be implemented here</p>
                                </div>
                            </div>
                        </div>

                        {{-- Skills Tab --}}
                        <div class="tab-pane fade" id="skills-inline" role="tabpanel">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addSkillBtnInline">Add Skill</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm" id="skillsTableInline">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%">Name</th>
                                            <th style="width: 15%">Tag</th>
                                            <th style="width: 10%">Acquired</th>
                                            <th>Notes</th>
                                            <th style="width: 40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($skills) > 0)
                                            @foreach($skills as $index => $skill)
                                                <tr>
                                                    <td>{{ $skill['skill_name'] ?? '' }}</td>
                                                    <td>{{ $skill['tag'] ?? '' }}</td>
                                                    <td>
                                                        <span class="badge {{ $skill['acquired'] === 'yes' ? 'bg-success' : 'bg-secondary' }}">
                                                            {{ $skill['acquired'] === 'yes' ? 'Yes' : 'No' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $skill['notes'] ?? '' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm p-0 px-1">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No skills added</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Race Predictions Tab --}}
                        <div class="tab-pane fade" id="predictions-inline" role="tabpanel">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addPredictionBtnInline">Add Prediction</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm" id="predictionsTableInline">
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
                                    <tbody>
                                        @if(count($racePredictions) > 0)
                                            @foreach($racePredictions as $prediction)
                                                <tr>
                                                    <td>{{ $prediction['race_name'] ?? '' }}</td>
                                                    <td>{{ $prediction['venue'] ?? '' }}</td>
                                                    <td>{{ $prediction['ground'] ?? '' }}</td>
                                                    <td>{{ $prediction['distance'] ?? '' }}</td>
                                                    <td>{{ $prediction['speed'] ?? '' }}</td>
                                                    <td>{{ $prediction['stamina'] ?? '' }}</td>
                                                    <td>{{ $prediction['power'] ?? '' }}</td>
                                                    <td>{{ $prediction['comment'] ?? '' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm p-0 px-1">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">No predictions added</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Goals Tab --}}
                        <div class="tab-pane fade" id="goals-inline" role="tabpanel">
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addGoalBtnInline">Add Goal</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm" id="goalsTableInline">
                                    <thead>
                                        <tr>
                                            <th>Goal</th>
                                            <th>Result</th>
                                            <th style="width: 40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($goals) > 0)
                                            @foreach($goals as $goal)
                                                <tr>
                                                    <td>{{ $goal['goal'] ?? '' }}</td>
                                                    <td>{{ $goal['result'] ?? '' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm p-0 px-1">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No goals added</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Progress Chart Tab --}}
                        <div class="tab-pane fade" id="progress-chart-inline" role="tabpanel">
                            <div class="position-relative" style="height: 320px;">
                                <canvas id="growthChartInline"></canvas>
                                <div id="growthChartMessageInline" class="text-center text-muted mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button type="button" class="btn btn-outline-secondary me-2" id="downloadTxtInline">
                    <i class="bi bi-file-earmark-text"></i> Export as TXT
                </button>
                <button type="button" class="btn btn-info me-2" id="exportPlanBtnInline">Copy to Clipboard</button>
                <button type="submit" class="btn btn-uma">Save Changes</button>
            </div>
        </form>
    </div>
</div>
