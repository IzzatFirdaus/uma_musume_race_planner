# UMA MUSUME PLANNER — Laravel

Unified design, as-built documentation, and frontend enhancement plan for the Uma Musume Race Planner (Uma Tracker) — consolidated and updated for the Laravel-based project with official Umamusume: Pretty Derby game mechanics.

---

## Table of Contents

- SPEC-05 — UI Design (Uma Tracker)
  - Purpose & Audience
  - Visual & UX Inspirations
  - Layout & Component Designs
  - Branding & Aesthetic
  - Responsive Strategy
  - Component Blueprint
  - Iconography & Fonts
  - Accessibility & UX
  - Prototyping & Implementation Flow
  - Example Career Run Form Layout (Mobile)
  - Summary
- SPEC-06 — As-Built Frontend Documentation
  - Background & Requirements
  - Stat Color Summary
  - Layout & Component Designs (As-Built)
  - Plan Details Inline (Livewire / Blade examples)
  - Energy Bar & Dark Mode
  - Implementation — File Highlights & Milestones
  - Results Gathering & Analytics
- SPEC-07 — Frontend Enhancement Proposal (v2.0)
  - Executive Summary
  - Visual Identity & Theming
  - Dashboard Modernization
  - Intelligent Plan Editor
  - Advanced Data Visualization Suite
  - Technical Implementation Plan & Timeline
  - File Modification Overview
  - Game-Integration Considerations & Success Metrics
- Layout System Implementation — Complete Documentation
  - Overview & Architecture
  - Blade Partials & Livewire Components
  - Main Layout Templates
  - Accessibility & Theme Support
  - Usage Examples
  - Asset Management & Performance
  - Testing & Maintenance
  - Implementation Status
- Appendix — Official Game Mechanics Integration
  - Stat Calculations & Caps
  - Race Phase System
  - Terrain & Condition Effects
  - Skill System Implementation
  - Inheritance & Legacy Systems

---

# SPEC-05 — UMA-TRACKER UI DESIGN

## 1. Purpose & Audience

Primary users: Umamusume: Pretty Derby players who want to log training runs, track stats/skills, and plan SP usage according to official game mechanics .

Goals:

- Streamlined logging of training progress with official stat calculations
- Accurate skill planning interface with official SP costs and activation requirements
- Exporting data in formats compatible with community tools
- Clean, engaging UI styled after the game's official aesthetic

## 2. Visual & UX Inspirations from the Game

### Key UI Elements from Umamusume: Pretty Derby

| Feature              | Description                                                     |
| -------------------- | --------------------------------------------------------------- |
| Character-Centric UI | Official character portraits and color schemes                  |
| Stat Bars & Icons    | Visual stat meters with official color coding                   |
| Training Interface   | Mimic official training selection UI with support card displays |
| Race Simulation      | Implement official race phase visualization                     |

Design takeaway: Adopt official stat colors (Speed = blue, Stamina = green, etc.) and implement the four-phase race system (Early-Race, Mid-Race, Late-Race, Last Spurt) .

## 3. Layout & Component Designs

### 3.1 Dashboard

- Header: Welcome section with daily login bonus concept
- Quick cards: Rounded, colored buttons linking to Character List, Training Plans, Race Scheduler
- Layout: Mobile-first grid → 1 column; desktop → 3 columns
- Training preview: Show current motivation level (Great, Good, Normal, etc.)

### 3.2 Character List

- Title: "Umamusume Roster"
- Buttons: Preview and Export links styled with official colors
- Cards: Name, running style, distance aptitudes (S, A, B, etc.)
- Grid: Responsive (1-3 columns)
- Design: Hover transitions with character-specific colors

### 3.3 Training Plan Form

Form Sections:

- Character info: Name, motivation level, condition
- Stats: Inputs with official stat caps (1200 effective cap, 2000 absolute max)
- Aptitude ratings: Dropdowns with official grade icons (S, A, B, C, D, E, F, G)
- Skills section: Dynamic rows with official skill names, costs, and types
- Inheritance section: Legacy parent selection and inherited stats
- Layout: Tabbed interface matching training year structure

## 4. Branding & Aesthetic

- Color system: Map stat categories to official game palette :
  - Speed = #3399ff (blue), Stamina = #33cc99 (green), Power = #ff4d4d (red)
  - Guts = #ffa500 (orange), Wisdom = #9933ff (purple)
- Icons: Use official-style stat icons (⚡, 🛡️, 🔥, 💪, 🧠)
- Typography: Modern sans-serif (Montserrat for headings, Figtree for body)
- Animations: Subtle transitions mimicking game animations

## 5. Responsive Strategy

- Mobile-first grid: Flex layout shifting from single to multi-column
- Touch targets: Ensure buttons and inputs ≥ 44px for mobile usability
- Form UI: Accordion sections for training years on mobile
- Fixed footer: Mobile action buttons for quick saving

## 6. Component Blueprint

- `character-card.blade.php`: Character card with official data
- `stat-meter.blade.php`: Displays colored stat with official caps
- `skill-row.blade.php`: Official skill data with cost and activation conditions
- `training-year.blade.php`: Yearly training section with turn tracking
- `inheritance-selector.blade.php`: Legacy parent selection interface

## 7. Iconography & Fonts

- Custom icons: Game-style visual stat icons
- Font pairing:
  - Headlines: Montserrat (similar to official game font)
  - Body: Figtree or system UI font
- Buttons: Rounded corners with official color scheme

## 8. Accessibility & UX

- Color contrast: Follow WCAG AA with official colors
- Form labels: Always visible with validation
- Keyboard navigation: Full support for form navigation

## 9. Prototyping & Implementation Flow

- Wireframe mockups: Dashboard, character list, training form
- Component development: Stat meters, skill inputs, inheritance UI
- UI review: Ensure mobile and desktop consistency

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

Design focused on:

- Official Umamusume: Pretty Derby aesthetics and mechanics
- Responsive, accessible UX
- Accurate game mechanics implementation
- Comprehensive training planning

---

# SPEC-06 — UMA-MUSUME UI (AS-BUILT)

## Background

This documents the current frontend structure and styling of the Umamusume Race Planner as implemented (as-built). The implementation uses Bootstrap 5, Livewire, and official game mechanics.

### Requirements (As-Built)

Must have:

- Bootstrap-based responsive UI
- Official stat colors and calculations
- Training form with official race phase system
- Mobile-friendly interaction
- Export plans in community-standard formats

Should have:

- Dynamic skill rows with official data
- Plan detail modals with inheritance tracking
- Motivation level tracking
- Themed dark mode toggle

Could have:

- Turn-based growth calculators
- Official character avatar support
- Aptitude rating visuals

Won't have:

- Copyrighted game assets (use original assets)

## Stat Color Summary (As-Built)

CSS variables define official stat colors:

- --color-speed: #3399ff (blue)
- --color-stamina: #33cc99 (green)
- --color-power: #ff4d4d (red)
- --color-guts: #ffa500 (orange)
- --color-wisdom: #9933ff (purple)

These are applied to sliders, badges, and progress bars.

## Layout & Component Designs (As-Built)

- Grid Layout: Bootstrap container > row > col split
- Left Column: Plan List Livewire component
- Right Column: Stats panel, recent activity, support summary
- Action Buttons: Bootstrap icons for edit, view, delete

### Plan Details Inline (Livewire example)

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

## Motivation Tracking (As-Built)

Motivation level selector with official multipliers:

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

Custom CSS variables with official color adaptations:

```css
:root {
    --color-stat-speed: #3399ff;
    --color-stat-stamina: #33cc99;
    --color-stat-power: #ff4d4d;
    --color-stat-guts: #ffa500;
    --color-stat-wisdom: #9933ff;
    /* Official motivation colors */
    --color-motivation-great: #4caf50;
    --color-motivation-good: #8bc34a;
    --color-motivation-normal: #ffeb3b;
    --color-motivation-bad: #ff9800;
    --color-motivation-awful: #f44336;
}

body.dark-mode {
    --color-bg: #121212;
    --color-text: #f1f1f1;
    /* Dark mode adaptations of official colors */
}
```

## Implementation — File Highlights (As-Built)

- `resources/views/livewire/dashboard/header-banner.blade.php`
- `resources/views/livewire/dashboard/plan-inline-details.blade.php`
- `resources/views/livewire/training-form.blade.php`
- `resources/views/livewire/dashboard/stats-panel.blade.php`
- `resources/views/livewire/layout/footer.blade.php`
- `resources/js/official-mechanics.js`
- `resources/js/skill_manager.js`
- `resources/css/official-styles.css`

### Milestones (As-Built)

- Bootstrap 5 integration — ✅
- Official stat color system — ✅
- Training form with motivation tracking — ✅
- Skill system with official data — ✅
- Dark mode toggle — ✅
- Export functionality — ✅

## Results Gathering & Analytics (As-Built)

Evaluation checklist completed:

- Mobile rendering verified
- Dark mode persisted per session
- Export feature working
- Official mechanics validation
- Livewire reactivity in place

Recommended analytics:

- Track training plan completion rates
- Monitor skill selection trends
- Measure export usage patterns

---

# SPEC-07 — FRONTEND ENHANCEMENT PROPOSAL (V2.0)

## Executive Summary

A plan to enhance UI polish, streamline data entry, and add analytical features to make Uma Planner feel premium and closely aligned with the official game's aesthetic and mechanics.

## 1. Visual Identity & Theming System

### Typography

- Add official-style fonts (Montserrat for headings, Roboto for body)
- Update CSS variables with official color scheme:

```css
:root {
    --font-heading: "Montserrat", sans-serif;
    --font-body: "Roboto", sans-serif;
    --color-primary: #5e17eb;
    --color-secondary: #ff3e9d;
    --color-ura: #e91e63; /* URA Finals accent color */
}
```

### Iconography

- Custom SVG icons matching official game style
- Stat icons with official color coding

### Color & Styles

- Official color palette for all UI elements
- WCAG AA contrast compliance for accessibility

## 2. Dashboard Modernization

- Visual plan summaries with official stat caps
- Advanced filtering by character, running style, distance aptitude
- Real-time search with official terminology highlighting

## 3. Intelligent Plan Editor

- Official skill autocomplete with rarity colors
- Skill comparison with activation conditions
- Synced stat inputs with official caps (1200/2000)
- Template system for common training approaches

## 4. Advanced Data Visualization

- Turn history growth charts with motivation effects
- Race performance analytics with phase breakdowns
- Training efficiency metrics with official calculations

## 5. Technical Implementation Plan (Timeline)

Phase 1 (Weeks 1-2): Official theming, fonts, icons
Phase 2 (Weeks 3-4): Dashboard improvements with official data
Phase 3 (Weeks 5-6): Editor improvements with official skills
Phase 4 (Weeks 7-8): Data visualization with race phase analytics
Phase 5 (Weeks 9-10): Polish, testing, and user feedback

## 6. File Modification Overview

High-priority files:

- `official-style.css`, `training-form.php`, `skill-database.php`
- `stat-calculator.js`, `race-simulator.js`

Lower-priority:

- Analytics endpoints and visualization components

## 7. Game-Integration Considerations

- Use official terminology and mechanics without copyrighted assets
- Community API integration for skill data
- Open-source icon sets inspired by official style

## 8. Success Metrics

- Reduced time to create accurate training plans
- Increased user engagement with official mechanics
- Positive feedback on authenticity and accuracy

---

# Layout System Implementation — Complete Documentation

## Overview

Provides both traditional Blade partials and interactive Livewire components with official Umamusume: Pretty Derby mechanics integration.

## Architecture

### Blade Partials (`resources/views/layouts/partials/`)

- `navbar.blade.php` - Updated with official terminology
- `footer.blade.php` - Official links and disclaimer
- `stats-display.blade.php` - Official stat calculations
- `motivation-indicator.blade.php` - Motivation level display

### Livewire Components

- `training-tracker.blade.php` + `TrainingTracker.php`
- `skill-manager.blade.php` + `SkillManager.php`
- `inheritance-calculator.blade.php` + `InheritanceCalculator.php`

### Main Layout Templates

- `layouts/official.blade.php` — Official-style layout
- `components/layouts/training.blade.php` — Training-specific layout

## Features Implemented

### Accessibility

- ARIA roles and labels with official terminology
- Semantic HTML5 layout
- Keyboard navigation support

### Theme Support

- Official color scheme with dark/light variants
- CSS custom properties for theming
- Motivation-level color coding

### Responsive Design

- Mobile-first layout with official style adaptations
- Touch-friendly controls for mobile

### Interactive Features

- Alpine.js for client-side reactivity
- Livewire for server-driven components
- Smooth transitions matching game animations

## Usage Examples

### Traditional Blade Partial

```blade
@extends('layouts.official')

@section('content')
  @include('layouts.partials.motivation-indicator')
  <div class="container">
    {{-- Training content --}}
  </div>
@endsection
```

### Livewire Component Usage

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

### CSS

- Bootstrap 5.3 with official color overrides
- Custom CSS with official mechanics
- Component-specific styles

### JavaScript

- Alpine.js for lightweight interactivity
- Livewire for real-time components
- Official mechanics calculations

## Performance Considerations

- Efficient stat calculations
- Minimal DOM manipulation
- Browser caching optimization

## Testing Coverage

- Playwright tests for core training flows
- Manual testing of official mechanics
- Cross-browser compatibility testing

## Browser Compatibility

- Chrome/Edge, Firefox, Safari modern versions
- Progressive enhancement approach

## Maintenance & Customization

- Keep official mechanics updated
- Customize with new game features
- Extend Livewire components

## Implementation Status

- Traditional Blade layout system — Completed
- Livewire interactive components — Completed
- Official mechanics implementation — Completed
- Production-ready for core functionality

---

# Appendix — Official Game Mechanics Integration

## Stat Calculations & Caps

- Base stats capped at 2000, with values past 1200 halved in calculations
- Motivation multipliers: Great (+4%), Good (+2%), Normal (±0%), Bad (-2%), Awful (-4%)
- Training bonuses applied before motivation multipliers

## Race Phase System

Implemented the four-phase race system:

1. **Early-Race** (序盤): First 1/6 of race distance
2. **Mid-Race** (中盤): Next 3/6 of race distance (2nd-4th sixth)
3. **Late-Race** (終盤): 5th sixth of race distance
4. **Last Spurt** (ラストスパート): Final sixth of race distance

## Terrain & Condition Effects

- Turf vs. dirt track differences
- Terrain conditions: Firm (良), Good (稍重), Soft (重), Heavy (不良)
- Stat reductions based on terrain and condition
- HP consumption increases on soft/heavy terrain

## Skill System Implementation

- Official skill types: Unique, Speed, Acceleration, Recovery, etc.
- Skill activation based on Wisdom stat and conditions
- Gold skill upgrades and evolution conditions
- Debuff skills and detrimental skills implementation

## Inheritance & Legacy Systems

- 3-star spark inheritance (+21 stats per spark)
- Parent selection and stat inheritance
- Proficiency spark inheritance for aptitude improvements
- Multiple generation legacy planning

---

## Quick Start (Local Development)

1. Clone repository:

```bash
git clone https://github.com/your-org/uma-planner.git
cd uma-planner
```

2. Install dependencies:

```bash
composer install
npm install
npm run build
```

3. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
# Update DB credentials in .env
php artisan migrate --seed
```

4. Serve:

```bash
php artisan serve
```

Visit: <http://127.0.0.1:8000>

---

## License

MIT — Free for personal and academic use. Not affiliated with Cygames or Umamusume: Pretty Derby official products.

This updated documentation incorporates official Umamusume: Pretty Derby game mechanics, terminology, and systems based on the comprehensive information available from official sources and community research . The implementation now accurately reflects stat calculations, race mechanics, skill systems, and training approaches used in the actual game.
