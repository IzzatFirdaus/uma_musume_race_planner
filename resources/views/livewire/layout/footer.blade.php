{{-- Livewire Layout Footer Component - Enhanced with theme support and better accessibility --}}
<div>
    <footer class="footer-theme mt-5 py-4" role="contentinfo">
        <div class="container">
            {{-- Main footer links --}}
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3 mb-3">
                        <a href="https://github.com/IzzatFirdaus/uma_musume_race_planner"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="d-inline-flex align-items-center text-decoration-none">
                            <i class="bi bi-github me-2" aria-hidden="true"></i>
                            <span class="visually-hidden">Repository</span>
                            Uma Musume Planner {{ config('app.version', 'v1.0') }}
                        </a>

                        <span class="text-muted d-none d-md-inline">|</span>

                        <div class="text-muted">
                            Last Updated:
                            <time datetime="{{ config('app.last_updated', now()->toDateString()) }}">
                                {{ config('app.last_updated', now()->format('Y-m-d')) }}
                            </time>
                        </div>
                    </div>

                    {{-- Official game links --}}
                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3 small">
                        <a href="https://umamusume.com/"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none">
                            Global Official Site
                        </a>
                        <span class="text-muted">|</span>
                        <a href="https://umamusume.jp/"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none">
                            Japanese Official Site
                        </a>
                        <span class="text-muted">|</span>
                        <a href="https://store.steampowered.com/app/3224770/Umamusume_Pretty_Derby/"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none">
                            Steam Version
                        </a>
                    </div>

                    {{-- Social links --}}
                    <div class="d-flex justify-content-center gap-3 mb-3" aria-label="Social media links">
                        <a href="https://x.com/umamusume_eng"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none"
                           title="Official X (Twitter)"
                           aria-label="Official X (Twitter)">
                            <i class="bi bi-twitter-x fs-5" aria-hidden="true"></i>
                        </a>
                        <a href="https://www.facebook.com/umamusume.eng"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none"
                           title="Official Facebook"
                           aria-label="Official Facebook">
                            <i class="bi bi-facebook fs-5" aria-hidden="true"></i>
                        </a>
                        <a href="https://www.youtube.com/@umamusume_eng"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none"
                           title="Official YouTube"
                           aria-label="Official YouTube">
                            <i class="bi bi-youtube fs-5" aria-hidden="true"></i>
                        </a>
                        <a href="https://discord.com/invite/umamusume-eng"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none"
                           title="Official Discord"
                           aria-label="Official Discord">
                            <i class="bi bi-discord fs-5" aria-hidden="true"></i>
                        </a>
                        <a href="https://umamusume.fandom.com/wiki/Game"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none"
                           title="Umamusume Wiki"
                           aria-label="Umamusume Wiki">
                            <i class="bi bi-book fs-5" aria-hidden="true"></i>
                        </a>
                    </div>

                    {{-- Disclaimer --}}
                    <div class="text-center small text-muted mb-2">
                        <em>This fan-made planning tool is not affiliated with Cygames Inc. or the Uma Musume: Pretty Derby franchise.
                        Uma Musume: Pretty Derby is a registered trademark of Cygames, Inc. All game assets and character designs are
                        property of their respective owners. This tool is designed for educational and planning purposes only.</em>
                    </div>

                    {{-- Game release info --}}
                    <div class="text-center very-small text-muted">
                        Uma Musume: Pretty Derby was developed by Cygames and released on
                        <a href="https://umamusume.jp/"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none">February 24, 2021 (JP)</a> and
                        <a href="https://umamusume.com/"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="text-decoration-none">August 24, 2024 (Global)</a>.
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>

@push('styles')
<style>
    .footer-theme {
        background: rgba(var(--bs-dark-rgb), 0.8);
        backdrop-filter: blur(10px);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--bs-light);
    }

    .footer-theme a {
        color: var(--bs-light);
        transition: color 0.2s ease;
    }

    .footer-theme a:hover {
        color: var(--bs-primary);
    }

    .very-small {
        font-size: 0.75rem;
    }

    /* Light mode */
    body.light-bg .footer-theme {
        background: rgba(var(--bs-light-rgb), 0.9);
        color: var(--bs-dark);
        border-top-color: rgba(0, 0, 0, 0.1);
    }

    body.light-bg .footer-theme a {
        color: var(--bs-dark);
    }

    body.light-bg .footer-theme .text-muted {
        color: var(--bs-secondary) !important;
    }
</style>
@endpush
