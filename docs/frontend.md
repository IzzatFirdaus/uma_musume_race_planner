# Uma Musume Race Planner - Frontend Documentation

## Overview

The Uma Musume Race Planner is a comprehensive web application built with Laravel + Livewire + Alpine.js + TailwindCSS that enables players of Uma Musume: Pretty Derby to track, manage, and analyze their career progression. The application consolidates features from six previous versions into a unified platform with dual storage modes supporting both anonymous and authenticated users.

## Table of Contents

- [Architecture](#architecture)
- [Technology Stack](#technology-stack)
- [Storage Strategy](#storage-strategy)
- [Component Architecture](#component-architecture)
- [Data Models](#data-models)
- [User Interface](#user-interface)
- [Accessibility](#accessibility)
- [Performance](#performance)
- [Testing](#testing)
- [Development Guidelines](#development-guidelines)

## Architecture

### High-Level Architecture

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

### Dual Storage Architecture

The application supports two distinct storage modes:

1. **Local_Runs (localStorage)**: For anonymous users or offline-first usage
2. **Account_Runs (Database)**: For authenticated users requiring cross-device sync

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

## Technology Stack

### Core Technologies

- **Backend Framework**: Laravel 12+ with Blade templating
- **Frontend Reactivity**: Livewire 3 for server-driven components
- **Client-Side Interactivity**: Alpine.js for lightweight client-side state
- **Styling**: TailwindCSS v4 with custom design tokens
- **Build Tool**: Vite for asset bundling and HMR
- **Testing**: Playwright for E2E, Pest for backend, Vitest for JS utilities

### Key Dependencies

```json
{
  "dependencies": {
    "alpinejs": "^3.x",
    "tailwindcss": "^4.x",
    "vite": "^5.x"
  },
  "devDependencies": {
    "@playwright/test": "^1.x",
    "vitest": "^1.x",
    "fast-check": "^3.x"
  }
}
```

## Storage Strategy

### Local Storage (localStorage)

**Purpose**: Anonymous users, offline-first usage, quick prototyping

**Characteristics**:

- Fully functional without network connectivity
- Limited by browser storage quotas (~5-10MB)
- Client-generated UUIDs for plan identification
- Versioned schema for future migrations

**Storage Keys**:

```javascript
const STORAGE_KEYS = {
  LOCAL_RUNS: 'uma_local_runs',           // LocalRunsStore
  DRAFTS: 'uma_drafts',                   // Record<string, DraftState>
  PREFERENCES: 'uma_preferences',          // UserPreferences
  DISMISSED_TOOLTIPS: 'uma_dismissed_tips', // string[]
  RECENT_SEARCHES: 'uma_recent_searches',  // string[]
  SAVED_VIEWS: 'uma_saved_views',          // SavedView[]
};
```

### Database Storage (Account_Runs)

**Purpose**: Authenticated users, cross-device sync, data persistence

**Characteristics**:

- Requires network connectivity for save operations
- Unlimited storage capacity
- Server-assigned numeric IDs
- Optimistic locking for conflict resolution

### Route Strategy

The application uses distinct routing patterns for each storage mode:

- **Account runs**: `/plans/{id}` and `/plans/{id}/edit` (numeric database ID)
- **Local runs**: `/plans/local/{uuid}` and `/plans/local/{uuid}/edit` (client-generated UUID)

## Component Architecture

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
| `LocalDataManager` | `/local-data` | Local storage management interface |

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

## Data Models

### Core Data Structures

#### CareerRun (Plan)

The primary entity representing a training career run:

```typescript
interface CareerRun {
  // Identifiers
  id: number | null;       // Database ID for Account runs, null for Local runs
  uuid: string;            // Client-generated UUID for Local runs
  storage_mode: 'local' | 'account';
  
  // Basic Info
  title: string;
  character_id: number | null;
  character_name: string;
  status: 'in_progress' | 'completed' | 'archived';
  career_stage: 'junior' | 'classic' | 'senior';
  current_turn: number;
  
  // Current Stats
  speed: number;
  stamina: number;
  power: number;
  guts: number;
  wit: number;
  
  // Growth Rates
  speed_growth: number;
  stamina_growth: number;
  power_growth: number;
  guts_growth: number;
  wit_growth: number;
  
  // Aptitudes (SS, S, A, B, C, D, E, F, G)
  turf_aptitude: AptitudeGrade;
  dirt_aptitude: AptitudeGrade;
  sprint_aptitude: AptitudeGrade;
  mile_aptitude: AptitudeGrade;
  medium_aptitude: AptitudeGrade;
  long_aptitude: AptitudeGrade;
  nige_aptitude: AptitudeGrade;    // Front Runner
  senkou_aptitude: AptitudeGrade;  // Pace Chaser
  sashi_aptitude: AptitudeGrade;   // Late Surger
  oikomi_aptitude: AptitudeGrade;  // End Closer
  
  // Status
  mood: 'great' | 'good' | 'normal' | 'bad' | 'awful';
  conditions: Condition[];
  energy: number;
  total_sp_available: number;
  stamina_percentage: number;
  
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
```

#### Skill System

Skills are categorized by type and tier, with SS tier reserved for maximum stat requirements:

```typescript
interface Skill {
  id: string;
  name: string;
  name_jp: string | null;
  sp_cost: number;
  tier: 'G-' | 'G' | 'G+' | 'F-' | 'F' | 'F+' | 'E-' | 'E' | 'E+' | 
        'D-' | 'D' | 'D+' | 'C-' | 'C' | 'C+' | 'B-' | 'B' | 'B+' | 
        'A-' | 'A' | 'A+' | 'S-' | 'S' | 'S+' | 'SS';
  type: 'speed' | 'stamina' | 'power' | 'guts' | 'wit' | 'debuff';
  status: 'acquired' | 'skipped' | 'suggested';
  turn_acquired: number | null;
  notes: string;
}
```

#### Turn Tracking

Turn-by-turn progression tracking with milestone support:

```typescript
interface Turn {
  id: string;
  turn_number: number;
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
  milestone_name: string | null; // e.g., "Summer Camp", "URA Finale"
}
```

### Data Mapping Rules

**Current Stats Source of Truth**:

- The `speed`, `stamina`, `power`, `guts`, `wit` fields on `CareerRun` represent the current snapshot
- When a new turn is added, the run's current stats should be updated to match the turn's stats
- Charts display turn history; stat displays show current values from the run

**Effective Stats Calculation**:

```typescript
// Hard max at 1200
function validateStat(rawValue: number): boolean {
  const HARD_MAX = 1200;
  return rawValue >= 0 && rawValue <= HARD_MAX;
}
```

**Skill SP Totals**:

- `acquired_sp`: Sum of `sp_cost` for skills where `status === 'acquired'`
- `planned_sp`: Sum of `sp_cost` for skills where `status === 'suggested'`
- `total_sp_available`: User-entered current SP available (not auto-calculated)

## User Interface

### Design System

#### Color Palette

The application uses game-accurate colors for stats, grades, and UI elements:

```javascript
// Stat colors (game-accurate)
const statColors = {
  speed: '#3399ff',    // Blue
  stamina: '#33cc99',  // Green
  power: '#ff4d4d',    // Red
  guts: '#ffa500',     // Orange
  wit: '#9933ff',      // Purple
};

// Aptitude grade colors
const gradeColors = {
  SS: '#e5e7eb',  // Platinum/Light Gray
  S: '#ffd700',   // Gold
  A: '#ef4444',   // Red
  B: '#f97316',   // Orange
  C: '#22c55e',   // Green
  D: '#3b82f6',   // Blue
  E: '#a855f7',   // Purple
  F: '#6b7280',   // Gray
  G: '#9ca3af',   // Dark Gray
};

// Mood colors
const moodColors = {
  great: '#22c55e',   // +4%
  good: '#84cc16',    // +2%
  normal: '#6b7280',  // 0%
  bad: '#f97316',     // -2%
  awful: '#ef4444',   // -4%
};
```

#### Typography and Layout

- **Heading Hierarchy**: h1 for page titles, h2 for sections, h3 for subsections
- **Line Length**: Limited to 65-75 characters for optimal readability
- **Line Height**: 1.5 for body text
- **Touch Targets**: Minimum 44x44 pixels for mobile accessibility

### Key UI Components

#### Dashboard Layout

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
```

#### Plan Editor Interface

```
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

#### Circular Progress Component

A game-inspired visual component for displaying stats:

```html
<div 
  x-data="{ 
    value: {{ $value }}, 
    max: {{ $max ?? 1200 }},
    animatedValue: 0 
  }"
  x-init="
    $nextTick(() => {
      if (!$store.preferences.reducedMotion) {
        // Animate from 0 to value over 1 second
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
>
  <!-- SVG circular progress implementation -->
</div>
```

### Responsive Design

The application follows a mobile-first approach:

- **Mobile (< 768px)**: Single-column layout, hamburger navigation
- **Tablet (768px - 1024px)**: Adaptive two-column layout
- **Desktop (> 1024px)**: Full multi-column layout with sidebars

## Accessibility

### WCAG AA Compliance

The application meets WCAG 2.1 Level AA standards:

#### Perceivable (WCAG 1.x)

- **1.1.1**: Alt text for all images and icons
- **1.4.1**: Color information available through text
- **1.4.3**: 4.5:1 contrast ratio for normal text
- **1.4.4**: Text resizable up to 200% without loss of functionality
- **1.4.10**: Content reflows at 400% zoom

#### Operable (WCAG 2.x)

- **2.1.1**: All functionality available via keyboard
- **2.1.2**: No keyboard traps
- **2.4.1**: Skip-to-main-content link
- **2.4.7**: Visible focus indicators

#### Understandable (WCAG 3.x)

- **3.1.1**: Page language specified in HTML
- **3.2.1**: No unexpected context changes on focus
- **3.3.1**: Input errors identified and described

#### Robust (WCAG 4.x)

- **4.1.1**: Valid HTML markup
- **4.1.2**: Proper name, role, value for UI components
- **4.1.3**: Status messages announced to assistive technologies

### Accessibility Features

#### Keyboard Navigation

- Tab navigation through all interactive elements
- Keyboard shortcuts: N (new plan), Ctrl+S (save), Escape (close modal), ? (help)
- Focus management for modals and dynamic content

#### Screen Reader Support

- Semantic HTML elements (nav, main, section, article)
- ARIA labels and descriptions where needed
- Table headers properly associated with data cells
- Form labels explicitly associated with inputs

#### Reduced Motion Support

- Respects `prefers-reduced-motion: reduce`
- Manual "Reduce Motion" toggle in settings
- Alternative tabular views for animated charts

## Performance

### Optimization Strategies

#### Frontend Performance

- **Lazy Loading**: Images and heavy components loaded on demand
- **Code Splitting**: Route-based chunks with prefetching on hover
- **Virtualized Lists**: For plan lists exceeding 50 items
- **Service Worker**: Caching for static assets

#### Livewire Optimization

- **Selective Updates**: Use `wire:model.blur` instead of `wire:model.live`
- **Loading States**: Visual feedback during server communication
- **Debounced Inputs**: 300ms debounce for search inputs
- **Lazy Loading**: `wire:init` for heavy data components

#### Performance Targets

- **First Contentful Paint (FCP)**: < 1.5 seconds
- **Time to Interactive (TTI)**: < 3 seconds
- **Largest Contentful Paint (LCP)**: < 2.5 seconds

### Caching Strategy

#### Client-Side Caching

- **localStorage**: User preferences, drafts, Local_Runs
- **Browser Cache**: Static assets via service worker
- **Component Cache**: Character roster and skill database (24-hour TTL)

#### Server-Side Caching

- **Laravel Cache**: Reference data (characters, skills)
- **Query Optimization**: Eager loading for relationships
- **Database Indexing**: Optimized queries for plan lists

## Testing

### Testing Strategy

The application employs a comprehensive testing approach:

#### Unit Tests (Pest)

- Backend logic, services, models
- Data validation and transformation
- Business rule enforcement

#### Component Tests (Livewire)

- Individual component behavior
- State management and updates
- Event handling and communication

#### End-to-End Tests (Playwright)

- Critical user flows
- Cross-browser compatibility
- Visual regression testing

#### Accessibility Tests

- axe-core integration for WCAG compliance
- Keyboard navigation testing
- Screen reader compatibility

### Test Configuration

#### E2E Test Example

```javascript
// tests/e2e/critical-flows.spec.js
import { test, expect } from '@playwright/test';

test.describe('Critical User Flows', () => {
    test('Dashboard loads and displays plans', async ({ page }) => {
        await page.goto('/dashboard');
        await expect(page.getByTestId('plan-list')).toBeVisible();
        await expect(page.getByTestId('stats-panel')).toBeVisible();
    });
    
    test('Create local plan flow', async ({ page }) => {
        await page.goto('/dashboard');
        await page.getByTestId('plan-create-button').click();
        
        await page.getByTestId('plan-title-input').fill('Test Plan');
        await page.getByTestId('storage-mode-local').click();
        await page.getByTestId('plan-submit-button').click();
        
        // Local runs use UUID in /plans/local/{uuid} route
        await expect(page).toHaveURL(/\/plans\/local\/[a-f0-9-]+\/edit/);
    });
});
```

#### Accessibility Test Example

```javascript
// tests/e2e/accessibility.spec.js
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('Dashboard meets WCAG AA', async ({ page }) => {
    await page.goto('/dashboard');
    
    const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa'])
        .analyze();
    
    expect(results.violations).toEqual([]);
});
```

### Data-testid Attributes

All interactive elements include `data-testid` attributes for stable test selectors:

```html
<!-- Consistent naming convention -->
<button data-testid="plan-create-button">Create Plan</button>
<input data-testid="plan-title-input" />
<div data-testid="plan-card-123">Plan Card</div>
<div data-testid="skill-row-0">Skill Row</div>
```

## Development Guidelines

### Code Organization

#### File Structure

```
resources/
├── css/
│   ├── app.css              # Main stylesheet
│   └── components/          # Component-specific styles
├── js/
│   ├── app.js              # Main JavaScript entry
│   ├── alpine/             # Alpine.js components
│   └── utils/              # Utility functions
└── views/
    ├── components/         # Blade components
    ├── livewire/          # Livewire component views
    └── layouts/           # Layout templates

app/
├── Livewire/              # Livewire components
├── Models/                # Eloquent models
├── Services/              # Business logic services
└── View/
    └── Components/        # Blade components
```

#### Naming Conventions

**Livewire Components**:

- PascalCase for class names: `PlanList`, `SkillsEditor`
- kebab-case for view files: `plan-list.blade.php`

**Alpine.js Components**:

- Prefixed with `x-`: `x-dropdown`, `x-modal`
- camelCase for properties: `activeTab`, `isOpen`

**CSS Classes**:

- TailwindCSS utilities preferred
- Custom classes in kebab-case: `.plan-card`, `.skill-row`

### Best Practices

#### Livewire Best Practices

1. Use `wire:model.blur` instead of `wire:model.live` for form inputs
2. Implement loading states with `wire:loading`
3. Use `wire:key` on repeated elements for proper DOM diffing
4. Debounce search inputs with `wire:model.debounce.300ms`
5. Lazy load heavy components with `wire:init`

#### Alpine.js Best Practices

1. Use Alpine for client-side interactions only
2. Use `x-cloak` to prevent flash of unstyled content
3. Communicate with Livewire via `$wire` when needed
4. Prefer `x-show` over Livewire re-renders for visibility toggles
5. Use `x-transition` for smooth animations

#### TailwindCSS Best Practices

1. Use design tokens consistently throughout the application
2. Extract repeated patterns into Blade components
3. Use responsive prefixes for mobile-first design
4. Define custom colors in `tailwind.config.js`
5. Use group and peer utilities for interactive states

### Error Handling

#### Client-Side Error Handling

```javascript
// Global error boundary for JavaScript errors
window.addEventListener('error', (event) => {
    console.error('JavaScript error:', event.error);
    // Report to monitoring service if configured
});

// Livewire error handling
document.addEventListener('livewire:init', () => {
    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 0) {
                // Network error
                showConnectionLostBanner();
                preventDefault();
            }
        });
    });
});
```

#### Server-Side Error Handling

```php
// Livewire component error handling
trait HandlesErrors
{
    public function handleLivewireError(\Exception $e)
    {
        Log::error('Livewire error', [
            'component' => get_class($this),
            'message' => $e->getMessage(),
        ]);
        
        $this->dispatch('toast', [
            'type' => 'error',
            'message' => __('An error occurred. Please try again.'),
        ]);
    }
}
```

### Security Considerations

#### Input Validation

- All user input validated on both client and server
- CSRF protection on all form submissions
- XSS prevention through proper output escaping

#### File Upload Security

- Content-type validation for image uploads
- File size limits (2MB maximum)
- Image resizing for large uploads

#### Data Protection

- Per-user plan isolation for authenticated users
- Rate limiting on API endpoints
- Audit logging for sensitive operations

---

## Conclusion

The Uma Musume Race Planner frontend represents a comprehensive solution for career tracking in Uma Musume: Pretty Derby. Built with modern web technologies and following accessibility best practices, it provides a robust, performant, and user-friendly experience for both anonymous and authenticated users.

The dual storage architecture enables flexible usage patterns while maintaining data integrity and user privacy. The component-based architecture ensures maintainability and scalability as the application continues to evolve.

For developers working on this application, following the established patterns and guidelines will ensure consistency and quality across all features and enhancements.
