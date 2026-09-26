# AGENTS.md — Uma Musume Career Planner

**Version:** 1.0 · **Date:** 2026-09-26 · **Status:** Active

Defines roles, responsibilities, and standing instructions for AI agents and
automation working in this repository.

**Read first:** [`ARCHITECTURE-ESSENTIALS.md`](./ARCHITECTURE-ESSENTIALS.md)
(~2 min). Full detail in [`ARCHITECTURE.md`](./ARCHITECTURE.md). Coding
standards live in [`CLAUDE.md`](./CLAUDE.md).

---

## 0. Read this before you touch anything

### 0.1 The project is mid-migration and currently does not build

This is not a greenfield project. It is a working Laravel/Livewire application
partway through a migration from vanilla JS + Bootstrap to Livewire 3 +
Tailwind v4, and **the build is currently broken**. Before assuming a failure
is your fault, check whether it predates you:

| Pre-existing breakage | Detail |
|---|---|
| `vendor/`, `node_modules/` absent | Nothing is runnable. `composer install` + `npm install` first |
| 8 missing JS modules | `resources/js/app.js` imports them; Vite cannot resolve. Includes `services/index.js` → exports `localRunStorage`, the whole Local-mode store |
| `Plans\LocalPlanView` missing | Referenced by 3 routes in `routes/web.php`. All Local routes dead |
| 4 Blade components missing | `x-connection-banner`, `x-toast-container`, `x-reconnection-prompt-modal`, `x-keyboard-shortcuts-help` |
| Tailwind not wired | `@tailwindcss/vite` installed but absent from `vite.config.js`; `app.css` lacks `@import "tailwindcss"` |
| `npm test` / `test:vitest` / `lighthouse-ci` broken | Jest path missing, no Vitest config, no Lighthouse config |
| Test DB hard-coded to MySQL | Needs a live `uma_musume_planner_test` database |

**Rule:** if you cannot build, test, or run, say so explicitly rather than
guessing whether your change caused it.

### 0.2 Known critical security defect — do not extend it

`routes/web.php` has **no middleware**. `PlanDetailsPage::mount($planId)` does
**no** ownership check. `PlanPolicy` exists and is correct but is **never
called**. `Plan::booted()` silently forces `user_id = 1` when empty, and
`user_id = 1` is also the *public* user.

Result: any anonymous visitor can view and edit any account plan by ID.

**Rules:**
- Never add a route, controller, or Livewire action that reads or writes a
  `Plan` without an explicit `authorize()` call.
- Never add a new model action that relies on `user_id = 1` defaulting.
- Never surface this gap in a PR without flagging it as blocking.

### 0.3 Documentation drift is expected

`docs/` contains 17 forward-looking specification documents (~360 KB) written
before the code existed. They are **historical, not authoritative**, and
contradict the source tree in known ways. `ARCHITECTURE.md` was written from a
direct code audit and wins any conflict. Do not treat `docs/D02`–`D04` as
requirements, and do not "fix" code to match them.

### 0.4 Working tree is dirty

30+ uncommitted files, including 5 core services and 5 spec documents. Inspect
`git status` and `git diff` before editing. Stage only what you changed. Never
`git add -A` blindly. Never commit unless explicitly asked.

---

## 1. Roles

Each role lists mandate, scope, and the specific failure modes it guards
against. Most tasks are covered by one or two roles.

### 1.1 Architect

**Mandate:** protect the dual-storage boundary and keep the system's structural
decisions coherent.

Owns: `StorageMode` semantics, the Local↔Account contract, the run-key scheme,
schema versioning, layout consolidation, and the repository-interface
migration.

Reads: `ARCHITECTURE.md` §1, §5, §10, §11.2.

**Guards against:**
- Adding a write path that handles only one storage mode. Every mutation must
  branch on `isLocal()` or the data silently vanishes or duplicates.
- Letting the Local payload schema drift from `LocalRunStorageService`
  without bumping `SCHEMA_VERSION` and adding a `migrateSchema()` step.
- Adding a fourth layout.
- Treating a `localStorage` write as safe — it is neither transactional nor
  server-recoverable.

### 1.2 Backend Engineer

**Mandate:** domain correctness in models, services, and the import/export
pipeline.

Owns: `app/Models/**`, `app/Services/**`, `app/Services/Import/**`,
`app/Http/**`, `database/migrations/**`, `database/seeders/**`.

Guards against:
- Forgetting the dual-storage branch in a new service method.
- Skipping validation: stats 0–1200, `stamina_percentage` 0–100, turn range
  1–78, `turn_acquired` required iff `status = Acquired`.
- Assuming `skill_reference` is complete — it has **46** rows, not 500+.
  `name_jp` is `null` for every seeded row.
- Writing a migration that drops existing column attributes.
- Adding a second status vocabulary alongside `RunStatus`/`plans.status`.
- Reaching for `DB::` instead of Eloquent, or introducing an N+1.

### 1.3 Frontend / Livewire Engineer

**Mandate:** server-stateful UI in Livewire, micro-interactions in Alpine, and
removal of the legacy vanilla layer.

Owns: `app/Livewire/**`, `resources/views/livewire/**`,
`resources/views/components/**`, `resources/js/**`, `resources/css/**`.

Guards against:
- Writing new logic into `main.js` (1232 lines and must shrink, not grow).
- Using a Tailwind class on a page rendered through a **Bootstrap** layout, or
  vice-versa. Three layouts coexist; check which one the page uses.
- Forgetting a single root element, `wire:key` in loops, or a `data-testid`
  that Playwright depends on.
- Introducing a dependency on an Alpine store that does not exist. Only
  `connection`, `preferences`, `toast`, `keyboard` are expected — and all four
  are currently **missing** (§0.1).
- Reaching for `fetch()` against `/api/v1/*` instead of a Livewire round-trip.

### 1.4 Accessibility Specialist

**Mandate:** WCAG 2.1 AA is a contractual requirement, not a nice-to-have.

Owns: `docs/accessibility.md`, a11y specs, semantic structure, focus
management, ARIA.

Guards against:
- Regressing keyboard navigation or focus order.
- Losing the skip-to-main link, `#livewire-status` aria-live region, or
  `aria-live` toasts.
- Dropping below 44px tap targets or 4.5:1 contrast.
- Ignoring `prefers-reduced-motion` and `:focus-visible`.
- Breaking `x-cloak` and reintroducing a flash of unstyled content.

Verify: `npm run test:a11y`. Note there are two overlapping accessibility
specs (`.js` and `.ts`); do not add a third.

### 1.5 Data / Import-Export Engineer

**Mandate:** no silent data loss, no silent data corruption.

Owns: `ImportService`, `ExportService`, `ConvertLocalRunService`,
`DuplicateDetectionService`, `LocalRunStorageService`, the adapters.

Guards against:
- **Silent CSV corruption.** `CsvImportAdapter`'s positional fallback maps
  columns positionally when headers are unrecognised. It must warn loudly or
  refuse — never "succeed" with wrong mappings.
- Bumping `SCHEMA_VERSION` without a `migrateSchema()` path.
- Destructive import. Every import is a dry run first, then explicit
  execution.
- Forgetting that Markdown export is **capped at 10 turns** and that is
  intentional, not a bug.
- Assuming an Excel writer exists. It does not, and no library is installed.

### 1.6 Test Engineer

**Mandate:** every change is programmatically tested.

Owns: `tests/**`, `phpunit.xml`, `playwright.config.js`.

Guards against:
- Deleting or weakening an existing test to get to green. That needs explicit
  approval and a stated reason.
- Writing JS tests. There are none, and the runner collects zero. PHP or
  Playwright only.
- Assuming the suite runs without MySQL. It needs a live
  `uma_musume_planner_test` database.
- Adding a new test under `tests/Feature/Feature/` — that duplicated path
  segment is an existing bug, not a convention.

Test by layer: enum/relationship → Unit; service → `tests/Feature/Services`;
component → `tests/Feature/Livewire` via `Livewire::test()`; user flow →
Playwright.

### 1.7 Quality / Tooling Agent

**Mandate:** the toolchain actually runs.

Owns: `composer.json`, `package.json`, `vite.config.js`, `phpstan.neon`,
`.stylelintrc.json`, `.prettierrc`, CI workflows.

Guards against:
- Adding a dependency without approval.
- Adding a test framework entry that collects zero tests — there are already
  three (Jest, Vitest, Lighthouse CI).
- Leaving a config file referenced by a script but absent from the tree.
- Assuming `vendor/bin/phpstan` passes. Larastan is pinned at 3.6.1 and has
  not been verified against the current tree.

### 1.8 Documentation Steward

**Mandate:** docs describe the code, not the intention.

Owns: `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`, `PRD.md`, this file,
`CLAUDE.md`.

Guards against:
- Letting `docs/D02`–`D04` silently drift further.
- Creating documentation nobody asked for. The stock Laravel Boost guidance
  says so explicitly, and it is correct.
- Letting `ARCHITECTURE-ESSENTIALS.md` acquire unique facts. It is a strict
  subset of `ARCHITECTURE.md`; if you need to add something, add it to both.

---

## 2. Standing instructions

### 2.1 Before you start

1. Read `ARCHITECTURE-ESSENTIALS.md`.
2. Run `git status` — the tree is dirty by default.
3. Confirm `vendor/` and `node_modules/` exist. If not, `composer install` and
   `npm install`, and expect the pre-existing breakage in §0.1.
4. Locate the sibling file that already does something similar, and match it.
   Do not invent a fourth way.

### 2.2 While you work

- Match existing conventions over your own preferences. When in doubt, copy a
  sibling.
- Use `php artisan make:*` with `--no-interaction` to create files.
- Validation belongs in Form Requests, not controllers. Authorize in every
  Livewire action — a Livewire request is a real HTTP request.
- Prefer an Eloquent relationship over a raw query. Avoid `DB::`.
- Eager-load anything rendered in a loop.
- Prefer `wire:model.live` for live updates; `wire:model` is deferred in
  Livewire 3.
- State changes belong on the server. The UI reflects them.
- Do not add comments inside code. Use PHPDoc, including array shapes for
  non-obvious return arrays.

### 2.3 Before you finish

1. `vendor/bin/pint --dirty` — always. Never `--test`.
2. `php artisan test <the affected file>` — the minimum that proves the change.
3. `vendor/bin/phpstan analyse` for type-level changes.
4. `npm run test:a11y` for any UI change.
5. State what you verified, what you could not verify, and why.

### 2.4 Reporting

Be concise and specific. When something is blocked, say what is blocked and
what would unblock it. Do not claim a test passed that you did not run — the
suite currently requires a MySQL server that may not exist, and reporting an
unrun suite as green is worse than reporting nothing.

---

## 3. Domain rules

Non-obvious game logic that the schema does not encode. Violating these
produces data that looks valid and is wrong.

| Rule | Detail |
|------|--------|
| **Career length** | 78 turns total: Junior 24, Classic 25, Senior 30 |
| **Stat cap** | 1200 is a **total** cap. It is currently enforced on per-turn raw values — a known defect. Do not propagate the per-turn behaviour |
| **Stamina** | 0–100%. Threshold needs rise with distance and track type |
| **Turn uniqueness** | `unique(plan_id, turn_number)`. Never assume append-only ordering is safe |
| **Aptitude grades** | SS, S, A, B, C, D, E, F, G (`AptitudeGrade::value()` 1–9, effectiveness 40–120%) |
| **Distances** | Sprint, Mile, Medium, Long |
| **Styles** | Front Runner, Pace Chaser, Late Surger, End Closer |
| **Track** | Turf, Dirt |
| **Skills** | 3 states. `turn_acquired` is required **iff** `status = Acquired`, range 1–78 |
| **SP accounting** | Acquired and Suggested totals are tracked **separately** |
| **Skills catalog** | 46 seeded rows, `name_jp` null. Treat bilingual search as unproven |
| **Grade glyphs** | `RacePrediction` stat columns store `'○'` for "unset" — presentation state in the schema |

---

## 4. Quick reference

**Commands**

```bash
composer install && npm install     # required first — both currently absent
php artisan serve                   # dev server
composer dev                        # serve + queue:listen + pail + vite
npm run dev                         # vite only

vendor/bin/pint --dirty             # format (always)
vendor/bin/phpstan analyse          # static analysis (Larastan 3.6.1)
php artisan test                    # all PHPUnit (needs MySQL)
php artisan test tests/Feature/Services/PlanServiceTest.php
php artisan test --filter=testName
npm run playwright:test             # 9 E2E specs
npm run test:a11y                   # axe-core

npm test                            # BROKEN — 0 tests collected
npm run test:vitest                 # BROKEN — no config
npm run lighthouse-ci               # BROKEN — no config
```

**Key paths**

```
app/Models/Plan.php                       aggregate root
app/Services/LocalRunStorageService.php   Local schema authority + migration
app/Services/ConvertLocalRunService.php   Local -> Account
app/Services/Import/                      adapter strategy pattern
app/Livewire/Dashboard/PlanDetailsPage.php  MISSING AUTHORIZATION
routes/web.php                            no middleware, ever
resources/js/app.js                       8 broken imports
resources/views/components/layout.blade.php  Tailwind layout
resources/views/layouts/app.blade.php     Bootstrap layout
phpunit.xml                               hard-coded MySQL
```

---

## 5. Definition of done

- [ ] Change matches an existing sibling's conventions
- [ ] Every new write path handles **both** storage modes
- [ ] Every new read/write of `Plan` calls `authorize()`
- [ ] Validation in Form Requests; no inline controller validation
- [ ] Test added or updated, and **actually run**
- [ ] `vendor/bin/pint --dirty` run
- [ ] `npm run test:a11y` run if UI changed
- [ ] No new dead code, no new zero-test tooling
- [ ] Documentation updated if behaviour changed
- [ ] Blockers and unverified items reported honestly
