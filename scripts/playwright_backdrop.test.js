const { chromium } = require('playwright');

(async () => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  const out = { url, steps: [], pageErrors: [], console: [] };
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  page.on('console', msg => out.console.push({ type: msg.type(), text: msg.text() }));
  page.on('pageerror', err => out.pageErrors.push(String(err)));

  try {
    out.steps.push('goto');
    await page.goto(url, { waitUntil: 'networkidle' });

    // Open the create modal (reliable control) then close with the X button
    out.steps.push('open modal');
    await page.click('#createPlanBtn');
    await page.waitForSelector('#createPlanModal', { state: 'visible', timeout: 3000 });

    out.steps.push('close modal via X');
    // Click the first .btn-close inside the visible modal
    await page.click('#createPlanModal .btn-close');

    // Wait briefly for cleanup and Bootstrap events
    await page.waitForTimeout(300);

    // Check for any remaining backdrops or modal-open on body
    const result = await page.evaluate(() => {
      const backdrops = Array.from(document.querySelectorAll('.modal-backdrop'));
      const bodyOpen = document.body.classList.contains('modal-open');
      return { backdrops: backdrops.length, bodyOpen };
    });

    out.steps.push({ backdropCount: result.backdrops, bodyOpen: result.bodyOpen });

    const pass = result.backdrops === 0 && result.bodyOpen === false;
    console.log(JSON.stringify({ pass, out }, null, 2));
    await browser.close();
    process.exit(pass ? 0 : 2);
  } catch (err) {
    console.error('ERROR', err);
    await browser.close();
    process.exit(3);
  }
})();
