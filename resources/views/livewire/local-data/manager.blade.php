{{-- Local Data Manager Page - Livewire Component View --}}
{{-- Implements Requirements 56B.1-56B.8 --}}
<div class="container py-4" x-data="localDataManager()" x-init="init()" data-testid="local-data-manager">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">
                <i class="bi bi-hdd me-2" aria-hidden="true"></i>
                Local Data Management
            </h1>
            <p class="text-muted mb-0">
                Manage plans stored locally in your browser
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" data-testid="back-to-dashboard">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
            Back to Dashboard
        </a>
    </div>

    {{-- Storage Stats Card (Req 56B.7) --}}
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="row g-3">
                {{-- Run Count --}}
                <div class="col-md-3 text-center">
                    <div class="h3 mb-1 text-warning" data-testid="local-plans-count" x-text="runCount">
                        0
                    </div>
                    <small class="text-muted">Local Plans</small>
                </div>

                {{-- Storage Used --}}
                <div class="col-md-3 text-center">
                    <div class="h3 mb-1 text-info" data-testid="storage-used" x-text="formatBytes(storageUsed)">
                        0 B
                    </div>
                    <small class="text-muted">Storage Used</small>
                </div>

                {{-- Storage Progress Bar --}}
                <div class="col-md-3 text-center">
                    <div class="progress mb-2" style="height: 24px;" role="progressbar" :aria-valuenow="percentUsed"
                        aria-valuemin="0" aria-valuemax="100" :aria-label="'Storage usage: ' + percentUsed + '%'">
                        <div class="progress-bar"
                            :class="{
                                'bg-danger': isNearQuota,
                                'bg-warning': percentUsed > 50 && !isNearQuota,
                                'bg-success': percentUsed <= 50
                            }"
                            :style="'width: ' + percentUsed + '%'" x-text="percentUsed + '%'">
                        </div>
                    </div>
                    <small class="text-muted">of ~5MB quota</small>
                    <template x-if="isNearQuota">
                        <div class="text-danger small mt-1" data-testid="quota-warning">
                            <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
                            Approaching storage limit!
                        </div>
                    </template>
                </div>

                {{-- Auth Status --}}
                <div class="col-md-3 text-center">
                    <div class="h3 mb-1 {{ $this->isAuthenticated ? 'text-success' : 'text-secondary' }}">
                        <i class="bi {{ $this->isAuthenticated ? 'bi-check-circle' : 'bi-x-circle' }}"
                            aria-hidden="true"></i>
                    </div>
                    <small class="text-muted">
                        {{ $this->isAuthenticated ? 'Signed In' : 'Not Signed In' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-header fw-bold">
            <i class="bi bi-gear me-2" aria-hidden="true"></i>
            Actions
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                {{-- Export All (Req 56B.3) --}}
                <button type="button" class="btn btn-outline-primary" @click="exportAllRuns()"
                    :disabled="runCount === 0" data-testid="export-all-json">
                    <i class="bi bi-download me-1" aria-hidden="true"></i>
                    Export All as JSON
                </button>

                {{-- Export Selected (Req 56B.8) --}}
                <button type="button" class="btn btn-outline-info" @click="exportSelectedRuns()"
                    :disabled="selectedRuns.length === 0" data-testid="export-selected-json">
                    <i class="bi bi-check2-square me-1" aria-hidden="true"></i>
                    Export Selected (<span x-text="selectedRuns.length">0</span>)
                </button>

                {{-- Import (Req 56B.4) --}}
                <button type="button" class="btn btn-outline-secondary" wire:click="showImport"
                    data-testid="import-json">
                    <i class="bi bi-upload me-1" aria-hidden="true"></i>
                    Import from JSON
                </button>

                {{-- Convert All to Account (Req 56B.6) --}}
                <button type="button" class="btn btn-outline-success" wire:click="showBulkConvert"
                    :disabled="runCount === 0"
                    title="{{ !$this->isAuthenticated ? 'Sign in required' : 'Convert all local plans to your account' }}"
                    data-testid="convert-all-account">
                    <i class="bi bi-cloud-upload me-1" aria-hidden="true"></i>
                    Convert All to Account
                    @if (!$this->isAuthenticated)
                        <i class="bi bi-lock ms-1" aria-hidden="true"></i>
                    @endif
                </button>

                {{-- Purge All (Req 56B.5) --}}
                <button type="button" class="btn btn-outline-danger" wire:click="showPurgeConfirmation"
                    :disabled="runCount === 0" data-testid="purge-all-local">
                    <i class="bi bi-trash me-1" aria-hidden="true"></i>
                    Purge All Local Data
                </button>
            </div>

            @if (!$this->isAuthenticated)
                <div class="alert alert-info mt-3 mb-0" role="alert">
                    <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                    <a href="{{ route('login') }}" class="alert-link">Sign in</a> to convert local plans to your
                    account and sync across devices.
                </div>
            @endif
        </div>
    </div>

    {{-- Local Plans List (Req 56B.2) --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header fw-bold d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                <i class="bi bi-list-ul me-2" aria-hidden="true"></i>
                Local Plans
            </span>
            <div class="d-flex gap-2 align-items-center">
                {{-- Select All / Deselect All --}}
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" @click="selectAll()"
                        :disabled="runs.length === 0" data-testid="select-all-runs">
                        Select All
                    </button>
                    <button type="button" class="btn btn-outline-secondary" @click="deselectAll()"
                        :disabled="selectedRuns.length === 0" data-testid="deselect-all-runs">
                        Deselect All
                    </button>
                </div>
                {{-- Search --}}
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </span>
                    <input type="search" class="form-control border-start-0" placeholder="Search plans..."
                        x-model="searchQuery" @input.debounce.300ms="filterRuns()" aria-label="Search local plans"
                        data-testid="local-plans-search">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <template x-if="filteredRuns.length > 0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" data-testid="local-plans-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 40px;">
                                    <span class="visually-hidden">Select</span>
                                </th>
                                <th scope="col">Plan Name</th>
                                <th scope="col">Character</th>
                                <th scope="col">Status</th>
                                <th scope="col">Size</th>
                                <th scope="col">Last Updated</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="run in filteredRuns" :key="run.uuid">
                                <tr :data-testid="'local-plan-row-' + run.uuid">
                                    <td>
                                        <input type="checkbox" class="form-check-input"
                                            :checked="selectedRuns.includes(run.uuid)"
                                            @change="toggleSelection(run.uuid)" :aria-label="'Select ' + run.title"
                                            :data-testid="'select-run-' + run.uuid">
                                    </td>
                                    <td>
                                        <div class="fw-semibold" x-text="run.title || 'Untitled Plan'"></div>
                                        <small class="text-muted font-monospace"
                                            x-text="run.uuid.substring(0, 8) + '...'"></small>
                                    </td>
                                    <td x-text="run.character_name || '-'"></td>
                                    <td>
                                        <span class="badge"
                                            :class="{
                                                'bg-success': run.status === 'completed',
                                                'bg-primary': run.status === 'in_progress',
                                                'bg-secondary': run.status === 'archived'
                                            }"
                                            x-text="run.status || 'unknown'">
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted" x-text="formatBytes(getRunSize(run))"></small>
                                    </td>
                                    <td>
                                        <time :datetime="run.updated_at" x-text="formatDate(run.updated_at)"></time>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a :href="'/plans/local/' + run.uuid" class="btn btn-outline-primary"
                                                title="View plan" :data-testid="'view-plan-' + run.uuid">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                                <span class="visually-hidden">View</span>
                                            </a>
                                            <button type="button" class="btn btn-outline-success"
                                                @click="$wire.convertSingleRun(run.uuid)"
                                                title="{{ $this->isAuthenticated ? 'Convert to account' : 'Sign in required' }}"
                                                :data-testid="'convert-plan-' + run.uuid"
                                                {{ !$this->isAuthenticated ? 'disabled' : '' }}>
                                                <i class="bi bi-cloud-upload" aria-hidden="true"></i>
                                                <span class="visually-hidden">Convert to Account</span>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger"
                                                @click="confirmDeleteRun(run)" title="Delete plan"
                                                :data-testid="'delete-plan-' + run.uuid">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                                <span class="visually-hidden">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>

            <template x-if="filteredRuns.length === 0">
                <div class="text-center py-5" data-testid="local-plans-empty">
                    <i class="bi bi-inbox display-4 text-muted mb-3 d-block" aria-hidden="true"></i>
                    <p class="text-muted mb-0">
                        <template x-if="searchQuery">
                            <span>No local plans match your search</span>
                        </template>
                        <template x-if="!searchQuery">
                            <span>No local plans found</span>
                        </template>
                    </p>
                    <small class="text-muted">
                        Plans created without signing in will appear here
                    </small>
                </div>
            </template>
        </div>
    </div>

    {{-- Purge Step 1 Modal (Req 56B.5) --}}
    @if ($showPurgeStep1)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="purgeStep1Title"
            aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title" id="purgeStep1Title">
                            <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                            Purge All Local Data - Step 1
                        </h5>
                        <button type="button" class="btn-close" wire:click="cancelPurge"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            You are about to delete <strong x-text="runCount">0</strong> local plan(s)
                            totaling approximately <strong x-text="formatBytes(storageUsed)">0 B</strong>.
                        </p>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                            Consider exporting your data first. This action cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelPurge">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-warning" wire:click="proceedToPurgeStep2"
                            data-testid="purge-step1-continue">
                            Continue
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Purge Step 2 Modal (Double Confirmation) --}}
    @if ($showPurgeStep2)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="purgeStep2Title"
            aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="purgeStep2Title">
                            <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                            Confirm Purge - Step 2
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cancelPurge"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-danger fw-bold">
                            This will permanently delete ALL local plans. This cannot be undone!
                        </p>
                        <div class="mb-3">
                            <label for="purgeConfirmInput" class="form-label">
                                Type <strong>DELETE</strong> to confirm:
                            </label>
                            <input type="text" class="form-control" id="purgeConfirmInput"
                                wire:model="purgeConfirmText" placeholder="Type DELETE" autocomplete="off"
                                data-testid="purge-confirm-input">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelPurge">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="executePurge"
                            wire:loading.attr="disabled" data-testid="confirm-purge-all">
                            <span wire:loading.remove wire:target="executePurge">
                                <i class="bi bi-trash me-1" aria-hidden="true"></i>
                                Purge All Data
                            </span>
                            <span wire:loading wire:target="executePurge">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Purging...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Bulk Convert Modal (Req 56B.6) --}}
    @if ($showBulkConvertModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="bulkConvertTitle"
            aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="bulkConvertTitle">
                            <i class="bi bi-cloud-upload me-2" aria-hidden="true"></i>
                            Convert All to Account
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cancelBulkConvert"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            Convert <strong x-text="runCount">0</strong> local plan(s) to your account?
                        </p>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                            Plans will be synced across your devices and the local copies will be removed.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelBulkConvert">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-success" wire:click="executeBulkConvert"
                            wire:loading.attr="disabled" data-testid="confirm-convert-all">
                            <span wire:loading.remove wire:target="executeBulkConvert">
                                <i class="bi bi-cloud-upload me-1" aria-hidden="true"></i>
                                Convert All
                            </span>
                            <span wire:loading wire:target="executeBulkConvert">
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

    {{-- Convert Results Modal --}}
    @if ($showConvertResultsModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="convertResultsTitle"
            aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div
                        class="modal-header {{ $convertResults['failed'] > 0 ? 'bg-warning' : 'bg-success text-white' }}">
                        <h5 class="modal-title" id="convertResultsTitle">
                            <i class="bi bi-clipboard-check me-2" aria-hidden="true"></i>
                            Conversion Results
                        </h5>
                        <button type="button"
                            class="btn-close {{ $convertResults['failed'] > 0 ? '' : 'btn-close-white' }}"
                            wire:click="closeConvertResults" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="h3 text-success mb-0">{{ $convertResults['converted'] }}</div>
                                <small class="text-muted">Converted</small>
                            </div>
                            <div class="col-6">
                                <div class="h3 text-danger mb-0">{{ $convertResults['failed'] }}</div>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                        @if (count($convertResults['errors']) > 0)
                            <div class="alert alert-danger">
                                <strong>Errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($convertResults['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="closeConvertResults"
                            data-testid="close-convert-results">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Import Modal (Req 56B.4) --}}
    @if ($showImportModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="importTitle"
            aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title" id="importTitle">
                            <i class="bi bi-upload me-2" aria-hidden="true"></i>
                            Import Local Data
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cancelImport"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- File Input --}}
                        <div class="mb-4">
                            <label for="importFile" class="form-label fw-bold">Select JSON File</label>
                            <input type="file" class="form-control" id="importFile"
                                accept=".json,application/json" @change="handleFileSelect($event)"
                                data-testid="import-file-input">
                            <div class="form-text">
                                Select a JSON file exported from this application.
                            </div>
                        </div>

                        {{-- Import Preview --}}
                        <template x-if="importPreview.count > 0">
                            <div class="mb-4">
                                <h6 class="fw-bold">Preview</h6>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                                    Found <strong x-text="importPreview.count">0</strong> plan(s) to import
                                    <template x-if="importPreview.conflicts > 0">
                                        <span class="text-warning">
                                            (<strong x-text="importPreview.conflicts">0</strong> conflicts)
                                        </span>
                                    </template>
                                </div>
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Plan Name</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="plan in importPreview.plans" :key="plan.uuid">
                                                <tr>
                                                    <td x-text="plan.title"></td>
                                                    <td>
                                                        <template x-if="plan.isConflict">
                                                            <span class="badge bg-warning">Conflict</span>
                                                        </template>
                                                        <template x-if="!plan.isConflict">
                                                            <span class="badge bg-success">New</span>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>

                        {{-- Conflict Resolution --}}
                        <template x-if="importPreview.conflicts > 0">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Conflict Resolution</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="conflictResolution"
                                        id="conflictSkip" value="skip" wire:model="conflictResolution"
                                        data-testid="conflict-skip">
                                    <label class="form-check-label" for="conflictSkip">
                                        <strong>Skip</strong> - Don't import conflicting plans
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="conflictResolution"
                                        id="conflictOverwrite" value="overwrite" wire:model="conflictResolution"
                                        data-testid="conflict-overwrite">
                                    <label class="form-check-label" for="conflictOverwrite">
                                        <strong>Overwrite</strong> - Replace existing plans with imported data
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="conflictResolution"
                                        id="conflictCopy" value="copy" wire:model="conflictResolution"
                                        data-testid="conflict-copy">
                                    <label class="form-check-label" for="conflictCopy">
                                        <strong>Import as Copy</strong> - Create new plans with "(Copy)" suffix
                                    </label>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelImport">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" @click="executeImport()"
                            :disabled="!importPreview.count || importPreview.count === 0"
                            data-testid="confirm-import">
                            <i class="bi bi-upload me-1" aria-hidden="true"></i>
                            Import <span x-text="importPreview.count || 0">0</span> Plan(s)
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
