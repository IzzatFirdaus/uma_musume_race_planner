---
applyTo: "**"
---

# UI Development Instructions (Laravel + Livewire)

Last updated: 2026-03-01

## Scope

These rules apply to UI work in this repo (Blade, Livewire, Tailwind, Alpine).

## Stack

- Blade templates + Livewire v3
- Tailwind CSS v4
- Alpine.js for small client-only interactions
- Vite v7 for assets

## Component Locations

- Livewire classes: `app/Livewire/...` (PascalCase)
- Livewire views: `resources/views/livewire/...` (kebab-case)
- Blade components: `resources/views/components/...`
- Shared assets: `resources/js` and `resources/css`

## Implementation Rules

- Use Livewire for server-stateful UI (forms, add/remove rows, validation).
- Keep Alpine for micro-interactions only.
- Livewire components must have a single root element.
- Use `wire:model.live` for live updates and add `wire:key` in loops.
- Keep business logic in services when it grows beyond simple view models.

## Accessibility

- Target WCAG 2.1 AA.
- Preserve keyboard navigation, focus states, labels, and contrast.
- Maintain ARIA roles and labels for interactive elements.

## Testing

- Livewire tests live under `tests/Feature/Livewire` using `Livewire::test()`.
- UI flows are covered by Playwright in `tests/playwright`.
- Run `npm run test:a11y` for accessibility changes.

## Commands

- Dev: `npm run dev` and `php artisan serve` (or `composer run dev`).
- Build: `npm run build`.
- Format: `vendor/bin/pint --dirty`, `npm run prettier:fix`.
- Tests: `php artisan test`, `npm run test`, `npm run playwright:test`.
