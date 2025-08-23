const { test, expect } = require('@playwright/test');

test('user can edit and delete a plan', async ({ page }) => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  await page.goto(url, { waitUntil: 'networkidle' });

  // Create a plan first
  await page.click('#createPlanBtn');
  await expect(page.locator('#createPlanModal')).toBeVisible();
  const traineeName = 'EditDeleteTest_' + Date.now();
  await page.fill('#quick_trainee_name', traineeName);
  await page.selectOption('#quick_career_stage', { index: 1 }).catch(()=>{});
  await page.selectOption('#quick_traineeClass', { index: 1 }).catch(()=>{});
  await page.click('#quickCreateSubmitBtn');
  await page.waitForTimeout(1000);

  // Edit the plan
  const editBtn = await page.$(`#planListBody button.edit-btn:has-text('${traineeName.split('_')[0]}')`);
  if (editBtn) {
    await editBtn.click();
    await expect(page.locator('#planDetailsModal')).toBeVisible();
    const newTitle = 'Edited_' + Date.now();
    await page.fill('#plan_title', newTitle);
    await page.click('#planDetailsForm button[type="submit"]');
    await page.waitForTimeout(1000);
    const listHtml = await page.innerHTML('#planListBody');
    expect(listHtml).toContain(newTitle);
  }

  // Delete the plan
  const rows = await page.$$('#planListBody tr');
  let deleted = false;
  for (const r of rows) {
    const text = await r.innerText();
    if (text.includes('Edited_') || text.includes(traineeName)) {
      const delBtn = await r.$('button.delete-btn');
      if (delBtn) {
        page.on('dialog', dialog => dialog.accept());
        await delBtn.click();
        deleted = true;
        break;
      }
    }
  }
  expect(deleted).toBe(true);
});
