# Component Library

This file documents the lightweight partial components added to the project.

## x-stat-bar.php
- Location: `components/x-stat-bar.php`
- Purpose: Small, accessible stat bar to display numeric stats (e.g., Speed, Stamina) with a progress bar.
- Inputs: $label, $value, $max, $color

### Design notes (VERSION 3)
- Stat colors are defined in `css/style.css` as CSS variables:
  - `--color-stat-speed` (Speed)
  - `--color-stat-stamina` (Stamina)
  - `--color-stat-power` (Power)
  - `--color-stat-guts` (Guts)
  - `--color-stat-wit` (Wit)

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

## Planning & Version History

This components guide is part of a larger implementation plan. For the full, versioned design and roadmap (VERSION 1 → VERSION 4), see `APPLICATION_PLANNING.md` at the repository root. That file records design decisions, priorities, and next steps for the project.
