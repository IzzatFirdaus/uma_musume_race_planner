# SPEC-06-UMA-MUSUME-UI-AS-BUILT

## Background

This specification documents the current frontend structure and styling implementation
of the Uma Musume Race Planner as it exists in production (as-built).
It reflects user-facing enhancements aligned with the look and feel of Uma Musume:
Pretty Derby, implemented using Bootstrap 5, vanilla JavaScript, and custom CSS
variables for dark mode and stat color theming.

---

## Requirements

### Must Have

- Bootstrap-based responsive UI

- Familiar stat colors and icons for Uma Musume players

- Dynamic row builders for skills, goals, predictions

| Stat color system (CSS variables) | ✅ |
| Plan editor UI tabs (Livewire) | ✅ |
| Skill table rows (Blade+JS) | ✅ |
| Dark mode with toggle (Alpine.js) | ✅ |

## Gathering Results

### Results Gathering: Evaluation Checklist

- Mobile rendering verified on Chrome and Safari
- UI follows contrast and touch accessibility
- Livewire reactivity and validation for all forms
- No copyright assets used

- Export/download events

## Stat Color Summary

### Component Design: Evaluation Checklist

| Stat | Color Code | CSS Var |

### Component Design: Recommended Analytics

## Component Design: Summary

| Speed | #3399ff | --color-speed |

| Stamina | #33cc99 | --color-stamina |
| Power | #ff4d4d | --color-power |
| Guts | #ffa500 | --color-guts |
| Wit | #9933ff | --color-wit |

- Applied to sliders, badges, and text borders dynamically in JS.

### 2. Layout & Component Designs (As-Built)

- (shows logo, welcome, stats)

- Grid Layout: Bootstrap container > row > col split
- Left Column: Plan List (`livewire/dashboard/plan-list.blade.php`),
  Recent Activity (`livewire/dashboard/recent-activity.blade.php`),
  Support Card Summary (`livewire/dashboard/support-card-summary.blade.php`)

    (`plan-list-theme`)

- Action Buttons: Edit, View, Delete use Bootstrap Icons (e.g.,
  `<i class="bi bi-pencil-square"></i>`, `<i class="bi bi-eye"></i>`,

#### 2.3 Plan Details Inline (`livewire/dashboard/plan-inline-details.blade.php`)

- Tabs for General, Attributes, Aptitude Grades, Skills, Predictions, Goals,
  Progress Chart (tabbed via `livewire/form-tabs.blade.php`)

```blade
<tr wire:key="skill-{{ $i }}">
    <td>
        <input
            type="text"
            class="form-control form-control-sm"
            placeholder="Skill name"
            wire:model.lazy="skills.{{ $i }}.name"
        >
    </td>
    <td>
        <input
            type="text"
            class="form-control form-control-sm"
            placeholder="Tag"
            wire:model.lazy="skills.{{ $i }}.tag"
        >
    </td>
    <td class="text-center">
        <input
            type="checkbox"
            class="form-check-input"
            wire:model.live="skills.{{ $i }}.acquired"
        >
    </td>
    <td>
        <input
            type="text"
            class="form-control form-control-sm"
            placeholder="Notes"
            wire:model.lazy="skills.{{ $i }}.notes"
        >
    </td>
    <td>
        <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            title="Remove"
            wire:click="removeSkill({{ $i }})"
        >
            <i class="bi bi-x"></i>
        </button>
    </td>
</tr>
```

```html
<tr>
    <td>
        <input
            name="skills[skill_123][name]"
            class="form-control skill-name-input"
        />
    </td>
    <td>
        <input name="skills[skill_123][sp_cost]" class="form-control" />
    </td>
    <td class="text-center">
        <input
            type="checkbox"
            name="skills[skill_123][acquired]"
            class="form-check-input"
        />
    </td>
    <td>
        <input type="text" class="form-control skill-tag" readonly />
    </td>
    <td>
        <input type="text" class="form-control skill-notes" />
    </td>
    <td>
        <button
            type="button"
            class="btn btn-sm btn-outline-danger remove-skill-btn"
        >
            <i class="bi bi-trash"></i>
        </button>
    </td>
</tr>
```

#### Energy Bar (Blade + JS)

```blade
<label for="energyRange{{ $id_suffix }}" class="form-label">Energy</label>
<div class="d-flex align-items-center gap-2">
    <input
        type="range"
        min="0"
        max="100"
        step="1"
        class="form-range"
        id="energyRange{{ $id_suffix }}"
        name="energyRange"
        wire:model.live="energy"
    >
    <span class="badge bg-secondary" id="energyValue{{ $id_suffix }}">
        {{ (int) ($energy ?? 0) }}
    </span>
</div>
```

#### Dark Mode Styling (`resources/css/style.css`)

```css
:root {
    --color-stat-stamina: #dc3545;
    --color-stat-power: #fd7e14;
    --color-stat-guts: #e83e8c;
    --color-stat-wit: #28a745;
    /* ...other variables... */
}

body.dark-mode {
    background-color: var(--color-bg-dark);
    color: var(--color-text-neon-dark);
    /* ...other overrides... */
}

---

## Implementation


### File Highlights (As-Built)

- `resources/views/livewire/dashboard/header-banner.blade.php`:
    Dashboard header banner (logo, welcome, stats)
- `resources/views/livewire/dashboard/plan-inline-details.blade.php`:
    Inline plan details editor (tabbed)
- `resources/views/livewire/form-tabs.blade.php`:
    Tabbed form for plan details (General, Skills, etc.)
- `resources/views/livewire/dashboard/stats-panel.blade.php`:
    Quick stats panel (total, active, finished)
- `resources/views/livewire/layout/footer.blade.php`:
    Footer (links, disclaimers, social)
- `resources/js/main.js`:
    Main dashboard JS (event handlers, dark mode, etc.)
- `resources/js/skill_management.js`:
    Skill table dynamic row logic


### Milestones

| Milestone                         | Status |
| --------------------------------- | ------ |
| Bootstrap 5 integration           | ✅     |
| Stat color system (CSS variables) | ✅     |
| Plan editor UI tabs (Livewire)    | ✅     |
| Skill table rows (Blade+JS)       | ✅     |
| Dark mode with toggle (Alpine.js) | ✅     |
| TXT export functionality          | ✅     |

---

## Results Gathering

### Results Gathering (Final): Evaluation Checklist

- Mobile rendering verified on Chrome and Safari
- Dark mode toggle persisted per session (Alpine.js + localStorage)
- Export feature works on inline and modal form
- UI follows contrast and touch accessibility
- Livewire reactivity and validation for all forms
- No copyright assets used

### Results Gathering (Final): Recommended Analytics

- Basic usage tracking via Plausible or Matomo
- Export/download events
- Skill tab interaction tracking

---

### Evaluation Checklist

- Mobile rendering verified on Chrome and Safari
- Dark mode toggle persisted per session (Alpine.js + localStorage)
- Export feature works on inline and modal form
- UI follows contrast and touch accessibility
- Livewire reactivity and validation for all forms
- No copyright assets used

### Recommended Analytics

- Basic usage tracking via Plausible or Matomo
- Export/download events
- Skill tab interaction tracking

---

## Results Gathering (Final): Summary

The Uma Musume Race Planner delivers a clean, mobile-friendly, game-inspired
interface for career tracking, with UX elements that resonate with fans of the
franchise. The as-built implementation leverages Laravel Blade, Livewire,
Bootstrap 5, custom CSS variables, and Alpine.js for dark mode. The result is a
robust, maintainable, and accessible user experience. All UI/UX features are
mapped to real files and components, ensuring the documentation matches the
production codebase.
```
