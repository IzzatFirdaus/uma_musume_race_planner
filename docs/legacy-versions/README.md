# Legacy Uma Musume Tracker Versions

This directory documents the various iterations of the Uma Musume tracking applications that existed before consolidation into the main Laravel application.

## Version Overview

| Version                                                 | Tech Stack              | Status | Key Features                                   |
| ------------------------------------------------------- | ----------------------- | ------ | ---------------------------------------------- |
| [uma_musume_race_planner](./uma_musume_race_planner.md) | PHP + MySQL + Bootstrap | Legacy | Full plan management, dark mode, image uploads |
| [umamusume-tracker](./umamusume-tracker.md)             | Laravel 12 + React      | Legacy | API-first, React client, CSV/XLSX export       |
| [uma-tracker](./uma-tracker.md)                         | Laravel 11 + Blade      | Legacy | Excel export, Blade components, Livewire       |
| [uma-run-tracker](./uma-run-tracker.md)                 | Static HTML + JS        | Legacy | Offline-first, local storage, markdown export  |
| [uma-tracker-form](./uma-tracker-form.md)               | Native PHP + MVC        | Legacy | Simple form entry, CSRF protection             |

## Consolidation Target

All features from these legacy versions are being consolidated into the main `uma-musume-planner-laravel` application, which uses:

- Laravel 12+ with PHP 8.2+
- Livewire v3 for interactive components
- Tailwind CSS v4 for styling
- Alpine.js for client-side interactions
- MySQL/MariaDB for data persistence

## Migration Path

See [CONSOLIDATION_SPEC.md](./CONSOLIDATION_SPEC.md) for the detailed migration and consolidation plan.
