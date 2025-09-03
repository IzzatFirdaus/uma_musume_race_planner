# 🐎 Uma Musume Race Planner

A lightweight PHP + MySQL web application for planning and tracking turn-based training strategies, stat development, skill acquisition, and race goals inspired by Uma Musume. Built for fast manual data entry with autosuggestions, clean interfaces, and no login — ideal for offline strategy planners.

---

## Application Preview

### Application Screenshots

Screenshots will be added to this repository soon. The UI includes a dashboard with plan list and stats, a full-screen plan editor with tabs (General, Attributes, Aptitudes, Skills, Race Predictions, Goals), a quick-create modal, dark mode, and an in-app guide.
## ✨ Features

- **Visual Enhancements (New in v1.4.0)**
  - **Trainee Image Management:** Personalize each plan by uploading a trainee image, which appears in the editor and as a thumbnail on the main dashboard.
  - **Stat Progression Chart:** A new "Progress Chart" tab in the editor provides a line graph visualizing the trainee's stat growth.
  - **Dynamic Theming:** The application's primary accent color is now configurable via the `.env` file.
- **Core Functionality**
  - **Detailed Plan Management:** Create, view, update, and delete comprehensive training plans.
  - **Two Editing Views:** A full-screen **Details Modal** for in-depth editing and an **Inline Details Panel** for quick access.
  - **Dynamic Dashboard:** Includes panels for quick stats and a log of recent activity.
- **Utility & UX**
  - **Quick Create Modal:** Quickly start a new plan with essential details.
  - **Dark Mode:** A theme toggle for user comfort.
  - **Plain Text Export:** A "Copy to Clipboard" feature generates a clean summary of any plan, perfect for sharing.
  - **Active Navbar Links:** The navbar now highlights the active page for better navigation.

---

## 🖥️ Tech Stack

- **Frontend**: HTML, CSS (Bootstrap 5), Vanilla JavaScript
- **Backend**: PHP 8.1+ (Monolog 3.x), Composer
- **Database**: MySQL / MariaDB

---

## 🎨 Design & Mobile Guidance (VERSION 3)

- Stat color mapping (used in UI and charts):
  - Speed: blue (`--color-stat-speed` or alias `--color-speed`)
  - Stamina: green (`--color-stat-stamina` or alias `--color-stamina`)
  - Power: red (`--color-stat-power` or alias `--color-power`)
  - Guts: orange (`--color-stat-guts` or alias `--color-guts`)
  - Wit: purple (`--color-stat-wit` or alias `--color-wit`)

- The UI is mobile-first. Touch targets are sized >=44px and components use responsive stacking. The default font is `Figtree` with a rounded fallback.


## 🎯 VERSION 6 — Frontend Redesign (2025-09-02)

This release delivers a major UI overhaul to match Uma Musume style (no direct copyright):

**What's new:**
- Theme: "M PLUS Rounded 1c" font, pastel stat palette, pill-shaped buttons/tabs.
- Card-based dashboard and plan list: responsive grid, soft shadows, emoji headers.
- Accessibility: ARIA attributes, visible focus, keyboard navigation, WCAG AA contrast.
- Component refactors:
  - `x-stat-bar.php`: stat color via CSS variable, emoji icon, animated fill.
  - `x-skill-card.php`: pastel card, colored border by skill tag, pill action button.
  - `x-plan-card.php`: plan card with emoji, badges, summary strip, pill Open button.
  - `plan-list.php`: replaced table with card grid, pill filters, accessible tablist.
  - `plan_details_modal.php` & `plan-inline-details.php`: emoji title, summary strip, pill footer buttons.
- Created: `css/theme_v6.css` (font import, stat palette, card/pill styles).
- Improved: accessibility, keyboard navigation, ARIA labels.

**Validation:**
- No PHP/JS errors in updated files.
- Smoke test output matches new card/pill UI.

For full roadmap and design history, see `APPLICATION_PLANNING.md`.


## 📖 VERSION 7 — As-Built Frontend & Future Iteration (2025-09-02)

This milestone documents the "As-Built" frontend (see SPEC-06-UMA-MUSUME-UI-AS-BUILT):
- Bootstrap 5, vanilla JS, custom CSS variables, system-native font stack
- Responsive dashboard, plan editor with tabs, dynamic skill/goal/prediction rows
- Stat color system standardized via CSS variables (`--color-speed`, etc.)
- Dark mode toggle with smooth transitions
- Export as styled text (inline and modal)
- Accessibility: ARIA labels, keyboard navigation, touch targets
- Avatar upload/preview in plan forms
- Growth stat calculators in plan details
- Enhanced iconography (Bootstrap Icons, custom SVGs)
- Sticky mobile footer for Save/Export actions

**Next Iteration Plan:**
- Refine accessibility and dark mode
- Expand stat overview panel
- Integrate AI SP optimization suggestions
- Add game-style modal previews with transitions
- Run UI and accessibility tests (Playwright, Lighthouse)

All changes and planning are tracked in `APPLICATION_PLANNING.md` and `docs/components.md`.

## 🚀 Getting Started

### Prerequisites

- A local web server environment (e.g., Laragon, XAMPP, WAMP, MAMP).
- PHP 8.1 or higher.
- MySQL or MariaDB database server.
- [Composer](https://getcomposer.org/) for managing PHP dependencies.

### 1. Clone the Repository

```bash
git clone https://github.com/IzzatFirdaus/uma_musume_race_planner.git
cd uma_musume_race_planner
```

### 2\. Install Dependencies

The project uses Monolog for logging. Install it using Composer.

```bash
composer install
```

### 3. Database Setup

1. **Create the Database:** Using a tool like phpMyAdmin, create a new database. The default name is `uma_musume_planner`.

2. **Import the Schema:** Import the database structure by executing the `uma_musume_planner.sql` file. This will create all the necessary tables.

3. **(Optional) Load Sample Data:** You can populate the database with example plans and reference data by importing `sample_data.sql`.

### 4. Environment Configuration

1. Copy the example file and then edit it:

  - Copy `.env.example` to `.env`.
  - Open `.env` and adjust values to your local setup.

2. The application supports either DB_NAME/DB_USER/DB_PASS or DB_DATABASE/DB_USERNAME/DB_PASSWORD. A typical configuration looks like this:

   ```ini
   # .env - Local Development Configuration

   # Database Configuration
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=uma_musume_planner
   DB_USERNAME=root
   DB_PASSWORD=

   # Application Settings
   APP_DEBUG=false
   APP_THEME_COLOR=#6f42c1
   APP_VERSION=2025.09.01
   LAST_UPDATED="2025-09-01"
   ```

### 5. Running the Application

Place the project folder inside your web server's root directory and navigate to it in your browser.

- Laragon: `C:/laragon/www/uma_musume_race_planner` → http://localhost/uma_musume_race_planner/
- XAMPP: `C:/xampp/htdocs/uma_musume_race_planner` → http://localhost/uma_musume_race_planner/

---

## 📁 Folder Structure

```bash
uma_musume_race_planner/
│
├── components/               # Reusable UI partials (navbar, footer, plan list, etc.)
├── css/                      # Main application stylesheets
├── includes/                 # Core backend scripts (env loader, DB connection, logger)
├── js/                       # Client-side JavaScript utilities (e.g., autosuggest.js)
├── tests/                    # UI tests (Playwright) and future test assets
├── tools/                    # Dev tools and scripts (SQL audits, smoke tests)
├── uploads/                  # Directory for user-uploaded content
│   ├── trainee_images/       # Uploaded trainee images
│   ├── app_bg/               # App background assets (optional)
│   └── app_logo/             # App logo assets (optional)
├── vendor/                   # Composer-managed PHP dependencies
│
├── index.php                 # Main application entry point and dashboard UI
├── guide.php                 # The in-app user guide page
├── handle_plan_crud.php      # Primary API endpoint for Create, Update, & Delete operations
│
├── get_*.php                 # Various API endpoints (e.g., get_plans.php, get_progress_chart_data.php)
├── plan_details_modal.php    # UI for the full-screen plan editor
├── quick_create_plan_modal.php # UI for the quick create plan modal
│
├── .env                      # Environment configuration (DB credentials, app settings)
├── .env.example              # Example environment configuration
├── composer.json             # PHP project dependencies
├── uma_musume_planner.sql    # The complete database schema
├── sample_data.sql           # Optional data for populating the database
│
├── DIRECTORY.md              # Auto-generated detailed file and directory listing
├── README.md                 # This file
├── UMAMUSUME PLANS/          # Reference plan write-ups (docs)
└── ...                       # Other development and config files (.gitignore, phpcs.xml, etc.)
```

---

## 🗃️ Database Overview

- `plans`: The core table storing general plan info, including the `trainee_image_path`.
- `attributes`: Stores the five core stats for each plan.
- `skills`, `goals`, `race_predictions`: Child tables for detailed tracking.
- `terrain_grades`, `distance_grades`, `style_grades`: Aptitude grades.
- `turns`: Stores turn-by-turn stat progression for the Progress Chart.
- `activity_log`: Tracks recent user actions.

Tip: The `get_progress_chart_data.php` endpoint aggregates turn data to feed the chart in the plan editor.

---

## 📌 Notes

- ✅ Built for single-user, local/offline use
- 🔒 No authentication required
- 🐣 Inspired by Uma Musume: Pretty Derby
- 🧪 Ideal for simulation planning and strategy testing

---

## 🧩 To-Do

- [x] Autosuggest skills, races, names
- [x] Soft-delete support
- [x] Export plans as formatted text
- [x] Stat progression chart
- [ ] Optional login & cloud sync support
- [ ] Advanced search/filtering (by stats, skills, etc.)

---

## 📜 License

MIT License © 2025

---

## 🔧 Troubleshooting

- Database connection errors: verify DB host/port, database name, and credentials in `.env`. Both `DB_*` and `DB_*NAME/DB_*USER` styles are supported.
- Blank page or PHP errors: set `APP_DEBUG=true` in `.env` to increase log verbosity; check your PHP error log.
- Uploads not saving: ensure the `uploads/` subfolders are writable by your web server user.
- Charts not rendering: confirm you have turn data for the selected plan; the chart uses `get_progress_chart_data.php`.

## 🧪 Tests & QA (optional)

- Static analysis: run the Composer script for PHPStan: `composer analyse`.
- Coding standards: `composer cs:check` and auto-fix with `composer cs:fix`.
- UI tests: Playwright config exists (`tests/ui.spec.js`), but Node dependencies are not wired in this repo yet.

---

## Planning & Versions

This repository tracks design and implementation history in `APPLICATION_PLANNING.md`. The file contains versioned design iterations (VERSION 1 → VERSION 7) and the next milestone plan (VERSION 8 – Official Game Style Alignment). Use it to review decisions, proposed tasks, and next steps as the project evolves.

See also `docs/components.md` for component-level notes and the VERSION 6/7/8 changelogs.

---

## 🏁 VERSION 8 — Official Game Style Alignment (2025-09-03)

**What's new:**
- Visual style: white base, green main, orange/pink accent, game-inspired gradients and drop shadows.
- Motif theming: CSS variables for per-character accent colors (`--motif-primary`, `--motif-accent`, `--motif-bg`).
- Typography: "M PLUS Rounded 1c" font imported globally.
- Components refactored: stat bar, skill card, plan card, modal, sidebar, export UI, trainee image widget, navbar, footer, recent activity, skill row, copy-to-clipboard.
- New V8 classes: `.v8-plan-card`, `.v8-gradient-text`, `.v8-animated-pill`, `.v8-modal-content`, `.v8-tap-feedback`, `.v8-stat-bar`, `.v8-skill-card`.
- Tap feedback: animated ripple effect for buttons, respects reduced motion.
- Accessibility: ARIA, keyboard navigation, WCAG AA contrast, touch targets ≥44px.
- Milestone status: Core palette, font, stat bars, pill buttons, modals, sidebar, tap feedback, and accessibility audit in progress.
