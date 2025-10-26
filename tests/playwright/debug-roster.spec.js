import { test, expect } from "@playwright/test";

test.describe("Umamusume Roster Debug", () => {
    test("debug what is actually on the page", async ({ page }) => {
        await page.goto("http://127.0.0.1:8000/characters");

        // Take screenshot to see what's actually displayed
        await page.screenshot({
            path: "debug-characters-page.png",
            fullPage: true,
        });

        // Get page content to see what's loaded
        const content = await page.content();
        console.log("Page title:", await page.title());

        // Check if we have the main elements
        const h1Count = await page.locator("h1").count();
        console.log("H1 elements found:", h1Count);

        if (h1Count > 0) {
            const h1Text = await page.locator("h1").first().textContent();
            console.log("First H1 text:", h1Text);
        }

        // Check for character cards
        const cardCount = await page.locator(".character-card").count();
        console.log("Character cards found:", cardCount);

        // Check for any error messages
        const errorMessage = await page
            .locator(".alert-danger, .alert-warning")
            .textContent()
            .catch(() => "No error found");
        console.log("Error message:", errorMessage);

        // Wait a bit to see if it's loading
        await page.waitForTimeout(2000);

        const cardCountAfter = await page.locator(".character-card").count();
        console.log("Character cards found after wait:", cardCountAfter);
    });
});
