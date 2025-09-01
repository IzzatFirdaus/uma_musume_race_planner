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

## VERSION 8 — Official Game Style Alignment (Planning)

Goals:
- Adopt rounded typography (M PLUS Rounded 1c) and increase white space with green primary and orange/pink accents.
- Prepare character motif theming via CSS variables for per-plan accent colors.
- Upgrade components to use gradient fills, soft shadows, and pill shapes while preserving accessibility.

Component targets:
- `x-stat-bar.php`: gradient progress (tint→solid), numeric overlay, bold label; `prefers-reduced-motion` fallback.
- `x-skill-card.php`: rarity/type badge with colored tag; consistent paddings and compact mobile layout.
- `x-card.php` (Plan Card): thumbnail slot, left stats/right goals layout, motif-accent border.
- `x-modal-preview.php` (Dialog): two vertical sizes, consistent paddings, focus traps and ARIA.
- Sidebar/Menu (navbar.php): icon-labeled sections, pastel group panels.

Implementation notes:
- Add Google Font import for "M PLUS Rounded 1c" in `index.php`/`header.php`.
- Extend `css/style.css` and/or `css/theme_v6.css` with gradient utilities and motif variables: `--motif-primary`, `--motif-accent`, `--motif-bg`.
- JS: add tap feedback ripple class and micro-animation utilities; respect reduced motion.

Validation:
- Thumb reach on mobile, WCAG AA contrast, keyboard navigation across dialogs/tabs.
- No reliance on copyrighted assets; only inspired shapes/colors/styles.
