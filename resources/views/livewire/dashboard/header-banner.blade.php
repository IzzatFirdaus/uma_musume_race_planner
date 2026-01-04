{{-- Dashboard Header Banner - Livewire Component View --}}
{{-- Implements FR-7.10: Local Data management entry point --}}
{{-- Implements Requirements 1.1, 1.5: Header banner with app branding and Create Plan button --}}
<div class="card shadow-sm mb-4 rounded-4 border-0 header-banner-theme position-relative overflow-hidden"
    data-testid="header-banner">
    {{-- Background decoration --}}
    <div class="position-absolute top-0 end-0 opacity-25 d-none d-lg-block">
        <i class="bi bi-trophy" style="font-size: 6rem;" aria-hidden="true"></i>
    </div>

    <div class="card-body py-4 position-relative">
        <div class="row align-items-center">
            <div class="col-md-2 col-3 text-center">
                <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_256.png') }}"
                    alt="Uma Musume Planner Logo" class="img-fluid rounded-circle shadow"
                    style="max-width: 80px; aspect-ratio: 1;">
            </div>
            <div class="col-md-4 col-9">
                <h1 class="h3 mb-2 fw-bold" data-testid="header-banner-title">Welcome to Uma Musume Planner</h1>
                <p class="mb-0 opacity-90">
                    Plan, track, and optimize your Umamusume's racing career
                </p>
                @if ($this->userName)
                    <small class="text-muted mt-1 d-block" data-testid="header-banner-greeting">Hello,
                        {{ $this->userName }}!</small>
                @endif
            </div>
            <div class="col-md-6 d-none d-md-flex justify-content-end align-items-center gap-3">
                {{-- Total Plans stat --}}
                <div class="text-center me-3" data-testid="header-banner-total-plans">
                    <div class="h4 mb-1 fw-bold">{{ $this->totalPlans }}</div>
                    <small class="text-muted">Total Plans</small>
                </div>

                {{-- Create Plan Button (Requirement 1.5) --}}
                <button type="button" class="btn btn-primary d-flex align-items-center gap-2" id="headerCreatePlanBtn"
                    data-testid="header-create-plan-btn" title="Create a new race plan">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    <span>Create Plan</span>
                </button>

                {{-- Quick Actions --}}
                <div class="d-flex flex-column gap-2">
                    {{-- Local Data Management Entry Point (FR-7.10) --}}
                    <a href="{{ route('local-data') ?? '#' }}"
                        class="btn btn-outline-warning btn-sm d-flex align-items-center gap-2"
                        data-testid="local-data-link" title="Manage your locally stored plans">
                        <i class="bi bi-hdd" aria-hidden="true"></i>
                        <span>Local Data</span>
                        @if ($this->localPlansCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill">{{ $this->localPlansCount }}</span>
                        @endif
                    </a>

                    @if (!$this->isAuthenticated)
                        <a href="{{ route('login') ?? '#' }}"
                            class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2"
                            data-testid="login-link">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                            <span>Sign In</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mobile Quick Actions --}}
        <div class="d-md-none mt-3 pt-3 border-top">
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                {{-- Create Plan Button for Mobile (Requirement 1.5) --}}
                <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2"
                    id="headerCreatePlanBtnMobile" data-testid="header-create-plan-btn-mobile"
                    title="Create a new race plan">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    <span>Create Plan</span>
                </button>

                <a href="{{ route('local-data') ?? '#' }}"
                    class="btn btn-outline-warning btn-sm d-flex align-items-center gap-2"
                    data-testid="local-data-link-mobile">
                    <i class="bi bi-hdd" aria-hidden="true"></i>
                    <span>Local Data</span>
                    @if ($this->localPlansCount > 0)
                        <span class="badge bg-warning text-dark rounded-pill">{{ $this->localPlansCount }}</span>
                    @endif
                </a>
                @if (!$this->isAuthenticated)
                    <a href="{{ route('login') ?? '#' }}"
                        class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2"
                        data-testid="login-link-mobile">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                        <span>Sign In</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@script
    <script>
        // Wire up Create Plan buttons to open the quick create modal
        document.addEventListener('DOMContentLoaded', function() {
            const createBtns = document.querySelectorAll('#headerCreatePlanBtn, #headerCreatePlanBtnMobile');
            createBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    Livewire.dispatch('open-create-plan-modal');
                });
            });
        });
    </script>
@endscript
