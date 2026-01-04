import { test, expect } from "@playwright/test";

test.describe("Umamusume Roster", () => {
    test.beforeEach(async ({ page }) => {
        await page.goto("http://127.0.0.1:8000/characters");
    });

    test("displays the roster page with proper title and layout", async ({
        page,
    }) => {
        // Check page title and header
        await expect(page.locator("h1")).toContainText("Umamusume Roster");
        await expect(page.locator(".lead")).toContainText(
            "Meet the aspiring racehorses of Tracen Academy",
        );

        // Check search and filter controls
        await expect(page.locator("#searchInput")).toBeVisible();
        await expect(page.locator("#teamFilter")).toBeVisible();

        // Check that character cards are displayed
        const cardCount = await page.locator(".character-card").count();
        expect(cardCount).toBeGreaterThan(0);
    });

    test("displays character cards with proper information", async ({
        page,
    }) => {
        const firstCard = page.locator(".character-card").first();

        // Check card structure
        await expect(firstCard.locator(".card-title")).toBeVisible();
        await expect(firstCard.locator("img")).toBeVisible();
        await expect(firstCard.locator(".view-details-btn")).toBeVisible();

        // Check stats display
        await expect(firstCard.locator(".stat-mini")).toHaveCount(5); // Speed, Stamina, Power, Guts, Wisdom

        // Check rarity stars
        const starCount = await firstCard
            .locator('span:has-text("⭐")')
            .count();
        expect(starCount).toBeGreaterThan(0);
    });

    test("search functionality works correctly", async ({ page }) => {
        // Get initial count of character cards
        const initialCount = await page.locator(".character-card").count();
        expect(initialCount).toBeGreaterThan(0);

        // Search for a specific character (Agnes Tachyon should exist)
        await page.fill("#searchInput", "Agnes");
        await page.waitForTimeout(500); // Wait for filter to apply

        // Check that results are filtered
        const filteredCount = await page
            .locator(".character-card:visible")
            .count();
        expect(filteredCount).toBeLessThanOrEqual(initialCount);
        expect(filteredCount).toBeGreaterThan(0);

        // Clear search
        await page.fill("#searchInput", "");
        await page.waitForTimeout(500);

        // Check that all cards are visible again
        const resetCount = await page
            .locator(".character-card:visible")
            .count();
        expect(resetCount).toBe(initialCount);
    });

    test("team filter functionality works correctly", async ({ page }) => {
        // Get initial count
        const initialCount = await page.locator(".character-card").count();
        expect(initialCount).toBeGreaterThan(0);

        // Filter by Team Spica
        await page.selectOption("#teamFilter", "Spica");

        // Wait for Livewire to update the DOM
        await page.waitForFunction(
            () => {
                const cards = document.querySelectorAll(".character-card");
                if (cards.length === 0) return false;
                // Check if all visible cards have Spica team
                return Array.from(cards).every(
                    (card) => card.getAttribute("data-team") === "spica",
                );
            },
            { timeout: 5000 },
        );

        // Check that results are filtered
        const spicaCount = await page.locator(".character-card").count();
        expect(spicaCount).toBeLessThanOrEqual(initialCount);
        expect(spicaCount).toBeGreaterThan(0);

        // Verify that all visible cards have Spica team
        const spicaCards = page.locator(".character-card");
        const spicaCardCount = await spicaCards.count();

        for (let i = 0; i < spicaCardCount; i++) {
            const card = spicaCards.nth(i);
            const team = await card.getAttribute("data-team");
            expect(team).toBe("spica");
        }

        // Reset filter
        await page.selectOption("#teamFilter", "");
        await page.waitForTimeout(500);

        const resetCount = await page.locator(".character-card").count();
        expect(resetCount).toBe(initialCount);
    });

    test("character detail modal opens and displays information", async ({
        page,
    }) => {
        // Click on the first "View Details" button
        const firstViewButton = page.locator(".view-details-btn").first();
        await firstViewButton.click();

        // Wait for Livewire to render the modal
        await page.waitForSelector('[data-testid="character-detail-modal"]', {
            timeout: 10000,
        });

        const modal = page.locator('[data-testid="character-detail-modal"]');
        await expect(modal).toBeVisible();

        // Check modal header contains character name
        await expect(modal.locator(".modal-title")).toBeVisible();

        // Wait for content to load
        const modalBody = modal.locator("#characterModalBody");
        await expect(modalBody).toBeVisible();

        // Close modal
        await modal.locator('[data-testid="character-modal-close"]').click();
        await page.waitForTimeout(300);
        await expect(modal).not.toBeVisible();
    });

    test("API endpoint for character details works", async ({ page }) => {
        // Get the first character ID from the page
        const firstButton = page.locator(".view-details-btn").first();
        const characterId = await firstButton.getAttribute("data-character-id");

        // Test the API endpoint directly
        const response = await page.request.get(
            `http://127.0.0.1:8000/api/v1/umamusume/${characterId}`,
        );
        expect(response.ok()).toBeTruthy();

        const json = await response.json();
        // API wraps response in data property
        const data = json.data || json;
        expect(data).toHaveProperty("id");
        expect(data).toHaveProperty("name");
        expect(data).toHaveProperty("base_stats");
    });
});
