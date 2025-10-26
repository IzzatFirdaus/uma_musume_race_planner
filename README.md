# 🌸 Uma Musume Career Tracker System Documentation

## Laravel 12+ Compatible

---

## 📘 Overview

Uma Musume Planner is a Laravel 12+ platform designed to track, manage, and analyze the career progression of Uma Musume: Pretty Derby characters. Built with a modern tech stack, it supports turn-by-turn stat logging, dynamic skill management, and career analytics with a smooth, responsive interface.

---

## 🔧 Features

- ✅ Track multiple Uma Musume characters and their career runs
- 📊 Log detailed stats per turn (Speed, Stamina, Power, Guts, Wit)
- 🧠 Track skill acquisition with SP costs, types, and notes
- ⚖️ Log growth rate bonuses and suitability ratings (Track, Distance, Style)
- 📝 Annotate runs with notes and special conditions
- ⬇️ Export full run logs to Excel (.xlsx)
- 📱 Responsive UI powered by TailwindCSS + Alpine.js
- ➕ Add dynamic skill rows in real-time
- 🌙 Supports Dark Mode via class-based toggling
- 🎴 Blade Component System: skill cards, stamina bars, stat grids, aptitude inputs, growth rate inputs, initial career run form, animated transitions, add/remove skill buttons

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

## Frontend Standardization

This project follows a standard frontend stack and conventions to keep the UI consistent, testable, and easy to convert to Livewire-driven components.

Stack (canonical):

- Tailwind CSS v4 (design system and utility classes)
- Vite (asset bundling) — v7.x as declared in `package.json`
- Alpine.js for small client-side interactions
- Livewire v3.x for server-driven interactive components
- Playwright / Jest for end-to-end and unit test automation

Conventions and locations:

- Livewire PHP classes: `app/Livewire/...` (PSR-4 namespace `App\\Livewire\\...`)
- Livewire views: `resources/views/livewire/...` (use kebab-case file names)
- Blade components: `resources/views/components/...` (stateless UI pieces)
- Shared JS: `resources/js/` and component-specific scripts in the same folder
- Shared CSS & Tailwind config: `resources/css/`, `tailwind.config.js`

Developer commands (project root):

- Install deps: `composer install` and `npm install`
- Development (hot-reload): `npm run dev` (Vite) and `php artisan serve` or the repo `dev` script
- Build assets: `npm run build`
- Format PHP: `vendor/bin/pint --dirty` (or run pint to auto-fix)
- Run PHP tests: `php artisan test` (or `composer test` / package script)
- Run frontend tests: `npm run test` (Jest) and `npm run playwright:test`

Livewire & conversion guidance (short):

- Prefer Livewire for server-stateful UI: forms that submit, add/remove lists, server-side validation, file uploads.
- Keep tiny UI-only behaviors (animations, simple toggles) in Alpine.js.
- When adding Livewire components, follow the file layout above and add a Feature Livewire test under `tests/Feature/Livewire/`.

Accessibility & testing:

- Maintain WCAG AA contrast and keyboard navigation for all converted components.
- Add Playwright tests for critical flows (form submit, modal dialogs, add/remove rows).

If you plan mass conversions, follow the mapping and workflow in `docs/livewire-conversion-mapping.md`.

---

## 🧩 Data Models

### 🔹 UmaMusume

| Field             | Type    | Description                                     |
| ----------------- | ------- | ----------------------------------------------- |
| id                | int, PK | Unique identifier for the Uma Musume character. |
| name              | string  | The character's name.                           |
| image_url         | string  | URL to the character's image.                   |
| aptitude_style    | json    | Array of running style suitabilities.           |
| aptitude_distance | json    | Array of distance suitabilities.                |
| aptitude_track    | json    | Array of track suitabilities.                   |
| growth_speed      | int     | Bonus percentage for Speed growth.              |
| growth_stamina    | int     | Bonus percentage for Stamina growth.            |

### 🔹 CareerRun

| Field              | Type    | Description                                            |
| ------------------ | ------- | ------------------------------------------------------ |
| id                 | int, PK | Unique identifier for the career run.                  |
| uma_musume_id      | int, FK | Foreign key linking to the UmaMusume character.        |
| year               | enum    | Current career year (e.g., Junior, Classic, Senior).   |
| status             | enum    | Current status of the run (Ongoing, Finished, Failed). |
| uma_class          | enum    | Uma's current class (Debut to Legend).                 |
| current_turn       | int     | The current turn number in the career.                 |
| current_race       | string  | Name of the current race (if applicable).              |
| total_sp_available | int     | Total SP available for skill acquisition.              |
| stamina_percentage | int     | Current stamina percentage.                            |

### 🔹 StatProgress

| Field         | Type    | Description                                       |
| ------------- | ------- | ------------------------------------------------- |
| id            | int, PK | Unique identifier for the stat progress entry.    |
| career_run_id | int, FK | Foreign key linking to the CareerRun.             |
| speed         | int     | Speed stat value for the turn.                    |
| stamina       | int     | Stamina stat value for the turn.                  |
| power         | int     | Power stat value for the turn.                    |
| guts          | int     | Guts stat value for the turn.                     |
| wit           | int     | Wit stat value for the turn.                      |
| turn_number   | int     | The specific turn this stat entry corresponds to. |

### 🔹 Skill

| Field       | Type    | Description                                           |
| ----------- | ------- | ----------------------------------------------------- |
| id          | int, PK | Unique identifier for the skill.                      |
| name        | string  | The name of the skill.                                |
| description | text    | Full description of the skill's effect.               |
| type        | enum    | Category of the skill (e.g., Speed, Accel, Recovery). |
| sp_cost     | int     | The SP (Skill Point) cost to acquire the skill.       |
| best_for    | string  | Recommended usage strategy for the skill.             |

### 🔹 SkillCareerRun

| Field         | Type    | Description                                             |
| ------------- | ------- | ------------------------------------------------------- |
| id            | int, PK | Unique identifier for the association.                  |
| career_run_id | int, FK | Foreign key referencing the CareerRun.                  |
| skill_id      | int, FK | Foreign key referencing the Skill.                      |
| status        | enum    | Status of the skill within the run (Acquired, Skipped). |
| turn_acquired | int     | The turn number when the skill was logged/acquired.     |

---

## 🧭 User Flow Example

1. **Character Registration:** User creates a new UmaMusume record, defining basic info, aptitudes, and growth rates.
2. **Career Initiation:** A CareerRun is started for the newly registered (or an existing) Uma Musume.
3. **Turn-by-Turn Logging:** Each turn, the user logs current stats (StatProgress) and manages skill acquisition/usage.
4. **Skill Actions:** Skills are marked as Acquired, Skipped, or Suggested within the SkillCareerRun context.
5. **Run Conclusion:** The CareerRun is eventually marked as Finished or Failed.
6. **Data Archival:** The complete run log can be exported to Excel for personal records or meta-analysis.

---

## 🔌 Optional API Routes

| Method | Endpoint      | Description                                  |
| ------ | ------------- | -------------------------------------------- |
| GET    | /api/uma      | Retrieve a list of all Uma Musume characters |
| GET    | /api/uma/{id} | View detailed profile of a single Uma Musume |
| POST   | /api/uma      | Register a new Uma Musume                    |
| PUT    | /api/uma/{id} | Update an existing Uma Musume's profile      |
| POST   | /api/career   | Start a new career run for an Uma Musume     |

---

## 🌟 Blade Components (Reusable)

| Component                            | Description                                               |
| ------------------------------------ | --------------------------------------------------------- |
| x-umamusume::skill-card              | Styled input card for managing individual skill details.  |
| x-umamusume::stamina-bar             | Visual meter to represent stamina levels.                 |
| x-umamusume::add-skill-button        | Button component to dynamically add new skill input rows. |
| x-umamusume::remove-skill-button     | Button component to remove dynamic skill input rows.      |
| x-umamusume::aptitude-inputs         | Form inputs for managing Uma Musume aptitude ratings.     |
| x-umamusume::growth-rate-inputs      | Form inputs for managing Uma Musume growth rate bonuses.  |
| x-umamusume::initial-career-run-form | Form section for logging initial stats and career run.    |
| x-umamusume::responsive-grid         | Wrapper for responsive grid layouts.                      |
| x-umamusume::animated-transition     | Alpine.js-driven component for smooth UI transitions.     |

---

## 🔮 Future Roadmap

| Planned Feature                        | Description                                         |
| -------------------------------------- | --------------------------------------------------- |
| 🤖 AI-assisted skill recommendations   | Suggest optimal skills based on career progression. |
| 📈 Graphical stat trend visualizations | Interactive charts to visualize stat progression.   |
| 🔄 Import/export legacy spreadsheets   | Tools for converting/importing existing logs.       |
| 🧠 Skill icon detection/autocomplete   | Real-time skill name suggestions with icons.        |
| 📊 Visual graphs of stat progression   | User-friendly visual tools for deeper analysis.     |
| 🧪 Stat outcome predictors             | Forecast future stat outcomes based on progression. |
| 🧑‍🤝‍🧑 Role-based user collaboration       | User roles for shared tracking environments.        |

---

## 🛠️ Setup Instructions

1. **Clone the repository:**

```bash
git clone https://github.com/IzzatFirdaus/uma-musume-planner-laravel.git
```

1. **Navigate to the project directory:**

```bash
cd uma-musume-planner-laravel
```

1. **Install PHP dependencies using Composer:**

```bash
composer install
```

1. **Set up environment variables:**
   Copy the example environment file and generate an application key:

```powershell
copy .env.example .env
php artisan key:generate
```

(On Linux/macOS, use `cp` instead of `copy`.)

Ensure you configure your database connection in the `.env` file (MySQL, MariaDB, or SQLite for local dev).

1. **Run database migrations and seeders:**

```bash
php artisan migrate --seed
```

1. **Install frontend dependencies and build assets:**

```bash
npm install
npm run build
```

1. **Launch the development server:**

```bash
php artisan serve
```

The application will typically be accessible at <http://127.0.0.1:8000>.

---

## ⚖️ License

MIT License  
Free for academic and personal use. Attribution appreciated.
