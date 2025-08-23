# Update Checklist — Uma Musume Race Planner (XAMPP)

Use this checklist every time you update the local deployment at:

`C:\xampp\htdocs\uma_musume_race_planner`

Includes steps for archiving legacy endpoints and verifying the new `/api` routes.

Last reviewed: 2025-08-24

---

## Pre-Update

- [ ] Back up `.env`
- [ ] Back up `uploads\trainee_images\`
- [ ] Back up database `uma_musume_planner` (phpMyAdmin export or `mysqldump`)
- [ ] Confirm PHP version (>= 8.1)
- [ ] Optional: Stop Apache
- [ ] Ensure `backup\` is in `.gitignore`

## Archive Legacy Endpoints (Modernization)

- [x] Create `C:\xampp\htdocs\uma_musume_race_planner\backup\legacy_endpoints` (legacy files already archived)

- [x] Move these legacy files if present:
  - [x] `get_plan_attributes.php`
  - [x] `get_plan_distance_grades.php`
  - [x] `get_plan_goals.php`
  - [x] `get_plan_predictions.php`
  - [x] `get_plan_skills.php`
  - [x] `get_plan_style_grades.php`
  - [x] `get_plan_terrain_grades.php`
  - [x] `get_plan_turns.php`
  - [x] `get_progress_chart_data.php`
  - [x] `get_autosuggest_backup.php`

- [x] Keep consolidated/active files in web root:
  - [x] `get_plans.php`
  - [x] `get_stats.php`
  - [x] `fetch_plan_details.php`
  - [x] `export_plan_data.php`
  - [x] `get_activities.php`
  - [x] `get_skill_reference.php`
  - [x] `get_autosuggest.php`
  - [x] `get_plan_section.php`
  - [x] `handle_plan_crud.php`
  - [x] `plan_details_modal.php`
  - [x] `quick_create_plan_modal.php`
  - [x] `includes/*`, `components/*`, `assets/*`, `api/*`

Note on progress chart:

- [x] Use `/api/progress.php?action=chart&plan_id={id}`

## Update Code

- [ ] Update source code (git pull or replace with latest release ZIP)

- [ ] Ensure `.env` exists and has correct DB settings

- [ ] Composer:
  - [ ] `composer install --no-dev --optimize-autoloader`
  - [ ] If required by release notes: `composer update --no-dev --optimize-autoloader`

## Database (Only if Release Includes Schema Changes)

- [ ] Import `uma_musume_planner.sql` into `uma_musume_planner` via phpMyAdmin or MySQL CLI

- [ ] Do NOT run `sample_data.sql` unless you intentionally want to reset all data

## Post-Update Verification — `/api` Endpoints

Open in a browser, expect JSON success:

- [x] `/api/stats.php?action=get`
- [x] `/api/plan.php?action=list`
- [x] `/api/plan.php?action=get&id=1`
- [x] `/api/plan_section.php?type=attributes&id=1`
- [x] `/api/plan_section.php?type=skills&id=1`
- [x] `/api/plan_section.php?type=turns&id=1`
- [x] `/api/autosuggest.php?action=get&field=name&query=a`
- [x] `/api/activity.php?action=get`
- [x] `/api/progress.php?action=chart&plan_id=1`

Note: Code-level verification completed — each `/api/*.php` file exists and implements the listed GET action. For plan-specific endpoints to return non-empty results, ensure the database contains the referenced records (for example, `plan_id=1`).

Frontend sanity checks:

- [ ] Verify the site does not request `/public/api/...` paths (check browser Network tab). If you see requests to `/public/api/...`, update client-side fetch/XHR calls to use the absolute site-root API path (for example: `/uma_musume_race_planner/api/plan.php?action=list`) or set a configurable `BASE_URL` in your JS.

Compatibility endpoints (root):

- [x] `/get_stats.php` (present)
- [x] `/get_plans.php` (present)
- [x] `/fetch_plan_details.php?id=1` (present)
- [x] `/get_plan_section.php?id=1&type=attributes` (present)
- [x] `/get_skill_reference.php?search=speed` (present)
- [x] `/get_activities.php` (present)
- [x] `/export_plan_data.php?id=1` (and `&format=txt` downloads) (present)

## Post-Update Verification — UI

- [ ] Dashboard loads without JS errors — CODE: verified (frontend assets present); RUNTIME: check browser console. See `public/index.php` and `assets/js/` for loaded scripts.
- [ ] Plan Details modal:
  - [ ] General tab data loads — CODE: `plan_details_modal.php` + `assets/js/plan_details_modal.js` present; RUNTIME: `php_errors.log` shows PHP warnings during rendering (see below).
  - [ ] Attributes/Grades/Skills/Goals/Predictions load — CODE: server endpoints exist (`/api/plan_section.php`); RUNTIME: requires DB records.
  - [ ] Progress chart renders via `/api/progress.php` — CODE: `/api/progress.php?action=chart` implemented; RUNTIME: requires `turns` rows for plan.
  - [ ] Save changes succeeds (`handle_plan_crud.php` returns success) — CODE: `handle_plan_crud.php` exists; RUNTIME: logs show previous DB constraint errors (see below).
- [ ] Skill autosuggest returns results — CODE: `assets/js/autosuggest.js` and `api/autosuggest.php` present; RUNTIME: returns results when DB has values.
- [x] Image upload/change works in `uploads\trainee_images\` — Verified: folder exists (`uploads/trainee_images/.gitkeep`).
- [ ] "Export as TXT" downloads a file — CODE: `export_plan_data.php` and client links present; RUNTIME: requires plan id and working PHP.

Runtime issues found in logs (actionable):

- Several PHP warnings from `plan_details_modal.php` (undefined array keys) may cause missing UI data; see `php_errors.log` entries around 26-Jul-2025 (multiple "Undefined array key" warnings referencing `plan_details_modal.php` lines ~98,108,136).
- A fatal error in `fetch_plan_details.php` complaining about "Unknown column 'type' in 'field list'" (see `php_errors.log` entry at 26-Jul-2025 02:28:57). This will break plan detail fetches at runtime.
- Database-related errors during plan save/update are present in `logs/app-*.log` (example: integrity constraint violations and duplicate key errors recorded in `logs/app-2025-07-31.log` and `logs/app-2025-07-26.log`) — these indicate the DB schema or input data may need attention before Save works reliably.

Next steps to validate fully (runtime checks):

1. Start Apache and open the app in a browser, then check the Console and Network tabs for JS errors and failing requests.
2. Open `php_errors.log` while reproducing Plan Details to capture fresh errors and fix the undefined index warnings in `plan_details_modal.php`.
3. Inspect `fetch_plan_details.php` SQL (line ~36) and adjust the query or DB schema to remove the `type` column reference or add the missing column.
4. Attempt a plan save and examine `logs/app-*.log` for constraint failures; adjust sample data or migration scripts accordingly.

## Troubleshooting

- [ ] Check `php_errors.log` in project root
- [ ] Verify `.env` (`DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS`)
- [ ] Confirm lookup tables exist (`moods`, `conditions`, `strategies`)
- [ ] Re-run `composer install`
- [x] Ensure `uploads\trainee_images\` exists and is writable (created)
- [ ] If a request 404s, restore archived endpoint or update frontend to `/api` route

## Rollback

- [ ] Stop Apache
- [ ] Restore code and `.env` from backup
- [ ] Restore DB from backup `.sql`
- [ ] If needed, move files back from `backup\legacy_endpoints`
- [ ] Start Apache and re-verify

---

Notes:

- Keep this checklist in the project and update the "Last reviewed" date when you change the process.

- Never run `sample_data.sql` on live
