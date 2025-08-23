# Update Checklist — Uma Musume Race Planner (XAMPP)

Use this checklist every time you update the local deployment at:
C:\xampp\htdocs\uma_musume_race_planner

Includes steps for archiving legacy endpoints and verifying the new /api routes.

Last reviewed: 2025-08-23

---

## Pre-Update

- [ ] Back up .env
- [ ] Back up uploads\trainee_images\
- [ ] Back up database uma_musume_planner (phpMyAdmin export or mysqldump)
- [ ] Confirm PHP version (>= 8.1)
- [ ] Optional: Stop Apache
- [ ] Ensure backup\ is in .gitignore

## Archive Legacy Endpoints (Modernization)

- [x] Create C:\xampp\htdocs\uma_musume_race_planner\backup\legacy_endpoints (legacy files already archived)
- [x] Move these legacy files if present:
  - [x] get_plan_attributes.php
  - [x] get_plan_distance_grades.php
  - [x] get_plan_goals.php
  - [x] get_plan_predictions.php
  - [x] get_plan_skills.php
  - [x] get_plan_style_grades.php
  - [x] get_plan_terrain_grades.php
  - [x] get_plan_turns.php
  - [x] get_progress_chart_data.php
  - [x] get_autosuggest_backup.php
- [ ] Keep consolidated/active files in web root:
  - [ ] get_plans.php, get_stats.php, fetch_plan_details.php, export_plan_data.php
  - [ ] get_activities.php, get_skill_reference.php, get_autosuggest.php
  - [ ] get_plan_section.php, handle_plan_crud.php
  - [ ] plan_details_modal.php, quick_create_plan_modal.php
  - [ ] includes\*, components\*, assets\*, api\*

Note on progress chart:

- [ ] Use /api/progress.php?action=chart&plan_id={id}

## Update Code

- [ ] Update source code (git pull or replace with latest release ZIP)
- [ ] Ensure .env exists and has correct DB settings
- [ ] Composer:
  - [ ] composer install --no-dev --optimize-autoloader
  - [ ] If required by release notes: composer update --no-dev --optimize-autoloader

## Database (Only if Release Includes Schema Changes)

- [ ] Import uma_musume_planner.sql into uma_musume_planner via phpMyAdmin or mysql CLI
- [ ] Do NOT run sample_data.sql unless you intentionally want to reset all data

## Post-Update Verification — /api Endpoints

Open in a browser, expect JSON success:

- [ ] /api/stats.php?action=get
- [ ] /api/plan.php?action=list
- [ ] /api/plan.php?action=get&id=1
- [ ] /api/plan_section.php?type=attributes&id=1
- [ ] /api/plan_section.php?type=skills&id=1
- [ ] /api/plan_section.php?type=turns&id=1
- [ ] /api/autosuggest.php?action=get&field=name&query=a
- [ ] /api/activity.php?action=get
- [ ] /api/progress.php?action=chart&plan_id=1

Frontend sanity checks:

- [ ] Verify the site does not request `/public/api/...` paths (check browser Network tab). If you see requests to `/public/api/...`, update client-side fetch/XHR calls to use the absolute site-root API path (for example: `/uma_musume_race_planner/api/plan.php?action=list`) or set a configurable `BASE_URL` in your JS.

Compatibility endpoints (root):

- [x] /get_stats.php (present)
- [x] /get_plans.php (present)
- [x] /fetch_plan_details.php?id=1 (present)
- [x] /get_plan_section.php?id=1&type=attributes (present)
- [x] /get_skill_reference.php?search=speed (present)
- [x] /get_activities.php (present)
- [x] /export_plan_data.php?id=1 (and &format=txt downloads) (present)

## Post-Update Verification — UI

- [ ] Dashboard loads without JS errors
- [ ] Plan Details modal:
  - [ ] General tab data loads
  - [ ] Attributes/Grades/Skills/Goals/Predictions load
  - [ ] Progress chart renders via /api/progress.php
  - [ ] Save changes succeeds (handle_plan_crud.php returns success)
- [ ] Skill autosuggest returns results
- [ ] Image upload/change works in uploads\trainee_images\
- [ ] “Export as TXT” downloads a file

## Troubleshooting

- [ ] Check php_errors.log in project root
- [ ] Verify .env (DB_HOST/DB_NAME/DB_USER/DB_PASS)
- [ ] Confirm lookup tables exist (moods, conditions, strategies)
- [ ] Re-run composer install
- [x] Ensure uploads\trainee_images\ exists and is writable (created)
- [ ] If a request 404s, restore archived endpoint or update frontend to /api route

## Rollback

- [ ] Stop Apache
- [ ] Restore code and .env from backup
- [ ] Restore DB from backup .sql
- [ ] If needed, move files back from backup\legacy_endpoints
- [ ] Start Apache and re-verify

---
Notes:

- Keep this checklist in the project and update the “Last reviewed” date when you change the process.

- Never run sample_data.sql on live
