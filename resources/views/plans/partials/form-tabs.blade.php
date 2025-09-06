
<div>
    <div class="row mb-3">
        <select class="form-select" id="modalCareerStage{{ $id_suffix }}" name="modalCareerStage" required>
            @foreach ($careerStageOptions ?? [] as $option)
            <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="modalClass{{ $id_suffix }}" class="form-label">Class</label>
            <select class="form-select" id="modalClass{{ $id_suffix }}" name="modalClass" required>
                @foreach ($classOptions ?? [] as $option)
                <option value="{{ $option['value'] }}">{{ $option['text'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8">
            <label for="modalStatus{{ $id_suffix }}" class="form-label">Status</label>
            <select class="form-select" id="modalStatus{{ $id_suffix }}" name="modalStatus">
                <option value="Planning">Planning</option>
                <option value="Active">Active</option>
                <option value="Finished">Finished</option>
                <option value="Draft">Draft</option>
                <option value="Abandoned">Abandoned</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4">
            <label for="modalStrategy{{ $id_suffix }}" class="form-label">Strategy</label>
            <select class="form-select" id="modalStrategy{{ $id_suffix }}" name="modalStrategy">
                @foreach ($strategyOptions ?? [] as $option)
                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="modalMood{{ $id_suffix }}" class="form-label">Mood</label>
            <select class="form-select" id="modalMood{{ $id_suffix }}" name="modalMood">
                @foreach ($moodOptions ?? [] as $option)
                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="modalCondition{{ $id_suffix }}" class="form-label">Condition</label>
            <select class="form-select" id="modalCondition{{ $id_suffix }}" name="modalCondition">
                @foreach ($conditionOptions ?? [] as $option)
                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6"><label for="modalGoal{{ $id_suffix }}" class="form-label">Goal</label><input type="text" class="form-control" id="modalGoal{{ $id_suffix }}" name="modalGoal"></div>
        <div class="col-md-6"><label for="modalSource{{ $id_suffix }}" class="form-label">Source</label><input type="text" class="form-control" id="modalSource{{ $id_suffix }}" name="modalSource"></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6"><label for="modalMonth{{ $id_suffix }}" class="form-label">Month</label><input type="text" class="form-control" id="modalMonth{{ $id_suffix }}" name="modalMonth" placeholder="e.g., July"></div>
        <div class="col-md-6"><label for="modalTimeOfDay{{ $id_suffix }}" class="form-label">Time of Day</label><input type="text" class="form-control" id="modalTimeOfDay{{ $id_suffix }}" name="modalTimeOfDay" placeholder="e.g., Early / Late"></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4"><label for="skillPoints{{ $id_suffix }}" class="form-label">Total SP</label><input type="number" class="form-control" id="skillPoints{{ $id_suffix }}" name="skillPoints" value="0"></div>
        <div class="col-md-4 d-flex align-items-center"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" id="acquireSkillSwitch{{ $id_suffix }}" name="acquireSkillSwitch" value="YES"><label class="form-check-label" for="acquireSkillSwitch{{ $id_suffix }}">Acquire Skill?</label></div></div>
        <div class="col-md-4 d-flex align-items-center"><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" id="raceDaySwitch{{ $id_suffix }}" name="raceDaySwitch"><label class="form-check-label" for="raceDaySwitch{{ $id_suffix }}">Race Day?</label></div></div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12"><label for="energyRange{{ $id_suffix }}" class="form-label">Energy (<span id="energyValue{{ $id_suffix }}">0</span>/100%)</label><input type="range" class="form-range" min="0" max="100" id="energyRange{{ $id_suffix }}" name="energyRange"></div>
    </div>
    <div class="row mb-3">
        <h6>Growth Rates (%)</h6>
        <div class="col"><label for="growthRateSpeed{{ $id_suffix }}" class="form-label">Speed</label><input type="number" class="form-control" id="growthRateSpeed{{ $id_suffix }}" name="growthRateSpeed" value="0"></div>
        <div class="col"><label for="growthRateStamina{{ $id_suffix }}" class="form-label">Stamina</label><input type="number" class="form-control" id="growthRateStamina{{ $id_suffix }}" name="growthRateStamina" value="0"></div>
        <div class="col"><label for="growthRatePower{{ $id_suffix }}" class="form-label">Power</label><input type="number" class="form-control" id="growthRatePower{{ $id_suffix }}" name="growthRatePower" value="0"></div>
        <div class="col"><label for="growthRateGuts{{ $id_suffix }}" class="form-label">Guts</label><input type="number" class="form-control" id="growthRateGuts{{ $id_suffix }}" name="growthRateGuts" value="0"></div>
        <div class="col"><label for="growthRateWit{{ $id_suffix }}" class="form-label">Wit</label><input type="number" class="form-control" id="growthRateWit{{ $id_suffix }}" name="growthRateWit" value="0"></div>
    </div>
    <div class="row mb-3">
        <div class="tab-pane fade" id="attributes{{ $id_suffix }}" role="tabpanel"><div id="attributeSlidersContainer{{ $id_suffix }}"></div></div>
        <div class="tab-pane fade" id="grades{{ $id_suffix }}" role="tabpanel"><div class="row" id="aptitudeGradesContainer{{ $id_suffix }}"></div></div>
        <div class="tab-pane fade" id="skills{{ $id_suffix }}" role="tabpanel">
            <div class="table-responsive"><table class="table table-sm" id="skillsTable{{ $id_suffix }}"><thead><tr><th>Skill Name</th><th>SP Cost</th><th class="text-center">Acquired</th><th>Tag</th><th>Notes</th><th>Actions</th></tr></thead><tbody></tbody></table></div><button type="button" class="btn btn-uma w-100 mt-2" id="addSkillBtn{{ $id_suffix }}">Add Skill</button>
        </div>
        <div class="tab-pane fade" id="predictions{{ $id_suffix }}" role="tabpanel">
            <div class="table-responsive"><table class="table table-sm" id="predictionsTable{{ $id_suffix }}"><thead><tr><th>Race</th><th>Venue</th><th>Ground</th><th>Distance</th><th>Track</th><th>Direction</th><th>Speed</th><th>Stamina</th><th>Power</th><th>Guts</th><th>Wit</th><th>Comment</th><th>Actions</th></tr></thead><tbody></tbody></table></div><button type="button" class="btn btn-uma w-100 mt-2" id="addPredictionBtn{{ $id_suffix }}">Add Prediction</button>
        </div>
        <div class="tab-pane fade" id="goals{{ $id_suffix }}" role="tabpanel">
            <div class="table-responsive"><table class="table table-sm" id="goalsTable{{ $id_suffix }}"><thead><tr><th>Goal</th><th>Result</th><th>Actions</th></tr></thead><tbody></tbody></table></div><button type="button" class="btn btn-uma w-100 mt-2" id="addGoalBtn{{ $id_suffix }}">Add Goal</button>
        </div>
        <div class="tab-pane fade" id="progress-chart{{ $id_suffix }}" role="tabpanel">
            <div class="chart-container" style="position: relative; height: 400px;"><canvas id="growthChart{{ $id_suffix }}"></canvas><div id="growthChartMessage{{ $id_suffix }}" class="text-center p-5 h-100 d-flex justify-content-center align-items-center" style="display: none;"><p class="text-muted fs-5">No progression data available.</p></div></div>
        </div>
    </div>
</div>
