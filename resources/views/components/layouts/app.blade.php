{{--
    App Layout Component (Blade) - Main layout for all pages

    Contract:
    - Inputs: $title (optional), $metaDescription (optional), $bodyClass (optional), slots
    - Outputs: Complete HTML document with navbar, footer, asset loading, theme support
    - Edge Cases: Falls back to Blade partials if Livewire unavailable, safe asset() usage
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF token for AJAX and form requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- App base URL for building API paths from JS (works with or without subdirectory) --}}
    <meta name="app-base-url" content="{{ url('') }}">
    {{-- App public path (subdirectory-aware), e.g. /uma-musume-planner-laravel/public --}}
    @php($publicPath = rtrim(parse_url(asset(''), PHP_URL_PATH) ?? '', '/'))
    <meta name="app-public-path" content="{{ $publicPath }}">
    {{-- Ensure relative asset URLs resolve when app is served from a subdirectory --}}
    <base href="{{ url('') }}/">

    {{-- Dynamic title and meta description --}}
    <title>{{ $title ?? config('app.name', 'Uma Musume Race Planner') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Uma Musume Planner — create and manage training plans for Umamusume characters' }}">

    {{-- Third-party CSS Dependencies (from CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    {{-- Google Font: M PLUS Rounded 1c (moved from CSS @import for better performance) --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;600;700&display=swap">

    {{-- Favicons and Touch Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_32.ico') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_256.png') }}">

    {{-- Custom Theme Color using environment variable with fallback --}}
    <style>
        :root {
            --app-theme-color: {{ config('app.theme_color', '#007bff') }};
            /* Ensure CSS vars for background images resolve to public/uploads even during Vite dev */
            --bg-image-body-light: url("{{ asset('uploads/app_bg/uma_musume_race_planner_bg_light_1536x1028.png') }}");
            --bg-image-body-dark: url("{{ asset('uploads/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png') }}");
            --bg-image-component-light: url("{{ asset('uploads/app_bg/uma_musume_race_planner_bg_light_1028x1536.png') }}");
            --bg-image-component-dark: url("{{ asset('uploads/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png') }}");
        }

        /*
            Adaptive background image for desktop and mobile/tablet devices.
            - Uses landscape image for wider screens.
            - Uses portrait/vertical image for narrower screens (e.g. mobile).
            - Uses light or dark backgrounds based on a custom class on body (can be toggled for dark/light themes).
        */
        body {
            background: var(--bg-image-body-dark) no-repeat center center fixed;
            background-size: cover;
            /* Fallback background color */
            background-color: #222;
            font-family: 'M PLUS Rounded 1c', sans-serif;
        }

        /* Portrait/vertical layout for small screens (e.g. mobile/tablet) */
        @media (max-width: 900px) {
            body {
                background-image: var(--bg-image-component-dark);
            }
        }

        /* Support alternate backgrounds for light theme with a body.light-bg class */
        body.light-bg {
            background-image: var(--bg-image-body-light);
            background-color: #f8f9fa;
        }

        @media (max-width: 900px) {
            body.light-bg {
                background-image: var(--bg-image-component-light);
            }
        }

        /* Dark mode toggle support */
        body.dark-mode {
            background-image: var(--bg-image-body-dark);
            background-color: #222;
        }

        @media (max-width: 900px) {
            body.dark-mode {
                background-image: var(--bg-image-component-dark);
            }
        }
    </style>

    {{-- Chart.js for data visualization --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Vite Asset Bundling - loads app's local CSS and JS files --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Per-page styles hook --}}
    @stack('styles')

    {{-- Livewire Styles --}}
    @livewireStyles

</head>

<body class="{{ $bodyClass ?? '' }}" data-theme="{{ config('app.theme', 'auto') }}">
    {{-- Skip-to-content link for accessibility --}}
    <a class="visually-hidden-focusable" href="#main-content">Skip to content</a>

    {{-- Navbar - prefer Livewire component with Blade fallback --}}
    @if(class_exists(\Livewire\Livewire::class))
        <livewire:layout.navbar />
    @else
        @include('layouts.partials.navbar')
    @endif

    {{-- Main content area (accessible landmark) --}}
    <main id="main-content" tabindex="-1" role="main">
        {{ $slot }}
    </main>

    {{-- Global Message Box Modal for user notifications --}}
    <div class="modal fade"
         id="messageBoxModal"
         tabindex="-1"
         role="dialog"
         aria-hidden="true"
         aria-labelledby="messageBoxLabel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center alert alert-success mb-0"
                     id="messageBoxBody"
                     role="status"
                     aria-live="polite">
                </div>
            </div>
        </div>
    </div>

    {{-- Footer - prefer Blade partial for consistent rendering --}}
    @include('layouts.partials.footer')

    {{-- SweetAlert2 for notifications and modals --}}
    {{-- Bootstrap JS for modals and other interactivity --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Per-page scripts hook --}}
    @stack('scripts')

    {{-- Livewire Scripts --}}
    @livewireScripts
</body>
</html>
