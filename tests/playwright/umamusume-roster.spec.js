import { test, expect } from '@playwright/test';

test.describe('Umamusume Roster', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('http://127.0.0.1:8000/characters');
    });

    test('displays the roster page with proper title and layout', async ({ page }) => {
        // Check page title and header
        await expect(page.locator('h1')).toContainText('Umamusume Roster');
        await expect(page.locator('.lead')).toContainText('Meet the aspiring racehorses of Tracen Academy');

        // Check search and filter controls
        await expect(page.locator('#searchInput')).toBeVisible();
        await expect(page.locator('#teamFilter')).toBeVisible();

        // Check that character cards are displayed
        const cardCount = await page.locator('.character-card').count();
        expect(cardCount).toBeGreaterThan(0);
    });

    test('displays character cards with proper information', async ({ page }) => {
        const firstCard = page.locator('.character-card').first();

        // Check card structure
        await expect(firstCard.locator('.card-title')).toBeVisible();
        await expect(firstCard.locator('img')).toBeVisible();
        await expect(firstCard.locator('.view-details-btn')).toBeVisible();

        // Check stats display
        await expect(firstCard.locator('.stat-mini')).toHaveCount(5); // Speed, Stamina, Power, Guts, Wisdom

        // Check rarity stars
        const starCount = await firstCard.locator('span:has-text("⭐")').count();
        expect(starCount).toBeGreaterThan(0);
    });

    test('search functionality works correctly', async ({ page }) => {
        // Get initial count of character cards
        const initialCount = await page.locator('.character-card').count();
        expect(initialCount).toBeGreaterThan(0);

        // Search for a specific character (Agnes Tachyon should exist)
        await page.fill('#searchInput', 'Agnes');
        await page.waitForTimeout(500); // Wait for filter to apply

        // Check that results are filtered
        const filteredCount = await page.locator('.character-card:visible').count();
        expect(filteredCount).toBeLessThanOrEqual(initialCount);
        expect(filteredCount).toBeGreaterThan(0);

        // Clear search
        await page.fill('#searchInput', '');
        await page.waitForTimeout(500);

        // Check that all cards are visible again
        const resetCount = await page.locator('.character-card:visible').count();
        expect(resetCount).toBe(initialCount);
    });

    test('team filter functionality works correctly', async ({ page }) => {
        // Get initial count
        const initialCount = await page.locator('.character-card').count();
        expect(initialCount).toBeGreaterThan(0);

        // Filter by Team Spica
        await page.selectOption('#teamFilter', 'Spica');
        await page.waitForTimeout(500);

        // Check that results are filtered
        const spicaCount = await page.locator('.character-card:visible').count();
        expect(spicaCount).toBeLessThanOrEqual(initialCount);

        // Verify that visible cards have Spica team badge
        const spicaCards = page.locator('.character-card:visible');
        const spicaCardCount = await spicaCards.count();

        if (spicaCardCount > 0) {
            for (let i = 0; i < Math.min(spicaCardCount, 3); i++) {
                const card = spicaCards.nth(i);
                await expect(card.locator('.badge:has-text("Spica")').first()).toBeVisible();
            }
        }

        // Reset filter
        await page.selectOption('#teamFilter', '');
        await page.waitForTimeout(500);

        const resetCount = await page.locator('.character-card:visible').count();
        expect(resetCount).toBe(initialCount);
    });

    test('character detail modal opens and displays information', async ({ page }) => {
        // Click on the first "View Details" button
        const firstViewButton = page.locator('.view-details-btn').first();
        await firstViewButton.click();

        // Wait for modal to appear
        const modal = page.locator('#characterModal');
        await expect(modal).toBeVisible();

        // Check modal header
        await expect(modal.locator('.modal-title')).toContainText('Character Details');

        // Wait for content to load (either loading spinner or actual content)
        await expect(modal.locator('.modal-body')).toBeVisible();

        // Wait for loading to complete (up to 5 seconds)
        await page.waitForFunction(() => {
            const modalBody = document.querySelector('#characterModalBody');
            return modalBody && !modalBody.querySelector('.spinner-border');
        }, { timeout: 5000 });

        // Check that content has loaded
        const modalBody = modal.locator('#characterModalBody');
        await expect(modalBody).not.toContainText('Loading');

        // Close modal
        await modal.locator('.btn-close').click();
        await expect(modal).not.toBeVisible();
    });

    test('API endpoint for character details works', async ({ page }) => {
        // Get the first character ID from the page
        const firstButton = page.locator('.view-details-btn').first();
        const characterId = await firstButton.getAttribute('data-character-id');

        // Test the API endpoint directly
        const response = await page.request.get(`http://127.0.0.1:8000/api/v1/umamusume/${characterId}`);
        expect(response.ok()).toBeTruthy();

        const data = await response.json();
        expect(data).toHaveProperty('id');
        expect(data).toHaveProperty('name');
        expect(data).toHaveProperty('base_stats');
    });
});
