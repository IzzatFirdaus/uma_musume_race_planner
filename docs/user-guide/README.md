# Uma Musume Planner - User Guide

## Introduction

Uma Musume Planner is a comprehensive tool for tracking and managing your Uma Musume: Pretty Derby career runs. Track stats, manage skills, plan races, and analyze your progress across multiple characters.

---

## Getting Started

### Creating Your First Character

1. Navigate to **Characters** from the main menu
2. Click **Add Character**
3. Fill in the character details:
    - Name (English and Japanese)
    - Aptitude grades for Style, Distance, and Track
    - Growth rate bonuses for each stat
4. Optionally upload a character image
5. Click **Save**

### Starting a Career Run

**Quick Create (Recommended for fast entry):**

1. Click the **+ Quick Create** button on the Dashboard
2. Select a character
3. Choose the scenario (default: URA)
4. Click **Create**

**Full Create:**

1. Navigate to **Plans** → **New Plan**
2. Fill in all details including initial stats
3. Click **Create Plan**

---

## Managing Career Runs

### Dashboard Overview

The Dashboard shows:

- **Stats Panel**: Total plans, breakdown by storage mode
- **Recent Activity**: Your latest actions
- **Plan List**: All your career runs with quick actions

### Storage Modes

Plans can be stored in two ways:

| Mode        | Description            | Persistence                  |
| ----------- | ---------------------- | ---------------------------- |
| **Local**   | Stored in your browser | This device only             |
| **Account** | Stored in database     | All devices (requires login) |

Look for the badge on each plan indicating its storage mode.

### Editing Plans

**Inline Edit** (Quick changes):

- Click the expand arrow on any plan in the list
- Edit status, turn, SP, stamina %, or notes
- Changes save automatically

**Full Editor** (Comprehensive editing):

- Click **Edit** on any plan
- Use tabs to navigate: General, Stats, Skills, Racing, Goals
- Press **Ctrl+S** to save or click the Save button

### Converting Local to Account

If you started with local storage and want to sync across devices:

1. Log in to your account
2. Find the local plan (marked with "Local" badge)
3. Click **Convert to Account**
4. Confirm the conversion
5. The plan is now synced to your account

---

## Stat Tracking

### Logging Stats

1. Open a plan's detail view
2. Go to the **Stats** tab
3. Click **Add Turn**
4. Enter stats for: Speed, Stamina, Power, Guts, Wit
5. The turn number auto-increments

### Viewing Progress

**Table View**: See all turns in a scrollable table

**Chart View**: Toggle the chart to visualize progression

- Click stat names in the legend to show/hide lines
- Hover for exact values

### Stat Summary

The summary panel shows:

- **Totals**: Sum of all stats
- **Averages**: Average per turn
- **Growth**: Change from first to last turn

---

## Skill Management

### Adding Skills

1. Go to the **Skills** tab in plan details
2. Use the search box to find skills
    - Search works in English and Japanese
    - Use keyboard: ↑/↓ to navigate, Enter to select
3. Select a skill from the dropdown

### Skill Status

Each skill has one of three statuses:

| Status        | Meaning             | Turn Required |
| ------------- | ------------------- | ------------- |
| **Acquired**  | You have this skill | Yes           |
| **Suggested** | Planning to get     | No            |
| **Skipped**   | Decided not to get  | No            |

### SP Tracking

The skill panel shows:

- **Acquired SP**: Total SP spent on acquired skills
- **Suggested SP**: SP needed for suggested skills
- **Skill Counts**: Number of skills by status

---

## Race Planning

### Race Predictions

1. Go to the **Racing** tab
2. Click **Add Race**
3. Enter race details:
    - Race name
    - Distance category
    - Track type
    - Venue
4. Drag to reorder race priority

### Goals

Set goals for your career run:

1. Click **Add Goal**
2. Enter description (e.g., "Speed >= 1000")
3. Check off goals as you achieve them
4. Turn achieved is recorded automatically

### Race-Day Snapshots

Capture your run's state at important moments:

1. Click **Create Snapshot**
2. Enter a note (e.g., "Before Japan Cup")
3. The snapshot saves all current stats, skills, and conditions
4. Snapshots are immutable - they preserve that exact moment

---

## Export & Import

### Exporting Data

1. Open a plan or select multiple plans
2. Click **Export**
3. Choose format:
    - **JSON**: For backup or transfer
    - **CSV**: For spreadsheet analysis
    - **Markdown**: For sharing/documentation
    - **Excel**: Full spreadsheet with multiple sheets
4. Preview the export
5. Click **Download** or **Copy to Clipboard**

### Importing Data

1. Navigate to **Import** from the menu
2. Upload your file (JSON or CSV)
3. The system auto-detects the format
4. Review the field mapping preview
5. Choose import target:
    - **Local Storage**: No account needed
    - **Account**: Requires login
6. Click **Import**
7. Review the results report

### Local Data Management

Access via **Local Data** in the menu:

- View all local runs with storage info
- Export all local data as JSON backup
- Import local data from JSON
- Delete all local data (with confirmation)
- Bulk convert all to account

---

## Dark Mode

Toggle dark mode using the sun/moon icon in the navigation bar.

- Your preference is saved automatically
- On first visit, system preference is detected
- Works across all pages

---

## Keyboard Shortcuts

| Shortcut   | Action                   |
| ---------- | ------------------------ |
| **Ctrl+S** | Save current form        |
| **Esc**    | Close modal/panel        |
| **↑/↓**    | Navigate autocomplete    |
| **Enter**  | Select autocomplete item |
| **Tab**    | Navigate between fields  |

---

## Accessibility Features

- **Skip Links**: Press Tab on page load to access skip links
- **Keyboard Navigation**: All features accessible via keyboard
- **Screen Reader Support**: ARIA labels and live announcements
- **Reduced Motion**: Respects system preference
- **Focus Indicators**: Visible focus on all interactive elements

---

## Troubleshooting

### Local Data Not Showing

- Check if you're on the same browser/device
- Local data doesn't sync across devices
- Try refreshing the page

### Export Not Working

- Check your browser's download settings
- Try a different export format
- For large exports, use JSON format

### Skills Not Saving

- Ensure turn_acquired is set for Acquired skills
- Check for validation errors (red highlights)
- Try refreshing and re-entering

### Performance Issues

- Large stat tables load lazily - wait for loading
- Charts may take a moment to render
- Consider exporting old runs to reduce data

---

## Getting Help

- Check the [API Documentation](../api/README.md) for technical details
- Review [Component Documentation](../components/README.md) for UI reference
- Report issues via the project repository
