const { test, expect } = require('@playwright/test');

test('navbar navigation and stats panel loads', async ({ page }) => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  await page.goto(url, { waitUntil: 'networkidle' });

  // Test navbar navigation to plans
  await page.click('nav a[href*="index.php"]');
  await expect(page.locator('#planListBody')).toBeVisible();

  // Test navbar navigation to stats panel
  await page.click('nav a[href*="stats-panel.php"]');
  await expect(page.locator('#statsPanel')).toBeVisible();

  // Test stats panel loads
  const statsText = await page.textContent('#statsPanel');
  expect(statsText.length).toBeGreaterThan(0);

  // Go back to plans
  await page.click('nav a[href*="index.php"]');
  await expect(page.locator('#planListBody')).toBeVisible();
});
