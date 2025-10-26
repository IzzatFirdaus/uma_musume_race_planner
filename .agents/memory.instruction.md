---
applyTo: '**'
---

# Livewire Standardization Project Memory

## Coding Preferences

- PHP 8.2+ with constructor property promotion and explicit return types
- Laravel 12.x with Livewire v3.6+
- Tailwind CSS v4 with Vite v7 bundler
- Use kebab-case for Livewire view filenames and PascalCase for class names
- Prefer Livewire for server-stateful components (forms, lists with add/remove, modals)
- Keep Alpine.js or vanilla JS for UI-only micro-interactions
- Always add tests under tests/Feature/Livewire/ using Livewire::test()
- Format with vendor/bin/pint --dirty before committing

## Project Architecture

- Backend: Laravel 12, PSR-4 namespace App\Livewire
- Livewire classes: app/Livewire/<Feature>/<Name>.php
- Livewire views: resources/views/livewire/<feature>/<name>.blade.php
- Blade components (stateless): resources/views/components/<name>.blade.php
- Form requests: app/Http/Requests/<Feature>/<FormName>Request.php
- Tests: tests/Feature/Livewire/<ComponentName>Test.php

## Frontend Stack (Verified)

- PHP: ^8.2 (installed 8.2.12)
- Laravel: ^12.0 (laravel/framework ^12.0)
- Livewire: ^3.6 (livewire/livewire ^3.6)
- Tailwind CSS: ^4.0.0
- Vite: ^7.0.4
- Alpine.js: Included with Livewire v3
- Testing: PHPUnit v11, Jest, Playwright

## Project Conventions Discovered

### Existing Livewire Components (Reference Patterns)

Dashboard components:

- App\Livewire\Dashboard\HeaderBanner, PlanDetailsPage, PlanInlineDetails, PlanList, RecentActivity, StatsPanel, SupportCardSummary

Layout components:

- App\Livewire\Layout\Alerts, Footer, Modals, Navbar

Standalone:

- App\Livewire\FormTabs, GuideStickyNav, PlanDetails, QuickCreatePlan, TraineeImageHandler

Total count: 16 Livewire classes + 16 corresponding views

### High-Priority Conversion Targets

1. **SkillRow** (HIGH priority)
   - Source: resources/views/components/partials/skill-row.blade.php
   - Target: App\Livewire\Plans\SkillRow
   - Reason: add/remove rows with validation

2. **TrainingYear** (HIGH priority)
   - Source: resources/views/components/partials/training-year.blade.php
   - Target: App\Livewire\Plans\TrainingYear
   - Reason: per-year state management

3. **CharacterList** (HIGH priority)
   - Source: resources/views/characters.blade.php
   - Target: App\Livewire\Characters\CharacterList
   - Reason: character roster with modals

## Solutions Repository

- **Verified JSON object**: Created at docs/livewire-verified.json with version constraints, counts, and next steps
- **Conversion mapping**: docs/livewire-conversion-mapping.md contains YAML mapping of all conversion candidates
- **Blade partials to convert**: 5 identified in components/partials/, 3 in plans/partials/
- **Keep as Blade**: Stateless UI-only components (motivation-indicator, stat-meter, copy-script)

## Failed Approaches to Avoid

- Do not convert UI-only stateless components to Livewire (keep as Blade components)
- Do not skip accessibility checks when converting (preserve ARIA, keyboard nav, tab order)
- Do not forget to update parent Blade/controller templates when converting (replace @component with @livewire)
- Do not skip tests (write Livewire::test for happy + failure paths)

## Developer Commands (Quick Reference)

```powershell
# Verification (RULE #0)
php -v
php -r "$c=json_decode(file_get_contents('composer.json'),true); echo $c['require']['php'];"
Test-Path .\app\Livewire
Test-Path .\resources\views\livewire

# Development
composer install
npm install
npm run dev        # Vite dev server
php artisan serve  # Laravel dev server
composer run dev   # Concurrent dev (PHP, queue, logs, Vite)

# Build & Test
npm run build
vendor/bin/pint --dirty
php artisan test --filter=Livewire
php artisan test

# Create new components
php artisan make:livewire <Feature>/<Name> --no-interaction
php artisan make:test Feature/Livewire/<Name>Test --no-interaction
php artisan make:request <Feature>/<FormName>Request --no-interaction
```

## Accessibility Standards

- WCAG AA compliance mandatory
- Semantic HTML (header, nav, main, footer)
- Keyboard navigation (Tab, Enter, Escape)
- Focus indicators (visible, 2px minimum)
- ARIA labels/roles on interactive elements
- Form labels associated with inputs
- Color contrast 4.5:1 (normal text), 3:1 (large text)
- Test with Playwright for keyboard flows and screen readers

---

Last updated: October 26, 2025 (Session 2 - Standardize Livewire Frontend Integration)
