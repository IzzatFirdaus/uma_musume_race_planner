
<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Plan list card for dashboard --}}
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
                <div class="btn-group" role="group">
                    <button type="button" wire:click="setFilter('all')"
                            class="btn btn-sm dashboard-btn-outline {{ $currentFilter === 'all' ? 'active' : '' }}">
                        All ({{ App\Models\Plan::count() }})
                    </button>
                    <button type="button" wire:click="setFilter('Active')"
                            class="btn btn-sm dashboard-btn-outline {{ $currentFilter === 'Active' ? 'active' : '' }}">
                        Active ({{ App\Models\Plan::where('status', 'Active')->count() }})
                    </button>
                    <button type="button" wire:click="setFilter('Planning')"
                            class="btn btn-sm dashboard-btn-outline {{ $currentFilter === 'Planning' ? 'active' : '' }}">
                        Planning ({{ App\Models\Plan::where('status', 'Planning')->count() }})
                    </button>
                    <button type="button" wire:click="setFilter('Finished')"
                            class="btn btn-sm dashboard-btn-outline {{ $currentFilter === 'Finished' ? 'active' : '' }}">
                        Finished ({{ App\Models\Plan::where('status', 'Finished')->count() }})
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-vcenter mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;"></th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Next Race</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="plan-list-body">
                        @forelse($plans as $plan)
                            <tr>
                                <td>
                                    @if($plan->trainee_image_path)
                                        <img src="{{ asset($plan->trainee_image_path) }}"
                                             alt="{{ $plan->name }}"
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px;">
                                            <i class="bi bi-person text-white"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $plan->name }}</strong>
                                        @if($plan->plan_title)
                                            <br><small class="text-muted">{{ $plan->plan_title }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($plan->status === 'Active') bg-success
                                        @elseif($plan->status === 'Planning') bg-warning text-dark
                                        @elseif($plan->status === 'Finished') bg-primary
                                        @else bg-secondary
                                        @endif">
                                        {{ $plan->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($plan->race_name)
                                        {{ $plan->race_name }}
                                        @if($plan->turn_before)
                                            <br><small class="text-muted">Turn {{ $plan->turn_before }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">No race scheduled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button wire:click="viewPlan({{ $plan->id }})"
                                                data-id="{{ $plan->id }}"
                                                class="btn btn-outline-primary view-inline-btn"
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button wire:click="editPlan({{ $plan->id }})"
                                                data-id="{{ $plan->id }}"
                                                class="btn btn-outline-secondary edit-btn"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="deletePlan({{ $plan->id }})"
                                                data-id="{{ $plan->id }}"
                                                wire:confirm="Are you sure you want to delete '{{ $plan->name }}'? This action cannot be undone!"
                                                class="btn btn-outline-danger delete-btn"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No plans found
                                        @if($currentFilter !== 'all')
                                            for status "{{ $currentFilter }}"
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@script
<script>
// Add SweetAlert2 for deletion confirmation
document.addEventListener('livewire:init', () => {
    Livewire.on('plan-deleted', (event) => {
        if (window.Swal) {
            Swal.fire({
                title: 'Deleted!',
                text: event[0].message || 'Plan has been deleted successfully.',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        }
    });

    Livewire.on('plan-error', (event) => {
        if (window.Swal) {
            Swal.fire({
                title: 'Error!',
                text: event[0].message || 'An error occurred.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});

// Replace the default Livewire confirm dialog with SweetAlert2
    document.addEventListener('DOMContentLoaded', function() {
    // Override delete button clicks to use SweetAlert2
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('button[wire\\:confirm]');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();

            const confirmText = btn.getAttribute('wire:confirm');
            const wireClick = btn.getAttribute('wire:click');

            if (window.Swal && confirmText && wireClick) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Extract plan ID from wire:click attribute
                        const match = wireClick.match(/deletePlan\((\d+)\)/);
                        if (match) {
                            @this.call('deletePlan', parseInt(match[1]));
                        }
                    }
                });
            }
        }
    }, true);
});
</script>
@endscript
