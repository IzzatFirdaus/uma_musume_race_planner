// assets/js/app.js
// Modular enhancements: Theme switching (light/dark/system) + Quick Stats chart
// Applies General Best Practices: accessibility, robustness, reduced motion support, and clear code.

/* eslint-env browser */
(() => {
    'use strict';

  /**
   * Utilities
   */
    const $ = (sel, root = document) => root.querySelector(sel);
    const on = (el, evt, handler, opts) => el && el.addEventListener(evt, handler, opts);

  /**
   * Theme management
   * - Supports three modes: 'light', 'dark', 'system'
   * - Persists preference in localStorage under 'theme'
   * - Updates both <html data-theme="..."> and <body class="dark-mode"> for legacy CSS compatibility
   * - Syncs a toggle element (checkbox or button with role="switch") when present
   */
    const THEME_STORAGE_KEY = 'theme'; // values: 'light' | 'dark' | 'system'

    const prefersDarkQuery = window.matchMedia?.('(prefers-color-scheme: dark)');

  /**
   * Compute the effective theme ('light' or 'dark') from the stored preference.
   * @returns {'light'|'dark'}
   */
function getEffectiveTheme()
{
    let stored = 'system';
    try {
        stored = localStorage.getItem(THEME_STORAGE_KEY) || 'system';
    } catch (_) {
      // no-op
    }
    if (stored === 'dark') {
        return 'dark';
    }
    if (stored === 'light') {
        return 'light';
    }
    // system
    return prefersDarkQuery?.matches ? 'dark' : 'light';
}

  /**
   * Apply theme to DOM and sync any toggle control.
   * @param {'light'|'dark'|'system'} pref
   */
function applyTheme(pref)
{
    const effective = pref === 'system' ? getEffectiveTheme() : pref;
    // Set attribute on <html> for token-driven CSS and fallback class for legacy rules
    const root = document.documentElement;
    root.setAttribute('data-theme', effective);
    document.body.classList.toggle('dark-mode', effective === 'dark');

    // Persist explicit pref if possible
    try {
        localStorage.setItem(THEME_STORAGE_KEY, pref); } catch (_) {
        }

    // Sync toggle UI (checkbox or role="switch")
        const toggle = $('#darkModeToggle');
        if (toggle) {
            const isChecked = effective === 'dark';
            if ('checked' in toggle) {
                toggle.checked = isChecked;
            }
            toggle.setAttribute('aria-checked', String(isChecked));
            toggle.setAttribute('aria-label', `Toggle dark mode(currently ${effective})`);
        }
}

  /**
   * Initialize theme from storage or system
   */
function initTheme()
{
    let pref = 'system';
    try {
        pref = localStorage.getItem(THEME_STORAGE_KEY) || 'system';
    } catch (_) {
      // no-op
    }
    applyTheme(pref);
}

  document.addEventListener('DOMContentLoaded', () => {
    // Initialize theme
        initTheme();

    // Listen for system preference changes IF user chose 'system'
    if (prefersDarkQuery?.addEventListener) {
            prefersDarkQuery.addEventListener('change', () => {
                try {
                    const pref = localStorage.getItem(THEME_STORAGE_KEY) || 'system';
                    if (pref === 'system') {
                        applyTheme('system');
                    }
                } catch (_) {
                  // no-op
                }
            });
        }

    // Theme toggle wiring (supports checkbox or button with role="switch")
        const darkModeToggle = $('#darkModeToggle');
        if (darkModeToggle) {
          // Ensure accessible semantics
            if (!darkModeToggle.getAttribute('role')) {
                darkModeToggle.setAttribute('role', 'switch');
            }
          // Handle both change events (for checkboxes) and click events (for buttons)
            const handleToggle = () => {
              // If input[type=checkbox], use checked; if button, toggle aria-checked
                const isChecked = 'checked' in darkModeToggle ? darkModeToggle.checked : darkModeToggle.getAttribute('aria-checked') !== 'true';
                applyTheme(isChecked ? 'dark' : 'light');
            };

            on(darkModeToggle, 'change', handleToggle);
            on(darkModeToggle, 'click', handleToggle);

          // Support Shift+T shortcut to toggle theme quickly (accessible)
            on(document, 'keydown', (e) => {
                if ((e.key === 'T' || e.key === 't') && e.shiftKey) {
                    e.preventDefault();
                    const effective = getEffectiveTheme();
                    applyTheme(effective === 'dark' ? 'light' : 'dark');
                }
            });
        }

    /**
     * Quick Stats Doughnut Chart
     * - Uses CSS variables for colors (Bootstrap vars or fallbacks)
     * - Honors reduced motion preference
     */
        const canvas = $('#statsChart');
        if (canvas && window.Chart) {
            const total = parseInt($('#statsPlans')?.textContent || '0', 10);
            const active = parseInt($('#statsActive')?.textContent || '0', 10);
            const finished = parseInt($('#statsFinished')?.textContent || '0', 10);
            const planning = Math.max(total - active - finished, 0);

          /** Get CSS var value with fallback (defer to rAF to avoid forcing layout)
           *  Usage: call inside requestAnimationFrame when available.
           */
            const css = (v, fb = '') => getComputedStyle(document.documentElement).getPropertyValue(v).trim() || fb;

            let backgroundColor = ['#198754', '#0d6efd', '#f0ad4e'];
          // Defer reading CSS variables to next animation frame when possible
            requestAnimationFrame(() => {
                backgroundColor = [
                css('--bs-success', '#198754'),
                css('--bs-primary', '#0d6efd'),
                css('--bs-warning', '#f0ad4e')
                ];
            });

                    // Respect reduced motion
                        const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;

          // Prepare chart instance (cleanup if re-initialized)
            const ctx = canvas.getContext('2d');

          // eslint-disable-next-line no-new
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Finished', 'Planning'],
                    datasets: [{
                        data: [active, finished, planning],
                            backgroundColor,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    animation: reducedMotion ? false : { duration : 500 },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12 } },
                        tooltip: {
                            callbacks: {
                                label: (tt) => `${tt.label}: ${tt.parsed}`
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }
    });
})();