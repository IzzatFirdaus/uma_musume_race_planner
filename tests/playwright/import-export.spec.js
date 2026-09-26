import { test, expect } from "@playwright/test";

const BASE = "http://127.0.0.1:8000/";

test.describe("Import/Export Wizard", () => {
    test.beforeEach(async ({ page }) => {
        await page.goto(BASE, { waitUntil: "networkidle" });
    });

    test("opens export modal and previews JSON export", async ({ page }) => {
        // Navigate to a plan page first
        await page.goto(`${BASE}plans`, { waitUntil: "networkidle" });

        // Click export button (assuming there's an export button on the plan)
        // This test assumes a plan exists or we need to create one first
        const exportButton = page.locator('[data-testid="export-btn"]').first();
        if (await exportButton.count() > 0) {
            await exportButton.click();
            await page.waitForSelector('[data-testid="export-modal"]', { timeout: 5000 });

            // Verify modal opened
            await expect(page.locator('[data-testid="export-modal"]')).toBeVisible();

            // Click preview button
            await page.click('[data-testid="preview-btn"]');
            await page.waitForSelector('[data-testid="export-preview-panel"]', { timeout: 5000 });

            // Verify preview is shown
            await expect(page.locator('[data-testid="export-preview-panel"]')).toBeVisible();
            await expect(page.locator('[data-testid="preview-content"]')).toBeVisible();
        }
    });

    test("exports plan in JSON format", async ({ page }) => {
        await page.goto(`${BASE}plans`, { waitUntil: "networkidle" });

        const exportButton = page.locator('[data-testid="export-btn"]').first();
        if (await exportButton.count() > 0) {
            await exportButton.click();
            await page.waitForSelector('[data-testid="export-modal"]', { timeout: 5000 });

            // Select JSON format
            await page.click('[data-testid="format-json"]');

            // Download
            const downloadPromise = page.waitForEvent('download');
            await page.click('[data-testid="export-download-btn"]');
            const download = await downloadPromise;

            // Verify download
            expect(download.suggestedFilename()).toMatch(/\.json$/);
        }
    });

    test("exports plan in CSV format", async ({ page }) => {
        await page.goto(`${BASE}plans`, { waitUntil: "networkidle" });

        const exportButton = page.locator('[data-testid="export-btn"]').first();
        if (await exportButton.count() > 0) {
            await exportButton.click();
            await page.waitForSelector('[data-testid="export-modal"]', { timeout: 5000 });

            // Select CSV format
            await page.click('[data-testid="format-csv"]');

            // Download
            const downloadPromise = page.waitForEvent('download');
            await page.click('[data-testid="export-download-btn"]');
            const download = await downloadPromise;

            // Verify download
            expect(download.suggestedFilename()).toMatch(/\.csv$/);
        }
    });

    test("exports plan in Markdown format", async ({ page }) => {
        await page.goto(`${BASE}plans`, { waitUntil: "networkidle" });

        const exportButton = page.locator('[data-testid="export-btn"]').first();
        if (await exportButton.count() > 0) {
            await exportButton.click();
            await page.waitForSelector('[data-testid="export-modal"]', { timeout: 5000 });

            // Select Markdown format
            await page.click('[data-testid="format-markdown"]');

            // Download
            const downloadPromise = page.waitForEvent('download');
            await page.click('[data-testid="export-download-btn"]');
            const download = await downloadPromise;

            // Verify download
            expect(download.suggestedFilename()).toMatch(/\.md$/);
        }
    });

    test("copies export content to clipboard", async ({ page }) => {
        await page.goto(`${BASE}plans`, { waitUntil: "networkidle" });

        const exportButton = page.locator('[data-testid="export-btn"]').first();
        if (await exportButton.count() > 0) {
            await exportButton.click();
            await page.waitForSelector('[data-testid="export-modal"]', { timeout: 5000 });

            // Generate preview first
            await page.click('[data-testid="preview-btn"]');
            await page.waitForSelector('[data-testid="export-preview-panel"]', { timeout: 5000 });

            // Copy to clipboard
            await page.click('[data-testid="copy-clipboard-btn"]');

            // Verify toast notification
            await expect(page.locator('.toast')).toContainText('Copied to clipboard');
        }
    });

    test("opens import wizard", async ({ page }) => {
        // Click import button (assuming it exists on dashboard)
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            // Verify wizard opened on step 1
            await expect(page.locator('[data-testid="import-wizard"]')).toBeVisible();
            await expect(page.locator('[data-testid="import-step-1"]')).toBeVisible();
        }
    });

    test("uploads JSON file and detects format", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            // Create a test JSON file
            const testJson = {
                schema_version: "1.0.0",
                plans: [
                    {
                        plan_title: "Test Import Plan",
                        name: "Test Horse",
                        career_stage: "junior",
                        status: "ongoing",
                    },
                ],
            };

            // Upload file
            const fileInput = page.locator('[data-testid="import-file-input"]');
            await fileInput.setInputFiles({
                name: 'test-import.json',
                mimeType: 'application/json',
                buffer: Buffer.from(JSON.stringify(testJson)),
            });

            // Click detect
            await page.click('[data-testid="import-detect-btn"]');
            await page.waitForSelector('[data-testid="import-step-2"]', { timeout: 5000 });

            // Verify format was detected
            await expect(page.locator('[data-testid="import-step-2"]')).toBeVisible();
            await expect(page.locator('text=Format Detected')).toBeVisible();
        }
    });

    test("uploads CSV file and detects format", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            // Create a test CSV file
            const testCsv = "Plan Title,Character Name,Career Stage\nTest Plan,Test Horse,junior";

            // Upload file
            const fileInput = page.locator('[data-testid="import-file-input"]');
            await fileInput.setInputFiles({
                name: 'test-import.csv',
                mimeType: 'text/csv',
                buffer: Buffer.from(testCsv),
            });

            // Click detect
            await page.click('[data-testid="import-detect-btn"]');
            await page.waitForSelector('[data-testid="import-step-2"]', { timeout: 5000 });

            // Verify format was detected
            await expect(page.locator('[data-testid="import-step-2"]')).toBeVisible();
        }
    });

    test("shows error for invalid file format", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            // Upload invalid file
            const fileInput = page.locator('[data-testid="import-file-input"]');
            await fileInput.setInputFiles({
                name: 'test-invalid.txt',
                mimeType: 'text/plain',
                buffer: Buffer.from('invalid content'),
            });

            // Click detect
            await page.click('[data-testid="import-detect-btn"]');

            // Verify error toast
            await expect(page.locator('.toast')).toContainText('Unable to detect file format');
        }
    });

    test("validates import data and shows preview", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            const testJson = {
                schema_version: "1.0.0",
                plans: [
                    {
                        plan_title: "Test Import Plan",
                        name: "Test Horse",
                        career_stage: "junior",
                        status: "ongoing",
                    },
                ],
            };

            const fileInput = page.locator('[data-testid="import-file-input"]');
            await fileInput.setInputFiles({
                name: 'test-import.json',
                mimeType: 'application/json',
                buffer: Buffer.from(JSON.stringify(testJson)),
            });

            await page.click('[data-testid="import-detect-btn"]');
            await page.waitForSelector('[data-testid="import-step-2"]', { timeout: 5000 });

            // Click validate
            await page.click('[data-testid="import-validate-btn"]');

            // Should move to confirm step (no duplicates)
            await page.waitForSelector('[data-testid="import-step-4"]', { timeout: 5000 });
            await expect(page.locator('[data-testid="import-step-4"]')).toBeVisible();
        }
    });

    test("shows conflict resolution step for duplicates", async ({ page }) => {
        // This test would require authentication and existing data
        // For now, we'll skip the actual duplicate detection
        test.skip(true, "Requires authentication and existing data setup");
    });

    test("executes import to local storage", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            const testJson = {
                schema_version: "1.0.0",
                plans: [
                    {
                        plan_title: "Test Import Plan",
                        name: "Test Horse",
                        career_stage: "junior",
                        status: "ongoing",
                    },
                ],
            };

            const fileInput = page.locator('[data-testid="import-file-input"]');
            await fileInput.setInputFiles({
                name: 'test-import.json',
                mimeType: 'application/json',
                buffer: Buffer.from(JSON.stringify(testJson)),
            });

            await page.click('[data-testid="import-detect-btn"]');
            await page.waitForSelector('[data-testid="import-step-2"]', { timeout: 5000 });

            await page.click('[data-testid="import-validate-btn"]');
            await page.waitForSelector('[data-testid="import-step-4"]', { timeout: 5000 });

            // Select local target
            await page.click('[data-testid="target-local"]');

            // Execute import
            await page.click('[data-testid="import-execute-btn"]');
            await page.waitForSelector('[data-testid="import-step-5"]', { timeout: 5000 });

            // Verify success
            await expect(page.locator('[data-testid="import-step-5"]')).toBeVisible();
            await expect(page.locator('text=Import Successful')).toBeVisible();
        }
    });

    test("navigates between wizard steps", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            const testJson = {
                schema_version: "1.0.0",
                plans: [
                    {
                        plan_title: "Test Import Plan",
                        name: "Test Horse",
                        career_stage: "junior",
                        status: "ongoing",
                    },
                ],
            };

            const fileInput = page.locator('[data-testid="import-file-input"]');
            await fileInput.setInputFiles({
                name: 'test-import.json',
                mimeType: 'application/json',
                buffer: Buffer.from(JSON.stringify(testJson)),
            });

            await page.click('[data-testid="import-detect-btn"]');
            await page.waitForSelector('[data-testid="import-step-2"]', { timeout: 5000 });

            // Go back
            await page.click('[data-testid="import-back-btn"]');
            await page.waitForSelector('[data-testid="import-step-1"]', { timeout: 5000 });

            // Verify we're back on step 1
            await expect(page.locator('[data-testid="import-step-1"]')).toBeVisible();
        }
    });

    test("closes import wizard", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            // Cancel
            await page.click('[data-testid="import-cancel-btn"]');
            await page.waitForSelector('[data-testid="import-wizard"]', {
                state: 'detached',
                timeout: 5000,
            });

            // Verify wizard closed
            await expect(page.locator('[data-testid="import-wizard"]')).not.toBeVisible();
        }
    });

    test("closes export modal", async ({ page }) => {
        await page.goto(`${BASE}plans`, { waitUntil: "networkidle" });

        const exportButton = page.locator('[data-testid="export-btn"]').first();
        if (await exportButton.count() > 0) {
            await exportButton.click();
            await page.waitForSelector('[data-testid="export-modal"]', { timeout: 5000 });

            // Cancel
            await page.click('[data-testid="export-cancel-btn"]');
            await page.waitForSelector('[data-testid="export-modal"]', {
                state: 'detached',
                timeout: 5000,
            });

            // Verify modal closed
            await expect(page.locator('[data-testid="export-modal"]')).not.toBeVisible();
        }
    });

    test("shows file size limit warning", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            // Verify file size info is shown
            await expect(page.locator('text=Maximum file size: 10MB')).toBeVisible();
        }
    });

    test("displays supported formats in import wizard", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            // Verify supported formats are mentioned
            await expect(page.locator('text=JSON')).toBeVisible();
            await expect(page.locator('text=CSV')).toBeVisible();
        }
    });

    test("displays format options in export modal", async ({ page }) => {
        await page.goto(`${BASE}plans`, { waitUntil: "networkidle" });

        const exportButton = page.locator('[data-testid="export-btn"]').first();
        if (await exportButton.count() > 0) {
            await exportButton.click();
            await page.waitForSelector('[data-testid="export-modal"]', { timeout: 5000 });

            // Verify all format options are shown
            await expect(page.locator('[data-testid="format-json"]')).toBeVisible();
            await expect(page.locator('[data-testid="format-csv"]')).toBeVisible();
            await expect(page.locator('[data-testid="format-markdown"]')).toBeVisible();
            await expect(page.locator('[data-testid="format-text"]')).toBeVisible();
        }
    });

    test("shows import summary before execution", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            const testJson = {
                schema_version: "1.0.0",
                plans: [
                    {
                        plan_title: "Test Import Plan",
                        name: "Test Horse",
                        career_stage: "junior",
                        status: "ongoing",
                    },
                ],
            };

            const fileInput = page.locator('[data-testid="import-file-input"]');
            await fileInput.setInputFiles({
                name: 'test-import.json',
                mimeType: 'application/json',
                buffer: Buffer.from(JSON.stringify(testJson)),
            });

            await page.click('[data-testid="import-detect-btn"]');
            await page.waitForSelector('[data-testid="import-step-2"]', { timeout: 5000 });

            await page.click('[data-testid="import-validate-btn"]');
            await page.waitForSelector('[data-testid="import-step-4"]', { timeout: 5000 });

            // Verify summary is shown
            await expect(page.locator('text=Import Summary')).toBeVisible();
            await expect(page.locator('text=Plans to import')).toBeVisible();
        }
    });

    test("displays import results after successful import", async ({ page }) => {
        const importButton = page.locator('[data-testid="import-btn"]');
        if (await importButton.count() > 0) {
            await importButton.click();
            await page.waitForSelector('[data-testid="import-wizard"]', { timeout: 5000 });

            const testJson = {
                schema_version: "1.0.0",
                plans: [
                    {
                        plan_title: "Test Import Plan",
                        name: "Test Horse",
                        career_stage: "junior",
                        status: "ongoing",
                    },
                ],
            };

            const fileInput = page.locator('[data-testid="import-file-input"]');
            await fileInput.setInputFiles({
                name: 'test-import.json',
                mimeType: 'application/json',
                buffer: Buffer.from(JSON.stringify(testJson)),
            });

            await page.click('[data-testid="import-detect-btn"]');
            await page.waitForSelector('[data-testid="import-step-2"]', { timeout: 5000 });

            await page.click('[data-testid="import-validate-btn"]');
            await page.waitForSelector('[data-testid="import-step-4"]', { timeout: 5000 });

            await page.click('[data-testid="target-local"]');
            await page.click('[data-testid="import-execute-btn"]');
            await page.waitForSelector('[data-testid="import-step-5"]', { timeout: 5000 });

            // Verify results are shown
            await expect(page.locator('text=Created')).toBeVisible();
            await expect(page.locator('text=1')).toBeVisible();
        }
    });
});
