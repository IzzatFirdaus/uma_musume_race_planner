# Uma Musume Race Planner (Legacy)

**Location:** `C:\XAMPP\htdocs\uma_musume_race_planner`  
**Version:** v1.4.0  
**Status:** Legacy - To be consolidated

## Overview

A lightweight PHP + MySQL web application for planning and tracking turn-based training strategies, stat development, skill acquisition, and race goals. Built for fast manual data entry with autosuggestions, clean interfaces, and no login requirement.

## Tech Stack

- **Frontend:** HTML, CSS (Bootstrap 5), Vanilla JavaScript
- **Backend:** PHP 8.1+, Composer
- **Database:** MySQL / MariaDB
- **Logging:** Monolog

## Key Features

### Visual Enhancements (v1.4.0)

- Trainee Image Management - upload and display trainee images
- Stat Progression Chart - line graph visualization of stat growth
- Dynamic Theming - configurable accent color via `.env`

### Core Functionality

- Detailed Plan Management (CRUD operations)
- Two Editing Views: Full-screen Details Modal + Inline Details Panel
- Dynamic Dashboard with quick stats and activity log

### Utility & UX

- Quick Create Modal for rapid plan creation
- Dark Mode toggle
- Plain Text Export (Copy to Clipboard)
- Active Navbar Links highlighting

## Database Schema

### Core Tables

- `plans` - Main plan info including `trainee_image_path`
- `attributes` - Five core stats (Speed, Stamina, Power, Guts, Wit)
- `skills`, `goals`, `race_predictions` - Child tables for detailed tracking
- `terrain_grades`, `distance_grades`, `style_grades` - Aptitude grades
- `turns` - Turn-by-turn stat progression
- `activity_log` - Recent user actions

## Directory Structure

```
uma_musume_race_planner/
├── .vscode/                  # VS Code settings
├── components/               # Reusable UI partials
├── css/                      # Stylesheets
├── includes/                 # Core backend (DB, logger)
├── js/                       # Client-side JavaScript
├── assets/
│   ├── screenshots/          # Documentation images
│   └── images/trainee_images/ # User uploads
├── vendor/                   # Composer dependencies
├── index.php                 # Main entry point
├── guide.php                 # In-app user guide
├── handle_plan_crud.php      # CRUD API endpoint
├── get_*.php                 # Data fetch endpoints
├── .env                      # Environment config
├── composer.json             # PHP dependencies
└── uma_musume_planner.sql    # Database schema
```

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `handle_plan_crud.php` | POST | Create, Update, Delete plans |
| `get_plans.php` | GET | List all plans |
| `get_plan_section.php` | GET | Get specific plan section |
| `get_activities.php` | GET | Get activity log |
| `get_autosuggest.php` | GET | Autocomplete suggestions |
| `get_skill_reference.php` | GET | Skill reference data |
| `get_stats.php` | GET | Dashboard statistics |
| `export_plan_data.php` | GET | Export plan as text |

## Features to Migrate

### High Priority

- [ ] Plan CRUD with image upload
- [ ] Stat progression chart
- [ ] Activity logging
- [ ] Autosuggest functionality
- [ ] Dark mode theming

### Medium Priority

- [ ] Quick create modal
- [ ] Plain text export
- [ ] Guide page content

### Low Priority

- [ ] Legacy endpoint compatibility layer

## Configuration

Environment variables (`.env`):

```ini
DB_HOST=localhost
DB_NAME=uma_musume_planner
DB_USER=root
DB_PASS=
APP_VERSION=v1.4.0
APP_THEME_COLOR=#7d2b8b
LAST_UPDATED="July 29, 2025"
```

## Notes

- Single-user, local/offline use design
- No authentication required
- Ideal for simulation planning and strategy testing
- Contains comprehensive screenshot documentation
