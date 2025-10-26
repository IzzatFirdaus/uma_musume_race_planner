
{{-- Livewire Layout Navbar Component - Enhanced with accessibility, theme support, and interactivity --}}
<nav class="navbar navbar-expand-lg navbar-dark sticky-top navbar-theme"
     role="navigation"
     aria-label="Main navigation"
     x-data="{ darkMode: $persist(false) }"
     x-init="$watch('darkMode', val => document.body.classList.toggle('dark-mode', val))">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center"
           href="{{ route('dashboard') }}"
           aria-label="Uma Musume Planner - Go to training center">
            <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}"
                 alt="Uma Musume Planner Logo"
                 class="navbar-logo me-2"
                 style="height: 32px; width: auto;">
            <span class="fw-semibold">Uma Musume: Pretty Derby Planner</span>
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto" role="menubar">
                <li class="nav-item" role="none">
                    <a class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}"
                       role="menuitem"
                       @if(Route::is('dashboard')) aria-current="page" @endif>
                        <i class="bi bi-house-door me-1" aria-hidden="true"></i>
                        Training Center
                    </a>
                </li>

                <li class="nav-item" role="none">
                    <a class="nav-link {{ Route::is('characters') ? 'active' : '' }}"
                       href="{{ route('characters') }}"
                       role="menuitem"
                       @if(Route::is('characters')) aria-current="page" @endif>
                        <i class="bi bi-people me-1" aria-hidden="true"></i>
                        Umamusume Roster
                    </a>
                </li>

                <li class="nav-item" role="none">
                    <a class="nav-link"
                       href="#"
                       id="newPlanBtn"
                       role="menuitem"
                       wire:click="$dispatch('open-create-plan-modal')">
                        <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>
                        New Training Plan
                    </a>
                </li>

                @if(Route::has('guide'))
                    <li class="nav-item" role="none">
                        <a class="nav-link {{ Route::is('guide') ? 'active' : '' }}"
                           href="{{ route('guide') }}"
                           role="menuitem"
                           @if(Route::is('guide')) aria-current="page" @endif>
                            <i class="bi bi-journal-bookmark me-1" aria-hidden="true"></i>
                            Trainer's Guide
                        </a>
                    </li>
                @endif

                <li class="nav-item dropdown" role="none">
                    <a class="nav-link dropdown-toggle"
                       href="#"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                       aria-haspopup="true">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                        Game Info
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-glass" role="menu">
                        <li role="none">
                            <a class="dropdown-item"
                               href="https://umamusume.wiki/Game:Mechanics"
                               target="_blank"
                               rel="noopener noreferrer"
                               role="menuitem">
                                <i class="bi bi-gear me-2" aria-hidden="true"></i>
                                Game Mechanics
                            </a>
                        </li>
                        <li role="none">
                            <a class="dropdown-item"
                               href="https://gametora.com/umamusume/beginners-guide"
                               target="_blank"
                               rel="noopener noreferrer"
                               role="menuitem">
                                <i class="bi bi-lightbulb me-2" aria-hidden="true"></i>
                                Beginner's Guide
                            </a>
                        </li>
                        <li role="none">
                            <a class="dropdown-item"
                               href="https://umamusume.fandom.com/wiki/Game"
                               target="_blank"
                               rel="noopener noreferrer"
                               role="menuitem">
                                <i class="bi bi-book me-2" aria-hidden="true"></i>
                                Official Wiki
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item d-flex align-items-center ms-lg-3" role="none">
                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               id="darkModeToggle"
                               role="switch"
                               x-model="darkMode"
                               aria-describedby="darkModeLabel">
                        <label class="form-check-label text-light small"
                               for="darkModeToggle"
                               id="darkModeLabel">
                            <i class="bi bi-moon me-1" aria-hidden="true" x-show="!darkMode"></i>
                            <i class="bi bi-sun me-1" aria-hidden="true" x-show="darkMode"></i>
                            <span class="d-none d-xl-inline">Dark Mode</span>
                        </label>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
