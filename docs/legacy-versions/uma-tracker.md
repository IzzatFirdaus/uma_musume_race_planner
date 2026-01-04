# Uma Tracker (Legacy)

**Location:** `C:\XAMPP\htdocs\uma-tracker`  
**Version:** Laravel 11+  
**Status:** Legacy - To be consolidated

## Overview

A Laravel 11+ application for tracking Uma Musume training careers. Features Excel export, Blade components, and a game-inspired UI with dark/light mode support.

## Tech Stack

### Backend

- Laravel 11+
- PHP 8.2+
- MySQL/MariaDB
- Laravel Excel (Maatwebsite)
- Laravel Sanctum (API)

### Frontend

- Blade Components
- Tailwind CSS
- Alpine.js
- Laravel Livewire (partials)

## Key Features

### Training Management

- Complete character profiles with aptitudes
- Turn-by-turn stat tracking (Speed/Stamina/Power/Guts/Wit)
- Skill acquisition logging with SP management
- Growth rate bonus tracking

### Game-Inspired UI

- Authentic Uma Musume visual style
- Dark/light mode toggle
- Fully responsive mobile interface
- Interactive training dashboard

### Data Tools

- Excel export for career runs
- Skill search and autocomplete
- Stat progression visualization
- Import/export functionality

## Directory Structure

```
uma-tracker/
├── _docs/                      # Internal documentation
│   ├── DEVELOPMENT.md
│   └── UMAMUSUME.md
├── app/
│   ├── Exports/                # Excel export handlers
│   │   ├── CareerRunsExport.php
│   │   ├── SkillCareerRunExport.php
│   │   └── StatProgressExport.php
│   ├── Helpers/
│   │   ├── functions.php
│   │   └── UiHelpers.php
│   ├── Http/Controllers/
│   │   ├── Auth/               # Authentication controllers
│   │   ├── CareerRunController.php
│   │   ├── ExportPreviewController.php
│   │   ├── ProfileController.php
│   │   ├── SkillCareerRunController.php
│   │   ├── StatProgressController.php
│   │   └── UmaMusumeController.php
│   ├── Models/
│   │   ├── CareerRun.php
│   │   ├── Skill.php
│   │   ├── SkillCareerRun.php
│   │   ├── StatProgress.php
│   │   └── UmaMusume.php
│   └── View/
│       ├── Components/
│       └── Composers/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/views/
│   ├── auth/
│   ├── careers/
│   ├── components/
│   │   ├── buttons/
│   │   ├── forms/
│   │   └── layout/
│   ├── layouts/
│   ├── profile/
│   ├── uma/
│   └── umamusume/              # Custom Uma components
│       ├── skill-card.blade.php
│       ├── stamina-bar.blade.php
│       ├── add-skill-button.blade.php
│       ├── responsive-grid.blade.php
│       └── animated-transition.blade.php
├── routes/
│   ├── api.php
│   ├── auth.php
│   └── web.php
└── tests/
```

## Data Models

### UmaMusume

| Field             | Type    | Description                 |
| ----------------- | ------- | --------------------------- |
| id                | int, PK | Unique identifier           |
| name              | string  | Character name              |
| image_url         | string  | Character image URL         |
| aptitude_style    | json    | Running style suitabilities |
| aptitude_distance | json    | Distance suitabilities      |
| aptitude_track    | json    | Track suitabilities         |
| growth_speed      | int     | Speed growth bonus %        |
| growth_stamina    | int     | Stamina growth bonus %      |

### CareerRun

| Field              | Type    | Description                          |
| ------------------ | ------- | ------------------------------------ |
| id                 | int, PK | Unique identifier                    |
| uma_musume_id      | int, FK | Character reference                  |
| year               | enum    | Career year (Junior/Classic/Senior)  |
| status             | enum    | Run status (Ongoing/Finished/Failed) |
| uma_class          | enum    | Current class (Debut to Legend)      |
| current_turn       | int     | Current turn number                  |
| total_sp_available | int     | Available SP                         |

### StatProgress

| Field         | Type    | Description          |
| ------------- | ------- | -------------------- |
| career_run_id | int, FK | Career run reference |
| speed         | int     | Speed stat value     |
| stamina       | int     | Stamina stat value   |
| power         | int     | Power stat value     |
| guts          | int     | Guts stat value      |
| wit           | int     | Wit stat value       |
| turn_number   | int     | Turn number          |

## Blade Components

| Component                 | Description                                |
| ------------------------- | ------------------------------------------ |
| `<x-uma::skill-card>`     | Interactive skill management card          |
| `<x-uma::stamina-bar>`    | Animated stamina gauge                     |
| `<x-uma::stat-radial>`    | Circular stat progress indicator           |
| `<x-uma::aptitude-badge>` | Style/distance/track suitability indicator |
| `<x-uma::training-log>`   | Turn history timeline                      |

## API Endpoints

### Character Management

- `GET /api/uma` - List characters
- `POST /api/uma` - Create new character
- `GET /api/uma/{id}` - Character details

### Career Tracking

- `POST /api/career` - Start new career
- `POST /api/career/{id}/stats` - Log turn stats
- `POST /api/career/{id}/skills` - Manage skills

### Data Export

- `GET /api/export/career/{id}` - Excel export
- `GET /api/export/skills` - Skill report

## Features to Migrate

### High Priority

- [ ] Excel export classes (CareerRunsExport, etc.)
- [ ] Blade components (skill-card, stamina-bar, etc.)
- [ ] UI Helpers
- [ ] View Composers

### Medium Priority

- [ ] Authentication flow
- [ ] Profile management
- [ ] Export preview functionality

### Low Priority

- [ ] Test factories and seeders

## Roadmap (from original)

### v1.2 (Completed)

- [x] Dark mode support
- [x] Mobile optimization

### v1.3 (Planned)

- [ ] AI training suggestions
- [ ] Race simulation
- [ ] Multi-language support

### v2.0 (Future)

- [ ] Team management
- [ ] Scenario builder
- [ ] Community sharing

## Notes

- Most similar to current Laravel implementation
- Blade components can be directly migrated
- Excel export classes are production-ready
- Good reference for UI/UX patterns
