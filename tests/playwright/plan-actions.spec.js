import { test, expect } from "@playwright/test";

const BASE = "http://localhost/uma-musume-planner-laravel/public/";

test.describe("Plan actions (view, edit, delete)", () => {
    test("view inline, open edit modal, and delete with SweetAlert2", async ({
        page,
    }) => {
        await page.goto(BASE, { waitUntil: "networkidle" });

        // Ensure plan list is present
        await page.waitForSelector("#plan-list-body tr");
        const rows = page.locator("#plan-list-body tr");
        const initialCount = await rows.count();
        expect(initialCount).toBeGreaterThan(0);

        const firstRow = rows.first();

        // VIEW INLINE - call client helper directly to avoid Livewire re-render race
        const planId = await firstRow
            .locator(".view-inline-btn[data-id]")
            .getAttribute("data-id");
        await page.evaluate((id) => {
            // eslint-disable-next-line no-undef
            return window.UmaPlanner?.fetchAndPopulatePlan(id, true);
        }, planId);

        // Wait for inline details to become visible
        await page.waitForSelector("#planInlineDetails", {
            state: "visible",
            timeout: 5000,
        });
        await expect(page.locator("#planInlineDetails")).toBeVisible();
        // Close inline view
        await page.locator("#closeInlineDetailsBtn").click();
        await page.waitForSelector("#planInlineDetails", {
            state: "hidden",
            timeout: 5000,
        });

        // EDIT (open modal)
        // EDIT - open modal via client helper
        await page.evaluate((id) => {
            // eslint-disable-next-line no-undef
            return window.UmaPlanner?.fetchAndPopulatePlan(id, false);
        }, planId);
        await page.waitForSelector(
            '#planDetailsModal.show, #planDetailsModal[aria-hidden="false"]',
            { timeout: 5000 },
        );
        await expect(page.locator("#planDetailsModal")).toBeVisible();
        // Close the modal
        await page.locator("#planDetailsModal button.btn-secondary").click();
        await page.waitForSelector("#planDetailsModal", {
            state: "hidden",
            timeout: 5000,
        });

        // DELETE (SweetAlert2)
        // Re-query rows because modal actions might refresh DOM
        const rowsBeforeDelete = page.locator("#plan-list-body tr");
        const beforeCount = await rowsBeforeDelete.count();
        expect(beforeCount).toBeGreaterThan(0);

        // DELETE - call client delete helper to trigger SweetAlert2
        await page.evaluate((id) => {
            // eslint-disable-next-line no-undef
            window.UmaPlanner?.handleDeletePlan(id);
        }, planId);

        // Wait for SweetAlert2 popup
        await page.waitForSelector(".swal2-popup", { timeout: 5000 });
        await expect(page.locator(".swal2-popup")).toBeVisible();

        // Confirm deletion
        await page.locator(".swal2-confirm").click();

        // Wait for the list to decrease by at least 1 row
        await expect(page.locator("#plan-list-body tr")).toHaveCount(
            beforeCount - 1,
            { timeout: 7000 },
        );
    });
});
