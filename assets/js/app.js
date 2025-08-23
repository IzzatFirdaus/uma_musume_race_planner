// assets/js/app.js
// Modular enhancements: dark mode toggle + Quick Stats chart

document.addEventListener('DOMContentLoaded', function () {
  // Dark mode: honor saved preference or system setting
  const body = document.body;
  const darkModeToggle = document.getElementById('darkModeToggle');

  function setDarkMode(isDark) {
    body.classList.toggle('dark-mode', isDark);
    try { localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled'); } catch (_) {}
    if (darkModeToggle) darkModeToggle.checked = isDark;
  }

  try {
    const saved = localStorage.getItem('darkMode');
    if (saved === 'enabled') setDarkMode(true);
    else if (saved === 'disabled') setDarkMode(false);
    else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) setDarkMode(true);
  } catch (_) {}

  if (darkModeToggle) {
    darkModeToggle.addEventListener('change', () => setDarkMode(darkModeToggle.checked));
  }

  // Quick Stats Chart (doughnut)
  const canvas = document.getElementById('statsChart');
  if (canvas && window.Chart) {
    const total = parseInt(document.getElementById('statsPlans')?.textContent || '0', 10);
    const active = parseInt(document.getElementById('statsActive')?.textContent || '0', 10);
    const finished = parseInt(document.getElementById('statsFinished')?.textContent || '0', 10);
    const planning = Math.max(total - active - finished, 0);

    const ctx = canvas.getContext('2d');
    const getCss = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();

    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Finished', 'Planning'],
        datasets: [{
          data: [active, finished, planning],
          backgroundColor: [
            getCss('--bs-success'),
            getCss('--bs-primary'),
            getCss('--bs-warning') || '#f0ad4e'
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.parsed}` } }
        },
        cutout: '60%'
      }
    });
  }
});
