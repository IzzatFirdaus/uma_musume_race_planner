
<div>
    {{-- The best athlete wants his opponent at his best. --}}
    {{--
        Plan list card for dashboard.
        If plan thumbnails or status icons reference uploaded images,
        ensure you use asset() helper for any dynamic image src.
    --}}
    <div class="card shadow-sm mb-4 border-0 rounded-4 plan-list-theme">
        <div class="card-header d-flex justify-content-between align-items-center rounded-top-4 plan-list-header-theme">
            <h5 class="mb-0">
                <i class="bi bi-card-checklist me-2"></i>
                Your Race Plans
            </h5>
            <button class="btn btn-sm dashboard-btn-primary" id="createPlanBtn">
                <i class="bi bi-plus-circle me-1"></i> Create New
            </button>
        </div>

        <div class="card-body p-0 plan-list-body-theme">
            <div class="plan-filters p-3 border-bottom">
                <div class="btn-group" role="group" id="plan-filter-buttons">
                    <button type="button" class="btn btn-sm dashboard-btn-outline active" data-filter="all">All</button>
                    <button type="button" class="btn btn-sm dashboard-btn-outline" data-filter="Active">Active</button>
                    <button type="button" class="btn btn-sm dashboard-btn-outline" data-filter="Planning">Planning</button>
                    <button type="button" class="btn btn-sm dashboard-btn-outline" data-filter="Finished">Finished</button>
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
</div>
