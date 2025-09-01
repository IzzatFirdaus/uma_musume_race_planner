# UMA-TRACKER SYSTEM — VERSIONED DESIGN & IMPLEMENTATION HISTORY

This file records the iterative design and implementation planning for Uma-Tracker (Uma Musume Race Planner). It preserves the major design iterations so the team can track changes, decisions, and next steps.

---

# Uma-Tracker System: Design & Implementation Planning (VERSION 1)

## 1. Purpose & Scope

- **Audience:** Players of Uma Musume: Pretty Derby who wish to log and analyze career runs.
- **Goals:** 
	- Intuitive turn-by-turn stat and skill logging
	- Skill planning and SP management
	- Data export (Excel/spreadsheet)
	- Game-inspired, responsive UI
	- Accessibility, mobile-first

## 2. Core Visual & UX Inspirations

- Bright character cards; stat bars with color coding
- Tabbed/segmented layouts for modes/screens
- Portrait-first mobile grid, responsive up to desktop
- Vibrant Uma Musume palette: Speed (blue), Stamina (green), Power (red), Guts (orange), Wit (purple)

## 3. Key Screens & Components

### Dashboard
- **Header:** Personalized greeting
- **Quick Cards:** Uma List, Export, New Run
- **Grid:** 1 column mobile, 3 columns desktop
- **Styling:** Cygames-style hues, hover shadows, rounded corners

### Uma List
- **Title:** 🎴 Uma Musume List
- **Cards:** Name, aptitudes, career links
- **Buttons:** Preview, Export
- **Grid:** Responsive, hover transitions

### Career Run Form
- **Sections:**
	- Character info
	- Stats (inputs + color mini-bars)
	- Suitability (grade icons)
	- Skills (dynamic rows, autocomplete)
- **Interactivity:** Alpine.js for skill row add/remove, inline validation
- **Layout:** Collapsible/tabs for long forms, mobile stacking

## 4. Branding & Style

- **Color System:** Stat mapping to palette
- **Icons:** Game-style (⚡🛡️🔥💪🧠)
- **Font:** Figtree for body, Inter/Montserrat for headers
- **Buttons:** Rounded pills, accent backgrounds, drop-shadow
- **Transitions:** Subtle on buttons, cards, toggles

## 5. Responsiveness & Accessibility

- Mobile-first grid, adaptive columns
- Button/input min height: 44px
- Collapsible/accordion skill section on mobile
- Fixed mobile footer for "Save" CTA
- WCAG AA color contrast
- ARIA labels, keyboard navigation

## 6. Blade Component Blueprint

- `x-card.blade.php`: Uma card (name, badges, link)
- `x-stat-bar.blade.php`: Colored stat bar
- `x-skill-row.blade.php`: Skill input row (name, cost, toggle, notes)
- `x-responsive-grid.blade.php`: Adaptive grid
- Tailwind utility layout wrappers

## 7. API & Data Flow

- **Endpoints:**
	- `/api/uma` (GET/POST): List/create Uma Musume
	- `/api/uma/{id}` (GET): Details
	- `/api/career` (POST): Start career
	- `/api/career/{id}/stats` (POST): Log turn stats
	- `/api/career/{id}/skills` (POST): Manage skills
	- `/api/export/career/{id}` (GET): Export career run
	- `/api/export/skills` (GET): Export skills

## 8. Implementation Sequence

### Design Mockups (Figma)
- Dashboard
- Uma List grid
- Career Run form (dynamic skill rows)

### Frontend Prototyping
- Blade components for stat bars, skill cards, responsive grid
- Alpine.js for dynamic form rows, validation

### Backend
- Model & migration for UmaMusume, CareerRun, StatProgress, Skill, SkillCareerRun
- Controller logic for CRUD, export, validation
- API routes and resource formatting

### Accessibility Review
- ARIA-labels, contrast, tab indexes

### Testing
- Feature tests for API endpoints, form logic, export functionality

## 9. Roadmap & Milestones

- **v1.2:** Dark mode, mobile optimization, basic stat charts
- **v1.3:** AI training suggestions, race simulation, multi-language
- **v2.0:** Team management, scenario builder, community sharing

## 10. Next Steps

- [ ] Finalize Figma mockups for all key screens/components
- [ ] Scaffold Blade components per blueprint
- [ ] Develop backend models/controllers/routes
- [ ] Prototype dynamic form logic with Alpine.js
- [ ] Implement accessibility features
- [ ] Prepare documentation for API/component usage
- [ ] Add actual screenshots and logo assets

---

**This planning file is a living document. Update regularly as implementation proceeds and feature priorities shift.**


# Uma‑Tracker Design & Implementation Planning (Iteration) (VERSION 2)

... (VERSION 2 content follows below)

## 1. Purpose & Audience
**Target Users:** Uma Musume: Pretty Derby players  
**Core Goals:**
- Log turn-by-turn stat growth and skill decisions
- Plan/tracking for SP usage and match strategies
- Export career progression to Excel for analysis/sharing
- Deliver a visually polished, game-inspired UI

## 2. Inspirations from Game Design
- **Top-screen stamina bars**: Numeric overlays, gradient fill, persistent visibility.
- **Stat color coding:** 
	- ⚡ Speed = blue
	- 🛡️ Stamina = green
	- 🔥 Power = red
	- 💪 Guts = orange
	- 🧠 Wit = purple
- **Skill cards:** Modal overlays, colored borders by skill type, iconography.
- **Mobile-first, portrait grid:** Adaptive to desktop, responsive spacing.
- **Animated feedback:** Bar shakes, stat flashes, Alpine.js transitions.

## 3. Key Screens & Layouts

### Dashboard
- Welcome banner
- Responsive grid of action cards (Uma List, Export, New Run)
- Vibrant Tailwind color palette, shadow/hovers

### Uma List
- Emoji title, preview & export buttons
- Uma cards: name, aptitudes, tags, navigation
- Responsive grid, soft transitions

### Career Run Form
- **Header:** Avatar, name, turn/stamina bar
- **Stats:** Inline colored bars, circular progress indicators
- **Grade Inputs:** Dropdowns for suitability (A–G)
- **Growth/Condition:** Text + icons
- **Skills Section:**
	- Alpine.js dynamic skill rows
	- Colored border cards (type-based)
	- Inputs: skill name, SP, acquired, notes
	- Floating "Add Skill" button
- **Total SP/Submit:** Fixed mobile footer

## 4. Visual & Interactive Blueprint

- **Stamina/Turn Bars:** Persistent, animated, numeric overlays
- **Colored Stat Indicators:** SVG or ASCII progress, grade overlays
- **Skill Card Template:** Flex layout, color-coded border, responsive stacking
- **Responsive Layout:** Grid expands/collapses, mobile stacking
- **Animated Feedback:** Stat bar shake, color flashes, Alpine.js transitions

## 5. Component Library (Blade)

- `x-card` — Uma card for lists/dashboards
- `x-stat-bar` — Stat meter with icon/color
- `x-skill-row` — Dynamic skill input card
- `x-progress-gauge` — Circular/radial progress
- `x-modal-preview` — Export preview modal

## 6. Accessibility & Branding

- **Contrast/Fonts:** Figtree font, WCAG AA color contrast, clear labels
- **Icons:** Emoji/SVG for stats, intuitive navigation
- **Palette:** Bright, upbeat, Cygames-inspired colors

## 7. Roadmap & Feature Milestones

| Feature                | Benefit                       | Status   |
|------------------------|------------------------------|----------|
| Radial stat charts     | Quick visual insights        | [ ]      |
| Turn history timeline  | Replay/run visualization     | [ ]      |
| Avatars/support icons  | Game-like personalization    | [ ]      |
| AI skill suggestions   | SP/training optimization     | [ ]      |
| Animated interactions  | Dynamic feedback/polish      | [x] Base |
| Excel export           | Data sharing/analysis        | [x]      |

## 8. Implementation Steps

1. **Finalize Figma wireframes** for all major screens/components.
2. **Scaffold Blade components** (`x-stat-bar`, `x-skill-row`, etc.) per design.
3. **Integrate Alpine.js** for dynamic form rows and transitions.
4. **Style UI** with Tailwind palette and custom utility classes.
5. **Develop backend models/routes** for stat, skill, SP tracking and export.
6. **Accessibility audit:** ARIA, color contrast, keyboard navigation.
7. **Testing:** Unit/feature tests for frontend forms and backend logic.
8. **Documentation:** Update README and code comments per new features.
9. **Update roadmap** as features ship.

## 9. Validation Criteria

- All UI elements match game-inspired design and are mobile-first.
- Skill cards, stat bars, and progress gauges are animated and color-coded.
- Forms are accessible, responsive, and provide clear feedback.
- Export/preview modals are styled and functional.
- Core features (logging, planning, export) are robust and easy to use.
- Roadmap is kept current and visible in docs.

---

**This planning doc is iterative—review and expand as features are designed, prototyped, and shipped.**


# UMA MUSUME RACE PLANNER: Design & Implementation Planning (Iteration) (VERSION 3)


---

# UMA MUSUME RACE PLANNER: As-Built Design & Implementation Planning (VERSION 7)

## 1. Overview
This milestone documents the current "As-Built" frontend (see SPEC-06-UMA-MUSUME-UI-AS-BUILT):
- Bootstrap 5, vanilla JS, custom CSS variables, system-native font stack
- Responsive dashboard, plan editor with tabs, dynamic skill/goal/prediction rows
- Stat color system standardized via CSS variables (`--color-speed`, etc.)
- Dark mode toggle with smooth transitions
- Export as styled text (inline and modal)
- Accessibility: ARIA labels, keyboard navigation, touch targets
- Avatar upload/preview in plan forms
- Growth stat calculators in plan details
- Enhanced iconography (Bootstrap Icons, custom SVGs)
- Sticky mobile footer for Save/Export actions

## 2. Changelog
- Standardized stat color system and applied to all components
- Refactored dark mode for smooth transitions and accessibility
- Skill/goal/prediction row builders updated for markup and JS consistency
- Export logic verified and enhanced for styled text output
- Accessibility audit performed (ARIA, keyboard, touch)
- Avatar upload/preview added to plan forms
- Growth stat calculators implemented in plan details
- Iconography enhanced with custom SVGs
- Sticky mobile footer refined for Save/Export actions

## 3. Next Iteration Plan
- Refine accessibility and dark mode
- Expand stat overview panel
- Integrate AI SP optimization suggestions
- Add game-style modal previews with transitions
- Run UI and accessibility tests (Playwright, Lighthouse)

## 4. Validation Checklist
- [x] Stat color system standardized and applied
- [x] Dark mode transitions smooth and accessible
- [x] Skill/goal/prediction row builders consistent
- [x] Export logic works inline and modal
- [x] Accessibility audit complete
- [x] Avatar upload/preview functional
- [x] Growth stat calculators present
- [x] Iconography enhanced
- [x] Sticky mobile footer refined
**Target Users:** Uma Musume: Pretty Derby players  
**Core Goals:**
- Streamlined logging of turn-by-turn stat development
- Intuitive planning/tracking for SP usage and skill acquisition
- Export career data for analysis and sharing (spreadsheet formats)
- Visually enriched, game-inspired, mobile-first UI

## 2. Visual & UX Inspirations

- **Persistent Gauges:** Top-screen stamina and turn bars, numeric overlays for clarity
- **Stat Color Coding:**
	- ⚡ Speed = blue
	- 🛡️ Stamina = green
	- 🔥 Power = red
	- 💪 Guts = orange
	- 🧠 Wit = purple
- **Skill Card Design:** Tailwind-styled cards, colored left borders by type, icons
- **Mobile-first Layout:** Portrait grid, adaptive to desktop, clear spacing
- **Animated Feedback:** Bar shakes, stat flashes, Alpine.js transitions

## 3. Layout & Component Designs

### Dashboard
- Welcome banner / personalized greeting
- Responsive grid of quick-action cards for Plan List, Export, Create Form
- Colored button cards, hover shadows

### Plan List
- Prominent title ("🎴 Uma Musume Plan List")
- Preview & Export buttons (styled per data type)
- Plan cards: name, style/track tags, navigation to detail
- Responsive grid, soft transitions

### Career Run Form
- **Header:** Avatar/name section, "Turn X/Y" label, stamina gauge
- **Stats Grid:** Inputs, inline progress bars or circular indicators (x/1200)
- **Aptitude Grades:** Dropdowns for Turf/Dirt/Sprint/Mile/etc. (A–G)
- **Skills Section:** Alpine.js dynamic rows (add/remove), color-coded cards, skill name/SP/acquired/notes
- **Layout:** Collapsible/tabs for long forms on mobile

## 4. Component Blueprint

- `x-stat-bar` – Horizontal progress bar for Turn and Stamina gauges
- `x-skill-row` – Card for single skill entry (color border per type)
- `x-card` – Display card for plan in main list
- `x-modal-preview` – Modal for export preview

**Examples:**
```html
<!-- x-stat-bar -->
<div class="h-4 bg-gray-200 rounded-full overflow-hidden">
	<div class="h-full bg-green-500 transition-all" style="width: {{ $turnPercent }}%"></div>
</div>
<p class="text-sm text-gray-600 mt-1">Turn {{ $currentTurn }}/{{ $maxTurns }}</p>

<!-- x-skill-row -->
<div class="bg-white shadow rounded-lg p-4 flex items-center space-x-4 border-l-4 border-blue-500">
	<input name="skills[0][name]" class="flex-1" placeholder="Skill Name"/>
	<input name="skills[0][sp_cost]" class="w-16 text-center"/>
	<label class="flex items-center space-x-1">
		<input type="checkbox" name="skills[0][acquired]" />
		<span class="text-sm text-gray-700">Acquired</span>
	</label>
	<input name="skills[0][notes]" class="flex-1" placeholder="Notes"/>
</div>
```
(*Border color changes based on skill type*)

## 5. Branding & Aesthetic

- **Color System:** Stat palette matches game
- **Icons:** ⚡, 🛡️, 🔥, 💪, 🧠 for stats
- **Typography:** Figtree (modern sans-serif)
- **Animations:** Subtle hover transitions, feedback on stat changes

## 6. Responsive Strategy

- Mobile-first flexible grid (single → multi-column)
- Touch targets ≥ 44px
- Fixed mobile footer for "Save" CTA

## 7. Accessibility & UX

- WCAG AA color contrast
- Text labels for all color-coded elements
- Always-visible form labels, inline error messages
- Keyboard access (tab indexes, ARIA labels on dynamic rows)

## 8. Prototyping & Implementation Flow

1. **Wireframes:** Mockups of all key screens/components in Figma
2. **Prototypes:** Interactive components (skill repeater, stat bars) in Alpine.js/React
3. **UI Review:** Consistency and intuitiveness on all devices before full build

## 9. Mobile-First Layout (Example)

```
[ Header: UMA MUSUME PLANNER ]   [ Turn 12/70 ]
-----------------------------------------------
Stamina ▶■■■■■□□□□□ (60%)
-----------------------------------------------
Stats:
Speed   ⚡ 720/1200 ▓▓▓▓▓▓░░░░░
Stamina 🛡️ 400/1200 ▓▓▓░░░░░░░░
...
-----------------------------------------------
Growth: +10% Speed     Conditions: Rain
-----------------------------------------------
Skills [ + Add Skill ]
┌─────────────────────────────────────────────┐
│ Hydrate | 20 SP [✓]                         │
│ Notes: Mid-race recovery                    │
└─────────────────────────────────────────────┘
...
-----------------------------------------------
Total SP: 340
[ Submit Career Run ]
```

## 10. Roadmap & Enhancements

| Feature                | Benefit                         | Status   |
|------------------------|---------------------------------|----------|
| Radial Stat Charts     | Visual insights for each run    | [ ]      |
| Turn History Timeline  | Career progression replay       | [ ]      |
| Avatars/Support Icons  | Game-like personalization       | [ ]      |
| AI Skill Suggestions   | SP/training optimization        | [ ]      |
| Animated Interactions  | Feedback and polish             | [x] Base |
| Excel Export           | Data sharing/analysis           | [x]      |

## 11. Summary

By combining Uma Musume’s design hallmarks (persistent gauges, color-coded stat bars, mobile-first layout, responsive components), UMA MUSUME RACE PLANNER will deliver a polished, user-friendly platform mirroring the clarity and interactivity of the game, with powerful planning and tracking tools.

---

**This planning document should be updated as features are designed, prototyped, and deployed. Track progress and adjust priorities as needed.**

---


# VERSION 6 — Frontend Redesign Milestone (2025-09-02)

Status: In-progress — major UI overhaul, accessibility, and component refactors implemented; documentation updated.

Overview:
- Goal: Redesign UI to match Uma Musume style (no direct copyright), maximize engagement and accessibility.
- Key focuses: "M PLUS Rounded 1c" font, pastel stat palette, pill-shaped buttons/tabs, card-based dashboard/plan list, ARIA attributes, keyboard navigation, WCAG AA contrast.

Completed/implemented so far:
- Created: `css/theme_v6.css` (font import, stat palette, card/pill styles).
- Updated: `index.php` (theme link), `components/plan-list.php`, `x-stat-bar.php`, `x-skill-card.php`, `x-card.php`, `plan_details_modal.php`, `plan-inline-details.php`.
- Card-based dashboard and plan list: responsive grid, soft shadows, emoji headers.
- Accessibility: ARIA attributes, visible focus, keyboard navigation, WCAG AA contrast.
- Component refactors:
	- `x-stat-bar.php`: stat color via CSS variable, emoji icon, animated fill.
	- `x-skill-card.php`: pastel card, colored border by skill tag, pill action button.
	- `x-plan-card.php`: plan card with emoji, badges, summary strip, pill Open button.
	- `plan-list.php`: replaced table with card grid, pill filters, accessible tablist.
	- `plan_details_modal.php` & `plan-inline-details.php`: emoji title, summary strip, pill footer buttons.

Next steps / backlog:
- JS lint/test: run linters and unit tests (requires fixing `package.json` / installing Node deps) — optional but recommended.
- Performance: If skill reference grows large, switch autosuggest to server-side incremental queries.
- UX polish: add tooltips, per-stat slider color segments, and keyboard shortcut improvements.
- Accessibility audit (Lighthouse), Playwright UI tests.

Validation checklist:
- [x] Theme and font applied globally
- [x] Card/pill UI in dashboard and plan list
- [x] Accessibility improvements (ARIA, keyboard, contrast)
- [x] Component refactors (stat bar, skill card, plan card)
- [x] No PHP/JS errors in updated files
- [x] Smoke test output matches new card/pill UI

Notes:
- All changes tracked in SPEC-05-UMA-MUSUME-UI-DESIGN and referenced in README and docs/components.md.
- Progressive enhancement: features fail gracefully if JS or Chart.js is unavailable.



# UMA MUSUME RACE PLANNER: As-Built Design & Implementation Planning (Iteration) (VERSION 4)

## 1. Purpose & Audience
**Target Users:** Uma Musume: Pretty Derby players  
**Goals:**
- Streamlined stat development logging
- Intuitive SP usage and skill acquisition planning
- Export career data as formatted plain-text summaries
- Game-inspired, visually engaging, and mobile-friendly UI

## 2. Visual & UX Inspirations

- **Persistent Gauges:** Energy slider (`<input type="range">`) for stamina, numeric overlays for clarity
- **Stat Color Coding & Badges:** Custom CSS palette and Bootstrap Badges for stats/grades
- **Component Styling:** Bootstrap 5 Cards with custom CSS to recreate layered, organized menus
- **Bootstrap Icons:** Used for intuitive, game-like visual cues

## 3. Layout & Component Designs

### Dashboard
- Header banner
- Responsive Bootstrap grid: left (plan list), right (stats, activity)
- All components update dynamically (no page reloads)

### Plan List
- Card with Bootstrap Table listing all plans
- Action buttons: Edit, View Details, Delete (Bootstrap Icons)
- Table populated via JS from backend API

### Career Run Form
- Presented in Bootstrap Modal or inline details view
- Tabbed interface (General, Attributes, Skills, etc.)
- Dynamic skill/prediction/goal rows managed by vanilla JS
- Standard Bootstrap Form Controls for inputs

## 4. Component Implementation

- **plan-list.php:** Renders plan table structure for JS population
- **plan_details_modal.php:** HTML for the editing modal, tabs, and form sections
- **JavaScript:** Functions (e.g., `createModalSkillRow()`) generate dynamic skill rows using Bootstrap classes

**Skill Row Example:**
```html
<td><input type="text" class="form-control form-control-sm skill-name-input"></td>
<td><input type="number" class="form-control form-control-sm skill-sp-cost-input"></td>
<td class="text-center"><input type="checkbox" class="form-check-input skill-acquired-checkbox"></td>
<td><select class="form-select form-select-sm skill-tag-select">...</select></td>
<td><input type="text" class="form-control form-control-sm skill-notes-input"></td>
<td><button class="btn btn-danger btn-sm remove-skill-btn">...</button></td>
```

## 5. Branding & Aesthetic

- **Color System:** CSS variables for palette, stat-based backgrounds
- **Icons:** Bootstrap Icons for all actionable UI elements
- **Typography:** System-native sans-serif stack ("Segoe UI", Tahoma, Verdana, etc.)
- **Animations:** CSS transitions for dark mode and hover/active states

## 6. Responsive Strategy

- Mobile-first Bootstrap 5 grid
- Buttons/inputs sized for touch targets
- Modals and panels stack vertically on small screens

## 7. Accessibility & UX

- High color contrast
- Always-visible `<label>` for inputs
- Bootstrap ensures keyboard navigation and ARIA support

## 8. Implementation Flow

1. **Backend API:** PHP scripts serving secure JSON endpoints
2. **Frontend:** `index.php` assembles UI from PHP includes
3. **JavaScript:** Handles API calls, DOM updates, form submission

## 9. Mobile-First Layout (Example)

```
[ Header: UMA MUSUME PLANNER ]
-----------------------------------------------
[ Plan List Card ]
	Plan 1        [ Edit ] [ View ] [ Del ]
	Plan 2        [ Edit ] [ View ] [ Del ]
	...
-----------------------------------------------
[ Stats Card ]
	Total Plans: 5
	Active: 2
-----------------------------------------------
[ Recent Activity Card ]
	- Plan created...
	- Plan updated...
-----------------------------------------------
[ Footer ]
```

## 10. Roadmap & Enhancements

| Feature                | Benefit                       | Status   |
|------------------------|------------------------------|----------|
| Radial Stat Charts     | Visual insights per run      | [ ]      |
| Turn History Timeline  | Career replay visualization  | [ ]      |
| Avatars & Support Icons| Game-like personalization    | [ ]      |
| AI Skill Suggestions   | SP/training optimization     | [ ]      |
| Animated Interactions  | Feedback and polish          | [x] Base |
| Text Export            | Data sharing/analysis        | [x]      |

## 11. Summary

The UMA MUSUME RACE PLANNER delivers a user-friendly and authentic-feeling career planning experience by combining Bootstrap 5, custom CSS, and dynamic vanilla JavaScript. The platform maintains organizational clarity and responsiveness, providing powerful tools for stat tracking, skill planning, and data export.

---

**Iterate and update as new features are designed, developed, or deployed.** (This file preserves versions 1–4 for traceability.)

---

## UMA MUSUME RACE PLANNER: Iterative UI Planning – Official Game Style Alignment (VERSION 8)

This section defines the next UX/UI milestone to align the app’s visuals and interactions more closely with the official game’s look and feel while maintaining originality and accessibility.

### 1. Visual Style Inspiration (Screenshots Referenced)

- Use a predominantly white base (~70%), green main (~25%), and orange/pink accent (~5%) for a polished, game-like appearance.
- UI color schemes dynamically adapt to match each Uma Musume’s motif, reflecting character personalization.
- Text elements use gradients, outlines, and glowing effects for "NEW" badges and special emphasis.
- Stat bars and badges leverage iconic colors: blue for Speed, green for Stamina, red for Power, orange for Guts, purple for Wit.

### 2. Layout & Structure

- Header/Footer: Persistent info (current turn, goals, energy) in a fixed header; actionable elements (skip, menu) in a thumb-friendly footer.
- Main Content: Rigid placement of primary info (stats, buttons, dialog) for consistency across screens.
- Cards & Dialogs: Consistent vertical sizing, drop shadow, rounded corners. Buttons use white-to-green gradients and subtle shadows.
- Menu/Sidebar: Use icon-labeled navigation with clear grouping and sectioning.

### 3. Interaction & Feedback

- Tap and button feedback use horseshoe/star/colored dot animations to distinguish interaction types.
- Navigation icons and actionable elements include continuous animation for feedback and UI liveliness.
- Scene and lighting colors adapt to time, weather, and location for context awareness.
- Stat and energy bars animate smoothly on change; respect `prefers-reduced-motion`.

### 4. Typography

- Use "M PLUS Rounded 1c" from Google Fonts for main UI font, supporting Japanese text where needed; fallback to "Figtree" or system sans-serif.
- Text layouts prioritize readable line breaks and allow dynamic breaking for names as appropriate.

### 5. Component Blueprints

- Stat Bar: Colored, gradient progress bar with stat icon and bold label.
- Button: Large, pill-shaped with drop shadow, icon/emoji label.
- Skill Card: Colored border/tag for rarity/type, game-style layout.
- Plan Card: Mimic race/training card with left-aligned stats and right-aligned goals.
- Dialog: Two vertical size variants, drop shadow, consistent padding.
- Menu/Sidebar: Large icons, grouped sections, pastel backgrounds.

### 6. Responsive & Accessibility

- Mobile-first grid: main actions and navigation within thumb reach.
- All touch targets ≥ 44px.
- WCAG AA contrast for text/backgrounds.
- Keyboard navigation and ARIA labels for modals, tabs, and skill rows.

### 7. Animation & Effects

- Smooth transitions for tab changes, modal open/close, stat bar updates.
- Tap feedback and animated icons for interactive elements.
- Optional: Add toggleable sound effects for actions like save/delete.

### 8. Implementation Steps

1. Add Google Fonts import for "M PLUS Rounded 1c" in HTML head.
2. Define base color palette with CSS variables for stat, accent, and character-motif colors.
3. Build reusable components: stat bar, skill card, plan card, dialog/modal, sidebar menu.
4. Apply gradient backgrounds, drop shadows, rounded corners, and animated transitions.
5. Integrate tap feedback and animation logic in JS.
6. Ensure accessibility (contrast, keyboard, ARIA, reduced-motion).
7. Optionally add audio cues for key actions.
8. Use a data-first approach: group/categorize information before UI layout.
9. Test for mobile usability and thumb reach.

### 9. Milestones

| Milestone                | Target Date | Status |
|--------------------------|-------------|--------|
| Core palette & font setup| Sep 5       | ⏳     |
| Stat bars & components   | Sep 7       | ⏳     |
| Button/dialog redesign   | Sep 8       | ⏳     |
| Menu/sidebar UI          | Sep 10      | ⏳     |
| Animation/tap feedback   | Sep 12      | ⏳     |
| Accessibility audit      | Sep 14      | ⏳     |
| Scene-based color adapts | Sep 16      | ⏳     |

### 10. Validation & Metrics

- Consistent color, typography, and layout across screens.
- Stat bars, buttons, and dialogs match game visual style without copying assets.
- Mobile users complete planning tasks quickly and easily (thumb reach tests).
- Accessibility audits pass (contrast, navigation, focus management).
- Session recordings and user feedback indicate higher engagement.

