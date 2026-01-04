# Uma Musume Planner - Livewire Components Documentation

## Overview

This application uses Laravel Livewire v3.x for server-driven interactive components. Components are organized by feature domain and follow PSR-4 namespacing under `App\Livewire`.

## Component Directory Structure

```
app/Livewire/
├── Auth/                    # Authentication-related components
├── CareerRun/               # Career run management
├── Characters/              # Character (Uma Musume) management
├── Common/                  # Shared utility components
├── Dashboard/               # Dashboard widgets and panels
├── Export/                  # Export functionality
├── Layout/                  # Layout components (navbar, footer)
├── LocalData/               # Local storage management
├── Plans/                   # Plan detail components
├── Skills/                  # Skill management
└── [Root components]        # Standalone page components
```

---

## Auth Components

### ConvertRunModal

`App\Livewire\Auth\ConvertRunModal`

Modal dialog for converting a local run to an account-stored run.

**Props:**

- `localRunUuid` - UUID of the local run to convert

**Events Emitted:**

- `run-converted` - When conversion completes successfully

**Usage:**

```blade
<livewire:auth.convert-run-modal :local-run-uuid="$uuid" />
```

---

## CareerRun Components

### StorageModeIndicator

`App\Livewire\CareerRun\StorageModeIndicator`

Displays a badge indicating whether a run is stored locally or in the account.

**Props:**

- `storageMode` - Either `'local'` or `'account'`

**Usage:**

```blade
<livewire:career-run.storage-mode-indicator :storage-mode="$plan->storage_mode" />
```

### ConvertToAccountButton

`App\Livewire\CareerRun\ConvertToAccountButton`

Button to trigger conversion of a local run to account storage. Only visible when user is authenticated and viewing a local run.

**Props:**

- `planId` - The local plan UUID

**Events Emitted:**

- `open-convert-modal` - Opens the ConvertRunModal

---

## Characters Components

### CharacterList

`App\Livewire\Characters\CharacterList`

Paginated list of Uma Musume characters with search and filtering.

**Features:**

- Search by name (EN/JP)
- Pagination
- Quick view character details
- Link to create new character

**Usage:**

```blade
<livewire:characters.character-list />
```

---

## Common Components

### DarkModeToggle

`App\Livewire\Common\DarkModeToggle`

Toggle switch for dark/light mode with localStorage persistence.

**Features:**

- Respects system preference on first visit
- Persists choice to localStorage
- Applies `dark` class to `<html>` element

**Usage:**

```blade
<livewire:common.dark-mode-toggle />
```

### ConfirmModal

`App\Livewire\Common\ConfirmModal`

Reusable confirmation dialog with focus trap and keyboard support.

**Props:**

- `title` - Modal title
- `message` - Confirmation message
- `confirmText` - Confirm button text (default: "Confirm")
- `cancelText` - Cancel button text (default: "Cancel")
- `danger` - Boolean for destructive action styling

**Events:**

- Listens: `open-confirm-modal`
- Emits: `confirmed`, `cancelled`

**Usage:**

```blade
<livewire:common.confirm-modal
    title="Delete Plan"
    message="Are you sure?"
    :danger="true"
/>
```

### Toast

`App\Livewire\Common\Toast`

Notification toast with aria-live announcements for accessibility.

**Features:**

- Auto-dismiss after configurable duration
- Success, error, warning, info variants
- Screen reader announcements

**Events Listened:**

- `show-toast` with `{ type, message, duration }`

**Usage:**

```blade
<livewire:common.toast />

// Trigger from other components:
$this->dispatch('show-toast', type: 'success', message: 'Saved!');
```

### SkeletonLoader

`App\Livewire\Common\SkeletonLoader`

Loading placeholder for async content.

**Props:**

- `type` - `'text'`, `'card'`, `'table'`, `'chart'`
- `lines` - Number of lines for text type

**Usage:**

```blade
<livewire:common.skeleton-loader type="card" />
```

### DirtyStateWarning

`App\Livewire\Common\DirtyStateWarning`

Warns users about unsaved changes before navigation.

**Features:**

- Tracks form dirty state
- Shows warning on close/navigate
- Keyboard support (Esc to dismiss)

---

## Dashboard Components

### HeaderBanner

`App\Livewire\Dashboard\HeaderBanner`

Dashboard header with welcome message and quick actions.

### StatsPanel

`App\Livewire\Dashboard\StatsPanel`

Dashboard statistics panel showing:

- Total plans count
- Plans by storage mode (Local: X, Account: Y)
- Active runs count
- Recent activity summary

**Usage:**

```blade
<livewire:dashboard.stats-panel />
```

### RecentActivity

`App\Livewire\Dashboard\RecentActivity`

Recent activity feed showing user-scoped and local activities.

**Features:**

- User-scoped activities for authenticated users
- Local activities from localStorage
- Activity type indicators (Local vs Account)

### PlanList

`App\Livewire\Dashboard\PlanList`

Main plan listing with filtering, sorting, and pagination.

**Features:**

- Filter by status, year, storage mode
- Sort by date, name, progress
- Storage mode badges
- Inline actions (edit, delete, convert)

**Usage:**

```blade
<livewire:dashboard.plan-list />
```

### PlanInlineDetails

`App\Livewire\Dashboard\PlanInlineDetails`

Inline expandable panel for quick plan editing.

**Props:**

- `planId` - Plan ID to display

**Features:**

- Quick edit: status, turn, SP, stamina %, notes
- Skill toggle shortcuts
- Dirty state warning

### PlanDetailsPage

`App\Livewire\Dashboard\PlanDetailsPage`

Full plan detail page component.

### SupportCardSummary

`App\Livewire\Dashboard\SupportCardSummary`

Summary widget for support card information.

---

## Export Components

### ExportModal

`App\Livewire\Export\ExportModal`

Export dialog with format selection and preview.

**Props:**

- `planId` - Plan to export (or null for bulk)
- `planIds` - Array for bulk export

**Features:**

- Format selection (JSON, CSV, Markdown, Excel)
- Live preview with format switching
- Copy to clipboard with toast notification
- Download action

**Events Listened:**

- `open-export-modal`

**Usage:**

```blade
<livewire:export.export-modal :plan-id="$plan->id" />
```

---

## Layout Components

### Navbar

`App\Livewire\Layout\Navbar`

Main navigation bar with responsive mobile menu.

### Footer

`App\Livewire\Layout\Footer`

Page footer component.

### Alerts

`App\Livewire\Layout\Alerts`

Flash message display component.

### Modals

`App\Livewire\Layout\Modals`

Global modal container for portal-rendered modals.

---

## LocalData Components

### Manager

`App\Livewire\LocalData\Manager`

Local data management page at `/local-data`.

**Features:**

- List all local runs with storage info
- Export all local runs as JSON
- Import local runs from JSON
- Delete all local runs (with confirmation)
- Bulk convert to account (requires auth)

**Usage:**

```blade
<livewire:local-data.manager />
```

---

## Plans Components

### SkillRow

`App\Livewire\Plans\SkillRow`

Individual skill row in skill list with status management.

**Props:**

- `skill` - Skill data object
- `planId` - Parent plan ID

**Features:**

- 3-state status toggle (Acquired/Skipped/Suggested)
- Turn acquired input (required for Acquired)
- Notes field
- Remove action

### TrainingYear

`App\Livewire\Plans\TrainingYear`

Training year selector and display component.

---

## Skills Components

### SkillSearch

`App\Livewire\Skills\SkillSearch`

Accessible autocomplete for skill search.

**Features:**

- Debounced search (300ms)
- EN + JP name matching
- Keyboard navigation (↑/↓/Enter/Esc)
- ARIA listbox pattern
- Rate limited (60 req/min)

**Props:**

- `planId` - Plan to add skills to

**Events Emitted:**

- `skill-selected` with skill data

**Usage:**

```blade
<livewire:skills.skill-search :plan-id="$plan->id" />
```

### SkillEditor

`App\Livewire\Skills\SkillEditor`

Full skill management interface for a plan.

**Features:**

- Add skills via search
- 3-state status management
- SP totals calculation
- Filter by status
- Bulk actions

---

## Root Components

### QuickCreatePlan

`App\Livewire\QuickCreatePlan`

Quick create modal for rapid plan entry.

**Features:**

- Minimal required fields
- Character autocomplete
- Default values for quick start

### PlanDetails

`App\Livewire\PlanDetails`

Full plan detail view with tabbed interface.

**Tabs:**

- General - Basic info, status, notes
- Stats - Stat progression table and chart
- Skills - Skill management
- Racing - Race predictions and goals
- Snapshots - Race-day snapshots

### FormTabs

`App\Livewire\FormTabs`

Reusable tabbed form interface.

### GuideStickyNav

`App\Livewire\GuideStickyNav`

Sticky navigation for guide/documentation pages.

### TraineeImageHandler

`App\Livewire\TraineeImageHandler`

Image upload handler with preview and thumbnail generation.

---

## Component Conventions

### Naming

- PHP classes: PascalCase (`SkillSearch.php`)
- Blade views: kebab-case (`skill-search.blade.php`)
- View location: `resources/views/livewire/{domain}/{component}.blade.php`

### Events

- Use `$this->dispatch()` for emitting events
- Use `#[On('event-name')]` attribute for listeners
- Event names: kebab-case (`skill-selected`, `open-modal`)

### Testing

- Feature tests: `tests/Feature/Livewire/{Component}Test.php`
- Use `Livewire::test()` for component testing

### Accessibility

- All interactive elements have ARIA labels
- Focus management in modals
- Keyboard navigation support
- Screen reader announcements via aria-live
