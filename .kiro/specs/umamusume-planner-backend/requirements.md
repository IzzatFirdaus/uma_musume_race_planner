# Uma Musume Planner Backend - Requirements

## Overview

This document specifies the backend requirements for the Uma Musume Planner application. The backend provides RESTful APIs, service layers, database management, and server-side business logic that support the frontend Livewire application. This spec implements the consolidation schema and supports frontend user flows.

## Spec Precedence & Reconciliation

This backend spec follows a clear precedence model to ensure consistency across the project:

### Precedence Hierarchy

1. **Consolidation Spec** (`.kiro/specs/consolidation/`) - Source of truth for:
   - Canonical schema naming (`career_runs`, `career_run_id`, `turn_number`, etc.)
   - Storage mode model (`local` vs `account`)
   - Import/export versioning (`schema_version`)
   - Domain entity definitions

2. **Frontend Spec** (`.kiro/specs/umamusume-planner-frontend/`) - Source of truth for:
   - User-facing routes (`/plans/{id}`, `/plans/local/{uuid}`)
   - Livewire interaction patterns
   - Client-side UX flows

3. **Backend Spec** (this document) - Derived from (1) + (2):
   - Implements consolidation schema
   - Supports frontend flows
   - Adds server-side validation and business logic

### Reconciled Decisions

| Topic | Decision | Source |
|-------|----------|--------|
| Stat validation | 0-1200 hard max (no soft cap) | Consolidation FR-3.1 |
| Image size | Max 2MB, jpg/png/webp only | Consolidation FR-11.2 |
| FK naming | `{table_singular}_id` (e.g., `career_run_id`) | Consolidation Schema |
| Table naming | `career_runs`, `stat_progress`, `skill_career_runs` | Consolidation Schema |
| REST API | Optional but implemented for programmatic access | Backend decision |
| Local storage | Frontend-only (localStorage/IndexedDB), no backend calls | Consolidation FR-9B |
| Convert to Account | Backend service supports conversion flow | Consolidation FR-9C |

### Conflict Resolution Rule

> If consolidation and frontend specs conflict, consolidation schema naming wins. User-facing behavior (validation limits, routes, UX) must be reconciled explicitly and updated in all specs.

## Canonical Naming & Terminology

### API vs Domain Naming

| Context | Term Used | Notes |
|---------|-----------|-------|
| API Routes | `/api/v1/plans` | User-friendly UI term |
| Database Table | `career_runs` | Canonical domain name |
| Model Class | `Plan` | Maps to `career_runs` table via `$table` |
| Service Layer | `PlanService` | Uses UI terminology |

The API uses `/plans` for user-friendliness while the database uses the canonical `career_runs` table name. The Laravel model `Plan` bridges this mapping with explicit `$table = 'career_runs'`.

### Internal Search Routes

For Livewire/internal use, the following routes are also available:

- `GET /internal/skills/search?q=...` - Internal skill search (Livewire)
- `GET /internal/uma-musume/search?q=...` - Internal character search (Livewire)

The `/api/v1/autosuggest/*` routes are the public API equivalents.

## Glossary

- **Plan**: A career run tracking a character's training journey (UI term for CareerRun)
- **CareerRun**: Database entity representing a training run (table: `career_runs`)
- **UmaMusume**: A horse girl character from the game (table: `uma_musumes`)
- **StatProgress**: Turn-by-turn stat snapshot (table: `stat_progress`)
- **SkillCareerRun**: Pivot table linking skills to career runs with status and turn acquired (table: `skill_career_runs`)
- **Snapshot**: Immutable capture of run state at a specific turn/race (table: `career_snapshots`)
- **Storage_Mode**: Indicator of where data is stored (`local` or `account`)
- **Plan_Service**: Backend service handling plan CRUD operations
- **Cache_Service**: Backend service managing cache invalidation
- **API_Controller**: REST API endpoint handler
- **Soft_Deletable_Entities**: Plan, Goal, RacePrediction, Snapshot, ActivityLog
- **Reference_Data_Entities**: UmaMusume, Skill (NOT soft deletable, admin hard-delete only)
- **Hard_Deletable_Entities**: StatProgress (turns), SkillCareerRun pivot records

## Requirement Priorities

Requirements are prioritized as follows:

- **P0 (Critical)**: Core functionality required for MVP launch
- **P1 (High)**: Important features for complete user experience
- **P2 (Medium)**: Enhanced features for power users
- **P3 (Low)**: Future enhancements and nice-to-haves

## Functional Requirements

### FR-BE-1: Database Schema Management [P0]

Implements: Consolidation Schema Canonicalization

- FR-BE-1.1: Use canonical naming conventions from consolidation spec
- FR-BE-1.2: Use foreign keys with `{table_singular}_id` pattern (e.g., `career_run_id`)
- FR-BE-1.3: Support soft deletes via `deleted_at` on Soft_Deletable_Entities (Plan, Goal, RacePrediction, Snapshot, ActivityLog)
- FR-BE-1.4: Use hard deletes for Hard_Deletable_Entities (turns, skill pivots)
- FR-BE-1.4B: Reference data (UmaMusume, Skill) is NOT soft deletable - admin hard-delete only
- FR-BE-1.5: Include `created_at` and `updated_at` timestamps on all tables
- FR-BE-1.6: Ensure migrations are reversible with proper `down()` methods
- FR-BE-1.7: Support MySQL, MariaDB, and SQLite databases

### FR-BE-2: Plan (CareerRun) API [P0]

Implements: Consolidation FR-2, Frontend Requirement 3-5

- FR-BE-2.1: `GET /api/v1/plans` returns paginated list for authenticated user
- FR-BE-2.2: `GET /api/v1/plans/{id}` returns plan with relations based on `include` parameter
- FR-BE-2.3: Default includes (no `include` param): `attributes`, `umamusume`
- FR-BE-2.4: Optional includes: `skills`, `goals`, `turns`, `race_predictions`
- FR-BE-2.5: `POST /api/v1/plans` creates plan, returns 201 with resource
- FR-BE-2.6: `PUT /api/v1/plans/{id}` updates plan, returns updated resource
- FR-BE-2.7: `DELETE /api/v1/plans/{id}` soft-deletes plan, returns 204
- FR-BE-2.8: Unauthenticated requests return 401 Unauthorized
- FR-BE-2.9: Accessing another user's plan returns 403 Forbidden
- FR-BE-2.10: Validation failures return 422 with field-level errors

### FR-BE-3: Plan Service Layer [P0]

Implements: Business logic separation

- FR-BE-3.1: Handle plan creation with related entities (attributes, skills, goals)
- FR-BE-3.2: Handle plan updates with proper validation
- FR-BE-3.3: Handle plan deletion with cascade (soft-delete soft-deletables, hard-delete turns/pivots)
- FR-BE-3.4: Dispatch `PlanCreated` event on creation
- FR-BE-3.5: Dispatch `PlanUpdated` event on update
- FR-BE-3.6: Support filtering by status, character, date range
- FR-BE-3.7: Support sorting by created_at, updated_at, title

### FR-BE-4: Skill Autocomplete API [P0]

Implements: Consolidation FR-4B

- FR-BE-4.1: `GET /api/v1/autosuggest/skills` returns matches within 200ms
- FR-BE-4.2: Support partial, case-insensitive matching
- FR-BE-4.3: Match both English name and Japanese name fields
- FR-BE-4.4: Limit results to 20 items by default
- FR-BE-4.5: Rate-limit to 60 requests per minute per user
- FR-BE-4.6: Cache results with 5-minute TTL
- FR-BE-4.7: Return empty array (not 404) when no results found

### FR-BE-5: Character Autocomplete API [P0]

Implements: Consolidation FR-4B.9

- FR-BE-5.1: `GET /api/v1/autosuggest/characters` returns matches within 200ms
- FR-BE-5.2: Support partial, case-insensitive matching on name fields
- FR-BE-5.3: Limit results to 20 items by default
- FR-BE-5.4: Return empty array when no results found

### FR-BE-6: Export Service [P1]

Implements: Consolidation FR-6

- FR-BE-6.1: Generate Excel (.xlsx) format with defined column schema
- FR-BE-6.2: Generate CSV format with UTF-8 encoding and proper quoting
- FR-BE-6.3: Generate Markdown format for human readability
- FR-BE-6.4: Include `schema_version` field in all exports
- FR-BE-6.5: Complete export of 50,000 rows within 60 seconds
- FR-BE-6.6: Use queued jobs for large dataset exports
- FR-BE-6.7: Support single plan and bulk export

### FR-BE-7: Import Service [P1]

Implements: Consolidation FR-6B

- FR-BE-7.1: Detect legacy format automatically from uploaded file
- FR-BE-7.2: Support JSON import as primary format
- FR-BE-7.3: Support CSV import as secondary format
- FR-BE-7.4: Perform dry-run validation with row-level error reporting
- FR-BE-7.5: Use database transactions for account imports
- FR-BE-7.6: Detect duplicates based on title, character, created date
- FR-BE-7.7: Generate downloadable error report as CSV
- FR-BE-7.8: Report counts of created, updated, and skipped records

### FR-BE-8: Cache Management [P1]

Implements: Performance optimization

- FR-BE-8.1: Invalidate plan cache on create, update, delete
- FR-BE-8.2: Use cache tags for granular invalidation
- FR-BE-8.3: Cache skill search results with 5-minute TTL
- FR-BE-8.4: Cache character search results with 5-minute TTL
- FR-BE-8.5: Clear relevant caches on plan events

### FR-BE-9: Authentication and Authorization [P0]

Implements: Consolidation Authentication section

- FR-BE-9.1: Support Laravel Sanctum for API token authentication
- FR-BE-9.2: Support session-based authentication for web UI
- FR-BE-9.3: Ensure users can only access their own plans
- FR-BE-9.4: Support admin role for system-wide access
- FR-BE-9.5: Reject unauthenticated requests to protected endpoints with 401
- FR-BE-9.6: No public plans - all account-based plans require authentication
- FR-BE-9.7: Support API key authentication option for programmatic access (per Consolidation FR-9.9)

### FR-BE-10: Activity Logging [P1]

Implements: Consolidation FR-8

- FR-BE-10.1: Log plan creation with user ID and timestamp
- FR-BE-10.2: Log plan updates with changed fields
- FR-BE-10.3: Log plan deletion
- FR-BE-10.4: Scope activity log to current user when queried
- FR-BE-10.5: Support filtering by action type and date range
- FR-BE-10.6: Retain entries for 90 days by default

### FR-BE-11: Stat Progress Management [P0]

Implements: Consolidation FR-3

- FR-BE-11.1: Store speed, stamina, power, guts, wit values per turn
- FR-BE-11.2: Use `turn_number` field (not `turn`)
- FR-BE-11.3: Validate stat values within 0-1200 range (hard max)
- FR-BE-11.4: Reject values above 1200 with validation error
- FR-BE-11.5: Support bulk stat entry for multiple turns
- FR-BE-11.6: Return stats ordered by turn_number ascending

### FR-BE-12: Skill Career Run Management [P0]

Implements: Consolidation FR-4

- FR-BE-12.1: Support three statuses: `acquired`, `skipped`, `suggested`
- FR-BE-12.2: Require `turn_acquired` when status is `acquired`
- FR-BE-12.3: Calculate SP totals for acquired skills only
- FR-BE-12.4: Calculate suggested SP budget separately
- FR-BE-12.5: Support filtering skills by status
- FR-BE-12.6: Support skill notes per career run entry

### FR-BE-13: Race Prediction Management [P1]

Implements: Consolidation FR-5

- FR-BE-13.1: Store race_name, distance_category, track_type, venue
- FR-BE-13.2: Support manual reordering via `sort_order` field
- FR-BE-13.3: Store predicted_pos and actual_pos for outcomes
- FR-BE-13.4: Support optional notes field
- FR-BE-13.5: Store race-day snapshots with stats at race time
- FR-BE-13.6: Support converting predictions to results
- FR-BE-13.7: Display recommended stamina thresholds by distance

### FR-BE-14: Goal Tracking [P1]

Implements: Consolidation FR-5B

- FR-BE-14.1: Store description and optional target value
- FR-BE-14.2: Track achievement status with `turn_achieved` field
- FR-BE-14.3: Default `turn_achieved` to current turn when marking complete
- FR-BE-14.4: Return goals ordered by creation date

### FR-BE-15: Image Upload and Management [P1]

Implements: Consolidation FR-11

- FR-BE-15.1: Validate file type (jpg, png, webp only)
- FR-BE-15.2: Validate file size (max 2MB)
- FR-BE-15.3: Verify MIME type by content sniffing
- FR-BE-15.4: Strip EXIF metadata for privacy
- FR-BE-15.5: Generate thumbnail variant for list views
- FR-BE-15.6: Store images on local disk with S3 option
- FR-BE-15.7: Retain images until hard delete on character soft-delete

### FR-BE-16: Snapshot Management [P1]

Implements: Consolidation FR-12

- FR-BE-16.1: Capture all stats, mood, conditions, skills, SP, stamina
- FR-BE-16.2: Snapshots are immutable once created (no edits)
- FR-BE-16.3: Support creating snapshots from current run state
- FR-BE-16.4: Support deleting snapshots
- FR-BE-16.5: Return snapshots ordered by turn_number and timestamp
- FR-BE-16.6: Include snapshots when exporting a plan

### FR-BE-17: API Response Standards [P0]

Implements: Consistent API contract

- FR-BE-17.1: Use Laravel Resource format for responses
- FR-BE-17.2: Include pagination metadata (total, per_page, current_page, last_page)
- FR-BE-17.3: Use consistent error format with `message` and `errors` fields
- FR-BE-17.4: Return appropriate HTTP status codes (200, 201, 204, 400, 401, 403, 404, 422, 500)
- FR-BE-17.5: Include `Content-Type: application/json` header on all responses
- FR-BE-17.6: Support `include` parameter for eager loading with defined defaults

### FR-BE-18: Convert Local to Account [P1]

Implements: Consolidation FR-9C

- FR-BE-18.1: Accept local run data and create DB CareerRun with all related data
- FR-BE-18.2: Preserve created timestamps, turn ordering, skill statuses
- FR-BE-18.3: Detect duplicates (same title + character + created date)
- FR-BE-18.4: Support bulk conversion
- FR-BE-18.5: Use database transactions for atomicity

## Non-Functional Requirements

### NFR-BE-1: Performance [P0]

- NFR-BE-1.1: API response time < 200ms for autocomplete endpoints
- NFR-BE-1.2: Export 50,000 rows in < 60 seconds
- NFR-BE-1.3: Use eager loading to prevent N+1 queries
- NFR-BE-1.4: Support pagination with configurable page size (default 15, max 100)
- NFR-BE-1.5: Use chunking for large dataset queries

### NFR-BE-2: Database Optimization [P0]

- NFR-BE-2.1: Include indexes on frequently queried columns
- NFR-BE-2.2: Include composite indexes for common query patterns
- NFR-BE-2.3: Use database-agnostic queries (MySQL, MariaDB, SQLite)

### NFR-BE-3: Security [P0]

- NFR-BE-3.1: CSRF protection on all forms
- NFR-BE-3.2: Input validation and sanitization
- NFR-BE-3.3: SQL injection prevention via Eloquent ORM
- NFR-BE-3.4: XSS prevention via Blade escaping
- NFR-BE-3.5: Sanctum token authentication for API

### NFR-BE-4: Maintainability [P1]

- NFR-BE-4.1: PSR-12 coding standards
- NFR-BE-4.2: Comprehensive test coverage (>80%)
- NFR-BE-4.3: Documentation for all public APIs
- NFR-BE-4.4: Consistent service/repository architecture

### NFR-BE-5: Reliability [P1]

- NFR-BE-5.1: Database transactions for multi-table operations
- NFR-BE-5.2: Graceful error handling with user-friendly messages
- NFR-BE-5.3: Logging for debugging and audit trails

## Acceptance Criteria

### AC-BE-1: Plan API

- Can create plan with all required fields via POST
- Can retrieve plan with default includes via GET
- Can retrieve plan with custom includes via GET
- Can update plan fields via PUT
- Can soft-delete plan via DELETE
- Unauthenticated requests return 401
- Cross-user access returns 403
- Validation errors return 422 with field details

### AC-BE-2: Autocomplete API

- Skill search returns results in < 200ms
- Search matches partial, case-insensitive on EN and JP names
- Results limited to 20 items
- Empty results return empty array, not 404
- Rate limiting enforced at 60 req/min

### AC-BE-3: Export/Import

- Excel export includes Plan Info, Stats, Skills, Goals sheets
- CSV export is UTF-8 with proper quoting
- All exports include schema_version field
- Import dry-run reports errors without persisting
- Import detects duplicates and warns user
- Import reports created/updated/skipped counts

### AC-BE-4: Stat Validation

- Stats accept values 0-1200 (hard max)
- Stats reject values outside range with 422
- Stats returned ordered by turn_number

### AC-BE-5: Skill Management

- Acquired skills require turn_acquired
- Skipped/Suggested skills allow null turn_acquired
- SP totals calculated correctly (acquired vs suggested)
- Skills filterable by status

### AC-BE-6: Authentication

- Sanctum tokens work for API authentication
- Session auth works for web UI
- Users can only access own plans
- Admin role can access all plans
- No public plan access without auth

### AC-BE-7: Convert Local to Account

- Conversion creates DB records for all related data
- Timestamps and ordering preserved
- Duplicate detection warns user
- Bulk conversion supported
- Transaction rollback on failure
