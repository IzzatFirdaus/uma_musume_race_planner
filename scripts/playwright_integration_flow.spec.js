const { test, expect } = require('@playwright/test');

test('user can create a plan and see stats panel update', async ({ page }) => {
  // Log all browser console output
  page.on('console', msg => console.log('[browser]', msg.type(), msg.text()));
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  await page.goto(url, { waitUntil: 'networkidle' });

  // Open create plan modal
  await page.click('#createPlanBtn');
  // Log window.bootstrap after page load and after click
  const bootstrapDefined = await page.evaluate(() => typeof window.bootstrap !== 'undefined');
  console.log('[diagnostic] window.bootstrap defined after click:', bootstrapDefined);
  // Try to force show modal via JS if not visible
  const forceShowResult = await page.evaluate(() => {
    const modalEl = document.getElementById('createPlanModal');
    if (!modalEl) return 'modal not found';
    if (typeof window.bootstrap !== 'undefined') {
      try {
        const modal = new window.bootstrap.Modal(modalEl);
        modal.show();
        return 'modal.show() called';
      } catch (e) {
        return 'modal.show() error: ' + e;
      }
    } else {
      return 'window.bootstrap not defined';
    }
  });
  console.log('[diagnostic] forceShowResult:', forceShowResult);
  // Take a screenshot after clicking Create New for diagnosis
  await page.screenshot({ path: 'debug_create_modal.png' });
  // Log modal computed style and class list for diagnosis
  const modalInfo = await page.evaluate(() => {
    const modal = document.getElementById('createPlanModal');
    if (!modal) return { found: false };
    const style = window.getComputedStyle(modal);
    return {
      found: true,
      display: style.display,
      visibility: style.visibility,
      opacity: style.opacity,
      classList: Array.from(modal.classList),
      ariaHidden: modal.getAttribute('aria-hidden'),
    };
  });
  console.log('[diagnostic] Modal info after click:', modalInfo);
  // Take another screenshot after waiting 500ms for animation
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'debug_create_modal_after_wait.png' });
  // Wait for Bootstrap modal to be fully shown (show class or aria-hidden="false")
  await page.waitForSelector('#createPlanModal.show, #createPlanModal[aria-hidden="false"]', { timeout: 10000 });
  await expect(page.locator('#createPlanModal')).toBeVisible();

  // Fill in trainee name
  const traineeName = 'IntegrationTest_' + Date.now();
  await page.fill('#quick_trainee_name', traineeName);
  await page.selectOption('#quick_career_stage', { index: 1 }).catch(()=>{});
  await page.selectOption('#quick_traineeClass', { index: 1 }).catch(()=>{});

  // Submit
  await page.click('#quickCreateSubmitBtn');
  await page.waitForTimeout(1000);

  // Log table HTML after plan creation
  let listHtml = await page.innerHTML('#planListBody');
  console.log('[diagnostic] #planListBody HTML after plan creation:', listHtml);

  // Poll for table to update and have at least one row
  let foundRow = false;
  let lastApiResult = null;
  for (let i = 0; i < 10; i++) {
    await page.waitForTimeout(500);
    listHtml = await page.innerHTML('#planListBody');
    lastApiResult = await page.evaluate(() => window.lastPlanCreateResult || null);
    if (listHtml.includes(traineeName)) {
      foundRow = true;
      break;
    }
    console.log(`[diagnostic] Poll ${i}: #planListBody HTML:`, listHtml);
    console.log(`[diagnostic] Poll ${i}: lastPlanCreateResult:`, lastApiResult);
  }
  if (!foundRow) {
    // Log API error if plan not found
    const apiError = await page.evaluate(() => window.lastPlanCreateError || null);
    console.log('[diagnostic] lastPlanCreateError:', apiError);
    console.log('[diagnostic] lastPlanCreateResult:', lastApiResult);
    throw new Error('Plan not found in table after creation');
  }

  // Check stats panel is visible
  await expect(page.locator('section.card[aria-labelledby="quickStatsTitle"]')).toBeVisible();
});
