<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF token for AJAX and form requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Uma Musume Race Planner</title>

  {{-- Third-party CSS Dependencies (from CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    {{-- Favicons and Touch Icons --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_32.ico') }}" sizes="32x32">
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

    {{--
        Vite Asset Bundling
        This directive loads your app's local CSS and JS files, built by Vite.
        Replace individual <link> and <script> tags for local assets.
    --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Livewire Styles --}}
    @livewireStyles

</head>

<body>
    {{-- Navbar --}}
  @livewire('layout.navbar')

    @yield('content')

    {{-- Modals --}}
    @include('modals.plan-details')
    @include('modals.quick-create-plan')

    {{-- Global Message Box for user notifications --}}
    <div class="modal fade" id="messageBoxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center alert alert-success mb-0" id="messageBoxBody"></div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
  @livewire('layout.footer')

    {{-- Bootstrap JS for modals and other interactivity --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Stack for page-specific scripts pushed from other Blade views --}}
    @stack('scripts')
    {{-- Livewire Scripts --}}
    @livewireScripts
</body>
</html>
