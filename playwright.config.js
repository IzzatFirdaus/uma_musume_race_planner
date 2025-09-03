// Basic Playwright config for local static PHP dev
// Assumes the app is served locally (e.g., via Laragon or PHP built-in server)
// You can set BASE_URL env or default to http://localhost/uma_musume_race_planner/

const { defineConfig } = require('@playwright/test');

const baseURL = process.env.BASE_URL || 'http://localhost/uma_musume_race_planner/';

module.exports = defineConfig({
  testDir: './tests',
  use: {
    baseURL,
    headless: true,
    viewport: { width: 390, height: 844 }, // mobile-ish for sticky actions
  },
});
