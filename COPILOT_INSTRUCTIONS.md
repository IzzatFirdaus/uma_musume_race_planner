# Uma Musume Career Planner - Copilot Instructions

## Overview

Laravel 12+ app for tracking Uma Musume character progression with dual storage (local/account), turn-by-turn stats, skill management, and career analytics.

**Stack**: Laravel 12+, PHP 8.2+, Livewire 3, Alpine.js, TailwindCSS v4, Vite 7

## Key Features

- Character career tracking: Speed/Stamina/Power/Guts/Wit stats per turn
- Skill acquisition with SP costs and status (acquired/skipped/suggested)
- Aptitude grades (S-G) and growth rates
- Dual storage: localStorage (guests) + database (users)
- Excel export, responsive UI, dark mode

## Commands

```bash
# Setup
composer install && npm install && php artisan migrate --seed
# Dev
composer run dev  # All services
# Test
php artisan test && npm run playwright:test
# Format
vendor/bin/pint --dirty && npm run prettier:fix
```

## Architecture

**Storage Modes**:

- Local: localStorage + UUID routes (`/plans/local/{uuid}`)
- Account: Database + ID routes (`/plans/{id}`)

**Models**: UmaMusume → CareerRun → StatProgress/SkillCareerRun
**Flow**: User → Livewire → Service → Database/localStorage

## Key Enums

```php
enum StorageMode { Local, Account }
enum RunStatus { InProgress, Completed }
enum SkillStatus { Acquired, Skipped, Suggested }
enum AptitudeGrade { S, A, B, C, D, E, F, G }
```

## Components

**Livewire**: Dashboard, PlanView/Edit, LocalPlanView/Edit, PlanList, SkillsEditor, TurnsEditor
**Alpine**: x-dropdown, x-modal, x-tabs, x-dark-mode, x-toast

## Services

- CareerRunService: CRUD + stat calculations
- LocalRunStorageService: localStorage serialization
- ImportService: Data import with duplicate detection
- ExportService: Excel generation

## Structure

```
app/Livewire/     # Components
app/Services/     # Business logic
app/Models/       # Eloquent + enums
resources/views/livewire/  # Component views
resources/views/components/  # Blade components
```

## Patterns

- Soft deletes, JSON columns, UUID support
- Component-based Blade architecture
- Service layer for business logic
- Enum casting for type safety
- Responsive TailwindCSS + Alpine.js interactions

## Testing

Feature tests for Livewire, unit tests for services, Playwright E2E, Jest frontend

**Focus**: Maintain dual storage architecture, use service layer patterns, ensure local/account mode compatibility.
