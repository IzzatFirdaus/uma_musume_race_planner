{{-- Import Wizard Component --}}
{{-- Implements REQ-IMP-1: Multi-step import flow with format detection, preview, and conflict resolution --}}
<div x-data="{
        open: @entangle('show').live,
        lastActiveElement: null,
        init() {
            this.$watch('open', (value) => {
                if (value) {
                    this.lastActiveElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;

                    this.$nextTick(() => {
                        this.$refs.closeButton?.focus({ preventScroll: true });
                    });
                } else if (this.lastActiveElement instanceof HTMLElement) {
                    this.$nextTick(() => {
                        this.lastActiveElement.focus({ preventScroll: true });
                    });
                }
            });
        },
        close() {
            this.open = false;
            $wire.closeWizard();
        },
    }" @if ($show) class="modal fade show d-block" tabindex="-1" role="dialog"
    aria-labelledby="importWizardTitle" aria-describedby="importWizardDescription" aria-modal="true"
    x-trap.inert.noscroll="open" x-on:keydown.escape.window.prevent="close()" data-testid="import-wizard">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable motion-safe:transition-transform motion-safe:duration-200 motion-reduce:transition-none">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title d-flex align-items-center gap-2" id="importWizardTitle">
                    <i class="bi bi-upload" aria-hidden="true"></i>
                    Import Wizard
                </h5>
                <button type="button" class="btn-close btn-close-white min-h-11 min-w-11 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-primary"
                    x-ref="closeButton" x-on:click="close()" aria-label="Close import wizard"
                    data-testid="import-wizard-close"></button>
            </div>
            <div class="modal-body" id="importWizardDescription">
                <p class="visually-hidden" aria-live="polite" aria-atomic="true">
                    Step {{ $currentStep }} of 5: {{ $this->getStepLabel($currentStep) }}
                </p>
                {{-- Step Progress Indicator --}}
                <div class="mb-4">
                    <div class="progress" style="height: 30px;" role="progressbar" aria-label="Import progress"
                        aria-valuenow="{{ $currentStep }}" aria-valuemin="1" aria-valuemax="5"
                        aria-valuetext="Step {{ $currentStep }} of 5: {{ $this->getStepLabel($currentStep) }}"
                        data-testid="import-progress-bar">
                        <div class="progress-bar bg-primary" style="width: {{ ($currentStep / 5) * 100 }}%;"></div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-between gap-2 mt-2 small">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="text-center {{ $i <= $currentStep ? 'text-primary fw-semibold' : 'text-muted' }} min-w-[120px] flex-1">
                                <div class="fw-bold">Step {{ $i }}</div>
                                <div>{{ $this->getStepLabel($i) }}</div>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- Step 1: Upload File --}}
                @if ($currentStep === 1)
                    <div class="text-center py-5" data-testid="import-step-1">
                        <i class="bi bi-cloud-arrow-up display-1 text-primary mb-3 d-block" aria-hidden="true"></i>
                        <h4 class="mb-3">Upload Import File</h4>
                        <p class="text-muted mb-4">
                            Select a JSON or CSV file to import. Supported formats:
                            {{ implode(', ', $this->getSupportedFormats()) }}
                        </p>

                        <div class="mb-4">
                            <input type="file" wire:model="file" accept="{{ implode(',', $this->getSupportedExtensions()) }}"
                                class="form-control" id="importFile" data-testid="import-file-input">
                            @error('file')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info small" role="status" aria-live="polite">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                            Maximum file size: 10MB
                        </div>
                    </div>
                @endif

                {{-- Step 2: Preview --}}
                @if ($currentStep === 2)
                    <div data-testid="import-step-2">
                        <h5 class="mb-3">
                            <i class="bi bi-eye me-2" aria-hidden="true"></i>
                            Preview Import Data
                        </h5>

                        {{-- Format Detection Info --}}
                        <div class="alert alert-success d-flex align-items-center gap-3 mb-4" role="status" aria-live="polite">
                            <i class="bi bi-check-circle display-6" aria-hidden="true"></i>
                            <div>
                                <div class="fw-bold">Format Detected: {{ $detectedFormat }}</div>
                                <small class="text-muted">Confidence: {{ $confidence }}</small>
                            </div>
                        </div>

                        {{-- Preview Summary --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light text-center h-100">
                                    <div class="card-body py-3">
                                        <div class="display-6 fw-bold text-primary">
                                            {{ count($previewData['plans'] ?? []) }}
                                        </div>
                                        <small class="text-muted">Plans</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light text-center h-100">
                                    <div class="card-body py-3">
                                        <div class="display-6 fw-bold text-success">
                                            {{ count($previewData['characters'] ?? []) }}
                                        </div>
                                        <small class="text-muted">Characters</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light text-center h-100">
                                    <div class="card-body py-3">
                                        <div class="display-6 fw-bold text-warning">
                                            {{ count($validationWarnings) }}
                                        </div>
                                        <small class="text-muted">Warnings</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light text-center h-100">
                                    <div class="card-body py-3">
                                        <div class="display-6 fw-bold text-info">
                                            {{ $previewData['metadata']['schema_version'] ?? 'N/A' }}
                                        </div>
                                        <small class="text-muted">Schema</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Field Mapping --}}
                        @if (!empty($fieldMapping))
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-diagram-3 me-2" aria-hidden="true"></i>
                                        Field Mapping
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="overflow-x-auto">
                                        <table class="table table-sm small mb-0 min-w-[640px]">
                                            <thead>
                                                <tr>
                                                    <th>Source Field</th>
                                                    <th>Target Field</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($fieldMapping as $source => $target)
                                                    <tr>
                                                        <td><code>{{ $source }}</code></td>
                                                        <td><code>{{ $target }}</code></td>
                                                        <td>
                                                            <span class="badge bg-success">Mapped</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Warnings --}}
                        @if (!empty($validationWarnings))
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                                    Warnings
                                </h6>
                                <ul class="mb-0 small">
                                    @foreach ($validationWarnings as $warning)
                                        <li>{{ $warning['message'] ?? $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Sample Plan Preview --}}
                        @if (!empty($previewData['plans']))
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-earmark-text me-2" aria-hidden="true"></i>
                                        Sample Plan Preview
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <pre class="small bg-light p-3 rounded"
                                        style="max-height: 200px; overflow-y: auto;">{{ json_encode($previewData['plans'][0] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Step 3: Resolve Conflicts --}}
                @if ($currentStep === 3)
                    <div data-testid="import-step-3">
                        <h5 class="mb-3">
                            <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                            Resolve Conflicts
                        </h5>

                        <div class="alert alert-warning" role="status" aria-live="polite">
                            <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                            {{ count($duplicates) }} duplicate plan(s) detected. Please choose how to handle them.
                        </div>

                        {{-- Duplicate List --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Duplicate Plans</h6>
                            </div>
                            <div class="card-body">
                                @if (!empty($duplicates))
                                    <div class="overflow-x-auto">
                                        <table class="table table-sm mb-0 min-w-[640px]">
                                            <thead>
                                                <tr>
                                                    <th>Import Plan</th>
                                                    <th>Existing Plan ID</th>
                                                    <th>Reason</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($duplicates as $duplicate)
                                                    <tr>
                                                        <td>
                                                            {{ $previewData['plans'][$duplicate['plan_index']]['plan_title'] ?? 'Unknown' }}
                                                        </td>
                                                        <td>#{{ $duplicate['existing_id'] }}</td>
                                                        <td>{{ $duplicate['reason'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No duplicates found.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Conflict Resolution Options --}}
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Resolution Strategy</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="conflictResolution"
                                        id="skipDuplicates" value="skip" wire:model.live="skipDuplicates"
                                        data-testid="skip-duplicates">
                                    <label class="form-check-label" for="skipDuplicates">
                                        <strong>Skip Duplicates</strong>
                                        <div class="small text-muted">
                                            Do not import plans that already exist. Existing plans will remain unchanged.
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="conflictResolution"
                                        id="overwriteDuplicates" value="overwrite" wire:model.live="overwriteDuplicates"
                                        data-testid="overwrite-duplicates">
                                    <label class="form-check-label" for="overwriteDuplicates">
                                        <strong>Overwrite Duplicates</strong>
                                        <div class="small text-muted">
                                            Replace existing plans with imported data. This cannot be undone.
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 4: Confirm Import --}}
                @if ($currentStep === 4)
                    <div data-testid="import-step-4">
                        <h5 class="mb-3">
                            <i class="bi bi-check-circle me-2" aria-hidden="true"></i>
                            Confirm Import Settings
                        </h5>

                        {{-- Import Target Selection --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Import Target</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="importTarget" id="targetLocal"
                                        value="local" wire:model.live="target" data-testid="target-local">
                                    <label class="form-check-label" for="targetLocal">
                                        <strong>Local Storage</strong>
                                        <div class="small text-muted">
                                            Import to browser localStorage. Works offline. No authentication required.
                                        </div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="importTarget" id="targetAccount"
                                        value="account" wire:model.live="target" data-testid="target-account"
                                        @if (!auth()->check()) disabled @endif>
                                    <label class="form-check-label" for="targetAccount"
                                        @if (!auth()->check()) class="text-muted" @endif>
                                        <strong>Account Storage</strong>
                                        <div class="small text-muted">
                                            Import to database. Requires authentication. Syncs across devices.
                                            @if (!auth()->check())
                                                <span class="text-danger">(Sign in to enable)</span>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Import Summary --}}
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Import Summary</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Format:</dt>
                                    <dd class="col-sm-8">{{ $detectedFormat }}</dd>

                                    <dt class="col-sm-4">Plans to import:</dt>
                                    <dd class="col-sm-8">{{ count($previewData['plans'] ?? []) }}</dd>

                                    <dt class="col-sm-4">Characters:</dt>
                                    <dd class="col-sm-8">{{ count($previewData['characters'] ?? []) }}</dd>

                                    <dt class="col-sm-4">Target:</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-{{ $target === 'local' ? 'primary' : 'success' }}">
                                            {{ ucfirst($target) }}
                                        </span>
                                    </dd>

                                    @if (!empty($duplicates))
                                        <dt class="col-sm-4">Duplicates:</dt>
                                        <dd class="col-sm-8">
                                            {{ count($duplicates) }}
                                            @if ($skipDuplicates)
                                                <span class="text-muted">(will be skipped)</span>
                                            @elseif ($overwriteDuplicates)
                                                <span class="text-danger">(will be overwritten)</span>
                                            @endif
                                        </dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 5: Results --}}
                @if ($currentStep === 5)
                    <div data-testid="import-step-5">
                        @if ($importSuccess)
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle display-1 text-success mb-3 d-block" aria-hidden="true"></i>
                                <h4 class="mb-3">Import Successful!</h4>
                                <p class="text-muted mb-4">
                                    Your data has been imported successfully.
                                </p>

                                <div class="row justify-content-center mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-light text-center h-100">
                                            <div class="card-body py-3">
                                                <div class="display-6 fw-bold text-success">
                                                    {{ $importResults['created'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Created</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light text-center h-100">
                                            <div class="card-body py-3">
                                                <div class="display-6 fw-bold text-warning">
                                                    {{ $importResults['updated'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Updated</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-light text-center h-100">
                                            <div class="card-body py-3">
                                                <div class="display-6 fw-bold text-info">
                                                    {{ $importResults['skipped'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Skipped</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-x-circle display-1 text-danger mb-3 d-block" aria-hidden="true"></i>
                                <h4 class="mb-3">Import Failed</h4>
                                <p class="text-muted mb-4">
                                    There was an error importing your data. Please review the errors below.
                                </p>

                                @if (!empty($importResults['errors']))
                                        <div class="alert alert-danger text-start" role="alert">
                                        <h6 class="alert-heading">Errors:</h6>
                                        <ul class="mb-0">
                                            @foreach ($importResults['errors'] as $error)
                                                <li>Row {{ $error['row'] ?? '?' }}: {{ $error['message'] }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="modal-footer flex-column flex-sm-row gap-2">
                @if ($currentStep > 1 && $currentStep < 5)
                    <button type="button" class="btn btn-secondary min-h-11" wire:click="previousStep"
                        data-testid="import-back-btn">
                        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                        Back
                    </button>
                @endif

                @if ($currentStep === 1)
                    <button type="button" class="btn btn-primary min-h-11" wire:click="uploadAndDetect"
                        wire:loading.attr="disabled" data-testid="import-detect-btn">
                        <span wire:loading.remove wire:target="uploadAndDetect">
                            <i class="bi bi-arrow-right me-1" aria-hidden="true"></i>
                            Next
                        </span>
                        <span wire:loading wire:target="uploadAndDetect">
                            <span class="spinner-border spinner-border-sm me-1 motion-reduce:hidden" role="status"
                                aria-hidden="true"></span>
                            Detecting...
                        </span>
                    </button>
                @endif

                @if ($currentStep === 2)
                    <button type="button" class="btn btn-primary min-h-11" wire:click="validateImport"
                        wire:loading.attr="disabled" data-testid="import-validate-btn">
                        <span wire:loading.remove wire:target="validateImport">
                            <i class="bi bi-arrow-right me-1" aria-hidden="true"></i>
                            Next
                        </span>
                        <span wire:loading wire:target="validateImport">
                            <span class="spinner-border spinner-border-sm me-1 motion-reduce:hidden" role="status"
                                aria-hidden="true"></span>
                            Validating...
                        </span>
                    </button>
                @endif

                @if ($currentStep === 3)
                    <button type="button" class="btn btn-primary min-h-11" wire:click="resolveConflicts"
                        data-testid="import-resolve-btn">
                        <i class="bi bi-arrow-right me-1" aria-hidden="true"></i>
                        Next
                    </button>
                @endif

                @if ($currentStep === 4)
                    <button type="button" class="btn btn-success min-h-11" wire:click="executeImport"
                        wire:loading.attr="disabled" data-testid="import-execute-btn">
                        <span wire:loading.remove wire:target="executeImport">
                            <i class="bi bi-check-circle me-1" aria-hidden="true"></i>
                            Import Now
                        </span>
                        <span wire:loading wire:target="executeImport">
                            <span class="spinner-border spinner-border-sm me-1 motion-reduce:hidden" role="status"
                                aria-hidden="true"></span>
                            Importing...
                        </span>
                    </button>
                @endif

                @if ($currentStep === 5)
                    <button type="button" class="btn btn-primary min-h-11" x-on:click="close()"
                        data-testid="import-close-btn">
                        <i class="bi bi-check me-1" aria-hidden="true"></i>
                        Done
                    </button>
                @endif

                <button type="button" class="btn btn-outline-secondary min-h-11" x-on:click="close()"
                    data-testid="import-cancel-btn">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-on:click="close()" aria-hidden="true"></div>
@endif
