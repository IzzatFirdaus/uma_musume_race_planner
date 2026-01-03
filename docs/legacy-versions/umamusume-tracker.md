# UmaMusume Tracker (Legacy)

**Location:** `C:\XAMPP\htdocs\umamusume-tracker`  
**Version:** Laravel 12 + React  
**Status:** Legacy - To be consolidated

## Overview

A modern Laravel 12 + React (Vite) tracker for UmaMusume careers, skills, and stats. Features a monorepo layout with Laravel API backend and separate React client.

## Tech Stack

### Backend

- PHP 8.2+
- Laravel 12 (framework)
- Eloquent ORM
- MySQL / MariaDB / PostgreSQL / SQLite (configurable)
- PHPUnit/Pest for testing
- Maatwebsite/Laravel-Excel for exports
- Laravel Sanctum for API auth (optional)

### Frontend

- React 18.x with TypeScript
- Vite 5.x (build tool)
- React Query 5.x (data fetching)
- Tailwind CSS 3.x (styling)
- Zod (validation)
- Vitest + Testing Library (testing)

## Key Features

- REST API with Eloquent ORM
- API key authentication (X-API-Key header)
- Rate limiting (Laravel throttle)
- Export to CSV (XLSX optional with Laravel Excel)
- Career, Uma, Skill management
- Separate React client application

## Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                     Client Application (React)                  │
│  Character Management │ Plan Editor │ Skills │ Export Module   │
└─────────────────────────────┬────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      API Layer (Laravel)                         │
│  Character Controller │ Plan Controller │ Skills │ Export       │
│                    Application Services                          │
│                      Domain Layer                                │
│                    Data Access Layer                             │
└─────────────────────────────┬────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         Database                                 │
│  characters │ plans │ plan_turns │ plan_skills │ snapshots      │
└─────────────────────────────────────────────────────────────────┘
```

## Directory Structure

```
umamusume-tracker/
├── .github/                    # GitHub Actions workflows
├── app/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/Api/    # API controllers
│   │   └── Middleware/
│   ├── Models/                 # Eloquent models
│   ├── Providers/
│   └── Repositories/           # Repository pattern
├── client/                     # React frontend (Vite)
│   ├── src/
│   └── package.json
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/                       # Documentation
├── public/
├── resources/
├── routes/
│   ├── api.php
│   └── web.php
├── storage/
├── tests/
├── artisan
├── composer.json
└── vite.config.js
```

## API Endpoints

### Character Management

- `GET /api/uma` - List characters
- `POST /api/uma` - Create character
- `GET /api/uma/{id}` - Character details
- `PUT /api/uma/{id}` - Update character
- `DELETE /api/uma/{id}` - Delete character

### Career/Plan Management

- `GET /api/plans` - List plans
- `POST /api/plans` - Create plan
- `GET /api/plans/{id}` - Plan details
- `PUT /api/plans/{id}` - Update plan
- `DELETE /api/plans/{id}` - Delete plan

### Export

- `GET /api/plans/{id}/export?format=csv` - CSV export
- `GET /api/plans/{id}/export?format=xlsx` - XLSX export (requires Laravel Excel)

## Data Models

### Uma (Character)

```php
protected $fillable = [
    'name',
    'image_url',
    'aptitude_style',
    'aptitude_distance',
    'aptitude_track',
    'growth_speed',
    'growth_stamina',
    'growth_power',
    'growth_guts',
    'growth_wit',
];
```

### CareerRun (Plan)

```php
protected $fillable = [
    'uma_id',
    'year',
    'status',
    'uma_class',
    'current_turn',
    'current_race',
    'total_sp_available',
    'stamina_percentage',
];
```

## Features to Migrate

### High Priority

- [ ] REST API structure and endpoints
- [ ] Repository pattern implementation
- [ ] Export functionality (CSV/XLSX)
- [ ] API authentication middleware

### Medium Priority

- [ ] React component patterns (for reference)
- [ ] React Query data fetching patterns
- [ ] Zod validation schemas

### Low Priority

- [ ] Separate client architecture (consolidate into Livewire)

## Configuration

Backend `.env`:

```ini
DB_CONNECTION=sqlite
API_KEY=yourkey
```

Client `.env`:

```ini
VITE_API_URL=http://127.0.0.1:8000/api
```

## Engineering Documentation

This version includes comprehensive engineering documentation:

- `UMAMUSUME-TRACKER.md` - Full implementation guide
- `UMAMUSUME-TRACKER-ENGINEERING-IMPLEMENTATION.md` - Technical specs
- Development roadmap with 12-week timeline
- Testing framework specifications
- Deployment architecture

## Notes

- Most mature API implementation
- Good reference for REST API patterns
- React client can inform future SPA features
- Repository pattern worth adopting
