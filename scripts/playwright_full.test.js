const { chromium } = require('playwright');

(async () => {
  const url = process.env.TEST_URL || 'http://localhost/uma_musume_race_planner/public/index.php';
  const out = { url, errors: [], steps: [] };
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  page.on('console', msg => out.steps.push({ type: 'console', text: msg.text() }));
  page.on('pageerror', err => out.errors.push(String(err)));

  try {
    out.steps.push('goto');
    await page.goto(url, { waitUntil: 'networkidle' });

    // Open create modal
    out.steps.push('open create modal');
    await page.click('#createPlanBtn');
    await page.waitForSelector('#createPlanModal.show, #createPlanModal[aria-hidden="false"]', { timeout: 3000 });

    // Fill quick create form
    const traineeName = 'Test Trainee ' + Date.now();
    await page.fill('#quick_trainee_name', traineeName);
    // choose first non-disabled career stage and class
    await page.selectOption('#quick_career_stage', { index: 1 }).catch(()=>{});
    await page.selectOption('#quick_traineeClass', { index: 1 }).catch(()=>{});

    out.steps.push('submit create');
    await Promise.all([
      page.waitForEvent('dialog', { timeout: 1000 }).catch(()=>{}),
      page.click('#quickCreateSubmitBtn')
    ]);

    // Wait for success message or modal hide then refresh
    await page.waitForTimeout(800);

    // Refresh list by waiting for planUpdated event handling (poll table)
    out.steps.push('wait for new plan in list');
    let found = false;
    for (let i = 0; i < 10; i++) {
      const html = await page.innerHTML('#planListBody').catch(()=>null);
      if (html && html.includes(traineeName)) { found = true; break; }
      await page.waitForTimeout(300);
    }
    out.steps.push({ foundNewPlan: found });

    if (!found) {
      throw new Error('Created plan not found in list');
    }

    // Click Edit on the newly created plan
    out.steps.push('click edit on created plan');
    const editBtn = await page.$(`#planListBody button.edit-btn:has-text("${traineeName.split(' ')[0]}")`);
    // fallback: click the first edit button that contains the traineeName
    const editButtons = await page.$$('#planListBody button.edit-btn');
    let targetEdit = null;
    for (const btn of editButtons) {
      const id = await btn.getAttribute('data-id');
      const rowHtml = await btn.evaluate((b) => b.closest('tr').innerText);
      if (rowHtml && rowHtml.includes(traineeName.split(' ')[0])) { targetEdit = btn; break; }
    }
    if (!targetEdit) targetEdit = editButtons[0];
    if (!targetEdit) throw new Error('No edit button available');

    await targetEdit.click();
    await page.waitForSelector('#planDetailsModal.show, #planDetailsModal[aria-hidden="false"]', { timeout: 3000 });
    out.steps.push('modal shown for edit');

    // Change title
    const newTitle = 'Edited Title ' + Date.now();
    await page.fill('#plan_title', newTitle);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle', timeout: 3000 }).catch(()=>{}),
      page.click('#planDetailsForm button[type="submit"]')
    ]).catch(()=>{});

    // Wait a moment and verify table contains the new title
    await page.waitForTimeout(800);
    const listHtml = await page.innerHTML('#planListBody');
    out.steps.push({ editedFound: listHtml.includes(newTitle) });

    // Delete the plan (find delete button in the same row as edited plan)
    out.steps.push('delete edited plan');
    const rows = await page.$$('#planListBody tr');
    let deleted = false;
    for (const r of rows) {
      const text = await r.innerText();
      if (text.includes(newTitle) || text.includes(traineeName)) {
        const delBtn = await r.$('button.delete-btn');
        if (delBtn) {
          // confirm dialog appears - accept it
          page.on('dialog', dialog => dialog.accept());
          await delBtn.click();
          deleted = true;
          break;
        }
      }
    }
    if (!deleted) throw new Error('Could not find delete button for created plan');

    // Wait for removal
    await page.waitForTimeout(800);
    const finalHtml = await page.innerHTML('#planListBody');
    out.steps.push({ stillPresent: finalHtml.includes(traineeName) || finalHtml.includes(newTitle) });

    console.log(JSON.stringify(out, null, 2));
    await browser.close();
    process.exit(out.errors.length ? 2 : 0);
  } catch (err) {
    console.error('ERROR', err);
    await browser.close();
    process.exit(3);
  }
})();
