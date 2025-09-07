@if(!$isEditMode)
    {{-- View mode disables form fields via readonly/disabled attributes only. Styling handled by style.css. --}}
@endif

<div class="container py-4 {{ !$isEditMode ? 'view-mode' : '' }}">
    {{-- Navigation Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            {{-- Removed breadcrumb dashboard link, keeping only the Back to Dashboard button --}}
        </div>
        <div class="d-flex gap-2">
            @if(!$isEditMode)
                <a href="{{ route('plans.edit', ['planId' => $planId]) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit Plan
                </a>
            @else
                <a href="{{ route('plans.view', ['planId' => $planId]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-eye"></i> View Only
                </a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card mb-4 plan-list-theme">
    <div class="card-header plan-list-header-theme d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-{{ $isEditMode ? 'pencil-square' : 'eye' }} me-2"></i>
                @if($plan_title)
                    {{ $isEditMode ? 'Edit' : 'View' }} Plan: {{ $plan_title }}
                @else
                    {{ $isEditMode ? 'Edit' : 'View' }} Plan Details
                @endif
            </h5>
            @if($isEditMode)
                <span class="badge bg-warning">Edit Mode</span>
            @else
                <span class="badge bg-info">View Mode</span>
            @endif
        </div>

        @if($isLoading)
        <div class="loading-overlay">
            <div class="spinner-border dashboard-btn-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        @endif

        @if($isEditMode)
            <form id="planDetailsFormPage" enctype="multipart/form-data" wire:submit.prevent="save">
                @method('PUT')
                @csrf
                <input type="hidden" wire:model="planId" name="planId">
        @endif

            <div class="card-body">
                {{-- Form Tabs --}}
                <ul class="nav nav-tabs" id="planTabsPage" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab-page" data-bs-toggle="tab" data-bs-target="#general-page" type="button" role="tab">General</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attributes-tab-page" data-bs-toggle="tab" data-bs-target="#attributes-page" type="button" role="tab">Attributes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="grades-tab-page" data-bs-toggle="tab" data-bs-target="#grades-page" type="button" role="tab">Aptitude Grades</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="skills-tab-page" data-bs-toggle="tab" data-bs-target="#skills-page" type="button" role="tab">Skills</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="predictions-tab-page" data-bs-toggle="tab" data-bs-target="#predictions-page" type="button" role="tab">Race Predictions</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="goals-tab-page" data-bs-toggle="tab" data-bs-target="#goals-page" type="button" role="tab">Goals</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="progress-chart-tab-page" data-bs-toggle="tab" data-bs-target="#progress-chart-page" type="button" role="tab">Progress Chart</button>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    {{-- General Tab --}}
                    <div class="tab-pane fade show active" id="general-page" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="plan_title_page" class="form-label">Plan Title</label>
                                <input type="text" class="form-control" id="plan_title_page" name="plan_title" wire:model="plan_title" {{ !$isEditMode ? 'readonly' : '' }}>
                            </div>
                            <div class="col-md-4">
                                <label for="turn_before_page" class="form-label">Turn Before</label>
                                <input type="number" class="form-control" id="turn_before_page" name="turn_before" wire:model="turn_before" {{ !$isEditMode ? 'readonly' : '' }}>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name_page" class="form-label">Trainee Name</label>
                                <input type="text" class="form-control" id="name_page" name="name" wire:model="name" required {{ !$isEditMode ? 'readonly' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label for="race_name_page" class="form-label">Next Race Name</label>
                                <input type="text" class="form-control" id="race_name_page" name="race_name" wire:model="race_name" {{ !$isEditMode ? 'readonly' : '' }}>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label for="career_stage_page" class="form-label">Career Stage</label>
                                        <select class="form-select" id="career_stage_page" name="career_stage" wire:model="career_stage" {{ !$isEditMode ? 'disabled' : '' }}>
                                            <option value="" disabled>Select Stage</option>
                                            <option value="predebut">Pre-debut</option>
                                            <option value="junior">Junior</option>
                                            <option value="classic">Classic</option>
                                            <option value="senior">Senior</option>
                                            <option value="finale">Finale</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="class_page" class="form-label">Class</label>
                                        <select class="form-select" id="class_page" name="class" wire:model="class" {{ !$isEditMode ? 'disabled' : '' }}>
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
                                <label for="goal_page" class="form-label">Primary Goal</label>
                                <input type="text" class="form-control" id="goal_page" name="goal" wire:model="goal">
                            </div>
                            <div class="col-md-4">
                                <label for="strategy_id_page" class="form-label">Strategy</label>
                                <select class="form-select" id="strategy_id_page" name="strategy_id" wire:model="strategy_id">
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
                                <label for="mood_id_page" class="form-label">Mood</label>
                                <select class="form-select" id="mood_id_page" name="mood_id" wire:model="mood_id">
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
                                <label for="condition_id_page" class="form-label">Condition</label>
                                <select class="form-select" id="condition_id_page" name="condition_id" wire:model="condition_id">
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
                                           id="energy_page" name="energy" wire:model.live="energy">
                                    <span class="badge bg-secondary" id="energyValue_page">{{ $energy }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="race_day_page"
                                           name="race_day" wire:model="race_day">
                                    <label class="form-check-label" for="race_day_page">Race Day</label>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="acquire_skill_page"
                                           name="acquire_skill" wire:model="acquire_skill">
                                    <label class="form-check-label" for="acquire_skill_page">Acquire Skill</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="total_available_skill_points_page" class="form-label">Skill Points</label>
                                <input type="number" class="form-control" id="total_available_skill_points_page"
                                       name="total_available_skill_points" wire:model="total_available_skill_points">
                            </div>
                            <div class="col-md-3">
                                <label for="status_page" class="form-label">Status</label>
                                <select class="form-select" id="status_page" name="status" wire:model="status">
                                    <option value="Planning">Planning</option>
                                    <option value="Active">Active</option>
                                    <option value="Finished">Finished</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="time_of_day_page" class="form-label">Time of Day</label>
                                <input type="text" class="form-control" id="time_of_day_page"
                                       name="time_of_day" wire:model="time_of_day" placeholder="e.g. Morning, Noon, Evening">
                            </div>
                            <div class="col-md-3">
                                <label for="month_page" class="form-label">Month</label>
                                <input type="text" class="form-control" id="month_page"
                                       name="month" wire:model="month" placeholder="e.g. January">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="source_page" class="form-label">Source</label>
                                <input type="text" class="form-control" id="source_page"
                                       name="source" wire:model="source">
                            </div>
                            <div class="col-md-6">
                                <div class="row g-2">
                                    <div class="col-12"><label class="form-label">Growth Rates</label></div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text">Speed</span>
                                            <input type="number" class="form-control" id="growth_rate_speed_page"
                                                   name="growth_rate_speed" wire:model="growth_rate_speed">
                                        </div>
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text">Power</span>
                                            <input type="number" class="form-control" id="growth_rate_power_page"
                                                   name="growth_rate_power" wire:model="growth_rate_power">
                                        </div>
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text">Wit</span>
                                            <input type="number" class="form-control" id="growth_rate_wit_page"
                                                   name="growth_rate_wit" wire:model="growth_rate_wit">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text">Stamina</span>
                                            <input type="number" class="form-control" id="growth_rate_stamina_page"
                                                   name="growth_rate_stamina" wire:model="growth_rate_stamina">
                                        </div>
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text">Guts</span>
                                            <input type="number" class="form-control" id="growth_rate_guts_page"
                                                   name="growth_rate_guts" wire:model="growth_rate_guts">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Attributes Tab --}}
                    <div class="tab-pane fade" id="attributes-page" role="tabpanel">
                        <div id="attributesContainer_page" class="row g-3">
                            @if(count($planAttributes ?? []) > 0)
                                @foreach($planAttributes ?? [] as $index => $attribute)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ $attribute['attribute_name'] ?? 'Unknown' }}</h6>
                                                <div class="mb-2">
                                                    <label class="form-label">Value</label>
                                                    <input type="number" class="form-control"
                                                        wire:model="planAttributes.{{ $index }}.value"
                                                        value="{{ $attribute['value'] ?? 0 }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">Grade</label>
                                                    <select class="form-select"
                                                        wire:model="planAttributes.{{ $index }}.grade">
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
                    <div class="tab-pane fade" id="grades-page" role="tabpanel">
                        <div id="gradesContainer_page" class="row g-3">
                            <div class="col-12">
                                <p class="text-muted">Aptitude grades functionality will be implemented here</p>
                            </div>
                        </div>
                    </div>

                    {{-- Skills Tab --}}
                    <div class="tab-pane fade" id="skills-page" role="tabpanel">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addSkillBtnPage">Add Skill</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm" id="skillsTablePage">
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
                    <div class="tab-pane fade" id="predictions-page" role="tabpanel">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addPredictionBtnPage">Add Prediction</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm" id="predictionsTablePage">
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
                    <div class="tab-pane fade" id="goals-page" role="tabpanel">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addGoalBtnPage">Add Goal</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm" id="goalsTablePage">
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
                    <div class="tab-pane fade" id="progress-chart-page" role="tabpanel">
                        <div class="position-relative" style="height: 320px;">
                            <canvas id="growthChartPage"></canvas>
                            <div id="growthChartMessagePage" class="text-center text-muted mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2 rounded-bottom-4">
                @if(!$isEditMode)
                    <button type="button" class="btn-uma" id="downloadTxtPage">
                        <i class="bi bi-file-earmark-text"></i> Download Plan as TXT
                    </button>
                    <button type="button" class="dashboard-btn-primary" id="exportPlanBtnPage">
                        <i class="bi bi-clipboard"></i> Copy Plan to Clipboard
                    </button>
                @endif
                @if($isEditMode)
                    <button type="submit" class="dashboard-btn-primary">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                @endif
            </div>
        @if($isEditMode)
        </form>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('plan-saved', (event) => {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Saved!',
                        text: event[0]?.message || 'Plan has been saved successfully.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
            Livewire.on('show-error', (event) => {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Error!',
                        text: event[0]?.message || 'An error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>
    @endpush
</div>
