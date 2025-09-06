import { defineConfig } from "@playwright/test";

export default defineConfig({
    testDir: "./tests/playwright",
    timeout: 30_000,
    expect: { timeout: 5000 },
    use: {
        headless: true,
        baseURL: "http://localhost/uma-musume-planner-laravel/public/",
        viewport: { width: 1280, height: 800 },
        actionTimeout: 5000,
    },
    projects: [{ name: "chromium", use: { browserName: "chromium" } }],
});
