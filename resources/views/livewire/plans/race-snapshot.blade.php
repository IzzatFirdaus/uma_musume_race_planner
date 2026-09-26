{{-- Race Snapshots Section (Req 77.3, 77.4) --}}
<div class="race-snapshots" data-testid="race-snapshots">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">
            Race Snapshots
            <span class="badge bg-secondary ms-1" data-testid="snapshot-count">
                {{ $snapshots->count() }} / {{ \App\Livewire\Plans\RaceSnapshot::MAX_SNAPSHOTS }}
            </span>
        </h5>
        @if ($isEditMode)
            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="openModal()"
                data-testid="btn-add-snapshot">
                <i class="bi bi-camera" aria-hidden="true"></i>
                Add Snapshot
            </button>
        @endif
    </div>

    @if ($snapshots->isEmpty())
        <p class="text-muted" data-testid="no-snapshots-message">
            No snapshots yet. Use “Create Snapshot” on a prediction to capture the stats for that turn.
        </p>
    @else
        <div class="table-responsive">
            <table class="table table-sm align-middle" data-testid="snapshots-table">
                <caption class="visually-hidden">
                    Career snapshots recorded for this plan, most recent turn first.
                </caption>
                <thead>
                    <tr>
                        <th scope="col">Turn</th>
                        <th scope="col">Race</th>
                        <th scope="col">Speed</th>
                        <th scope="col">Stamina</th>
                        <th scope="col">Power</th>
                        <th scope="col">Guts</th>
                        <th scope="col">Wit</th>
                        <th scope="col">Total</th>
                        <th scope="col">Stamina %</th>
                        <th scope="col">SP</th>
                        <th scope="col">Notes</th>
                        @if ($isEditMode)
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($snapshots as $snapshot)
                        <tr wire:key="snapshot-{{ $snapshot->id }}" data-testid="snapshot-row-{{ $snapshot->id }}">
                            <td>{{ $snapshot->turn_number }}</td>
                            <td>{{ $snapshot->race_name ?? '—' }}</td>
                            <td>{{ $snapshot->speed }}</td>
                            <td>{{ $snapshot->stamina }}</td>
                            <td>{{ $snapshot->power }}</td>
                            <td>{{ $snapshot->guts }}</td>
                            <td>{{ $snapshot->wit }}</td>
                            <td>{{ $snapshot->total_stats }}</td>
                            <td>{{ $snapshot->stamina_percentage ?? '—' }}</td>
                            <td>{{ $snapshot->total_sp_available ?? '—' }}</td>
                            <td>{{ $snapshot->notes ?? '—' }}</td>
                            @if ($isEditMode)
                                <td>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                        wire:click="delete({{ $snapshot->id }})"
                                        wire:confirm="Delete this snapshot?"
                                        title="Delete snapshot"
                                        data-testid="btn-delete-snapshot-{{ $snapshot->id }}">
                                        <i class="bi bi-trash" aria-hidden="true"></i>
                                        <span class="visually-hidden">Delete snapshot for turn
                                            {{ $snapshot->turn_number }}</span>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Create Snapshot Modal --}}
@if ($showModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
        aria-labelledby="raceSnapshotModalLabel" data-testid="race-snapshot-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="raceSnapshotModalLabel">
                        Create Snapshot{{ $raceName !== '' ? ' — '.$raceName : '' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="save" id="race-snapshot-form">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="snapshot-turn-{{ $planId }}" class="form-label">Turn</label>
                                <input type="number" min="1" max="78" class="form-control"
                                    id="snapshot-turn-{{ $planId }}" wire:model="turnNumber"
                                    data-testid="input-snapshot-turn">
                                @error('turnNumber')
                                    <div class="text-danger small" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="snapshot-stamina-pct-{{ $planId }}" class="form-label">Stamina %</label>
                                <input type="number" min="0" max="100" class="form-control"
                                    id="snapshot-stamina-pct-{{ $planId }}" wire:model="staminaPercentage"
                                    data-testid="input-snapshot-stamina-percentage">
                                @error('staminaPercentage')
                                    <div class="text-danger small" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="snapshot-sp-{{ $planId }}" class="form-label">SP Available</label>
                                <input type="number" min="0" class="form-control" id="snapshot-sp-{{ $planId }}"
                                    wire:model="totalSpAvailable" data-testid="input-snapshot-sp">
                                @error('totalSpAvailable')
                                    <div class="text-danger small" role="alert">{{ $message }}</div>
                                @enderror
                            </div>

                            @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                <div class="col-6 col-md-2">
                                    <label for="snapshot-{{ $stat }}-{{ $planId }}" class="form-label text-capitalize">
                                        {{ $stat }}
                                    </label>
                                    <input type="number" min="0" max="1200" class="form-control"
                                        id="snapshot-{{ $stat }}-{{ $planId }}" wire:model="stats.{{ $stat }}"
                                        data-testid="input-snapshot-{{ $stat }}">
                                    @error('stats.'.$stat)
                                        <div class="text-danger small" role="alert">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach

                            <div class="col-12">
                                <label for="snapshot-notes-{{ $planId }}" class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" id="snapshot-notes-{{ $planId }}"
                                    wire:model="notes" data-testid="input-snapshot-notes"></textarea>
                                @error('notes')
                                    <div class="text-danger small" role="alert">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeModal()">
                        Cancel
                    </button>
                    <button type="submit" form="race-snapshot-form" class="btn btn-primary"
                        data-testid="btn-save-snapshot">
                        Save Snapshot
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
