# 🐎 Uma Musume Race Planner

A lightweight PHP + MySQL web application for planning and tracking turn-based training strategies, stat development, skill acquisition, and race goals inspired by Uma Musume. Built for fast manual data entry with autosuggestions, clean interfaces, and no login — ideal for offline strategy planners.

---

## 💡 Features

- 📋 Full turn-based planning UI: stats, skills, terrain, race data
- 🔍 Autosuggest fields based on previous entries (skills, races, names)
- 🏷️ Tag and label skills with colored categories (e.g., Burst, Passive)
- 🔄 Create, edit, update, and soft-delete plans (no data loss)
- 📊 View structured plan history and recent activity
- ⚡ Zero-login simplicity, fully local and lightweight

---

## 🖥️ Tech Stack

- **Frontend**: HTML, CSS (Bootstrap 5), JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+/8+

---

## 🚀 Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/uma_musume_race_planner.git
cd uma_musume_race_planner
````

### 2. Setup the Database

1. Import the schema file into your MySQL server:

```bash
mysql -u your_user -p < uma_musume_planner.sql
```

2. Update `config.php` with your DB credentials:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_user');
define('DB_PASS', 'your_password');
define('DB_NAME', 'uma_musume_planner');
?>
```

### 3. Run the App

You can use PHP’s built-in server for local testing:

```bash
php -S localhost:8000
```

Then go to: [http://localhost:8000](http://localhost:8000)

Alternatively, deploy inside `/htdocs` if you're using XAMPP or WAMP.

---

## 📁 Folder Structure

```
uma_musume_race_planner/
│
├── components/
│   ├── create-panel.php
│   ├── filter-panel.php
│   ├── footer.php         
│   ├── header.php
│   ├── modal-plan.php
│   ├── modal-import.php        
│   ├── navbar.php
│   ├── plan-details.php
│   ├── plan-list.php
│   ├── recent-activity.php
│   └── stats-panel.php
│
├── css/
│   └── style.css
│
├── js/
│   ├── constants.js
│   ├── formEvents.js
│   ├── modal.js
│   ├── renderHelpers.js
│   ├── script.js
│   └── utils.js
│
├── config.php
├── delete_plan.php
├── export_plan.php             
├── import_plan.php             
├── get_autosuggest.php
├── get_plan_details.php
├── get_plans.php
├── get_recent_activity.php
├── index.php
├── save_plan.php
├── uma_musume_planner.sql
└── README.md
```

---

## 🗃️ Database Overview

This project uses a normalized schema:

* `plans`: core plan and race metadata (with soft-delete via `deleted_at`)
* `attributes`: stat values and grades
* `skills`: skills acquired or considered (with `tag`)
* `terrain_grades`, `distance_grades`, `style_grades`: affinity grades
* `race_predictions`: pre-race commentary and stat impact
* `goals`: objectives with actual results

Optimized with indexes for fast autosuggestion and filtering.

---

## 📌 Notes

* ✅ Built for single-user, offline use
* 🔒 No authentication required
* 🐣 Inspired by Uma Musume: Pretty Derby
* 🧪 Ideal for simulation planning and strategy testing

---

## 🧩 To-Do

* [x] Autosuggest skills, races, names
* [x] Add tag/category color labeling to skills
* [x] Plan search and filtering panel
* [x] Export/import plans as JSON
* [x] Soft-delete support
* [ ] Optional login & cloud sync support
* [ ] Race/training performance visualizations

---

## 📜 License

MIT License © 2025