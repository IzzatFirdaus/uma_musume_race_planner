{{-- Dark Mode Toggle Component --}}
{{-- Implements FR-7.1: Dark/light mode toggle with persistence --}}
<div x-data="darkModeToggle()" x-init="init()" class="dark-mode-toggle" data-testid="dark-mode-toggle">
    <button type="button" class="btn btn-link p-0 border-0 text-decoration-none" x-on:click="toggle()"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'" aria-pressed="false"
        x-bind:aria-pressed="isDark ? 'true' : 'false'">
        {{-- Sun icon (shown in dark mode) --}}
        <i class="bi bi-sun-fill fs-5" x-show="isDark" x-cloak aria-hidden="true"></i>
        {{-- Moon icon (shown in light mode) --}}
        <i class="bi bi-moon-fill fs-5" x-show="!isDark" aria-hidden="true"></i>
        <span class="visually-hidden" x-text="isDark ? 'Switch to light mode' : 'Switch to dark mode'"></span>
    </button>
</div>

@script
    <script>
        Alpine.data('darkModeToggle', () => ({
            isDark: false,

            init() {
                // Check localStorage first, then system preference
                const stored = localStorage.getItem('darkMode');
                if (stored !== null) {
                    this.isDark = stored === 'true';
                } else {
                    // Check system preference
                    this.isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                }

                this.applyMode();

                // Listen for system preference changes
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    // Only apply if no stored preference
                    if (localStorage.getItem('darkMode') === null) {
                        this.isDark = e.matches;
                        this.applyMode();
                    }
                });

                // Listen for Livewire events
                Livewire.on('dark-mode-changed', (data) => {
                    this.isDark = data[0].mode === 'dark';
                    this.applyMode();
                });
            },

            toggle() {
                this.isDark = !this.isDark;
                localStorage.setItem('darkMode', this.isDark.toString());
                this.applyMode();

                // Sync with Livewire
                $wire.setMode(this.isDark ? 'dark' : 'light');
            },

            applyMode() {
                if (this.isDark) {
                    document.documentElement.classList.add('dark');
                    document.body.setAttribute('data-bs-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.body.setAttribute('data-bs-theme', 'light');
                }
            }
        }));
    </script>
@endscript
