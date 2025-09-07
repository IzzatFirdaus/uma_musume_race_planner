
{{-- Converted from components/navbar.php --}}

<div>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top uma-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}" alt="Uma Musume Logo" style="height: 32px; margin-right: 12px;">
                <span class="brand-text">Uma Musume: Pretty Derby Planner</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-house-door me-1"></i> Training Center
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('characters') ? 'active' : '' }}" href="{{ route('characters') }}">
                            <i class="bi bi-people me-1"></i> Umamusume Roster
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="newPlanBtn">
                            <i class="bi bi-plus-circle me-1"></i> New Training Plan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('guide') ? 'active' : '' }}" href="{{ route('guide') }}">
                            <i class="bi bi-journal-bookmark me-1"></i> Trainer's Guide
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-info-circle me-1"></i> Game Info
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="https://umamusume.fandom.com/wiki/Game" target="_blank">Game Mechanics</a></li>
                            <li><a class="dropdown-item" href="https://gametora.com/umamusume/beginners-guide" target="_blank">Beginner's Guide</a></li>
                            <li><a class="dropdown-item" href="https://umamusume.fandom.com/wiki/Game" target="_blank">Official Wiki</a></li>
                        </ul>
                    </li>
                    <li class="nav-item d-flex align-items-center ms-lg-3">
                        <div class="form-check form-switch text-light">
                            <input class="form-check-input" type="checkbox" id="darkModeToggle">
                            <label class="form-check-label" for="darkModeToggle">
                                <i class="bi bi-sun-fill me-1"></i>
                                Day Mode
                            </label>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>
