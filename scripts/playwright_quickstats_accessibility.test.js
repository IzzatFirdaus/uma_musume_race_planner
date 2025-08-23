// scripts/playwright_quickstats_accessibility.test.js
const { test, expect } = require('@playwright/test');
const { injectAxe, checkA11y } = require('axe-playwright');

test.describe('Quick Stats Accessibility', () => {
  test('should have no accessibility violations and proper ARIA', async ({ page }) => {
    await page.goto('http://localhost/index.php');
    await injectAxe(page);
    // Focus Quick Stats panel
    const quickStats = await page.locator('section[aria-labelledby="quickStatsTitle"]');
    await expect(quickStats).toBeVisible();
    // Check ARIA attributes
    await expect(quickStats).toHaveAttribute('aria-labelledby', 'quickStatsTitle');
    await expect(page.locator('#quickStatsTitle')).toBeVisible();
    // Check labels and numbers are visible and have sufficient contrast
    const labels = await page.locator('.quick-stats-label');
    const numbers = await page.locator('.quick-stats-number');
    await expect(labels).toHaveCount(3);
    await expect(numbers).toHaveCount(3);
    // Run Axe accessibility checks
    await checkA11y(page, 'section[aria-labelledby="quickStatsTitle"]', {
      detailedReport: true,
      detailedReportOptions: { html: true }
    });
  });
});
