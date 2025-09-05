// Playwright config to only run Playwright tests in scripts/
module.exports = {
  testDir: './scripts',
  testMatch: /.*\.(spec|test)\.js$/,
  timeout: 30000,
  retries: 0,
  use: {
    headless: true,
    baseURL: 'http://localhost/uma_musume_race_planner/public',
    ignoreHTTPSErrors: true,
  },
};
