const { chromium } = require('playwright');

(async () => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/';
  const out = { url, console: [], pageErrors: [], steps: [] };
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  page.on('console', msg => out.console.push({ type: msg.type(), text: msg.text() }));
  page.on('pageerror', err => out.pageErrors.push(String(err)));

  try {
    out.steps.push('goto');
    await page.goto(url, { waitUntil: 'networkidle' });

    // wait for plan list table
    try {
      await page.waitForSelector('#planListBody', { timeout: 5000 });
      out.steps.push('found planListBody');
    } catch (e) {
      out.steps.push('planListBody not found');
    }

    // try click first edit button
    const edit = await page.$('#planListBody .edit-btn');
    if (edit) {
      out.steps.push('click edit');
      await edit.click();
      // wait briefly for modal to appear
      try {
        await page.waitForSelector('#planDetailsModal.show, #planDetailsModal[style*="display: block"], #planDetailsModal[aria-hidden="false"]', { timeout: 5000 });
        out.steps.push('modal shown');
      } catch (e) {
        out.steps.push('modal not shown');
      }
    } else {
      out.steps.push('no edit button found');
    }

    // give any async work a moment
    await page.waitForTimeout(500);
    console.log(JSON.stringify(out, null, 2));
    await browser.close();
    process.exit(out.pageErrors.length ? 2 : 0);
  } catch (err) {
    console.error('ERROR', err);
    await browser.close();
    process.exit(3);
  }
})();
