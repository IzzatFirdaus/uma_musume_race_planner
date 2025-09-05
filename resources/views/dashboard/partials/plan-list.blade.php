{{--
    Plan list card for dashboard.
    If plan thumbnails or status icons reference uploaded images,
    ensure you use asset() helper for any dynamic image src.
--}}
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-card-checklist me-2"></i>
            Your Race Plans
        </h5>
        <button class="btn btn-sm btn-uma" id="createPlanBtn">
            <i class="bi bi-plus-circle me-1"></i> Create New
        </button>
    </div>

    <div class="card-body p-0">
        <div class="plan-filters p-3 border-bottom">
            <div class="btn-group" role="group" id="plan-filter-buttons">
                <button type="button" class="btn btn-sm btn-outline-secondary active" data-filter="all">All</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="Active">Active</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="Planning">Planning</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-filter="Finished">Finished</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-vcenter mb-0" id="planTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;"></th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Next Race</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                {{-- Table body populated by JS.
                     Ensure JS uses asset() for any image src for thumbnails/status. --}}
                <tbody id="planListBody"></tbody>
            </table>
        </div>
    </div>
</div>
