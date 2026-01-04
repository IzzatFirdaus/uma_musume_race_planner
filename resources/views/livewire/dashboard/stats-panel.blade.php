{{-- Dashboard Quick Stats Panel - Livewire Component View --}}
{{-- Implements FR-7.11: Dashboard stats panel shows counts by storage mode --}}
{{-- Implements Requirements 1.2: Stats panel with aggregate counts --}}
<div class="card mb-4 shadow-sm border-0 rounded-4 stats-panel-theme" data-testid="stats-panel">
    <div class="card-header d-flex align-items-center fw-bold rounded-top-4 stats-panel-header-theme">
        <i class="bi bi-bar-chart me-2" aria-hidden="true"></i>
        Quick Stats
    </div>
    <div class="card-body stats-panel-body-theme">
        {{-- Main stats row --}}
        <div class="row g-3 text-center">
            <div class="col-md-4 col-sm-6">
                <div class="stat-item" data-testid="stat-total-plans">
                    <div id="statsPlans" class="display-4 fw-bold quick-stats-number text-primary"
                        data-testid="stat-total-plans-value">
                        {{ $this->stats['total_plans'] ?? 0 }}
                    </div>
                    <div class="quick-stats-label text-muted small">Total Plans</div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="stat-item" data-testid="stat-active-plans">
                    <div id="statsActive" class="display-4 fw-bold quick-stats-number text-success"
                        data-testid="stat-active-plans-value">
                        {{ $this->stats['active_plans'] ?? 0 }}
                    </div>
                    <div class="quick-stats-label text-muted small">Active</div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="stat-item" data-testid="stat-finished-plans">
                    <div id="statsFinished" class="display-4 fw-bold quick-stats-number text-info"
                        data-testid="stat-finished-plans-value">
                        {{ $this->stats['finished_plans'] ?? 0 }}
                    </div>
                    <div class="quick-stats-label text-muted small">Finished</div>
                </div>
            </div>
        </div>

        {{-- Storage mode breakdown (FR-7.11) --}}
        <div class="mt-4 pt-3 border-top" data-testid="storage-mode-breakdown">
            <div class="d-flex justify-content-center align-items-center gap-4 flex-wrap">
                <div class="d-flex align-items-center gap-2" data-testid="stat-local-plans">
                    <span class="badge bg-warning text-dark d-flex align-items-center gap-1">
                        <i class="bi bi-hdd" aria-hidden="true"></i>
                        Local
                    </span>
                    <span class="fw-semibold"
                        data-testid="stat-local-plans-value">{{ $this->storageModeStats['local'] ?? 0 }}</span>
                    @if (($this->stats['total_plans'] ?? 0) > 0)
                        <small class="text-muted">({{ $this->storageModePercentages['local'] ?? 0 }}%)</small>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2" data-testid="stat-account-plans">
                    <span class="badge bg-primary text-white d-flex align-items-center gap-1">
                        <i class="bi bi-cloud" aria-hidden="true"></i>
                        Account
                    </span>
                    <span class="fw-semibold"
                        data-testid="stat-account-plans-value">{{ $this->storageModeStats['account'] ?? 0 }}</span>
                    @if (($this->stats['total_plans'] ?? 0) > 0)
                        <small class="text-muted">({{ $this->storageModePercentages['account'] ?? 0 }}%)</small>
                    @endif
                </div>
            </div>
        </div>

        @if (isset($this->stats['last_updated']))
            <div class="text-center mt-3 pt-3 border-top">
                <small class="text-muted">
                    <i class="bi bi-clock me-1" aria-hidden="true"></i>
                    Last updated: {{ $this->stats['last_updated'] }}
                </small>
            </div>
        @endif
    </div>
</div>
