# ARCHITECTURE-ESSENTIALS.md

**Condensed reference for `ARCHITECTURE.md`.**
Every fact here appears in [`ARCHITECTURE.md`](./ARCHITECTURE.md). This file
adds nothing — it exists to get you oriented in ~2 minutes instead of ~25.
When the two ever disagree, `ARCHITECTURE.md` wins.

---

## 1. One-paragraph summary

Laravel 12 + Livewire 3 SPA-style app for planning *Uma Musume* career runs.
Its defining feature is **dual storage**: a run lives either in the browser's
`localStorage` (Local) or in MySQL (Account). The server holds *no* copy of a
Local run — it is a **stateless schema authority and computation engine** for
them. Business logic is in 13 services; UI is 33 Livewire components. There is
no third-party integration and no AI/LLM integration of any kind.

---

## 2. Stack (exact installed versions)

**Backend:** PHP 8.4.11 · Laravel **12.44.0** · Livewire **3.7.3** ·
Sanctum 4.2.1 · PHPUnit 11.5.46 · Pint 1.26.0 · Larastan 3.6.1 · Boost 1.8.7

**Frontend:** Vite 7.1.12 · Tailwind **4.1.11 (declared, NOT wired)** ·
Alpine 3.15.3 (inside Livewire's bundle) · SweetAlert2 11.23.0 ·
Playwright 1.56.1 · axe-core 4.11.0

**DB:** MySQL/MariaDB. 26 migrations (3 stock + 23 domain).
**Deploy:** subdirectory `/uma-musume-planner-laravel/public/`, not domain root.

**Also loaded:** Bootstrap 5.3.0-alpha1, bootstrap-icons, unpinned Chart.js,
Google Fonts — all via CDN, not Vite.

---

## 3. The 5 things that matter most

### 3.1 Dual storage is the whole design

`StorageMode` = `local` | `account`. Implemented as **`if ($plan->isLocal())`
branches inside services** — *not* a repository interface.

| Operation | Local | Account |
|-----------|-------|---------|
| Create run | Client persists payload from server | `INSERT` + default attributes |
| Log turn | Server returns array; client persists | Creates `Turn`, recalcs totals |
| Convert | — | `ConvertLocalRunService`, one transaction, preserves timestamps/order |

Conversion is **one-way** (Local → Account). ~40 KB per run; ~125 runs exhaust
a 5 MB `localStorage` quota. Nothing enforces that ceiling.

### 3.2 `Plan` is the aggregate root

One `Plan` = one career run. Fans out to 9 relations: `attributes`, `skills`,
`goals`, `racePredictions`, `turns`, `terrainGrades`, `distanceGrades`,
`styleGrades`, `careerSnapshots`. Belongs to `mood`/`condition`/`strategy`.
Soft-deleted, with activity logged on every create/update/delete.

Route identity is the run key: `account:<id>` → `/plans/{id}`;
`local:<uuid>` → `/plans/local/{uuid}`.

### 3.3 Nothing is authorized — CRITICAL

`routes/web.php` has **no middleware at all** (no `auth`, no `guest`).
`PlanDetailsPage::mount($planId)` does **no** ownership check. `PlanPolicy`
defines owner-only `update`/`delete` correctly but **is never called**.
`Plan::booted()` silently forces `user_id = 1` when empty — and `user_id = 1`
is also the *public* user.

→ Any anonymous visitor can read and modify any account plan by ID (IDOR).
Fix this before any real user data exists.

### 3.4 The app does not currently build

`vendor/` and `node_modules/` are absent. Beyond that, statically:

- **8 missing JS modules** imported by `resources/js/app.js` — including
  `services/index.js`, which must export `localRunStorage`, the entire
  client-side Local persistence layer.
- **`App\Livewire\Plans\LocalPlanView` does not exist** but 3 routes bind to
  it → all Local-mode routes are dead.
- **Tailwind is inert**: `@tailwindcss/vite` installed but not in
  `vite.config.js`, and `app.css` lacks `@import "tailwindcss"`. The 295-line
  `tailwind.config.js` produces nothing.
- **4 Blade components** referenced by the primary layout are absent
  (`x-connection-banner`, `x-toast-container`, `x-reconnection-prompt-modal`,
  `x-keyboard-shortcuts-help`).

### 3.5 Mid-migration from vanilla JS to Livewire

Three competing layouts (2 Bootstrap, 1 Tailwind, 1 unreferenced duplicate),
a 1232-line legacy `main.js` still driving the DOM alongside Livewire, and
CSS utilities in use for an MVP. Both styling systems are half-present.

---

## 4. Layer map

```
routes/web.php    (public, NO middleware, 14 routes)
   └─► app/Livewire/**       33 components  ──► app/Services/**  13 services
   └─► app/Policies/PlanPolicy                   (defined, UNUSED)
                        └─► app/Models/**  17 models ──► MySQL

routes/api.php    (/api/v1, Sanctum + throttle, 24 routes)
   └─► app/Http/Controllers/Api/V1/**  6 controllers
          └─► app/Http/Resources/Api/V1/**  6 resources
```

Supporting: `app/Jobs/ProcessPlanExport` (only queued job) ·
`app/Events/{PlanCreated,PlanUpdated}` · `app/Listeners/ClearPlanCache` ·
`app/Exceptions/{ApiException, ApiError(unused)}` · 9 string-backed enums.

**No custom base class for Livewire** — every component extends
`Livewire\Component` directly. Cross-cutting concerns are copy-pasted.

---

## 5. Data model (17 models)

| Model | Notes |
|-------|-------|
| `Plan` | Aggregate root. Soft-deleted. Silently defaults `user_id = 1` |
| `Turn` | Unique `(plan_id, turn_number)`. No factory exists |
| `Skill` | 3-state `status`; soft-deleted; `turn_acquired` required iff `Acquired` |
| `SkillReference` | Master catalog, 46 rows |
| `RacePrediction` | Stat columns are **strings defaulting to `'○'`** |
| `CareerSnapshot` | **Immutable** — `updating()` returns false |
| `ActivityLog` | Polymorphic; custom `timestamp` column, no `updated_at` |
| `Umamusume` | **String PK, no relations, 18 JSON columns** (`AsArrayObject`) |
| `Mood`/`Condition`/`Strategy` | Lookup dimensions |
| `Attribute`, `Goal`, `TerrainGrade`, `DistanceGrade`, `StyleGrade` | Child tables, `timestamps = false` |

**Convention deviations:** `Umamusume` is a document store, not a relational
model. `RacePrediction` persists presentation glyphs. Most child tables disable
timestamps, so recency ordering is impossible.

**Enums:** 9, all string-backed. `RunStatus` and `CareerYear` are **dead** —
`RunStatus` also duplicates the incompatible `plans.status` vocabulary.

**Factories:** 14 exist. **Missing** for `Turn`, `RacePrediction`,
`CareerSnapshot` (all declare `HasFactory` but can't be built).
`SkillFactory` sets a `rarity` attribute that is not a column.

---

## 6. Services

| Service | Role |
|---------|------|
| `PlanService` | Plan CRUD + dual-mode branching |
| `StatProgressService` | Turn logging (dual-mode), totals, chart data |
| `SkillService` | Cached catalog search, 3-state status, SP totals |
| `SnapshotService` | Immutable snapshots, `compareSnapshots()` |
| `ExportService` | Pure-PHP JSON/CSV/Markdown. **Zero dependencies** |
| `ImportService` | detect → preview → validate → import |
| `ConvertLocalRunService` | Local → Account, transactional |
| `DuplicateDetectionService` | exact title → name+date → **Levenshtein ≥ 0.85** |
| `LocalRunStorageService` | Local schema authority: validate, migrate, size, quota |
| `UmaMusumeService`, `ImageProcessingService`, `ActivityLogService`, `CacheService` | Supporting |

**Import = strategy pattern.** `ImportAdapterInterface` + `FormatDetector` +
`JsonImportAdapter` (59-entry field map) + `CsvImportAdapter` (48-entry header
map, delimiter auto-detect). **Only 2 adapters exist.**

---

## 7. Import / export

| Direction | Formats | Mechanism |
|-----------|---------|-----------|
| Import | JSON, CSV/TSV | `FormatDetector` → adapter → `ImportService` |
| Export | JSON, CSV (UTF-8 BOM + RFC-4180), Markdown | Hand-written PHP. **No Excel library** |

Every export root carries `schema_version: "1.0.0"`.
`LocalRunStorageService::migrateSchema()` handles v0→v1
(`total_sp` → `total_sp_available`, `stamina_pct` → `stamina_percentage`,
`turn` → `current_turn`).

---

## 8. Testing

- **22 PHPUnit** classes, **9 Playwright** specs, **0 JS tests**.
- **Test DB is hard-coded MySQL** (`uma_musume_planner_test`, root, no
  password) — a live server is a hard prerequisite. Should be SQLite `:memory:`.
- **No route/HTTP integration tests.** The 5 `Api/V1` tests assert *Resource*
  shape, not HTTP behaviour.
- **No policy tests. No job tests.**
- `tests/Feature/Feature/Livewire/` — duplicated path segment (3 files).
- `npm test` (Jest) → 0 tests. `npm run test:vitest` → no config, 0 tests.
  `npm run lighthouse-ci` → missing config, fails.

**Commands:** `php artisan test [file|--filter=]` · `vendor/bin/pint --dirty` ·
`npm run playwright:test` · `npm run test:a11y` · `composer dev`

---

## 9. Highest-risk areas

1. **Authorization / IDOR** (§3.3) — critical, unauthenticated data access.
2. **Local-mode data loss** — `localStorage` quota is unenforced; a
   `QuotaExceededError` mid-write corrupts the store, and the server has no
   copy. Mitigations absent: no quota check, no IndexedDB.
3. **Build integrity** (§3.4) — nothing is buildable or testable as-is.
4. **Unencoded state** — no optimistic locking. Two tabs = last-write-win.
5. **Domain validation gaps** — the 1200 stat cap is applied to *per-turn raw
   values* rather than totals, and turn numbers are never validated against
   career stage. Both make derived data meaningless while appearing valid.
6. **Import silent corruption** — the CSV adapter's positional fallback
   "succeeds" with wrong column mapping on unrecognised headers.
7. **Duplicate resolution is a dead end** — 0.85 Levenshtein false-positives on
   the common case (same character, same scenario, two players); only
   "create duplicate" or "cancel" are offered. Merge is unimplemented.
8. **Orphaned soft-deleted children** — nothing purges `Plan`/`Skill`.

---

## 10. Over-engineered for the MVP

Three layouts · a 24-endpoint REST API the Livewire UI doesn't consume ·
Sanctum with no token flow · the single queued job building an unused 4th
export format · polymorphic `ActivityLog` for 2 model types · duplicate
`.js`/`.ts` accessibility specs · four test frameworks of which three collect
zero tests · dead code (`ApiError`, `RunStatus`, `CareerYear`,
`PlanSeeder::createSkill()`, orphaned `SkillSeeder` + 2 views, `fast-check`).

**Meanwhile genuinely under-engineered:** no repository interface behind dual
storage, no optimistic locking, no server-side quota enforcement, no `Turn`
factory.

---

## 11. PRD divergences

| PRD claim | As-built |
|-----------|----------|
| Excel `.xlsx` export | Not implemented; no library |
| Import "all 5 legacy formats" | 2 adapters |
| 500+ skills | 46 seeded |
| Bilingual EN/JP search | `name_jp` null for all 46 |
| Per-user data isolation | **False** — no auth middleware |
| Local mode functional | Persistence missing; 3 routes dead |
| Tailwind v4 styling | Not wired |
| 90%+ coverage | 0 JS tests; no route/policy tests |

---

## 12. Fix-first order

1. `composer install` + `npm install` (both absent)
2. Restore the 8 missing JS modules
3. Create `Plans\LocalPlanView` (3 dead routes)
4. **Add `auth` middleware + call `authorize()`** — before any real user data
5. Remove the `user_id = 1` silent default
6. Wire Tailwind
7. Add the 4 missing Blade components
8. Collapse 3 layouts → 1
9. Tests → SQLite `:memory:`
10. Add missing factories; fix `SkillFactory`
11. Introduce a `RunRepository` interface
12. Enforce the Local-mode quota server-side
