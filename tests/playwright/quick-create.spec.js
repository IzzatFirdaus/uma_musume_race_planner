import { test, expect } from "@playwright/test";

const BASE = "http://127.0.0.1:8000/";

// Helper to open the quick-create modal by clicking an element
async function openQuickCreateModalBy(page, selector) {
    await page.click(selector);
    await page.waitForSelector(
        "#createPlanModal.show, #createPlanModal[style*='display: block']",
        { timeout: 5000 },
    );
}

test.describe("Quick Create Plan modal", () => {
    test("opens from navbar and creates a plan successfully", async ({
        page,
    }) => {
        await page.goto(BASE, { waitUntil: "networkidle" });

        // Open via navbar link
        await openQuickCreateModalBy(page, "#newPlanBtn");

        // Fill form
        const name = `Quick Horse ${Math.floor(Math.random() * 100000)}`;
        await page.fill("#quick_trainee_name", name);
        await page.selectOption("#quick_career_stage", "junior");
        await page.selectOption("#quick_traineeClass", "beginner");
        await page.fill("#quick_race_name", "Test Quick Race");

        // Submit
        await page.click("#quickCreatePlanForm button[type=submit]");

        // Modal should close
        await page.waitForSelector("#createPlanModal.show", {
            state: "detached",
            timeout: 7000,
        });

        // Wait for a planUpdated-driven refresh cycle to complete by observing a DOM change
        await page
            .waitForFunction(
                () => {
                    const tbody = document.querySelector("#plan-list-body");
                    if (!tbody) return false;
                    const now = Date.now();
                    const last = tbody.getAttribute("data-last-refresh");
                    // Livewire render will change innerHTML; we mark a timestamp attribute when it happens in our JS
                    return last && Number(last) > now - 5000;
                },
                { timeout: 7000 },
            )
            .catch(() => {});

        // The list should refresh and contain the new name
        await expect(page.locator("#plan-list-body")).toContainText(name, {
            timeout: 10000,
        });
    });

    test("opens from dashboard Create New button", async ({ page }) => {
        await page.goto(BASE, { waitUntil: "networkidle" });

        // Open via dashboard button
        await openQuickCreateModalBy(page, "#createPlanBtn");

        // Close via Close button
        await page.click("#createPlanModal .btn.btn-secondary");
        await page.waitForSelector("#createPlanModal.show", {
            state: "detached",
            timeout: 5000,
        });
    });
});
