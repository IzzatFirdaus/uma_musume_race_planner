{{-- Local Data Manager Page - Livewire Component View --}}
{{-- Implements FR-9D.1 through FR-9D.6 --}}
<div class="container py-4" data-testid="local-data-manager">
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
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
            Back to Dashboard
        </a>
    </div>

    {{-- Stats Card --}}
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-md-4">
                    <div class="h3 mb-1 text-warning" data-testid="local-plans-count">
                        {{ $this->totalLocalPlans }}
                    </div>
                    <small class="text-muted">Local Plans</small>
                </div>
                <div class="col-md-4">
                    <div class="h3 mb-1 text-info" data-testid="storage-used">
                        {{ $this->approximateStorageUsed }}
                    </div>
                    <small class="text-muted">Approximate Storage</small>
                </div>
                <div class="col-md-4">
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
                {{-- Export All (FR-9D.3) --}}
                <button type="button" class="btn btn-outline-primary" wire:click="exportAllAsJson"
                    wire:loading.attr="disabled" data-testid="export-all-json"
                    {{ $this->totalLocalPlans === 0 ? 'disabled' : '' }}>
                    <i class="bi bi-download me-1" aria-hidden="true"></i>
                    Export All as JSON
                </button>

                {{-- Convert All to Account (FR-9D.6) --}}
                <button type="button" class="btn btn-outline-success" wire:click="showBulkConvert"
                    wire:loading.attr="disabled" data-testid="convert-all-account"
                    {{ $this->totalLocalPlans === 0 ? 'disabled' : '' }}
                    title="{{ !$this->isAuthenticated ? 'Sign in required' : 'Convert all local plans to your account' }}">
                    <i class="bi bi-cloud-upload me-1" aria-hidden="true"></i>
                    Convert All to Account
                    @if (!$this->isAuthenticated)
                        <i class="bi bi-lock ms-1" aria-hidden="true"></i>
                    @endif
                </button>

                {{-- Delete All (FR-9D.5) --}}
                <button type="button" class="btn btn-outline-danger" wire:click="confirmDeleteAll"
                    wire:loading.attr="disabled" data-testid="delete-all-local"
                    {{ $this->totalLocalPlans === 0 ? 'disabled' : '' }}>
                    <i class="bi bi-trash me-1" aria-hidden="true"></i>
                    Delete All Local Data
                </button>
            </div>

            @if (!$this->isAuthenticated)
                <div class="alert alert-info mt-3 mb-0" role="alert">
                    <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                    <a href="{{ route('login') ?? '#' }}" class="alert-link">Sign in</a> to convert local plans to your
                    account and sync across devices.
                </div>
            @endif
        </div>
    </div>

    {{-- Local Plans List (FR-9D.2) --}}
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-list-ul me-2" aria-hidden="true"></i>
                Local Plans
            </span>
            {{-- Search --}}
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="bi bi-search" aria-hidden="true"></i>
                </span>
                <input type="search" class="form-control border-start-0" placeholder="Search plans..."
                    wire:model.live.debounce.300ms="searchQuery" aria-label="Search local plans"
                    data-testid="local-plans-search">
            </div>
        </div>
        <div class="card-body p-0">
            @if ($this->localPlans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" data-testid="local-plans-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Plan Name</th>
                                <th scope="col">Character</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last Updated</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->localPlans as $plan)
                                <tr wire:key="local-plan-{{ $plan->id }}"
                                    data-testid="local-plan-row-{{ $plan->id }}">
                                    <td>
                                        <div class="fw-semibold">{{ $plan->plan_title ?? $plan->name }}</div>
                                        @if ($plan->local_uuid)
                                            <small
                                                class="text-muted font-monospace">{{ Str::limit($plan->local_uuid, 8) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $plan->name }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $plan->status === 'Active' ? 'bg-success' : ($plan->status === 'Finished' ? 'bg-info' : 'bg-secondary') }}">
                                            {{ $plan->status ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        <time datetime="{{ $plan->updated_at?->toIso8601String() }}">
                                            {{ $plan->updated_at?->diffForHumans() ?? 'N/A' }}
                                        </time>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('plans.show', $plan->id) }}"
                                                class="btn btn-outline-primary" title="View plan"
                                                data-testid="view-plan-{{ $plan->id }}">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                                <span class="visually-hidden">View</span>
                                            </a>
                                            <button type="button" class="btn btn-outline-success"
                                                wire:click="convertToAccount({{ $plan->id }})"
                                                wire:loading.attr="disabled"
                                                title="{{ $this->isAuthenticated ? 'Convert to account' : 'Sign in required' }}"
                                                data-testid="convert-plan-{{ $plan->id }}"
                                                {{ !$this->isAuthenticated ? 'disabled' : '' }}>
                                                <i class="bi bi-cloud-upload" aria-hidden="true"></i>
                                                <span class="visually-hidden">Convert to Account</span>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger"
                                                wire:click="deletePlan({{ $plan->id }})"
                                                wire:loading.attr="disabled"
                                                wire:confirm="Are you sure you want to delete this plan?"
                                                title="Delete plan" data-testid="delete-plan-{{ $plan->id }}">
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                                <span class="visually-hidden">Delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-transparent">
                    {{ $this->localPlans->links() }}
                </div>
            @else
                <div class="text-center py-5" data-testid="local-plans-empty">
                    <i class="bi bi-inbox display-4 text-muted mb-3 d-block" aria-hidden="true"></i>
                    <p class="text-muted mb-0">
                        @if ($searchQuery)
                            No local plans match your search
                        @else
                            No local plans found
                        @endif
                    </p>
                    <small class="text-muted">
                        Plans created without signing in will appear here
                    </small>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete All Confirmation Modal --}}
    @if ($showDeleteConfirmation)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmTitle"
            aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteConfirmTitle">
                            <i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>
                            Confirm Delete All
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cancelDeleteAll"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">
                            Are you sure you want to delete <strong>all {{ $this->totalLocalPlans }} local
                                plan(s)</strong>?
                        </p>
                        <p class="text-danger mt-2 mb-0">
                            <i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>
                            This action cannot be undone. Consider exporting your data first.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDeleteAll">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteAllLocalRuns"
                            wire:loading.attr="disabled" data-testid="confirm-delete-all">
                            <span wire:loading.remove wire:target="deleteAllLocalRuns">
                                <i class="bi bi-trash me-1" aria-hidden="true"></i>
                                Delete All
                            </span>
                            <span wire:loading wire:target="deleteAllLocalRuns">
                                <span class="spinner-border spinner-border-sm me-1" role="status"
                                    aria-hidden="true"></span>
                                Deleting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Bulk Convert Modal --}}
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
                        <p class="mb-0">
                            Convert <strong>all {{ $this->totalLocalPlans }} local plan(s)</strong> to your account?
                        </p>
                        <p class="text-muted mt-2 mb-0">
                            <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                            Plans will be synced across your devices and the local copies will be removed.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelBulkConvert">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-success" wire:click="convertAllToAccount"
                            wire:loading.attr="disabled" data-testid="confirm-convert-all">
                            <span wire:loading.remove wire:target="convertAllToAccount">
                                <i class="bi bi-cloud-upload me-1" aria-hidden="true"></i>
                                Convert All
                            </span>
                            <span wire:loading wire:target="convertAllToAccount">
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

{{-- JavaScript for JSON download --}}
@script
    <script>
        $wire.on('download-json', (data) => {
            const blob = new Blob([data[0].data], {
                type: 'application/json'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = data[0].filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    </script>
@endscript
