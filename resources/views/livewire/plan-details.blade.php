<div class="modal fade" id="planDetailsModal" tabindex="-1" aria-labelledby="planDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planDetailsModalLabel">
                    @if ($plan_title)
                        Plan Details: {{ $plan_title }}
                    @else
                        Plan Details
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- Bootstrap Tab Navigation (Req 14.3 - Horizontal scrolling on mobile) --}}
                <div class="tabs-scroll-container -mx-3 px-3 sm:mx-0 sm:px-0">
                    <ul class="nav nav-tabs tabs-scroll-list mb-3" id="planTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="attributes-tab" data-bs-toggle="tab"
                                data-bs-target="#attributes-pane" type="button" role="tab"
                                aria-controls="attributes-pane" aria-selected="true">
                                Attributes
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="skills-tab" data-bs-toggle="tab" data-bs-target="#skills-pane"
                                type="button" role="tab" aria-controls="skills-pane" aria-selected="false">
                                Skills
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="predictions-tab" data-bs-toggle="tab"
                                data-bs-target="#predictions-pane" type="button" role="tab"
                                aria-controls="predictions-pane" aria-selected="false">
                                Race Predictions
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="goals-tab" data-bs-toggle="tab" data-bs-target="#goals-pane"
                                type="button" role="tab" aria-controls="goals-pane" aria-selected="false">
                                Goals
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="support-cards-tab" data-bs-toggle="tab"
                                data-bs-target="#support-cards-pane" type="button" role="tab"
                                aria-controls="support-cards-pane" aria-selected="false">
                                Support Cards
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Tab Content --}}
                <div class="tab-content" id="planTabsContent">
                    {{-- Attributes Tab --}}
                    <div class="tab-pane fade show active" id="attributes-pane" role="tabpanel"
                        aria-labelledby="attributes-tab" tabindex="0">
                        @if (empty($planAttributes))
                            <div class="text-center text-muted p-4">
                                <p>No attributes data available</p>
                            </div>
                        @else
                            <div class="row">
                                @foreach ($planAttributes ?? [] as $index => $attribute)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ $attribute['attribute_name'] ?? 'Unknown' }}
                                                </h6>
                                                <div class="mb-2">
                                                    <label class="form-label">Value</label>
                                                    <input type="number" class="form-control"
                                                        wire:model.defer="planAttributes.{{ $index }}.value"
                                                        value="{{ $attribute['value'] ?? 0 }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">Grade</label>
                                                    <select class="form-select"
                                                        wire:model.defer="planAttributes.{{ $index }}.grade">
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
                            </div>
                        @endif
                    </div>

                    {{-- Skills Tab --}}
                    <div class="tab-pane fade" id="skills-pane" role="tabpanel" aria-labelledby="skills-tab"
                        tabindex="0">
                        <livewire:plans.skills-editor :planId="$planId" :skills="$skills" />
                    </div>

                    {{-- Race Predictions Tab --}}
                    <div class="tab-pane fade" id="predictions-pane" role="tabpanel"
                        aria-labelledby="predictions-tab" tabindex="0">
                        <livewire:plans.race-predictions-editor :planId="$planId" :racePredictions="$racePredictions" />
                    </div>

                    {{-- Goals Tab --}}
                    <div class="tab-pane fade" id="goals-pane" role="tabpanel" aria-labelledby="goals-tab"
                        tabindex="0">
                        <livewire:plans.goals-editor :planId="$planId" :goals="$goals" />
                    </div>

                    {{-- Support Cards Tab --}}
                    <div class="tab-pane fade" id="support-cards-pane" role="tabpanel"
                        aria-labelledby="support-cards-tab" tabindex="0">
                        <div class="py-3">
                            @livewire('dashboard.support-card-summary')
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-info" id="exportPlanBtn">Copy to Clipboard</button>
                <a href="#" id="downloadTxtLink" class="btn btn-outline-secondary">
                    <i class="bi bi-file-earmark-text"></i> Export as TXT
                </a>
                <button type="submit" class="btn btn-uma">Save Changes</button>
            </div>
        </div>
    </div>
</div>
