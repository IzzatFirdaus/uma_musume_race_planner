// assets/js/dashboard.js
// Handles dashboard Chart.js integration for a Stats Radar chart.
// - Uses a distinct canvas ID 'statsRadar' to avoid collisions with the doughnut chart in app.js
// - Applies best practices: reduced motion, defensive DOM checks, CSS variable-driven theming.

/* eslint-env browser */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('statsRadar');
    if (!canvas || !window.Chart) return;

    // Gather stats from DOM if available, else fallback to example values
    const keys = ['speed', 'stamina', 'power', 'guts', 'wisdom'];
    const defaults = { speed: 80, stamina: 70, power: 90, guts: 60, wisdom: 85 };
    const stats = Object.fromEntries(keys.map((k) => {
      const el = document.getElementById('stats' + k.charAt(0).toUpperCase() + k.slice(1));
      const val = parseInt(el?.textContent || `${defaults[k]}`, 10);
      return [k, Number.isFinite(val) ? val : defaults[k]];
    }));

    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;

    // CSS-driven colors with sensible fallbacks. Defer reading CSS variables
    // to the next animation frame to avoid forced layout before styles are applied.
    const css = (v, fb) => getComputedStyle(document.documentElement).getPropertyValue(v).trim() || fb;
    let stroke = 'rgba(111, 66, 193, 1)';
    let fill = 'transparent';
    requestAnimationFrame(() => {
      stroke = css('--color-secondary', 'rgba(111, 66, 193, 1)');
      fill = 'color-mix(in oklab, ' + stroke + ' 25%, transparent)';
      // Re-create chart or update dataset colors here if needed in future.
    });

    // eslint-disable-next-line no-new
    new Chart(canvas, {
      type: 'radar',
      data: {
        labels: keys.map(k => k.charAt(0).toUpperCase() + k.slice(1)),
        datasets: [{
          label: 'Stats',
          data: keys.map(k => stats[k]),
          backgroundColor: fill,
          borderColor: stroke,
          pointBackgroundColor: stroke,
          pointBorderColor: '#fff',
          pointHoverBackgroundColor: '#fff',
          pointHoverBorderColor: stroke,
        }]
      },
      options: {
        responsive: true,
        animation: reducedMotion ? false : { duration: 500 },
        plugins: {
          legend: { position: 'top' }
        },
        scales: {
          r: {
            angleLines: { display: false },
            grid: { color: 'rgba(0,0,0,0.1)' },
            ticks: { display: false },
            suggestedMin: 0,
            suggestedMax: 120
          }
        }
      }
    });
  });
})();