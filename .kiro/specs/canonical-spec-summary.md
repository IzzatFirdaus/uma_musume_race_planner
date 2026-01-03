# Uma Musume Career Planner - Canonical Spec Summary

**Document Version:** 1.0
**Date:** 2026-01-03
**Status:** AUTHORITATIVE

---

## Purpose

This document serves as the **single source of truth** for the Uma Musume Career Planner project. All spec files and documentation MUST align with the definitions in this document. When conflicts exist between documents, this canonical summary takes precedence.

---

## 1. Product Identity

| Property | Canonical Value |
|----------|-----------------|
| **Product Name** | Uma Musume Career Planner |
| **Document Title** | "Uma Musume Career Planner" (NOT "Career Tracker") |
| **Tech Stack** | Laravel 12+ / Livewire 3 / Alpine.js / TailwindCSS v4 |
| **PHP Version** | 8.2+ |
| **Database** | MySQL / MariaDB / SQLite |

---

## 2. Entity Glossary

| UI Term | Domain Entity | Database Table | Notes |
|---------|---------------|----------------|-------|
| Plan | CareerRun | `career_runs` | UI uses "Plan" for user-friendliness |
| Character | UmaMusume | `uma_musumes` | Horse girl character |
| Turn | StatProgress | `stat_progress` | Single turn's stat snapshot |
| Skill Entry | SkillCareerRun | `skill_career_runs` | Pivot with status + turn |
| Skill | Skill | `skills` | Reference data (NOT `skill_references`) |
| Snapshot | CareerSnapshot | `career_snapshots` | Race-day immutable capture |
| Goal | Goal | `goals` | Training objectives |
| Race Prediction | RacePrediction | `race_predictions` | Race planning entries |
| Activity Log | ActivityLog | `activity_logs` | User action history |

---

## 3. Canonical Naming Conventions

### 3.1 Database Tables (snake_case, plural)

```
career_runs
stat_progress
skill_career_runs
skills
uma_musumes
goals
race_predictions
career_snapshots
activity_logs
users
```

### 3.2 Foreign Keys (`{singular_table}_id`)

| Correct | INCORRECT (Do Not Use) |
|---------|------------------------|
| `career_run_id` | `run_id`, `plan_id` |
| `skill_id` | `skill_ref_id` |
| `uma_musume_id` | `character_id` |
| `user_id` | `owner_id` |

### 3.3 Canonical Field Names

| Canonical Name | INCORRECT Names | Context |
|----------------|-----------------|---------|
| `turn_number` | `turn`, `current_turn` (in history) | In `stat_progress` table |
| `current_turn` | `turn` | In `career_runs` table (current position) |
| `total_sp_available` | `sp`, `total_sp`, `sp_balance` | SP tracking |
| `stamina_percentage` | `stamina_pct`, `stamina_%` | Stamina tracking |
| `created_at` | `created`, `date_created` | Timestamps |
| `updated_at` | `modified`, `date_modified` | Timestamps |
| `deleted_at` | `deleted`, `is_deleted` | Soft delete |

---

## 4. Enum Definitions

### 4.1 Run Status (`career_runs.status`)

| Value | Description |
|-------|-------------|
| `in_progress` | Active training run |
| `completed` | Finished training run |
| `archived` | Archived for reference |

### 4.2 Skill Status (`skill_career_runs.status`)

| Value | Description | `turn_acquired` Required? |
|-------|-------------|---------------------------|
| `acquired` | Skill purchased | **YES** (1-78) |
| `skipped` | Decided not to buy | No |
| `suggested` | Recommended but undecided | No |

### 4.3 Storage Mode (`career_runs.storage_mode`)

| Value | Description |
|-------|-------------|
| `local` | Browser localStorage |
| `account` | Database (requires auth) |

### 4.4 Career Stage (`career_runs.career_stage`)

| Value | Description |
|-------|-------------|
| `junior` | Junior Year (~24 turns) |
| `classic` | Classic Year (~24 turns) |
| `senior` | Senior Year (~24 turns) |

### 4.5 Mood (`career_runs.mood`)

| Value | Modifier | Japanese |
|-------|----------|----------|
| `great` | +4% | 最高 |
| `good` | +2% | 良い |
| `normal` | 0% | 普通 |
| `bad` | -2% | 悪い |
| `awful` | -4% | 最悪 |

### 4.6 Aptitude Grade

| Value | Effectiveness |
|-------|---------------|
| `SS` | 120% |
| `S` | 110% |
| `A` | 100% |
| `B` | 90% |
| `C` | 80% |
| `D` | 70% |
| `E` | 60% |
| `F` | 50% |
| `G` | 40% |

### 4.7 Skill Tier

```
G-, G, G+, F-, F, F+, E-, E, E+, D-, D, D+, C-, C, C+, B-, B, B+, A-, A, A+, S-, S, S+, SS
```

### 4.8 Skill Type

```
speed, stamina, power, guts, wit, debuff
```

---

## 5. Validation Rules

### 5.1 Stat Validation

| Rule | Value | Notes |
|------|-------|-------|
| **Minimum** | 0 | Hard minimum |
| **Maximum** | 1200 | **HARD MAX** (NOT 2000) |
| **Soft Cap** | N/A | Removed - use 1200 hard max |

**IMPORTANT**: The stat range is **0-1200 hard maximum**. There is NO soft cap with diminishing returns. All documents referencing "0-2000 with soft cap at 1200" must be updated.

### 5.2 Turn Validation

| Field | Range |
|-------|-------|
| `turn_number` | 1-78 |
| `current_turn` | 1-78 |

### 5.3 Other Validations

| Field | Rule |
|-------|------|
| `plan.title` | Required, max 255 characters |
| `energy` | 0-100 |
| `stamina_percentage` | 0-100 |
| Image upload | Max 2MB, jpg/png/webp only |

---

## 6. Route Map

### 6.1 Web Routes (User-Facing)

| Route | Description |
|-------|-------------|
| `/` or `/dashboard` | Dashboard |
| `/plans` | Plan list |
| `/plans/{id}` | View account plan |
| `/plans/{id}/edit` | Edit account plan |
| `/plans/local/{uuid}` | View local plan |
| `/plans/local/{uuid}/edit` | Edit local plan |
| `/characters` | Character roster |
| `/import` | Import wizard |
| `/local-data` | Local data management |
| `/guide` | User guide |

### 6.2 Public API Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/api/v1/plans` | GET | List plans (authenticated) |
| `/api/v1/plans` | POST | Create plan |
| `/api/v1/plans/{id}` | GET | Get plan |
| `/api/v1/plans/{id}` | PUT | Update plan |
| `/api/v1/plans/{id}` | DELETE | Delete plan |
| `/api/v1/plans/import` | POST | Import plans |
| `/api/v1/plans/export` | GET | Export plans |
| `/api/v1/autosuggest/skills` | GET | Skill search |
| `/api/v1/autosuggest/characters` | GET | Character search |

**Note**: The public API uses `/api/v1/plans` (NOT `/api/v1/career-runs`) for user-friendliness.

### 6.3 Internal Routes (Livewire)

| Route | Description |
|-------|-------------|
| `/internal/skills/search` | Skill autocomplete |
| `/internal/uma-musume/search` | Character autocomplete |

---

## 7. Storage Mode Rules

### 7.1 Local Mode

- Uses browser localStorage (MVP), IndexedDB planned for future
- Plans identified by UUID
- Routes: `/plans/local/{uuid}`
- Fully functional offline
- No authentication required
- Data persists until browser cache cleared

### 7.2 Account Mode

- Uses database (MySQL/MariaDB/SQLite)
- Plans identified by integer ID
- Routes: `/plans/{id}`
- Requires network connectivity to save
- Requires authentication
- Cross-device access

### 7.3 Convert/Claim Flow

- "Convert to Account" action on local plans (when authenticated)
- Default: Move (delete local after successful conversion)
- Optional: "Keep local copy" checkbox
- Duplicate detection: same title + character + created date

---

## 8. Soft Delete Policy

### 8.1 Soft-Deletable Entities

| Entity | Soft Delete? | Notes |
|--------|--------------|-------|
| CareerRun (Plan) | ✅ Yes | 30-day recovery |
| Goal | ✅ Yes | Cascade with plan |
| RacePrediction | ✅ Yes | Cascade with plan |
| CareerSnapshot | ✅ Yes | Cascade with plan |
| ActivityLog | ✅ Yes | Audit retention |

### 8.2 Reference Data (NOT Soft-Deletable)

| Entity | Soft Delete? | Notes |
|--------|--------------|-------|
| Skill | ❌ No | Reference data, admin hard-delete only |
| UmaMusume | ❌ No | Reference data, admin hard-delete only |

### 8.3 Hard-Deletable Entities

| Entity | Notes |
|--------|-------|
| StatProgress (turns) | Hard delete, cascade with plan |
| SkillCareerRun (pivot) | Hard delete, cascade with plan |

---

## 9. Export/Import Schema

### 9.1 Schema Versioning

All exports MUST include:

```json
{
  "schema_version": "1.0",
  "exported_at": "2026-01-03T12:00:00Z",
  "data": { ... }
}
```

### 9.2 Supported Formats

| Format | Priority | Notes |
|--------|----------|-------|
| JSON | P0 (MVP) | Primary format |
| CSV | P1 | Secondary format |
| Excel (.xlsx) | P1 | Multi-sheet export |
| Markdown | P1 | Human-readable |

---

## 10. Precedence Rules

When documents conflict, apply this precedence:

1. **This Canonical Summary** (highest)
2. **Consolidation Spec** (`.kiro/specs/consolidation/`)
3. **Backend Spec** (`.kiro/specs/umamusume-planner-backend/`)
4. **Frontend Spec** (`.kiro/specs/umamusume-planner-frontend/`)
5. **Documentation** (`docs/D01-D17`) (lowest - must be updated to match)

---

## 11. Conflict Matrix (Issues Resolved)

All conflicts identified in the initial audit have been resolved. The following changes were applied:

| Issue | Previous Value | Canonical Value | Status |
|-------|----------------|-----------------|--------|
| Product Name | "Career Tracker" | "Career Planner" | ✅ Resolved |
| Stat Range | 1-2000 | 0-1200 hard max | ✅ Resolved |
| Soft Cap | "soft cap at 1200" | Removed (1200 hard max) | ✅ Resolved |
| Skills Table | `skill_references` | `skills` | ✅ Resolved |
| Skill/UmaMusume Soft Delete | Soft deletable | NOT soft deletable | ✅ Resolved |
| API Route | `/api/v1/career-runs` | `/api/v1/plans` | ✅ Resolved |
| Image Size | 5MB (some docs) | 2MB | ✅ Resolved |

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-01-03 | System | Initial canonical summary |
| 1.1 | 2026-01-03 | System | Resolved all conflicts - updated all specs and docs |
