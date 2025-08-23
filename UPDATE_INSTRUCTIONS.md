# Update Instructions — Uma Musume Race Planner (XAMPP on Windows)

This document explains how to safely update your local deployment located at:

`C:\xampp\htdocs\uma_musume_race_planner`

The app is PHP + MySQL (no framework). It uses Composer for PHP libraries and SQL files for schema/data.

This version consolidates legacy endpoints into structured API routes under `/api`.

Last reviewed: 2025-08-23

---

## TL;DR

- Back up your `.env`, database, and user uploads.
- Archive legacy endpoints to `backup\legacy_endpoints` (see section 2).
- Stop Apache if replacing many files.
- Update code (git pull or replace with a release), then run Composer.
- If the release includes schema changes, import `uma_musume_planner.sql`.
- Start Apache and verify APIs listed in the Post-Update checks.

---

## 1) Before you begin

1. Back up configuration:

   - Copy `.env`: `C:\xampp\htdocs\uma_musume_race_planner\.env`

2. Back up uploads:

   - `C:\xampp\htdocs\uma_musume_race_planner\uploads\trainee_images\`

3. Back up database:

   - phpMyAdmin: Export `uma_musume_planner` (Structure + Data)

   - Or CLI (PowerShell):

     ```powershell
     "C:\xampp\mysql\bin\mysqldump.exe" -u root -p uma_musume_planner > C:\backup\uma_musume_planner_backup_YYYYMMDD.sql
     ```

4. Confirm PHP and Composer:

   - `C:\xampp\php\php.exe -v` (PHP >= 8.1)
   - `composer -V`

5. Optional: Stop Apache (XAMPP Control Panel)

---

## 2) Archive legacy endpoints

Create the archive folder and move legacy root endpoints there if present.

```powershell
New-Item -ItemType Directory -Force -Path '.\backup\legacy_endpoints' | Out-Null
$legacy = @(
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
foreach ($f in $legacy) { if (Test-Path $f) { Move-Item -Force $f '.\backup\legacy_endpoints\' } }
```

Active files that remain in web root include: `get_plans.php`, `get_stats.php`, `fetch_plan_details.php`, `export_plan_data.php`, `get_activities.php`, `get_skill_reference.php`, `get_autosuggest.php`, `get_plan_section.php`, `handle_plan_crud.php`, `plan_details_modal.php`, `quick_create_plan_modal.php`, and the `includes/`, `components/`, `assets/`, and `api/` folders.

The progress chart is available at: `/api/progress.php?action=chart&plan_id={id}`

---

## 3) Update the code

If using Git:

```powershell
cd C:\xampp\htdocs\uma_musume_race_planner
git fetch --all
git pull
```

If using a ZIP release: extract files over the project root but do not overwrite `.env`.

---

## 4) Composer

From project root:

```powershell
composer install --no-dev --optimize-autoloader
# Or when updating deps:
composer update --no-dev --optimize-autoloader
```

---

## 5) Environment

Ensure `.env` exists (copy `.env.example` if missing). Typical local settings:

```ini
DB_HOST=127.0.0.1
DB_NAME=uma_musume_planner
DB_USER=root
DB_PASS=
APP_URL=http://localhost/uma_musume_race_planner
APP_THEME_COLOR=#7d2b8b
APP_VERSION=v1.4.0
LAST_UPDATED="August 23, 2025"
```

---

## DB schema updates

If required, import `uma_musume_planner.sql` via phpMyAdmin or CLI (see the CLI example above).

---

## File / folder permissions

Ensure `uploads\trainee_images\` exists and is writable by your web server user.

---

## Start Apache and verify

Open `http://localhost/uma_musume_race_planner/` and check API endpoints (for example):

- `/api/plan.php?action=list`
- `/api/stats.php?action=get`
- `/api/progress.php?action=chart&plan_id=1`

Also verify UI functionality: plan details modal, save operations, skill autosuggest, trainee image upload, and export features.

---

## Troubleshooting

- Check `php_errors.log` in project root
- Re-run `composer install` if vendor issues occur
- Verify `.env` DB values and that the DB user has proper permissions
- If an endpoint returns 404, it may be archived in `backup\legacy_endpoints` — restore or update frontend paths

---

## Rollback

If the update fails:

- Stop Apache
- Restore `.env`, code, and `vendor/` from backups or from your previous git tag
- Restore the database from your SQL export
- Move archived endpoints back from `backup\legacy_endpoints` if required
- Start Apache and re-test

---

## Notes

- Prefer consolidated `/api` endpoints over one-file-per-section scripts.
- Keep a changelog of schema and endpoint changes.
- Do not run `sample_data.sql` on production data.
