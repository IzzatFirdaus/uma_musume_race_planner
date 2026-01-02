@if(!$isEditMode)
    {{-- View mode disables form fields via readonly/disabled attributes only. Styling handled by style.css. --}}
@endif

<div class="container py-4 {{ !$isEditMode ? 'view-mode' : '' }}">
    {{-- Navigation Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            {{-- Removed breadcrumb dashboard link, keeping only the Back to Dashboard button --}}
        </div>
        <div class="d-flex gap-2">
            @if(!$isEditMode)
                {{-- Force a full navigation (avoid SPA interception) so Playwright sees a load event for URL waits. --}}
                <a href="{{ route('plans.edit', ['planId' => $planId]) }}" class="btn btn-primary" onclick="window.location.href=this.href;">
                    <i class="bi bi-pencil"></i> Edit Plan
                </a>
            @else
                <a href="{{ route('plans.view', ['planId' => $planId]) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-eye"></i> View Only
                </a>
            @endif
            {{-- Use base URL for Back to Dashboard so tests that expect '/' will pass consistently. --}}
            <a href="/" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card mb-4 plan-list-theme">
    <div class="card-header plan-list-header-theme d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-{{ $isEditMode ? 'pencil-square' : 'eye' }} me-2"></i>
                @if($plan_title)
                    {{ $isEditMode ? 'Edit' : 'View' }} Plan: {{ $plan_title }}
                @else
                    {{ $isEditMode ? 'Edit' : 'View' }} Plan Details
                @endif
            </h5>
            @if($isEditMode)
                <span class="badge bg-warning">Edit Mode</span>
            @else
                <span class="badge bg-info">View Mode</span>
            @endif
        </div>

        <div class="loading-overlay" wire:loading>
            <div class="spinner-border dashboard-btn-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        @if($isEditMode)
            <form id="planDetailsFormPage" enctype="multipart/form-data" wire:submit.prevent="save">
                @method('PUT')
                @csrf
                <input type="hidden" wire:model.defer="planId" name="planId">
        @endif

            <div class="card-body">
                    <div wire:loading.flex wire:transition class="justify-content-center align-items-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div wire:loading.remove>
                {{-- Form Tabs: use the reusable Livewire component which renders the same IDs when passed an id_suffix of "_page" --}}
                <div>
                    {{-- Pass the loaded plan data as props to the FormTabs child so it mounts with full state server-side. --}}
                    <livewire:form-tabs
                        :id_suffix="'_page'"
                        :planId="$planId"
                        :plan_title="$plan_title"
                        :name="$name"
                        :career_stage="$career_stage"
                        :class="$class"
                        :race_name="$race_name"
                        :turn_before="$turn_before"
                        :goal="$goal"
                        :strategy_id="$strategy_id"
                        :mood_id="$mood_id"
                        :condition_id="$condition_id"
                        :energy="$energy"
                        :race_day="$race_day"
                        :acquire_skill="$acquire_skill"
                        :total_available_skill_points="$total_available_skill_points"
                        :status="$status"
                        :time_of_day="$time_of_day"
                        :month="$month"
                        :source="$source"
                        :growth_rate_speed="$growth_rate_speed"
                        :growth_rate_stamina="$growth_rate_stamina"
                        :growth_rate_power="$growth_rate_power"
                        :growth_rate_guts="$growth_rate_guts"
                        :growth_rate_wit="$growth_rate_wit"
                        :skills="$skills"
                        :predictions="$racePredictions"
                        :goals="$goals"
                        :planAttributes="$planAttributes"
                        :terrainGrades="$terrainGrades"
                        :distanceGrades="$distanceGrades"
                        :styleGrades="$styleGrades"
                        :isEditMode="$isEditMode"
                    />
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2 rounded-bottom-4">
                <button type="button" class="btn-uma" id="downloadTxtPage">
                    <i class="bi bi-file-earmark-text"></i> Download Plan as TXT
                </button>
                <button type="button" class="dashboard-btn-primary" id="exportPlanBtnPage">
                    <i class="bi bi-clipboard"></i> Copy Plan to Clipboard
                </button>
                @if($isEditMode)
                    <button type="submit" class="dashboard-btn-primary">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                @endif
            </div>
        @if($isEditMode)
        </form>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('plan-saved', (event) => {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Saved!',
                        text: event[0]?.message || 'Plan has been saved successfully.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
            Livewire.on('show-error', (event) => {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Error!',
                        text: event[0]?.message || 'An error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>
    @endpush
</div>
