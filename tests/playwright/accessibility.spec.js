// @ts-check
import { expect, test } from "@playwright/test";
import AxeBuilder, { checkA11y, injectAxe } from "@axe-core/playwright";

async function openFirstPlanDetail(page) {
    await page.goto("/plans", { waitUntil: "networkidle" });

    const planLink = page.locator('[data-testid="plan-link"], a[href*="/plans/"]').first();

    if (!(await planLink.count())) {
        return false;
    }

    await planLink.click();
    await page.waitForLoadState("networkidle");

    return true;
}

async function runA11y(page, selector = undefined) {
    await injectAxe(page);

    if (selector) {
        await checkA11y(page, selector, {
            includedImpacts: ["critical", "serious"],
        });

        return;
    }

    const results = await new AxeBuilder({ page })
        .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"])
        .analyze();

    const seriousViolations = results.violations.filter((violation) =>
        ["critical", "serious"].includes(violation.impact),
    );

    expect(seriousViolations).toHaveLength(0);
}

test.describe("Accessibility audit", () => {
    test("dashboard is accessible", async ({ page }) => {
        await page.goto("/", { waitUntil: "networkidle" });
        await runA11y(page);
    });

    test("plan editor is accessible", async ({ page }) => {
        const opened = await openFirstPlanDetail(page);

        if (!opened) {
            test.skip(true, "No plan detail page was available in this environment.");
        }

        const editLink = page.locator('a[href$="/edit"], [data-testid="plan-edit-link"]').first();

        if (await editLink.count()) {
            await editLink.click();
            await page.waitForLoadState("networkidle");
        }

        await runA11y(page);
    });

    test("import wizard is accessible", async ({ page }) => {
        await page.goto("/import", { waitUntil: "networkidle" });
        await runA11y(page, '[data-testid="import-wizard"], main');
    });

    test("export modal is accessible", async ({ page }) => {
        const opened = await openFirstPlanDetail(page);

        if (!opened) {
            test.skip(true, "No plan detail page was available in this environment.");
        }

        const exportButton = page
            .locator('[data-testid="export-button"], [data-testid="export-btn"], button:has-text("Export")')
            .first();

        if (!(await exportButton.count())) {
            test.skip(true, "No export trigger was available on the current plan page.");
        }

        await exportButton.click();
        await page.waitForLoadState("networkidle");

        await runA11y(page, '[data-testid="export-modal"]');
    });
});