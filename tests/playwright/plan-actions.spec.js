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
        let planId = await viewBtn.getAttribute("data-id");

        // Click the view button to navigate to the plan view page
        await viewBtn.click();

        // Wait for navigation to the plan view page (use a tolerant assertion)
        await expect(page).toHaveURL(`${BASE}plans/${planId}/view`, {
            timeout: 10000,
        });

        // Re-read the actual plan id from the current URL to avoid stale id issues
        // (some DOM refreshes or server-side redirects can change ordering).
        const viewUrlMatch = page.url().match(/\/plans\/(\d+)\/view/);
        if (viewUrlMatch) {
            // Use the canonical id from the actual page URL for subsequent steps
            // (this prevents mismatches when rows reorder between requests).
            // eslint-disable-next-line no-unused-vars
            planId = viewUrlMatch[1];
        }
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
        // Click without waiting for navigation to finish (handle SPA or plain navigation)
        await page
            .getByRole("link", { name: "Edit Plan" })
            .click({ noWaitAfter: true });

        // The navigation to edit may be SPA-like; wait for the edit page UI instead
        await page.waitForSelector('h5:has-text("Edit Plan")', {
            timeout: 10000,
        });
        await expect(page.locator('h5:has-text("Edit Plan")')).toBeVisible();
        await expect(
            page.locator('.badge:has-text("Edit Mode")'),
        ).toBeVisible();

        // Verify fields are now editable (no readonly attribute)
        await expect(
            page.locator('input[name="plan_title"]'),
        ).not.toHaveAttribute("readonly");

        // Navigate back to dashboard - some runs are SPA-like; click without waiting
        // for navigation and explicitly wait for the dashboard UI to appear.
        await page
            .getByRole("link", { name: "Back to Dashboard" })
            .click({ noWaitAfter: true });
        await page.waitForSelector("#plan-list-body", { timeout: 10000 });

        // Re-query rows after navigation (old reference may be stale)
        const freshRows = page.locator("#plan-list-body tr");
        await expect(freshRows.first()).toBeVisible({ timeout: 5000 });

        // Test the edit button on the plan list
        const freshFirstRow = freshRows.first();
        const editBtn = freshFirstRow.locator(".edit-btn[data-id]");
        await editBtn.click();

        // Wait for navigation to the plan edit page (tolerant assertion)
        await expect(page).toHaveURL(`${BASE}plans/${planId}/edit`, {
            timeout: 10000,
        });
        await expect(page.locator('h5:has-text("Edit Plan")')).toBeVisible();

        // Navigate back to dashboard for delete test (wait for plan list to re-appear)
        await page
            .getByRole("link", { name: "Back to Dashboard" })
            .click({ noWaitAfter: true });
        await page.waitForSelector("#plan-list-body", { timeout: 10000 });
        await page.waitForSelector("#planDetailsModal", {
            state: "hidden",
            timeout: 5000,
        });

        // DELETE (SweetAlert2)
        // Re-query rows because modal actions might refresh DOM
        const rowsBeforeDelete = page.locator("#plan-list-body tr");
        const beforeCount = await rowsBeforeDelete.count();
        expect(beforeCount).toBeGreaterThan(0);

        // DELETE - click the delete button for the plan to trigger SweetAlert2
        const deleteBtn = page.locator(`.delete-btn[data-id="${planId}"]`);
        await expect(deleteBtn).toBeVisible({ timeout: 5000 });

        // Allow native confirm dialogs to be auto-accepted if SweetAlert2 is not present.
        page.once("dialog", (dialog) => dialog.accept());

        await deleteBtn.click();

        // Wait for SweetAlert2 popup (confirmation dialog) if present; otherwise native confirm was used.
        const swalPopup = await page
            .waitForSelector(".swal2-popup", { timeout: 5000 })
            .catch(() => null);
        if (swalPopup) {
            await expect(page.locator(".swal2-popup")).toBeVisible();
            // Confirm deletion in SweetAlert2
            await page.locator(".swal2-confirm").click();
        }

        // Wait for SweetAlert2 popup (confirmation dialog) - allow more time for async operations
        await page.waitForSelector(".swal2-popup", { timeout: 10000 });
        await expect(page.locator(".swal2-popup")).toBeVisible();

        // Confirm deletion
        await page.locator(".swal2-confirm").click();

        // Wait for the specific deleted plan row to be removed (more robust than raw row counts)
        await expect(
            page.locator(`.delete-btn[data-id="${planId}"]`),
        ).toHaveCount(0, {
            timeout: 7000,
        });
    });
});
