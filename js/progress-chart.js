// js/progress-chart.js
// Lightweight chart initializer that renders growth chart using Chart.js
(function () {
  function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  async function fetchChartData(planId) {
    if (!planId) return null;
    const res = await fetch(`get_progress_chart_data.php?plan_id=${planId}`);
    const json = await res.json();
    return json.success ? json.data : null;
  }

  function getCssVariableValue(variableName) {
    return getComputedStyle(document.documentElement).getPropertyValue(variableName).trim();
  }

  function renderChart(canvas, data) {
    if (!canvas || !data) return null;
    const ctx = canvas.getContext('2d');
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: data.map(t => `Turn ${t.turn}`),
        datasets: [
          { label: 'Speed', data: data.map(t => t.speed), borderColor: getCssVariableValue('--stat-speed-color'), pointBackgroundColor: getCssVariableValue('--stat-speed-color'), tension: 0.3 },
          { label: 'Stamina', data: data.map(t => t.stamina), borderColor: getCssVariableValue('--stat-stamina-color'), pointBackgroundColor: getCssVariableValue('--stat-stamina-color'), tension: 0.3 },
          { label: 'Power', data: data.map(t => t.power), borderColor: getCssVariableValue('--stat-power-color'), pointBackgroundColor: getCssVariableValue('--stat-power-color'), tension: 0.3 },
          { label: 'Guts', data: data.map(t => t.guts), borderColor: getCssVariableValue('--stat-guts-color'), pointBackgroundColor: getCssVariableValue('--stat-guts-color'), tension: 0.3 },
          { label: 'Wit', data: data.map(t => t.wit), borderColor: getCssVariableValue('--stat-wit-color'), pointBackgroundColor: getCssVariableValue('--stat-wit-color'), tension: 0.3 }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { labels: { usePointStyle: true } } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }

  async function attach(opts) {
    const canvas = qs(opts.canvasSelector);
    const message = qs(opts.messageSelector);
    const tabEl = qs(opts.tabSelector);
    let chartInstance = null;

    async function loadAndRender() {
      const planId = (qs(opts.planIdSelector) || {}).value;
      if (!planId) return;
      try {
        const data = await fetchChartData(planId);
        if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
        if (data && data.length) {
          canvas.style.display = 'block';
          if (message) message.style.display = 'none';
          chartInstance = renderChart(canvas, data);
        } else {
          if (canvas) canvas.style.display = 'none';
          if (message) { message.style.display = 'block'; message.innerHTML = '<p class="text-muted fs-5">No progression data available for this plan.</p>'; }
        }
      } catch (err) {
        if (canvas) canvas.style.display = 'none';
        if (message) { message.style.display = 'block'; message.innerHTML = '<p class="text-danger">Could not load chart data.</p>'; }
        console.error('ProgressChart error', err);
      }
    }

    if (tabEl) {
      tabEl.addEventListener('shown.bs.tab', function () { loadAndRender(); });
    }

    // Expose manual trigger
    return { loadAndRender };
  }

  window.ProgressChart = { attach };
})();
