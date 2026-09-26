{{-- Skill Editor Component --}}
{{-- Implements REQ-SKILL-1.2, REQ-SKILL-1.3, REQ-SKILL-1.4, REQ-SKILL-1.5 --}}
<div class="skill-editor space-y-4" data-testid="skill-editor">
    {{-- SP Totals Summary --}}
    <div class="card mb-3" data-testid="sp-totals">
        <div class="card-body">
            <h5 class="card-title mb-3">Skill Point Totals</h5>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xxl-4 g-3">
                <div class="col">
                    <div class="h-100 rounded-xl border border-slate-200 border-l-4 border-l-emerald-500 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                        <div class="text-sm font-medium text-slate-600 dark:text-slate-300">Acquired SP</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ $sp['acquired'] }}</div>
                    </div>
                </div>
                <div class="col">
                    <div class="h-100 rounded-xl border border-slate-200 border-l-4 border-l-amber-500 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                        <div class="text-sm font-medium text-slate-600 dark:text-slate-300">Suggested SP</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ $sp['suggested'] }}</div>
                    </div>
                </div>
                <div class="col">
                    <div class="h-100 rounded-xl border border-slate-200 border-l-4 border-l-slate-500 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                        <div class="text-sm font-medium text-slate-600 dark:text-slate-300">Skipped SP</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ $sp['skipped'] }}</div>
                    </div>
                </div>
                <div class="col">
                    <div class="h-100 rounded-xl border border-slate-200 border-l-4 border-l-sky-500 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                        <div class="text-sm font-medium text-slate-600 dark:text-slate-300">Total Planned</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900 dark:text-slate-50">{{ $sp['total_planned'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Skill Search --}}
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-3">Add Skill</h5>
            <livewire:skills.skill-search :planId="$plan->id" />
        </div>
    </div>

    {{-- Skills List --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <h5 class="card-title mb-0">Skills ({{ count($skills) }})</h5>
                <button type="button" wire:click="addSkill"
                    class="btn btn-sm btn-outline-primary min-h-11"
                    data-testid="add-skill-button">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
                    Add Skill
                </button>
            </div>

            @if (count($skills) === 0)
                <div class="text-center py-5 text-muted" data-testid="no-skills" role="status" aria-live="polite">
                    <i class="bi bi-lightbulb fs-1 mb-2 d-block" aria-hidden="true"></i>
                    <p>No skills added yet. Search above to add skills.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                    <table class="table table-hover mb-0 min-w-[760px] align-middle" data-testid="skills-table"
                        aria-label="Skill tracker table">
                        <thead>
                            <tr>
                                <th scope="col">Skill</th>
                                <th scope="col">Status</th>
                                <th scope="col">Turn Acquired</th>
                                <th scope="col">SP Cost</th>
                                <th scope="col">Notes</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($skills as $index => $skill)
                                <tr wire:key="skill-row-{{ $skill['id'] ?? 'new-' . $index }}"
                                    data-testid="skill-row-{{ $index }}">
                                    <td>
                                        <div class="fw-bold">{{ $skill['name'] }}</div>
                                        @if ($skill['name_jp'])
                                            <small class="text-muted">{{ $skill['name_jp'] }}</small>
                                        @endif
                                        @if ($skill['tag'])
                                            <span class="badge bg-info ms-1">{{ $skill['tag'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select wire:model.live="skills.{{ $index }}.status"
                                            wire:change="updateStatus({{ $index }}, $event.target.value)"
                                            class="form-select form-select-sm min-h-11"
                                            data-testid="skill-status-{{ $index }}"
                                            aria-label="Skill status">
                                            <option value="acquired">Acquired</option>
                                            <option value="suggested">Suggested</option>
                                            <option value="skipped">Skipped</option>
                                        </select>
                                        @error('skills.' . $index . '.status')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number"
                                            wire:model.live="skills.{{ $index }}.turn_acquired"
                                            wire:change="updateTurnAcquired({{ $index }}, $event.target.value)"
                                            class="form-control form-control-sm min-h-11"
                                            placeholder="Turn"
                                            min="1"
                                            max="78"
                                            {{ $skill['status'] !== 'acquired' ? 'disabled' : '' }}
                                            data-testid="skill-turn-{{ $index }}"
                                            aria-label="Turn acquired">
                                        @error('skills.' . $index . '.turn_acquired')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number"
                                            wire:model.live="skills.{{ $index }}.sp_cost"
                                            class="form-control form-control-sm min-h-11"
                                            placeholder="SP"
                                            min="0"
                                            data-testid="skill-sp-{{ $index }}"
                                            aria-label="SP cost">
                                    </td>
                                    <td>
                                        <input type="text"
                                            wire:model.live="skills.{{ $index }}.notes"
                                            class="form-control form-control-sm min-h-11"
                                            placeholder="Notes"
                                            maxlength="1000"
                                            data-testid="skill-notes-{{ $index }}"
                                            aria-label="Skill notes">
                                    </td>
                                    <td class="text-end">
                                        <button type="button"
                                            wire:click="removeSkill({{ $index }})"
                                            class="btn btn-sm btn-outline-danger min-h-11 min-w-11"
                                            data-testid="remove-skill-{{ $index }}"
                                            aria-label="Remove skill">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class="card-footer">
            <button type="button"
                wire:click="save"
                class="btn btn-primary min-h-11"
                data-testid="save-skills-button">
                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>
                Save Skills
            </button>
        </div>
    </div>
</div>
