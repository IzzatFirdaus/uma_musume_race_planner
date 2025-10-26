
{{-- Dashboard Header Banner - Livewire Component View --}}
<div class="card shadow-sm mb-4 rounded-4 border-0 header-banner-theme position-relative overflow-hidden">
    {{-- Background decoration --}}
    <div class="position-absolute top-0 end-0 opacity-25 d-none d-lg-block">
        <i class="bi bi-trophy" style="font-size: 6rem;" aria-hidden="true"></i>
    </div>

    <div class="card-body py-4 position-relative">
        <div class="row align-items-center">
            <div class="col-md-2 col-3 text-center">
                <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_256.png') }}"
                     alt="Uma Musume Planner Logo"
                     class="img-fluid rounded-circle shadow"
                     style="max-width: 80px; aspect-ratio: 1;">
            </div>
            <div class="col-md-7 col-9">
                <h1 class="h3 mb-2 fw-bold">Welcome to Uma Musume Planner</h1>
                <p class="mb-0 opacity-90">
                    Plan, track, and optimize your Umamusume's racing career
                </p>
                @if($userName ?? null)
                    <small class="text-muted mt-1 d-block">Hello, {{ $userName }}!</small>
                @endif
            </div>
            <div class="col-md-3 d-none d-md-block text-end">
                @if(isset($totalPlans))
                    <div class="text-center">
                        <div class="h4 mb-1 fw-bold">{{ $totalPlans }}</div>
                        <small class="text-muted">Total Plans</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
