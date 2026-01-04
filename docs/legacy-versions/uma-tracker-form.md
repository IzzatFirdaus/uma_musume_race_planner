# Uma Tracker Form (Legacy)

**Location:** `C:\XAMPP\htdocs\uma-tracker-form`  
**Version:** Native PHP + MVC  
**Status:** Legacy - To be consolidated

## Overview

A lightweight native PHP web application to manage and track Uma Musume characters, their career runs, and associated skills. Built with a PSR-4 architecture (Controllers, Models, Views, Helpers) without frameworks.

## Tech Stack

- **Backend:** PHP 8.0+, Native PDO
- **Frontend:** HTML, CSS (Bootstrap 5 optional), Vanilla JavaScript
- **Database:** MySQL / MariaDB
- **Architecture:** Custom MVC pattern

## Key Features

- Register Uma Musume characters and their stats
- Add multiple learned and available skills dynamically
- Real-time search and filtering via AJAX
- CSRF protection on all POST requests
- Modular PSR-4 architecture
- Responsive UI with vanilla CSS
- Secure database access with PDO prepared statements

## Directory Structure

```
uma-tracker-form/
├── public/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── js/
│   │   └── script.js         # Form interactivity, AJAX
│   ├── db_schema.php         # Dev tool: view DB schema
│   └── index.php             # Public entry point
├── src/
│   ├── config/
│   │   └── db.php            # PDO database connection
│   ├── controllers/
│   │   └── UmamusumeController.php  # Routing, AJAX & HTML responses
│   ├── helpers/
│   │   └── Functions.php     # CSRF, sanitization, AJAX helpers
│   ├── models/
│   │   └── Umamusume.php     # Domain logic & data persistence
│   ├── templates/
│   │   └── form_template.php # AJAX-formatted output snippet
│   └── views/
│       ├── form.php          # Main registration form
│       └── layout.php        # Page template wrapper
├── index.html                # Static landing page
├── README.md
└── .env                      # Environment configuration
```

## Database Schema

### umamusume_progress

| Field                 | Type          | Description           |
| --------------------- | ------------- | --------------------- |
| id                    | INT, PK, AUTO | Unique identifier     |
| observation_number    | INT           | Observation sequence  |
| umamusume_name        | VARCHAR(100)  | Character name        |
| career_stage          | VARCHAR(100)  | Current career stage  |
| umamusume_class       | VARCHAR(50)   | Character class       |
| race_name             | VARCHAR(100)  | Current race          |
| turns_before_race     | INT           | Turns until race      |
| race_objective        | TEXT          | Race goal             |
| speed                 | INT           | Speed stat            |
| stamina               | INT           | Stamina stat          |
| power                 | INT           | Power stat            |
| guts                  | INT           | Guts stat             |
| wit                   | INT           | Wit stat              |
| current_skill_points  | INT           | Available SP          |
| conditions            | VARCHAR(255)  | Current conditions    |
| track_turf_apt        | CHAR(1)       | Turf aptitude grade   |
| track_dirt_apt        | CHAR(1)       | Dirt aptitude grade   |
| dist_sprint_apt       | CHAR(1)       | Sprint aptitude       |
| dist_mile_apt         | CHAR(1)       | Mile aptitude         |
| dist_medium_apt       | CHAR(1)       | Medium aptitude       |
| dist_long_apt         | CHAR(1)       | Long aptitude         |
| style_front_apt       | CHAR(1)       | Front-runner aptitude |
| style_pace_apt        | CHAR(1)       | Pace-maker aptitude   |
| style_late_apt        | CHAR(1)       | Late-runner aptitude  |
| style_end_apt         | CHAR(1)       | End-runner aptitude   |
| growth_speed          | TINYINT       | Speed growth bonus    |
| growth_guts           | TINYINT       | Guts growth bonus     |
| energy_percentage     | TINYINT       | Current energy %      |
| mood                  | VARCHAR(50)   | Current mood          |
| observation_timestamp | TIMESTAMP     | Record timestamp      |

### learned_skills

| Field       | Type          | Description           |
| ----------- | ------------- | --------------------- |
| id          | INT, PK, AUTO | Unique identifier     |
| progress_id | INT, FK       | Reference to progress |
| skill_name  | VARCHAR(100)  | Skill name            |
| acquired    | TINYINT       | Acquisition status    |
| notes       | TEXT          | Additional notes      |

### available_skills

| Field       | Type          | Description           |
| ----------- | ------------- | --------------------- |
| id          | INT, PK, AUTO | Unique identifier     |
| progress_id | INT, FK       | Reference to progress |
| skill_name  | VARCHAR(100)  | Skill name            |
| skill_cost  | INT           | SP cost               |

## MVC Architecture

### Controller (UmamusumeController.php)

- Handles routing for AJAX and HTML responses
- Processes form submissions
- Returns JSON for AJAX requests
- Renders views for page requests

### Model (Umamusume.php)

- Domain logic and data persistence
- PDO prepared statements for all queries
- CRUD operations for progress entries
- Skill management methods

### Views

- `layout.php` - Base template with header/footer
- `form.php` - Main registration form
- `form_template.php` - AJAX response template

### Helpers (Functions.php)

- CSRF token generation and validation
- Input sanitization
- AJAX response helpers
- Common utility functions

## Security Features

- CSRF tokens on all POST requests
- PDO with prepared statements for SQL injection prevention
- Output escaping (`htmlspecialchars`) for XSS prevention
- Dev-only schema viewer guarded by `APP_ENV`

## Features to Migrate

### High Priority

- [ ] CSRF protection pattern
- [ ] PDO database abstraction
- [ ] Form validation logic
- [ ] AJAX response patterns

### Medium Priority

- [ ] MVC structure (adapt to Laravel)
- [ ] Helper functions
- [ ] Search/filter functionality

### Low Priority

- [ ] Dev schema viewer (use Laravel migrations)

## Usage Flow

1. Fill out the Uma Musume progress form
2. Add learned and available skills dynamically
3. Click Save Progress to store via AJAX
4. View formatted Markdown in the "Copy/Paste" panel
5. Search existing entries with the search box

## Configuration

Environment variables (`.env`):

```ini
APP_ENV=development
DB_HOST=localhost
DB_NAME=uma_tracker
DB_USER=root
DB_PASS=yourpassword
```

## Roadmap (from original)

- [ ] Edit / Delete progress entries
- [ ] Skill tagging, filtering, and sorting
- [ ] Chart.js integration for stat graphs
- [ ] Pagination for large record sets
- [ ] Dark mode / theming toggle

## Notes

- Simplest implementation of the tracker concept
- Good reference for vanilla PHP patterns
- Clean separation of concerns
- Security-conscious implementation
- No framework dependencies
