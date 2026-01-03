# Uma Run Tracker (Legacy)

**Location:** `C:\XAMPP\htdocs\uma-run-tracker`  
**Version:** Static HTML + JavaScript  
**Status:** Legacy - To be consolidated

## Overview

A static HTML/CSS/JavaScript application for career run entry and export. Designed for offline-first usage with local storage persistence and markdown/text export capabilities. No backend required.

## Tech Stack

- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Fonts:** M PLUS Rounded 1c, Figtree (Google Fonts)
- **Storage:** Browser LocalStorage
- **Export:** Plain text, Markdown

## Key Features

### Data Entry

- Comprehensive career run form
- General info (run number, horse name, title, career stage, class, plan)
- Key race and result/goal tracking
- Attribute tracking with visual stat bars (Speed, Stamina, Power, Guts, Wit)
- Grade inputs for each attribute

### Accessibility

- Skip link for keyboard/screen reader users
- Live region for status announcements
- ARIA labels and roles throughout
- Keyboard-navigable form controls

### UX Features

- Dark mode toggle with persistence
- Real-time stat bar visualization
- Form validation
- Save/Load to LocalStorage
- Export to .txt/.md files
- Import from files

## Directory Structure

```
uma-run-tracker/
├── assets/
│   ├── avatars/              # Character avatar images
│   ├── icons/                # UI icons
│   └── demo-data.json        # Sample data
├── components/
│   ├── aptitude-badge.html
│   ├── chart.html
│   ├── dashboard-summary.html
│   ├── export-modal.html
│   ├── glossary-accordion.html
│   ├── goals-badge.html
│   ├── header.html
│   ├── onboarding-modal.html
│   ├── plan-form.html
│   ├── sidebar.html
│   ├── skill-card.html
│   └── stat-bar.html
├── css/
│   ├── components.css
│   ├── dashboard.css
│   ├── plan-editor.css
│   ├── style.css
│   ├── style-altdesign.css
│   └── style-newdesign.css
├── js/
│   ├── chart.js
│   ├── dashboard.js
│   ├── export.js
│   ├── glossary.js
│   ├── onboarding.js
│   ├── plan-editor.js
│   ├── script.js
│   ├── script-altdesign.js
│   └── script-newdesign.js
├── lang/
│   └── en.json               # Internationalization
├── markdown-docs/
│   ├── DEPRECATED_SYSTEM_EXCAVATION.md
│   ├── PLAN-INDEX-IMPROVEMENTS.md
│   └── PLAN-UMA-ALTDESIGN-IMPROVEMENTS.md
├── index.html                # Main entry point
├── index-altdesign.html      # Alternative design
├── index-newdesign.html      # New design iteration
├── career_run_preview.html   # Preview page
└── uma_career_run_*.json     # Sample career data
```

## Form Sections

### General Info

- Career Run # (required)
- Horse Name (required)
- Title
- Career Stage
- Class
- Plan
- Key Race
- Result/Goal

### Attributes

| Attribute | Value Range | Grade |
|-----------|-------------|-------|
| SPEED | 0-1200 | A-G |
| STAMINA | 0-1200 | A-G |
| POWER | 0-1200 | A-G |
| GUTS | 0-1200 | A-G |
| WIT | 0-1200 | A-G |

### Visual Components

- Stat bars with real-time updates
- Grade hint tooltips
- Responsive table layouts

## JavaScript Modules

| Module | Purpose |
|--------|---------|
| `script.js` | Main form logic, save/load, export |
| `chart.js` | Stat visualization charts |
| `dashboard.js` | Dashboard summary logic |
| `export.js` | File export functionality |
| `glossary.js` | Glossary accordion behavior |
| `onboarding.js` | First-time user onboarding |
| `plan-editor.js` | Plan editing functionality |

## HTML Components

Reusable HTML partials for consistent UI:

| Component | Description |
|-----------|-------------|
| `aptitude-badge.html` | Aptitude grade display |
| `chart.html` | Chart container |
| `dashboard-summary.html` | Dashboard overview |
| `export-modal.html` | Export dialog |
| `glossary-accordion.html` | Expandable glossary |
| `goals-badge.html` | Goal status indicator |
| `header.html` | Page header |
| `onboarding-modal.html` | Welcome/tutorial modal |
| `plan-form.html` | Main plan entry form |
| `sidebar.html` | Navigation sidebar |
| `skill-card.html` | Skill display card |
| `stat-bar.html` | Stat progress bar |

## Features to Migrate

### High Priority

- [ ] Form structure and validation
- [ ] Stat bar visualization component
- [ ] Dark mode implementation
- [ ] Export to text/markdown

### Medium Priority

- [ ] Accessibility patterns (ARIA, skip links)
- [ ] Onboarding modal flow
- [ ] Glossary accordion
- [ ] Dashboard summary layout

### Low Priority

- [ ] Alternative design variants
- [ ] LocalStorage persistence (replace with DB)

## Design Variants

The project includes multiple design iterations:

1. **Original** (`index.html`, `style.css`) - Base design
2. **Alt Design** (`index-altdesign.html`, `style-altdesign.css`) - Alternative layout
3. **New Design** (`index-newdesign.html`, `style-newdesign.css`) - Latest iteration

## Sample Data

JSON files contain sample career run data:

- `uma_career_run_9.txt` - Text format
- `uma_career_run_12.json` - JSON format
- `uma_career_run_12.md` - Markdown format
- `uma_career_run_12_newdesign.json` - New design format

## Internationalization

Language strings stored in `lang/en.json` for future multi-language support.

## Notes

- Excellent accessibility implementation
- Good reference for offline-first patterns
- Component-based HTML structure
- Multiple design iterations available
- No backend dependencies - pure frontend
