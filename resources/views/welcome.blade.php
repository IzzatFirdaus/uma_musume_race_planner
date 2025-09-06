<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Uma Musume Race Planner</title>

    {{-- Third-party CSS from CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_32.ico') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_256.png') }}">

    @vite(['resources/css/app.css'])
    {{-- Livewire Styles (welcome page uses Livewire navbar/footer) --}}
    @livewireStyles

</head>
<body class="antialiased">

    {{-- Use Livewire navbar component for consistency --}}
    @livewire('layout.navbar')

    <div class="container d-flex flex-column justify-content-center min-vh-100 py-5">

        {{-- Main Hero Section --}}
        <div class="header-banner text-center p-4 p-md-5">
            <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_128.png') }}" alt="Uma Musume Race Planner Logo" class="logo mb-4">
            <h1 class="display-4 fw-bold">Uma Musume Race Planner</h1>
            <p class="lead mb-4">Your ultimate toolkit for crafting the perfect racing career.</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-uma btn-lg px-4">Go to Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-uma btn-lg px-4 gap-3">Log In</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg px-4">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>

        {{-- Features Section --}}
        <div class="row text-center mt-5 g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="feature-icon-lg bg-primary bg-gradient text-white rounded-3 mb-3">
                            <i class="bi bi-card-checklist"></i>
                        </div>
                        <h4 class="fw-bold">Detailed Planning</h4>
                        <p class="text-muted">Track every detail, from core attributes and aptitude grades to specific skills and career goals.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="feature-icon-lg bg-primary bg-gradient text-white rounded-3 mb-3">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h4 class="fw-bold">Visualize Progress</h4>
                        <p class="text-muted">Use dynamic charts to see your trainee's growth over time, helping you optimize your strategy.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="feature-icon-lg bg-primary bg-gradient text-white rounded-3 mb-3">
                            <i class="bi bi-share-fill"></i>
                        </div>
                        <h4 class="fw-bold">Export & Share</h4>
                        <p class="text-muted">Easily export your comprehensive plans to text format or copy them to your clipboard to share with others.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Use Livewire footer component for consistency --}}
    @livewire('layout.footer')

    {{-- Bootstrap JS for navbar toggler and other interactivity --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Stack for page-specific scripts pushed from other Blade views --}}
    @stack('scripts')

    {{-- Livewire Scripts for standalone welcome page --}}
    @livewireScripts
</body>
</html>
