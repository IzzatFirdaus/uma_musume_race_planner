# 🌸 Uma Musume Career Planner System

[![Laravel Version](https://img.shields.io/badge/Laravel-12+-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)]()

A Laravel 12+ platform for tracking, managing, and analyzing Uma Musume: Pretty Derby characters' career progression.
This application consolidates **five legacy Uma Musume tracking applications** into a unified, feature-rich platform with both local and account-based storage options.

---

## 📖 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Screenshots](#screenshots)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Installation](#installation)
- [Testing](#testing)
- [Data Models](#data-models)
- [User Flow](#user-flow)
- [API Routes](#api-routes)
- [Blade Components](#blade-components)
- [Frontend Standardization](#frontend-standardization)
- [Future Roadmap](#future-roadmap)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

## 📘 Overview

Uma Musume Planner is a Laravel 12+ platform designed to track, manage, and analyze the career progression of Uma Musume: Pretty Derby characters. Built with a modern tech stack, it supports turn-by-turn stat logging, dynamic skill management, and career analytics with a smooth, responsive interface.

**Key differentiators:**
- **Dual storage modes** – use local storage (browser) for quick offline tracking, or account storage (database) for permanent access.
- **Seamless conversion** – move local runs to your account when you're ready.
- **Import/Export** – support for JSON, CSV, Markdown, Excel, and legacy tracker formats.

---

## 🔧 Features

### Core Features

- ✅ Track multiple Uma Musume characters and their career runs
- 📊 Log detailed stats per turn (Speed, Stamina, Power, Guts, Wit)
- 🧠 Track skill acquisition with 3-state status (Acquired / Skipped / Suggested)
- ⚖️ Log growth rate bonuses and suitability ratings (Track, Distance, Style)
- 📝 Annotate runs with notes and special conditions
- 🏇 Race planning with predictions and race-day snapshots
- 🎯 Goal tracking with achievement status

### Storage & Sync

- 💾 Dual storage modes: **Local** (browser) or **Account** (database)
- 🔄 Convert local runs to account storage when ready
- 📤 Export data to JSON, CSV, Markdown, or Excel
- 📥 Import from legacy tracker formats

### User Experience

- 🌙 Dark/Light mode with system preference detection
- 📱 Responsive mobile-first design
- ⌨️ Full keyboard navigation support
- ♿ WCAG 2.1 AA accessibility compliance
- 🔍 Skill autocomplete with EN + JP search

### API Access

- 🔌 RESTful API v1 for programmatic access
- 📄 Comprehensive API documentation in [docs/api/README.md](docs/api/README.md)

---

## 🖼️ Screenshots

*(Add screenshots of the dashboard, run log, skill management, dark mode, export dialog, etc.)*

---

## 💻 Technologies Used

- **Laravel 12+**
- **PHP 8.2+**
- **MySQL / MariaDB** (or SQLite for local dev)
- **Blade + TailwindCSS v4**
- **Alpine.js** (for dynamic forms)
- **Laravel Eloquent ORM**
- **Laravel Excel (Maatwebsite)**
- **Laravel Sanctum** (optional API authentication)
- **Livewire v3** – for server-driven interactive components
- **Vite** – asset bundling
- **Playwright / Jest** – testing

---

## 📋 Prerequisites

Before installing, ensure your system meets the following requirements:

- PHP 8.2 or higher with extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL
- Composer
- Node.js 18+ and npm
- MySQL 5.7+ / MariaDB 10.2+ (or SQLite)
- Optional: Laravel Sail for Docker-based development

---

## 🚀 Quick Start (Laravel Sail)

If you're using Docker, you can get started in one command:

```bash
curl -s https://laravel.build/uma-planner | bash
cd uma-planner
./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm install && ./vendor/bin/sail npm run build
./vendor/bin/sail artisan migrate --seed
```

For a traditional local setup, follow the [Installation](#installation) steps below.

## 🛠️ Installation

1. Clone the repository:

```bash
git clone https://github.com/IzzatFirdaus/uma-musume-planner-laravel.git
cd uma-musume-planner-laravel
```

2. Install PHP dependencies:

```bash
composer install
```

3. Set up environment variables:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database connection (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

For SQLite, set `DB_CONNECTION=sqlite` and create an empty `database/database.sqlite` file.

> Optional: If you want to enable local storage mode by default, set `STORAGE_MODE=local` in your `.env`.

4. Run database migrations and seeders:

```bash
php artisan migrate --seed
```

5. Install and build frontend assets:

```bash
npm install
npm run build
```

6. Start the development server:

```bash
php artisan serve
```

The application will be accessible at [http://127.0.0.1:8000](http://127.0.0.1:8000).

## 🧪 Testing

The project includes both backend (PHPUnit) and frontend (Jest / Playwright) tests.

- Run all PHP tests:

```bash
php artisan test
```

or

```bash
composer test
```

- Run frontend unit tests (Jest):

```bash
npm run test
```

- Run end-to-end tests (Playwright):

```bash
npm run playwright:test
```

- Format PHP code with Laravel Pint:

```bash
vendor/bin/pint --dirty
```

## 🧩 Data Models

### 🔹 UmaMusume

| Field | Type | Description |
| --- | --- | --- |
| id | int, PK | Unique identifier for the Uma Musume character. |
| name | string | The character's name. |
| image_url | string | URL to the character's image. |
| aptitude_style | json | Array of running style suitabilities. |
| aptitude_distance | json | Array of distance suitabilities. |
| aptitude_track | json | Array of track suitabilities. |
| growth_speed | int | Bonus percentage for Speed growth. |
| growth_stamina | int | Bonus percentage for Stamina growth. |

### 🔹 CareerRun

| Field | Type | Description |
| --- | --- | --- |
| id | int, PK | Unique identifier for the career run. |
| uma_musume_id | int, FK | Foreign key linking to the UmaMusume character. |
| year | enum | Current career year (e.g., Junior, Classic, Senior). |
| status | enum | Current status of the run (Ongoing, Finished, Failed). |
| uma_class | enum | Uma's current class (Debut to Legend). |
| current_turn | int | The current turn number in the career. |
| current_race | string | Name of the current race (if applicable). |
| total_sp_available | int | Total SP available for skill acquisition. |
| stamina_percentage | int | Current stamina percentage. |

### 🔹 StatProgress

| Field | Type | Description |
| --- | --- | --- |
| id | int, PK | Unique identifier for the stat progress entry. |
| career_run_id | int, FK | Foreign key linking to the CareerRun. |
| speed | int | Speed stat value for the turn. |
| stamina | int | Stamina stat value for the turn. |
| power | int | Power stat value for the turn. |
| guts | int | Guts stat value for the turn. |
| wit | int | Wit stat value for the turn. |
| turn_number | int | The specific turn this stat entry corresponds to. |

### 🔹 Skill

| Field | Type | Description |
| --- | --- | --- |
| id | int, PK | Unique identifier for the skill. |
| name | string | The name of the skill. |
| description | text | Full description of the skill's effect. |
| type | enum | Category of the skill (e.g., Speed, Accel, Recovery). |
| sp_cost | int | The SP (Skill Point) cost to acquire the skill. |
| best_for | string | Recommended usage strategy for the skill. |

### 🔹 SkillCareerRun

| Field | Type | Description |
| --- | --- | --- |
| id | int, PK | Unique identifier for the association. |
| career_run_id | int, FK | Foreign key referencing the CareerRun. |
| skill_id | int, FK | Foreign key referencing the Skill. |
| status | enum | Status of the skill within the run (Acquired, Skipped). |
| turn_acquired | int | The turn number when the skill was logged/acquired. |

## 🧭 User Flow

1. Character Registration: User creates a new UmaMusume record, defining basic info, aptitudes, and growth rates.
2. Career Initiation: A CareerRun is started for the newly registered or an existing Uma Musume.
3. Turn-by-Turn Logging: Each turn, the user logs current stats (StatProgress) and manages skill acquisition and usage.
4. Skill Actions: Skills are marked as Acquired, Skipped, or Suggested within the SkillCareerRun context.
5. Run Conclusion: The CareerRun is eventually marked as Finished or Failed.
6. Data Archival: The complete run log can be exported to Excel for personal records or meta-analysis.

## 🔌 Optional API Routes

The API is protected by Sanctum and requires a valid token.

| Method | Endpoint | Description | Request Example (JSON) |
| --- | --- | --- | --- |
| GET | /api/uma | List all Uma Musume characters | - |
| GET | /api/uma/{id} | View detailed profile of a single character | - |
| POST | /api/uma | Register a new Uma Musume | {"name":"Special Week","image_url":"...","aptitude_style":["A","B"],"...":"..."} |
| PUT | /api/uma/{id} | Update an existing Uma Musume's profile | Same as POST |
| POST | /api/career | Start a new career run for an Uma Musume | {"uma_musume_id":1,"year":"Junior","status":"Ongoing","...":"..."} |

For detailed API documentation, including error codes and pagination, refer to [docs/api/README.md](docs/api/README.md).

## 🌟 Blade Components (Reusable)

| Component | Description |
| --- | --- |
| x-umamusume::skill-card | Styled input card for managing individual skill details. |
| x-umamusume::stamina-bar | Visual meter to represent stamina levels. |
| x-umamusume::add-skill-button | Button to dynamically add new skill input rows. |
| x-umamusume::remove-skill-button | Button to remove dynamic skill input rows. |
| x-umamusume::aptitude-inputs | Form inputs for managing aptitude ratings. |
| x-umamusume::growth-rate-inputs | Form inputs for managing growth rate bonuses. |
| x-umamusume::initial-career-run-form | Form section for logging initial stats and career run. |
| x-umamusume::responsive-grid | Wrapper for responsive grid layouts. |
| x-umamusume::animated-transition | Alpine.js-driven component for smooth UI transitions. |

Each component accepts standard Blade attributes; see the individual component class for available props.

## 🧠 Frontend Standardization

This project follows a consistent frontend stack and conventions to keep the UI maintainable and testable. Detailed guidelines are documented in [docs/frontend-guidelines.md](docs/frontend-guidelines.md). In summary:

- **Stack:** TailwindCSS v4, Vite, Alpine.js, Livewire v3
- **Livewire components:** `app/Livewire/` (PHP) and `resources/views/livewire/` (Blade, kebab-case names)
- **Blade components:** `resources/views/components/` (stateless UI)
- **JavaScript:** Shared logic in `resources/js/`, component-specific scripts in the same folder
- **CSS:** `resources/css/` and `tailwind.config.js`

Developer commands:

- `composer install` / `npm install`
- `npm run dev` for Vite hot-reload
- `npm run build` for a production build
- `vendor/bin/pint` for automatic PHP formatting

When converting existing UI to Livewire, prefer Livewire for server-stateful interactions and keep Alpine.js for purely client-side animations and toggles. Add Feature tests for each Livewire component.

## 🔮 Future Roadmap

| Planned Feature | Description |
| --- | --- |
| AI-assisted skill recommendations | Suggest optimal skills based on career progression. |
| Graphical stat trend visualizations | Interactive charts to visualize stat progression. |
| Skill icon detection/autocomplete | Real-time skill name suggestions with icons. |
| Stat outcome predictors | Forecast future stat outcomes based on progression. |
| Role-based user collaboration | Shared tracking environments with user roles. |
| Snapshot comparison | Side-by-side race-day snapshot comparison. |

## 📚 Documentation

Detailed documentation is available in the `docs/` directory:

- [API Documentation](docs/api/README.md) - RESTful endpoints and usage
- [Component Documentation](docs/components/README.md) - Livewire component reference
- [User Guide](docs/user-guide/README.md) - How to use the application
- [Migration Guide](docs/migration/README.md) - Importing data from legacy trackers
- [Frontend Guidelines](docs/frontend-guidelines.md) - UI and JavaScript standards

## 🤝 Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before submitting a pull request.

- Report bugs or request features via Issues.
- Follow the PSR-12 coding standard.
- Write tests for new features and ensure all tests pass.

## 🛠️ Troubleshooting

| Issue | Solution |
| --- | --- |
| PDOException when running migrations | Check database credentials in `.env`; ensure the database server is running. |
| `npm run build` fails | Clear the npm cache and reinstall dependencies. |
| Livewire component not rendering | Verify the component is registered and the view path matches the PHP class namespace. |
| Dark mode not toggling | Ensure the `dark` class is applied to the `html` element and check the JavaScript toggle logic. |
| Excel export fails | Confirm the `maatwebsite/excel` package is installed and required PHP extensions are enabled. |
| Permissions errors in storage or cache | Adjust permissions for `storage` and `bootstrap/cache` as needed for your environment. |
| Local storage data not persisting | Check browser local storage availability and quota limits. |

## ⚖️ License

MIT License - free for academic and personal use. Attribution appreciated.

Happy planning, trainers!
