# Frontend Guidelines

This document collects the frontend implementation standards for the Uma Musume Career Planner application.

## Stack

- Tailwind CSS v4 for styling and design tokens
- Vite for asset bundling
- Alpine.js for small client-only interactions
- Livewire v3 for server-driven UI and stateful components
- Playwright and Jest for UI and frontend test coverage

## Directory Conventions

- Livewire PHP classes live in `app/Livewire/`
- Livewire Blade views live in `resources/views/livewire/`
- Blade components live in `resources/views/components/`
- Shared JavaScript lives in `resources/js/`
- Shared styles and design tokens live in `resources/css/` and `tailwind.config.js`

## Livewire Usage

- Use Livewire for forms, row add/remove flows, validation, and other server-stateful interactions.
- Keep Alpine.js for small client-side behaviors such as toggles, transient animations, and simple UI state.
- Livewire components must have a single root element.
- Use `wire:model.live` for live updates where the UI should react immediately.
- Add `wire:key` in loops to keep DOM updates stable.
- Keep business logic in services once a component starts accumulating non-trivial rules.

## Accessibility

- Target WCAG 2.1 AA.
- Preserve keyboard navigation, focus states, labels, and contrast.
- Maintain ARIA roles and labels for interactive elements.
- Validate important flows with accessibility checks when UI changes affect semantics or keyboard behavior.

## Testing

- Add Livewire tests under `tests/Feature/Livewire/` using `Livewire::test()`.
- Add Playwright coverage for critical end-to-end flows.
- Run `npm run test:a11y` when accessibility-sensitive UI changes are introduced.

## Common Commands

- `composer install`
- `npm install`
- `npm run dev`
- `npm run build`
- `vendor/bin/pint --dirty`
- `php artisan test`
- `npm run test`
- `npm run playwright:test`

## Conversion Guidance

- Prefer Livewire for server-stateful UI such as submit flows, add/remove lists, file uploads, and validation-heavy forms.
- Keep tiny UI-only behaviors in Alpine.js.
- When adding a Livewire component, create the PHP class, Blade view, and a Feature test together.
- Preserve WCAG AA contrast and keyboard navigation in every converted component.
- Follow the Livewire conversion mapping in `docs/livewire-conversion-mapping.md` when planning larger migrations.