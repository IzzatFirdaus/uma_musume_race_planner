{{-- Convert Run Modal Component --}}
{{-- Implements FR-9C.1: Convert single local run to account --}}
<div>
    @if ($show && $this->plan)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="convertRunModalTitle"
            aria-modal="true" data-testid="convert-run-modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="convertRunModalTitle">
                            <i class="bi bi-cloud-upload me-2" aria-hidden="true"></i>
                            Convert to Account
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Plan info --}}
                        <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                            @if ($this->plan->trainee_image_path)
                                <img src="{{ asset($this->plan->trainee_image_path) }}" alt="{{ $this->plan->name }}"
                                    class="rounded me-3" style="width: 48px; height: 48px; object-fit: cover;">
                            @else
                                <div class="rounded bg-secondary d-flex align-items-center justify-content-center me-3"
                                    style="width: 48px; height: 48px;">
                                    <i class="bi bi-person text-white" aria-hidden="true"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-bold">{{ $this->plan->name }}</div>
                                @if ($this->plan->plan_title)
                                    <small class="text-muted">{{ $this->plan->plan_title }}</small>
                                @endif
                            </div>
                        </div>

                        {{-- Duplicate warning (FR-9C.4, FR-9C.5) --}}
                        @if ($hasDuplicate && $duplicateInfo)
                            <div class="alert alert-warning" role="alert">
                                <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                                <strong>Possible duplicate detected</strong>
                                <p class="mb-2 mt-2">
                                    A similar plan already exists in your account:
                                </p>
                                <div class="bg-white p-2 rounded border mb-3">
                                    <strong>{{ $duplicateInfo['name'] }}</strong>
                                    @if ($duplicateInfo['title'])
                                        <br><small class="text-muted">{{ $duplicateInfo['title'] }}</small>
                                    @endif
                                    <br><small class="text-muted">Created: {{ $duplicateInfo['created_at'] }}</small>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="duplicateAction"
                                        id="duplicateCreate" value="create" wire:model="duplicateAction">
                                    <label class="form-check-label" for="duplicateCreate">
                                        Create duplicate anyway
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="duplicateAction"
                                        id="duplicateCancel" value="cancel" wire:model="duplicateAction">
                                    <label class="form-check-label" for="duplicateCancel">
                                        Cancel conversion
                                    </label>
                                </div>
                            </div>
                        @else
                            <p class="mb-3">
                                This will convert your local plan to your account, making it available across all your
                                devices.
                            </p>
                        @endif

                        {{-- Keep local copy option (FR-9C.8) --}}
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="keepLocalCopy"
                                wire:model="keepLocalCopy">
                            <label class="form-check-label" for="keepLocalCopy">
                                Also keep local copy (offline fallback)
                            </label>
                            <div class="form-text">
                                By default, the local copy is removed after conversion.
                            </div>
                        </div>

                        {{-- Info about what gets converted --}}
                        <div class="small text-muted">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                            All related data will be converted: skills, stats, goals, race predictions, and snapshots.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-success" wire:click="convert" wire:loading.attr="disabled"
                            data-testid="confirm-convert-btn"
                            {{ $hasDuplicate && $duplicateAction === 'cancel' ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="convert">
                                <i class="bi bi-cloud-upload me-1" aria-hidden="true"></i>
                                Convert to Account
                            </span>
                            <span wire:loading wire:target="convert">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Converting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
