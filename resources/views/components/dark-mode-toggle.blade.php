{{--
    Dark/light mode toggle.

    Pure Alpine, backed by the `preferences` store. The store is the single
    source of truth for theme state and persists to the `uma_preferences`
    localStorage key — the same key read by the inline anti-FOUC script at the
    top of components/layout.blade.php, so the two must stay in sync.

    Do not confuse this with the Livewire `App\Livewire\Common\DarkModeToggle`
    component, which is a separate server-stateful implementation used by
    Bootstrap-rendered pages. Pages using components/layout.blade.php should use
    this one.
--}}

<button
    type="button"
    x-data
    x-on:click="$store.preferences.toggle()"
    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg p-2 text-slate-700 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:focus-visible:ring-sky-400 dark:focus-visible:ring-offset-slate-900"
    :aria-label="$store.preferences.darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
    aria-pressed="false"
    data-testid="dark-mode-toggle"
>
    {{-- Sun: shown while dark mode is active (i.e. "switch to light") --}}
    <svg
        x-show="$store.preferences.darkMode"
        x-cloak
        class="h-5 w-5"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        viewBox="0 0 24 24"
        aria-hidden="true"
        data-testid="dark-mode-icon-sun"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"
        />
    </svg>

    {{-- Moon: shown while light mode is active --}}
    <svg
        x-show="!$store.preferences.darkMode"
        class="h-5 w-5"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        viewBox="0 0 24 24"
        aria-hidden="true"
        data-testid="dark-mode-icon-moon"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"
        />
    </svg>
</button>
