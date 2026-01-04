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

    <title>@yield('title', config('app.name', 'Uma Musume Race Planner'))</title>
    <meta name="description" content="@yield('meta_description', 'Uma Musume Planner — create and manage training plans for Umamusume')">

    {{-- Third-party CSS Dependencies (from CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    {{-- Google Font: M PLUS Rounded 1c (moved from CSS @import for better performance) --}}
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;600;700&display=swap">

    {{-- Favicons and Touch Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_32.ico') }}"
        sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_256.png') }}">

    {{-- Custom Theme Color using environment variable with fallback --}}
    <style>
        :root {
            --app-theme-color: {{ config('app.theme_color') }};
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
            background: url('{{ asset('uploads/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png') }}') no-repeat center center fixed;
            background-size: cover;
            /* Fallback background color */
            background-color: #222;
        }

        /* Portrait/vertical layout for small screens (e.g. mobile/tablet) */
        @media (max-width: 900px) {
            body {
                background: url('{{ asset('uploads/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png') }}') no-repeat center center fixed;
                background-size: cover;
            }
        }

        /* Example: support alternate backgrounds for light theme with a body.light-bg class */
        body.light-bg {
            background: url('{{ asset('uploads/app_bg/uma_musume_race_planner_bg_light_1536x1028.png') }}') no-repeat center center fixed;
            background-size: cover;
            background-color: #f8f9fa;
        }

        @media (max-width: 900px) {
            body.light-bg {
                background: url('{{ asset('uploads/app_bg/uma_musume_race_planner_bg_light_1028x1536.png') }}') no-repeat center center fixed;
                background-size: cover;
            }
        }

        /* Prevent horizontal scrolling when text is resized for accessibility tests */
        html,
        body {
            overflow-x: hidden;
        }

        /* Example: SVG support for particular backgrounds if needed (uncomment to use) */
        /*
      body.svg-bg {
        background: url('{{ asset('uploads/app_bg/uma_musume_race_planner_bg_dark_1028x1536.svg') }}') no-repeat center center fixed;
        background-size: cover;
      }
      */
    </style>

    {{-- Chart.js for data visualization --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Bootstrap JS for modals and other interactivity - must load before Vite bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    {{--
        Vite Asset Bundling
        This directive loads your app's local CSS and JS files, built by Vite.
        Replace individual <link> and <script> tags for local assets.
    --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Vite bundles and per-page styles --}}
    @stack('styles')

    {{-- Livewire Styles --}}
    @livewireStyles

</head>

<body class="@yield('body-class', '')" data-theme="{{ config('app.theme', 'auto') }}" style="overflow-x: hidden;">
    {{-- Skip-to-main link for keyboard users (matches Playwright tests) --}}
    <a class="visually-hidden-focusable" href="#main">Skip to main</a>

    {{-- Page header/banner (landmark) - the navbar component renders its own header role. Insert it directly. --}}
    <livewire:layout.navbar />

    {{-- Main content area (accessible landmark) --}}
    <main id="main" tabindex="-1" role="main">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>

    {{-- Livewire Alerts and Modals Components --}}
    <livewire:layout.alerts />
    <livewire:layout.modals />

    {{-- Global Message Box for user notifications --}}
    <div class="modal fade" id="messageBoxModal" tabindex="-1" role="dialog" aria-hidden="true"
        aria-labelledby="messageBoxLabel">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center alert alert-success mb-0" id="messageBoxBody" role="status"
                    aria-live="polite"></div>
            </div>
        </div>
    </div>

    {{-- Livewire Footer Component for interactive, theme-aware footer --}}
    <livewire:layout.footer />

    {{-- Stack for page-specific scripts pushed from other Blade views --}}
    @stack('scripts')

    {{-- Livewire Script Config (Livewire/Alpine are bundled via Vite in app.js) --}}
    @livewireScriptConfig
</body>

</html>
