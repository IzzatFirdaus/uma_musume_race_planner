# Uma Musume Planner - Consolidation Specification

## Executive Summary

This document outlines the consolidation of five legacy Uma Musume tracking applications into a single, unified Laravel 12+ application. The goal is to combine the best features from each version while modernizing the codebase and improving maintainability.

## Source Applications Analysis

### 1. uma_musume_race_planner (PHP + MySQL)

**Strengths:**

- Most feature-complete plan management
- Trainee image upload functionality
- Stat progression charts
- Activity logging
- Autosuggest functionality
- Dynamic theming

**Features to Adopt:**

- Plan CRUD with image upload
- Activity log system
- Autosuggest patterns
- Quick create modal UX

### 2. umamusume-tracker (Laravel 12 + React)

**Strengths:**

- Best API design (RESTful)
- Repository pattern
- Comprehensive engineering documentation
- React Query patterns for data fetching
- Zod validation schemas

**Features to Adopt:**

- API endpoint structure
- Repository pattern (optional)
- Export service architecture
- Rate limiting configuration

### 3. uma-tracker (Laravel 11 + Blade)

**Strengths:**

- Closest to target stack
- Production-ready Excel exports
- Well-designed Blade components
- View Composers pattern
- UI Helpers

**Features to Adopt:**

- Excel export classes (direct migration)
- Blade component library
- View Composers
- UI Helper functions

### 4. uma-run-tracker (Static HTML + JS)

**Strengths:**

- Excellent accessibility implementation
- Offline-first design patterns
- Multiple design iterations
- Internationalization support
- Component-based HTML structure

**Features to Adopt:**

- Accessibility patterns (ARIA, skip links)
- Form structure and validation
- Stat bar visualization
- Dark mode implementation
- Export to text/markdown

### 5. uma-tracker-form (Native PHP + MVC)

**Strengths:**

- Simple, clean implementation
- Strong security practices
- CSRF protection pattern
- PDO abstraction

**Features to Adopt:**

- CSRF protection patterns
- Input validation logic
- Search/filter functionality

## Consolidated Feature Set

### Core Features (Must Have)

| Feature | Source | Priority |
|---------|--------|----------|
| Character CRUD | All | P0 |
| Career Run Management | All | P0 |
| Turn-by-turn Stat Logging | All | P0 |
| Skill Management | All | P0 |
| Excel Export | uma-tracker | P0 |
| Dark Mode | uma-run-tracker | P0 |
| Responsive Design | All | P0 |

### Enhanced Features (Should Have)

| Feature | Source | Priority |
|---------|--------|----------|
| Stat Progression Charts | uma_musume_race_planner | P1 |
| Image Upload | uma_musume_race_planner | P1 |
| Activity Logging | uma_musume_race_planner | P1 |
| Skill Autocomplete (Accessible) | uma_musume_race_planner | P1 |
| Quick Create Modal | uma_musume_race_planner | P1 |
| CSV Export | umamusume-tracker | P1 |
| Text/Markdown Export | uma-run-tracker | P1 |
| Import Wizard (JSON/CSV) | uma-run-tracker | P1 |
| Dual Editing Modes | uma_musume_race_planner | P1 |
| Race-day Snapshots | New | P1 |
| Local + Account Storage | uma-run-tracker | P1 |
| Export Preview + Copy | uma-tracker | P1 |

### Nice to Have Features

| Feature | Source | Priority |
|---------|--------|----------|
| REST API | umamusume-tracker | P2 |
| Snapshot Comparison | New | P2 |
| Race Predictions | uma_musume_race_planner | P1 |
| Goal Tracking | uma_musume_race_planner | P1 |
| Internationalization | uma-run-tracker | P3 |

## Technical Architecture

### Stack Decision

| Layer | Technology | Rationale |
|-------|------------|-----------|
| Backend | Laravel 12+ | Current project base, mature ecosystem |
| PHP | 8.2+ | Type safety, performance |
| Database | MySQL/MariaDB | Production-ready, existing data |
| Frontend | Livewire v3 | Server-driven, less JS complexity |
| Styling | Tailwind CSS v4 | Utility-first, consistent design |
| Interactivity | Alpine.js | Lightweight, Livewire integration |
| Charts | Chart.js | Lightweight, good documentation |
| Export | Laravel Excel | Production-proven in uma-tracker |
| Testing | Pest + Playwright | Modern testing, E2E coverage |

### Directory Structure

```text
uma-musume-planner-laravel/
├── app/
│   ├── Enums/                    # Status, Type, StorageMode enums
│   ├── Exports/                  # Excel export classes
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/              # Optional API controllers
│   │   │   └── Web/              # Web controllers
│   │   └── Middleware/
│   ├── Livewire/                 # Livewire components
│   │   ├── Dashboard/
│   │   ├── UmaMusume/
│   │   ├── CareerRun/            # Includes InlineEditor, FullscreenEditor
│   │   ├── Stats/
│   │   ├── Skills/
│   │   ├── Racing/
│   │   ├── Snapshots/            # Race-day snapshot components
│   │   ├── Export/               # Includes ExportPreview, CopyToClipboard
│   │   ├── Import/
│   │   ├── Auth/                 # ClaimDataModal, DuplicateResolver
│   │   └── Common/               # DirtyStateWarning, AccessibleDropdown
│   ├── Models/                   # Eloquent models (incl. CareerSnapshot)
│   ├── Services/                 # Business logic
│   │   └── Import/               # Import adapters
│   └── View/
│       └── Components/           # Blade components
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/
│   ├── legacy-versions/          # Legacy documentation
│   └── api/                      # API documentation
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── components/
│       │   └── umamusume/        # Uma-specific components
│       ├── layouts/
│       └── livewire/
├── routes/
│   ├── api.php
│   └── web.php
└── tests/
    ├── Feature/
    │   └── Livewire/
    ├── Unit/
    └── Browser/
        └── Playwright/           # E2E tests
```

## Migration Strategy

### Phase 1: Database Consolidation

1. Analyze all legacy schemas
2. Create unified schema with all required fields
3. Add `storage_mode` and `local_uuid` to `career_runs`
4. Create `career_snapshots` table for race-day snapshots
5. Write migrations with proper indexes (canonical naming)
6. Create seeders with sample data
7. Test with existing data imports
8. **Verify schema canonicalization** (`career_run_id`, `turn_number`, `total_sp_available`, `stamina_percentage`)

### Phase 2: Model & Service Layer

1. Update/create all Eloquent models (including `CareerSnapshot`)
2. Define relationships and scopes
3. Create service classes for business logic
4. Create `SnapshotService` for immutable snapshots
5. Create `LocalRunStorageService` (serialize/deserialize, versioning)
6. Create `ConvertLocalRunService` (local JSON → DB models)
7. Create `DuplicateDetectionService` for convert/import
8. Migrate export classes from uma-tracker
9. Add activity logging (user-scoped for DB, localStorage for local)

### Phase 3: UI Components

1. Migrate Blade components from uma-tracker
2. Create Livewire components for interactivity
3. Create `InlineEditor` and `FullscreenEditor` components
4. Create `Auth/ConvertRunModal` and `Auth/BulkConvertModal`
5. Create `LocalData/Manager` page with export/import/purge
6. Create `Snapshots/*` components
7. Create `Export/ExportPreview` and `Export/CopyToClipboard`
8. Implement RunList with mixed sources (local + account) and storage badges
9. Implement accessibility from uma-run-tracker (skip links, focus trap/restore, aria-live)
10. Add dark mode support with system preference detection
11. Ensure responsive design

### Phase 4: Feature Parity

1. Implement all P0 features
2. Add P1 enhanced features (including Import MVP with target selection)
3. Implement dual editing modes (inline + fullscreen)
4. Implement race-day snapshots
5. Implement local + account storage modes with unified UI
6. Implement Convert Local → Account flow (single + bulk)
7. Implement Local Data management page
8. Test thoroughly
9. Document all features

### Phase 5: Polish & Deploy

1. Performance optimization
2. Accessibility audit (axe-core on key pages)
3. Security review
4. Documentation completion
5. Production deployment

## Data Migration

### Legacy Data Import

Each legacy application stores data differently. Import scripts will:

1. **Detect source format** - JSON, SQL dump, or direct DB connection
2. **Validate data** - Check required fields, data types
3. **Transform data** - Map to new schema
4. **Import with relationships** - Maintain referential integrity
5. **Log results** - Track success/failure

### Supported Import Formats

| Source | Format | Support |
|--------|--------|---------|
| uma_musume_race_planner | MySQL dump | Full |
| umamusume-tracker | SQLite/MySQL | Full |
| uma-tracker | MySQL | Full |
| uma-run-tracker | JSON files | Full |
| uma-tracker-form | MySQL | Full |

## Testing Strategy

### Unit Tests (90%+ coverage)

- Model relationships
- Service methods
- Helper functions
- Enums and casts

### Feature Tests (80%+ coverage)

- Livewire component behavior
- Form submissions
- Export generation
- Import processing

### Browser Tests (Critical paths - Playwright)

1. Create character → upload image → save
2. Create run via quick create → add stat turns → chart renders
3. Add skill via autocomplete → keyboard navigate → set status + turn acquired
4. Export Excel/CSV/Markdown with preview
5. Import JSON legacy → select target (Local/Account) → preview → confirm → verify
6. Dark mode toggle → refresh → persists
7. Keyboard nav through tabs/modals → focus trap works
8. Create local run → refresh → still present (localStorage persistence)
9. Convert local run after login → appears as account run → local copy removed
10. Export local JSON → re-import → data preserved
11. Create race-day snapshot → verify immutable → export
12. Inline editor: open → edit → dirty warning on close → save → toast
13. Fullscreen editor: navigate tabs → edit multiple sections → save all
14. Autocomplete: type → keyboard navigate → select → verify added
15. Local Data page: view all local runs → bulk convert → verify in account

### Accessibility Tests

- axe-core automated tests on Dashboard, Run Detail, Export/Import modals, Local Data page
- Lighthouse CI integration
- Manual keyboard testing (tab order, focus visible)
- Screen reader verification (NVDA/VoiceOver)
- Reduced motion preference testing

## Timeline

| Phase | Duration | Deliverables |
|-------|----------|--------------|
| Phase 1 | 2 weeks | Database schema, models, seeders |
| Phase 2 | 2 weeks | Services, exports, imports |
| Phase 3 | 3 weeks | Livewire components, Blade components |
| Phase 4 | 2 weeks | Feature completion, integration |
| Phase 5 | 1 week | Polish, testing, deployment |
| **Total** | **10 weeks** | Production-ready application |

## Success Criteria

### Functional

- [ ] All P0 features implemented and working
- [ ] All P1 features implemented (including Import MVP with target selection)
- [ ] Data can be imported from JSON and CSV legacy sources
- [ ] Import supports target selection (Local storage vs Account)
- [ ] Export produces valid Excel/CSV/Markdown files with schema versioning
- [ ] Dark mode works correctly with system preference detection
- [ ] 3-state skill status (Acquired/Skipped/Suggested) with turn tracking
- [ ] Race predictions and goals tracking functional
- [ ] Race-day snapshots are immutable (create/delete only)
- [ ] Dual editing modes work (inline + fullscreen)
- [ ] Local + account storage modes coexist with unified UI
- [ ] Storage mode badge visible on all runs
- [ ] Convert Local → Account works (single + bulk)
- [ ] Local Data management page functional (export/import/purge/convert)
- [ ] Local storage schema is versioned with forward migration

### Performance

- [ ] Page load < 2 seconds
- [ ] Export 50k rows < 60 seconds
- [ ] Autocomplete response < 200ms (cached, rate-limited)
- [ ] Lazy loading for charts and large tables

### Quality

- [ ] Test coverage > 80%
- [ ] No critical accessibility issues
- [ ] WCAG 2.1 AA compliant
- [ ] Mobile responsive
- [ ] Focus trap/restore works in modals
- [ ] Reduced motion preference respected
- [ ] Autocomplete is accessible (listbox pattern, keyboard nav)

### Documentation

- [ ] README updated
- [ ] API documented (if implemented)
- [ ] User guide created
- [ ] Migration guide for legacy users
- [ ] Export schema documented with versioning

## Risks and Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Data loss during migration | High | Backup all legacy data, test imports thoroughly |
| Feature regression | Medium | Comprehensive test suite, feature parity checklist |
| Performance degradation | Medium | Performance benchmarks, optimization phase |
| Scope creep | Medium | Strict prioritization, phase gates |
| Accessibility issues | Medium | Early accessibility testing, WCAG checklist |

## Appendix

### Related Documents

- [Legacy Version Documentation](./README.md)
- [uma_musume_race_planner](./uma_musume_race_planner.md)
- [umamusume-tracker](./umamusume-tracker.md)
- [uma-tracker](./uma-tracker.md)
- [uma-run-tracker](./uma-run-tracker.md)
- [uma-tracker-form](./uma-tracker-form.md)

### Spec Files

- Requirements: `.kiro/specs/consolidation/requirements.md`
- Design: `.kiro/specs/consolidation/design.md`
- Tasks: `.kiro/specs/consolidation/tasks.md`
