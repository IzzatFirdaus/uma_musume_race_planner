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
│   ├── ExportModal.php           # Export format selector
│   ├── ExportProgress.php        # Export progress indicator
│   └── ExportHistory.php         # Past exports list
├── Import/
│   ├── ImportModal.php           # Import file upload
│   ├── ImportPreview.php         # Preview before import
│   └── ImportProgress.php        # Import progress indicator
└── Shared/
    ├── Modal.php                 # Base modal component
    ├── Toast.php                 # Toast notifications
    ├── ConfirmDialog.php         # Confirmation dialogs
    └── LoadingSpinner.php        # Loading states
```

### Blade Components

```text
resources/views/components/
├── layout/
│   ├── app.blade.php             # Main app layout
│   ├── guest.blade.php           # Guest layout
│   └── navigation.blade.php      # Navigation bar
├── umamusume/
│   ├── skill-card.blade.php      # Skill display card
│   ├── stamina-bar.blade.php     # Stamina visual meter
│   ├── aptitude-inputs.blade.php # Aptitude form inputs
│   └── growth-rate-inputs.blade.php # Growth rate inputs
├── forms/
│   ├── input.blade.php           # Text input
│   ├── select.blade.php          # Select dropdown
│   ├── checkbox.blade.php        # Checkbox input
│   ├── textarea.blade.php        # Textarea input
│   └── label.blade.php           # Form label
├── ui/
│   ├── button.blade.php          # Button component
│   ├── badge.blade.php           # Badge component
│   ├── card.blade.php            # Card container
│   ├── alert.blade.php           # Alert messages
│   └── tabs.blade.php            # Tab navigation
└── icons/
    ├── speed.blade.php           # Speed stat icon
    ├── stamina.blade.php         # Stamina stat icon
    ├── power.blade.php           # Power stat icon
    ├── guts.blade.php            # Guts stat icon
    └── wit.blade.php             # Wit stat icon
```

## Service Layer

### Core Services

```text
app/Services/
├── CareerRunService.php          # Career run business logic
├── StatProgressService.php       # Stat tracking logic
├── SkillManagementService.php    # Skill acquisition logic
├── StorageService.php            # Local/Account storage abstraction
├── ExportService.php             # Export to JSON/CSV/Excel/MD
├── ImportService.php             # Import from legacy formats
├── SnapshotService.php           # Snapshot creation/comparison
└── ActivityLogService.php        # Activity logging
```

## Storage Architecture

### Dual Storage Mode

```text
┌─────────────────────────────────────────────────────────────┐
│                    Storage Abstraction                       │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────┐         ┌──────────────────┐         │
│  │   Local Storage  │         │ Account Storage  │         │
│  ├──────────────────┤         ├──────────────────┤         │
│  │ • Browser        │         │ • Database       │         │
│  │ • localStorage   │         │ • User-scoped    │         │
│  │ • UUID-based     │         │ • Auth required  │         │
│  │ • No auth needed │         │ • Persistent     │         │
│  │ • Convertible    │         │ • Shareable      │         │
│  └──────────────────┘         └──────────────────┘         │
│           │                            │                    │
│           └────────────┬───────────────┘                    │
│                        │                                    │
│                        ▼                                    │
│              ┌──────────────────┐                          │
│              │  Unified API     │                          │
│              │  (same methods)  │                          │
│              └──────────────────┘                          │
└─────────────────────────────────────────────────────────────┘
```

### Storage Mode Logic

- **Local Mode**: `storage_mode = 'local'`, `user_id = null`, `local_uuid` generated
- **Account Mode**: `storage_mode = 'account'`, `user_id` set, `local_uuid = null`
- **Conversion**: Copy local run to account, update `storage_mode` and `user_id`

## Export/Import Formats

### Export Formats

1. **JSON** - Full data structure, machine-readable
2. **CSV** - Flat stat progression, spreadsheet-compatible
3. **Excel** - Multi-sheet workbook with formatting
4. **Markdown** - Human-readable report format

### Import Sources

1. **Legacy Tracker v1** - JSON format
2. **Legacy Tracker v2** - CSV format
3. **Legacy Tracker v3** - Excel format
4. **Legacy Tracker v4** - Custom XML format
5. **Legacy Tracker v5** - JSON with different schema

## API Routes

### RESTful API v1

```text
GET    /api/v1/uma-musume              # List all characters
GET    /api/v1/uma-musume/{id}         # Get character details
POST   /api/v1/uma-musume              # Create character
PUT    /api/v1/uma-musume/{id}         # Update character
DELETE /api/v1/uma-musume/{id}         # Delete character

GET    /api/v1/career-runs             # List career runs
GET    /api/v1/career-runs/{id}        # Get run details
POST   /api/v1/career-runs             # Create run
PUT    /api/v1/career-runs/{id}        # Update run
DELETE /api/v1/career-runs/{id}        # Delete run

GET    /api/v1/skills                  # List skills
GET    /api/v1/skills/search           # Search skills (EN+JP)

POST   /api/v1/career-runs/{id}/stats  # Log stat progress
POST   /api/v1/career-runs/{id}/skills # Acquire/skip skill
POST   /api/v1/career-runs/{id}/snapshot # Create snapshot

GET    /api/v1/export/{id}             # Export run
POST   /api/v1/import                  # Import data
```

## Enums

### Core Enums

```php
enum StorageMode: string {
    case LOCAL = 'local';
    case ACCOUNT = 'account';
}

enum CareerYear: string {
    case JUNIOR = 'junior';
    case CLASSIC = 'classic';
    case SENIOR = 'senior';
}

enum CareerStatus: string {
    case ONGOING = 'ongoing';
    case FINISHED = 'finished';
    case FAILED = 'failed';
}

enum UmaClass: string {
    case DEBUT = 'debut';
    case PRE_OPEN = 'pre_open';
    case OPEN = 'open';
    case GRADE_3 = 'grade_3';
    case GRADE_2 = 'grade_2';
    case GRADE_1 = 'grade_1';
    case LEGEND = 'legend';
}

enum SkillStatus: string {
    case ACQUIRED = 'acquired';
    case SKIPPED = 'skipped';
    case SUGGESTED = 'suggested';
}

enum SkillType: string {
    case SPEED = 'speed';
    case ACCELERATION = 'acceleration';
    case RECOVERY = 'recovery';
    case POSITION = 'position';
    case STAMINA = 'stamina';
    case DEBUFF = 'debuff';
    case UNIQUE = 'unique';
}

enum Aptitude: string {
    case S = 'S';
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
    case F = 'F';
    case G = 'G';
}
```

## Events

### Domain Events

```text
app/Events/
├── CareerRunCreated.php          # Fired when run created
├── CareerRunUpdated.php          # Fired when run updated
├── CareerRunCompleted.php        # Fired when run finished/failed
├── StatProgressLogged.php        # Fired when stats logged
├── SkillAcquired.php             # Fired when skill acquired
├── SnapshotCreated.php           # Fired when snapshot created
└── StorageModeConverted.php      # Fired when local→account
```

## Jobs

### Background Jobs

```text
app/Jobs/
├── ExportCareerRunJob.php        # Export run to file
├── ImportLegacyDataJob.php       # Import from legacy format
├── GenerateThumbnailJob.php      # Generate image thumbnail
└── CleanupOldExportsJob.php      # Cleanup old export files
```

## Policies

### Authorization Policies

```text
app/Policies/
├── UmaMusumePolicy.php           # Character authorization
├── CareerRunPolicy.php           # Run authorization (storage-aware)
└── SnapshotPolicy.php            # Snapshot authorization
```

## Testing Strategy

### Test Coverage

```text
tests/
├── Feature/
│   ├── Livewire/
│   │   ├── CareerRunListTest.php
│   │   ├── StatLoggerTest.php
│   │   ├── SkillManagerTest.php
│   │   └── StorageConversionTest.php
│   ├── Api/
│   │   ├── UmaMusumeApiTest.php
│   │   └── CareerRunApiTest.php
│   └── Services/
│       ├── ExportServiceTest.php
│       └── ImportServiceTest.php
├── Unit/
│   ├── Models/
│   │   ├── UmaMusumeTest.php
│   │   └── CareerRunTest.php
│   └── Enums/
│       └── StorageModeTest.php
└── Browser/
    └── CareerRunFlowTest.php     # Playwright E2E test
```

## Performance Considerations

### Optimization Strategies

1. **Lazy Loading**: Use `wire:init` for heavy components (charts, tables)
2. **Caching**: Cache skill lists, character data (Redis)
3. **Pagination**: Paginate run lists, stat tables
4. **Eager Loading**: Prevent N+1 queries on relationships
5. **Database Indexing**: Index foreign keys, search fields
6. **Asset Optimization**: Lazy load images, use thumbnails

### Database Indexes

```sql
-- CareerRun indexes
INDEX idx_career_runs_user_storage (user_id, storage_mode);
INDEX idx_career_runs_local_uuid (local_uuid);
INDEX idx_career_runs_status (status);

-- StatProgress indexes
INDEX idx_stat_progress_run_turn (career_run_id, turn_number);

-- SkillCareerRun indexes
INDEX idx_skill_career_run_status (career_run_id, status);

-- Skills indexes
INDEX idx_skills_name (name);
INDEX idx_skills_name_jp (name_jp);
FULLTEXT INDEX ft_skills_search (name, name_jp, description);
```

## Security Considerations

### Security Measures

1. **Authorization**: Policy-based access control for account runs
2. **CSRF Protection**: Laravel's built-in CSRF tokens
3. **XSS Prevention**: Blade's automatic escaping
4. **SQL Injection**: Eloquent ORM parameterized queries
5. **File Upload**: Validate image types, sizes, sanitize filenames
6. **API Rate Limiting**: Throttle API requests
7. **Local Storage**: Encrypt sensitive data in localStorage

## Accessibility (WCAG 2.1 AA)

### Accessibility Features

1. **Keyboard Navigation**: Full keyboard support, focus indicators
2. **Screen Reader**: ARIA labels, semantic HTML
3. **Color Contrast**: Minimum 4.5:1 contrast ratio
4. **Focus Management**: Proper focus trapping in modals
5. **Error Messages**: Clear, descriptive error messages
6. **Form Labels**: All inputs have associated labels
7. **Skip Links**: Skip to main content link

## Deployment

### Deployment Checklist

- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed skills database: `php artisan db:seed --class=SkillSeeder`
- [ ] Build assets: `npm run build`
- [ ] Optimize: `php artisan optimize`
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Set up queue worker: `php artisan queue:work`
- [ ] Set up scheduler: Add cron job for `php artisan schedule:run`
- [ ] Configure storage: `php artisan storage:link`

## Future Enhancements (Phase 2)

### Planned Features

1. **AI Skill Recommendations**: ML-based skill suggestions
2. **Stat Trend Visualization**: Interactive charts with Chart.js
3. **Snapshot Comparison**: Side-by-side race-day comparisons
4. **Collaborative Runs**: Share runs with other users
5. **Mobile App**: React Native companion app
6. **Real-time Sync**: WebSocket-based live updates
7. **Advanced Analytics**: Statistical analysis, meta reports
8. **Custom Scenarios**: Support for non-URA scenarios

---

## Revision History

| Version | Date       | Author | Changes                 |
| ------- | ---------- | ------ | ----------------------- |
| 1.0     | 2024-01-XX | Team   | Initial design document |

---

**End of Design Document**
