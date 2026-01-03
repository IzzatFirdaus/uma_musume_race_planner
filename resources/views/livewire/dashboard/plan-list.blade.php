<div id="PlanListRoot">
    <style>
        /* Make the plans table wrap long content and avoid horizontal scroll when text is resized */
        #PlanListRoot .table {
            table-layout: fixed;
            width: 100%;
        }

        #PlanListRoot td,
        #PlanListRoot th {
            white-space: normal !important;
            word-break: break-word;
        }

        /* Ensure images don't cause overflow */
        #PlanListRoot img {
            max-width: 100%;
            height: auto;
        }
    </style>
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
    <div id="planListCard" class="card shadow-sm mb-4 border-0 rounded-4 plan-list-theme">
        <div class="card-header d-flex justify-content-between align-items-center rounded-top-4 plan-list-header-theme">
            <h5 class="mb-0">
                <i class="bi bi-card-checklist me-2"></i>
                Your Race Plans
            </h5>
            <button class="btn btn-sm dashboard-btn-primary" id="createPlanBtn" type="button">
                <i class="bi bi-plus-circle me-1"></i> Create New
            </button>
        </div>

        <div class="card-body p-0 plan-list-body-theme">
            <div class="plan-filters p-3 border-bottom">
                <div class="btn-group" role="group">
                    <button type="button" wire:click="setFilter('all')"
                        class="btn btn-sm dashboard-btn-outline {{ $currentFilter === 'all' ? 'active' : '' }}">
                        All ({{ $counts['total'] }})
                    </button>
                    <button type="button" wire:click="setFilter('Active')"
                        class="btn btn-sm dashboard-btn-outline {{ $currentFilter === 'Active' ? 'active' : '' }}">
                        Active ({{ $counts['active'] }})
                    </button>
                    <button type="button" wire:click="setFilter('Planning')"
                        class="btn btn-sm dashboard-btn-outline {{ $currentFilter === 'Planning' ? 'active' : '' }}">
                        Planning ({{ $counts['planning'] }})
                    </button>
                    <button type="button" wire:click="setFilter('Finished')"
                        class="btn btn-sm dashboard-btn-outline {{ $currentFilter === 'Finished' ? 'active' : '' }}">
                        Finished ({{ $counts['finished'] }})
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <div wire:loading.flex wire:transition class="justify-content-center align-items-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <table class="table table-hover table-vcenter mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Character</th>
                            <th>Plan Details</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;">Next Race</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="plan-list-body">
                        @forelse($plans as $plan)
                            <tr wire:key="plan-{{ $plan->id }}">
                                <td>
                                    @if ($plan->trainee_image_path)
                                        <div class="position-relative">
                                            <img src="{{ asset($plan->trainee_image_path) }}"
                                                alt="{{ $plan->name }}"
                                                class="rounded-3 border border-2 border-light shadow-sm"
                                                style="width: 64px; height: 64px; object-fit: cover; object-position: top;"
                                                title="{{ $plan->name }}">
                                            <!-- Character quality/rarity indicator -->
                                            <div class="position-absolute bottom-0 end-0 translate-middle">
                                                <span class="badge bg-warning text-dark rounded-circle"
                                                    style="width: 20px; height: 20px; font-size: 10px; line-height: 10px;"
                                                    title="Plan Status">
                                                    @if ($plan->status === 'Active')
                                                        ▶️
                                                    @elseif($plan->status === 'Planning')
                                                        📝
                                                    @elseif($plan->status === 'Finished')
                                                        ✅
                                                    @else
                                                        ❓
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="rounded-3 bg-secondary d-flex align-items-center justify-content-center border border-2 border-light shadow-sm"
                                            style="width: 64px; height: 64px;">
                                            <i class="bi bi-person text-white fs-4"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold text-primary mb-1 d-flex align-items-center gap-2">
                                            {{ $plan->name }}
                                            {{-- Storage Mode Badge (FR-7.8) --}}
                                            @if ($plan->storage_mode)
                                                <span
                                                    class="badge {{ $plan->storage_mode->value === 'local' ? 'bg-warning text-dark' : 'bg-primary text-white' }} rounded-pill"
                                                    style="font-size: 0.65rem;"
                                                    title="{{ $plan->storage_mode->value === 'local' ? 'Stored locally in browser' : 'Stored in your account' }}"
                                                    data-testid="storage-mode-badge-{{ $plan->id }}">
                                                    <i class="bi {{ $plan->storage_mode->value === 'local' ? 'bi-hdd' : 'bi-cloud' }}"
                                                        aria-hidden="true"></i>
                                                    {{ $plan->storage_mode->label() }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($plan->plan_title)
                                            <div class="text-muted small">{{ $plan->plan_title }}</div>
                                        @endif
                                        @if ($plan->turn_before)
                                            <div class="text-info small mt-1">
                                                <i class="bi bi-clock me-1"></i>Turn {{ $plan->turn_before }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge px-3 py-2
                                        @if ($plan->status === 'Active') bg-success
                                        @elseif($plan->status === 'Planning') bg-warning text-dark
                                        @elseif($plan->status === 'Finished') bg-primary
                                        @else bg-secondary @endif">
                                        {{ $plan->status }}
                                    </span>
                                </td>
                                <td>
                                    @if ($plan->race_name)
                                        <div class="fw-semibold">{{ $plan->race_name }}</div>
                                        @if ($plan->turn_before)
                                            <small class="text-muted">Turn {{ $plan->turn_before }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">No race scheduled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Plan actions">
                                        <a href="{{ route('plans.view', $plan->id) }}" data-id="{{ $plan->id }}"
                                            class="btn btn-outline-primary view-details-btn" title="View Details"
                                            data-testid="plan-view-{{ $plan->id }}">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                            <span class="visually-hidden">View {{ $plan->name }}</span>
                                        </a>
                                        <a href="{{ route('plans.edit', $plan->id) }}" data-id="{{ $plan->id }}"
                                            class="btn btn-outline-secondary edit-btn" title="Edit"
                                            data-testid="plan-edit-{{ $plan->id }}">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                            <span class="visually-hidden">Edit {{ $plan->name }}</span>
                                        </a>
                                        {{-- Convert to Account button (FR-7.9) - visible when authenticated and plan is local --}}
                                        @auth
                                            @if ($plan->storage_mode?->value === 'local')
                                                <button
                                                    wire:click="$dispatch('open-convert-modal', { planId: {{ $plan->id }} })"
                                                    class="btn btn-outline-success convert-btn" title="Convert to Account"
                                                    data-testid="plan-convert-{{ $plan->id }}">
                                                    <i class="bi bi-cloud-upload" aria-hidden="true"></i>
                                                    <span class="visually-hidden">Convert {{ $plan->name }} to
                                                        Account</span>
                                                </button>
                                            @endif
                                        @endauth
                                        <button wire:click="deletePlan({{ $plan->id }})"
                                            data-id="{{ $plan->id }}"
                                            wire:confirm="Are you sure you want to delete '{{ $plan->name }}'? This action cannot be undone!"
                                            class="btn btn-outline-danger delete-btn" title="Delete"
                                            data-testid="plan-delete-{{ $plan->id }}">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                            <span class="visually-hidden">Delete {{ $plan->name }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                                        <h4 class="mb-2">No plans yet</h4>
                                        <p class="mb-3 text-muted">You haven't created any race plans. Get started by
                                            creating your first plan.</p>
                                        <button class="btn btn-primary" id="emptyStateCreatePlanBtn" type="button">
                                            <i class="bi bi-plus-circle me-1"></i> Create Plan
                                        </button>
                                        @if ($currentFilter !== 'all')
                                            <div class="mt-2 text-muted">No plans found for status
                                                "{{ $currentFilter }}"</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 py-2">
                {{ $plans->links() }}
            </div>
        </div>
    </div>
</div>

@script
    <script>
        // Add SweetAlert2 for deletion confirmation
        document.addEventListener('livewire:init', () => {
            // Quick Create Plan modal open logic
            document.getElementById('createPlanBtn')?.addEventListener('click', function() {
                Livewire.emit('open-create-plan-modal');
            });
            document.getElementById('emptyStateCreatePlanBtn')?.addEventListener('click', function() {
                Livewire.emit('open-create-plan-modal');
            });
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

            // Show SweetAlert2 toast when plan is updated or actions occur
            Livewire.on('plan-updated', (event) => {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: event[0]?.message || 'Plan updated successfully.',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                }
                // Refresh the list after any plan update/create - scope to this component only
                // Emit to the server-side component to update filter and refresh list
                Livewire.emit('filterPlansByStatus', @js($currentFilter));
            });

            Livewire.on('refreshPlans', () => {
                Livewire.emit('filterPlansByStatus', @js($currentFilter));
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
                                    // Use Livewire component instance to call method
                                    // Emit an event so the Livewire component can handle deletion server-side
                                    Livewire.emit('deletePlan', parseInt(match[1]));
                                }
                            }
                        });
                    }
                }
            }, true);
        });
    </script>
@endscript
