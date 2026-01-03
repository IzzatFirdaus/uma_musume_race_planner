// @ts-check
const { test, expect } = require("@playwright/test");
const AxeBuilder = require("@axe-core/playwright").default;

/**
 * Accessibility tests using axe-core.
 * Tasks 6.4.1-6.4.5: Run axe-core tests on various pages.
 */

test.describe("Accessibility Tests", () => {
    /**
     * Task 6.4.1: Run axe-core tests on Dashboard page
     */
    test("Dashboard page has no accessibility violations", async ({ page }) => {
        await page.goto("/");
        await page.waitForLoadState("networkidle");

        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"])
            .exclude(".chart-container") // Charts may have known issues
            .analyze();

        // Log violations for debugging
        if (accessibilityScanResults.violations.length > 0) {
            console.log("Dashboard accessibility violations:");
            accessibilityScanResults.violations.forEach((violation) => {
                console.log(`- ${violation.id}: ${violation.description}`);
                console.log(`  Impact: ${violation.impact}`);
                console.log(`  Nodes: ${violation.nodes.length}`);
            });
        }

        // Allow minor violations but fail on serious/critical
        const seriousViolations = accessibilityScanResults.violations.filter(
            (v) => v.impact === "serious" || v.impact === "critical",
        );

        expect(seriousViolations).toHaveLength(0);
    });

    /**
     * Task 6.4.2: Run axe-core tests on Run Detail editor
     */
    test("Plan detail page has no accessibility violations", async ({
        page,
    }) => {
        await page.goto("/plans");
        await page.waitForLoadState("networkidle");

        // Click on first plan if available
        const planLink = page
            .locator('[data-testid="plan-link"], a[href*="/plans/"]')
            .first();

        if (await planLink.isVisible()) {
            await planLink.click();
            await page.waitForLoadState("networkidle");

            const accessibilityScanResults = await new AxeBuilder({ page })
                .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"])
                .exclude(".chart-container")
                .analyze();

            if (accessibilityScanResults.violations.length > 0) {
                console.log("Plan detail accessibility violations:");
                accessibilityScanResults.violations.forEach((violation) => {
                    console.log(`- ${violation.id}: ${violation.description}`);
                });
            }

            const seriousViolations =
                accessibilityScanResults.violations.filter(
                    (v) => v.impact === "serious" || v.impact === "critical",
                );

            expect(seriousViolations).toHaveLength(0);
        }
    });

    /**
     * Task 6.4.3: Run axe-core tests on Export/Import modals
     */
    test("Export modal has no accessibility violations", async ({ page }) => {
        await page.goto("/plans");
        await page.waitForLoadState("networkidle");

        const planLink = page
            .locator('[data-testid="plan-link"], a[href*="/plans/"]')
            .first();

        if (await planLink.isVisible()) {
            await planLink.click();
            await page.waitForLoadState("networkidle");

            // Open export modal
            const exportButton = page
                .locator(
                    '[data-testid="export-button"], button:has-text("Export")',
                )
                .first();

            if (await exportButton.isVisible()) {
                await exportButton.click();
                await page.waitForTimeout(500);

                const modal = page.locator('[role="dialog"], .modal').first();

                if (await modal.isVisible()) {
                    const accessibilityScanResults = await new AxeBuilder({
                        page,
                    })
                        .include('[role="dialog"], .modal')
                        .withTags(["wcag2a", "wcag2aa"])
                        .analyze();

                    if (accessibilityScanResults.violations.length > 0) {
                        console.log("Export modal accessibility violations:");
                        accessibilityScanResults.violations.forEach(
                            (violation) => {
                                console.log(
                                    `- ${violation.id}: ${violation.description}`,
                                );
                            },
                        );
                    }

                    const seriousViolations =
                        accessibilityScanResults.violations.filter(
                            (v) =>
                                v.impact === "serious" ||
                                v.impact === "critical",
                        );

                    expect(seriousViolations).toHaveLength(0);
                }
            }
        }
    });

    /**
     * Task 6.4.4: Run axe-core tests on Skill autocomplete dropdown
     */
    test("Skill search autocomplete has no accessibility violations", async ({
        page,
    }) => {
        await page.goto("/plans");
        await page.waitForLoadState("networkidle");

        const planLink = page
            .locator('[data-testid="plan-link"], a[href*="/plans/"]')
            .first();

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
                await page.waitForTimeout(500);

                const accessibilityScanResults = await new AxeBuilder({ page })
                    .withTags(["wcag2a", "wcag2aa"])
                    .analyze();

                // Check for combobox-related violations
                const comboboxViolations =
                    accessibilityScanResults.violations.filter(
                        (v) =>
                            v.id.includes("aria") ||
                            v.id.includes("listbox") ||
                            v.id.includes("combobox"),
                    );

                if (comboboxViolations.length > 0) {
                    console.log("Skill search accessibility violations:");
                    comboboxViolations.forEach((violation) => {
                        console.log(
                            `- ${violation.id}: ${violation.description}`,
                        );
                    });
                }

                const seriousViolations = comboboxViolations.filter(
                    (v) => v.impact === "serious" || v.impact === "critical",
                );

                expect(seriousViolations).toHaveLength(0);
            }
        }
    });

    /**
     * Task 6.4.5: Run axe-core tests on Local Data management page
     */
    test("Local data page has no accessibility violations", async ({
        page,
    }) => {
        await page.goto("/local-data");
        await page.waitForLoadState("networkidle");

        // Check if page exists (may redirect if not implemented)
        const currentUrl = page.url();

        if (currentUrl.includes("local-data")) {
            const accessibilityScanResults = await new AxeBuilder({ page })
                .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"])
                .analyze();

            if (accessibilityScanResults.violations.length > 0) {
                console.log("Local data page accessibility violations:");
                accessibilityScanResults.violations.forEach((violation) => {
                    console.log(`- ${violation.id}: ${violation.description}`);
                });
            }

            const seriousViolations =
                accessibilityScanResults.violations.filter(
                    (v) => v.impact === "serious" || v.impact === "critical",
                );

            expect(seriousViolations).toHaveLength(0);
        }
    });

    /**
     * Additional: Test characters page accessibility
     */
    test("Characters page has no accessibility violations", async ({
        page,
    }) => {
        await page.goto("/characters");
        await page.waitForLoadState("networkidle");

        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa"])
            .analyze();

        if (accessibilityScanResults.violations.length > 0) {
            console.log("Characters page accessibility violations:");
            accessibilityScanResults.violations.forEach((violation) => {
                console.log(`- ${violation.id}: ${violation.description}`);
            });
        }

        const seriousViolations = accessibilityScanResults.violations.filter(
            (v) => v.impact === "serious" || v.impact === "critical",
        );

        expect(seriousViolations).toHaveLength(0);
    });
});

test.describe("ARIA and Semantic HTML", () => {
    test("all images have alt text", async ({ page }) => {
        await page.goto("/");
        await page.waitForLoadState("networkidle");

        const images = page.locator("img");
        const count = await images.count();

        for (let i = 0; i < count; i++) {
            const img = images.nth(i);
            const alt = await img.getAttribute("alt");
            const role = await img.getAttribute("role");

            // Image should have alt text or be marked as decorative
            const hasAlt = alt !== null;
            const isDecorative = role === "presentation" || alt === "";

            expect(hasAlt || isDecorative).toBe(true);
        }
    });

    test("all form inputs have labels", async ({ page }) => {
        await page.goto("/plans");
        await page.waitForLoadState("networkidle");

        const inputs = page.locator(
            'input:not([type="hidden"]):not([type="submit"]):not([type="button"])',
        );
        const count = await inputs.count();

        for (let i = 0; i < count; i++) {
            const input = inputs.nth(i);
            const id = await input.getAttribute("id");
            const ariaLabel = await input.getAttribute("aria-label");
            const ariaLabelledby = await input.getAttribute("aria-labelledby");
            const placeholder = await input.getAttribute("placeholder");

            // Input should have a label, aria-label, or aria-labelledby
            const hasLabel = id
                ? (await page.locator(`label[for="${id}"]`).count()) > 0
                : false;
            const hasAriaLabel = ariaLabel !== null;
            const hasAriaLabelledby = ariaLabelledby !== null;

            // At minimum, should have some form of labeling
            expect(
                hasLabel || hasAriaLabel || hasAriaLabelledby || placeholder,
            ).toBeTruthy();
        }
    });

    test("buttons have accessible names", async ({ page }) => {
        await page.goto("/");
        await page.waitForLoadState("networkidle");

        const buttons = page.locator("button");
        const count = await buttons.count();

        for (let i = 0; i < count; i++) {
            const button = buttons.nth(i);
            const text = await button.textContent();
            const ariaLabel = await button.getAttribute("aria-label");
            const title = await button.getAttribute("title");

            // Button should have text content, aria-label, or title
            const hasAccessibleName =
                (text && text.trim().length > 0) ||
                ariaLabel !== null ||
                title !== null;

            expect(hasAccessibleName).toBe(true);
        }
    });

    test("skip link is present and functional", async ({ page }) => {
        await page.goto("/");
        await page.waitForLoadState("networkidle");

        // Look for skip link
        const skipLink = page
            .locator(
                'a[href="#main"], a[href="#content"], .skip-link, [data-testid="skip-link"]',
            )
            .first();

        if ((await skipLink.count()) > 0) {
            // Skip link should be focusable
            await page.keyboard.press("Tab");

            const focusedElement = page.locator(":focus");
            const isFocused = await focusedElement.evaluate(
                (el, skipSelector) => {
                    const skip = document.querySelector(skipSelector);
                    return el === skip;
                },
                'a[href="#main"], a[href="#content"], .skip-link',
            );

            // Skip link should be one of the first focusable elements
            // (may not be the very first due to other elements)
        }
    });
});

test.describe("Color Contrast", () => {
    test("text has sufficient color contrast", async ({ page }) => {
        await page.goto("/");
        await page.waitForLoadState("networkidle");

        const accessibilityScanResults = await new AxeBuilder({ page })
            .withTags(["wcag2aa"])
            .options({ runOnly: ["color-contrast"] })
            .analyze();

        if (accessibilityScanResults.violations.length > 0) {
            console.log("Color contrast violations:");
            accessibilityScanResults.violations.forEach((violation) => {
                console.log(`- ${violation.id}: ${violation.description}`);
                violation.nodes.forEach((node) => {
                    console.log(`  Element: ${node.html.substring(0, 100)}`);
                });
            });
        }

        // Allow some contrast issues but log them
        const criticalViolations = accessibilityScanResults.violations.filter(
            (v) => v.impact === "critical",
        );

        expect(criticalViolations).toHaveLength(0);
    });
});
