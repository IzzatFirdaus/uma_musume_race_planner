# DIRECTORY (generated)

This file lists the current project files and important subfolders (snapshot).

## Top-level files

- .env.example — example env file
- .gitignore
- .php-cs-fixer.php
- composer.json
- composer.lock
- config.php
- README.md
- guide.php
- index.php
- index.main.backup
- phpcs.xml
- phpstan.neon
- php_errors.log
- sample_data.sql
- uma_musume_planner.sql
- test.md

## Top-level PHP endpoints / scripts

- export_plan_data.php
- fetch_plan_details.php
- get_activities.php
- get_autosuggest.php
- get_autosuggest_backup.php
- get_plan_attributes.php
- get_plan_distance_grades.php
- get_plan_goals.php
- get_plan_predictions.php
- get_plan_section.php
- get_plan_skills.php
- get_plan_style_grades.php
- get_plan_terrain_grades.php
- get_plan_turns.php
- get_plans.php
- get_progress_chart_data.php
- get_skill_reference.php
- get_stats.php
- handle_plan_crud.php

## Key directories

- components/ — reusable PHP UI components (header, footer, navbar, plan list, stats panel, etc.)
  - copy_to_clipboard.php
  - footer.php
  - header.php
  - navbar.php
  - plan-inline-details.php
  - plan-list.php
  - recent-activity.php
  - stats-panel.php
  - trainee_image_handler.php

- includes/ — app bootstrap and helpers
  - db.php (PDO helper)
  - env.php (.env loader)
  - logger.php

- css/ — styles
  - style.css
  - style_glass.css
  - style_og.css

- js/ — client JS
  - autosuggest.js
  - autosuggest.backup.js

- backup/unused/ — archived files (audit tools, rector config, tmp schema snapshots)
  - rector.php (archived)
  - move-manifest.json
  - tmp_check_schema.php
  - tmp_schema.json
  - tmp_schema_full.json
  - test_db.php
  - tools/ (archived audit tools and reports)

- vendor/ — Composer dependencies (libraries)

## Notes

- rector.php has been archived to backup/unused/rector.php and removed from the project root to avoid running Rector.
- Audit and schema-check tools were archived under backup/unused/tools/.
