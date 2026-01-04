{{--
    FormTabs Component - Tabbed form interface for plan editing
    Requirements: 4.2, 5.2, 29.1 - Tabbed interface with keyboard navigation
    Requirements: 31.1, 31.2, 31.3, 32.1, 32.2 - Mood, condition, energy display
--}}

@props(['id_suffix' => ''])

<div x-data="{
    activeTab: 'general',
    tabs: ['general', 'attributes', 'grades', 'skills', 'predictions', 'goals', 'progress-chart'],
    focusTab(direction) {
        const currentIndex = this.tabs.indexOf(this.activeTab);
        let newIndex;
        if (direction === 'next') {
            newIndex = (currentIndex + 1) % this.tabs.length;
        } else {
            newIndex = (currentIndex - 1 + this.tabs.length) % this.tabs.length;
        }
        this.activeTab = this.tabs[newIndex];
        this.$nextTick(() => {
            const tabButton = document.getElementById(this.tabs[newIndex] + '-tab{{ $id_suffix }}');
            if (tabButton) tabButton.focus();
        });
    }
}" @keydown.arrow-right.prevent="focusTab('next')" @keydown.arrow-left.prevent="focusTab('prev')"
    data-testid="form-tabs-container">
    {{-- Tab Navigation (Req 29.1 - Keyboard navigation, Req 14.3 - Horizontal scrolling on mobile) --}}
    <div class="tabs-scroll-container -mx-4 px-4 sm:mx-0 sm:px-0">
        <ul class="nav nav-tabs tabs-scroll-list" id="planTabs{{ $id_suffix }}" role="tablist"
            data-testid="form-tabs-nav">
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ 'active': activeTab === 'general' }"
                    id="general-tab{{ $id_suffix }}" type="button" role="tab"
                    aria-controls="general{{ $id_suffix }}" :aria-selected="activeTab === 'general'"
                    @click="activeTab = 'general'" data-testid="tab-general">General</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ 'active': activeTab === 'attributes' }"
                    id="attributes-tab{{ $id_suffix }}" type="button" role="tab"
                    aria-controls="attributes{{ $id_suffix }}" :aria-selected="activeTab === 'attributes'"
                    @click="activeTab = 'attributes'" data-testid="tab-attributes">Attributes</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ 'active': activeTab === 'grades' }"
                    id="grades-tab{{ $id_suffix }}" type="button" role="tab"
                    aria-controls="grades{{ $id_suffix }}" :aria-selected="activeTab === 'grades'"
                    @click="activeTab = 'grades'" data-testid="tab-aptitude-grades">Aptitude Grades</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ 'active': activeTab === 'skills' }"
                    id="skills-tab{{ $id_suffix }}" type="button" role="tab"
                    aria-controls="skills{{ $id_suffix }}" :aria-selected="activeTab === 'skills'"
                    @click="activeTab = 'skills'" data-testid="tab-skills">Skills</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ 'active': activeTab === 'predictions' }"
                    id="predictions-tab{{ $id_suffix }}" type="button" role="tab"
                    aria-controls="predictions{{ $id_suffix }}" :aria-selected="activeTab === 'predictions'"
                    @click="activeTab = 'predictions'" data-testid="tab-race-predictions">Race Predictions</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ 'active': activeTab === 'goals' }"
                    id="goals-tab{{ $id_suffix }}" type="button" role="tab"
                    aria-controls="goals{{ $id_suffix }}" :aria-selected="activeTab === 'goals'"
                    @click="activeTab = 'goals'" data-testid="tab-goals">Goals</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" :class="{ 'active': activeTab === 'progress-chart' }"
                    id="progress-chart-tab{{ $id_suffix }}" type="button" role="tab"
                    aria-controls="progress-chart{{ $id_suffix }}" :aria-selected="activeTab === 'progress-chart'"
                    @click="activeTab = 'progress-chart'" data-testid="tab-progress-chart">Progress Chart</button>
            </li>
        </ul>
    </div>

    <div class="tab-content pt-3" wire:ignore.self>
        {{-- General Tab --}}
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'general' }"
            id="general{{ $id_suffix }}" role="tabpanel" aria-labelledby="general-tab{{ $id_suffix }}"
            x-show="activeTab === 'general'" data-testid="tab-panel-general">
            {{-- Plan Title and Turn --}}
            <div class="row mb-3">
                <div class="col-md-8">
                    <label for="plan_title{{ $id_suffix }}" class="form-label">Plan Title</label>
                    <input type="text" class="form-control" id="plan_title{{ $id_suffix }}" name="plan_title"
                        wire:model.lazy="plan_title" {{ $isEditMode ? '' : 'readonly' }}
                        data-testid="input-plan-title">
                    <input type="hidden" id="planId{{ $id_suffix }}" name="planId" wire:model.defer="planId"
                        aria-hidden="true" aria-label="plan id">
                </div>
                <div class="col-md-4">
                    <label for="modalTurnBefore{{ $id_suffix }}" class="form-label">Turn Before</label>
                    <input type="number" class="form-control" id="modalTurnBefore{{ $id_suffix }}"
                        name="modalTurnBefore" wire:model.lazy="turn_before" {{ $isEditMode ? '' : 'readonly' }}
                        data-testid="input-turn-before">
                </div>
            </div>

            {{-- Character Selector and Race Name (Req 4.3) --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalCharacter{{ $id_suffix }}" class="form-label">Character</label>
                    <select class="form-select" id="modalCharacter{{ $id_suffix }}" name="modalCharacter"
                        wire:model.live="umamusume_id" {{ $isEditMode ? '' : 'disabled' }}
                        data-testid="select-character">
                        <option value="">Select Character</option>
                        @foreach ($characterOptions ?? [] as $opt)
                            <option value="{{ $opt['id'] ?? ($opt['value'] ?? '') }}">
                                {{ $opt['label'] ?? ($opt['text'] ?? '') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="modalRaceName{{ $id_suffix }}" class="form-label">Next Race Name</label>
                    <input type="text" class="form-control" id="modalRaceName{{ $id_suffix }}"
                        name="modalRaceName" wire:model.lazy="race_name" {{ $isEditMode ? '' : 'readonly' }}
                        data-testid="input-race-name">
                </div>
            </div>

            {{-- Trainee Name (legacy field) --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalName{{ $id_suffix }}" class="form-label">Trainee Name</label>
                    <input type="text" class="form-control" id="modalName{{ $id_suffix }}" name="modalName"
                        wire:model.lazy="name" required {{ $isEditMode ? '' : 'readonly' }}
                        data-testid="input-trainee-name">
                </div>
                <div class="col-md-6">
                    <livewire:trainee-image-handler :planId="$planId" :localUuid="$localUuid" :storageMode="$storageMode"
                        :existingImagePath="$traineeImagePath" wire:key="trainee-image-{{ $planId ?? ($localUuid ?? 'new') }}" />
                </div>
            </div>

            {{-- Career Stage and Class --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="modalCareerStage{{ $id_suffix }}" class="form-label">Career Stage</label>
                    <select class="form-select" id="modalCareerStage{{ $id_suffix }}" name="modalCareerStage"
                        wire:model.defer="career_stage" {{ $isEditMode ? '' : 'disabled' }}
                        data-testid="select-career-stage">
                        <option value="" selected disabled>Select Stage</option>
                        @foreach ($careerStageOptions ?? [] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="modalClass{{ $id_suffix }}" class="form-label">Class</label>
                    <select class="form-select" id="modalClass{{ $id_suffix }}" name="modalClass"
                        wire:model.defer="class" {{ $isEditMode ? '' : 'disabled' }} data-testid="select-class">
                        <option value="" selected disabled>Select Class</option>
                        @foreach ($classOptions ?? [] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="modalStatus{{ $id_suffix }}" class="form-label">Status</label>
                    <select class="form-select" id="modalStatus{{ $id_suffix }}" name="modalStatus"
                        wire:model.defer="status" {{ $isEditMode ? '' : 'disabled' }} data-testid="select-status">
                        <option value="Planning">Planning</option>
                        <option value="Active">Active</option>
                        <option value="Finished">Finished</option>
                    </select>
                </div>
            </div>

            {{-- Goal and Strategy --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalGoal{{ $id_suffix }}" class="form-label">Primary Goal</label>
                    <input type="text" class="form-control" id="modalGoal{{ $id_suffix }}" name="modalGoal"
                        wire:model.lazy="goal" {{ $isEditMode ? '' : 'readonly' }} data-testid="input-goal">
                </div>
                <div class="col-md-6">
                    <label for="modalStrategy{{ $id_suffix }}" class="form-label">Strategy</label>
                    <select class="form-select" id="modalStrategy{{ $id_suffix }}" name="modalStrategy"
                        wire:model.defer="strategy_id" {{ $isEditMode ? '' : 'disabled' }}
                        data-testid="select-strategy">
                        <option value="">Select Strategy</option>
                        @foreach ($strategyOptions ?? [] as $opt)
                            <option value="{{ $opt['id'] ?? ($opt['value'] ?? '') }}">
                                {{ $opt['label'] ?? ($opt['text'] ?? '') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Mood Selector with Percentage Display (Req 31.1, 31.2) --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="modalMood{{ $id_suffix }}" class="form-label">Mood</label>
                    <select class="form-select" id="modalMood{{ $id_suffix }}" name="modalMood"
                        wire:model.live="mood_id" {{ $isEditMode ? '' : 'disabled' }} data-testid="select-mood">
                        <option value="">Select Mood</option>
                        @foreach ($moodOptions ?? [] as $opt)
                            <option value="{{ $opt['id'] ?? ($opt['value'] ?? '') }}">
                                {{ $opt['label'] ?? ($opt['text'] ?? '') }} ({{ $opt['percentage'] ?? '0%' }})
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted" data-testid="mood-percentage-hint">
                        Mood affects training and racing performance
                    </small>
                </div>

                {{-- Condition Checkboxes with Positive/Negative Styling (Req 31.3, 31.4) --}}
                <div class="col-md-6">
                    <label class="form-label">Conditions</label>
                    <div class="d-flex flex-wrap gap-2" data-testid="condition-checkboxes">
                        @foreach ($conditionOptions ?? [] as $opt)
                            @php
                                $conditionType = $opt['type'] ?? 'neutral';
                                $badgeClass = match ($conditionType) {
                                    'positive' => 'bg-success text-white',
                                    'negative' => 'bg-danger text-white',
                                    default => 'bg-secondary text-white',
                                };
                            @endphp
                            <div class="form-check" data-testid="condition-checkbox-{{ $opt['id'] ?? '' }}">
                                <input class="form-check-input" type="checkbox"
                                    id="condition_{{ $opt['id'] ?? '' }}{{ $id_suffix }}"
                                    value="{{ $opt['id'] ?? '' }}" wire:model.live="selected_conditions"
                                    {{ $isEditMode ? '' : 'disabled' }}
                                    data-testid="checkbox-condition-{{ $opt['id'] ?? '' }}">
                                <label class="form-check-label badge {{ $badgeClass }}"
                                    for="condition_{{ $opt['id'] ?? '' }}{{ $id_suffix }}">
                                    {{ $opt['label'] ?? ($opt['text'] ?? '') }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Energy Level with Color-Coded Indicator (Req 32.1, 32.2, 32.3) --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="energyRange{{ $id_suffix }}">Energy Level</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" min="0" max="100" step="1" class="form-range"
                            id="energyRange{{ $id_suffix }}" name="energyRange" wire:model.live="energy"
                            {{ $isEditMode ? '' : 'disabled' }} data-testid="input-energy-range">
                        <span class="badge {{ $energyColorClass ?? 'bg-secondary' }}"
                            id="energyValue{{ $id_suffix }}" data-testid="energy-indicator">
                            {{ (int) ($energy ?? 0) }}%
                        </span>
                    </div>
                    @if ($energyWarning ?? false)
                        <div class="alert alert-danger mt-2 py-1 px-2" role="alert" data-testid="energy-warning">
                            <small><i class="bi bi-exclamation-triangle"></i> {{ $energyWarning }}</small>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="raceDaySwitch{{ $id_suffix }}"
                            name="raceDaySwitch" wire:model.live="race_day" {{ $isEditMode ? '' : 'disabled' }}
                            data-testid="switch-race-day">
                        <label class="form-check-label" for="raceDaySwitch{{ $id_suffix }}">Race Day</label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="acquireSkillSwitch{{ $id_suffix }}"
                            name="acquireSkillSwitch" wire:model.live="acquire_skill"
                            {{ $isEditMode ? '' : 'disabled' }} data-testid="switch-acquire-skill">
                        <label class="form-check-label" for="acquireSkillSwitch{{ $id_suffix }}">Acquire
                            Skill</label>
                    </div>
                </div>
            </div>

            {{-- Skill Points, Time of Day, Month --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="skillPoints{{ $id_suffix }}" class="form-label">Skill Points</label>
                    <input type="number" class="form-control" id="skillPoints{{ $id_suffix }}"
                        name="skillPoints" wire:model.lazy="total_available_skill_points"
                        {{ $isEditMode ? '' : 'readonly' }} data-testid="input-skill-points">
                </div>
                <div class="col-md-3">
                    <label for="modalTimeOfDay{{ $id_suffix }}" class="form-label">Time of Day</label>
                    <input type="text" class="form-control" id="modalTimeOfDay{{ $id_suffix }}"
                        name="modalTimeOfDay" placeholder="e.g. Morning, Noon, Evening" wire:model.lazy="time_of_day"
                        {{ $isEditMode ? '' : 'readonly' }} data-testid="input-time-of-day">
                </div>
                <div class="col-md-3">
                    <label for="modalMonth{{ $id_suffix }}" class="form-label">Month</label>
                    <input type="text" class="form-control" id="modalMonth{{ $id_suffix }}" name="modalMonth"
                        placeholder="e.g. January" wire:model.lazy="month" {{ $isEditMode ? '' : 'readonly' }}
                        data-testid="input-month">
                </div>
                <div class="col-md-3">
                    <label for="modalSource{{ $id_suffix }}" class="form-label">Source</label>
                    <input type="text" class="form-control" id="modalSource{{ $id_suffix }}"
                        name="modalSource" wire:model.lazy="source" {{ $isEditMode ? '' : 'readonly' }}
                        data-testid="input-source">
                </div>
            </div>

            {{-- Growth Rates --}}
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Growth Rates</label>
                    <div class="row g-2" data-testid="growth-rates-container">
                        <div class="col-md-4 col-lg-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Speed</span>
                                <input type="number" class="form-control" id="growthRateSpeed{{ $id_suffix }}"
                                    name="growthRateSpeed" wire:model.lazy="growth_rate_speed"
                                    aria-label="Speed growth rate" {{ $isEditMode ? '' : 'readonly' }}
                                    data-testid="input-growth-rate-speed">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Stamina</span>
                                <input type="number" class="form-control"
                                    id="growthRateStamina{{ $id_suffix }}" name="growthRateStamina"
                                    wire:model.lazy="growth_rate_stamina" aria-label="Stamina growth rate"
                                    {{ $isEditMode ? '' : 'readonly' }} data-testid="input-growth-rate-stamina">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Power</span>
                                <input type="number" class="form-control" id="growthRatePower{{ $id_suffix }}"
                                    name="growthRatePower" wire:model.lazy="growth_rate_power"
                                    aria-label="Power growth rate" {{ $isEditMode ? '' : 'readonly' }}
                                    data-testid="input-growth-rate-power">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Guts</span>
                                <input type="number" class="form-control" id="growthRateGuts{{ $id_suffix }}"
                                    name="growthRateGuts" wire:model.lazy="growth_rate_guts"
                                    aria-label="Guts growth rate" {{ $isEditMode ? '' : 'readonly' }}
                                    data-testid="input-growth-rate-guts">
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Wit</span>
                                <input type="number" class="form-control" id="growthRateWit{{ $id_suffix }}"
                                    name="growthRateWit" wire:model.lazy="growth_rate_wit"
                                    aria-label="Wit growth rate" {{ $isEditMode ? '' : 'readonly' }}
                                    data-testid="input-growth-rate-wit">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attributes Tab --}}
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'attributes' }"
            id="attributes{{ $id_suffix }}" role="tabpanel"
            aria-labelledby="attributes-tab{{ $id_suffix }}" x-show="activeTab === 'attributes'"
            data-testid="tab-panel-attributes">
            <div id="attributesContainer{{ $id_suffix }}" class="row g-3">
                @if (count($planAttributes ?? []) > 0)
                    @foreach ($planAttributes as $i => $attr)
                        <div class="col-md-6 col-lg-4" wire:key="attribute-{{ $i }}">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $attr['attribute_name'] ?? 'Attribute' }}</h6>
                                    <div class="mb-2">
                                        <label class="form-label">Value</label>
                                        <input type="number" class="form-control"
                                            wire:model.lazy="planAttributes.{{ $i }}.value"
                                            {{ $isEditMode ? '' : 'readonly' }}
                                            data-testid="input-attribute-{{ $i }}-value">
                                    </div>
                                    <div>
                                        <label class="form-label">Grade</label>
                                        <select class="form-select"
                                            wire:model.lazy="planAttributes.{{ $i }}.grade"
                                            {{ $isEditMode ? '' : 'disabled' }}
                                            data-testid="select-attribute-{{ $i }}-grade">
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
                    <div class="col-12 text-center text-muted" data-testid="no-attributes-message">No attributes
                        available</div>
                @endif
            </div>
        </div>

        {{-- Aptitude Grades Tab --}}
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'grades' }"
            id="grades{{ $id_suffix }}" role="tabpanel" aria-labelledby="grades-tab{{ $id_suffix }}"
            x-show="activeTab === 'grades'" data-testid="tab-panel-aptitude-grades">
            <div id="gradesContainer{{ $id_suffix }}" class="row g-3">
                <div class="col-md-4">
                    <h6>Terrain Grades</h6>
                    @if (count($terrainGrades ?? []) > 0)
                        @foreach ($terrainGrades as $i => $t)
                            <div class="d-flex align-items-center gap-2 mb-2"
                                wire:key="terrain-{{ $i }}">
                                <div class="flex-grow-1">{{ $t['terrain'] ?? '' }}</div>
                                <div style="width: 110px">
                                    <select class="form-select form-select-sm"
                                        wire:model.lazy="terrainGrades.{{ $i }}.grade"
                                        {{ $isEditMode ? '' : 'disabled' }}
                                        data-testid="select-terrain-{{ $i }}-grade">
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
                    @if (count($distanceGrades ?? []) > 0)
                        @foreach ($distanceGrades as $i => $d)
                            <div class="d-flex align-items-center gap-2 mb-2"
                                wire:key="distance-{{ $i }}">
                                <div class="flex-grow-1">{{ $d['distance'] ?? '' }}</div>
                                <div style="width: 110px">
                                    <select class="form-select form-select-sm"
                                        wire:model.lazy="distanceGrades.{{ $i }}.grade"
                                        {{ $isEditMode ? '' : 'disabled' }}
                                        data-testid="select-distance-{{ $i }}-grade">
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
                    @if (count($styleGrades ?? []) > 0)
                        @foreach ($styleGrades as $i => $s)
                            <div class="d-flex align-items-center gap-2 mb-2" wire:key="style-{{ $i }}">
                                <div class="flex-grow-1">{{ $s['style'] ?? '' }}</div>
                                <div style="width: 110px">
                                    <select class="form-select form-select-sm"
                                        wire:model.lazy="styleGrades.{{ $i }}.grade"
                                        {{ $isEditMode ? '' : 'disabled' }}
                                        data-testid="select-style-{{ $i }}-grade">
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
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'skills' }"
            id="skills{{ $id_suffix }}" role="tabpanel" aria-labelledby="skills-tab{{ $id_suffix }}"
            x-show="activeTab === 'skills'" data-testid="tab-panel-skills">
            @livewire(
                'skills.skills-editor',
                [
                    'planId' => $planId,
                    'isEditMode' => $isEditMode,
                    'skills' => $skills ?? [],
                ],
                key('skills-editor-' . ($planId ?? 'new'))
            )
        </div>

        {{-- Race Predictions Tab --}}
        {{-- Requirements: 77.1 - Provide "Create Snapshot" action for each race entry --}}
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'predictions' }"
            id="predictions{{ $id_suffix }}" role="tabpanel"
            aria-labelledby="predictions-tab{{ $id_suffix }}" x-show="activeTab === 'predictions'"
            data-testid="tab-panel-race-predictions">
            <div class="d-flex justify-content-end mb-2">
                @if ($isEditMode)
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addPrediction"
                        data-testid="btn-add-prediction">Add Prediction</button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle" data-testid="predictions-table"
                    aria-label="Race predictions">
                    <caption class="visually-hidden">Race predictions with venue, distance, and recommended stats
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">Race</th>
                            <th scope="col">Venue</th>
                            <th scope="col">Ground</th>
                            <th scope="col">Distance</th>
                            <th scope="col">Track</th>
                            <th scope="col">Direction</th>
                            <th scope="col">Speed</th>
                            <th scope="col">Stamina</th>
                            <th scope="col">Power</th>
                            <th scope="col">Guts</th>
                            <th scope="col">Wit</th>
                            <th scope="col">Comment</th>
                            <th scope="col" style="width: 80px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($predictions ?? []) as $i => $p)
                            <tr wire:key="prediction-{{ $i }}"
                                data-testid="prediction-row-{{ $i }}">
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.race_name"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-race-name"></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.venue"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-venue"></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.ground"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-ground"></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.distance"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-distance"></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.track"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-track"></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.direction"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-direction"></td>
                                <td><input type="number" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.speed"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-speed"></td>
                                <td><input type="number" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.stamina"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-stamina"></td>
                                <td><input type="number" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.power"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-power"></td>
                                <td><input type="number" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.guts"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-guts"></td>
                                <td><input type="number" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.wit"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-wit"></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="predictions.{{ $i }}.comment"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-prediction-{{ $i }}-comment"></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group"
                                        aria-label="Prediction actions">
                                        {{-- Create Snapshot Button (Req 77.1) --}}
                                        @if ($isEditMode && $planId)
                                            <button type="button" class="btn btn-outline-info"
                                                title="Create Snapshot"
                                                @click="$dispatch('open-snapshot-modal', { racePredictionId: {{ $p['id'] ?? 'null' }}, raceName: '{{ addslashes($p['race_name'] ?? '') }}' })"
                                                data-testid="btn-create-snapshot-{{ $i }}">
                                                <i class="bi bi-camera" aria-hidden="true"></i>
                                                <span class="visually-hidden">Create Snapshot</span>
                                            </button>
                                        @endif
                                        @if ($isEditMode)
                                            <button type="button" class="btn btn-outline-danger"
                                                wire:click="removePrediction({{ $i }})" title="Remove"
                                                data-testid="btn-remove-prediction-{{ $i }}">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-muted text-center"
                                    data-testid="no-predictions-message">No predictions yet. Click "Add Prediction"
                                    to
                                    begin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Race Snapshots Section (Req 77.3, 77.4) --}}
            @if ($planId)
                <hr class="my-4">
                <livewire:plans.race-snapshot :planId="$planId" :isEditMode="$isEditMode"
                    wire:key="race-snapshot-{{ $planId }}" />
            @endif
        </div>

        {{-- Goals Tab --}}
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'goals' }"
            id="goals{{ $id_suffix }}" role="tabpanel" aria-labelledby="goals-tab{{ $id_suffix }}"
            x-show="activeTab === 'goals'" data-testid="tab-panel-goals">
            <div class="d-flex justify-content-end mb-2">
                @if ($isEditMode)
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addGoalRow"
                        data-testid="btn-add-goal">Add Goal</button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle" data-testid="goals-table" aria-label="Training goals">
                    <caption class="visually-hidden">Training goals checklist with goal description and result
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">Goal</th>
                            <th scope="col">Result</th>
                            <th scope="col" style="width: 40px"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($goals ?? []) as $i => $g)
                            <tr wire:key="goal-{{ $i }}" data-testid="goal-row-{{ $i }}">
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="goals.{{ $i }}.goal"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-goal-{{ $i }}-text"></td>
                                <td><input type="text" class="form-control form-control-sm"
                                        wire:model.lazy="goals.{{ $i }}.result"
                                        {{ $isEditMode ? '' : 'readonly' }}
                                        data-testid="input-goal-{{ $i }}-result"></td>
                                <td>
                                    @if ($isEditMode)
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            wire:click="removeGoalRow({{ $i }})" title="Remove"
                                            data-testid="btn-remove-goal-{{ $i }}">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-muted text-center" data-testid="no-goals-message">
                                    No
                                    goals yet. Click "Add Goal" to begin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Progress Chart Tab --}}
        <div class="tab-pane fade" :class="{ 'show active': activeTab === 'progress-chart' }"
            id="progress-chart{{ $id_suffix }}" role="tabpanel"
            aria-labelledby="progress-chart-tab{{ $id_suffix }}" x-show="activeTab === 'progress-chart'"
            data-testid="tab-panel-progress-chart">
            <div class="position-relative" style="height: 320px;">
                <canvas id="growthChart{{ $id_suffix === '_inline' ? 'Inline' : '' }}"
                    data-testid="growth-chart-canvas"></canvas>
                <div id="growthChartMessage{{ $id_suffix === '_inline' ? 'Inline' : '' }}"
                    class="text-center text-muted mt-3" data-testid="growth-chart-message"></div>
            </div>
        </div>
    </div>
</div>
