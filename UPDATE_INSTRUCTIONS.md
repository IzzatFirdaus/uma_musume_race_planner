# Update Instructions — Uma Musume Race Planner (XAMPP on Windows)

This document explains how to safely update your local deployment located at:
C:\xampp\htdocs\uma_musume_race_planner

The app is PHP + MySQL (no framework), uses Composer for PHP libraries and SQL files for schema/data.

This version incorporates the Modernization & Enhancement Plan (No Frameworks), consolidating multiple legacy endpoints into structured API routes under /api.

Last reviewed: 2025-08-23

NOTE: During an automated check, legacy endpoint files were detected under "backup/legacy_endpoints" and the uploads\trainee_images directory was created if missing. You can skip the archiving step if those files are already present in the backup folder.

IMPORTANT: Frontend API path note

- If your web server uses the repository `public/` folder as the web root, client-side code that uses relative paths like `api/plan.php` may resolve to `/your_site/public/api/...` and return 404. Prefer absolute, site-root paths in frontend code (for example: `/uma_musume_race_planner/api/plan.php?action=list`) or configure a JS `BASE_URL` so fetch/XHR calls point to the correct `/api/` location.

---

## 0) Summary (TL;DR)

- Back up your .env, database, and user uploads.
- Create backup\legacy_endpoints and move legacy endpoints there (see section 2).
- Stop Apache (recommended) if replacing many files.
- Update project files (git pull or replace with the new release ZIP).
- Run Composer install/update.
- If the update includes schema changes, import uma_musume_planner.sql.
- Start Apache and verify via the “Post-Update Verification” list.

---

## 1) Before You Begin

1. Back up configuration:
   - Copy your .env: C:\xampp\htdocs\uma_musume_race_planner\.env
2. Back up uploads:
   - C:\xampp\htdocs\uma_musume_race_planner\uploads\trainee_images\
3. Back up database:
   - phpMyAdmin: Export database uma_musume_planner (Structure + Data).
   - Or CLI:
     - "C:\xampp\mysql\bin\mysqldump.exe" -u root -p uma_musume_planner > C:\backup\uma_musume_planner_backup_YYYYMMDD.sql
4. Confirm PHP and Composer:
   - PHP: C:\xampp\php\php.exe -v (>= 8.1)
   - Composer: composer -V (or C:\xampp\php\php.exe composer.phar -V)
5. Optional: Stop Apache to avoid partial loads
   - XAMPP Control Panel → Stop Apache

---

## 2) Modernization: Archive Legacy Endpoints

Several one-off root endpoints have been superseded by consolidated APIs in /api. Keep a local backup so you can restore quickly if needed.

- Ensure backup\ is in .gitignore.
- Create: C:\xampp\htdocs\uma_musume_race_planner\backup\legacy_endpoints
- Move the following legacy files (only if present):

  - get_plan_attributes.php
  - get_plan_distance_grades.php
  - get_plan_goals.php
  - get_plan_predictions.php
  - get_plan_skills.php
  - get_plan_style_grades.php
  - get_plan_terrain_grades.php
  - get_plan_turns.php
  - get_progress_chart_data.php
  - get_autosuggest_backup.php

PowerShell (run in project root):

- New-Item -ItemType Directory -Force -Path '.\backup\legacy_endpoints' | Out-Null
- $legacy = @(
    'get_plan_attributes.php',
    'get_plan_distance_grades.php',
    'get_plan_goals.php',
    'get_plan_predictions.php',
    'get_plan_skills.php',
    'get_plan_style_grades.php',
    'get_plan_terrain_grades.php',
    'get_plan_turns.php',
    'get_progress_chart_data.php',
    'get_autosuggest_backup.php'
  )
- foreach ($f in $legacy) { if (Test-Path $f) { Move-Item -Force $f '.\backup\legacy_endpoints\' } }

What remains in web root (in active use):

- get_plans.php
- get_stats.php
- fetch_plan_details.php
- export_plan_data.php
- get_activities.php
- get_skill_reference.php
- get_autosuggest.php
- get_plan_section.php
- handle_plan_crud.php
- plan_details_modal.php
- quick_create_plan_modal.php
- plus: includes\*, components\*, assets\*, api\*

Important: Progress chart endpoint

- The modal uses /api/progress.php?action=chart&plan_id={id}

- If you had been using get_progress_chart_data.php, archive it and use the /api route going forward.

---

## 3) Update the Code

A) If using Git:

- cd C:\xampp\htdocs\uma_musume_race_planner
- git fetch --all
- git pull

B) If using a ZIP release:

- Extract the new version over C:\xampp\htdocs\uma_musume_race_planner
- Do not overwrite your .env unless you intend to reset it

---

## 4) Composer Dependencies

From the project root:

- composer install --no-dev --optimize-autoloader
- If the update requires newer deps: composer update --no-dev --optimize-autoloader

Target: PHP >= 8.1, using standard libraries (e.g., Monolog).

---

## 5) Environment Configuration

Ensure .env exists (copy .env.example → .env if missing).

Typical local settings:

- DB_HOST=127.0.0.1
- DB_NAME=uma_musume_planner
- DB_USER=root
- DB_PASS=
- APP_URL=<http://localhost/uma_musume_race_planner>
- Optional:
  - APP_THEME_COLOR=#7d2b8b
  - APP_VERSION=v1.4.0
  - LAST_UPDATED="August 23, 2025"

Security:

- Never commit .env.
- Keep .env backups private.

---

## 6) Database Schema Updates

If the release includes DB schema changes, import:

- phpMyAdmin → uma_musume_planner → Import → uma_musume_planner.sql → Go
- Or CLI:
  - "C:\xampp\mysql\bin\mysql.exe" -u root -p uma_musume_planner < C:\xampp\htdocs\uma_musume_race_planner\uma_musume_planner.sql

Notes:

- uma_musume_planner.sql creates/updates schema and inserts lookup rows with INSERT IGNORE where appropriate. Safe to re-run.

- Do NOT run sample_data.sql on live data (it truncates tables).

---

## 7) File/Folder Permissions

On Windows (XAMPP), ensure the app can write to:

- uploads\trainee_images\

Check existence:

- C:\xampp\htdocs\uma_musume_race_planner\uploads\trainee_images\

---

## 8) Start Apache and Post-Update Verification

1) Start Apache (XAMPP Control Panel)
2) Open <http://localhost/uma_musume_race_planner/>

Core API checks (/api):

- [API Stats](http://localhost/uma_musume_race_planner/api/stats.php?action=get)
- [API Plan List](http://localhost/uma_musume_race_planner/api/plan.php?action=list)
- [API Plan Get](http://localhost/uma_musume_race_planner/api/plan.php?action=get&id=1)
- [API Plan Section (Attributes)](http://localhost/uma_musume_race_planner/api/plan_section.php?type=attributes&id=1)
- [API Plan Section (Turns)](http://localhost/uma_musume_race_planner/api/plan_section.php?type=turns&id=1)
- [API Plan Section (Skills)](http://localhost/uma_musume_race_planner/api/plan_section.php?type=skills&id=1)
- [API Autosuggest](http://localhost/uma_musume_race_planner/api/autosuggest.php?action=get&field=name&query=a)
- [API Activity](http://localhost/uma_musume_race_planner/api/activity.php?action=get)
- [API Progress Chart](http://localhost/uma_musume_race_planner/api/progress.php?action=chart&plan_id=1)

Root endpoints (kept for compatibility):

- [get_stats.php](http://localhost/uma_musume_race_planner/get_stats.php)
- [get_plans.php](http://localhost/uma_musume_race_planner/get_plans.php)
- [fetch_plan_details.php](http://localhost/uma_musume_race_planner/fetch_plan_details.php?id=1)
- [get_plan_section.php](http://localhost/uma_musume_race_planner/get_plan_section.php?id=1&type=attributes)
- [get_skill_reference.php](http://localhost/uma_musume_race_planner/get_skill_reference.php?search=speed)
- [get_activities.php](http://localhost/uma_musume_race_planner/get_activities.php)
- [export_plan_data.php](http://localhost/uma_musume_race_planner/export_plan_data.php?id=1) (and &format=txt)

UI sanity checks:

- Plan Details modal opens and loads data
- Save changes succeeds (handle_plan_crud.php returns success)
- Skill autosuggest returns results
- Trainee image upload works (uploads\trainee_images\)
- “Export as TXT” downloads a .txt file
- Progress chart renders via /api/progress.php

---

## 9) Troubleshooting

- White screen / PHP errors:
  - Check php_errors.log in project root
  - Use logger output for details
- Composer issues:
  - Delete vendor\ and run composer install again
- DB connection errors:
  - Verify .env DB_* values; ensure DB exists and user has permissions
- 404s on endpoints:
  - You may have archived a file still referenced by the UI; restore from backup\legacy_endpoints or update the frontend path to the /api route
- Chart not loading:
  - Confirm /api/progress.php path and that turns data exists

---

## 10) Rollback

If the update fails:

- Stop Apache.
- Restore your .env, vendor\, and code from backup or git.
- Restore DB:
  - "C:\xampp\mysql\bin\mysql.exe" -u root -p uma_musume_planner < C:\backup\uma_musume_planner_backup_YYYYMMDD.sql
- If you archived endpoints and need them back, move them from backup\legacy_endpoints to web root.
- Start Apache and re-test.

---

## 11) Notes for Future Updates

- Prefer consolidated /api endpoints (plan_section, plan, stats, progress) over one-file-per-section scripts.
- Keep a changelog of schema and endpoint changes.
- Never run sample_data.sql on important data.
