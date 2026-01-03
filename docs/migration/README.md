# Uma Musume Planner - Data Migration Guide

## Overview

This guide covers migrating data from the five legacy Uma Musume tracking applications into the consolidated Uma Musume Planner.

### Supported Legacy Formats

| Source Application | Format | Priority |
|--------------------|--------|----------|
| uma-run-tracker | JSON | Primary |
| umamusume-tracker | JSON/API | Secondary |
| uma-tracker | SQLite/MySQL | Secondary |
| uma_musume_race_planner | MySQL dump | Tertiary |
| uma-tracker-form | CSV export | Tertiary |

---

## Quick Start

### Using the Import Wizard

1. Navigate to `/import` in the application
2. Upload your legacy data file
3. The system auto-detects the format
4. Review the field mapping
5. Choose target: Local Storage or Account
6. Confirm and import

### Using Migration Scripts

For bulk migrations or automated processes:

```bash
# JSON import (uma-run-tracker format)
php artisan migrate:legacy-json storage/imports/legacy-data.json

# CSV import
php artisan migrate:legacy-csv storage/imports/legacy-data.csv

# MySQL dump import
php artisan migrate:legacy-mysql storage/imports/legacy-dump.sql
```

---

## JSON Import (Primary)

### uma-run-tracker Format

The primary import format from the uma-run-tracker application:

```json
{
  "schema_version": "1.0.0",
  "exported_at": "2025-01-03T12:00:00Z",
  "runs": [
    {
      "id": "uuid-string",
      "character": {
        "name": "Special Week",
        "name_jp": "スペシャルウィーク"
      },
      "year": "classic",
      "status": "ongoing",
      "class": "open",
      "current_turn": 45,
      "sp_available": 350,
      "stamina_pct": 85,
      "stats": [
        { "turn": 1, "speed": 150, "stamina": 120, "power": 130, "guts": 100, "wit": 110 }
      ],
      "skills": [
        { "name": "Last Legs", "status": "acquired", "turn_acquired": 15 }
      ],
      "goals": [
        { "description": "Win Japan Cup", "achieved": false }
      ],
      "created_at": "2025-01-01T10:00:00Z"
    }
  ]
}
```

### Field Mapping

| Legacy Field | Canonical Field | Notes |
|--------------|-----------------|-------|
| `sp_available` | `total_sp_available` | Renamed |
| `stamina_pct` | `stamina_percentage` | Renamed |
| `turn` | `turn_number` | In stat_progress |
| `class` | `uma_class` | Enum mapping |
| `status` | `status` | Direct mapping |

### Running JSON Import

```bash
# Via artisan command
php artisan migrate:legacy-json path/to/file.json --target=account

# Options:
#   --target=local|account  Import destination (default: local)
#   --dry-run               Validate without importing
#   --skip-duplicates       Skip duplicate detection warnings
```

---

## CSV Import

### Expected CSV Format

```csv
character_name,year,status,class,turn,speed,stamina,power,guts,wit,sp_available
"Special Week",classic,ongoing,open,45,850,720,680,550,620,350
```

### CSV Import Command

```bash
php artisan migrate:legacy-csv path/to/file.csv --target=account

# Options:
#   --delimiter=,           CSV delimiter (default: comma)
#   --has-header            First row is header (default: true)
#   --encoding=UTF-8        File encoding
```

---

## MySQL Dump Import

For migrating from uma_musume_race_planner or uma-tracker:

### Prerequisites

1. Export your legacy database:

```bash
mysqldump -u user -p legacy_db > legacy_dump.sql
```

1. Ensure the dump is UTF-8 encoded

### Import Command

```bash
php artisan migrate:legacy-mysql path/to/dump.sql

# Options:
#   --source=race_planner   Source application identifier
#   --dry-run               Validate without importing
```

### Table Mapping

| Legacy Table | Target Table | Notes |
|--------------|--------------|-------|
| `characters` | `uma_musumes` | Field mapping applied |
| `runs` | `career_runs` | Status enum converted |
| `stats` | `stat_progress` | Turn field renamed |
| `skills` | `skill_career_runs` | Status enum converted |

---

## Duplicate Detection

The migration system detects potential duplicates based on:

- Same character name
- Same run title/date
- Similar creation timestamp

### Handling Duplicates

When duplicates are detected:

1. **Skip**: Don't import the duplicate
2. **Create Anyway**: Import as new record
3. **Review**: Mark for manual review

```bash
# Skip all duplicates automatically
php artisan migrate:legacy-json file.json --skip-duplicates

# Interactive mode (default)
php artisan migrate:legacy-json file.json
# Prompts for each duplicate found
```

---

## Validation & Error Handling

### Dry Run

Always run a dry-run first:

```bash
php artisan migrate:legacy-json file.json --dry-run
```

Output:

```
Validating import file...
✓ Schema version: 1.0.0
✓ Records found: 25
✓ Characters: 5
✓ Career runs: 25
✓ Stat entries: 1,250
✓ Skills: 180

Validation complete. No errors found.
Run without --dry-run to import.
```

### Error Report

If validation fails, an error report is generated:

```
Validation Errors:
Row 15: Invalid status value 'complete' (expected: ongoing, finished, failed)
Row 23: Missing required field 'character_name'
Row 45: Invalid turn number -1 (must be positive)

3 errors found. Import aborted.
Error report saved to: storage/imports/errors-2025-01-03.csv
```

### Error CSV Format

```csv
row,field,value,error
15,status,complete,"Invalid enum value"
23,character_name,,"Required field missing"
45,turn,-1,"Must be positive integer"
```

---

## Post-Migration Verification

### Verify Counts

```bash
php artisan migrate:verify

# Output:
# Characters: 5 imported, 5 in database ✓
# Career runs: 25 imported, 25 in database ✓
# Stat entries: 1,250 imported, 1,250 in database ✓
# Skills: 180 imported, 180 in database ✓
```

### Verify Data Integrity

```bash
php artisan migrate:verify --detailed

# Checks:
# - All foreign keys valid
# - All enum values valid
# - No orphaned records
# - Timestamps reasonable
```

---

## Rollback

If migration needs to be undone:

```bash
# Rollback last migration batch
php artisan migrate:rollback-legacy

# Rollback specific import
php artisan migrate:rollback-legacy --batch=5
```

Migration batches are tracked in `legacy_migrations` table.

---

## Programmatic Migration

### Using the ImportService

```php
use App\Services\ImportService;
use App\Services\Import\ImportTarget;

$importService = app(ImportService::class);

// Import from JSON
$result = $importService->importJson(
    filePath: '/path/to/file.json',
    target: ImportTarget::Account,
    userId: auth()->id()
);

// Check results
echo "Created: {$result->created}";
echo "Skipped: {$result->skipped}";
echo "Errors: " . count($result->errors);
```

### Using Import Adapters

```php
use App\Services\Import\JsonImportAdapter;
use App\Services\Import\FormatDetector;

// Auto-detect format
$detector = new FormatDetector();
$format = $detector->detect($filePath);

// Use appropriate adapter
$adapter = match($format) {
    'json' => new JsonImportAdapter(),
    'csv' => new CsvImportAdapter(),
    default => throw new UnsupportedFormatException()
};

$data = $adapter->parse($filePath);
$validated = $adapter->validate($data);
$result = $adapter->import($validated, $target);
```

---

## Troubleshooting

### Common Issues

**"Invalid schema version"**

- Ensure your export is from a supported application version
- Check the schema_version field in your JSON

**"Character not found"**

- Characters must exist before importing runs
- Use `--create-characters` flag to auto-create

**"Encoding errors"**

- Ensure files are UTF-8 encoded
- For CSV, specify encoding: `--encoding=UTF-8`

**"Memory limit exceeded"**

- For large imports, increase PHP memory limit
- Or use chunked import: `--chunk-size=100`

### Getting Help

```bash
# View all migration commands
php artisan list migrate

# Get help for specific command
php artisan help migrate:legacy-json
```

---

## Migration Scripts Location

All migration scripts are located in:

- `database/migrations/legacy/` - Schema transformations
- `app/Console/Commands/` - Artisan commands
- `app/Services/Import/` - Import adapters and services
