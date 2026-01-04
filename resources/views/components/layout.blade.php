<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth" x-data
    x-bind:class="{ 'dark': $store.preferences.darkMode }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Uma Musume Planner') }}</title>

    <!-- Styles -->
    @vite('resources/css/app.css')
    @livewireStyles

    <!-- Prevent flash of wrong theme -->
    <script>
        (function() {
            const stored = localStorage.getItem('uma_preferences');
            const legacy = localStorage.getItem('darkMode');
            let dark = false;
            if (stored) {
                try {
                    dark = JSON.parse(stored).darkMode;
                } catch (e) {}
            } else if (legacy !== null) {
                dark = legacy === 'enabled';
            } else {
                dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            if (dark) document.documentElement.classList.add('dark');
        })();
    </script>
</head>

<body class="antialiased bg-white text-slate-900 dark:bg-slate-950 dark:text-white">
    <!-- Connection status banner -->
    <x-connection-banner />

    <!-- Skip to main content link for keyboard navigation -->
    <a href="#main"
        class="sr-only focus:not-sr-only fixed top-0 left-0 z-50 p-2 bg-blue-600 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
        Skip to main
    </a>

    <!-- Global live region for Livewire and app status announcements -->
    <div id="livewire-status" class="sr-only" aria-live="polite" aria-atomic="true" role="status"></div>

    <!-- Page header/banner -->
    <header role="banner" class="bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
            {{-- Site branding: use a non-h1 element so pages can provide the single page h1 --}}
            <div class="site-branding text-xl sm:text-2xl font-bold">
                <a href="/"
                    class="hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-1 min-h-[44px] inline-flex items-center"
                    aria-label="Home" data-testid="header-logo">
                    {{ config('app.name', 'Uma Musume Planner') }}
                </a>
            </div>
        </div>
    </header>

    <!-- Primary navigation with mobile hamburger menu (Req 14.1, 14.2) -->
    <nav role="navigation" aria-label="Main navigation"
        class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40"
        x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 sm:h-16">
                <!-- Desktop navigation links (hidden on mobile) -->
                <div class="hidden md:flex gap-4 lg:gap-6">
                    @auth
                        <a href="/dashboard"
                            class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-2 min-h-[44px] inline-flex items-center transition"
                            data-testid="nav-dashboard">
                            Dashboard
                        </a>
                        <a href="/plans"
                            class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-2 min-h-[44px] inline-flex items-center transition"
                            data-testid="nav-plans">
                            Plans
                        </a>
                        <a href="/characters"
                            class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-2 min-h-[44px] inline-flex items-center transition"
                            data-testid="nav-characters">
                            Characters
                        </a>
                        <a href="/guide"
                            class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-2 min-h-[44px] inline-flex items-center transition"
                            data-testid="nav-guide">
                            Guide
                        </a>
                    @else
                        <a href="/guide"
                            class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-2 min-h-[44px] inline-flex items-center transition"
                            data-testid="nav-guide">
                            Guide
                        </a>
                        <a href="/characters"
                            class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-2 min-h-[44px] inline-flex items-center transition"
                            data-testid="nav-characters">
                            Characters
                        </a>
                    @endauth
                </div>

                <!-- Mobile hamburger button (Req 14.2) -->
                <button type="button"
                    class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-700 dark:text-slate-300 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 min-w-[44px] min-h-[44px]"
                    @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen"
                    aria-controls="mobile-menu" aria-label="Toggle navigation menu" data-testid="mobile-menu-button">
                    <!-- Hamburger icon -->
                    <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <!-- Close icon -->
                    <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Right side actions -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <!-- Dark mode toggle -->
                    <x-dark-mode-toggle />

                    @auth
                        <form method="POST" action="/logout" class="hidden sm:block">
                            @csrf
                            <button type="submit"
                                class="text-slate-700 dark:text-slate-300 hover:text-red-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 rounded px-2 py-2 min-h-[44px] inline-flex items-center transition"
                                data-testid="nav-logout">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="/login"
                            class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-2 min-h-[44px] inline-flex items-center transition"
                            data-testid="nav-login">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile menu panel (Req 14.2) -->
        <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="md:hidden border-t border-slate-200 dark:border-slate-800" id="mobile-menu"
            @click.away="mobileMenuOpen = false" @keydown.escape.window="mobileMenuOpen = false">
            <div class="px-4 py-3 space-y-1 bg-slate-50 dark:bg-slate-900">
                @auth
                    <a href="/dashboard"
                        class="px-3 py-3 rounded-md text-base font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] flex items-center transition"
                        @click="mobileMenuOpen = false" data-testid="mobile-nav-dashboard">
                        Dashboard
                    </a>
                    <a href="/plans"
                        class="px-3 py-3 rounded-md text-base font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] flex items-center transition"
                        @click="mobileMenuOpen = false" data-testid="mobile-nav-plans">
                        Plans
                    </a>
                    <a href="/characters"
                        class="px-3 py-3 rounded-md text-base font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] flex items-center transition"
                        @click="mobileMenuOpen = false" data-testid="mobile-nav-characters">
                        Characters
                    </a>
                    <a href="/guide"
                        class="px-3 py-3 rounded-md text-base font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] flex items-center transition"
                        @click="mobileMenuOpen = false" data-testid="mobile-nav-guide">
                        Guide
                    </a>
                    <div class="border-t border-slate-200 dark:border-slate-700 my-2"></div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-3 py-3 rounded-md text-base font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 min-h-[44px] flex items-center transition"
                            data-testid="mobile-nav-logout">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="/guide"
                        class="px-3 py-3 rounded-md text-base font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] flex items-center transition"
                        @click="mobileMenuOpen = false" data-testid="mobile-nav-guide">
                        Guide
                    </a>
                    <a href="/characters"
                        class="px-3 py-3 rounded-md text-base font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] flex items-center transition"
                        @click="mobileMenuOpen = false" data-testid="mobile-nav-characters">
                        Characters
                    </a>
                    <div class="border-t border-slate-200 dark:border-slate-700 my-2"></div>
                    <a href="/login"
                        class="px-3 py-3 rounded-md text-base font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 min-h-[44px] flex items-center transition"
                        @click="mobileMenuOpen = false" data-testid="mobile-nav-login">
                        Login
                    </a>
                    <a href="/register"
                        class="px-3 py-3 rounded-md text-base font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 min-h-[44px] flex items-center transition"
                        @click="mobileMenuOpen = false" data-testid="mobile-nav-register">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main content area (Req 14.1 - single-column on mobile) -->
    <main id="main" role="main" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        {{ $slot }}
    </main>

    <!-- Page footer -->
    <footer role="contentinfo"
        class="mt-8 sm:mt-12 lg:mt-16 bg-slate-100 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="text-center text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                <p>&copy; {{ date('Y') }} Uma Musume Planner. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Toast notifications container -->
    <x-toast-container />

    <!-- Reconnection prompt modal for Account runs -->
    <x-reconnection-prompt-modal />

    <!-- Keyboard shortcuts help modal (Task 30.2 - Accessibility) -->
    <x-keyboard-shortcuts-help />

    <!-- Bootstrap JS for modals and other interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Vite scripts (includes Livewire and Alpine bundled) -->
    @vite('resources/js/app.js')

    <!-- Livewire script config (Livewire/Alpine are bundled via Vite) -->
    @livewireScriptConfig
</body>

</html>
