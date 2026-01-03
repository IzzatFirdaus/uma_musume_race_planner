# Uma Musume Planner Consolidation - Requirements

## Overview

Consolidate five legacy Uma Musume tracking applications into the main `uma-musume-planner-laravel` repository, leveraging Laravel 12+ to create a unified, feature-rich application.

## Source Applications

1. **uma_musume_race_planner** - PHP + MySQL + Bootstrap (most feature-complete)
2. **umamusume-tracker** - Laravel 12 + React (best API design)
3. **uma-tracker** - Laravel 11 + Blade (closest to target stack)
4. **uma-run-tracker** - Static HTML + JS (best accessibility)
5. **uma-tracker-form** - Native PHP + MVC (simplest implementation)

## Spec Reconciliation Notes

This consolidation spec defines the **target architecture** and **canonical schema**. The frontend spec (`.kiro/specs/umamusume-planner-frontend/`) defines **MVP implementation details**.

### Storage Technology

- **MVP (Frontend Spec):** localStorage for Local_Runs
- **Target (This Spec):** IndexedDB via localforage
- **Abstraction:** `LocalRunStorageService` interface allows backend swap without UI changes

### Field Name Mapping (UI Label → Canonical DB Field)

| UI Label | Canonical Field | Notes |
|----------|-----------------|-------|
| SP Balance | `total_sp_available` | Use canonical in code, UI label for display |
| Stamina % | `stamina_percentage` | |
| Turn | `turn_number` | In StatProgress table |
| Current Turn | `current_turn` | In CareerRun table |

### Route Strategy (Adopted from Frontend Spec)

- Account runs: `/plans/{id}` and `/plans/{id}/edit`
- Local runs: `/plans/local/{uuid}` and `/plans/local/{uuid}/edit`

### Authentication

- **Web UI:** Laravel built-in auth (Breeze/Fortify)
- **API (if needed):** Sanctum tokens

## Canonical Naming & Terminology

### Domain Glossary

| UI Term | Domain Entity | Database Table | Notes |
|---------|---------------|----------------|-------|
| Plan | CareerRun | `career_runs` | UI uses "Plan" for user-friendliness |
| Character | UmaMusume | `uma_musumes` | Horse girl character |
| Turn | StatProgress | `stat_progress` | Single turn's stat snapshot |
| Skill Entry | SkillCareerRun | `skill_career_runs` | Pivot with status + turn |

### Schema Field Naming Standards

- Foreign keys: `{table_singular}_id` (e.g., `career_run_id`, `skill_id`)
- Turn number field: `turn_number` (not `turn`)
- Timestamps: `created_at`, `updated_at`, `deleted_at`
- Status enums: lowercase snake_case values

### Export/Import Schema Versioning

- All exports include `schema_version` field (e.g., `"1.0.0"`)
- Import detects version and applies appropriate transformations
- Breaking changes increment major version

### Schema Canonicalization (Consistency Rules)

All code, migrations, ERD, and Livewire bindings MUST use these exact names:

| Entity | Table | Key Columns |
| ------ | ----- | ----------- |
| StatProgress | `stat_progress` | `career_run_id`, `turn_number`, `speed`, `stamina`, `power`, `guts`, `wit` |
| CareerRun | `career_runs` | `total_sp_available` (not `total_sp`), `stamina_percentage` |
| SkillCareerRun | `skill_career_runs` | `career_run_id`, `skill_id`, `status`, `turn_acquired` |
| ActivityLog | `activity_logs` | `user_id` (nullable), `model_type`, `model_id` |

Foreign key pattern: `{singular_table}_id` (e.g., `career_run_id`, not `run_id`)

## Functional Requirements

### FR-1: Character Management

- FR-1.1: Create, read, update, delete Uma Musume characters
- FR-1.2: Store character images with upload functionality
- FR-1.3: Track aptitude grades (Track: Turf/Dirt, Distance: Sprint/Mile/Medium/Long, Style: Front/Pace/Late/End)
- FR-1.4: Track growth rate bonuses for all five stats

### FR-2: Career Run Management

- FR-2.1: Create and manage career runs linked to characters
- FR-2.2: Track career year (Junior, Classic, Senior)
- FR-2.3: Track run status (Ongoing, Finished, Failed)
- FR-2.4: Track Uma class progression (Debut to Legend)
- FR-2.5: Track current turn, race, SP available, stamina percentage

### FR-3: Stat Progression Tracking

- FR-3.1: Log turn-by-turn stats (Speed, Stamina, Power, Guts, Wit)
- FR-3.2: Visualize stat progression with charts
- FR-3.3: Display stat bars with grade indicators
- FR-3.4: Calculate and display stat totals and averages

### FR-4: Skill Management

- FR-4.1: Maintain skill database with name, description, type, SP cost
- FR-4.2: Track skill acquisition per career run with 3-state status
- FR-4.3: Support skill status: **Acquired** (with required `turn_acquired`), **Skipped**, **Suggested**
- FR-4.4: Provide skill autocomplete/search functionality (EN + JP matching)
- FR-4.5: Track turn when skill was acquired (required for Acquired status)
- FR-4.6: Calculate SP totals: acquired-only total, suggested SP budget
- FR-4.7: Filter skills by status (show Acquired/Skipped/Suggested only)
- FR-4.8: Support skill notes per career run entry

### FR-4B: Autocomplete UX & Performance

- FR-4B.1: Skill search returns results in < 200ms (NFR-1.3)
- FR-4B.2: Search supports partial, case-insensitive matching
- FR-4B.3: Search matches both EN name and JP name fields
- FR-4B.4: Keyboard navigation in dropdown: ↑/↓ to navigate, Enter to select, Esc to close
- FR-4B.5: Results list is accessible: `role="listbox"`, options have `role="option"`, `aria-activedescendant`
- FR-4B.6: Debounce input: 300ms delay before search fires
- FR-4B.7: Server endpoint rate-limited (60 req/min) and cached (5 min TTL)
- FR-4B.8: Show "No results" state, not empty dropdown
- FR-4B.9: Character search uses same autocomplete patterns

### FR-5: Race Planning

- FR-5.1: Track race predictions with distance category, track type, venue
- FR-5.2: Store race-day snapshots (stats at race time)
- FR-5.3: Log race results and outcomes (predicted vs actual placement)
- FR-5.4: Support manual reordering of race predictions
- FR-5.5: Display recommended stamina thresholds by distance
- FR-5.6: Convert predictions to results after race day

### FR-5B: Goal Tracking

- FR-5B.1: Create goals with description and optional target value (e.g., "Speed >= 1000")
- FR-5B.2: Track goal achievement status with turn achieved
- FR-5B.3: Display goals as checklist with visual distinction for achieved
- FR-5B.4: Default turn achieved to current turn when marking complete

### FR-6: Data Export/Import

- FR-6.1: Export career runs to Excel (.xlsx) with defined column schema
- FR-6.2: Export career runs to CSV (UTF-8 encoding, proper quoting)
- FR-6.3: Export career runs to plain text/markdown
- FR-6.4: Copy-to-clipboard functionality for quick sharing
- FR-6.5: Golden-file tests for export format consistency

### FR-6B: Import System (MVP - P1)

- FR-6B.1: Import page at `/import` route
- FR-6B.2: Upload file → detect legacy format automatically
- FR-6B.3: Preview mapping before import (show field mappings)
- FR-6B.4: Dry-run validation with row-level error reporting
- FR-6B.5: Confirm import → execute → show results report
- FR-6B.6: Downloadable error report as CSV
- FR-6B.7: Duplicate detection with warning (same character/run title/date)
- FR-6B.8: Support JSON import (from uma-run-tracker) as primary format
- FR-6B.9: Support CSV import as secondary format
- FR-6B.10: Architecture for additional import adapters (MySQL dump, SQLite)
- FR-6B.11: **Import Target selection**: "Import into Local storage" (default for anonymous) or "Import into Account" (requires auth)
- FR-6B.12: Importing into Local shall not require network calls
- FR-6B.13: Importing into Account uses DB transactions + validation

### FR-7: User Interface

- FR-7.1: Dark/light mode toggle with persistence
- FR-7.2: Responsive mobile-first design
- FR-7.3: Game-inspired Uma Musume visual style
- FR-7.4: Quick create modal for rapid entry
- FR-7.5: Full-screen detail editor modal (tabs/sections for all run data)
- FR-7.6: Inline detail panel for quick edits (status, turn, SP, stamina, notes)
- FR-7.7: Dashboard with activity log and quick stats
- FR-7.8: **Storage Mode badge** on each run in RunList and RunDetail
- FR-7.9: **"Convert to Account" action** on Local runs (visible when authenticated)
- FR-7.10: **Local Data management entry point** (nav link or Dashboard section)
- FR-7.11: Dashboard stats panel shows counts by storage mode (e.g., `Local: 3`, `Account: 7`)

### FR-7B: Dual Editing Modes

- FR-7B.1: Inline editor opens from RunList without page navigation
- FR-7B.2: Fullscreen editor opens from RunList and from RunDetail
- FR-7B.3: Both modes support dirty-state warning before close
- FR-7B.4: Both modes show save success toast notifications
- FR-7B.5: Keyboard support: Esc closes inline/modal, Ctrl+S saves
- FR-7B.6: Inline editor fields: status, current turn, SP, stamina %, notes, quick skill toggle
- FR-7B.7: Fullscreen editor: full tabbed interface (General, Stats, Skills, Racing, Goals)

### FR-8: Activity Tracking

- FR-8.1: Log user actions (create, update, delete) with user scoping
- FR-8.2: Display recent activity on dashboard (current user only when logged in)
- FR-8.3: Track timestamps for all operations
- FR-8.4: Support activity log per career run view
- FR-8.5: **Local runs**: activity log stored separately in localStorage (not DB)
- FR-8.6: **Account runs**: activity log stored in DB, user-scoped
- FR-8.7: Dashboard activity feed indicates source (Local vs Account)

### FR-9: Authentication & Storage Modes

- FR-9.1: Optional user authentication via Laravel Sanctum
- FR-9.2: **Simultaneous local + DB storage modes supported**
- FR-9.3: Anonymous mode stores plans locally (JSON in IndexedDB/localStorage)
- FR-9.4: Each run has a "Storage Mode" indicator: `local` or `account`
- FR-9.5: Local runs persist across browser refresh (not across devices)
- FR-9.6: Account runs are available across devices after login
- FR-9.7: Local runs use UUIDs; Account runs use DB IDs (never mixed)
- FR-9.8: Signed-in users can still create local-only runs if desired
- FR-9.9: API key authentication option for programmatic access

### FR-9B: Local Storage Implementation

**MVP Phase (P0):** Use localStorage for simplicity and broad browser support.
**Target Phase (P2):** Migrate to IndexedDB (via localforage) for larger datasets and structured queries.

The `LocalRunStorageService` abstraction allows swapping storage backends without UI changes.

- FR-9B.1: **MVP:** Use localStorage; **Target:** Use IndexedDB (via localforage) for structured local storage
- FR-9B.2: Local runs have permanent UUIDs (not temporary)
- FR-9B.3: Local data includes: characters, runs, stats, skills, goals, predictions, snapshots
- FR-9B.4: Export local data as JSON backup file (single run or all)
- FR-9B.5: Clear local data option (with confirmation)
- FR-9B.6: Storage quota warning when approaching browser limits
- FR-9B.7: Local storage schema is versioned (`schema_version` field)
- FR-9B.8: System migrates older local schema versions forward on load
- FR-9B.9: `LocalRunStorageService` interface abstracts storage backend (localStorage MVP, IndexedDB target)

### FR-9C: Convert Local Runs to Account (Claim Flow)

- FR-9C.1: "Convert to Account" action available on Local runs when authenticated
- FR-9C.2: Conversion creates DB CareerRun + all related data (skills, turns, goals, predictions, snapshots)
- FR-9C.3: Conversion preserves: created timestamps, turn ordering, skill statuses + `turn_acquired`
- FR-9C.4: Duplicate detection: if similar run exists (same title + character + created date), warn user
- FR-9C.5: Duplicate resolution options: "Create duplicate" / "Cancel" (merge is P2)
- FR-9C.6: Bulk convert supported from Local Data page/modal
- FR-9C.7: **Move policy (default)**: delete local copy after successful conversion
- FR-9C.8: Optional "Also keep local copy" checkbox for users who want offline fallback
- FR-9C.9: "Download Backup" action available for any Local run before conversion

### FR-9D: Local Data Management Page

- FR-9D.1: `/local-data` route (or Dashboard modal) for managing local runs
- FR-9D.2: Lists all local runs with storage used (approximate)
- FR-9D.3: Actions: Export all local runs as JSON
- FR-9D.4: Actions: Import local runs from JSON
- FR-9D.5: Actions: Delete all local runs (requires confirmation)
- FR-9D.6: Actions: Convert all to Account (bulk, requires auth)

### FR-10: Scenario Extensibility

- FR-10.1: `scenario` field on career runs with default value `URA`
- FR-10.2: UI hides scenario-inapplicable fields without deleting data
- FR-10.3: Future scenarios can be added without schema changes
- FR-10.4: Scenario-specific validation rules (soft behavior)

### FR-11: Image Management

- FR-11.1: Character image upload with preview
- FR-11.2: Validate file type and size (max 2MB, jpg/png/webp)
- FR-11.3: Verify MIME by content sniffing (security)
- FR-11.4: Strip EXIF metadata (privacy + security)
- FR-11.5: Generate thumbnail variant for list views (performance)
- FR-11.6: Storage strategy: local disk (public) with S3 option
- FR-11.7: Cleanup images on character soft delete (policy decision)

### FR-12: Race-day Snapshots

- FR-12.1: Store immutable snapshot of run state at a given turn/race
- FR-12.2: Snapshot captures: all stats, mood, conditions, skills acquired, SP remaining, stamina %
- FR-12.3: User can click "Create Snapshot" from RunDetail at any time
- FR-12.4: Snapshots are immutable once created (no edits, only delete)
- FR-12.5: Snapshots can be exported individually or with run
- FR-12.6: Snapshot list shows turn number, race name, timestamp
- FR-12.7: Compare snapshots side-by-side (optional P2 feature)

### FR-13: Export Preview & Copy Panel

- FR-13.1: Before downloading, user can preview export in modal
- FR-13.2: Preview supports format switching (Excel preview as table, Markdown as text)
- FR-13.3: Copy-to-clipboard panel for quick sharing (like uma-tracker-form)
- FR-13.4: Copy action shows toast + announces via aria-live
- FR-13.5: Preview modal shows estimated file size

### FR-14: Accessibility Interaction Patterns

- FR-14.1: Skip link on every page (visible on focus)
- FR-14.2: Live region announcements for saves, errors, and state changes
- FR-14.3: Focus trap for all modals (focus cannot leave modal while open)
- FR-14.4: Focus restore after modal close (return to trigger element)
- FR-14.5: `prefers-reduced-motion` disables: stat bar animations, chart transitions, page transitions
- FR-14.6: All interactive elements have visible focus indicators
- FR-14.7: Form errors announced to screen readers immediately

## Non-Functional Requirements

### NFR-1: Performance

- NFR-1.1: Page load time < 2 seconds
- NFR-1.2: Export 50k rows in < 60 seconds
- NFR-1.3: Autocomplete/search response < 200ms
- NFR-1.4: Skill search rate limited and cached
- NFR-1.5: Lazy loading for charts and large tables (`wire:init`)
- NFR-1.6: Skeleton loaders during async operations
- NFR-1.7: Virtualization consideration for 70-78 turn tables

### NFR-2: Accessibility

- NFR-2.1: WCAG 2.1 AA compliance (stronger than A-level)
- NFR-2.2: Keyboard navigation support for all interactive elements
- NFR-2.3: Screen reader compatibility with proper announcements
- NFR-2.4: Skip links for main content on all pages
- NFR-2.5: ARIA labels and roles for custom components
- NFR-2.6: Focus trap and restore for modals
- NFR-2.7: `prefers-reduced-motion` support for animations
- NFR-2.8: Chart accessibility: data table view alternative + ARIA descriptions
- NFR-2.9: Touch targets minimum 44x44px on mobile
- NFR-2.10: Color contrast ratio minimum 4.5:1 for text

### NFR-3: Security

- NFR-3.1: CSRF protection on all forms
- NFR-3.2: Input validation and sanitization
- NFR-3.3: SQL injection prevention (Eloquent ORM)
- NFR-3.4: XSS prevention (Blade escaping)
- NFR-3.5: Optional API authentication (Sanctum)

### NFR-4: Maintainability

- NFR-4.1: PSR-12 coding standards
- NFR-4.2: Comprehensive test coverage (>80%)
- NFR-4.3: Documentation for all public APIs
- NFR-4.4: Consistent component architecture
- NFR-4.5: `data-testid` attributes on all interactive elements for stable E2E test selectors
- NFR-4.6: Naming convention: `data-testid="[component]-[action]-[context]"` (e.g., `plan-create-button`)
- NFR-4.7: Document all `data-testid` values in test selector reference file

### NFR-5: Compatibility

- NFR-5.1: PHP 8.2+ support
- NFR-5.2: MySQL/MariaDB/SQLite support
- NFR-5.3: Modern browser support (Chrome, Firefox, Safari, Edge)
- NFR-5.4: Mobile browser support (iOS Safari, Chrome Android)

## User Stories

### US-1: Character Registration

As a user, I want to register a new Uma Musume character with their base stats and aptitudes so I can track their career progression.

### US-2: Career Initiation

As a user, I want to start a new career run for a character so I can track their training journey.

### US-3: Turn Logging

As a user, I want to log my current stats each turn so I can track progression over time.

### US-4: Skill Management

As a user, I want to manage skills with 3-state status (Acquired/Skipped/Suggested) and track when I acquired them so I can plan my skill build effectively.

### US-5: Data Export

As a user, I want to export my career run data to Excel/CSV/Markdown so I can analyze it externally or share it.

### US-6: Dark Mode

As a user, I want to toggle dark mode so I can use the app comfortably in low-light conditions.

### US-7: Quick Entry

As a user, I want to quickly create a new plan with minimal input so I can start tracking immediately.

### US-8: Stat Visualization

As a user, I want to see my stat progression as a chart so I can visualize my training progress.

### US-9: Data Import

As a user, I want to import my data from legacy tracker formats so I can migrate without losing my history.

### US-10: Anonymous Usage

As a user, I want to use the app without logging in, with my data stored locally, so I can try it out easily.

### US-11: Claim Local Data

As a user who used the app anonymously, I want to import my local plans into my account when I sign up so I don't lose my work.

### US-12: Race Planning

As a user, I want to track race predictions and results so I can plan my race strategy and review outcomes.

### US-13: Goal Tracking

As a user, I want to set and track goals for my career run so I can measure my progress against targets.

## Acceptance Criteria

### AC-1: Character CRUD

- Can create character with all required fields
- Can view character details
- Can update character information
- Can delete character (soft delete)
- Character image upload works with preview
- Image validation rejects invalid types/sizes
- Thumbnail generated for list views

### AC-2: Career Run CRUD

- Can create career run linked to character
- Can view career run details with all related data
- Can update career run status and details
- Can delete career run (soft delete)
- Class progression selector works
- Run status selector works (Ongoing/Finished/Failed)
- Current turn, race, SP, stamina fields editable

### AC-3: Stat Logging

- Can add stat entry for a turn
- Stats display in table format with lazy loading
- Stats display in chart format with toggle per stat line
- Can edit/delete stat entries
- Skeleton loaders shown during load
- "No data" state is accessible

### AC-4: Skill Management

- Can search skills with autocomplete (EN + JP)
- Autocomplete responds in < 200ms
- Can add skill to career run with 3-state status
- If status = Acquired, turn_acquired is required
- Can update skill status and turn
- Can remove skill from career run
- SP totals calculated correctly (acquired vs suggested)
- Can filter by status

### AC-5: Export

- Excel export contains all career run data with defined columns
- CSV export is UTF-8 encoded with proper quoting
- Markdown export is human-readable
- Export completes within performance requirements
- Copy-to-clipboard works
- Export includes schema_version field

### AC-6: Import

- `/import` route exists and is accessible
- Can upload JSON file and detect format
- **Import Target selection**: Local storage or Account (Account requires auth)
- Preview shows field mappings before import
- Dry-run shows row-level errors
- Can download error report as CSV
- Duplicate detection warns user
- Import report shows created/updated/skipped counts
- Importing into Local works offline (no network calls)
- Importing into Account uses DB transactions

### AC-7: UI/UX

- Dark mode persists across sessions (localStorage)
- System preference detected on first visit
- All pages are responsive
- Forms validate input before submission
- Loading states displayed during async operations
- Focus trap works in modals
- Browser back closes modal (if modal route)

### AC-7B: Dual Editing Modes

- Inline editor opens from RunList row action (no navigation)
- Inline editor shows: status, turn, SP, stamina %, notes, quick skill toggles
- Fullscreen editor opens from RunList and RunDetail
- Fullscreen editor has tabs: General, Stats, Skills, Racing, Goals
- Dirty-state warning shown if closing with unsaved changes
- Esc key closes inline panel / modal
- Ctrl+S saves in both modes
- Save success shows toast notification

### AC-8: Accessibility

- Skip links present on all pages (visible on focus)
- Keyboard navigation works for all interactive elements
- Screen reader announces state changes via live regions
- Focus trapped in modals, restored on close
- Reduced motion respected (animations disabled)
- Charts have data table alternative
- Autocomplete dropdowns are accessible (listbox pattern)
- Form errors announced immediately

### AC-9: Authentication & Local Storage

- Can use app without logging in
- **MVP:** Local data stored in localStorage; **Target:** IndexedDB (persists across refresh)
- Storage mode indicator shows "Local" or "Account" per run
- Local runs use UUIDs; Account runs use DB IDs
- Dashboard stats show breakdown by storage mode
- Can export local data as JSON backup (single run or all)
- Activity log scoped appropriately (localStorage for local, DB for account)

### AC-9B: Convert Local Runs to Account

- "Convert to Account" action visible on Local runs when authenticated
- Conversion creates DB records for run + all related data
- Conversion preserves timestamps, ordering, skill statuses
- Duplicate detection warns if similar run exists
- User can choose "Create duplicate" or "Cancel"
- Bulk convert available from Local Data page
- Default behavior: delete local copy after successful conversion
- Optional "Also keep local copy" checkbox available
- "Download Backup" action available before conversion

### AC-9C: Local Data Management

- `/local-data` route accessible (or Dashboard modal)
- Lists all local runs with approximate storage used
- Can export all local runs as JSON
- Can import local runs from JSON
- Can delete all local runs (with confirmation)
- Can bulk convert all to Account (requires auth)

### AC-10: Race-day Snapshots

- Can create snapshot from RunDetail
- Snapshot captures all current run state
- Snapshots are immutable (no edit, only delete)
- Snapshot list shows turn, race, timestamp
- Can export snapshots with run

### AC-11: Export Preview

- Preview modal shows before download
- Can switch format in preview (Excel table / Markdown text)
- Copy-to-clipboard works with toast confirmation
- Estimated file size shown

## Critical User Flows (for Playwright testing)

1. Create character → upload image → save
2. Create run via quick create → add stat turns → chart renders
3. Add skill via autocomplete → keyboard navigate → set status + turn acquired
4. Export Excel/CSV/Markdown with preview
5. Import JSON legacy → select target (Local/Account) → preview → confirm → verify created data
6. Dark mode toggle → refresh → persists
7. Keyboard nav through tabs/modals → focus trap works
8. Create local run → refresh → still present (localStorage persistence)
9. Convert local run after login → appears as account run → local copy removed (or kept if checkbox)
10. Export local JSON → re-import → data preserved
11. Create race-day snapshot → verify immutable → export
12. Inline editor: open → edit → dirty warning on close → save → toast
13. Fullscreen editor: navigate tabs → edit multiple sections → save all
14. Autocomplete: type → keyboard navigate → select → verify added
15. Local Data page: view all local runs → bulk convert → verify in account
