# PRD.md - Product Requirements Document

## Uma Musume Career Planner

**Version:** 1.1  
**Date:** 2026-09-26  
**Status:** Active

> **Correction notice (v1.1).** Several requirements below describe intent that
> the source tree does not currently implement. Where reality and this document
> disagree, [`ARCHITECTURE.md`](./ARCHITECTURE.md) wins — it was written from a
> direct code audit. Known divergences are tabulated in
> [`ARCHITECTURE.md` §12](./ARCHITECTURE.md#12-prd-divergences). The most
> material: **Excel export is not built** (no library), **only 2 of 5 legacy
> import formats exist** (JSON, CSV), the **skill catalog holds 46 rows with
> `name_jp` null**, **per-user data isolation is not enforced** (no auth
> middleware, no authorization call), and **Local mode's client-side store does
> not exist**, leaving 3 Local routes dead.

---

## 1. Product Overview

### 1.1 Product Name
**Uma Musume Career Planner** (formerly "Uma Musume Race Planner")

### 1.2 Product Vision
A unified, modern web platform that consolidates five legacy Uma Musume tracking applications into a single, feature-rich experience. Players can plan, track, analyze, and share their character training progression across devices with full offline support.

### 1.3 Problem Statement
Uma Musume: Pretty Derby players currently face:
- **Fragmented tooling**: Five separate legacy applications with overlapping but incomplete features
- **No cross-device access**: Data locked to a single browser/device
- **Data loss risk**: localStorage quota limits and no backup strategy
- **Poor mobile experience**: Legacy apps not responsive or accessible
- **No data portability**: Cannot export/import between tools or share with community

### 1.4 Target Audience
| Segment | Description | Primary Needs |
|---------|-------------|---------------|
| **Casual Players** | Play occasionally, want simple tracking | Quick plan creation, offline access, simple exports |
| **Competitive Players** | Optimize for Champions Meeting, League of Heroes | Detailed stat tracking, skill planning, race predictions |
| **Content Creators** | Make guides, videos, comparisons | Excel/Markdown exports, clean data format |
| **Community Members** | Share and exchange training plans | Import/export compatibility, schema versioning |

---

## 2. Core Value Propositions

### 2.1 Dual Storage Architecture
- **Local Mode**: Full offline functionality using browser localStorage (MVP) → IndexedDB (Post-MVP)
- **Account Mode**: Authenticated, server-backed persistence with cross-device sync
- **Seamless Conversion**: One-click migration from Local → Account when user authenticates

### 2.2 Comprehensive Career Tracking
- Turn-by-turn stat logging (Speed, Stamina, Power, Guts, Wit) with 1200 cap enforcement
- Bilingual skill management (EN/JP autocomplete) with 3-state tracking (Acquired/Skipped/Suggested)
- Race planning with predictions, stamina thresholds, and race-day snapshots
- Goal setting with completion tracking

### 2.3 Data Portability & Legacy Migration
- Export: JSON, Excel (.xlsx), CSV, Markdown with schema versioning
- Import: All 5 legacy formats with preview, conflict resolution, and schema migration
- Community sharing via standardized export formats

### 2.4 Accessibility-First Design
- WCAG 2.1 AA compliance
- Full keyboard navigation
- Dark/Light mode with system preference detection
- Reduced-motion support
- Screen reader compatible

---

## 3. User Requirements

### 3.1 Functional Requirements

#### FR-1: Character Management
- CRUD operations for Uma Musume characters
- Aptitude grades (Turf/Dirt, Sprint/Mile/Medium/Long, Nige/Senkou/Sashi/Oikomi)
- Growth rate bonuses for all 5 stats
- Image upload (JPG/PNG/WebP, ≤2MB)

#### FR-2: Career Run (Plan) Management
- Create runs linked to characters with title, career stage (Junior/Classic/Senior), storage mode
- Status tracking: In Progress / Completed / Archived
- Turn tracking (1-78), SP balance, stamina %, energy, mood, conditions, strategy, notes

#### FR-3: Stat Progression
- Log 5 stats per turn with validation (0-1200 raw values)
- Auto-calculate totals and growth rates
- Visual progress bars with stat-specific colors
- Milestone turn highlighting

#### FR-4: Skill Management
- Bilingual (EN/JP) autocomplete search across 500+ skills
- 3-state status: Acquired (requires turn), Skipped, Suggested
- SP cost tracking with separate Acquired/Suggested totals
- Tier and type categorization

> **Status: partially built.** Catalog search, 3-state status, and SP totals
> are implemented. The catalog is **46 seeded skills**, not 500+; `name_jp` is
> `null` on every seeded row, so bilingual matching is unproven (an orphaned
> `SkillSeeder` holds 11 JP names but is never called by `DatabaseSeeder`).

#### FR-5: Race Planning
- Race predictions: name, venue, distance category, track type, predicted/actual placement
- Recommended stamina thresholds by distance/track
- Race-day snapshots capturing full character state

#### FR-6: Goals
- Create/edit/complete/delete training objectives
- Visual distinction for completed goals
- Sort ordering

#### FR-7: Data Export
- Single plan or bulk export
- Formats: JSON, Excel, CSV, Markdown
- Schema version metadata
- Copy to clipboard
- Preview before download

> **Status: JSON, CSV, Markdown built. Excel NOT built** — no spreadsheet
> library is installed and `ExportService` is deliberately dependency-free pure
> PHP. Markdown export is intentionally capped at the 10 most recent turns.

#### FR-8: Data Import
- Format detection (JSON, CSV, 5 legacy formats)
- Schema version migration
- Preview with duplicate detection
- Target storage mode selection (Local/Account)

> **Status: 2 of 5 legacy formats built** — `JsonImportAdapter` and
> `CsvImportAdapter` behind `ImportAdapterInterface`, orchestrated by
> `ImportService` (detect → preview → validate → import). Three more adapters
> are required to meet this requirement. Note the CSV adapter's positional
> fallback can "succeed" with wrong column mapping on unrecognised headers.

#### FR-9: Local Data Management
- Storage usage visualization (progress bar)
- List local runs with size info
- Bulk export, selective delete, bulk conversion to Account

> **Status: NOT functional.** The client-side Local store
> (`resources/js/services/index.js` → `window.localRunStorage`) is missing, and
> `App\Livewire\Plans\LocalPlanView` — which serves all 3 `/plans/local/*`
> routes — does not exist. Server-side support
> (`LocalRunStorageService`, `ConvertLocalRunService`) is built.

#### FR-10: Draft Auto-Save
- Timed interval auto-save to localStorage
- Keep last 3 versions with timestamps
- Restore/discard actions
- Clear on successful save

---

### 3.2 Non-Functional Requirements

| Category | Requirement | Target |
|----------|-------------|--------|
| **Performance** | Page load (dashboard/edit) | <2s (Fast 3G, Moto G4) |
| | First Contentful Paint | <1.5s |
| | Skill autocomplete response | <200ms |
| | Large export (50k rows) | <60s |
| **Accessibility** | WCAG 2.1 AA | 100% compliance |
| | Keyboard navigation | All interactive elements |
| | Contrast ratio | 4.5:1 normal text |
| | Reduced motion | Respect prefers-reduced-motion |
| **Responsive** | Viewport range | 320px - 2560px |
| | Touch targets | ≥44px |
| | Breakpoints | Mobile (<768px), Tablet, Desktop, Wide |
| **Security** | CSRF protection | All forms |
| | HTTPS enforcement | Account mode |
| | File upload validation | Type, size, MIME |
| | Per-user data isolation | Account mode |

> **CRITICAL — per-user data isolation is NOT met.** `routes/web.php` has no
> middleware, and `PlanDetailsPage::mount($planId)` performs no ownership
> check. `PlanPolicy` defines owner-only access correctly but is never called,
> and `Plan::booted()` silently forces `user_id = 1` (the public user) when
> empty. Any anonymous visitor can currently view and edit any account plan by
> ID. This must be fixed before any real user data exists.
| **Reliability** | Uptime (Account mode) | 99% |
| | Data retention (Account) | Soft delete 30 days |
| | Draft retention | 7 days |

---

## 4. Success Metrics

### 4.1 User Experience
- Time to create first plan: <30 seconds
- Task completion rate: >95%
- User satisfaction: >4/5 stars

### 4.2 Technical
- Test coverage: 90%+
- Zero critical bugs in first month post-launch
- Page load targets met consistently
- Accessibility score: 100% AA (axe-core)

> **Status: not currently measurable.** 22 PHPUnit classes and 9 Playwright
> specs exist, but there are **0 JS unit tests** (`npm test` and
> `npm run test:vitest` both collect nothing; `npm run lighthouse-ci` fails on a
> missing config), there are no route/HTTP or policy tests, and the suite is
> hard-coded to a live MySQL database. Coverage has not been measured.

### 4.3 Adoption
- Successful legacy imports: Track per format
- Local → Account conversion rate
- Export usage: exports/week
- Feature utilization tracking

---

## 5. Scope Boundaries

### 5.1 In Scope (MVP)
- All P0/P1 requirements from BRS (Requirements 1-79)
- Dual storage (Local + Account)
- Full CRUD for Career Runs, Stats, Skills, Races, Goals
- Import/Export with legacy format support
- WCAG 2.1 AA compliance
- Playwright E2E coverage for critical flows

### 5.2 Out of Scope (Post-MVP)
- Real-time collaborative editing
- Social/community features (sharing, comments, ratings)
- Native mobile apps
- Game server integration
- AI-assisted recommendations (planned P2)
- Advanced charting/analytics (planned P2)

---

## 6. Assumptions & Constraints

### 6.1 Assumptions
- Users have modern browsers (Chrome, Firefox, Safari, Edge)
- Users understand Uma Musume terminology
- English primary UI language, Japanese for skill names
- Sufficient browser storage for Local mode

### 6.2 Constraints
- localStorage quota: ~5-10MB (migrate to IndexedDB post-MVP)
- Account mode requires network connectivity
- No native app in MVP
- PHP 8.2+, MySQL/MariaDB/SQLite
- Web-only deployment

---

## 7. Dependencies

| Dependency | Purpose | Risk |
|------------|---------|------|
| Browser localStorage | Local mode persistence | Medium (quota limits) |
| Livewire connection | Account mode operations | Medium |
| Database (MySQL/MariaDB/SQLite) | Account data persistence | Medium |
| Skill reference database | Autocomplete search | Low (pre-populated) |
| Laravel Sanctum | Authentication | Low |

---

## 8. Related Documents

- [ARCHITECTURE.md](./ARCHITECTURE.md) - Technical implementation blueprint
- [ARCHITECTURE-ESSENTIALS.md](./ARCHITECTURE-ESSENTIALS.md) - Condensed architecture reference
- [AGENTS.md](./AGENTS.md) - AI agent roles and responsibilities
- [CLAUDE.md](./CLAUDE.md) - LLM coding standards and context
- [docs/D02_Business_Requirements_Specifications.md](./docs/D02_Business_Requirements_Specifications.md) - Detailed business requirements
- [docs/D03_System_Requirements_Specifications.md](./docs/D03_System_Requirements_Specifications.md) - Testable system requirements
- [docs/D04_System_Design_Specifications.md](./docs/D04_System_Design_Specifications.md) - Implementation design
- [docs/D09_Database_Documentation.md](./docs/D09_Database_Documentation.md) - Database schema