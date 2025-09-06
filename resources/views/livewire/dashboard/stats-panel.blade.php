
<div>
    {{-- The Master doesn't talk, he acts. --}}
    {{--
        Quick stats widget. No image assets used here, but
        if you later add icons/thumbnails, use asset() in img src.
    --}}
    <div class="card mb-4 shadow-sm border-0 rounded-4 stats-panel-theme">
        <div class="card-header fw-bold rounded-top-4 stats-panel-header-theme">Quick Stats</div>
        <div class="card-body stats-panel-body-theme">
            <div class="d-flex justify-content-around text-center flex-wrap gap-3">
                <div>
                    <div id="statsPlans" class="fs-1 fw-bold quick-stats-number">{{ $stats['total_plans'] ?? 0 }}</div>
                    <div class="quick-stats-label">Plans</div>
                </div>
                <div>
                    <div id="statsActive" class="fs-1 fw-bold quick-stats-number text-success">{{ $stats['active_plans'] ?? 0 }}</div>
                    <div class="quick-stats-label">Active</div>
                </div>
                <div>
                    <div id="statsFinished" class="fs-1 fw-bold quick-stats-number text-primary">{{ $stats['finished_plans'] ?? 0 }}</div>
                    <div class="quick-stats-label">Finished</div>
                </div>
            </div>
        </div>
    </div>
</div>
