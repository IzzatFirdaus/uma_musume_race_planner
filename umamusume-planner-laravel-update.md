# UMA MUSUME PLANNER — Laravel

Unified design, as-built documentation, and frontend enhancement plan for the Uma Musume Race Planner (Uma Tracker) — consolidated and updated for the Laravel-based project, with accurate integration of official Umamusume: Pretty Derby game mechanics.

---

## Table of Contents

- SPEC-05 — UI Design (Uma Tracker)
- SPEC-06 — As-Built Frontend Documentation
- SPEC-07 — Frontend Enhancement Proposal (v2.0)
- Layout System Implementation — Complete Documentation
- Appendix — Official Game Mechanics Integration

---

# SPEC-05 — UMA-TRACKER UI DESIGN

## 1. Purpose & Audience

Primary users: Umamusume: Pretty Derby players seeking to log training runs, track stats/skills, and plan SP usage per official game mechanics.

**Goals:**

- Streamlined logging of training progress with official stat calculations.
- Accurate skill planning interface reflecting official SP costs and activation requirements.
- Exporting data in formats compatible with community tools (Excel, TXT).
- Clean, engaging UI styled after the game's official aesthetic.

## 2. Visual & UX Inspirations from the Game

### Key UI Elements from Umamusume: Pretty Derby

| Feature            | Description                                                            |
| ------------------ | ---------------------------------------------------------------------- |
| Character-Centric  | Official character portraits, color schemes, and star ratings          |
| Stat Bars & Icons  | Stat meters with official color coding, icons, and caps                |
| Training Interface | Training selection UI with motivation/morale indicator, support cards  |
| Race Simulation    | Four-phase race breakdown visualization (Early, Mid, Late, Last Spurt) |

**Design Takeaway:**  
Adopt official stat colors (Speed = blue, Stamina = green, etc.), and implement the four-phase race system (Early-Race, Mid-Race, Late-Race, Last Spurt).

## 3. Layout & Component Designs

### 3.1 Dashboard

- Header: Welcome, daily login bonus mockup (for engagement)
- Quick cards: Links to Character List, Training Plans, Race Scheduler
- Layout: Mobile-first grid (1 col), desktop expands to 3 cols
- Training preview: Display current motivation (Great, Good, Normal, etc.)

### 3.2 Character List

- Title: "Umamusume Roster"
- Buttons: Preview/Export with official color scheme
- Cards: Name, running style, distance aptitudes (S–G)
- Grid: Responsive (1–3 cols)
- Design: Hover transitions with character-specific color accents

### 3.3 Training Plan Form

Sections:

- Character info: Name, portrait, motivation, condition
- Stats: Inputs with official stat caps (1200 effective, 2000 absolute)
- Aptitude ratings: Dropdowns with S–G grade icons
- Skills section: Dynamic rows with official skill names, SP cost, type, and activation text
- Inheritance: Parent selection and inherited stats + spark counts
- Layout: Tabs for each training year, accordion on mobile

## 4. Branding & Aesthetic

- **Color System:**
  - Speed = #3399ff (blue), Stamina = #33cc99 (green), Power = #ff4d4d (red),  
      Guts = #ffa500 (orange), Wisdom = #9933ff (purple)
- **Icons:** Official-style stat icons (⚡, 🛡️, 🔥, 💪, 🧠)
- **Typography:** Montserrat for headings, Figtree/Roboto for body
- **Animations:** Subtle transitions replicating game feel

## 5. Responsive Strategy

- Mobile-first grid, touch targets ≥ 44px
- Accordion sections for training years on mobile
- Fixed footer action buttons for quick saves

## 6. Component Blueprint

- `character-card.blade.php`: Official-style character data card
- `stat-meter.blade.php`: Stat bar with color, cap, and icon
- `skill-row.blade.php`: Skill selector with cost, type, activation
- `training-year.blade.php`: Per-year training, turns, and events
- `inheritance-selector.blade.php`: Legacy/parent selection UI

## 7. Iconography & Fonts

- Custom SVG icons matching official stat colors
- Font pairing: Montserrat (headings), Figtree/Roboto (body)
- Buttons: Rounded with colored backgrounds, drop-shadows

## 8. Accessibility & UX

- WCAG AA contrast
- Visible labels, inline validation
- Full keyboard navigation and ARIA roles

## 9. Prototyping & Implementation Flow

- Figma mockups: Dashboard, character list, training form
- Component development: Stat meters, skill rows, inheritance UI
- UI review: Ensure consistent, accessible UX

## 10. Example Training Plan Layout (Mobile)

```text
[Character portrait and name]

📅 Year 1 · Turn 3
Motivation: Great (+4% stats)

SPEED ⚡ [720 / 1200] ─────────▱────────
STAMINA 🛡️ [600 / 1200] ───────▱──────
...

Turf Aptitude: A  Dirt Aptitude: C
Distance Aptitude: Mile (A), Long (B)

Skills:
[ Speed Star | 180 SP | Rare | "Mid-Race speed boost" ]
[ + Add skill ]

Total SP: 340
Inheritance: +21 Speed from legacy parents

[ Save Plan ]
```

## 11. Summary

Focus:

- Official Umamusume: Pretty Derby aesthetics and mechanics
- Responsive, accessible UX
- Accurate, transparent game mechanics
- Comprehensive, community-compatible planning

---

# SPEC-06 — UMA-MUSUME UI (AS-BUILT)

## Background

Documents the as-built Laravel/Livewire/Bootstrap frontend, with official game mechanics and accurate stat calculations.

### Requirements (As-Built)

- Bootstrap-based responsive UI
- Official stat colors/caps, stat calculation rules
- Training form with race phase and motivation
- Mobile-friendly, community-standard exports
- Dynamic skill rows, inheritance, motivation tracking
- Dark mode toggle with official palette

## Stat Color Summary (As-Built)

CSS variables:

- --color-speed: #3399ff
- --color-stamina: #33cc99
- --color-power: #ff4d4d
- --color-guts: #ffa500
- --color-wisdom: #9933ff

Motivation colors (for selector, status badges):

- --color-motivation-great: #4caf50
- --color-motivation-good: #8bc34a
- --color-motivation-normal: #ffeb3b
- --color-motivation-bad: #ff9800
- --color-motivation-awful: #f44336

## Layout & Component Designs (As-Built)

- Bootstrap grid: left = plan list, right = stats/activity
- Action buttons: Bootstrap icons (edit, view, delete)

### Inline Plan Details (Livewire/Blade Example)

Skill row with official data:

```blade
<tr wire:key="skill-{{ $i }}">
    <td>
        <select class="form-select form-select-sm" wire:model.lazy="skills.{{ $i }}.name">
            <option value="">Select Skill</option>
            @foreach($officialSkills as $skill)
                <option value="{{ $skill['name'] }}" data-cost="{{ $skill['cost'] }}" data-type="{{ $skill['type'] }}">
                    {{ $skill['name'] }} ({{ $skill['cost'] }} SP)
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <span class="badge bg-{{ $skillTypeColor }}">{{ $skillType }}</span>
    </td>
    <td class="text-center">
        <input type="checkbox" class="form-check-input" wire:model.live="skills.{{ $i }}.acquired">
    </td>
    <td>
        <input type="text" class="form-control form-control-sm" placeholder="Activation Conditions" wire:model.lazy="skills.{{ $i }}.conditions">
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-outline-danger" title="Remove" wire:click="removeSkill({{ $i }})">
            <i class="bi bi-x"></i>
        </button>
    </td>
</tr>
```

Motivation selector:

```blade
<div class="mb-3">
    <label class="form-label">Motivation Level</label>
    <select class="form-select" wire:model="motivation">
        <option value="1.04">Great (+4%)</option>
        <option value="1.02">Good (+2%)</option>
        <option value="1.00" selected>Normal (±0%)</option>
        <option value="0.98">Bad (-2%)</option>
        <option value="0.96">Awful (-4%)</option>
    </select>
</div>
```

## Dark Mode Styling (As-Built)

```css
:root {
    --color-stat-speed: #3399ff;
    --color-stat-stamina: #33cc99;
    --color-stat-power: #ff4d4d;
    --color-stat-guts: #ffa500;
    --color-stat-wisdom: #9933ff;
    --color-motivation-great: #4caf50;
    --color-motivation-good: #8bc34a;
    --color-motivation-normal: #ffeb3b;
    --color-motivation-bad: #ff9800;
    --color-motivation-awful: #f44336;
}
body.dark-mode {
    --color-bg: #121212;
    --color-text: #f1f1f1;
}
```

## Implementation — File Highlights

- `resources/views/livewire/dashboard/header-banner.blade.php`
- `resources/views/livewire/training-form.blade.php`
- `resources/views/livewire/dashboard/stats-panel.blade.php`
- `resources/views/livewire/layout/footer.blade.php`
- `resources/js/official-mechanics.js`
- `resources/js/skill_manager.js`
- `resources/css/official-styles.css`

### Milestones (As-Built)

- Bootstrap 5 integration: ✅
- Official stat color system: ✅
- Training form with motivation: ✅
- Official skill system: ✅
- Dark mode toggle: ✅
- Export functionality: ✅

## Results Gathering & Analytics

- Mobile rendering verified
- Dark mode persistence
- Export working
- Official mechanics validation
- Livewire reactivity in place

**Analytics:**

- Track plan creation/completion rates
- Monitor skill selection
- Measure export usage

---

# SPEC-07 — FRONTEND ENHANCEMENT PROPOSAL (V2.0)

## Executive Summary

Enhancements for UI polish, streamlined data entry, and analytics, aligning closely with official Umamusume: Pretty Derby mechanics and terminology.

## 1. Visual Identity & Theming

- Official-style fonts (Montserrat/Roboto)
- CSS variables for official palette and URA Finals accent

## 2. Dashboard Modernization

- Visual plan summaries with stat caps
- Advanced filtering: character, running style, distance aptitude
- Real-time search with official term highlighting

## 3. Intelligent Plan Editor

- Official skill autocomplete (rarity colors)
- Skill comparison with activation info
- Stat inputs with official caps (1200/2000)
- Templates for common training patterns

## 4. Data Visualization

- Turn-by-turn stat growth with motivation effects
- Race analytics with phase breakdown
- Training efficiency metrics

## 5. Implementation Plan (Timeline)

- Weeks 1–2: Theming, fonts, icons
- Weeks 3–4: Dashboard w/ official data
- Weeks 5–6: Editor w/ official skills
- Weeks 7–8: Visualization (race analytics)
- Weeks 9–10: Polish, testing, feedback

## 6. File Modification Overview

- `official-style.css`, `training-form.php`, `skill-database.php`
- `stat-calculator.js`, `race-simulator.js`

## 7. Game-Integration Considerations

- Official terminology, mechanics, open-source icon sets

## 8. Success Metrics

- Faster plan creation
- Higher engagement with official mechanics
- Positive user feedback on authenticity

---

# Layout System Implementation — Complete Documentation

## Architecture

- Blade partials: `navbar`, `footer`, `stats-display`, `motivation-indicator`
- Livewire components: `training-tracker`, `skill-manager`, `inheritance-calculator`
- Main layouts: `layouts/official.blade.php`, `components/layouts/training.blade.php`

## Features

- Accessibility: ARIA roles, semantic HTML5, keyboard support
- Theme: Official colors, dark/light mode, motivation color coding
- Responsive: Mobile-first, touch-friendly controls
- Interactivity: Alpine.js, Livewire, official animation style

## Usage Examples

### Blade Partial

```blade
@extends('layouts.official')
@section('content')
  @include('layouts.partials.motivation-indicator')
  <div class="container">
    {{-- Training content --}}
  </div>
@endsection
```

### Livewire Component

```blade
<x-layouts.training>
  <livewire:training-tracker />
  <livewire:skill-manager />
  <div class="container-fluid">
    {{-- Training plan content --}}
  </div>
</x-layouts.training>
```

## Asset Management

- Bootstrap 5.3 w/ official overrides
- Custom CSS/JS with mechanics calculation

## Testing & Maintenance

- Playwright for core flows
- Manual validation of mechanics
- Keep game mechanics up to date

---

# Appendix — Official Game Mechanics Integration

## Stat Calculations & Caps

- Base stats capped at 2000, values past 1200 halved in effectiveness
- Motivation multipliers: Great (+4%), Good (+2%), Normal (±0%), Bad (-2%), Awful (-4%)
- Training bonuses applied before motivation

## Race Phase System

Four phases:

1. **Early-Race (序盤):** 1/6 race distance
2. **Mid-Race (中盤):** 2nd–4th sixths
3. **Late-Race (終盤):** 5th sixth
4. **Last Spurt (ラストスパート):** final sixth

## Terrain & Condition Effects

- Track: Turf/Dirt, affects stats/HP
- Conditions: Firm (良), Good (稍重), Soft (重), Heavy (不良)
- Stat reductions and HP drain for poor conditions

## Skill System Implementation

- Official skill types: Unique, Speed, Acceleration, Recovery, etc.
- Skill activation depends on Wisdom, position, conditions
- Gold skill upgrades, evolution, debuffs, detrimental skills

## Inheritance & Legacy

- 3-star spark inheritance: +21 stat per spark
- Parent/grandparent stat/aptitude inheritance
- Spark proficiency for aptitude upgrades
- Multi-generation legacy planning

---

## Quick Start (Local Development)

1. **Clone repository:**

    ```bash
    git clone https://github.com/your-org/uma-planner.git
    cd uma-planner
    ```

2. **Install dependencies:**

    ```bash
    composer install
    npm install
    npm run build
    ```

3. **Configure environment:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    # Update DB credentials in .env
    php artisan migrate --seed
    ```

4. **Serve:**

    ```bash
    php artisan serve
    ```

    Visit: <http://127.0.0.1:8000>

---

## License

MIT — Free for personal and academic use. Not affiliated with Cygames or Umamusume: Pretty Derby official products.

---

This documentation incorporates official Umamusume: Pretty Derby game mechanics, terminology, and systems based on official sources and community research. The implementation accurately reflects stat calculations, race mechanics, skill systems, and training approaches used in the actual game.
