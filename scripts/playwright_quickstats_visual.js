const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async () => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  const outDir = path.join(__dirname, 'screenshots');
  try { fs.mkdirSync(outDir, { recursive: true }); } catch (e) {}

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    await page.goto(url, { waitUntil: 'networkidle' });

    // Force dark theme
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'dark');
      document.body.classList.add('dark-mode');
    });

    // Wait for stats panel
    await page.waitForSelector('#statsPlans', { timeout: 5000 });

    // Locate the card that contains the stats
    const cardHandle = await page.$('#statsPlans');
    const card = await cardHandle.evaluateHandle((el) => el.closest('.card'));

    // BEFORE: simulate old problematic styling (link color for numbers, no shadow)
    await page.evaluate(() => {
      const nodes = document.querySelectorAll('.quick-stats-number');
      const linkColor = getComputedStyle(document.documentElement).getPropertyValue('--color-link') || '#90CAF9';
      nodes.forEach(n => {
        n.dataset._origColor = n.style.color || '';
        n.dataset._origTextShadow = n.style.textShadow || '';
        n.style.color = linkColor.trim();
        n.style.textShadow = 'none';
      });
      // dim the labels as older behavior might have done
      document.querySelectorAll('.card .text-muted').forEach(l => l.style.color = 'rgba(255,255,255,0.45)');
    });

    // Capture before screenshot of the whole card
    const cardEl = await card.asElement();
    const beforePath = path.join(outDir, 'quickstats_before.png');
    await cardEl.screenshot({ path: beforePath });

    // AFTER: restore proper styling (remove inline color so CSS rules apply) and apply subtle shadow
    await page.evaluate(() => {
      const nodes = document.querySelectorAll('.quick-stats-number');
      nodes.forEach(n => {
        // restore or set to text token
        n.style.color = '';
        n.style.textShadow = '0 2px 6px rgba(0,0,0,0.65)';
      });
      // restore labels
      document.querySelectorAll('.card .text-muted').forEach(l => l.style.color = '');
    });

    // Wait a brief moment to allow repaint
    await page.waitForTimeout(200);

    const afterPath = path.join(outDir, 'quickstats_after.png');
    await cardEl.screenshot({ path: afterPath });

    console.log(JSON.stringify({ before: beforePath, after: afterPath }, null, 2));
    await browser.close();
    process.exit(0);
  } catch (err) {
    console.error('ERROR', err);
    await browser.close();
    process.exit(2);
  }
})();
