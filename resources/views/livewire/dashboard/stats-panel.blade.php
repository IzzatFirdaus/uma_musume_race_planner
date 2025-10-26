
{{-- Dashboard Quick Stats Panel - Livewire Component View --}}
<div class="card mb-4 shadow-sm border-0 rounded-4 stats-panel-theme">
    <div class="card-header d-flex align-items-center fw-bold rounded-top-4 stats-panel-header-theme">
        <i class="bi bi-bar-chart me-2" aria-hidden="true"></i>
        Quick Stats
    </div>
    <div class="card-body stats-panel-body-theme">
        <div class="row g-3 text-center">
            <div class="col-md-4 col-sm-6">
                <div class="stat-item">
                    <div id="statsPlans" class="display-4 fw-bold quick-stats-number text-primary">
                        {{ $stats['total_plans'] ?? 0 }}
                    </div>
                    <div class="quick-stats-label text-muted small">Total Plans</div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="stat-item">
                    <div id="statsActive" class="display-4 fw-bold quick-stats-number text-success">
                        {{ $stats['active_plans'] ?? 0 }}
                    </div>
                    <div class="quick-stats-label text-muted small">Active</div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="stat-item">
                    <div id="statsFinished" class="display-4 fw-bold quick-stats-number text-info">
                        {{ $stats['finished_plans'] ?? 0 }}
                    </div>
                    <div class="quick-stats-label text-muted small">Finished</div>
                </div>
            </div>
        </div>

        @if(isset($stats['last_updated']))
            <div class="text-center mt-3 pt-3 border-top">
                <small class="text-muted">
                    <i class="bi bi-clock me-1" aria-hidden="true"></i>
                    Last updated: {{ $stats['last_updated'] }}
                </small>
            </div>
        @endif
    </div>
</div>
