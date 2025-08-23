const { chromium } = require('playwright');

(async () => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  const out = { url, steps: [], errors: [] };
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  page.on('console', msg => out.steps.push({ type: 'console', text: msg.text() }));
  page.on('pageerror', err => out.errors.push(String(err)));

  try {
    out.steps.push('goto');
    await page.goto(url, { waitUntil: 'networkidle' });

    // Test navbar navigation
    out.steps.push('navbar: plans');
    await page.click('nav a[href*="index.php"]');
    await page.waitForSelector('#planListBody', { timeout: 3000 });

    out.steps.push('navbar: stats');
    await page.click('nav a[href*="stats-panel.php"]');
    await page.waitForSelector('#statsPanel', { timeout: 3000 });

    // Test stats panel loads
    const statsText = await page.textContent('#statsPanel').catch(()=>null);
    out.steps.push({ statsPanelLoaded: !!statsText });

    // Go back to plans
    await page.click('nav a[href*="index.php"]');
    await page.waitForSelector('#planListBody', { timeout: 3000 });

    // Test passed if stats panel loaded
    const pass = !!statsText;
    console.log(JSON.stringify({ pass, out }, null, 2));
    await browser.close();
    process.exit(pass ? 0 : 2);
  } catch (err) {
    console.error('ERROR', err);
    await browser.close();
    process.exit(3);
  }
})();
