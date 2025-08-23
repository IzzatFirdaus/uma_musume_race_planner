// assets/js/dashboard.js
// Handles dashboard Chart.js integration for stats radar chart.
// Dark mode is handled in app.js (do not duplicate logic).

document.addEventListener('DOMContentLoaded', function () {
  // Example Chart.js radar setup with dynamic data

  const chartCanvas = document.getElementById('statsChart');
  if (chartCanvas && window.Chart) {
    // Try to fetch stats from HTML data attributes or API (fallback to hardcoded)
    let stats = {
      speed: 80,
      stamina: 70,
      power: 90,
      guts: 60,
      wisdom: 85
    };

    // Example: If stats are rendered server-side into elements
    const statKeys = ['speed', 'stamina', 'power', 'guts', 'wisdom'];
    statKeys.forEach(key => {
      const el = document.getElementById('stats' + key.charAt(0).toUpperCase() + key.slice(1));
      if (el) {
        stats[key] = parseInt(el.textContent || '0', 10);
      }
    });

    new Chart(chartCanvas, {
      type: 'radar',
      data: {
        labels: statKeys.map(k => k.charAt(0).toUpperCase() + k.slice(1)),
        datasets: [{
          label: 'Stats',
          data: statKeys.map(k => stats[k]),
          backgroundColor: 'rgba(111, 66, 193, 0.2)',
          borderColor: 'rgba(111, 66, 193, 1)'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' }
        },
        scales: {
          r: {
            angleLines: { display: false },
            suggestedMin: 0,
            suggestedMax: 120
          }
        }
      }
    });
  }
});