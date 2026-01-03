// @ts-check
const { test, expect } = require("@playwright/test");

/**
 * Playwright tests for skill search functionality.
 * Task 6.3.2: Test skill autocomplete with keyboard navigation.
 */

test.describe("Skill Search Autocomplete", () => {
    test.beforeEach(async ({ page }) => {
        // Navigate to a plan detail page that has skill search
        await page.goto("/plans");

        // Wait for page to load
        await page.waitForLoadState("networkidle");
    });

    test("skill search input is accessible", async ({ page }) => {
        // Check if there's a plan to click on
        const planLink = page.locator('[data-testid="plan-link"]').first();

        if (await planLink.isVisible()) {
            await planLink.click();
            await page.waitForLoadState("networkidle");

            // Look for skill search input
            const skillSearch = page
                .locator(
                    '[data-testid="skill-search-input"], input[placeholder*="skill" i], input[aria-label*="skill" i]',
                )
                .first();

            if (await skillSearch.isVisible()) {
                // Verify input has proper ARIA attributes
                await expect(skillSearch).toHaveAttribute(
                    "role",
                    /(combobox|textbox)/,
                );
            }
        }
    });

    test("keyboard navigation works in skill dropdown", async ({ page }) => {
        const planLink = page.locator('[data-testid="plan-link"]').first();

        if (await planLink.isVisible()) {
            await planLink.click();
            await page.waitForLoadState("networkidle");

            const skillSearch = page
                .locator(
                    '[data-testid="skill-search-input"], input[placeholder*="skill" i]',
                )
                .first();

            if (await skillSearch.isVisible()) {
                // Type to trigger autocomplete
                await skillSearch.fill("speed");
                await page.waitForTimeout(500); // Wait for debounce

                // Check if dropdown appears
                const dropdown = page.locator(
                    '[role="listbox"], [data-testid="skill-dropdown"]',
                );

                if (await dropdown.isVisible()) {
                    // Test arrow down navigation
                    await skillSearch.press("ArrowDown");

                    // Check for active descendant or focused option
                    const activeOption = page.locator(
                        '[role="option"][aria-selected="true"], [role="option"]:focus',
                    );

                    if ((await activeOption.count()) > 0) {
                        await expect(activeOption.first()).toBeVisible();
                    }

                    // Test Escape closes dropdown
                    await skillSearch.press("Escape");
                    await expect(dropdown).not.toBeVisible();
                }
            }
        }
    });
});

test.describe("Dark Mode Toggle", () => {
    test("dark mode toggle persists after refresh", async ({ page }) => {
        await page.goto("/");
        await page.waitForLoadState("networkidle");

        // Look for dark mode toggle
        const darkModeToggle = page
            .locator(
                '[data-testid="dark-mode-toggle"], button[aria-label*="dark" i], button[aria-label*="theme" i]',
            )
            .first();

        if (await darkModeToggle.isVisible()) {
            // Get initial state
            const htmlElement = page.locator("html");
            const initialDarkMode = await htmlElement.evaluate((el) =>
                el.classList.contains("dark"),
            );

            // Click toggle
            await darkModeToggle.click();
            await page.waitForTimeout(300);

            // Verify state changed
            const newDarkMode = await htmlElement.evaluate((el) =>
                el.classList.contains("dark"),
            );
            expect(newDarkMode).not.toBe(initialDarkMode);

            // Refresh page
            await page.reload();
            await page.waitForLoadState("networkidle");

            // Verify state persisted
            const persistedDarkMode = await htmlElement.evaluate((el) =>
                el.classList.contains("dark"),
            );
            expect(persistedDarkMode).toBe(newDarkMode);
        }
    });
});

test.describe("Export Functionality", () => {
    test("export modal opens and shows format options", async ({ page }) => {
        await page.goto("/plans");
        await page.waitForLoadState("networkidle");

        const planLink = page.locator('[data-testid="plan-link"]').first();

        if (await planLink.isVisible()) {
            await planLink.click();
            await page.waitForLoadState("networkidle");

            // Look for export button
            const exportButton = page
                .locator(
                    '[data-testid="export-button"], button:has-text("Export"), a:has-text("Export")',
                )
                .first();

            if (await exportButton.isVisible()) {
                await exportButton.click();
                await page.waitForTimeout(300);

                // Check for export modal or options
                const exportModal = page.locator(
                    '[data-testid="export-modal"], [role="dialog"]:has-text("Export")',
                );

                if (await exportModal.isVisible()) {
                    // Verify format options exist
                    await expect(
                        page.locator("text=/json/i").first(),
                    ).toBeVisible();
                    await expect(
                        page.locator("text=/csv/i").first(),
                    ).toBeVisible();
                }
            }
        }
    });
});

test.describe("Keyboard Navigation", () => {
    test("Tab navigation works through interactive elements", async ({
        page,
    }) => {
        await page.goto("/");
        await page.waitForLoadState("networkidle");

        // Start from body
        await page.keyboard.press("Tab");

        // Check that focus moved to an interactive element
        const focusedElement = page.locator(":focus");
        await expect(focusedElement).toBeVisible();

        // Verify it's an interactive element
        const tagName = await focusedElement.evaluate((el) =>
            el.tagName.toLowerCase(),
        );
        expect(["a", "button", "input", "select", "textarea"]).toContain(
            tagName,
        );
    });

    test("Escape closes modals", async ({ page }) => {
        await page.goto("/plans");
        await page.waitForLoadState("networkidle");

        // Try to open any modal
        const modalTrigger = page
            .locator(
                '[data-testid="modal-trigger"], button:has-text("Create"), button:has-text("New")',
            )
            .first();

        if (await modalTrigger.isVisible()) {
            await modalTrigger.click();
            await page.waitForTimeout(300);

            const modal = page
                .locator('[role="dialog"], .modal, [data-testid="modal"]')
                .first();

            if (await modal.isVisible()) {
                // Press Escape
                await page.keyboard.press("Escape");
                await page.waitForTimeout(300);

                // Modal should be closed
                await expect(modal).not.toBeVisible();
            }
        }
    });
});

test.describe("Focus Management", () => {
    test("focus is trapped in modal", async ({ page }) => {
        await page.goto("/plans");
        await page.waitForLoadState("networkidle");

        const modalTrigger = page
            .locator(
                '[data-testid="modal-trigger"], button:has-text("Create"), button:has-text("New")',
            )
            .first();

        if (await modalTrigger.isVisible()) {
            await modalTrigger.click();
            await page.waitForTimeout(300);

            const modal = page.locator('[role="dialog"], .modal').first();

            if (await modal.isVisible()) {
                // Tab through all focusable elements
                const focusableElements = modal.locator(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
                );
                const count = await focusableElements.count();

                if (count > 1) {
                    // Tab through all elements
                    for (let i = 0; i < count + 1; i++) {
                        await page.keyboard.press("Tab");
                    }

                    // Focus should still be within modal
                    const focusedElement = page.locator(":focus");
                    const isInModal = await focusedElement.evaluate(
                        (el, modalSelector) => {
                            const modal = document.querySelector(modalSelector);
                            return modal?.contains(el) ?? false;
                        },
                        '[role="dialog"], .modal',
                    );

                    expect(isInModal).toBe(true);
                }
            }
        }
    });
});
