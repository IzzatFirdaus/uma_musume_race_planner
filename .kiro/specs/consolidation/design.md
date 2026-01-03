# Uma Musume Planner Consolidation - Design

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                        Presentation Layer                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │
│  │   Livewire  │  │    Blade    │  │  Alpine.js  │  │  Tailwind   │ │
│  │  Components │  │  Templates  │  │ Interactions│  │    CSS v4   │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │
└─────────────────────────────────┬───────────────────────────────────┘
                                  │
┌─────────────────────────────────┴───────────────────────────────────┐
│                        Application Layer                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │
│  │ Controllers │  │  Services   │  │   Actions   │  │    Jobs     │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │
└─────────────────────────────────┬───────────────────────────────────┘
                                  │
┌─────────────────────────────────┴───────────────────────────────────┐
│                         Domain Layer                                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │
│  │   Models    │  │   Events    │  │  Policies   │  │    Enums    │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │
└─────────────────────────────────┬───────────────────────────────────┘
                                  │
┌─────────────────────────────────┴───────────────────────────────────┐
│                      Infrastructure Layer                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │
│  │  Eloquent   │  │   Exports   │  │   Storage   │  │    Cache    │ │
│  │Repositories │  │  (Excel)    │  │   (Files)   │  │   (Redis)   │ │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │
└─────────────────────────────────────────────────────────────────────┘
```

## Data Model Design

### Entity Relationship Diagram

```text
┌─────────────────┐       ┌─────────────────┐
│   UmaMusume     │       │     Skill       │
├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │
│ name            │       │ name            │
│ name_jp         │       │ name_jp         │
│ image_path      │       │ description     │
│ thumbnail_path  │       │ type            │
│ aptitude_style  │       │ sp_cost         │
│ aptitude_dist   │       │ best_for        │
│ aptitude_track  │       │ icon            │
│ growth_speed    │       └────────┬────────┘
│ growth_stamina  │                │
│ growth_power    │                │
│ growth_guts     │                │
│ growth_wit      │                │
│ created_at      │                │
│ updated_at      │                │
│ deleted_at      │                │
└────────┬────────┘                │
         │                         │
         │ 1:N                     │
         ▼                         │
┌─────────────────────┐            │
│     CareerRun       │            │
├─────────────────────┤            │
│ id                  │            │
│ uma_musume_id       │◄───────────┤
│ user_id (nullable)  │            │
│ storage_mode        │ (local/account)
│ local_uuid          │ (for local runs)
│ scenario            │ (default: URA)
│ year                │            │
│ status              │            │
│ uma_class           │            │
│ current_turn        │            │
│ current_race        │            │
│ total_sp_available  │ ◄── CANONICAL NAME
│ stamina_percentage  │ ◄── CANONICAL NAME
│ mood                │            │
│ conditions          │            │
│ notes               │            │
│ created_at          │            │
│ updated_at          │            │
│ deleted_at          │            │
└────────┬────────────┘            │
         │                         │
    ┌────┴────┬────────┐           │
    │         │        │           │
    ▼         ▼        ▼           │
┌───────────────┐ ┌─────────────────┐  │
│ StatProgress  │ │SkillCareerRun   │◄─┘
├───────────────┤ ├─────────────────┤
│ id            │ │ id              │
│ career_run_id │ │ career_run_id   │ ◄── CANONICAL (not run_id)
│ turn_number   │ │ skill_id        │ ◄── CANONICAL (not turn)
│ speed         │ │ status          │ (acquired/skipped/suggested)
│ stamina       │ │ turn_acquired   │ (required if acquired)
│ power         │ │ notes           │
│ guts          │ │ created_at      │
│ wit           │ │ updated_at      │
│ created_at    │ └─────────────────┘
│ updated_at    │
└───────────────┘

┌─────────────────────┐  ┌─────────────────┐
│   RacePrediction    │  │      Goal       │
├─────────────────────┤  ├─────────────────┤
│ id                  │  │ id              │
│ career_run_id       │  │ career_run_id   │
│ race_name           │  │ description     │
│ distance_category   │  │ target_value    │
│ track_type          │  │ achieved        │
│ venue               │  │ turn_achieved   │
│ predicted_pos       │  │ sort_order      │
│ actual_pos          │  │ created_at      │
│ sort_order          │  │ updated_at      │
│ notes               │  └─────────────────┘
│ created_at          │
│ updated_at          │
└──────────┬──────────┘
           │
           │ 1:N (optional)
           ▼
┌─────────────────────┐
│   CareerSnapshot    │  ◄── NEW: Race-day snapshots
├─────────────────────┤
│ id                  │
│ career_run_id       │
│ race_prediction_id  │ (nullable, links to race)
│ turn_number         │
│ race_name           │
│ speed               │
│ stamina             │
│ power               │
│ guts                │
│ wit                 │
│ total_sp_available  │
│ stamina_percentage  │
│ mood                │
│ conditions          │
│ skills_snapshot     │ (JSON: acquired skills at this point)
│ notes               │
│ created_at          │ (immutable after creation)
└─────────────────────┘

┌─────────────────┐
│  ActivityLog    │
├─────────────────┤
│ id              │
│ user_id (nullable)│ ◄── For user scoping
│ action          │
│ model_type      │
│ model_id        │
│ description     │
│ metadata (json) │
│ created_at      │
└─────────────────┘
```

### Schema Canonicalization Notes

These names are FINAL and must be used consistently:

- `career_run_id` (never `run_id`)
- `turn_number` (never `turn`)
- `total_sp_available` (never `total_sp`)
- `stamina_percentage` (never `stamina_pct`)
- `storage_mode` enum: `local`, `account`
- `status` enum (SkillCareerRun): `acquired`, `skipped`, `suggested`

## Component Architecture

### Livewire Components

```text
app/Livewire/
├── Dashboard/
│   ├── ActivityFeed.php          # Recent activity display (user-scoped + local)
│   ├── QuickStats.php            # Dashboard statistics (by storage mode)
│   └── RecentRuns.php            # Recent career runs (local + account mixed)
├── UmaMusume/
│   ├── CharacterList.php         # Character listing with search
│   ├── CharacterForm.php         # Create/edit character
│   ├── CharacterCard.php         # Character display card
│   └── ImageUpload.php           # Image upload with preview + thumbnail
├── CareerRun/
│   ├── RunList.php               # Career run listing (mixed sources + storage badge)
│   ├── RunForm.php               # Create/edit career run
│   ├── RunDetail.php             # Full run detail view (lazy loads children)
│   ├── QuickCreate.php           # Quick create modal
│   ├── InlineEditor.php          # Inline editing panel (quick edits)
│   ├── FullscreenEditor.php      # Fullscreen tabbed editor
│   ├── StorageModeIndicator.php  # Shows "Local" or "Account" badge
│   └── ConvertToAccountButton.php # Convert action (visible when auth + local)
├── Stats/
│   ├── StatLogger.php            # Turn stat entry
│   ├── StatTable.php             # Stat progression table (wire:init)
│   ├── StatChart.php             # Stat progression chart (wire:init)
│   └── StatBar.php               # Individual stat bar
├── Skills/
│   ├── SkillSearch.php           # Skill autocomplete (debounced, EN+JP, accessible)
│   ├── SkillManager.php          # Skill acquisition with 3-state + turn
│   ├── SkillCard.php             # Skill display card
│   └── SkillList.php             # Skills listing with filters
├── Racing/
│   ├── RacePredictionEditor.php  # Race predictions with reorder
│   ├── GoalsEditor.php           # Goals checklist
│   └── SnapshotList.php          # Race-day snapshots list
├── Snapshots/
│   ├── SnapshotCreateModal.php   # Create snapshot modal
│   ├── SnapshotDetail.php        # View snapshot (immutable)
│   └── SnapshotCompare.php       # Side-by-side comparison (P2)
├── Export/
│   ├── ExportModal.php           # Export options modal
│   ├── ExportPreview.php         # Preview with format switching
│   └── CopyToClipboard.php       # Copy panel with toast
├── Import/
│   ├── ImportWizard.php          # Upload → detect → preview → confirm
│   ├── ImportPreview.php         # Field mapping preview
│   └── ImportResults.php         # Results report
├── Auth/
│   ├── ConvertRunModal.php       # Convert single local run to account
│   ├── BulkConvertModal.php      # Bulk convert local runs
│   └── DuplicateResolver.php     # Per-duplicate resolution UI
├── LocalData/
│   ├── Manager.php               # Local data management page/modal
│   ├── RunList.php               # List local runs with storage info
│   └── ImportExport.php          # Export all / Import JSON
└── Common/
    ├── DarkModeToggle.php        # Theme toggle with persistence
    ├── ConfirmModal.php          # Confirmation dialog with focus trap
    ├── Toast.php                 # Notification toast with aria-live
    ├── SkeletonLoader.php        # Loading skeleton component
    ├── DirtyStateWarning.php     # Unsaved changes warning
    └── AccessibleDropdown.php    # Listbox pattern dropdown
```

### Blade Components

```
resources/views/components/
├── layout/
│   ├── app.blade.php             # Main layout
│   ├── navigation.blade.php      # Nav bar
│   ├── sidebar.blade.php         # Side navigation
│   └── footer.blade.php          # Footer
├── forms/
│   ├── input.blade.php           # Text input
│   ├── select.blade.php          # Select dropdown
│   ├── textarea.blade.php        # Textarea
│   ├── checkbox.blade.php        # Checkbox
│   └── file-upload.blade.php     # File upload
├── buttons/
│   ├── primary.blade.php         # Primary button
│   ├── secondary.blade.php       # Secondary button
│   ├── danger.blade.php          # Danger button
│   └── icon.blade.php            # Icon button
├── umamusume/
│   ├── stat-bar.blade.php        # Stat progress bar
│   ├── aptitude-badge.blade.php  # Aptitude grade badge
│   ├── skill-card.blade.php      # Skill display card
│   ├── stamina-gauge.blade.php   # Stamina indicator
│   └── growth-indicator.blade.php # Growth rate display
└── common/
    ├── card.blade.php            # Card container
    ├── modal.blade.php           # Modal dialog
    ├── table.blade.php           # Data table
    ├── badge.blade.php           # Status badge
    └── empty-state.blade.php     # Empty state display
```

## Service Layer Design

### Services

```php
app/Services/
├── UmaMusumeService.php          # Character business logic
├── CareerRunService.php          # Career run business logic
├── StatProgressService.php       # Stat tracking logic
├── SkillService.php              # Skill management logic (with search caching)
├── SnapshotService.php           # Race-day snapshot creation (immutable)
├── ExportService.php             # Export generation with preview
├── ImportService.php             # Data import handling (with target selection)
├── ActivityLogService.php        # Activity logging (user-scoped + local)
├── ChartDataService.php          # Chart data preparation
├── ImageProcessingService.php    # Image upload, EXIF strip, thumbnail
├── LocalRunStorageService.php    # Serialize/deserialize local schema, versioning
├── ConvertLocalRunService.php    # Convert local run → DB models (single + bulk)
├── DuplicateDetectionService.php # Find duplicates during convert/import
└── ConflictDetectionService.php  # Optimistic locking / versioning
```

### Local Storage Schema

```typescript
// Local storage schema (versioned)
interface LocalCareerRun {
  schema_version: string;        // e.g., "1.0.0"
  id: string;                    // UUID (permanent, not temporary)
  created_at: string;            // ISO timestamp
  updated_at: string;            // ISO timestamp
  career_run: {
    uma_musume_id: string;       // UUID reference to local character
    title: string;
    scenario: string;
    year: string;
    status: string;
    uma_class: string;
    current_turn: number;
    current_race: string | null;
    total_sp_available: number;
    stamina_percentage: number;
    mood: string | null;
    conditions: string | null;
    notes: string | null;
  };
  stat_progress: StatProgressEntry[];
  skills: SkillEntry[];
  goals: GoalEntry[];
  race_predictions: RacePredictionEntry[];
  snapshots: SnapshotEntry[];
  activity_log: ActivityLogEntry[];  // Local activity log
}
```

### Import Adapters

```php
app/Services/Import/
├── ImportAdapterInterface.php    # Contract for import adapters
├── ImportTarget.php              # Enum: Local | Account
├── JsonImportAdapter.php         # JSON format (uma-run-tracker) - PRIMARY
├── CsvImportAdapter.php          # CSV format - SECONDARY
├── UmaRunTrackerJsonImporter.php # Specific format from uma-run-tracker
├── LegacyMySqlAdapter.php        # Direct MySQL dump (P2+)
└── FormatDetector.php            # Auto-detect import format
```

### Export Classes

```php
app/Exports/
├── CareerRunExport.php           # Full career run export
├── StatProgressExport.php        # Stats-only export
├── SkillsExport.php              # Skills-only export
└── MultiSheetExport.php          # Combined multi-sheet export
```

## API Design (Optional)

### RESTful Endpoints

```
GET    /api/v1/uma-musume              # List characters
POST   /api/v1/uma-musume              # Create character
GET    /api/v1/uma-musume/{id}         # Get character
PUT    /api/v1/uma-musume/{id}         # Update character
DELETE /api/v1/uma-musume/{id}         # Delete character

GET    /api/v1/plans                  # List plans
POST   /api/v1/plans                  # Create plan
GET    /api/v1/plans/{id}             # Get plan
PUT    /api/v1/plans/{id}             # Update plan
DELETE /api/v1/plans/{id}             # Delete plan
POST   /api/v1/plans/import           # Import plans
GET    /api/v1/plans/export           # Export plans

POST   /api/v1/plans/{id}/stats       # Add stat entry
GET    /api/v1/plans/{id}/stats       # Get stat history
PUT    /api/v1/plans/{id}/stats/{statId}  # Update stat

POST   /api/v1/plans/{id}/skills      # Add skill
PUT    /api/v1/plans/{id}/skills/{skillId}  # Update skill status
DELETE /api/v1/plans/{id}/skills/{skillId}  # Remove skill

GET    /api/v1/skills                  # List all skills
GET    /api/v1/skills/search           # Search skills

GET    /api/v1/export/career-run/{id}  # Export career run
```

## Database Migrations

### Migration Order

1. `create_uma_musumes_table`
2. `create_skills_table`
3. `create_career_runs_table`
4. `create_stat_progress_table`
5. `create_skill_career_runs_table`
6. `create_race_predictions_table`
7. `create_goals_table`
8. `create_activity_logs_table`

### Indexes

```sql
-- Performance indexes (consistent naming)
CREATE INDEX idx_career_runs_uma_musume_id ON career_runs(uma_musume_id);
CREATE INDEX idx_career_runs_user_id ON career_runs(user_id);
CREATE INDEX idx_career_runs_status ON career_runs(status);
CREATE INDEX idx_career_runs_scenario ON career_runs(scenario);
CREATE UNIQUE INDEX idx_stat_progress_run_turn ON stat_progress(career_run_id, turn_number);
CREATE INDEX idx_skill_career_runs_career_run_id ON skill_career_runs(career_run_id);
CREATE INDEX idx_skill_career_runs_skill_id ON skill_career_runs(skill_id);
CREATE UNIQUE INDEX idx_skill_career_runs_composite ON skill_career_runs(career_run_id, skill_id);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);
CREATE INDEX idx_activity_logs_model ON activity_logs(model_type, model_id);
CREATE INDEX idx_skills_name ON skills(name);
CREATE INDEX idx_skills_name_jp ON skills(name_jp);
CREATE INDEX idx_race_predictions_career_run_id ON race_predictions(career_run_id);
CREATE INDEX idx_goals_career_run_id ON goals(career_run_id);
CREATE INDEX idx_career_snapshots_career_run_id ON career_snapshots(career_run_id);
CREATE INDEX idx_career_snapshots_turn_number ON career_snapshots(turn_number);
```

## Search Contract (Internal API)

### Skill Search Endpoint

Used by Livewire `SkillSearch` component.

```text
GET /internal/skills/search?q={query}&limit={limit}

Query params:
- q: Search term (matches name OR name_jp, partial match)
- limit: Max results (default 10, max 50)

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Last Legs",
      "name_jp": "ラストスパート",
      "type": "speed",
      "sp_cost": 40,
      "icon": "speed-icon.svg"
    }
  ],
  "meta": {
    "query": "last",
    "count": 1,
    "cached": true
  }
}

Performance:
- Rate limited: 60 requests/minute per IP
- Cached: 5 minute TTL
- Response time: < 200ms
```

### Character Search Endpoint

```text
GET /internal/uma-musume/search?q={query}&limit={limit}

Response shape same as skills, returns id, name, name_jp, thumbnail_path
```

## UI/UX Design

### Theme Implementation Strategy

Use Tailwind `dark:` classes for most styling, with CSS custom properties for game-specific palette tokens.

```javascript
// tailwind.config.js
module.exports = {
  darkMode: 'class', // Toggle via class on <html>
  theme: {
    extend: {
      colors: {
        // Stat colors (consistent across themes)
        'stat-speed': '#3b82f6',    // blue
        'stat-stamina': '#f97316',  // orange
        'stat-power': '#ef4444',    // red
        'stat-guts': '#eab308',     // yellow
        'stat-wit': '#22c55e',      // green
        
        // Aptitude grade colors
        'grade-s': '#fbbf24',       // gold
        'grade-a': '#f472b6',       // pink
        'grade-b': '#60a5fa',       // light blue
        'grade-c': '#4ade80',       // green
        'grade-d': '#a78bfa',       // purple
        'grade-e': '#94a3b8',       // gray
        'grade-f': '#78716c',       // stone
        'grade-g': '#57534e',       // dark stone
        
        // Uma Musume accent (game-inspired)
        'uma-primary': '#e94560',
        'uma-secondary': '#7d2b8b',
      }
    }
  }
}
```

### Color Palette (Dark Mode)

```css
:root.dark {
  --bg-primary: #1a1a2e;
  --bg-secondary: #16213e;
  --bg-card: #0f3460;
  --text-primary: #eaeaea;
  --text-secondary: #a0a0a0;
  --accent-primary: #e94560;
  --accent-secondary: #7d2b8b;
  --success: #4ade80;
  --warning: #fbbf24;
  --error: #ef4444;
}
```

### Color Palette (Light Mode)

```css
:root {
  --bg-primary: #ffffff;
  --bg-secondary: #f8fafc;
  --bg-card: #ffffff;
  --text-primary: #1e293b;
  --text-secondary: #64748b;
  --accent-primary: #e94560;
  --accent-secondary: #7d2b8b;
  --success: #22c55e;
  --warning: #f59e0b;
  --error: #dc2626;
}
```

### Theme Toggle Behavior

1. Check `localStorage.theme` on page load
2. If not set, check `prefers-color-scheme`
3. Apply `dark` class to `<html>` element
4. Toggle persists to `localStorage`
5. System preference changes detected via `matchMedia` listener

### Responsive Breakpoints

```css
/* Mobile first */
@media (min-width: 640px) { /* sm */ }
@media (min-width: 768px) { /* md */ }
@media (min-width: 1024px) { /* lg */ }
@media (min-width: 1280px) { /* xl */ }
```

### Animation & Motion

```css
/* Respect reduced motion preference */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

## Testing Strategy

### Test Categories

```
tests/
├── Unit/
│   ├── Models/
│   ├── Services/
│   └── Helpers/
├── Feature/
│   ├── Livewire/
│   ├── Api/
│   └── Export/
└── Browser/
    └── Playwright/
```

### Coverage Targets

- Unit tests: 90%+ coverage
- Feature tests: 80%+ coverage
- Critical paths: 100% coverage
