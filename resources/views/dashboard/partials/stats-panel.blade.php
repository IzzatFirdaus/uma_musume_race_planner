{{--
    Quick stats widget. No image assets used here, but
    if you later add icons/thumbnails, use asset() in img src.
--}}
<div class="card mb-4 shadow-sm">
  <div class="card-header fw-bold">Quick Stats</div>
  <div class="card-body">
    <div class="d-flex justify-content-around text-center flex-wrap gap-3">

      <div>
        <div id="statsPlans" class="fs-1 fw-bold quick-stats-number">
          {{ $stats['total_plans'] ?? 0 }}
        </div>
        <div class="text-muted">Plans</div>
      </div>

      <div>
        <div id="statsActive" class="fs-1 fw-bold quick-stats-number text-success">
          {{ $stats['active_plans'] ?? 0 }}
        </div>
        <div class="text-muted">Active</div>
      </div>

      <div>
        <div id="statsFinished" class="fs-1 fw-bold quick-stats-number text-primary">
          {{ $stats['finished_plans'] ?? 0 }}
        </div>
        <div class="text-muted">Finished</div>
      </div>

    </div>
  </div>
</div>
