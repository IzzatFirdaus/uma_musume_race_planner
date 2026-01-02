# Accessibility frontend updates

This PR updates the frontend to improve accessibility, semantics, and developer tooling.

Summary of changes (incremental first pass):

- Added `tailwind.config.js` with `darkMode: 'class'`, Blade content scans, color tokens mapped to CSS variables, and a safelist for dynamic classes.
- Appended accessibility-focused CSS to `resources/css/app.css` to ensure visible focus styles, honor `prefers-reduced-motion`, and improve skip-link visibility.
- Added `tools/accessibility-update/report.json` (stub) for automated tooling to write verification results.

Why:

- The repository was missing a Tailwind config; adding one improves the Vite/Tailwind build and ensures Blade templates are scanned for utility classes.
- Accessibility rules provide a consistent baseline for focus visibility and reduced-motion respect.

## Automated Accessibility Test Results (Playwright)

**Status:** ❌ All Playwright accessibility tests failed due to `net::ERR_CONNECTION_REFUSED` (the dev server was not running at the expected baseUrl).

- No axe violation counts are available.
- Please re-run tests with the dev server running on `http://localhost:8000` (or set the correct `PLAYWRIGHT_TEST_BASE_URL`).

---

Next steps (follow-up PRs):

1. Run Playwright axe tests and update `tools/accessibility-update/report.json` with `axe_violations_before` and `axe_violations_after`.
2. Incrementally update Blade templates to use centralized components where missing (already present components will be audited next).
3. Add a GitHub Actions workflow to run Playwright + axe + Lighthouse CI on PRs.

Files changed:

- `tailwind.config.js` (new)
- `resources/css/app.css` (updated)
- `tools/accessibility-update/report.json` (new stub)

Verification instructions (run locally):

1. npm install && npm run dev (or run your Vite dev server)
2. php artisan serve (or use your local dev URL)
3. Run Playwright tests: `npx playwright test tests/playwright/accessibility.spec.ts --project=chromium`
4. Run Pint and PHP unit tests for modified files: `vendor/bin/pint --dirty` and `php artisan test --filter=RelatedTests`

If you want, I can now run repository verification commands and then prepare an incremental patch to standardize (or audit) the existing Blade components (`resources/views/components/*`) and update a representative page (dashboard) to use any missing ARIA attributes.
