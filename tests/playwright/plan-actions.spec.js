import { test, expect } from "@playwright/test";

const BASE = "http://127.0.0.1:8000/";

test.describe("Plan actions (view, edit, delete)", () => {
    test("view details page, open edit modal, and delete with SweetAlert2", async ({
        page,
    }) => {
        await page.goto(BASE, { waitUntil: "networkidle" });

        // Ensure plan list is present
        await page.waitForSelector("#plan-list-body tr");
        const rows = page.locator("#plan-list-body tr");
        const initialCount = await rows.count();
        expect(initialCount).toBeGreaterThan(0);

        const firstRow = rows.first();

        // VIEW DETAILS - Navigate to the new plan details page
        const viewBtn = firstRow.locator(".view-details-btn[data-id]");
        const planId = await viewBtn.getAttribute("data-id");

        // Click the view button to navigate to the plan view page
        await viewBtn.click();

        // Wait for navigation to the plan view page
        await page.waitForURL(`**/plans/${planId}/view`, { timeout: 5000 });

        // Verify we're on the plan view page (read-only mode)
        await expect(page).toHaveURL(`${BASE}plans/${planId}/view`);
        await expect(page.locator('h5:has-text("View Plan")')).toBeVisible();
        await expect(
            page.locator('.badge:has-text("View Mode")'),
        ).toBeVisible();

        // Verify fields are in view mode (read-only)
        await expect(page.locator('input[name="plan_title"]')).toHaveAttribute(
            "readonly",
            "",
        );

        // Navigate to edit mode using the Edit Plan button
        await page.getByRole("link", { name: "Edit Plan" }).click();
        await page.waitForURL(`**/plans/${planId}/edit`, { timeout: 5000 });

        // Verify we're now in edit mode
        await expect(page).toHaveURL(`${BASE}plans/${planId}/edit`);
        await expect(page.locator('h5:has-text("Edit Plan")')).toBeVisible();
        await expect(
            page.locator('.badge:has-text("Edit Mode")'),
        ).toBeVisible();

        // Verify fields are now editable (no readonly attribute)
        await expect(
            page.locator('input[name="plan_title"]'),
        ).not.toHaveAttribute("readonly");

        // Navigate back to dashboard
        await page.getByRole("link", { name: "Back to Dashboard" }).click();
        await page.waitForURL(BASE, { timeout: 5000 });

        // Test the edit button on the plan list
        const editBtn = firstRow.locator(".edit-btn[data-id]");
        await editBtn.click();

        // Wait for navigation to the plan edit page
        await page.waitForURL(`**/plans/${planId}/edit`, { timeout: 5000 });

        // Verify we're on the edit page
        await expect(page).toHaveURL(`${BASE}plans/${planId}/edit`);
        await expect(page.locator('h5:has-text("Edit Plan")')).toBeVisible();

        // Navigate back to dashboard for delete test
        await page.getByRole("link", { name: "Back to Dashboard" }).click();
        await page.waitForURL(BASE, { timeout: 5000 });
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
