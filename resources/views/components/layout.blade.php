<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Uma Musume Planner') }}</title>

    <!-- Styles -->
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="antialiased bg-white text-slate-900 dark:bg-slate-950 dark:text-white">
    <!-- Skip to main content link for keyboard navigation -->
    <a href="#main" class="sr-only focus:not-sr-only fixed top-0 left-0 z-50 p-2 bg-blue-600 text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
        Skip to main
    </a>

    <!-- Global live region for Livewire and app status announcements -->
    <div id="livewire-status" class="sr-only" aria-live="polite" aria-atomic="true" role="status"></div>

    <!-- Page header/banner -->
    <header role="banner" class="bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <h1 class="text-2xl font-bold">
                <a href="/" class="hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-1">
                    {{ config('app.name', 'Uma Musume Planner') }}
                </a>
            </h1>
        </div>
    </header>

    <!-- Primary navigation -->
    <nav role="navigation" aria-label="Main navigation" class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex gap-6">
                    @auth
                        <a href="/dashboard" class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-1 transition">
                            Dashboard
                        </a>
                        <a href="/plans" class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-1 transition">
                            Plans
                        </a>
                    @endauth
                </div>
                <div class="flex gap-4">
                    @auth
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="text-slate-700 dark:text-slate-300 hover:text-red-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 rounded px-2 py-1 transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="/login" class="text-slate-700 dark:text-slate-300 hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded px-2 py-1 transition">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main content area -->
    <main id="main" role="main" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <!-- Page footer -->
    <footer role="contentinfo" class="mt-16 bg-slate-100 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-slate-600 dark:text-slate-400">
                <p>&copy; {{ date('Y') }} Uma Musume Planner. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Livewire scripts (if Livewire is installed) -->
    @livewireScripts

    <!-- Vite scripts -->
    @vite('resources/js/app.js')
</body>
</html>
