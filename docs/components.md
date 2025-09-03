# Component Library

This file documents the lightweight partial components added to the project.

## x-stat-bar.php
- Location: `components/x-stat-bar.php`
- Purpose: Small, accessible stat bar to display numeric stats (e.g., Speed, Stamina) with a progress bar.
- Inputs: $label, $value, $max, $color

### Design notes (VERSION 3)
- Stat colors are defined in `css/style.css` as CSS variables:
  - `--color-stat-speed` (Speed) — canonical
  - `--color-stat-stamina` (Stamina) — canonical
  - `--color-stat-power` (Power) — canonical
  - `--color-stat-guts` (Guts) — canonical
  - `--color-stat-wit` (Wit) — canonical

Recommendation: use the short aliases introduced in SPEC-06 when authoring new components for brevity and clarity; both sets are available for compatibility:

  - `--color-speed`, `--color-stamina`, `--color-power`, `--color-guts`, `--color-wit`

Components should reference CSS variables (not hard-coded colors). Example: `background: var(--color-speed)` or `border-left-color: var(--color-stat-speed)`.

Use `$color` or the CSS variable when rendering the progress bar to ensure consistent palette.

## x-skill-row.php
- Location: `components/x-skill-row.php`
- Purpose: A single skill row used in plan forms/modals. Contains inputs for skill name, SP cost, and an acquired toggle.
- Usage: Place inside a container with `data-skill-list`. Use a hidden template element with `data-skill-template` and a button with `data-skill-add` to add rows.

### Sample markup (mobile-first)
```html
<div class="skill-row skill-card skill-type-speed p-2">
  <input type="hidden" name="skills[0][skill_name]" class="skill-name-hidden" />
  <div>
    <input class="form-control skill-name-input" name="skills[0][skill_name_input]" placeholder="Skill name" />
  </div>
  <div>
    <input class="form-control skill-sp-input" name="skills[0][sp_cost]" />
  </div>
  <div>
    <input class="form-control skill-tag-input" name="skills[0][tag]" />
  </div>
  <div>
    <input class="form-control skill-notes-input" name="skills[0][notes]" />
  </div>
  <div>
    <input type="checkbox" class="skill-acquired-toggle" />
  </div>
  <button class="btn btn-sm btn-outline-danger btn-skill-remove">Remove</button>
</div>
```

## js/skill-rows.js
- Location: `js/skill-rows.js`
- Purpose: Manages adding/removing skill rows, serializing them into a hidden `skills_json` input, and integrating with an existing `autosuggest` if present.
- Auto-initializes containers with `data-skill-manager` on DOMContentLoaded. You can manually call `SkillRows.init(containerElement)`.

### Minimal example

HTML structure inside a form:

<form data-skill-manager>
  <div data-skill-list>
    <div data-skill-template style="display:none;">
      <!-- include the x-skill-row.php markup here as the template -->
    </div>
  </div>
  <button type="button" data-skill-add>Add skill</button>
  <input type="submit" value="Save">
</form>

---


## VERSION 6 — Frontend Redesign Progress (2025-09-02)

**Summary:**
- Major UI overhaul to match Uma Musume style (no direct copyright).
- New theme: "M PLUS Rounded 1c" font, pastel stat palette, pill-shaped buttons/tabs.
- Card-based dashboard and plan list: responsive grid, soft shadows, emoji headers.
- Accessibility: ARIA attributes, visible focus, keyboard navigation, WCAG AA contrast.
- Component refactors:
  - `x-stat-bar.php`: stat color via CSS variable, emoji icon, animated fill.
  - `x-skill-card.php`: pastel card, colored border by skill tag, pill action button.
  - `x-plan-card.php`: plan card with emoji, badges, summary strip, pill Open button.
  - `plan-list.php`: replaced table with card grid, pill filters, accessible tablist.
  - `plan_details_modal.php` & `plan-inline-details.php`: emoji title, summary strip, pill footer buttons.

**Changelog:**
- Created: `css/theme_v6.css` (font import, stat palette, card/pill styles).
- Updated: `index.php` (theme link), `components/plan-list.php`, `x-stat-bar.php`, `x-skill-card.php`, `x-card.php`, `plan_details_modal.php`, `plan-inline-details.php`.
- Added: summary strips for Turn/SP/Race in plan editor views.
- Improved: accessibility, keyboard navigation, ARIA labels.

**Validation:**
- No PHP/JS errors in updated files.
- Smoke test output matches new card/pill UI.

For full roadmap and design history, see `APPLICATION_PLANNING.md`.

---

## VERSION 7 — As-Built Frontend & Future Iteration (2025-09-02)

**Summary:**
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

**Changelog:**
- Standardized stat color system and applied to all components
- Refactored dark mode for smooth transitions and accessibility
- Skill/goal/prediction row builders updated for markup and JS consistency
- Export logic verified and enhanced for styled text output
- Accessibility audit performed (ARIA, keyboard, touch)
- Avatar upload/preview added to plan forms
- Growth stat calculators implemented in plan details
- Iconography enhanced with custom SVGs
- Sticky mobile footer refined for Save/Export actions

**Validation:**
- All components use standardized stat color system
- Dark mode transitions are smooth and accessible
- Skill/goal/prediction row builders are consistent
- Export logic works inline and modal
- Accessibility audit complete
- Avatar upload/preview functional
- Growth stat calculators present
- Iconography enhanced
- Sticky mobile footer refined

---

## VERSION 8 — Official Game Style Alignment (2025-09-03)

**Summary:**
- Adopted predominantly white base, green main, orange/pink accent for game-like appearance.
- UI color schemes now adapt to each Uma Musume’s motif using CSS variables (`--motif-primary`, `--motif-accent`, `--motif-bg`).
- Added gradient text, outlines, glowing effects for badges and emphasis.
- Stat bars and badges use iconic colors: blue (Speed), green (Stamina), red (Power), orange (Guts), purple (Wit).
- Refactored components to use V8 classes: `.v8-plan-card`, `.v8-gradient-text`, `.v8-animated-pill`, `.v8-modal-content`, `.v8-tap-feedback`, `.v8-stat-bar`, `.v8-skill-card`.
- Tap feedback and animated icons added for interactive elements; ripple effect respects reduced motion.
- Accessibility: ARIA labels, keyboard navigation, WCAG AA contrast, touch targets ≥44px.
- Milestone status: Core palette, font, stat bars, pill buttons, modals, sidebar, tap feedback, and accessibility audit in progress.

**Changelog:**
- `css/style.css`: updated with new color variables, gradient utilities, and soft shadow effects.
- `css/theme_v6.css`: deprecated; merged into `css/style.css`.
- `index.php`/`header.php`: Google Font import for "M PLUS Rounded 1c"; removed old font imports.
- Components:
  - `x-stat-bar.php`: gradient progress (tint→solid), numeric overlay, bold label; `prefers-reduced-motion` fallback.
  - `x-skill-card.php`: rarity/type badge with colored tag; consistent paddings and compact mobile layout.
  - `x-card.php` (Plan Card): thumbnail slot, left stats/right goals layout, motif-accent border.
  - `x-modal-preview.php` (Dialog): two vertical sizes, consistent paddings, focus traps and ARIA.
  - Sidebar/Menu (navbar.php): icon-labeled sections, pastel group panels.

**Validation:**
- Thumb reach on mobile, WCAG AA contrast, keyboard navigation across dialogs/tabs.
- No reliance on copyrighted assets; only inspired shapes/colors/styles.

---

## VERSION 9 — Mechanics-Driven Color and Layout Integration (2025-09-03)

**Summary:**
- Color-coded action bubbles for training mechanics (stamina, speed, power, guts, wit)
- Status bar and event countdown with animated gradients
- Stat panels with color-coded stats and bonus overlays
- Skill cards with type color differentiation and bonus badges
- Energy/motivation gauge with animated transitions
- Responsive, thumb-friendly layout for mobile (footer actions, touch targets)
- Animation logic for tap feedback, status changes, continuous icon movement
- Accessibility: ARIA labels, keyboard navigation, WCAG AA contrast, 44px+ touch targets

**Changelog:**
- `css/style.css`: added V9 color variables, gradients, and action bubble/button classes
- `components/stats-panel.php`: horizontal stat panel with color and bonus overlays
- `components/x-skill-card.php`: skill type color and bonus overlays
- `components/header.php`: energy/motivation gauge
- `components/footer.php`: thumb-friendly action row
- `js/v8-ux.js`: V9 animation logic for tap feedback, status, icons

**Validation:**
- All components use mechanics-driven color system
- Animations respect reduced motion
- Accessibility audit complete
- Mobile layout and touch targets validated
