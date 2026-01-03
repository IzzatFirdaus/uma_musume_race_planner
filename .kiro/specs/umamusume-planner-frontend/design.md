# Design Document: Uma Musume Career Planner Frontend

## Overview

This design document specifies the frontend architecture for the Uma Musume Career Planner application, a Laravel + Livewire + Alpine.js + TailwindCSS application that enables players to track, manage, and analyze their Uma Musume: Pretty Derby career progression. The system supports dual storage modes (Local localStorage and Account database), comprehensive plan management, and game-accurate stat/skill tracking.

## Spec Reconciliation Notes

This frontend spec defines **MVP implementation details**. The consolidation spec (`.kiro/specs/consolidation/`) defines the **target architecture** and **canonical schema**.

### Storage Technology

| Phase | Technology | Notes |
|-------|------------|-------|
| MVP (This Spec) | localStorage | Simple, broad browser support |
| Target (Consolidation) | IndexedDB via localforage | Larger datasets, structured queries |

The `LocalRunStorageService` abstraction allows swapping storage backends without UI changes.

### Field Name Mapping (UI Label → Canonical DB Field)

Code and Livewire bindings MUST use canonical field names. UI labels may differ for user-friendliness.

| UI Label | Canonical Field | Used In |
|----------|-----------------|---------|
| SP Balance | `total_sp_available` | CareerRun model, forms |
| Stamina % | `stamina_percentage` | CareerRun model |
| Turn | `turn_number` | StatProgress/Turn entries |
| Current Turn | `current_turn` | CareerRun model |

### Route Strategy

- Account runs: `/plans/{id}` and `/plans/{id}/edit` (numeric database ID)
- Local runs: `/plans/local/{uuid}` and `/plans/local/{uuid}/edit` (client-generated UUID)

### Authentication

- **Web UI:** Laravel built-in auth (Breeze/Fortify)
- **API (if needed):** Sanctum tokens

## Architecture

### Technology Stack

- **Backend Framework**: Laravel 12+ with Blade templating
- **Frontend Reactivity**: Livewire 3 for server-driven components
- **Client-Side Interactivity**: Alpine.js for lightweight client-side state
- **Styling**: TailwindCSS v4 with custom design tokens
- **Build Tool**: Vite for asset bundling and HMR
- **Testing**: Playwright for E2E, Pest for backend, Vitest for JS utilities (if needed)

### Architectural Patterns

```
┌─────────────────────────────────────────────────────────────────┐
│                        Browser Layer                             │
├─────────────────────────────────────────────────────────────────┤
│  Alpine.js Components          │  Livewire Components           │
│  - Dropdowns, Modals           │  - PlanList, PlanEditor        │
│  - Tabs, Tooltips              │  - SkillsEditor, TurnsEditor   │
│  - Dark Mode Toggle            │  - Dashboard, CharacterList    │
│  - Client-side validation      │  - ImportWizard, ExportPreview │
├─────────────────────────────────────────────────────────────────┤
│                     localStorage Layer                           │
│  - Local_Runs (full plan data)                                  │
│  - Draft autosave (form state)                                  │
│  - User preferences (dark mode, dismissed tooltips)             │
├─────────────────────────────────────────────────────────────────┤
│                     Livewire Wire Protocol                       │
├─────────────────────────────────────────────────────────────────┤
│                        Laravel Backend                           │
│  - Controllers (API routes)                                     │
│  - Livewire Components (server-side)                            │
│  - Services (CareerRunService, SkillService)                    │
│  - Models (CareerRun, Skill, Character, Turn)                   │
├─────────────────────────────────────────────────────────────────┤
│                     Database (MySQL/MariaDB)                     │
│  - Account_Runs (career_runs table)                             │
│  - Reference data (characters, skills)                          │
└─────────────────────────────────────────────────────────────────┘
```

### Storage Mode Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     Storage Mode Decision                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│   User Authenticated?                                            │
│         │                                                        │
│    ┌────┴────┐                                                   │
│    │         │                                                   │
│   No        Yes                                                  │
│    │         │                                                   │
│    ▼         ▼                                                   │
│ Local_Run  Choose Mode                                           │
│ (localStorage)  │                                                │
│              ┌──┴──┐                                             │
│              │     │                                             │
│           Local  Account                                         │
│              │     │                                             │
│              ▼     ▼                                             │
│         localStorage  Database                                   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### Page Components (Livewire Full-Page)

| Component | Route | Description |
|-----------|-------|-------------|
| `Dashboard` | `/`, `/dashboard` | Main landing page with plan list, stats panel, activity log |
| `PlanView` | `/plans/{id}` | Read-only plan details (Account runs, numeric ID) |
| `PlanEdit` | `/plans/{id}/edit` | Full-screen plan editor (Account runs) |
| `LocalPlanView` | `/plans/local/{uuid}` | Read-only plan details (Local runs, UUID) |
| `LocalPlanEdit` | `/plans/local/{uuid}/edit` | Full-screen plan editor (Local runs) |
| `CharacterList` | `/characters` | Browsable character roster with filtering |
| `GuidePage` | `/guide` | Application usage guide with sticky navigation |
| `ImportWizard` | `/import` | Multi-step import flow with preview |
| `LocalDataManager` | `/local-data` | Local storage management: export all, import, purge, convert |

**Route Strategy Notes:**

- Account runs use numeric IDs: `/plans/{id}` where `id` is database primary key
- Local runs use UUIDs: `/plans/local/{uuid}` where `uuid` is client-generated
- This separation allows clear routing logic and avoids ID collision
- Plan list links dynamically generate correct route based on `storage_mode`

### Reusable Livewire Components

| Component | Purpose | Key Props |
|-----------|---------|-----------|
| `PlanList` | Displays filterable plan cards | `filters`, `sortBy`, `storageMode` |
| `PlanCard` | Individual plan summary | `plan`, `expanded`, `storageMode` |
| `InlineEditor` | Quick-edit panel in list | `planId`, `storageMode` |
| `SkillsEditor` | Skill table with autocomplete | `skills`, `totalSpAvailable` |
| `TurnsEditor` | Turn-by-turn stat entry | `turns`, `careerStage` |
| `AttributesDisplay` | Stat visualization | `stats`, `showCircular` |
| `AptitudeGrades` | Grade selector grid | `aptitudes`, `editable` |
| `RacePredictions` | Race planning table | `predictions`, `snapshots` |
| `GoalsEditor` | Goal checklist | `goals` |
| `SupportCards` | 6-card deck manager | `cards`, `editable` |
| `LocalRunList` | List local runs with storage info | `runs`, `selectable` |
| `StorageStats` | Display localStorage usage | `stats`, `showWarning` |
| `ConvertModal` | Convert Local→Account flow | `runs`, `bulkMode` |

### Alpine.js Components

| Component | Purpose | State |
|-----------|---------|-------|
| `x-dropdown` | Generic dropdown menu | `open` |
| `x-modal` | Modal dialog wrapper | `show`, `onClose` |
| `x-tabs` | Tab navigation | `activeTab` |
| `x-tooltip` | Hover/click tooltips | `visible`, `content` |
| `x-dark-mode` | Theme toggle | `dark` (persisted) |
| `x-toast` | Notification stack | `toasts[]` |
| `x-confirm` | Confirmation dialog | `show`, `message`, `onConfirm` |

### Component Hierarchy

```
App Layout
├── Navbar
│   ├── Logo
│   ├── Navigation Links
│   ├── Global Search (x-dropdown)
│   ├── Dark Mode Toggle (x-dark-mode)
│   └── User Menu (x-dropdown)
├── Main Content
│   └── [Page Component]
├── Toast Container (x-toast)
└── Modal Container (x-modal)

Dashboard
├── Stats Panel
├── Plan List (Livewire)
│   ├── Filters Bar
│   ├── Plan Cards[]
│   │   └── Inline Editor (expandable)
│   └── Pagination
├── Activity Log
└── Quick Create Modal (x-modal)

Plan Editor
├── Header (title, status, storage badge)
├── Form Tabs (x-tabs)
│   ├── General Tab
│   ├── Attributes Tab
│   │   └── Circular Progress[]
│   ├── Aptitude Grades Tab
│   ├── Skills Tab
│   │   └── Skills Editor (Livewire)
│   ├── Race Predictions Tab
│   │   └── Snapshots Timeline
│   ├── Goals Tab
│   ├── Turns Tab
│   │   └── Turns Editor (Livewire)
│   └── Support Cards Tab
├── Action Bar (Save, Export, Duplicate)
└── Unsaved Changes Indicator
```

## Data Models

### Frontend State Models

```typescript
// Plan/CareerRun (matches backend model)
interface CareerRun {
  id: number | null;       // Database ID for Account runs, null for Local runs
  uuid: string;            // Client-generated UUID for Local runs, server-assigned for Account
  title: string;
  character_id: number | null;
  character_name: string;
  storage_mode: 'local' | 'account';
  status: 'in_progress' | 'completed' | 'archived';
  career_stage: 'junior' | 'classic' | 'senior';
  current_turn: number;
  
  // Stats (current values - see Mapping Rules below)
  speed: number;
  stamina: number;
  power: number;
  guts: number;
  wit: number;
  
  // Growth rates
  speed_growth: number;
  stamina_growth: number;
  power_growth: number;
  guts_growth: number;
  wit_growth: number;
  
  // Aptitudes
  turf_aptitude: AptitudeGrade;
  dirt_aptitude: AptitudeGrade;
  sprint_aptitude: AptitudeGrade;
  mile_aptitude: AptitudeGrade;
  medium_aptitude: AptitudeGrade;
  long_aptitude: AptitudeGrade;
  nige_aptitude: AptitudeGrade;
  senkou_aptitude: AptitudeGrade;
  sashi_aptitude: AptitudeGrade;
  oikomi_aptitude: AptitudeGrade;
  
  // Status
  mood: Mood;
  conditions: Condition[];
  energy: number;
  total_sp_available: number;  // Canonical field name (UI label: "SP Balance")
  stamina_percentage: number;  // Canonical field name (UI label: "Stamina %")
  
  // Relations
  skills: Skill[];
  turns: Turn[];
  goals: Goal[];
  race_predictions: RacePrediction[];
  support_cards: SupportCard[];
  snapshots: RaceSnapshot[];
  
  // Metadata
  strategy: Strategy | null;
  notes: string;
  image_path: string | null;
  created_at: string;
  updated_at: string;
}

// Helper to generate RunKey from CareerRun
function getRunKey(run: CareerRun): RunKey {
  return run.storage_mode === 'local' 
    ? `local:${run.uuid}` 
    : `account:${run.id}`;
}

// Helper to generate route from CareerRun
function getRunRoute(run: CareerRun, action: 'view' | 'edit' = 'view'): string {
  const suffix = action === 'edit' ? '/edit' : '';
  return run.storage_mode === 'local'
    ? `/plans/local/${run.uuid}${suffix}`
    : `/plans/${run.id}${suffix}`;
}

type AptitudeGrade = 'SS' | 'S' | 'A' | 'B' | 'C' | 'D' | 'E' | 'F' | 'G';
type Mood = 'great' | 'good' | 'normal' | 'bad' | 'awful';
type Condition = 'night_owl' | 'practice_poor' | 'overweight' | 'charming' | 'practice_perfect';
type Strategy = 'nige' | 'senkou' | 'sashi' | 'oikomi' | 'speed' | 'stamina' | 'power' | 'balanced';
type SkillStatus = 'acquired' | 'skipped' | 'suggested';

interface Skill {
  // Skill tiers range from G- (lowest) to SS (highest)
  // SS tier is reserved for skills that require maximum stat values (1200)
  id: string;
  name: string;
  name_jp: string | null;
  sp_cost: number;
  tier: 'G-' | 'G' | 'G+' | 'F-' | 'F' | 'F+' | 'E-' | 'E' | 'E+' | 'D-' | 'D' | 'D+' | 'C-' | 'C' | 'C+' | 'B-' | 'B' | 'B+' | 'A-' | 'A' | 'A+' | 'S-' | 'S' | 'S+' | 'SS';
  type: 'speed' | 'stamina' | 'power' | 'guts' | 'wit' | 'debuff';
  status: SkillStatus;
  turn_acquired: number | null;
  notes: string;
}

interface Turn {
  id: string;
  turn_number: number;  // Canonical field name
  career_year: 'junior' | 'classic' | 'senior';
  training_type: 'speed' | 'stamina' | 'power' | 'guts' | 'wit' | 'rest' | 'race' | null;
  speed: number;
  stamina: number;
  power: number;
  guts: number;
  wit: number;
  energy: number;
  notes: string;
  is_milestone: boolean;
  milestone_name: string | null;
}

interface RacePrediction {
  id: string;
  race_name: string;
  venue: string;
  distance_category: 'sprint' | 'mile' | 'medium' | 'long';
  track_type: 'turf' | 'dirt';
  predicted_placement: number | null;
  actual_placement: number | null;
  notes: string;
  order: number;
}

interface RaceSnapshot {
  id: string;
  race_prediction_id: string;
  captured_at: string;
  stats: { speed: number; stamina: number; power: number; guts: number; wit: number };
  skill_ids: string[];
  aptitudes: Record<string, AptitudeGrade>;
  mood: Mood;
  conditions: Condition[];
  energy: number;
}

interface Goal {
  id: string;
  description: string;
  completed: boolean;
  order: number;
}

interface SupportCard {
  id: string;
  card_id: number | null;
  card_name: string;
  card_type: 'speed' | 'stamina' | 'power' | 'guts' | 'wit' | 'friend' | 'group';
  level: number;
  limit_break: number;
  friendship: number;
  skill_hints: string[];
}
```

### localStorage Schema

```typescript
// localStorage keys
const STORAGE_KEYS = {
  LOCAL_RUNS: 'uma_local_runs',           // LocalRunsStore
  DRAFTS: 'uma_drafts',                   // Record<string, DraftState>
  PREFERENCES: 'uma_preferences',          // UserPreferences
  DISMISSED_TOOLTIPS: 'uma_dismissed_tips', // string[]
  RECENT_SEARCHES: 'uma_recent_searches',  // string[]
  SAVED_VIEWS: 'uma_saved_views',          // SavedView[]
};

// Versioned local storage wrapper
interface LocalRunsStore {
  schema_version: string;  // e.g., "1.0" - for future migrations
  runs: CareerRun[];
  last_modified: string;   // ISO timestamp
}

// Run key format for unified identification
// - Local runs: "local:<uuid>" (e.g., "local:550e8400-e29b-41d4-a716-446655440000")
// - Account runs: "account:<id>" (e.g., "account:123")
type RunKey = `local:${string}` | `account:${number}`;

interface DraftState {
  runKey: RunKey;          // Unified key format for both storage modes
  formData: Partial<CareerRun>;
  savedAt: string;
  expiresAt: string;
}

interface UserPreferences {
  darkMode: boolean;
  reducedMotion: boolean;
  defaultStorageMode: 'local' | 'account';
  tourCompleted: boolean;
  lastViewedRunKey: RunKey | null;
}

interface SavedView {
  id: string;
  name: string;
  filters: FilterState;
  isDefault: boolean;
}

// Storage quota tracking
interface StorageStats {
  used: number;        // bytes
  available: number;   // estimated available (browser-dependent)
  runCount: number;
  warningThreshold: number;  // 0.8 (80%)
  isNearQuota: boolean;
}
```

### Data Mapping Rules

**Current Stats Source of Truth:**

- The `speed`, `stamina`, `power`, `guts`, `wit` fields on `CareerRun` represent the **current snapshot** of stats
- These are stored as separate columns (not derived from turns) for quick access
- When a new turn is added, the run's current stats should be updated to match the turn's stats
- Charts display turn history; stat displays show current values from the run

**Stat Validation (Frontend):**

```typescript
// Hard max at 1200
function validateStat(rawValue: number): boolean {
  const HARD_MAX = 1200;
  return rawValue >= 0 && rawValue <= HARD_MAX;
}

// Total stats for display
function calculateTotalStats(run: CareerRun): number {
  return ['speed', 'stamina', 'power', 'guts', 'wit']
    .map(stat => run[stat])
    .reduce((sum, val) => sum + val, 0);
}
```

**Skill SP Totals:**

- `acquired_sp`: Sum of `sp_cost` for skills where `status === 'acquired'`
- `planned_sp`: Sum of `sp_cost` for skills where `status === 'suggested'`
- `total_sp_available`: User-entered current SP available (not auto-calculated) - canonical field name

**Turn-to-Run Sync:**

- When saving a turn, if `turn_number` equals `current_turn`, update run's stat fields
- When deleting the latest turn, optionally revert run stats to previous turn (prompt user)

## Data Flow

### Plan Creation Flow

```text
User clicks "Create Plan"
        │
        ▼
┌─────────────────┐
│ Quick Create    │
│ Modal Opens     │
│ (Alpine.js)     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ User enters:    │
│ - Title         │
│ - Character     │
│ - Storage Mode  │
└────────┬────────┘
         │
         ▼
    Authenticated?
    ┌────┴────┐
   No        Yes
    │         │
    ▼         ▼
localStorage  Livewire
(uuid gen)    (POST)
    │         │
    ▼         ▼
Save to      Save to
localStorage  database
    │         │
    ▼         ▼
Navigate to  Navigate to
/plans/local /plans/{id}
/{uuid}/edit /edit
```

### Skill Autocomplete Flow

```
User types in skill field
        │
        ▼
┌─────────────────┐
│ Debounce 300ms  │
│ (wire:model.    │
│  debounce)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Livewire query  │
│ skill database  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Return matches: │
│ - name          │
│ - name_jp       │
│ - sp_cost       │
│ - tier          │
│ - type          │
│ - description   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Display dropdown│
│ (Alpine.js)     │
└────────┬────────┘
         │
         ▼
User selects skill
         │
         ▼
┌─────────────────┐
│ Auto-populate:  │
│ - name          │
│ - sp_cost       │
│ - tier          │
│ - type          │
└─────────────────┘
```

### Storage Mode Conversion Flow

```text
User logs in with Local_Runs
        │
        ▼
┌─────────────────┐
│ "Claim Plans"   │
│ modal appears   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ User selects    │
│ plans to convert│
│ ☑ Keep local    │
│   copy (opt)    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ For each plan:  │
│ 1. POST to DB   │
│ 2. If success:  │
│    - Delete     │
│      local (or  │
│      keep copy) │
│ 3. Update UI    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Show results:   │
│ - Converted: N  │
│ - Failed: M     │
│ - Kept local: K │
└─────────────────┘
```

### Local Data Management Flow

```text
User navigates to /local-data
        │
        ▼
┌─────────────────────────────────────┐
│ Local Data Manager Page             │
│ ┌─────────────────────────────────┐ │
│ │ Storage Stats:                  │ │
│ │ Used: 2.3MB / ~5MB (46%)        │ │
│ │ Runs: 12 local plans            │ │
│ └─────────────────────────────────┘ │
│                                     │
│ Actions:                            │
│ [Export All] [Import] [Purge All]   │
│ [Convert All to Account] (if auth)  │
│                                     │
│ Local Runs List:                    │
│ ☐ Plan A (1.2KB, modified 2h ago)   │
│ ☐ Plan B (0.8KB, modified 1d ago)   │
│ ☐ Plan C (2.1KB, modified 3d ago)   │
│                                     │
│ Selected: [Export] [Delete] [Convert]│
└─────────────────────────────────────┘

Export All Flow:
1. Click "Export All"
2. System generates JSON with all Local_Runs
3. JSON includes schema_version for compatibility
4. Browser downloads uma_local_backup_{date}.json

Import Flow:
1. Click "Import"
2. File picker opens (accept .json)
3. System validates schema_version
4. Preview shows importable runs with conflict detection
5. User selects: Skip / Overwrite / Import as Copy
6. System imports and shows results report

Purge All Flow:
1. Click "Purge All"
2. First confirmation: "Delete all 12 local plans?"
3. Second confirmation: Type "DELETE" to confirm
4. System clears localStorage
5. Success message with undo option (10s window)

Convert All Flow (authenticated):
1. Click "Convert All to Account"
2. Preview shows runs to convert
3. User confirms (optional: keep local copies)
4. System converts each run sequentially
5. Results report: converted/failed/kept
```

### Connection State Management

```text
Livewire connection lost
        │
        ▼
┌─────────────────┐
│ Show banner:    │
│ "Connection     │
│  Lost"          │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Save draft to   │
│ localStorage    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Retry every 5s  │
│ (max 5 attempts)│
└────────┬────────┘
         │
    ┌────┴────┐
 Success    Fail
    │         │
    ▼         ▼
┌─────────┐ ┌─────────┐
│ Show    │ │ Show    │
│ "Recon- │ │ options:│
│ nected" │ │ - Retry │
│         │ │ - Work  │
│ Prompt  │ │   Offline│
│ to save │ └─────────┘
└─────────┘
```

## UI Component Specifications

### Color System

```javascript
// tailwind.config.js custom colors
module.exports = {
  theme: {
    extend: {
      colors: {
        // Stat colors (game-accurate)
        stat: {
          speed: '#3399ff',
          stamina: '#33cc99',
          power: '#ff4d4d',
          guts: '#ffa500',
          wit: '#9933ff',
        },
        // Aptitude grade colors
        grade: {
          SS: '#e5e7eb', // Platinum/Light Gray
          S: '#ffd700',  // Gold
          A: '#ef4444',  // Red
          B: '#f97316',  // Orange
          C: '#22c55e',  // Green
          D: '#3b82f6',  // Blue
          E: '#a855f7',  // Purple
          F: '#6b7280',  // Gray
          G: '#9ca3af',  // Dark Gray
        },
        // Skill tier colors (includes + and - modifiers)
        tier: {
          'SS': '#ffd700',  // Gold (reserved for max stats 1200)
          'S+': '#ffed4e',  // Light Gold
          'S': '#ffd700',   // Gold
          'S-': '#e6c200',  // Dark Gold
          'A+': '#ff6b6b',  // Light Red
          'A': '#ef4444',   // Red
          'A-': '#dc2626',  // Dark Red
          'B+': '#fb923c',  // Light Orange
          'B': '#f97316',   // Orange
          'B-': '#ea580c',  // Dark Orange
          'C+': '#4ade80',  // Light Green
          'C': '#22c55e',   // Green
          'C-': '#16a34a',  // Dark Green
          'D+': '#60a5fa',  // Light Blue
          'D': '#3b82f6',   // Blue
          'D-': '#2563eb',  // Dark Blue
          'E+': '#c084fc',  // Light Purple
          'E': '#a855f7',   // Purple
          'E-': '#9333ea',  // Dark Purple
          'F+': '#9ca3af',  // Light Gray
          'F': '#6b7280',   // Gray
          'F-': '#4b5563',  // Dark Gray
          'G+': '#d1d5db',  // Very Light Gray
          'G': '#9ca3af',   // Dark Gray
          'G-': '#6b7280',  // Very Dark Gray
        },
        // Mood colors
        mood: {
          great: '#22c55e',
          good: '#84cc16',
          normal: '#6b7280',
          bad: '#f97316',
          awful: '#ef4444',
        },
        // Status colors
        status: {
          'in-progress': '#3b82f6',
          completed: '#22c55e',
          archived: '#6b7280',
        },
        // Storage mode colors
        storage: {
          local: '#f59e0b',
          account: '#8b5cf6',
        },
      },
    },
  },
  // Safelist for dynamic class generation (e.g., text-stat-{{ $stat }})
  // Required because Tailwind purges classes not found in static analysis
  safelist: [
    'text-stat-speed', 'text-stat-stamina', 'text-stat-power', 'text-stat-guts', 'text-stat-wit',
    'bg-stat-speed', 'bg-stat-stamina', 'bg-stat-power', 'bg-stat-guts', 'bg-stat-wit',
    'border-stat-speed', 'border-stat-stamina', 'border-stat-power', 'border-stat-guts', 'border-stat-wit',
    'text-grade-SS', 'text-grade-S', 'text-grade-A', 'text-grade-B', 'text-grade-C', 'text-grade-D', 'text-grade-E', 'text-grade-F', 'text-grade-G',
    'bg-grade-SS', 'bg-grade-S', 'bg-grade-A', 'bg-grade-B', 'bg-grade-C', 'bg-grade-D', 'bg-grade-E', 'bg-grade-F', 'bg-grade-G',
    'text-mood-great', 'text-mood-good', 'text-mood-normal', 'text-mood-bad', 'text-mood-awful',
    'bg-mood-great', 'bg-mood-good', 'bg-mood-normal', 'bg-mood-bad', 'bg-mood-awful',
    'text-storage-local', 'text-storage-account', 'bg-storage-local', 'bg-storage-account',
  ],
  // Ensure content scanning includes Blade + Livewire templates
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './app/Livewire/**/*.php',
    './app/View/Components/**/*.php',
  ],
};
```

### Circular Progress Component

```html
<!-- Blade component: components/circular-progress.blade.php -->
<div 
  x-data="{ 
    value: {{ $value }}, 
    max: {{ $max ?? 1200 }},
    animatedValue: 0 
  }"
  x-init="
    $nextTick(() => {
      if (!$store.preferences.reducedMotion) {
        let start = 0;
        const duration = 1000;
        const startTime = performance.now();
        const animate = (currentTime) => {
          const elapsed = currentTime - startTime;
          const progress = Math.min(elapsed / duration, 1);
          animatedValue = Math.floor(progress * value);
          if (progress < 1) requestAnimationFrame(animate);
        };
        requestAnimationFrame(animate);
      } else {
        animatedValue = value;
      }
    })
  "
  class="relative w-24 h-24"
  data-testid="circular-progress-{{ $stat }}"
>
  <!-- Background circle -->
  <svg class="w-full h-full transform -rotate-90">
    <circle
      cx="48" cy="48" r="40"
      stroke="currentColor"
      stroke-width="8"
      fill="none"
      class="text-gray-200 dark:text-gray-700"
    />
    <!-- Progress circle -->
    <circle
      cx="48" cy="48" r="40"
      stroke="currentColor"
      stroke-width="8"
      fill="none"
      :stroke-dasharray="251.2"
      :stroke-dashoffset="251.2 - (251.2 * Math.min(animatedValue / max, 1))"
      class="text-stat-{{ $stat }} transition-all duration-300"
    />
    <!-- Max indicator (values at 1200) -->
    @if($value > 1200)
    <circle
      cx="48" cy="48" r="32"
      stroke="currentColor"
      stroke-width="4"
      fill="none"
      stroke-dasharray="201"
      :stroke-dashoffset="201 - (201 * (({{ $value }} - 1200) / 800))"
      class="text-stat-{{ $stat }} opacity-50"
    />
    @endif
  </svg>
  <!-- Center text -->
  <div class="absolute inset-0 flex flex-col items-center justify-center">
    <span 
      class="text-xl font-bold text-stat-{{ $stat }}"
      x-text="animatedValue"
    ></span>
    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
  </div>
</div>
```

### Skill Status Badge

```html
<!-- Blade component: components/skill-status-badge.blade.php -->
@props(['status'])

@php
$styles = [
  'acquired' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
  'skipped' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
  'suggested' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
];
$labels = [
  'acquired' => __('Acquired'),
  'skipped' => __('Skipped'),
  'suggested' => __('Suggested'),
];
@endphp

<span 
  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $styles[$status] }}"
  data-testid="skill-status-{{ $status }}"
>
  {{ $labels[$status] }}
</span>
```

### Storage Mode Badge

```html
<!-- Blade component: components/storage-badge.blade.php -->
@props(['mode'])

<span 
  class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium
    {{ $mode === 'local' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' }}"
  data-testid="storage-badge-{{ $mode }}"
>
  @if($mode === 'local')
    <x-icon name="device-mobile" class="w-3 h-3" />
    {{ __('Local') }}
  @else
    <x-icon name="cloud" class="w-3 h-3" />
    {{ __('Account') }}
  @endif
</span>
```

## Form Validation Rules

> **Note:** The validation snippets below are conceptual examples. The `required_if` with wildcard arrays (`skills.*.status`) requires custom validation logic in Laravel—typically implemented via a custom Rule class or per-row validation in a loop.

### Plan Validation (Conceptual)

```php
// app/Http/Livewire/PlanEditor.php
// Conceptual - actual implementation may vary
protected $rules = [
    'plan.title' => 'required|string|max:255',
    'plan.character_name' => 'nullable|string|max:255',
    'plan.status' => 'required|in:in_progress,completed,archived',
    'plan.career_stage' => 'required|in:junior,classic,senior',
    'plan.current_turn' => 'required|integer|min:1|max:78',
    
    // Stats (0-1200 hard max)
    'plan.speed' => 'required|integer|min:0|max:1200',
    'plan.stamina' => 'required|integer|min:0|max:1200',
    'plan.power' => 'required|integer|min:0|max:1200',
    'plan.guts' => 'required|integer|min:0|max:1200',
    'plan.wit' => 'required|integer|min:0|max:1200',
    
    // Aptitudes (all follow same pattern: SS,S,A,B,C,D,E,F,G)
    'plan.turf_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.dirt_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.sprint_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.mile_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.medium_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.long_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.nige_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.senkou_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.sashi_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    'plan.oikomi_aptitude' => 'required|in:SS,S,A,B,C,D,E,F,G',
    
    // Status
    'plan.mood' => 'required|in:great,good,normal,bad,awful',
    'plan.energy' => 'required|integer|min:0|max:100',
    'plan.sp_balance' => 'required|integer|min:0',
];

// Custom validation messages
protected $messages = [
    'plan.title.required' => 'Plan title is required.',
    'plan.current_turn.max' => 'Turn number cannot exceed 78.',
    'plan.speed.max' => 'Stat values cannot exceed 1200.',
];
```

### Skill Validation (Conceptual)

```php
// Skill entry validation - conceptual
// Note: required_if with wildcards needs custom implementation
// Recommended: validate each skill row in a loop with Rule::requiredIf()
protected $skillRules = [
    'skills.*.name' => 'required|string|max:255',
    'skills.*.sp_cost' => 'required|integer|min:0|max:1000',
    'skills.*.tier' => 'required|in:G-,G,G+,F-,F,F+,E-,E,E+,D-,D,D+,C-,C,C+,B-,B,B+,A-,A,A+,S-,S,S+,SS',
    'skills.*.type' => 'required|in:speed,stamina,power,guts,wit,debuff',
    'skills.*.status' => 'required|in:acquired,skipped,suggested',
    // turn_acquired required when status=acquired - implement via custom rule
    'skills.*.turn_acquired' => 'nullable|integer|min:1|max:78',
];

// Custom validation for turn_acquired conditional requirement
public function validateSkills(): void
{
    foreach ($this->skills as $index => $skill) {
        if ($skill['status'] === 'acquired' && empty($skill['turn_acquired'])) {
            $this->addError("skills.{$index}.turn_acquired", 'Turn acquired is required for acquired skills.');
        }
    }
}
```

## Error Handling

### Livewire Error Handling

```php
// app/Http/Livewire/Traits/HandlesErrors.php
trait HandlesErrors
{
    public function handleLivewireError(\Exception $e)
    {
        Log::error('Livewire error', [
            'component' => get_class($this),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        $this->dispatch('toast', [
            'type' => 'error',
            'message' => __('An error occurred. Please try again.'),
            'action' => 'retry',
        ]);
    }
    
    public function handleValidationError($errors)
    {
        $this->dispatch('validation-errors', [
            'errors' => $errors,
            'firstField' => array_key_first($errors),
        ]);
    }
}
```

### Connection State Handling (Conceptual)

> **Note:** This is conceptual pseudocode. Livewire v3 hook signatures may vary. Consider using a dedicated health endpoint (`GET /health`) for more reliable connectivity checks.

```javascript
// resources/js/connection-state.js
// Conceptual implementation - verify Livewire v3 hook API before use

// Alpine store for connection state
document.addEventListener('alpine:init', () => {
    Alpine.store('connection', {
        lost: false,
        failed: false,
        attempts: 0,
        maxAttempts: 5,
    });
});

document.addEventListener('livewire:init', () => {
    const store = Alpine.store('connection');
    
    // Listen for Livewire request failures
    // Note: Exact hook API depends on Livewire version
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 0) {
                // Network error - no response
                preventDefault();
                store.lost = true;
                attemptReconnect();
            }
        });
    });
    
    function attemptReconnect() {
        if (store.attempts >= store.maxAttempts) {
            store.failed = true;
            return;
        }
        
        store.attempts++;
        setTimeout(() => {
            // Use a lightweight health endpoint instead of static asset
            fetch('/health', { method: 'GET' })
                .then(response => {
                    if (response.ok) {
                        store.lost = false;
                        store.attempts = 0;
                        showReconnectedToast();
                    } else {
                        attemptReconnect();
                    }
                })
                .catch(() => attemptReconnect());
        }, 5000);
    }
    
    function showReconnectedToast() {
        // Dispatch toast event for UI feedback
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { type: 'success', message: 'Connection restored' }
        }));
    }
});
```

## Testing Strategy

### Test Categories

1. **Unit Tests (Pest)**: Backend logic, services, models
2. **Component Tests (Livewire)**: Individual Livewire component behavior
3. **E2E Tests (Playwright)**: Critical user flows
4. **Visual Regression Tests**: Dark mode, responsive layouts
5. **Accessibility Tests (axe-core)**: WCAG compliance

### E2E Test Scenarios

```javascript
// tests/e2e/critical-flows.spec.js
import { test, expect } from '@playwright/test';

test.describe('Critical User Flows', () => {
    test('Dashboard loads and displays plans', async ({ page }) => {
        await page.goto('/dashboard');
        await expect(page.getByTestId('plan-list')).toBeVisible();
        await expect(page.getByTestId('stats-panel')).toBeVisible();
    });
    
    // Account run creation (authenticated user)
    test('Create account plan flow', async ({ page }) => {
        // Assumes authenticated session
        await page.goto('/dashboard');
        await page.getByTestId('plan-create-button').click();
        await expect(page.getByTestId('quick-create-modal')).toBeVisible();
        
        await page.getByTestId('plan-title-input').fill('Test Plan');
        await page.getByTestId('storage-mode-account').click();
        await page.getByTestId('plan-submit-button').click();
        
        // Account runs use numeric IDs
        await expect(page).toHaveURL(/\/plans\/\d+\/edit/);
    });
    
    // Local run creation (anonymous or explicit local choice)
    test('Create local plan flow', async ({ page }) => {
        await page.goto('/dashboard');
        await page.getByTestId('plan-create-button').click();
        
        await page.getByTestId('plan-title-input').fill('Local Test Plan');
        await page.getByTestId('storage-mode-local').click();
        await page.getByTestId('plan-submit-button').click();
        
        // Local runs use UUID in /plans/local/{uuid} route
        await expect(page).toHaveURL(/\/plans\/local\/[a-f0-9-]+\/edit/);
    });
    
    test('Edit account plan and save', async ({ page }) => {
        await page.goto('/plans/1/edit');
        
        await page.getByTestId('stat-speed-input').fill('1000');
        await page.getByTestId('plan-save-button').click();
        
        await expect(page.getByTestId('toast-success')).toBeVisible();
    });
    
    test('Export plan', async ({ page }) => {
        await page.goto('/plans/1');
        await page.getByTestId('plan-export-button').click();
        
        await expect(page.getByTestId('export-preview-modal')).toBeVisible();
        
        const [download] = await Promise.all([
            page.waitForEvent('download'),
            page.getByTestId('export-confirm-button').click(),
        ]);
        
        expect(download.suggestedFilename()).toMatch(/\.json$/);
    });
});
```

### Accessibility Test Configuration

```javascript
// tests/e2e/accessibility.spec.js
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('Accessibility', () => {
    test('Dashboard meets WCAG AA', async ({ page }) => {
        await page.goto('/dashboard');
        
        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();
        
        expect(results.violations).toEqual([]);
    });
    
    test('Plan editor meets WCAG AA', async ({ page }) => {
        await page.goto('/plans/1/edit');
        
        const results = await new AxeBuilder({ page })
            .withTags(['wcag2a', 'wcag2aa'])
            .analyze();
        
        expect(results.violations).toEqual([]);
    });
    
    test('Dark mode maintains contrast', async ({ page }) => {
        await page.goto('/dashboard');
        await page.getByTestId('dark-mode-toggle').click();
        
        const results = await new AxeBuilder({ page })
            .withTags(['wcag2aa'])
            .analyze();
        
        expect(results.violations).toEqual([]);
    });
});
```
