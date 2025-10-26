{{-- Sidebar Partial - Optional dashboard sidebar with navigation --}}
@php
    $sidebarItems = $sidebarItems ?? [];
    $collapsed = $collapsed ?? false;
@endphp

<aside class="sidebar sidebar-theme {{ $collapsed ? 'collapsed' : '' }}"
       role="complementary"
       aria-label="Sidebar navigation">

    <div class="sidebar-header p-3 border-bottom sidebar-header-theme">
        <div class="d-flex align-items-center">
            <img src="{{ asset('uploads/app_logo/uma_musume_race_planner_logo_64.ico') }}"
                 alt="Uma Musume Planner"
                 class="sidebar-logo me-2"
                 style="width: 32px; height: 32px;">
            <span class="sidebar-title fw-semibold">Dashboard</span>
        </div>
        <button type="button"
                class="btn btn-sm btn-outline-secondary sidebar-toggle"
                aria-label="Toggle sidebar">
            <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="sidebar-nav flex-grow-1 p-3" role="navigation">
        @if(count($sidebarItems) > 0)
            <ul class="nav nav-pills flex-column">
                @foreach($sidebarItems as $item)
                    @if(isset($item['separator']))
                        <li class="nav-separator my-2">
                            <hr class="nav-divider">
                            @if(isset($item['title']))
                                <small class="text-muted text-uppercase fw-semibold">
                                    {{ $item['title'] }}
                                </small>
                            @endif
                        </li>
                    @else
                        <li class="nav-item">
                            @if(isset($item['submenu']))
                                {{-- Submenu item --}}
                                <button class="nav-link d-flex align-items-center justify-content-between w-100 {{ $item['active'] ?? false ? 'active' : '' }}"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#submenu{{ $loop->index }}"
                                        aria-expanded="{{ $item['expanded'] ?? false ? 'true' : 'false' }}">
                                    <div class="d-flex align-items-center">
                                        @if(isset($item['icon']))
                                            <i class="bi {{ $item['icon'] }} me-2" aria-hidden="true"></i>
                                        @endif
                                        <span class="nav-text">{{ $item['title'] }}</span>
                                    </div>
                                    <i class="bi bi-chevron-down submenu-arrow" aria-hidden="true"></i>
                                </button>
                                <div class="collapse {{ $item['expanded'] ?? false ? 'show' : '' }}"
                                     id="submenu{{ $loop->index }}">
                                    <ul class="nav nav-pills flex-column ms-3">
                                        @foreach($item['submenu'] as $subItem)
                                            <li class="nav-item">
                                                <a class="nav-link {{ $subItem['active'] ?? false ? 'active' : '' }}"
                                                   href="{{ $subItem['url'] }}">
                                                    @if(isset($subItem['icon']))
                                                        <i class="bi {{ $subItem['icon'] }} me-2" aria-hidden="true"></i>
                                                    @endif
                                                    <span class="nav-text">{{ $subItem['title'] }}</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                {{-- Regular nav item --}}
                                <a class="nav-link d-flex align-items-center {{ $item['active'] ?? false ? 'active' : '' }}"
                                   href="{{ $item['url'] }}"
                                   @if(isset($item['target'])) target="{{ $item['target'] }}" @endif>
                                    @if(isset($item['icon']))
                                        <i class="bi {{ $item['icon'] }} me-2" aria-hidden="true"></i>
                                    @endif
                                    <span class="nav-text">{{ $item['title'] }}</span>
                                    @if(isset($item['badge']))
                                        <span class="badge bg-{{ $item['badge']['color'] ?? 'primary' }} ms-auto">
                                            {{ $item['badge']['text'] }}
                                        </span>
                                    @endif
                                </a>
                            @endif
                        </li>
                    @endif
                @endforeach
            </ul>
        @else
            {{-- Default sidebar items --}}
            <ul class="nav nav-pills flex-column">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ Route::is('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-house-door me-2" aria-hidden="true"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ Route::is('characters') ? 'active' : '' }}"
                       href="{{ route('characters') }}">
                        <i class="bi bi-people me-2" aria-hidden="true"></i>
                        <span class="nav-text">Roster</span>
                    </a>
                </li>
            </ul>
        @endif
    </nav>
</aside>

@push('styles')
<style>
    .sidebar {
        width: 280px;
        min-height: 100vh;
        background: var(--bs-light);
        border-right: 1px solid var(--bs-border-color);
        transition: width 0.3s ease;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        display: flex;
        flex-direction: column;
    }

    .sidebar.collapsed {
        width: 60px;
    }

    .sidebar.collapsed .nav-text,
    .sidebar.collapsed .sidebar-title {
        display: none;
    }

    .sidebar-header-theme {
        background: var(--bs-body-bg);
        border-bottom-color: var(--bs-border-color);
    }

    .sidebar-logo {
        border-radius: 0.375rem;
    }

    .nav-divider {
        margin: 0.5rem 0;
        opacity: 0.5;
    }

    .submenu-arrow {
        transition: transform 0.2s;
    }

    .nav-link[aria-expanded="true"] .submenu-arrow {
        transform: rotate(180deg);
    }

    /* Dark mode support */
    body.dark-mode .sidebar {
        background: var(--bs-dark);
        border-right-color: var(--bs-border-color-translucent);
    }

    body.dark-mode .sidebar-header-theme {
        background: var(--bs-body-bg);
        border-bottom-color: var(--bs-border-color-translucent);
    }

    /* Responsive behavior */
    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');

            // Save state to localStorage
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        });

        // Restore sidebar state
        const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
        }
    }
});
</script>
@endpush
