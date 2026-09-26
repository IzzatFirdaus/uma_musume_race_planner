# ARCHITECTURE.md — Uma Musume Career Planner

**Version:** 1.0
**Date:** 2026-09-26
**Status:** Active — describes **as-built** reality, not intent
**Supersedes:** `docs/D04_System_Design_Specifications.md` (retained for history)

> **Reading contract.** This document is the technical source of truth. Where it
> contradicts `docs/D04*` or `PRD.md`, **this document wins** — it was written
> from a direct audit of the source tree. Known divergences are listed in
> §12 and flagged inline with **[DRIFT]**.

> **Condensed companion.** For quick context, read
> [`ARCHITECTURE-ESSENTIALS.md`](./ARCHITECTURE-ESSENTIALS.md) instead. It is a
> strict subset of this file with no unique facts.

---

## 1. System Context

### 1.1 What the product is

A career-planning tool for *Uma Musume: Pretty Derby*. A player tracks a
character ("trainee") through a simulated career: turn-by-turn stat growth,
skill acquisition, aptitude grades, race entries, and goals.

### 1.2 The defining architectural constraint: dual storage

The product's defining feature is that **career runs can live in two places**:

| Mode | Location | Survives browser clear? | Cross-device? | Requires auth? |
|------|----------|------------------------|---------------|----------------|
| **Local** | Browser `localStorage` (via `window.localRunStorage`) | No | No | No |
| **Account** | Server database, `plans.storage_mode = 'account'` | Yes | Yes | Yes |

The server holds *no* canonical record of a Local run. It is a **stateless
schema authority and computation engine** for Local runs: it defines the
payload shape, validates it, migrates it between schema versions, sizes it
against the browser quota, and converts it into a database `Plan` on demand.

This inversion — server computes, browser persists — is the single most
important thing to understand about this codebase, and the source of most of
its fragility. See §5 and §11.

### 1.3 Delivery model

Single Laravel application serving both server-rendered Blade/Livewire pages
and a versioned JSON API. Deployed to a **subdirectory** (`/uma-musume-planner-laravel/public/`),
not a domain root — this constrains asset URLs, the Playwright `baseURL`, and
the `<base href>` logic in the layouts.

---

## 2. Technology Stack

Exact installed versions, from `composer.lock` / `package-lock.json`.

### 2.1 Backend

| Component | Version | Role |
|-----------|---------|------|
| PHP | 8.4.11 | Runtime. `composer.json` requires `^8.2` |
| Laravel Framework | **12.44.0** | Application framework (constraint `^12.0 \|\| ^13.0`) |
| Livewire | **3.7.3** | All server-stateful UI (constraint `^3.6`) |
| Laravel Sanctum | 4.2.1 | API auth scaffolding for `api/*` group |
| Laravel Pint | 1.26.0 | Code formatter |
| Larastan | 3.6.1 (pinned exactly) | PHPStan static analysis |
| PHPUnit | 11.5.46 | Test runner |
| Laravel Boost | 1.8.7 | MCP tool server for AI assistants |
| Collision | 8.8.3 | TTY error rendering |
| ide-helper | 3.6.1 | Model/IDE metadata |
| phpinsights | 2.13.3 | Code-quality scoring (`config/insights.php`) |
| laravel-microscope | 1.0.431 | Request instrumentation |
| Faker | 1.24.1 | Test data |

**No Excel/spreadsheet library is installed.** Export is pure PHP. See §7.3.

### 2.2 Frontend

| Component | Version | Role |
|-----------|---------|------|
| Vite | 7.1.12 | Asset bundler, dev server |
| laravel-vite-plugin | 2.0.0 | Laravel/Vite bridge |
| Tailwind CSS | 4.1.11 | **Declared but NOT wired** — see §3.3 |
| @tailwindcss/vite | 4.1.11 | **Installed but not registered in `vite.config.js`** |
| Alpine.js | 3.15.3 | Shipped *inside* Livewire's bundle, not npm-imported |
| SweetAlert2 | 11.23.0 | Modal/alert library |
| axios | 1.12.2 | HTTP client (devDependency placement is wrong) |
| Prettier | 3.6.2 | Formatter |
| Stylelint | 16.23.1 | CSS linting |
| Playwright | 1.56.1 | E2E + axe-core a11y |
| axe-core (Playwright) | 4.11.0 | Accessibility assertions |
| Jest | 30.1.3 | **Configured, collects 0 tests** — see §10.3 |
| Vitest | 4.0.16 | **No config file, collects 0 tests** |
| fast-check | 4.5.3 | **Installed, zero usages** |
| concurrently | 9.2.0 | `composer dev` task runner |

### 2.3 CDN-loaded runtime dependencies

Loaded via `<script>`/`<link>` in `layouts/app.blade.php`, **not** via Vite:

- Bootstrap **5.3.0-alpha1** (CSS + JS) — an **alpha**, not a stable release
- bootstrap-icons 1.10.0
- Chart.js (**unpinned** — no version in the URL)
- Google Fonts *M PLUS Rounded 1c*

**[DRIFT] Bootstrap and Tailwind are both claimed as the styling system.** The
project is mid-migration from Bootstrap 5 to Tailwind v4 (§3.3).

### 2.4 Database

MySQL / MariaDB (SQLite nominally supported; **not** used for tests — see §10.2).
26 migrations: 3 stock Laravel + 23 domain, in three waves (§4.2).

---

## 3. Application Layers

```
┌──────────────────────────────────────────────────────────────┐
│  BROWSER                                                       │
│  ┌────────────────────┐   ┌───────────────────────────────┐   │
│  │ Livewire 3         │   │ localStorage                  │   │
│  │ 33 components      │◄─►│  local run payloads           │   │
│  │ (server stateful)  │   │  (Local mode canonical store) │   │
│  └────────────────────┘   └───────────────────────────────┘   │
│  ┌────────────────────┐   ┌───────────────────────────────┐   │
│  │ Alpine.js          │   │ Vanilla JS layer (legacy)     │   │
│  │ micro-interactions│   │  main.js (1232 lines)         │   │
│  └────────────────────┘   └───────────────────────────────┘   │
└──────────────┬──────────────────────────────┬──────────────────┘
               │ Livewire wire: protocol      │ fetch() / axios
               │ (CSRF-protected)             │
┌──────────────▼──────────────────────────────▼──────────────────┐
│  LARAVEL 12                                                     │
│                                                                │
│  routes/web.php  (public, no auth middleware)                  │
│      │                                                         │
│      ├─► app/Livewire/**            33 page components         │
│      │        │                                                 │
│      │        └─► app/Services/**   13 services (business)     │
│      │                  │                                      │
│      │                  ├─► app/Services/Import/**  4 classes  │
│      │                  └─► app/Models/**        17 models     │
│      │                             │                          │
│      │                             ▼                          │
│      │                      Eloquent  ──►  MySQL               │
│      │                                                        │
│      └─► app/Policies/PlanPolicy   (defined, largely unused)  │
│                                                                │
│  routes/api.php  (/api/v1, Sanctum stateful)                   │
│      └─► app/Http/Controllers/Api/V1/**  6 controllers         │
│               └─► app/Http/Resources/Api/V1/**  6 resources     │
│                                                                │
│  app/Jobs/ProcessPlanExport  (ShouldQueue)                     │
│  app/Events/{PlanCreated,PlanUpdated}                          │
│  app/Listeners/ClearPlanCache                                  │
└────────────────────────────────────────────────────────────────┘
```

### 3.1 Routing surfaces

**`routes/web.php` — 79 lines, 14 routes, ZERO middleware.**
Not a single `auth` or `guest` group exists anywhere in the project. Every
page — including `/local-data`, `/import`, and `/plans/{planId}/edit` — is
reachable anonymously. Auth state is expressed only in Blade via `@auth`/`@else`.
This is the root cause of the IDOR in §11.1.

Notable: `plans.local.*` routes are registered *before* `plans.{planId}`,
which is correct, but `local` is a literal segment living under a
`{planId}`-capable prefix.

**`routes/api.php` — 24 routes under `/api/v1`.**
`api` group is overridden in `bootstrap/app.php:19-23` to
`throttle:api` + `EnsureFrontendRequestsAreStateful` + `SubstituteBindings`.
Exception rendering is centralised in `bootstrap/app.php:26-56`:
`ApiException` → JSON, `ValidationException` → 422 on `api/*`,
`ModelNotFoundException` → 404 on `api/*`.

### 3.2 Livewire component inventory (33)

All extend `Livewire\Component` directly. **There is no project base class
and no shared trait** — cross-cutting concerns are copy-pasted per component.

| Namespace | Count | Components |
|-----------|-------|-----------|
| `Dashboard` | 7 | `PlanDetailsPage`, `PlanList`, `PlanInlineDetails`, `HeaderBanner`, `StatsPanel`, `RecentActivity`, `SupportCardSummary` |
| `Layout` | 4 | `Navbar`, `Footer`, `Alerts`, `Modals` |
| `Common` | 5 | `Toast`, `SkeletonLoader`, `DirtyStateWarning`, `DarkModeToggle`, `ConfirmModal` |
| *(root)* | 6 | `FormTabs`, `PlanDetails`, `QuickCreatePlan`, `TraineeImageHandler`, `TurnTracker`, `GuideStickyNav` |
| `Skills` | 2 | `SkillSearch`, `SkillEditor` |
| `Plans` | 2 | `TrainingYear`, `SkillRow` — **plus `LocalPlanView`, which is referenced by 3 routes and DOES NOT EXIST** |
| `CareerRun` | 2 | `StorageModeIndicator`, `ConvertToAccountButton` |
| `Import` / `Export` / `LocalData` / `Auth` / `Characters` | 5 | `ImportWizard`, `ExportModal`, `Manager`, `ConvertRunModal`, `CharacterList` |

Only two components use `WithFileUploads` (`ImportWizard`,
`TraineeImageHandler`). Only `PlanList` uses `WithPagination`.

**View parity:** every component has a 1:1 kebab-case view under
`resources/views/livewire/**` (30 views). `app/View/Components/` **does not
exist**; all Blade components are anonymous.

### 3.3 View layer — three competing layouts

This is the largest structural inconsistency in the codebase.

| Layout | Style | Consumers |
|--------|-------|-----------|
| `layouts/app.blade.php` (156 ln) | **Bootstrap 5** | `@extends` from `dashboard`, `guide`, `characters` |
| `components/layout.blade.php` (248 ln) | **Tailwind** + Alpine | `#[Layout]` on `Dashboard\PlanDetailsPage`, `LocalData\Manager` |
| `components/layouts/app.blade.php` (159 ln) | Tailwind, near-duplicate of #1 | **unreferenced** |

`components/layout.blade.php` reads `$store.preferences.darkMode` and declares
an FOUC-guard reading `uma_preferences` — an Alpine store that **does not
exist**, because `resources/js/stores/index.js` is missing (§10.1). It also
references four Blade components that do not exist: `x-connection-banner`,
`x-toast-container`, `x-reconnection-prompt-modal`, `x-keyboard-shortcuts-help`.

**Tailwind is not actually active.** `resources/css/app.css:1` is only
`@import url("style.css")` — there is no `@import "tailwindcss"`. And
`vite.config.js` does **not** register the `@tailwindcss/vite` plugin despite
it being a devDependency. The 295-line `tailwind.config.js` — with custom
`stat.*`, `grade.*`, `tier.*`, `mood.*`, `status.*`, `storage.*` colour scales
and a ~120-entry safelist — is therefore **entirely inert**. Any page rendered
through the Tailwind layout is unstyled.

### 3.4 JavaScript — hybrid, with a legacy layer

`resources/js/app.js` (61 lines) imports Livewire + Alpine from
`vendor/livewire/livewire/dist/livewire.esm` so both share one Alpine
instance, then calls `Livewire.start()`.

It also imports **eight modules that do not exist** (§10.1). Four of the eight
are destructured named exports (`localRunStorage`, `draftService`,
`localDataManager`, `offlineDraftManager`, `accountPlanEditor`,
`raceSnapshotQuota`) that are assigned to `window`.

Alongside this sits a **legacy vanilla layer that is still 1232 lines**:
`main.js` performs Bootstrap modal control, Chart.js growth charts, `fetch()`
calls to `/api/v1/*`, SweetAlert2, direct DOM writing via `populateForm()`, and
a `Livewire.on(...)` bridge for events (`openPlanModal`, `loadPlan`,
`loadPlanInline`, `show-error`, …). Plus `characters.js`, `autosuggest.js`,
`skill_management.js`, `utils.js`, `bootstrap.js`.

So the UI is simultaneously being driven by Livewire and by imperative JS
reaching into Livewire-rendered DOM. §11.4 quantifies the cost.

---

## 4. Data Model

17 models. 23 domain migrations.

### 4.1 Aggregate root: `Plan`

`plans` is the aggregate root. One `Plan` = one career run.

Notable columns and behaviours (`app/Models/Plan.php`):

- `storage_mode` (enum `StorageMode`), `local_uuid` (nullable, unique),
  `scenario` (enum `Scenario`)
- 5 × `growth_rate_*` integer columns (default 0)
- `softDeletes`
- `booted()` at line 165: **silently forces `user_id = 1` when empty**
- `booted()` writes an `ActivityLog` row on every `created` / `updated` / `deleted`

Helpers: `isLocal()`, `isAccount()`, `getRunKey()` (`local:<uuid>` /
`account:<id>`), `getRunRoute($action)`, `parseRunKey()`, `findByRunKey()`.
Scopes: `scopeLocal()`, `scopeAccount()`, `scopeStorageMode()`.

Relations: `user()`, `mood()`, `condition()`, `strategy()`, and nine
`HasMany`: `attributes`, `skills`, `goals`, `racePredictions`, `turns`,
`terrainGrades`, `distanceGrades`, `styleGrades`, `careerSnapshots`.

### 4.2 Entity map

| Model | Table | Timestamps | Soft deletes | Notes |
|-------|-------|-----------|--------------|-------|
| `User` | `users` | yes | no | `HasApiTokens`, `Notifiable` |
| `Plan` | `plans` | yes | **yes** | Aggregate root |
| `Turn` | `turns` | no | no | Unique `(plan_id, turn_number)`. Turn-by-turn stats |
| `Attribute` | `attributes` | no | no | Unique `(plan_id, attribute_name)` |
| `Skill` | `skills` | yes | **yes** | 3-state `status`; immutable-ish turn rules |
| `SkillReference` | `skill_reference` | no | no | Master catalog, explicit `$table` |
| `Goal` | `goals` | no | no | Unique `(plan_id, goal)` |
| `RacePrediction` | `race_predictions` | yes | no | Stat cols are **strings defaulting to `'○'`** |
| `CareerSnapshot` | `career_snapshots` | `created_at` only | no | **Immutable** — `updating()` returns false |
| `TerrainGrade` / `DistanceGrade` / `StyleGrade` | same | no | no | Aptitude grades, unique per plan+key |
| `Mood` / `Condition` / `Strategy` | same | no | no | Lookup dimensions |
| `ActivityLog` | `activity_log` | custom `timestamp` col | no | Polymorphic, `metadata` JSON |
| `Umamusume` | `umamusume` | yes | no | **String PK, no relations, 18 JSON columns** |

### 4.3 Model convention deviations

These break the house style and will surprise anyone extending the code:

1. **`Umamusume`** — `$incrementing = false`, `$primaryKey = 'id'`,
   `$keyType = 'string'`, **zero Eloquent relationships**, and 18 columns cast
   to `AsArrayObject`. It is a document store wearing a model's clothes.
   Every `aptitudes` / `base_stats` / `growth_rates` query is raw array
   filtering in PHP, not SQL.
2. **`SkillReference`**, **`ActivityLog`**, **`Umamusume`** declare `$table`
   explicitly even where the value matches convention.
3. **`ActivityLog`** uses `CREATED_AT = 'timestamp'`, `UPDATED_AT = null`.
4. **`CareerSnapshot`** is write-once by construction.
5. **`RacePrediction`** stores stat values as `string` with default `'○'`
   (a circle glyph meaning "not set"). This is a presentation concern
   persisted into a schema, and it makes the columns non-numeric and
   non-comparable in SQL.
6. Child models disable timestamps via `public $timestamps = false`, so
   ordering by recency is impossible for `turns`, `skills` (pre-consolidation),
   `goals`, and all grade tables.

### 4.4 Enums (9, all string-backed)

`UmaClass` (9 cases + `order()` 0-8) · `StorageMode` · `SkillStatus` ·
`Scenario` (only `URA` implemented; others commented out) · `RunStatus` ·
`ImportTarget` · `CareerYear` · `AptitudeGrade` (SS→G with `value()` 1-9,
`effectivenessPercentage()` 40-120, colour helpers).

**Dead enums:** `RunStatus` and `CareerYear` are defined but cast on no model.
`RunStatus::Ongoing/Finished/Failed` also duplicates the `plans.status`
column, which uses an entirely different value set (`'Planning'`, etc.). Two
incompatible status vocabularies coexist.

### 4.5 Seed data

`DatabaseSeeder` order: Test User → `LookupSeeder` → `SkillReferenceSeeder` →
`UmamusumeSeeder` → `PlanSeeder` → `ActivityLogSeeder`.

| Seeder | Contents |
|--------|----------|
| `LookupSeeder` | 5 moods, 11 conditions, 4 strategies (fixed IDs) + ensures Public User id=1 |
| `SkillReferenceSeeder` | **46 skills** — vs. the PRD's "500+" claim (§12) |
| `UmamusumeSeeder` | 10 characters, full nested JSON |
| `PlanSeeder` | 8 plans + truncates all child tables. Has an unused private `createSkill()` helper — dead code |
| `ActivityLogSeeder` | 8 static rows |
| `SkillSeeder` | 11 bilingual skills, **not called** by `DatabaseSeeder`; superseded |

**No `name_jp` is populated** by `SkillReferenceSeeder` — the column added by
migration `2026_07_03_000002` stays `null`, so the PRD's headline "bilingual
(EN/JP) autocomplete" runs against a single-language catalog for 46 of 46
seeded skills. The alternate `SkillSeeder` has the JP names but is orphaned.

### 4.6 Factories (14) and their gaps

Factories exist for `User`, `Umamusume`, `Plan`, `Attribute`, `Skill`,
`SkillReference`, `Goal`, `TerrainGrade`, `DistanceGrade`, `StyleGrade`,
`Mood`, `Condition`, `Strategy`, `User`.

- **No factory** for `Turn`, `RacePrediction`, or `CareerSnapshot` — all three
  declare `HasFactory` but cannot be built with it.
- `SkillFactory` sets a **`rarity` attribute that is not a column on `skills`**
  (stale, left over from the pre-consolidation schema).
- `PlanFactory` never sets `scenario`, `storage_mode`, or `local_uuid`, so
  factory-built plans do not exercise the dual-storage code path.

---

## 5. Dual Storage in Depth

### 5.1 Representation

`StorageMode` is a two-case enum threaded explicitly through the system.
`Plan` carries `storage_mode` and `local_uuid`; Local runs additionally have a
server-side *identity* (the UUID) with no server-side *data*.

The routing abstraction is the run key:

```
account run  →  "account:<id>"   →  /plans/{id}
local run    →  "local:<uuid>"   →  /plans/local/{uuid}
```

### 5.2 Where the branch occurs

The mode is **not** abstracted behind a repository interface. It is expressed
as `if ($plan->isLocal())` conditionals scattered through services:

| Location | Behaviour |
|----------|-----------|
| `PlanService::createQuickPlan()` | Local → returns `{storage_mode, redirect_url, local_payload, uuid}` for the client to persist. Account → creates DB row + default attributes |
| `StatProgressService::logTurn()` | Local → returns a plain array for the client to push. Account → creates a `Turn`, recalculates totals |
| `ConvertLocalRunService` | Local → Account, in a transaction, preserving timestamps and turn order |
| `LocalRunStorageService` | Local-only: schema validation, migration, sizing, quota math |

**This is the project's most significant design debt.** Every new write
operation must remember to branch, and there is no compiler-level or
interface-level enforcement. See §11.2.

### 5.3 `LocalRunStorageService` — the schema authority

Server-side utilities for a store the server cannot see. Schema version is
`1.0.0`, matched by `ExportService::SCHEMA_VERSION`.

- `generateUuid()`, `createEmptyRun()`, `buildQuickPlanPayload()`
- `validateStructure(array)` — validates a client payload
- `migrateSchema(array)` — forward migration `migrateToV1()`:
  `total_sp` → `total_sp_available`, `stamina_pct` → `stamina_percentage`,
  `turn` → `current_turn`
- `calculateStorageSize()`, `formatStorageSize()`, `getQuotaWarningThreshold()`,
  `isApproachingQuota()` — 5 MB warning threshold

### 5.4 Conversion (Local → Account)

`ConvertLocalRunService::convert()` validates, checks duplicates via
`DuplicateDetectionService`, then in one transaction creates the `Plan` plus
turns, skills, goals, race predictions, and snapshots, **preserving original
timestamps and turn ordering**. `bulkConvert()` returns per-item results.
`prepareBackup()` wraps the data for a pre-conversion download.

Duplicate detection strategies, in order: exact title match → exact
name+date → **fuzzy Levenshtein ≥ 0.85 similarity**. Resolution options are
`create_duplicate` and `cancel`; `merge` is explicitly deferred.

Conversion is one-way. There is no Account → Local path.

---

## 6. Services (13 + 4 import classes)

| Service | Responsibility |
|---------|----------------|
| `PlanService` | Plan CRUD with dual-storage branching; `getPlanByIdOrUuid()` |
| `StatProgressService` | Turn logging (dual-mode), totals, averages, growth, chart data |
| `SkillService` | Catalog search (5-min cache), 3-state status, SP totals, bulk ops, `findOrCreateReference()` |
| `SnapshotService` | Immutable race-day snapshots; `compareSnapshots()` |
| `ExportService` | Pure-PHP JSON/CSV/Markdown serialisation (§7.3) |
| `ImportService` | Import orchestration (§7.2) |
| `ConvertLocalRunService` | Local → Account conversion |
| `DuplicateDetectionService` | Three-tier duplicate detection |
| `LocalRunStorageService` | Local payload schema authority (§5.3) |
| `UmaMusumeService` | Character master data, cached search, image cleanup |
| `ImageProcessingService` | MIME sniff, 2 MB cap, EXIF strip (GD), 150×150 thumbnail |
| `ActivityLogService` | User-scoped activity feed; 12 named `log*` helpers |
| `CacheService` | Centralised cache keys, 1-hour TTL, `warmUp()` |

### 6.1 Import subsystem (strategy pattern)

`ImportAdapterInterface` contract: `canHandle()`, `parse()`, `validate()`,
`getFormatName()`, `getSupportedExtensions()`, `getFieldMapping()`,
`getExpectedSchemaVersion()`.

| Class | Role |
|-------|------|
| `FormatDetector` | Ordered adapter registry; `detect()` returns format + confidence (`high`/`medium`) + alternatives. `detectByExtension()`. Helpers `isJson()`, `isCsv()` (delimiter-consistency check) |
| `JsonImportAdapter` | Primary. Handles `uma-run-tracker` exports. **59-entry field map** absorbs legacy aliases. Parses `growth_rates.*` dot-notation. Warns on `schema_version ≠ 1.0.0` |
| `CsvImportAdapter` | Secondary. Auto-detects delimiter (`,` `\t` `;`). **48-entry case-insensitive header map.** Positional fallback with warning. No schema versioning (`null`) |

**Only two adapters exist.** The PRD's "all 5 legacy formats" is not met (§12).

`ImportService` pipeline: `detectFormat()` → `preview()` → `validate()` (dry
run) → `import()`. The terminal step branches to `importToLocal()` (returns
UUID-keyed payloads for the client) or `importToAccount()` (DB transaction
with duplicate detection).

---

## 7. Integration Points

### 7.1 External systems

**There are none.** No third-party API, no game server, no CDN-backed service
beyond static font/icon CDNs, and — confirmed by dependency and code audit —
**no AI/LLM integration of any kind** (no `openai-php`, `anthropic-php`,
`laravel/ai`, `prism-php`, `langchain`, or any `config/ai.php`).

### 7.2 Inbound: file import

`POST` via `ImportWizard` (`WithFileUploads`) → `ImportService`. Adapters
detect, parse, validate. Two destinations: browser storage, or database.

### 7.3 Outbound: file export

**Pure PHP, zero dependencies.** No `maatwebsite/excel`, no `openspout`, no
`phpoffice/phpspreadsheet`.

| Format | Implementation |
|--------|----------------|
| JSON | `json_encode` over a normalised array |
| CSV | Hand-written writer: UTF-8 BOM (`\xEF\xBB\xBF`) + RFC-4180 quoting. Sectioned: Plan header → Attributes → Skills → Stat Progress |
| Markdown | String templating, GFM tables, **most recent 10 turns only** |

Every export root carries `schema_version: "1.0.0"` and `exported_at`.

`ExportController`: `exportPlan()`, `preview()` (size + content),
`exportBulk()`, `formats()`. `ProcessPlanExport` is a `ShouldQueue` job that
builds a separate legacy text format into `storage/app/public/exports/`.

**The PRD's Excel (.xlsx) export is not implemented** and no library exists to
implement it (§12).

---

## 8. Frontend Architecture

### 8.1 Rendering strategy

| Concern | Mechanism |
|---------|-----------|
| Server-stateful UI (forms, row add/remove, validation) | Livewire 3 |
| Micro-interactions (dropdown, hamburger, transitions) | Alpine 3 |
| Charts (growth curves) | Chart.js, CDN |
| Modals/confirmations | Livewire `Common\ConfirmModal` + legacy Bootstrap JS + SweetAlert2 |
| Toasts | Livewire `Common\Toast` (aria-live) + SweetAlert2 |
| File uploads | Livewire `WithFileUploads` |

`@livewireScriptConfig` is present in all three layouts with no arguments.
No `@viteReactRefresh`; no Vue/Inertia/React.

### 8.2 Build

`vite.config.js` inputs: `resources/css/app.css`, `resources/css/characters.css`,
`resources/js/app.js`. `refresh: true`, `hmr.overlay: false`. Dev proxy:
`/uploads` and `/api` → `http://127.0.0.1:8000`.

**Not registered:** `@tailwindcss/vite`. **No** `test` block, `resolve.alias`,
`server.port`, or `base`.

### 8.3 Accessibility

WCAG 2.1 AA is a stated requirement. Present: skip-to-main link, `#livewire-status`
aria-live region, `aria-live` toasts, focus-trapped `ConfirmModal`, 44px minimum
tap targets, `prefers-reduced-motion` and `:focus-visible` CSS rules, `data-testid`
hooks throughout the Tailwind layout, `x-cloak`.

Automated: `tests/playwright/accessibility.spec.js` + `.spec.ts` via
`@axe-core/playwright`. **Both a `.js` and a `.ts` accessibility spec exist
and overlap** — likely duplicated coverage. `docs/accessibility.md` (14.2 KB)
is the reference.

---

## 9. Test Architecture

### 9.1 Inventory

22 PHPUnit classes + 9 Playwright specs + 0 JS unit tests.

```
tests/
├── TestCase.php                    abstract, extends Illuminate BaseTestCase
├── Unit/            (4)            ExampleTest, LivewireComponentsTest,
│                                  Models/PlanRelationshipsTest, Enums/EnumCastsTest
├── Feature/         (18)
│   ├── Services/    (6)            Plan, Skill, StatProgress, Import, Export, UmaMusume
│   ├── Livewire/    (3)            SkillEditor, QuickCreatePlan, ImportWizard
│   ├── Feature/Livewire/ (3)       TrainingYear, SkillRow, CharacterList
│   └── Api/V1/      (5)            Plan, Skill, StatProgress, UmaMusume, Export Resources
└── playwright/      (9)
```

`phpunit.xml` defines two suites (`Unit` → `tests/Unit`,
`Feature` → `tests/Feature`), coverage source `app/`.

### 9.2 Two structural test problems

**The test database is hard-coded MySQL, not SQLite.** `phpunit.xml` pins
`DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`,
`DB_DATABASE=uma_musume_planner_test`, `DB_USERNAME=root`,
`DB_PASSWORD=""`. A live MySQL server with a pre-created database is a hard
prerequisite. This is why tests are slow, why CI is fragile, and why
contributors without a local MySQL cannot run the suite at all.

**`tests/Feature/Feature/Livewire/` has a duplicated `Feature` path segment**
(3 files). The namespace matches (`Tests\Feature\Feature\Livewire`) so it runs,
but it is an artifact of a malformed `make:test` invocation.

### 9.3 Coverage gaps

**Absent:** HTTP/route integration tests (no controller-level feature tests at
all — the 5 `Api/V1` tests assert *Resource* shape, not HTTP behaviour),
`PlanPolicy` tests, job tests, and any test hitting the `/characters`,
`/import`, `/local-data`, or `/guide` page routes.

**Two duplicate accessibility specs.** `accessibility.spec.js` and
`accessibility.spec.ts` target the same pages.

### 9.4 Commands

| Command | Effect |
|---------|--------|
| `composer test` | `config:clear` then `artisan test` |
| `php artisan test [file\|--filter=]` | Unit + Feature |
| `npm run playwright:test` | 9 E2E specs |
| `npm run test:a11y` | Accessibility spec only |
| `npm test` | **Jest — 0 tests collected** |
| `npm run test:vitest` | **Vitest, no config — 0 tests** |
| `npm run lighthouse-ci` | **Fails — missing config** |
| `vendor/bin/pint --dirty` | Format |
| `composer dev` | `artisan serve` + `queue:listen` + `pail` + `npm run dev` |

Playwright `baseURL` is `http://localhost/uma-musume-planner-laravel/public/`
(subdirectory deployment). No `webServer` block, no `retries`, no reporter
config, no sharding.

---

## 10. Build Integrity — CRITICAL

**The application does not currently build or boot.** `vendor/` and
`node_modules/` are both absent, so nothing below has been executed in this
working tree. These are static-analysis findings and are individually
sufficient to break the app.

### 10.1 Vite build fails — 8 missing JS modules

`resources/js/app.js` imports 8 files that do not exist. `resources/js`
contains only 7 files: `app.js`, `autosuggest.js`, `bootstrap.js`,
`characters.js`, `main.js`, `skill_management.js`, `utils.js`.

| Missing import | Line | Needed contract |
|----------------|------|-----------------|
| `./stores/index.js` | 13 | Must register Alpine stores `connection`, `preferences`, `toast`, `keyboard` — each with an optional `init()` |
| `./keyboard-shortcuts.js` | 16 | Global shortcut manager |
| `./services/index.js` | 19 | Must export **`localRunStorage`** and **`draftService`** |
| `./components/localDataManager.js` | 24 | Must export **`localDataManager`** |
| `./components/offlineDraftManager.js` | 27 | Must export **`offlineDraftManager`** |
| `./components/accountPlanEditor.js` | 30 | Must export **`accountPlanEditor`** |
| `./components/raceSnapshotQuota.js` | 33 | Must export **`raceSnapshotQuota`** |

`window.localRunStorage` is the **entire client-side Local-mode persistence
layer** — the store that the whole dual-storage feature depends on.

The four `init()` guards at `app.js:41-52` are correctly defensive
(`Alpine.store("x")?.init`), so they will not throw. But
`components/layout.blade.php:17-33` reads `$store.preferences.darkMode` in
its FOUC-guard script, and that store will be undefined.

### 10.2 Three routes point at a non-existent class

`routes/web.php:10` imports `App\Livewire\Plans\LocalPlanView`. Lines 23, 26,
29 bind it to `plans.local.show`, `plans.local.view`, `plans.local.edit`.

`app/Livewire/Plans/` contains only `SkillRow.php` and `TrainingYear.php`.
**All three Local-mode routes are dead.** This means the Local-mode view and
edit experience — a headline PRD feature (FR-1 through FR-10, and the entire
premium value proposition) — has no working server-rendered entry point.

`docs/D04_System_Design_Specifications.md:214-215` documents a
route→component table that asserts this component exists. The docs are wrong.

### 10.3 Four Blade components referenced but absent

`app/View/Components/` does not exist. `components/layout.blade.php` references:

- `x-connection-banner` (line 38)
- `x-toast-container` (line 230)
- `x-reconnection-prompt-modal` (line 233)
- `x-keyboard-shortcuts-help` (line 236)

`resources/views/components/` holds only 12 files: `button`, `alert`, `input`,
`common/modal`, `umamusume/storage-badge`, 5 under `partials/`, and the two
layouts.

### 10.4 Tailwind is inert

`@tailwindcss/vite` is a devDependency but is not in `vite.config.js`'s plugin
array, and `resources/css/app.css` has no `@import "tailwindcss"`. The 295-line
`tailwind.config.js` and its ~120-entry safelist produce nothing. Every page
using `components/layout.blade.php` — including `PlanDetailsPage` and
`LocalData\Manager`, two of the most important pages — renders unstyled.

### 10.5 Other broken tooling

| Target | Status |
|--------|--------|
| `npm run lighthouse-ci` | `.lighthouseci/lighthouserc.json` missing (exists only in the stale `.kilo/worktrees/torpid-teeth/` copy) |
| `npm test` (Jest) | `testRegex` points to `resources/assets/js/test/`, which does not exist in this tree (exists only in the worktree) |
| `npm run test:vitest` | No `vitest.config.*` |
| `fast-check` | Installed, zero usages |
| `vendor/bin/phpstan` | `phpstan.neon` exists at root; Larastan pinned. Not yet verified to run |
| `PlanSeeder::createSkill()` | Dead private method, never called |
| `app/Exceptions/ApiError.php` | Near-duplicate of `ApiException`, appears unused |
| `RunStatus`, `CareerYear` enums | Defined, cast on nothing |
| `resources/views/characters.blade.php`, `welcome.blade.php` | Orphaned — routes serve Livewire pages instead |
| `search_replace.php`, `debug-characters-page.png`, `test-results-a11y.txt` | Stray dev artifacts at repo root |

### 10.6 Uncommitted working tree

30+ modified files, uncommitted, including 5 core services
(`LocalRunStorageService`, `PlanService`, `SkillService`, `StatProgressService`),
2 models, and 5 of the 8 `docs/D0*` specifications. The last 12 commits are
broad `update`/`chore` sweeps with no incremental history.

---

## 11. Critical Technical Audit

### 11.1 What breaks first: authorization (CRITICAL)

`routes/web.php` has **no middleware at all** — no `auth`, no `guest`, no
throttle. `PlanDetailsPage::mount($planId)`
(`app/Livewire/Dashboard/PlanDetailsPage.php:108`) assigns the route parameter
and resolves the current route name, with **no `authorize()` call and no
ownership check**.

Consequence: `GET /plans/1/edit` renders another user's plan, and the
component's Livewire update actions persist changes to it. `PlanPolicy`
exists and correctly defines `update`/`delete` as owner-only
(`app/Policies/PlanPolicy.php`) — **but nothing ever calls it.**
`PlanList` imports `AuthorizesRequests` (lines 11, 19) and then never uses
`$this->authorize()`.

Compounding this, `Plan::booted()` (line 167-170) **silently assigns
`user_id = 1`** when empty. `user_id = 1` is also the designated *public*
user (per `PlanPolicy` and `LookupSeeder`), so anonymous writes and genuine
public data are indistinguishable.

This is a textbook IDOR across the application's primary object. It is also
why the PRD's "Per-user data isolation" non-functional requirement is currently
**false**.

Secondary exposure from the same root cause: `ActivityLogService` scopes
correctly by user, but `DashboardController::getActivities()` is explicitly
**not** user-scoped, so it leaks other users' activity over
`/api/v1/dashboard/activities`.

### 11.2 What breaks under load: the Local-mode payload

`LocalRunStorageService` flags a 5 MB quota threshold, but nothing enforces
it. Concretely, one fully-populated career run at the game's real maximum
(78 turns) is roughly:

- 78 turns × 5 stats × ~5 bytes ≈ 2 KB of stat data
- 78 turns of metadata (turn, stamina %, mood, conditions, energy) ≈ 6 KB
- Skills: ~30 entries × ~200 B ≈ 6 KB
- Race predictions + snapshots: ~20 × ~1 KB ≈ 20 KB
- Base plan + grades + goals ≈ 4 KB

→ **~40 KB per run**, or roughly **125 runs to exhaust 5 MB**. The quota
warning threshold will not fire until a user is ~1% from a hard
`QuotaExceededError`.

The failure mode is nasty: `localStorage.setItem` throws
`QuotaExceededError` **at write time**, deep inside a Livewire action, with no
transaction. A half-written multi-key update (e.g. bulk-converting 20 runs,
or the draft auto-save retaining 3 versions) leaves the store inconsistent —
and because the server holds no copy, **that data is unrecoverable**.

Multipliers that make this much worse:
- `calculateStorageSize()` computes size in PHP from a payload the *server
  cannot see*, so its value is only as fresh as the last sync.
- Draft auto-save keeps 3 versions → 3× the write volume per edit.
- `IndexedDB` is a documented Post-MVP item, so the ceiling is structural.

### 11.3 Edge cases the requirements overlook

**Turn/stage boundaries.** The game has hard structural limits the data model
does not encode: Junior year is 24 turns, Classic 25, Senior 30 (≈78 total);
you cannot enter a G1 race before Classic; Triple Crown races are
distance-locked; a trainee has finite aptitude-grader slots. `turns` has
`unique(plan_id, turn_number)` and a `1..78` range check in `Skill`, but
**nothing validates a turn against its career stage**, so a 30-turn Senior
year can be logged inside a Junior plan with no complaint.

**Stat cap semantics.** The 1200 cap is described as applying to *totals*,
but it is enforced on *per-turn raw values* (`StatProgressService`, 0-1200).
A single turn at 1200 is therefore legal and makes every derived
`getStatTotals()` meaningless. Nothing prevents it. There is also no
degradation when a stat is missed or falls — a real risk when users are
back-filling from screenshots.

**Race/snapshot coupling.** `career_snapshots.race_prediction_id` is
`nullable` with `onDelete('set null')`, so deleting a race prediction silently
orphans its snapshots. `SnapshotService::compareSnapshots()` has no defined
behaviour for snapshots with different turn counts or null SP.

**Timezones and "season" semantics.** A career is 3 in-game years. The schema
has no game-date, so ordering and "current season" are derived from turn
number and integer `month`/`time_of_day` string columns. Anything
date-like the user wants is unrepresentable.

**Import edge cases.** `JsonImportAdapter` warns on schema mismatch but
proceeds. `CsvImportAdapter`'s positional fallback means a CSV with
unrecognised headers will "succeed" while mapping columns wrongly — silent
data corruption, not a failure. Duplicate detection at 0.85 Levenshtein will
false-positive on two players' runs of the same character in the same
scenario (a *very* common case), and the only resolutions offered are
"create duplicate" or "cancel" — merge is unimplemented, so the user is
forced to choose between clutter and manual re-entry.

**Soft deletes.** `Plan` and `Skill` soft-delete, but nothing in the codebase
appears to purge them. `ClearOldLogs()` exists for `activity_log` only. With
FK cascades, a soft-deleted parent leaves soft-deleted children accumulating
forever.

**Concurrency.** No optimistic locking anywhere. Two tabs open on the same
account plan will last-write-win with no conflict detection. Draft
auto-save and Livewire updates can interleave against the same run.

**Empty/zero states.** No plan, no skills, no turns, no character selected,
character with no `growth_rates` populated — none of these have a specified
rendering, and `PlanFactory` not setting `storage_mode` means they are
under-tested by construction.

### 11.4 Over-engineered for the MVP

| Area | Assessment |
|------|-----------|
| **3 layouts** | Two Bootstrap and one Tailwind, plus an unreferenced fourth. Pure migration residue. Costs a FOUC guard, a navbar partial set, and an alert/modal system in two flavours. Collapsing to one is a large win |
| **API v1 surface** | 24 REST endpoints wrapping a Livewire app that does not not use them. The web UI talks Livewire + a handful of `fetch()` calls. Full CRUD, versioned Resources, Sanctum, throttle, and 5 Resource test classes for an unconsumed surface |
| **Queued `ProcessPlanExport`** | The only queued job, and it builds a *fourth* export format (legacy text) that nothing in the UI invokes. Requires a worker for no benefit |
| **Sanctum** | Installed and wired into the `api` group, but no token auth flow, no personal-access-token UI, no client |
| **`LivewireComponentsTest` + `PlanDetails` vs `PlanDetailsPage`** | Two components rendering the same page at two routes |
| **`Accessibility` tooling breadth** | Jest + Vitest + Playwright + axe + Lighthouse, of which three collect zero tests or fail on missing config |
| **Polymorphic `ActivityLog`** | `model_type`/`model_id` for a system that only ever logs `Plan` and `CareerSnapshot` |
| **Dead code** | `ApiError`, `RunStatus`, `CareerYear`, `PlanSeeder::createSkill()`, orphaned `SkillSeeder`, orphaned views, `fast-check` |
| **Event/Listener pair** | `PlanCreated`/`PlanUpdated` + `ClearPlanCache`, where `Plan::booted()` already does the logging inline — two mechanisms for one concern |

**Under-engineered, by contrast** — the real risk is not excess here:

- **No repository interface** behind dual storage (§5.2). This is the one
  place where an abstraction is genuinely warranted, and it is missing.
- **No optimistic locking** on a 78-turn log users edit across sessions.
- **No server-side enforcement** of the localStorage quota.
- **No factory** for `Turn` — the most-written entity in the product.

### 11.5 Documentation drift summary

`docs/` holds 17 documents (~360 KB) written as forward-looking specifications.
They have drifted from the code in at least these ways:

| Claim | Reality |
|-------|---------|
| `D04` route table lists `LocalPlanView` | Class does not exist (§10.2) |
| PRD: "500+ skills" autocomplete | **46** seeded (§4.5) |
| PRD: Excel `.xlsx` export | Not implemented; no library (§7.3) |
| PRD: "all 5 legacy formats" | **2** adapters: JSON, CSV (§6.1) |
| PRD: bilingual EN/JP | `name_jp` is `null` for all 46 seeded skills (§4.5) |
| CLAUDE.md / `.junie/guidelines.md`: Tailwind v4 is the styling system | Tailwind not wired (§10.4) |
| CLAUDE.md: "only create documentation files if explicitly requested" | Directly conflicts with the current task |
| `ui.instructions.md`: "Livewire tests live under `tests/Feature/Livewire`" | True for 3 files; 3 more are in `tests/Feature/Feature/Livewire` (§9.2) |

---

## 12. PRD Divergences

Corrections to [`PRD.md`](./PRD.md) that architecture ownership requires:

| # | PRD claim | As-built | Severity |
|---|-----------|----------|----------|
| 1 | Export includes Excel (`.xlsx`) (FR-7) | JSON, CSV, Markdown only | **High** — no dependency exists |
| 2 | Import supports all 5 legacy formats (FR-8) | 2 adapters (JSON, CSV) | **High** |
| 3 | 500+ skills for autocomplete (FR-4) | 46 seeded | **High** |
| 4 | Bilingual EN/JP skill search (FR-4) | `name_jp` null for all seeded rows | **High** |
| 5 | Per-user data isolation (NFR Security) | **No auth middleware; IDOR** | **Critical** |
| 6 | Local mode fully functional (FR-9) | Persistence layer missing; 3 routes dead | **Critical** |
| 7 | Tailwind v4 styling | Not wired | High |
| 8 | Two status vocabularies (enum vs column) | `RunStatus` unused | Medium |
| 9 | Character CRUD w/ image upload (FR-1) | Implemented server-side; UI path unstyled | Medium |
| 10 | "Test coverage 90%+" (Success Metrics) | 0 JS tests; no route/policy tests; suite needs live MySQL | High |

---

## 13. Recommended Sequencing

Not a plan — a dependency order. Items 1–2 are prerequisites for any further
feature work, because nothing can be built, run, or tested until they land.

| # | Action | Rationale |
|---|--------|-----------|
| 1 | `composer install` + `npm install` | `vendor/` and `node_modules/` absent; nothing is verifiable |
| 2 | Restore the 8 missing JS modules | Build is otherwise impossible; `localRunStorage` gates the whole Local feature |
| 3 | Create `App\Livewire\Plans\LocalPlanView` | 3 dead routes; Local mode is the premium tier |
| 4 | Add `auth` middleware + call `authorize()` in `PlanDetailsPage` | Closes the IDOR. **Do this before any user data exists** |
| 5 | Remove the `user_id = 1` silent default in `Plan::booted()` | Removes the anonymous/public conflation |
| 6 | Wire Tailwind (`@tailwindcss/vite` + `@import "tailwindcss"`) | Unblocks all styling work |
| 7 | Add the 4 missing Blade components | Required by the primary layout |
| 8 | Collapse 3 layouts → 1 | Largest maintainability win |
| 9 | Switch tests to SQLite `:memory:` | Unblocks CI and contributors |
| 10 | Add factories for `Turn`, `RacePrediction`, `CareerSnapshot`; fix `SkillFactory` | Closes the worst test gaps |
| 11 | Introduce a `RunRepository` interface | Makes dual storage safe to extend |
| 12 | Enforce the localStorage quota server-side; move Local mode to IndexedDB | Data-loss prevention |

---

## 14. Related Documents

| Document | Relationship |
|----------|--------------|
| [`PRD.md`](./PRD.md) | Product intent. §12 lists required corrections |
| [`ARCHITECTURE-ESSENTIALS.md`](./ARCHITECTURE-ESSENTIALS.md) | Condensed subset of this file |
| [`AGENTS.md`](./AGENTS.md) | Agent roles and working agreements |
| [`CLAUDE.md`](./CLAUDE.md) | Project-specific coding standards and commands |
| `.github/instructions/ui.instructions.md` | UI conventions (source for CLAUDE.md §UI) |
| `docs/D02`–`D04` | Original requirements and design specs — historical, drifted (§11.5) |
| `docs/D09_Database_Documentation.md` | Schema reference |
| `docs/accessibility.md` | WCAG 2.1 AA detail |
| `docs/LIVEWIRE_STANDARDIZATION_GUIDE.md` | Livewire conversion conventions |
