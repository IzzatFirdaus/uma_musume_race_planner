const { test, expect } = require('@playwright/test');

test('user can create a plan and see stats panel update', async ({ page }) => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  await page.goto(url, { waitUntil: 'networkidle' });

  // Open create plan modal
  await page.click('#createPlanBtn');
  await expect(page.locator('#createPlanModal')).toBeVisible();

  // Fill in trainee name
  const traineeName = 'IntegrationTest_' + Date.now();
  await page.fill('#quick_trainee_name', traineeName);
  await page.selectOption('#quick_career_stage', { index: 1 }).catch(()=>{});
  await page.selectOption('#quick_traineeClass', { index: 1 }).catch(()=>{});

  // Submit
  await page.click('#quickCreateSubmitBtn');
  await page.waitForTimeout(1000);

  // Check plan appears in list
  const listHtml = await page.innerHTML('#planListBody');
  expect(listHtml).toContain(traineeName);

  // Check stats panel is visible
  await expect(page.locator('section.card[aria-labelledby="quickStatsTitle"]')).toBeVisible();
});
