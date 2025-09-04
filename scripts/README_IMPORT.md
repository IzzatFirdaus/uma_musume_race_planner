# Uma Musume Planner — Text Importer

CLI tool to import free-form “PLAN …” blocks into the database.

## Requirements

- PHP 8.1+
- MariaDB/MySQL with the schema similar to `uma_musume_planner_230825.sql`
- `includes/db.php` must return a PDO instance connected to your DB

## Usage

- From a file:

```bash
php scripts/import_plans_from_text.php --file=/absolute/path/to/plans.txt --user-id=5
```

- From STDIN:

```bash
cat plans.txt | php scripts/import_plans_from_text.php --user-id=5
```

- Dry run (no DB writes, prints parsed JSON):

```bash
php scripts/import_plans_from_text.php --file=plans.txt --user-id=5 --dry-run
```

- Override status for all imported plans (default auto-detected; options: Planning|Active|Finished|Draft|Abandoned):

```bash
php scripts/import_plans_from_text.php --file=plans.txt --user-id=5 --status=Finished
```

## What it imports

- plans (with lookups for mood, condition, strategy)
- attributes
- terrain_grades, distance_grades, style_grades
- skills (mapped to `skill_reference` by name; created if missing)
- race_predictions (from “RACE DAY PREDICTIONS”)
- goals (grouped as triples: goal name, target, result → stored as `goal = "name (target)"`, result = "result").

## Notes

- The parser is heuristic and resilient; it tolerates missing columns and minor formatting drift.
- If a `skill_reference` name is not found, it creates a stub with just `skill_name`.
- If a lookup label (mood/condition/strategy) is missing, it is created.
- `user_id` is required due to FK constraints. Use the ID of an existing user (e.g., `5` from the provided sample data).
