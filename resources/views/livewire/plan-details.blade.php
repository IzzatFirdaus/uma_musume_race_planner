
<div class="modal fade" id="planDetailsModal" tabindex="-1" aria-labelledby="planDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planDetailsModalLabel">
                    @if($plan_title)
                        Plan Details: {{ $plan_title }}
                    @else
                        Plan Details
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            @if($isLoading)
            <div class="loading-overlay" id="planDetailsLoadingOverlay" style="display: flex;">
                <div class="spinner-border text-uma" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            @endif
            
            <form id="planDetailsForm" method="POST" enctype="multipart/form-data" wire:submit.prevent="save">
                @method('PUT')
                @csrf
                <input type="hidden" wire:model="planId" id="planId" name="planId">
                <div class="modal-body">
                    {{-- Form Tabs Content (simplified version for modal) --}}
                    <div>
                        <ul class="nav nav-tabs" id="planTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="attributes-tab" data-bs-toggle="tab" data-bs-target="#attributes" type="button" role="tab">Attributes</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades" type="button" role="tab">Aptitude Grades</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills" type="button" role="tab">Skills</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="predictions-tab" data-bs-toggle="tab" data-bs-target="#predictions" type="button" role="tab">Race Predictions</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="goals-tab" data-bs-toggle="tab" data-bs-target="#goals" type="button" role="tab">Goals</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="progress-chart-tab" data-bs-toggle="tab" data-bs-target="#progress-chart" type="button" role="tab">Progress Chart</button>
                            </li>
                        </ul>

                        <div class="tab-content pt-3">
                            {{-- General Tab --}}
                            <div class="tab-pane fade show active" id="general" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <label for="plan_title" class="form-label">Plan Title</label>
                                        <input type="text" class="form-control" id="plan_title" name="plan_title" wire:model="plan_title">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalTurnBefore" class="form-label">Turn Before</label>
                                        <input type="number" class="form-control" id="modalTurnBefore" name="modalTurnBefore" wire:model="turn_before">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="modalName" class="form-label">Trainee Name</label>
                                        <input type="text" class="form-control" id="modalName" name="modalName" wire:model="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modalRaceName" class="form-label">Next Race Name</label>
                                        <input type="text" class="form-control" id="modalRaceName" name="modalRaceName" wire:model="race_name">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <label for="modalCareerStage" class="form-label">Career Stage</label>
                                                <select class="form-select" id="modalCareerStage" name="modalCareerStage" wire:model="career_stage">
                                                    <option value="" disabled>Select Stage</option>
                                                    <option value="predebut">Pre-debut</option>
                                                    <option value="junior">Junior</option>
                                                    <option value="classic">Classic</option>
                                                    <option value="senior">Senior</option>
                                                    <option value="finale">Finale</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="modalClass" class="form-label">Class</label>
                                                <select class="form-select" id="modalClass" name="modalClass" wire:model="class">
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
                                        <label for="modalGoal" class="form-label">Primary Goal</label>
                                        <input type="text" class="form-control" id="modalGoal" name="modalGoal" wire:model="goal">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="modalStrategy" class="form-label">Strategy</label>
                                        <select class="form-select" id="modalStrategy" name="modalStrategy" wire:model="strategy_id">
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
                                        <label for="modalMood" class="form-label">Mood</label>
                                        <select class="form-select" id="modalMood" name="modalMood" wire:model="mood_id">
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
                                        <label for="modalCondition" class="form-label">Condition</label>
                                        <select class="form-select" id="modalCondition" name="modalCondition" wire:model="condition_id">
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
                                                   id="energyRange" name="energyRange" wire:model.live="energy">
                                            <span class="badge bg-secondary" id="energyValue">{{ $energy }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input" type="checkbox" id="raceDaySwitch" 
                                                   name="raceDaySwitch" wire:model="race_day">
                                            <label class="form-check-label" for="raceDaySwitch">Race Day</label>
                                        </div>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="acquireSkillSwitch" 
                                                   name="acquireSkillSwitch" wire:model="acquire_skill">
                                            <label class="form-check-label" for="acquireSkillSwitch">Acquire Skill</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label for="skillPoints" class="form-label">Skill Points</label>
                                        <input type="number" class="form-control" id="skillPoints" 
                                               name="skillPoints" wire:model="total_available_skill_points">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="modalStatus" class="form-label">Status</label>
                                        <select class="form-select" id="modalStatus" name="modalStatus" wire:model="status">
                                            <option value="Planning">Planning</option>
                                            <option value="Active">Active</option>
                                            <option value="Finished">Finished</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="modalTimeOfDay" class="form-label">Time of Day</label>
                                        <input type="text" class="form-control" id="modalTimeOfDay" 
                                               name="modalTimeOfDay" wire:model="time_of_day" placeholder="e.g. Morning, Noon, Evening">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="modalMonth" class="form-label">Month</label>
                                        <input type="text" class="form-control" id="modalMonth" 
                                               name="modalMonth" wire:model="month" placeholder="e.g. January">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="modalSource" class="form-label">Source</label>
                                        <input type="text" class="form-control" id="modalSource" 
                                               name="modalSource" wire:model="source">
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row g-2">
                                            <div class="col-12"><label class="form-label">Growth Rates</label></div>
                                            <div class="col-6">
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Speed</span>
                                                    <input type="number" class="form-control" id="growthRateSpeed" 
                                                           name="growthRateSpeed" wire:model="growth_rate_speed">
                                                </div>
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Power</span>
                                                    <input type="number" class="form-control" id="growthRatePower" 
                                                           name="growthRatePower" wire:model="growth_rate_power">
                                                </div>
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Wit</span>
                                                    <input type="number" class="form-control" id="growthRateWit" 
                                                           name="growthRateWit" wire:model="growth_rate_wit">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Stamina</span>
                                                    <input type="number" class="form-control" id="growthRateStamina" 
                                                           name="growthRateStamina" wire:model="growth_rate_stamina">
                                                </div>
                                                <div class="input-group input-group-sm mb-2">
                                                    <span class="input-group-text">Guts</span>
                                                    <input type="number" class="form-control" id="growthRateGuts" 
                                                           name="growthRateGuts" wire:model="growth_rate_guts">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Attributes Tab --}}
                            <div class="tab-pane fade" id="attributes" role="tabpanel">
                                <div id="attributesContainer" class="row g-3">
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

                            {{-- Placeholder for other tabs --}}
                            <div class="tab-pane fade" id="grades" role="tabpanel">
                                <div class="text-center text-muted p-4">Aptitude Grades tab - Functionality to be implemented</div>
                            </div>

                            <div class="tab-pane fade" id="skills" role="tabpanel">
                                <div class="text-center text-muted p-4">Skills tab - Functionality to be implemented</div>
                            </div>

                            <div class="tab-pane fade" id="predictions" role="tabpanel">
                                <div class="text-center text-muted p-4">Race Predictions tab - Functionality to be implemented</div>
                            </div>

                            <div class="tab-pane fade" id="goals" role="tabpanel">
                                <div class="text-center text-muted p-4">Goals tab - Functionality to be implemented</div>
                            </div>

                            <div class="tab-pane fade" id="progress-chart" role="tabpanel">
                                <div class="position-relative" style="height: 320px;">
                                    <canvas id="growthChart"></canvas>
                                    <div id="growthChartMessage" class="text-center text-muted mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="exportPlanBtn">Copy to Clipboard</button>
                    <a href="#" id="downloadTxtLink" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-text"></i> Export as TXT</a>
                    <button type="submit" class="btn btn-uma">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
