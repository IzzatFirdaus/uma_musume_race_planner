# D03 - System Requirements Specifications

## Uma Musume Career Planner

**Document Version:** 3.0
**Date:** 2026-07-03
**Status:** Active
**Last Updated:** 2026-07-03

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Requirements Overview](#2-requirements-overview)
3. [Use Cases](#3-use-cases)
4. [Error Handling](#4-error-handling)
5. [Functional Requirements](#5-functional-requirements)
6. [Non-Functional Requirements](#6-non-functional-requirements)
7. [Interface Requirements](#7-interface-requirements)
8. [Data Requirements](#8-data-requirements)
9. [System Constraints](#9-system-constraints)
10. [Traceability Matrix](#10-traceability-matrix)
11. [Appendices](#11-appendices)

---

## 1. Introduction

### 1.1 Purpose

This document defines the system requirements for the Uma Musume Career Planner. It is derived directly from the BRS (D02) and translates business needs into specific, testable system behavior.

All requirements in this SRS trace back to business requirements, and any implementation work should preserve that traceability through to the SDP.

### 1.2 Scope

This specification covers:

- Functional requirements for the application modules.
- Non-functional requirements for performance, security, accessibility, and maintainability.
- Interface requirements for UI and API surfaces.
- Data requirements, including validation, retention, and canonical field definitions.

### 1.3 References

- BRS: [D02_Business_Requirements_Specifications.md](D02_Business_Requirements_Specifications.md)
- BRS Glossary of Game Terms: [D02_Business_Requirements_Specifications.md#11-1-glossary-of-game-terms](D02_Business_Requirements_Specifications.md#11-1-glossary-of-game-terms)
- SDP: [D01_System_Development_Plan.md](D01_System_Development_Plan.md)

### 1.4 Requirement Priorities

| Priority | Description |
| --- | --- |
| P0 | Core functionality required for the MVP release. |
| P1 | Important functionality required for a complete user experience. |
| P2 | Future or advanced functionality that may be implemented later. |

---

## 2. Requirements Overview

| Module | Requirement IDs | Priority | BRS Traceability | SDP Phase |
| --- | --- | --- | --- | --- |
| Dashboard | REQ-DASH-1, REQ-DASH-2 | P0 | BR-1.1, BR-1.2, BR-1.3 | Phase 3: Livewire Components / Phase 5: UI / UX Polish |
| Plan Creation and Management | REQ-PLAN-1, REQ-PLAN-2, REQ-PLAN-3, REQ-PLAN-4 | P0 / P2 | BR-2.1-2.5 | Phase 2: Service Layer / Phase 3: Livewire Components |
| Character Management | REQ-CHAR-1 [legacy review ref: REQ-75] | P1 | BR-1.1-1.4 | Phase 2: Service Layer / Phase 3: Livewire Components |
| Skills | REQ-SKILL-1 | P0 | BR-4.1-4.5 | Phase 2: Service Layer / Phase 3: Livewire Components |
| Stats and Turn Tracking | REQ-STAT-1, REQ-TURN-1 | P0 | BR-3.1-3.4 | Phase 3: Livewire Components |
| Aptitude Grades | REQ-APT-1 | P0 | BR-1.3-1.4 | Phase 3: Livewire Components |
| Race Planning and Snapshots | REQ-RACE-1, REQ-RACE-2 [legacy review ref: REQ-76] | P1 / P2 | BR-5.1-5.4 | Phase 3: Livewire Components / Phase 5: UI / UX Polish |
| Goals | REQ-GOAL-1, REQ-GOAL-2 | P1 / P2 | BR-6.1-6.3 | Phase 3: Livewire Components / Phase 5: UI / UX Polish |
| Theme and Accessibility | REQ-THEME-1 | P0 | BR-10.1-10.5 | Phase 5: UI / UX Polish |
| Export | REQ-EXP-1, REQ-EXP-2 | P0 / P1 | BR-7.1-7.7 | Phase 2: Service Layer / Phase 6: API Layer |
| Import | REQ-IMP-1 | P1 | BR-8.1-8.5, BR-12.1 | Phase 2: Service Layer / Phase 6: API Layer |
| Storage Modes and Local Data | REQ-STOR-1, REQ-STOR-2 | P0 / P1 | BR-9.1-9.6 | Phase 2: Service Layer / Phase 3: Livewire Components / Phase 5: UI / UX Polish |
| Draft Auto-Save | REQ-DRAFT-1 | P0 | BR-9.4, BR-11.2 | Phase 2: Service Layer / Phase 3: Livewire Components |
| Connection State | REQ-CONN-1 | P0 / P2 | BR-9.4, BR-11.1-11.3 | Phase 2: Service Layer / Phase 3: Livewire Components |
| Authentication | REQ-AUTH-1 [legacy review ref: REQ-80] | P0 | BR-11.1-11.3 | Phase 2: Service Layer / Phase 3: Livewire Components |

---

## 3. Use Cases

- A user opens the dashboard, reviews recent activity, checks local storage usage, and starts a new plan.
- A user creates a plan by selecting or quick-creating a character, choosing a storage mode, and saving the initial run.
- A user edits stats, skills, race predictions, goals, and turn-by-turn progress while staying in a single plan context.
- A user exports an individual plan or all plans for backup, sharing, or analysis.
- A user imports a backup file or a legacy tracker export, previews the result, and resolves duplicates before importing.
- A user signs in to move from Local mode to Account mode and optionally convert existing Local plans.

---

## 4. Error Handling

| Condition | System Response | User Feedback |
| --- | --- | --- |
| Invalid form input | Reject the request, preserve entered values, and surface field-level validation errors. | Inline error messages and summary feedback when needed. |
| Network failure or offline state | Pause Account-mode writes, preserve drafts locally, and continue Local-mode work where possible. | Offline indicator icon, banner, and retry option. |
| Storage quota exceeded | Stop the save operation and prompt the user to clear space or export data. | Quota warning with usage details and next-step guidance. |
| Import parsing failure | Reject the file and do not partially import invalid data. | Import error summary with the failing file type or record range. |
| Export generation failure | Abort the download and log the failure. | Error toast with a retry path. |
| Concurrent Account edit conflict | Mark the conflict, prevent silent overwrite, and defer automatic resolution to the future workflow. | Conflict warning with reload or discard options. |

---

## 5. Functional Requirements

### 5.1 Dashboard [REQ-DASH-1, REQ-DASH-2] - P0

**Description:** The dashboard is the main landing page for plan discovery, status awareness, and quick actions.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-DASH-1.1 | Display app branding, summary counters, and the primary create action. | Header branding, counts, and create button are visible on load. |
| REQ-DASH-1.2 | Display aggregate plan counts by status. | Total, active, and completed counts are correct. |
| REQ-DASH-1.3 | Display all plans with title, character, status, and storage mode. | Plan cards or rows render the existing dataset. |
| REQ-DASH-2.1 | Display recent activity as the last 10 recorded actions in reverse chronological order. | The activity list contains at most 10 entries and the newest entry appears first. |
| REQ-DASH-2.2 | Display localStorage quota usage and remaining capacity. | A usage indicator or progress bar shows used and available space. |
| REQ-DASH-2.3 | Adapt to a single-column mobile layout below the mobile breakpoint. | Layout remains usable below 768px. |

### 5.2 Plan Creation and Management [REQ-PLAN-1, REQ-PLAN-2, REQ-PLAN-3, REQ-PLAN-4] - P0 / P2

**Description:** The system must support fast plan creation, list management, and read-only / editable plan views.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-PLAN-1.1 | Open a quick-create modal from the dashboard or plan list. | The modal opens without navigation away from the current page. |
| REQ-PLAN-1.2 | Require a title, character selection, and storage mode at creation time. | These inputs are visible and validated before save. |
| REQ-PLAN-1.3 | Support quick-creating or selecting a character from the modal. | The character field allows lookup and a create-new path. |
| REQ-PLAN-1.4 | Apply default values on initial open. | Storage Mode defaults to Local and Career Stage defaults to Junior unless the user changes them. |
| REQ-PLAN-1.5 | Redirect to the correct plan route after creation. | Local plans open at UUID-based routes and Account plans open at ID-based routes. |
| REQ-PLAN-2.1 | Provide plan list filtering and sorting by status or character. | The user can narrow the list using status or character controls. |
| REQ-PLAN-2.2 | Expand inline details for a selected plan row or card. | The details panel opens without breaking list navigation. |
| REQ-PLAN-2.3 | Allow view, edit, and delete actions from the list. | Each action resolves to the correct route or confirmation flow. |
| REQ-PLAN-3.1 | Display the read-only plan view at the correct route. | `/plans/{id}` or `/plans/local/{uuid}` renders plan details. |
| REQ-PLAN-3.2 | Display the plan title, character, career stage, mood, conditions, and notes. | All fields are visible in the read-only view. |
| REQ-PLAN-3.3 | Display stats using bars with a hard max of 1200 and a max visual width of 100 percent. | Bars clamp at the cap and use consistent stat colors. |
| REQ-PLAN-3.4 | Display the skills table with SP cost, tier, status, and acquisition turn. | The table shows the current skill data in a readable format. |
| REQ-PLAN-3.5 | Provide navigation back to the dashboard. | The back link returns to the dashboard without losing state. |
| REQ-PLAN-4.1 | Display the editable plan view at the correct route. | `/plans/{id}/edit` or `/plans/local/{uuid}/edit` renders editable controls. |
| REQ-PLAN-4.2 | Preserve state when switching tabs. | Tab changes do not clear user input. |
| REQ-PLAN-4.3 | Warn before navigating away from dirty changes. | A dirty-state prompt appears when unsaved changes exist. |
| REQ-PLAN-4.4 | Validate all fields before save. | Invalid fields show inline errors and the save is blocked until corrected. |
| REQ-PLAN-4.5 | Treat concurrent Account-mode editing conflicts as future work. | Conflicts are detected and surfaced, but automatic collaborative resolution is P2/Future. |

### 5.3 Character Management [REQ-CHAR-1 / legacy review ref: REQ-75] - P1

**Description:** Users must be able to manage the character roster used by career plans.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-CHAR-1.1 | Support character CRUD operations. | The user can create, view, edit, and delete character records. |
| REQ-CHAR-1.2 | Support character image upload. | JPEG, PNG, and WebP uploads up to 2 MB are accepted. |
| REQ-CHAR-1.3 | Store images appropriately by mode. | Account mode stores files in server-backed storage; Local mode stores only the image path or URL reference. |
| REQ-CHAR-1.4 | Display aptitude grades and growth rate bonuses for the character. | Terrain, distance, style, and growth data are visible. |

### 5.4 Skill Management [REQ-SKILL-1] - P0

**Description:** Skill management must support bilingual search, skill status tracking, and SP calculations.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-SKILL-1.1 | Search skills using English and Japanese names in autocomplete. | Lookup results match either language. |
| REQ-SKILL-1.2 | Support Acquired, Skipped, and Suggested statuses. | All three states are selectable in the UI. |
| REQ-SKILL-1.3 | Require turn_acquired when status is Acquired. | Validation fails when Acquired has no turn. |
| REQ-SKILL-1.4 | Calculate SP totals separately for Acquired and Suggested skills. | Acquired SP total equals the sum of `sp_cost` for Acquired skills; Suggested SP total equals the sum of `sp_cost` for Suggested skills. |
| REQ-SKILL-1.5 | Display skill rows with delete and edit actions. | The skill table supports row-level management. |

### 5.5 Stat Tracking [REQ-STAT-1] - P0

**Description:** The system must track raw stats, growth bonuses, totals, and visual status.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-STAT-1.1 | Display all five stats with current values. | Speed, Stamina, Power, Guts, and Wit are visible. |
| REQ-STAT-1.2 | Display growth-rate bonuses alongside the stats. | Percentage bonuses appear next to the relevant stat. |
| REQ-STAT-1.3 | Enforce a raw stat range of 0 to 1200. | Values outside the range are rejected. |
| REQ-STAT-1.4 | Apply consistent stat color mapping. | Speed is red, Stamina is green, Power is blue, Guts is amber, and Wit is purple. |
| REQ-STAT-1.5 | Calculate and display the total stat sum. | The total updates when any stat changes. |

### 5.6 Aptitude Grades [REQ-APT-1] - P0

**Description:** Aptitudes must be readable, comparable, and aligned with the game terminology.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-APT-1.1 | Display terrain, distance, and style grades. | Turf, Dirt, Sprint, Mile, Medium, Long, Nige, Senkou, Sashi, and Oikomi are visible. |
| REQ-APT-1.2 | Display grade percentages alongside letter grades. | Each grade shows the letter and the numeric percentage or equivalent effectiveness label. |
| REQ-APT-1.3 | Use game-consistent grade colors. | The grade palette remains visually distinct and stable. |

### 5.7 Race Planning and Snapshots [REQ-RACE-1, REQ-RACE-2 / legacy review ref: REQ-76] - P1 / P2

**Description:** Race planning should help users prepare for events, while race snapshots preserve race-day state for later review.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-RACE-1.1 | Record race prediction entries. | Race rows render with the current schedule data. |
| REQ-RACE-1.2 | Capture race details such as name, venue, distance, and track. | All fields are editable. |
| REQ-RACE-1.3 | Display recommended stamina thresholds by distance and track. | Threshold guidance is visible in the UI. |
| REQ-RACE-2.1 | Capture race-day snapshots of the plan state. | A snapshot can be created, viewed, and removed. |
| REQ-RACE-2.2 | Store the snapshot with relevant race context. | The snapshot includes the race reference, stats, mood, conditions, and notes. |

### 5.8 Goals [REQ-GOAL-1, REQ-GOAL-2] - P1 / P2

**Description:** Goals allow users to track training objectives across the career run.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-GOAL-1.1 | Create, edit, complete, and delete goals. | Goal rows support full CRUD behavior. |
| REQ-GOAL-1.2 | Visually distinguish completed goals. | Completed goals use a different style. |
| REQ-GOAL-2.1 | Support a goal completion date field as future work. | The field may be displayed or reserved, but implementation is P2/Future. |

### 5.9 Turn Tracking [REQ-TURN-1] - P0

**Description:** The system must support turn-by-turn stat entry and derived totals.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-TURN-1.1 | Display turn entries grouped by career year. | The list remains organized by year. |
| REQ-TURN-1.2 | Add turns using the next sequential number. | New turns append in order. |
| REQ-TURN-1.3 | Allow editing of all five stats per turn. | All stat fields remain editable. |
| REQ-TURN-1.4 | Auto-calculate total stats per turn. | Each turn row shows the computed total. |
| REQ-TURN-1.5 | Highlight milestone turns. | Milestone rows are visually distinct. |

### 5.10 Dark Mode and Accessibility [REQ-THEME-1] - P0

**Description:** The interface must respect theme preferences and accessibility expectations.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-THEME-1.1 | Provide a visible dark mode toggle. | The toggle is available in the navbar. |
| REQ-THEME-1.2 | Detect system preference on first load. | The initial theme follows the OS preference when no saved preference exists. |
| REQ-THEME-1.3 | Persist the user preference. | The chosen theme is restored on return. |
| REQ-THEME-1.4 | Respect accessibility contrast requirements. | Dark mode and light mode both meet WCAG AA contrast expectations. |

### 5.11 Data Export [REQ-EXP-1, REQ-EXP-2] - P0 / P1

**Description:** Users must be able to export a single plan or all plans in multiple formats.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-EXP-1.1 | Export the current plan in JSON, Excel, CSV, and Markdown-compatible formats. | The selected format downloads successfully. |
| REQ-EXP-1.2 | Include the schema_version in every export. | The version field is present in the exported payload. |
| REQ-EXP-1.3 | Offer a copy-to-clipboard path where supported. | The clipboard action is visible and works. |
| REQ-EXP-2.1 | Provide a bulk global export feature for all plans. | The user can export all plans in one action. |
| REQ-EXP-2.2 | Generate a downloadable file for bulk export. | The browser download is triggered with the selected format. |

### 5.12 Import Wizard [REQ-IMP-1] - P1

**Description:** The import flow must support modern backups and legacy tracker migration.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-IMP-1.1 | Detect file format and schema version. | The system identifies the imported format before processing. |
| REQ-IMP-1.2 | Detect the five legacy app formats used in project history. | The wizard recognizes uma-run-tracker, umamusume-tracker, uma-tracker, uma_musume_race_planner, and uma-tracker-form exports. |
| REQ-IMP-1.3 | Apply schema migration when needed. | Older imports are migrated to the current schema. |
| REQ-IMP-1.4 | Handle large files with chunking or streaming. | Large files are processed without exhausting browser memory. |
| REQ-IMP-1.5 | Preview importable plans and duplicates before execution. | The preview shows what will be imported and what conflicts exist. |
| REQ-IMP-1.6 | Support import targets for Local and Account storage. | The user can choose the destination before importing. |

### 5.13 Storage Modes and Local Data [REQ-STOR-1, REQ-STOR-2] - P0 / P1

**Description:** The application must support Local mode, Account mode, and local data administration.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-STOR-1.1 | Support Local and Account storage modes. | Both modes function as defined. |
| REQ-STOR-1.2 | Use UUID routes for Local plans. | Local plans resolve through `/plans/local/{uuid}` routes. |
| REQ-STOR-1.3 | Use database IDs for Account plans. | Account plans resolve through `/plans/{id}` routes. |
| REQ-STOR-1.4 | Show an active storage-mode badge. | The UI clearly indicates Local or Account mode. |
| REQ-STOR-1.5 | Display localStorage usage as a progress bar. | Local storage usage is visible and percentage-based. |
| REQ-STOR-1.6 | Provide backup and restore actions for Local data. | The user can export a backup and restore it later. |
| REQ-STOR-2.1 | Provide a local data management interface. | The dashboard or navigation exposes the feature. |
| REQ-STOR-2.2 | List Local runs with storage information. | The list includes size or usage details. |
| REQ-STOR-2.3 | Support bulk export and selective delete. | The user can export all, select individual rows, and purge selected data. |
| REQ-STOR-2.4 | Convert selected or all Local runs to Account mode when authenticated. | Bulk conversion succeeds or reports conflicts. |

### 5.14 Draft Auto-Save [REQ-DRAFT-1] - P0

**Description:** Unsaved work must be preserved automatically.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-DRAFT-1.1 | Auto-save drafts to localStorage on a timed interval. | Drafts are saved without user intervention. |
| REQ-DRAFT-1.2 | Keep the last 3 draft versions with timestamps. | The restore UI can present a 3-version timeline. |
| REQ-DRAFT-1.3 | Offer restore and discard actions. | The user can recover or discard a draft. |
| REQ-DRAFT-1.4 | Clear drafts after a successful save. | Saved plans no longer leave stale draft entries behind. |

### 5.15 Connection State [REQ-CONN-1] - P0 / P2

**Description:** The UI must handle Livewire connection issues gracefully.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-CONN-1.1 | Display an offline indicator icon and status banner. | Connection loss is immediately visible. |
| REQ-CONN-1.2 | Retry transient failures automatically. | The system retries according to the configured retry policy. |
| REQ-CONN-1.3 | Preserve drafts during disconnects. | Draft state is not lost during a temporary outage. |
| REQ-CONN-1.4 | Queue offline changes for Account runs as future work. | Offline write queueing for Account mode is explicitly treated as P2/Future. |
| REQ-CONN-1.5 | Provide a recovery path after reconnection. | The UI prompts the user to retry, refresh, or continue. |

### 5.16 Authentication [REQ-AUTH-1 / legacy review ref: REQ-80] - P0

**Description:** Account mode requires authentication, while Local mode remains usable without sign-in.

| ID | Requirement | Acceptance Criteria |
| --- | --- | --- |
| REQ-AUTH-1.1 | Support sign-up and sign-in. | The user can create and access an account. |
| REQ-AUTH-1.2 | Support account management actions. | Password and profile management are available. |
| REQ-AUTH-1.3 | Require authentication for Account storage mode. | Account-mode actions are blocked until the user is signed in. |
| REQ-AUTH-1.4 | Keep Local mode fully usable without authentication. | Local mode remains available to anonymous users. |

---

## 6. Non-Functional Requirements

### 6.1 Performance [NFR-1]

| ID | Requirement | Target |
| --- | --- | --- |
| NFR-1.1 | Page load time on primary dashboard and plan views. | Under 2 seconds on the measurement profile. |
| NFR-1.2 | First Contentful Paint. | Under 1.5 seconds. |
| NFR-1.3 | Time to Interactive. | Under 3 seconds. |
| NFR-1.4 | Largest Contentful Paint. | Under 2.5 seconds. |
| NFR-1.5 | Skill autocomplete response. | Under 200 ms on typical datasets. |
| NFR-1.6 | Export of large datasets. | 50k rows completes within 60 seconds. |

#### 6.1.1 Measurement and Validation

- Measure in Google Chrome using Lighthouse in mobile emulation.
- Use Moto G4 emulation with Fast 3G throttling and CPU throttling enabled.
- Run three passes and record the median result.
- Validate the dashboard, plan edit screen, export flow, and import preview as representative user paths.

### 6.2 Security [NFR-2]

| ID | Requirement |
| --- | --- |
| NFR-2.1 | CSRF protection is required for all form submissions. |
| NFR-2.2 | Input must be validated and sanitized before persistence or display. |
| NFR-2.3 | Account-mode traffic must be served over HTTPS. |
| NFR-2.4 | Account-mode data at rest must be encrypted according to platform and storage controls. |
| NFR-2.5 | CORS must be configured explicitly if cross-origin API access is enabled. |
| NFR-2.6 | Password policy enforcement must require a strong minimum length and reject obviously weak passwords. |
| NFR-2.7 | Per-user isolation must be enforced for Account-mode data. |

### 6.3 Accessibility [NFR-3]

| ID | Requirement | WCAG Reference |
| --- | --- | --- |
| NFR-3.1 | Provide alt text or equivalent text alternatives where needed. | 1.1.1 |
| NFR-3.2 | Keep color information available through more than color alone. | 1.4.1 |
| NFR-3.3 | Provide a skip-to-main link. | 2.4.1 |
| NFR-3.4 | Use semantic HTML landmarks and ARIA landmarks where appropriate. | 4.1.2 |
| NFR-3.5 | Maintain a logical focus and tab order across the interface. | 2.4.3 / 2.4.7 |
| NFR-3.6 | Maintain 4.5:1 contrast for normal text. | 1.4.3 |
| NFR-3.7 | Support 400 percent zoom without breaking layout. | 1.4.10 |
| NFR-3.8 | Preserve keyboard access for all interactive elements. | 2.1.1 |
| NFR-3.9 | Trap focus inside modal dialogs. | 2.4.3 |
| NFR-3.10 | Respect reduced-motion preferences. | 2.3.3 |

### 6.4 Compatibility [NFR-4]

| ID | Requirement |
| --- | --- |
| NFR-4.1 | Support PHP 8.2 and later runtime environments used by the project. |
| NFR-4.2 | Support MySQL, MariaDB, and SQLite database backends. |
| NFR-4.3 | Support recent Chrome, Firefox, Safari, and Edge releases. |
| NFR-4.4 | Support iOS Safari and Chrome Android. |

### 6.5 Maintainability [NFR-5]

| ID | Requirement |
| --- | --- |
| NFR-5.1 | Follow PSR-12 coding standards and project formatting conventions. |
| NFR-5.2 | Maintain automated error logging and user action logging for important flows. |
| NFR-5.3 | Keep public APIs documented and versioned where exposed. |
| NFR-5.4 | Keep the CI/CD pipeline responsible for validation, linting, and test execution. |
| NFR-5.5 | Apply consistent `data-testid` naming for interactive elements. |

### 6.6 Responsive Design [NFR-6]

| ID | Requirement | Breakpoint / Constraint |
| --- | --- | --- |
| NFR-6.1 | Support mobile layouts. | Below 768px |
| NFR-6.2 | Support landscape and portrait orientations without layout breakage. | All supported mobile and tablet sizes |
| NFR-6.3 | Keep touch targets at least 44px wide/tall. | All mobile surfaces |
| NFR-6.4 | Remain usable from 320px to 2560px viewport widths. | Full supported range |

---

## 7. Interface Requirements

### 7.1 UI Components

| Component | Existing Reference | Purpose |
| --- | --- | --- |
| Navbar | [app/Livewire/Layout/Navbar.php](app/Livewire/Layout/Navbar.php) | Primary navigation, branding, dark mode access, and account actions. |
| Header Banner | [app/Livewire/Dashboard/HeaderBanner.php](app/Livewire/Dashboard/HeaderBanner.php) | Dashboard hero and overview entry point. |
| Stats Panel | [app/Livewire/Dashboard/StatsPanel.php](app/Livewire/Dashboard/StatsPanel.php) | Aggregate plan counters and summary metrics. |
| Recent Activity | [app/Livewire/Dashboard/RecentActivity.php](app/Livewire/Dashboard/RecentActivity.php) | Last 10 actions and activity history. |
| Plan List | [app/Livewire/Dashboard/PlanList.php](app/Livewire/Dashboard/PlanList.php) | List, filter, sort, and act on plans. |
| Plan Inline Details | [app/Livewire/Dashboard/PlanInlineDetails.php](app/Livewire/Dashboard/PlanInlineDetails.php) | Expandable inline plan details. |
| Quick Create Plan | [app/Livewire/QuickCreatePlan.php](app/Livewire/QuickCreatePlan.php) | Fast plan creation modal. |
| Plan Details | [app/Livewire/PlanDetails.php](app/Livewire/PlanDetails.php) | Read-only and editable plan content. |
| Skill Search | [app/Livewire/Skills/SkillSearch.php](app/Livewire/Skills/SkillSearch.php) | Skill autocomplete and lookup. |
| Skill Editor | [app/Livewire/Skills/SkillEditor.php](app/Livewire/Skills/SkillEditor.php) | Skill row editing and status updates. |
| Character List | [app/Livewire/Characters/CharacterList.php](app/Livewire/Characters/CharacterList.php) | Character CRUD and image handling. |
| Local Data Manager | [app/Livewire/LocalData/Manager.php](app/Livewire/LocalData/Manager.php) | Local backup, restore, export, and cleanup. |
| Storage Mode Indicator | [app/Livewire/CareerRun/StorageModeIndicator.php](app/Livewire/CareerRun/StorageModeIndicator.php) | Visual local/account mode badge. |
| Dark Mode Toggle | [app/Livewire/Common/DarkModeToggle.php](app/Livewire/Common/DarkModeToggle.php) | Theme switching and persistence. |
| Dirty State Warning | [app/Livewire/Common/DirtyStateWarning.php](app/Livewire/Common/DirtyStateWarning.php) | Unsaved change protection. |
| Confirm Modal | [app/Livewire/Common/ConfirmModal.php](app/Livewire/Common/ConfirmModal.php) | Deletion and destructive-action confirmation. |
| Toast | [app/Livewire/Common/Toast.php](app/Livewire/Common/Toast.php) | Success and error notifications. |
| Export Modal | [app/Livewire/Export/ExportModal.php](app/Livewire/Export/ExportModal.php) | Export format selection and download flow. |
| Convert Run Modal | [app/Livewire/Auth/ConvertRunModal.php](app/Livewire/Auth/ConvertRunModal.php) | Claim-plans flow for Local to Account conversion. |

### 7.2 API Interfaces

The API layer is optional in the SDP, but when exposed it should follow versioned, documented endpoints.

| Endpoint | Method | Description |
| --- | --- | --- |
| /api/v1/plans | GET | List plans. |
| /api/v1/plans | POST | Create a plan. |
| /api/v1/plans/{id} | GET | Get a plan. |
| /api/v1/plans/{id} | PUT | Update a plan. |
| /api/v1/plans/{id} | DELETE | Delete a plan. |
| /api/v1/plans/export | GET | Export one plan or all plans. |
| /api/v1/plans/import | POST | Import plan data. |
| /api/v1/autosuggest/skills | GET | Search skills by English or Japanese name. |
| /api/v1/autosuggest/characters | GET | Search characters for quick create and selection. |

#### 7.2.1 Example Request and Response Payloads

```json
{
    "request": {
        "method": "POST",
        "path": "/api/v1/plans",
        "body": {
            "title": "URA Finale Sprint",
            "character_id": 12,
            "storage_mode": "local",
            "career_stage": "junior"
        }
    },
    "response": {
        "status": 201,
        "body": {
            "id": 123,
            "uuid": "0f5b8f5c-5f7b-4e41-8f36-9d7e2ebf3f21",
            "title": "URA Finale Sprint",
            "storage_mode": "local",
            "career_stage": "junior"
        }
    }
}
```

```json
{
    "request": {
        "method": "GET",
        "path": "/api/v1/autosuggest/skills",
        "query": {
            "q": "speed"
        }
    },
    "response": {
        "status": 200,
        "body": {
            "data": [
                {
                    "id": 41,
                    "name": "Corner Recovery",
                    "name_jp": "コーナー回復",
                    "sp_cost": 180
                }
            ]
        }
    }
}
```

```json
{
    "request": {
        "method": "GET",
        "path": "/api/v1/plans/export",
        "query": {
            "scope": "all",
            "format": "csv"
        }
    },
    "response": {
        "status": 200,
        "body": {
            "schema_version": "3.0",
            "format": "csv",
            "scope": "all",
            "count": 24
        }
    }
}
```

---

## 8. Data Requirements

### 8.1 Data Entities

| Entity | Key Fields | Description |
| --- | --- | --- |
| Character | id, name, name_jp, image_path | Character roster records used by career runs. |
| CareerRun | id, uuid, character_id, user_id (nullable), storage_mode, title, status, career_stage, mood, conditions, notes | Core plan record. Local runs use UUID-based routing; Account runs use database IDs. |
| StatProgress | id, career_run_id, turn_number, speed, stamina, power, guts, wit | Turn-by-turn stat progress. |
| Skill | id, name, name_jp, sp_cost, tier | Skill reference data used by autocomplete. |
| SkillCareerRun | id, career_run_id, skill_id, status, turn_acquired | Join table for skill acquisitions and suggestions. |
| RacePrediction | id, career_run_id, race_name, venue, distance, track, stamina_threshold | Race planning entries. |
| Goal | id, career_run_id, description, completed, completion_date | Training goals and completion tracking. |
| RaceSnapshot | id, career_run_id, race_name, snapshot_data, notes | Race-day snapshots for later review. |
| ActivityLog | id, career_run_id, action, created_at | User-facing recent activity history. |
| Draft | id, career_run_id, version, payload, created_at | Auto-saved unsaved work with timestamps. |

### 8.2 Validation Rules

| Field | Rule |
| --- | --- |
| title | Required, trimmed, max 255 characters. |
| status | Enum: in_progress, completed, archived. |
| career_stage | Enum: junior, classic, senior. |
| storage_mode | Enum: local, account. |
| mood | Enum or controlled set aligned to the business glossary and game rules. |
| conditions | Controlled set of game-specific condition flags. |
| stat.* | Integer, range 0 to 1200. |
| skill.status | Enum: acquired, skipped, suggested. |
| skill.turn_acquired | Required when status is acquired; range 1 to 78. |
| goal.completion_date | Optional now; P2/Future if treated as a first-class editable field. |
| image upload | JPEG, PNG, or WebP only; max 2 MB. |

### 8.3 Data Retention

| Data Type | Retention Rule |
| --- | --- |
| Local runs | Retained until the user explicitly clears them or browser storage is cleared. |
| Account runs | Soft-deleted and recoverable for 30 days before permanent removal. |
| Drafts | Purged after 7 days. |
| Activity logs | Retained according to product and operational policy. |

---

## 9. System Constraints

### 9.1 Technical Constraints

1. Browser local storage quota limits apply to Local mode and may vary by browser.
2. No real-time collaboration updates are supported for Account mode.
3. Livewire Account-mode operations require a running PHP backend.
4. The application is web-only and does not include a native mobile client.

### 9.2 Business Constraints

1. English is the primary interface language.
2. Japanese names and game terms remain available for reference.
3. No integration with game servers is in scope for the MVP.
4. No user-to-user sharing workflow is included in the MVP unless explicitly added later.

---

## 10. Traceability Matrix

| BRS Requirement | SRS Requirement(s) | Priority | SDP Phase |
| --- | --- | --- | --- |
| BR-1.1 | REQ-CHAR-1.1 | P0 | Phase 2 / Phase 3 |
| BR-1.2 | REQ-CHAR-1.2, REQ-CHAR-1.3 | P1 | Phase 2 / Phase 3 |
| BR-1.3 | REQ-CHAR-1.4, REQ-APT-1.1 | P0 | Phase 3 |
| BR-1.4 | REQ-CHAR-1.4, REQ-STAT-1.2 | P0 | Phase 2 / Phase 3 |
| BR-2.1 | REQ-PLAN-1.1, REQ-PLAN-1.5 | P0 | Phase 2 / Phase 3 |
| BR-2.2 | REQ-PLAN-1.4 | P0 | Phase 2 / Phase 3 |
| BR-2.3 | REQ-PLAN-4.1 | P0 | Phase 2 / Phase 3 |
| BR-2.4 | REQ-PLAN-1.2, REQ-PLAN-3.2 | P0 | Phase 2 / Phase 3 |
| BR-2.5 | REQ-PLAN-1.3, REQ-PLAN-1.4 | P0 | Phase 2 / Phase 3 |
| BR-3.1 | REQ-STAT-1.1, REQ-TURN-1.3 | P0 | Phase 3 |
| BR-3.2 | REQ-STAT-1.5 | P1 | Phase 3 |
| BR-3.3 | REQ-STAT-1.3 | P0 | Phase 3 |
| BR-3.4 | REQ-STAT-1.5, REQ-TURN-1.4 | P0 | Phase 3 |
| BR-4.1 | REQ-SKILL-1.1 | P0 | Phase 2 / Phase 3 |
| BR-4.2 | REQ-SKILL-1.2 | P0 | Phase 2 / Phase 3 |
| BR-4.3 | REQ-SKILL-1.3 | P0 | Phase 2 / Phase 3 |
| BR-4.4 | REQ-SKILL-1.4 | P0 | Phase 2 / Phase 3 |
| BR-4.5 | REQ-SKILL-1.1 | P0 | Phase 2 / Phase 3 |
| BR-5.1 | REQ-RACE-1.1, REQ-RACE-1.2 | P1 | Phase 3 |
| BR-5.2 | REQ-RACE-2.2 | P1 | Phase 5 |
| BR-5.3 | REQ-RACE-2.1, REQ-RACE-2.2 | P2 | Phase 5 |
| BR-5.4 | REQ-RACE-1.3 | P1 | Phase 3 |
| BR-6.1 | REQ-GOAL-1.1 | P1 | Phase 3 |
| BR-6.2 | REQ-GOAL-1.1 | P1 | Phase 3 |
| BR-6.3 | REQ-GOAL-1.2 | P1 | Phase 3 |
| BR-7.1 | REQ-EXP-1.1 | P0 | Phase 2 / Phase 6 |
| BR-7.2 | REQ-EXP-1.1 | P0 | Phase 2 / Phase 6 |
| BR-7.3 | REQ-EXP-1.1 | P1 | Phase 2 / Phase 6 |
| BR-7.4 | REQ-EXP-1.1 | P1 | Phase 2 / Phase 6 |
| BR-7.5 | REQ-EXP-1.1 | P1 | Phase 2 / Phase 6 |
| BR-7.6 | REQ-EXP-1.3 | P1 | Phase 2 / Phase 6 |
| BR-7.7 | REQ-EXP-1.2 | P1 | Phase 2 / Phase 6 |
| BR-8.1 | REQ-IMP-1.1 | P0 | Phase 2 / Phase 6 |
| BR-8.2 | REQ-IMP-1.1, REQ-IMP-1.3 | P1 | Phase 2 / Phase 6 |
| BR-8.3 | REQ-IMP-1.5 | P1 | Phase 2 / Phase 6 |
| BR-8.4 | REQ-IMP-1.5 | P1 | Phase 2 / Phase 6 |
| BR-8.5 | REQ-IMP-1.6 | P1 | Phase 2 / Phase 6 |
| BR-9.1 | REQ-STOR-1.1, REQ-STOR-1.2 | P0 | Phase 2 / Phase 3 |
| BR-9.2 | REQ-STOR-1.1, REQ-AUTH-1.3 | P0 | Phase 2 / Phase 3 |
| BR-9.3 | REQ-STOR-1.4 | P0 | Phase 3 |
| BR-9.4 | REQ-STOR-1.6, REQ-DRAFT-1.1, REQ-CONN-1.3 | P0 | Phase 2 / Phase 3 |
| BR-9.5 | REQ-STOR-2.4 | P0 | Phase 2 / Phase 3 |
| BR-9.6 | REQ-STOR-2.1, REQ-STOR-2.2, REQ-STOR-2.3, REQ-STOR-2.4 | P1 | Phase 2 / Phase 3 |
| BR-10.1 | REQ-THEME-1.1, REQ-THEME-1.3 | P0 | Phase 5 |
| BR-10.2 | NFR-6.1, NFR-6.2, NFR-6.4 | P0 | Phase 5 |
| BR-10.3 | NFR-3.1-3.10 | P0 | Phase 5 |
| BR-10.4 | NFR-3.5, NFR-3.8 | P1 | Phase 5 |
| BR-10.5 | REQ-THEME-1.4, NFR-3.10 | P1 | Phase 5 |
| BR-11.1 | REQ-AUTH-1.3 | P0 | Phase 2 / Phase 3 |
| BR-11.2 | REQ-AUTH-1.4 | P0 | Phase 2 / Phase 3 |
| BR-11.3 | REQ-AUTH-1.1, REQ-AUTH-1.2 | P2 | Phase 2 / Phase 3 |
| BR-12.1 | REQ-IMP-1.2 | P0 | Phase 2 / Phase 6 |

**Traceability Note:** The following business requirements require direct one-to-one traceability and are explicitly covered above: BR-1.2, BR-3.4, BR-4.2-4.4, BR-5.4, BR-9.4, and BR-10.5.

---

## 11. Appendices

### 11.1 Requirement Change Log

| Date | Requirement ID(s) | Change Summary |
| --- | --- | --- |
| 2026-07-03 | REQ-DASH-2 | Clarified recent activity as the last 10 actions and added localStorage quota usage display. |
| 2026-07-03 | REQ-PLAN-1 | Added character quick-create support and default values for storage mode and career stage. |
| 2026-07-03 | REQ-SKILL-1 | Added bilingual autocomplete, third status value, and separate SP total rules. |
| 2026-07-03 | REQ-STOR-1, REQ-STOR-2 | Added UUID route guidance, backup/restore, storage usage progress, and local data management. |
| 2026-07-03 | REQ-IMP-1 | Added legacy-format detection and large-file chunking expectations. |
| 2026-07-03 | REQ-CHAR-1, REQ-RACE-2, REQ-AUTH-1 | Added the newly requested missing requirements. |

### 11.2 Test Data Requirements

| Data Set | Purpose | Volume |
| --- | --- | --- |
| Characters | Test character CRUD and image handling. | 10 records |
| Career Runs | Test plan creation, editing, and storage modes. | 50 records |
| Skills | Test autocomplete and SP totals. | 500+ records |
| Turns | Test turn tracking and milestone highlighting. | 78 turns per run |

### 11.3 Canonical Field Names Reference

| UI Label | Canonical Field | Table |
| --- | --- | --- |
| SP Balance | total_sp_available | career_runs |
| Stamina % | stamina_percentage | career_runs |
| Turn | turn_number | stat_progress |
| Current Turn | current_turn | career_runs |
| Plan ID | career_run_id | foreign keys |

### 11.4 Related Documents

- [D01_System_Development_Plan.md](D01_System_Development_Plan.md)
- [D02_Business_Requirements_Specifications.md](D02_Business_Requirements_Specifications.md)
- [D04_System_Design_Specifications.md](D04_System_Design_Specifications.md)

### 11.5 Revision History

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 1.0 | 2026-01-03 | System | Initial draft. |
| 2.0 | 2026-01-03 | System | Added diagrams, standardized formatting, and canonical field names. |
| 3.0 | 2026-07-03 | System | Refined traceability, added new requirements, improved error handling, and aligned with BRS and SDP. |
````
