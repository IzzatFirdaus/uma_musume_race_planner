const { test, expect } = require('@playwright/test');

test('stats panel displays correct elements and values', async ({ page }) => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  await page.goto(url, { waitUntil: 'networkidle' });

  // Stats panel should be visible
  await expect(page.locator('section.card[aria-labelledby="quickStatsTitle"]')).toBeVisible();

  // Check stats numbers
  const plans = await page.textContent('#statsPlans');
  const active = await page.textContent('#statsActive');
  const finished = await page.textContent('#statsFinished');
  expect(Number(plans)).not.toBeNaN();
  expect(Number(active)).not.toBeNaN();
  expect(Number(finished)).not.toBeNaN();

  // Chart should be present
  await expect(page.locator('#statsChart')).toBeVisible();
});
