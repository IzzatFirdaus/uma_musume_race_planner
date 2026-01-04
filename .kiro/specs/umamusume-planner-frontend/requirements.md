# Requirements Document

## Introduction

This document specifies the frontend user flows and use cases for the Uma Musume Career Planner application. The system consolidates features from six application versions (uma-musume-planner-laravel, uma_musume_race_planner, umamusume-tracker, uma-run-tracker, uma-tracker-form, uma-tracker) into a unified platform enabling players of Uma Musume: Pretty Derby to track, manage, and analyze career progression. This specification covers all primary user interactions, navigation patterns, and frontend behaviors.

## Glossary

- **Plan**: A career run record tracking an Uma Musume character's training progression, stats, skills, and race predictions
- **Dashboard**: The main landing page displaying plan lists, statistics, and quick actions
- **Umamusume**: A horse girl character from the Uma Musume: Pretty Derby game that can be trained and raced
- **Skill**: An ability that can be acquired during training with associated SP (Skill Points) cost; categorized by type (Speed, Stamina, Power, Guts, Wit, Debuff) and tier (G-, G, G+, F-, F, F+, E-, E, E+, D-, D, D+, C-, C, C+, B-, B, B+, A-, A, A+, S-, S, S+, SS); SS tier is reserved for skills at maximum stat values (1200)
- **Attribute**: One of five core training stats (Speed, Stamina, Power, Guts, Wit) tracked per plan
- **Speed**: Blue stat (#3399ff) - Determines maximum running speed during Late-Race and Last Spurt phases
- **Stamina**: Green stat (#33cc99) - Determines HP/effective stamina for maintaining top speed
- **Power**: Red stat (#ff4d4d) - Affects acceleration rate and lane-changing ability
- **Guts**: Orange stat (#ffa500) - Affects last spurt bonuses and reduces stamina consumption
- **Wit**: Purple stat (#9933ff) - Affects skill activation rate and reduces rushing chance
- **Aptitude_Grade**: A rating (SS, S, A, B, C, D, E, F, G) for terrain, distance, or running style suitability; SS=120%, S=110%, A=100%, down to G=40% effectiveness
- **Terrain_Aptitude**: Track surface suitability - Turf (芝/grass) or Dirt (ダート/sand)
- **Distance_Aptitude**: Race length suitability - Sprint (1000-1400m), Mile (1401-1800m), Medium (1801-2400m), Long (2401-3600m)
- **Style_Aptitude**: Running strategy suitability - Front Runner (逃げ/Nige), Pace Chaser (先行/Senkou), Late Surger (差し/Sashi), End Closer (追込/Oikomi)
- **Turn**: A single progression point in a career run (approximately 70-72 turns per career across Junior, Classic, and Senior years)
- **Career_Stage**: The current year of training - Junior Year, Classic Year, or Senior Year
- **Mood**: Character emotional state affecting training/racing performance - Great (+4%), Good (+2%), Normal (0%), Bad (-2%), Awful (-4%)
- **Condition**: Status effects like Night Owl, Practice Poor, Overweight that affect training
- **SP**: Skill Points earned from races and events, spent to purchase unlocked skills
- **Stat_Max**: The maximum stat value (1200) - hard cap, no values above this are allowed
- **Quick_Create_Modal**: A modal dialog for rapidly creating new plans
- **Plan_Details_Page**: A dedicated page for viewing/editing a single plan
- **Inline_Details**: An expandable row within the plan list showing plan details
- **Form_Tabs**: A tabbed interface for organizing plan data into sections
- **Dark_Mode**: An alternative color scheme for reduced eye strain
- **Stat_Chart**: A visual line chart showing stat progression over turns
- **Circular_Progress**: A game-inspired circular indicator showing stat values
- **Activity_Log**: A timestamped record of user actions (create, update, delete)
- **Career_Preview**: A formatted preview of plan data before export
- **Excel_Export**: Multi-sheet spreadsheet export with character info, stats, and skills
- **Text_Export**: Plain text or Markdown formatted export for sharing
- **Support_Card**: Training partner cards that provide stat bonuses and skill hints
- **Friendship_Training**: Enhanced training available when support card friendship reaches ~80%
- **URA_Finale**: The final race series at the end of Senior Year
- **Storage_Mode**: Indicates whether a plan is stored locally (localStorage) or in the database (Account)
- **Local_Run**: A plan stored in browser localStorage, fully usable offline, not synced to server
- **Account_Run**: A plan stored in the database, requires authentication and Livewire connectivity
- **Saved_View**: A named filter configuration that can be quickly applied to the plan list
- **Plan_Template**: A reusable plan configuration that can be used as a starting point for new plans
- **Skill_Preset**: A predefined collection of skills that can be added to a plan as a group
- **Skill_Status**: The acquisition state of a skill - Acquired (purchased), Skipped (decided not to buy), or Suggested (recommended but not yet decided)
- **Readiness_Score**: A calculated percentage indicating how prepared a character is for a specific race
- **SP_Balance**: The current amount of Skill Points available for purchasing skills
- **Share_Link**: A public URL that allows read-only access to a plan
- **Race_Snapshot**: A point-in-time capture of character stats and skills before a specific race
- **Inline_Editor**: A quick-edit panel that expands within the plan list for common field updates
- **Fullscreen_Editor**: The complete tabbed editing interface for comprehensive plan management

## Frontend Storage Strategy

This application supports two storage modes to accommodate both anonymous and authenticated users:

- **Local Runs (localStorage)**: Plans stored in browser localStorage for anonymous users or offline-first usage. Fully functional without network connectivity. Limited by browser storage quotas (~5-10MB). Future migration to IndexedDB planned for larger datasets.
- **Account Runs (Database)**: Plans stored in the database (MySQL/MariaDB/SQLite supported) via Livewire for authenticated users. Requires network connectivity. Supports cross-device access and backup.
- **Draft Autosave**: Form drafts saved to localStorage regardless of storage mode.
- **Reference Data Cache**: Character roster and skill database cached via Laravel cache with 24-hour TTL.

## Routing Conventions

- **User-facing URLs**: Use `/plans/*` for user-friendly naming (e.g., `/plans/123/edit`)
- **Domain Model**: Code and database use `CareerRun` as the entity name
- **API Endpoints**: Use `/api/v1/plans/*` for user-friendliness (maps to CareerRun internally)

## Architectural Decisions

The following architectural decisions have been made for this application:

1. **Technology Stack**: Laravel + Livewire + Alpine.js + TailwindCSS. Primary server communication uses Livewire's wire protocol. A REST API exists for programmatic access (see backend spec FR-BE-2); Livewire components use server actions for UI interactions.

2. **Authentication Model**: Optional sign-in. Users can use the app fully anonymously with Local_Runs (localStorage). Authentication enables Account_Runs (database storage) for cross-device access. No complex sync queue - Account_Runs require connectivity to save.

3. **Game Mode Support**: Primary focus is Career mode (training mode). Champions Meeting stat thresholds are supported for race readiness calculations. Team Stadium may be added as a future game mode.

4. **Database Agnostic**: Supports MySQL, MariaDB, and SQLite to accommodate different deployment scenarios.

5. **Offline-First for Local_Runs**: Local_Runs are fully functional offline. Account_Runs require connectivity but drafts are preserved locally during disconnection.

## Requirement Priorities

Requirements are prioritized as follows:

- **P0 (Critical)**: Core functionality required for MVP launch
- **P1 (High)**: Important features for complete user experience
- **P2 (Medium)**: Enhanced features for power users
- **P3 (Low)**: Future enhancements and nice-to-haves

## Requirements

### Requirement 1: Dashboard Navigation and Overview [P0]

**User Story:** As a player, I want to see an overview of all my career plans when I open the app, so that I can quickly access and manage my training records.

#### Acceptance Criteria

1. WHEN a user navigates to the root URL or /dashboard, THE Dashboard SHALL display a header banner with app branding and welcome message
2. WHEN the Dashboard loads, THE Stats_Panel SHALL display aggregate statistics including total plans, active plans, and completed plans
3. WHEN the Dashboard loads, THE Plan_List SHALL display all existing plans in a scrollable list with plan title, character name, and status
4. WHEN a user views the Dashboard, THE Recent_Activity component SHALL display the most recent plan modifications
5. THE Dashboard SHALL provide a visible "Create Plan" button for initiating new plan creation
6. WHEN the Dashboard loads on mobile devices, THE Layout SHALL adapt to a single-column responsive design

### Requirement 2: Plan Creation Flow [P0]

**User Story:** As a player, I want to create new career plans quickly, so that I can start tracking a new training run without friction.

#### Acceptance Criteria

1. WHEN a user clicks the "Create Plan" button, THE Quick_Create_Modal SHALL open with a form for basic plan information
2. WHEN the Quick_Create_Modal opens, THE Form SHALL pre-populate with default values for optional fields
3. WHEN a user submits a valid plan name, THE System SHALL create a new plan and redirect to the plan details page
4. WHEN a user attempts to submit an empty plan name, THE System SHALL display a validation error and prevent submission
5. WHEN a user clicks outside the modal or presses Escape, THE Quick_Create_Modal SHALL close without saving
6. WHEN a plan is successfully created, THE System SHALL display a success notification

### Requirement 3: Plan List Interaction [P0]

**User Story:** As a player, I want to browse, filter, and interact with my plans from the list view, so that I can efficiently manage multiple training records.

#### Acceptance Criteria

1. WHEN a user clicks on a plan row, THE Inline_Details component SHALL expand to show plan summary information
2. WHEN a user clicks the "View" action button, THE System SHALL navigate to the Plan_Details_Page in view mode
3. WHEN a user clicks the "Edit" action button, THE System SHALL navigate to the Plan_Details_Page in edit mode
4. WHEN a user clicks the "Delete" action button, THE System SHALL prompt for confirmation before deleting
5. IF a user confirms deletion, THEN THE System SHALL soft-delete the plan and remove it from the list
6. WHEN the plan list is empty, THE System SHALL display an empty state message with a call-to-action to create a plan

### Requirement 4: Plan Details Viewing [P0]

**User Story:** As a player, I want to view complete details of a career plan, so that I can review my training progress and decisions.

#### Acceptance Criteria

1. WHEN a user navigates to /plans/{id} or /plans/{id}/view, THE Plan_Details_Page SHALL display in read-only view mode
2. WHEN viewing a plan, THE Form_Tabs SHALL organize content into sections: General, Attributes, Aptitude Grades, Skills, Race Predictions, Goals, Turns
3. WHEN viewing the General tab, THE System SHALL display plan title, character name, career stage (Junior/Classic/Senior), class, mood (Great/Good/Normal/Bad/Awful), condition, and energy level
4. WHEN viewing the Attributes tab, THE System SHALL display all five stat values (Speed, Stamina, Power, Guts, Wit) with visual stat bars (max 1200)
5. WHEN viewing the Skills tab, THE System SHALL display all associated skills with name, SP cost, skill tier (G-, G, G+, F-, F, F+, E-, E, E+, D-, D, D+, C-, C, C+, B-, B, B+, A-, A, A+, S-, S, S+, SS), acquired status, and notes
6. THE Plan_Details_Page SHALL provide navigation to return to the Dashboard

### Requirement 5: Plan Details Editing [P0]

**User Story:** As a player, I want to edit my career plans with a comprehensive form, so that I can update training progress as my run evolves.

#### Acceptance Criteria

1. WHEN a user navigates to /plans/{id}/edit, THE Plan_Details_Page SHALL display in edit mode with editable form fields
2. WHEN editing, THE Form_Tabs SHALL allow switching between sections without losing unsaved changes within the current session
3. WHEN a user modifies any field, THE System SHALL track the dirty state and warn before navigating away with unsaved changes
4. WHEN a user clicks "Save", THE System SHALL validate all fields and persist changes to the database
5. IF validation fails, THEN THE System SHALL display inline error messages for invalid fields
6. WHEN changes are saved successfully, THE System SHALL display a success notification and remain on the edit page

### Requirement 6: Skill Management [P0]

**User Story:** As a player, I want to add, edit, and remove skills from my plan, so that I can track skill acquisition throughout my training run.

#### Acceptance Criteria

1. WHEN viewing the Skills tab in edit mode, THE Skills_Editor SHALL display existing skills in a table format with columns: Skill Name, SP Cost, Tier, Type, Status, Turn Acquired, Notes
2. WHEN a user clicks "Add Skill", THE System SHALL append a new empty skill row to the table
3. WHEN a user types in the skill name field, THE System SHALL provide autocomplete suggestions from the skill reference database, populating name, SP cost, tier, type, and description on selection
4. WHEN a user changes the skill status, THE System SHALL immediately update to one of three states: Acquired (purchased), Skipped (decided not to buy), or Suggested (recommended but not decided)
5. WHEN a skill status is set to "Acquired", THE System SHALL require a valid turn_acquired value (1-78 range)
6. WHEN a user clicks the remove button on a skill row, THE System SHALL remove that skill from the plan
7. THE Skills_Editor SHALL calculate and display the total SP cost of Acquired skills, and separately show planned SP for Suggested skills

### Requirement 7: Attribute and Stat Tracking [P0]

**User Story:** As a player, I want to record and visualize my character's stat progression, so that I can analyze training effectiveness.

#### Acceptance Criteria

1. WHEN viewing the Attributes tab, THE System SHALL display each stat (Speed, Stamina, Power, Guts, Wit) with its current value
2. WHEN editing attributes, THE System SHALL provide numeric input fields with validation for stat ranges (minimum 0, maximum 1200 hard cap)
3. THE System SHALL display stat values with color-coded visual indicators matching the game's color scheme (Speed=#3399ff, Stamina=#33cc99, Power=#ff4d4d, Guts=#ffa500, Wit=#9933ff)
4. WHEN viewing attributes, THE System SHALL display growth rate bonuses for each stat as percentages
5. THE System SHALL reject stat values above 1200 with a validation error
6. THE System SHALL display the total stat sum

### Requirement 8: Aptitude Grade Management [P0]

**User Story:** As a player, I want to record my character's aptitude grades, so that I can track terrain, distance, and style suitabilities.

#### Acceptance Criteria

1. WHEN viewing the Aptitude Grades tab, THE System SHALL display terrain grades for Turf (芝) and Dirt (ダート)
2. WHEN viewing the Aptitude Grades tab, THE System SHALL display distance grades for Sprint (1000-1400m), Mile (1401-1800m), Medium (1801-2400m), and Long (2401-3600m)
3. WHEN viewing the Aptitude Grades tab, THE System SHALL display style grades for Front Runner (逃げ/Nige), Pace Chaser (先行/Senkou), Late Surger (差し/Sashi), and End Closer (追込/Oikomi)
4. WHEN editing aptitude grades, THE System SHALL provide dropdown selectors with grade options (SS, S, A, B, C, D, E, F, G) and their effectiveness percentages (SS=120%, S=110%, A=100%, B=90%, C=80%, D=70%, E=60%, F=50%, G=40%)
5. THE System SHALL visually distinguish grades using game-accurate colors (SS=Platinum/#e5e7eb, S=Gold/#ffd700, A=Red/#ef4444, B=Orange/#f97316, C=Green/#22c55e, D=Blue/#3b82f6, E=Purple/#a855f7, F=Gray/#6b7280, G=Dark Gray/#9ca3af)

### Requirement 9: Race Predictions [P1]

**User Story:** As a player, I want to record race predictions and outcomes, so that I can plan my race schedule and track results.

#### Acceptance Criteria

1. WHEN viewing the Race Predictions tab, THE System SHALL display a list of race prediction entries
2. WHEN a user adds a race prediction, THE System SHALL capture race name, venue, distance category (Sprint/Mile/Medium/Long), track type (Turf/Dirt), predicted placement, and notes
3. WHEN editing race predictions, THE System SHALL allow adding multiple prediction entries with recommended stat thresholds based on distance
4. WHEN a user removes a race prediction, THE System SHALL delete that entry from the plan
5. THE Race_Predictions_Editor SHALL support reordering predictions via drag-and-drop or manual ordering
6. THE System SHALL display recommended minimum stamina values based on distance (Sprint: 350, Mile: 400, Medium: 500, Long: 600 for Career mode)

### Requirement 10: Goals Management [P1]

**User Story:** As a player, I want to set and track training goals, so that I can measure progress against my objectives.

#### Acceptance Criteria

1. WHEN viewing the Goals tab, THE System SHALL display all goals associated with the plan
2. WHEN a user adds a goal, THE System SHALL capture goal description and completion status
3. WHEN a user toggles goal completion, THE System SHALL update the goal's completed state
4. WHEN a user removes a goal, THE System SHALL delete that goal from the plan
5. THE Goals_Editor SHALL visually distinguish completed goals from incomplete goals

### Requirement 11: Character Roster Browsing [P1]

**User Story:** As a player, I want to browse the Uma Musume character roster, so that I can reference character information when creating plans.

#### Acceptance Criteria

1. WHEN a user navigates to /characters, THE Character_List SHALL display all available Uma Musume characters
2. WHEN viewing the character list, THE System SHALL display character name, team affiliation (Spica, Canopus, Rigil, Sirius, etc.), and rarity (1-star, 2-star, 3-star)
3. WHEN viewing a character, THE System SHALL display base stats, growth rate bonuses (e.g., Speed +20%, Stamina +10%), and default aptitudes
4. THE Character_List SHALL support filtering by character name, team, rarity, and specialty distance
5. THE Character_List SHALL display character images when available
6. WHEN selecting a character for a new plan, THE System SHALL auto-populate growth rates and default aptitudes

### Requirement 12: Dark Mode Toggle [P0]

**User Story:** As a player, I want to switch between light and dark color schemes, so that I can use the app comfortably in different lighting conditions.

#### Acceptance Criteria

1. THE Navbar SHALL include a visible dark mode toggle control
2. WHEN a user clicks the dark mode toggle, THE System SHALL immediately switch the color scheme
3. WHEN dark mode is enabled, THE System SHALL persist the preference to localStorage
4. WHEN the app loads, THE System SHALL restore the user's previously selected color scheme preference
5. WHEN dark mode is active, THE System SHALL apply appropriate contrast ratios meeting WCAG AA standards

### Requirement 13: Application Guide [P1]

**User Story:** As a new player, I want to access a guide explaining how to use the app, so that I can learn the features and workflows.

#### Acceptance Criteria

1. WHEN a user navigates to /guide, THE Guide_Page SHALL display comprehensive usage instructions
2. THE Guide_Page SHALL include sections explaining each major feature (plans, skills, attributes, etc.)
3. THE Guide_Page SHALL include a sticky navigation for jumping between sections
4. THE Guide_Page SHALL be accessible from the main navigation menu

### Requirement 14: Responsive Layout [P0]

**User Story:** As a player, I want to use the app on any device, so that I can track my training on desktop, tablet, or mobile.

#### Acceptance Criteria

1. WHEN the viewport width is less than 768px, THE Layout SHALL switch to mobile-optimized single-column design
2. WHEN on mobile, THE Navigation SHALL collapse into a hamburger menu
3. WHEN on mobile, THE Form_Tabs SHALL remain accessible with horizontal scrolling if needed
4. WHEN on mobile, THE touch targets SHALL be at least 44px in height for accessibility
5. THE Layout SHALL maintain usability across viewport widths from 320px to 2560px

### Requirement 15: Data Export [P0]

**User Story:** As a player, I want to export my plan data, so that I can backup or share my training records.

#### Acceptance Criteria

1. WHEN viewing a plan, THE System SHALL provide an export action button
2. WHEN a user clicks export, THE System SHALL generate a downloadable file containing plan data
3. THE export format SHALL include all plan fields, skills, attributes, and goals
4. WHEN export completes, THE System SHALL trigger a browser download of the generated file

### Requirement 16: Form Validation and Error Handling [P0]

**User Story:** As a player, I want clear feedback when I make mistakes in forms, so that I can correct errors and successfully save my data.

#### Acceptance Criteria

1. WHEN a required field is empty on submission, THE System SHALL display an inline error message below the field
2. WHEN a numeric field contains invalid input, THE System SHALL display a validation error
3. WHEN a server error occurs during save, THE System SHALL display a user-friendly error notification
4. IF a session expires during editing, THEN THE System SHALL notify the user and provide recovery options
5. THE System SHALL prevent form submission while validation errors exist

### Requirement 17: Trainee Image Upload [P1]

**User Story:** As a player, I want to upload a custom image for my plan, so that I can personalize my training records with character artwork.

#### Acceptance Criteria

1. WHEN editing a plan, THE System SHALL provide an image upload control
2. WHEN a user selects an image file, THE System SHALL preview the image before saving
3. WHEN an image is uploaded, THE System SHALL store it and associate it with the plan
4. THE System SHALL validate image file type (JPEG, PNG, WebP) and size limits (max 2MB per Consolidation FR-11.2)
5. IF image upload fails, THEN THE System SHALL display an error message with the reason

### Requirement 18: Turn-by-Turn Stat Tracking [P0]

**User Story:** As a player, I want to log my character's stats at each turn, so that I can track progression throughout my career run (approximately 70-72 turns across Junior, Classic, and Senior years).

#### Acceptance Criteria

1. WHEN viewing a plan in edit mode, THE Turns_Tab SHALL display a list of recorded turn entries organized by career year (Junior/Classic/Senior)
2. WHEN a user clicks "Add Turn", THE System SHALL create a new turn entry with the next sequential turn number (max ~78 with extensions)
3. WHEN editing a turn entry, THE System SHALL allow input for all five stats (Speed, Stamina, Power, Guts, Wit) with validation (0-1200 range, hard max)
4. WHEN a turn is saved, THE System SHALL validate stat values and reject any stat exceeding 1200
5. WHEN a user deletes a turn entry, THE System SHALL remove it and offer to renumber subsequent turns
6. THE Turns_Tab SHALL display turn entries in chronological order with visual indicators for training milestones (Summer Camp turns, URA Finale)

### Requirement 19: Stat Progression Charts [P1]

**User Story:** As a player, I want to visualize my stat progression over time, so that I can analyze training effectiveness and identify trends.

#### Acceptance Criteria

1. WHEN viewing a plan with turn data, THE Stat_Chart component SHALL display a line chart of stat progression
2. THE Stat_Chart SHALL plot each stat (Speed, Stamina, Power, Guts, Wit) as a separate colored line
3. THE Stat_Chart SHALL use the x-axis for turn numbers and y-axis for stat values
4. WHEN a user hovers over a data point, THE System SHALL display a tooltip with exact values
5. THE Stat_Chart SHALL provide toggle controls to show/hide individual stat lines
6. WHEN no turn data exists, THE System SHALL display an empty state message

### Requirement 20: Circular Progress Indicators [P1]

**User Story:** As a player, I want to see my stats displayed as circular progress indicators, so that I can quickly assess stat levels with a game-inspired visual style.

#### Acceptance Criteria

1. WHEN viewing the Attributes tab, THE System SHALL display each stat with a Circular_Progress indicator
2. THE Circular_Progress SHALL animate from 0 to the current value on initial render
3. THE Circular_Progress SHALL use stat-specific colors matching the game (Speed=#3399ff, Stamina=#33cc99, Power=#ff4d4d, Guts=#ffa500, Wit=#9933ff)
4. THE Circular_Progress SHALL display the numeric value in the center of the circle with the stat name below
5. THE Circular_Progress SHALL calculate fill percentage based on max value of 1200
6. THE Circular_Progress SHALL show 100% fill when stat reaches 1200 (the hard max)

### Requirement 21: Activity Log Display [P1]

**User Story:** As a player, I want to see a log of recent actions, so that I can track what changes have been made to my plans.

#### Acceptance Criteria

1. WHEN viewing the Dashboard, THE Activity_Log component SHALL display recent user actions
2. THE Activity_Log SHALL show action type (created, updated, deleted), target entity, and timestamp
3. THE Activity_Log SHALL display entries in reverse chronological order (newest first)
4. THE Activity_Log SHALL limit display to the most recent 10 entries with option to view more
5. WHEN a user clicks an activity entry, THE System SHALL navigate to the related plan if it exists

### Requirement 22: Career Preview Before Export [P1]

**User Story:** As a player, I want to preview my plan data in export format before downloading, so that I can verify the content is correct.

#### Acceptance Criteria

1. WHEN a user clicks the export button, THE Career_Preview modal SHALL open displaying formatted plan data
2. THE Career_Preview SHALL show all plan fields, stats, skills, and goals in the export format
3. THE Career_Preview SHALL provide format selection (Text, Markdown, Excel)
4. WHEN a user confirms export, THE System SHALL generate and download the file in the selected format
5. WHEN a user cancels, THE Career_Preview modal SHALL close without exporting

### Requirement 23: Excel Export with Multiple Sheets [P1]

**User Story:** As a player, I want to export my plan data to Excel with organized worksheets, so that I can analyze and share my data in a professional spreadsheet format.

#### Acceptance Criteria

1. WHEN a user selects Excel export, THE System SHALL generate an .xlsx file
2. THE Excel_Export SHALL include a "Plan Info" sheet with general plan details
3. THE Excel_Export SHALL include a "Stats" sheet with turn-by-turn stat history
4. THE Excel_Export SHALL include a "Skills" sheet with all skills, SP costs, and acquisition status
5. THE Excel_Export SHALL include a "Goals" sheet with all goals and completion status
6. THE Excel_Export SHALL apply formatting (headers, borders, stat colors) to worksheets

### Requirement 24: Text and Markdown Export [P1]

**User Story:** As a player, I want to export my plan as formatted text or Markdown, so that I can easily share my training records in forums or chat.

#### Acceptance Criteria

1. WHEN a user selects Text export, THE System SHALL generate a .txt file with formatted plan data
2. WHEN a user selects Markdown export, THE System SHALL generate a .md file with Markdown formatting
3. THE Text_Export SHALL include sections for character info, final stats, skills, and notes
4. THE Text_Export SHALL provide a "Copy to Clipboard" option for quick sharing
5. THE export format SHALL be human-readable with clear section headers and alignment

### Requirement 25: Skill Reference Database Search [P0]

**User Story:** As a player, I want to search the skill database when adding skills, so that I can quickly find and add skills with accurate SP costs and tier information.

#### Acceptance Criteria

1. WHEN a user types in the skill name field, THE System SHALL query the skill reference database with debounced input (300ms)
2. THE autocomplete dropdown SHALL display matching skills with name, SP cost, skill type (Speed/Stamina/Power/Guts/Wit/Debuff), tier (G-, G, G+, F-, F, F+, E-, E, E+, D-, D, D+, C-, C, C+, B-, B, B+, A-, A, A+, S-, S, S+, SS), and brief description
3. WHEN a user selects a skill from autocomplete, THE System SHALL populate the skill name, SP cost, tier, type, and description fields automatically
4. THE search SHALL support partial matching, be case-insensitive, and search both English and Japanese skill names
5. WHEN no matches are found, THE System SHALL allow manual entry of custom skill names with user-provided SP cost and tier
6. THE System SHALL display skill descriptions, activation conditions, and best-use recommendations from the skill database
7. THE System SHALL indicate skill compatibility with running styles (e.g., "Best for Front Runner")

### Requirement 26: Training Strategy Selection [P1]

**User Story:** As a player, I want to select a training strategy for my plan, so that I can categorize my approach and filter plans by strategy.

#### Acceptance Criteria

1. WHEN creating or editing a plan, THE System SHALL provide a strategy dropdown selector
2. THE strategy options SHALL include running style-based approaches (Front Runner/Nige build, Pace Chaser/Senkou build, Late Surger/Sashi build, End Closer/Oikomi build) and stat-focused approaches (Speed-focused, Stamina-focused, Power-focused, Balanced)
3. WHEN a strategy is selected, THE System SHALL save it with the plan and display recommended stat priorities
4. THE Plan_List SHALL support filtering by selected strategy
5. THE strategy field SHALL be optional with a default of "None"
6. WHEN a running style strategy is selected, THE System SHALL display recommended stat distributions (e.g., Front Runner: Speed > Stamina > Wit)

### Requirement 27: Plan Duplication [P1]

**User Story:** As a player, I want to duplicate an existing plan, so that I can create variations without re-entering all the data.

#### Acceptance Criteria

1. WHEN viewing a plan, THE System SHALL provide a "Duplicate" action button
2. WHEN a user clicks Duplicate, THE System SHALL create a new plan with copied data
3. THE duplicated plan SHALL have a modified title indicating it is a copy
4. THE duplicated plan SHALL include all attributes, skills, goals, and aptitude grades
5. WHEN duplication completes, THE System SHALL navigate to the new plan in edit mode

### Requirement 28: Plan Status Management [P0]

**User Story:** As a player, I want to mark plans with different statuses, so that I can organize active, completed, and archived training records.

#### Acceptance Criteria

1. WHEN editing a plan, THE System SHALL provide a status selector with options (In Progress, Completed, Archived)
2. WHEN a plan status changes, THE System SHALL update the visual indicator in the plan list
3. THE Plan_List SHALL support filtering by status
4. WHEN a plan is marked Completed, THE System SHALL prompt to record final stats
5. WHEN a plan is Archived, THE System SHALL move it to a separate archived section

### Requirement 29: Keyboard Navigation and Shortcuts [P1]

**User Story:** As a power user, I want to use keyboard shortcuts, so that I can navigate and perform actions more efficiently.

#### Acceptance Criteria

1. THE System SHALL support Tab navigation through all interactive elements
2. WHEN on the Dashboard, pressing "N" SHALL open the Quick Create Modal
3. WHEN editing a plan, pressing Ctrl+S SHALL save the current changes
4. WHEN in a modal, pressing Escape SHALL close the modal
5. THE System SHALL display a keyboard shortcuts help panel accessible via "?" key

### Requirement 30: Notification System [P0]

**User Story:** As a player, I want to receive visual feedback for my actions, so that I know when operations succeed or fail.

#### Acceptance Criteria

1. WHEN an action succeeds (save, delete, export), THE System SHALL display a success toast notification
2. WHEN an action fails, THE System SHALL display an error toast notification with details
3. THE toast notifications SHALL auto-dismiss after 5 seconds
4. THE toast notifications SHALL be dismissible by clicking the close button
5. WHEN multiple notifications occur, THE System SHALL stack them vertically

### Requirement 31: Mood and Condition Tracking [P0]

**User Story:** As a player, I want to track my character's mood and conditions, so that I can monitor training effectiveness modifiers.

#### Acceptance Criteria

1. WHEN editing a plan, THE System SHALL provide a mood selector with options: Great (最高/+4%), Good (良い/+2%), Normal (普通/0%), Bad (悪い/-2%), Awful (最悪/-4%)
2. THE System SHALL display the mood's effect on training and racing performance as a percentage modifier
3. WHEN editing a plan, THE System SHALL allow tracking of conditions (Night Owl, Practice Poor, Overweight, Charming, Practice Perfect)
4. THE System SHALL visually distinguish positive conditions (green) from negative conditions (red)
5. WHEN a negative condition is active, THE System SHALL display a warning indicator on the plan card in the list view

### Requirement 32: Energy Level Tracking [P0]

**User Story:** As a player, I want to track my character's energy level, so that I can plan training sessions and avoid training failures.

#### Acceptance Criteria

1. WHEN editing a plan, THE System SHALL provide an energy level input (0-100%)
2. THE System SHALL display energy level with color-coded indicators (green >50%, yellow 30-50%, red <30%)
3. WHEN energy is below 30%, THE System SHALL display a warning about high training failure risk
4. THE System SHALL allow recording energy changes per turn in the turn tracking feature
5. THE energy display SHALL show recommended actions based on current level (train, rest, recreation)

### Requirement 33: Career Year and Phase Display [P0]

**User Story:** As a player, I want to see which career year and phase my plan is in, so that I can track progress through the career timeline.

#### Acceptance Criteria

1. WHEN viewing a plan, THE System SHALL display the current career year (Junior Year, Classic Year, Senior Year)
2. THE System SHALL calculate and display approximate turn ranges for each year (Junior: ~24 turns, Classic: ~24 turns, Senior: ~24 turns)
3. WHEN viewing turn data, THE System SHALL highlight special periods (Summer Training Camp, URA Finale)
4. THE System SHALL display a career progress bar showing completion percentage
5. WHEN a plan reaches Senior Year final turns, THE System SHALL indicate URA Finale preparation phase

### Requirement 34: WCAG Level A - Perceivable Content [P0]

**User Story:** As a user with visual impairments, I want content to be perceivable through multiple senses, so that I can access all information regardless of my abilities.

#### Acceptance Criteria

1. THE System SHALL provide text alternatives (alt text) for all non-text content including character images, stat icons, and decorative graphics (WCAG 1.1.1)
2. WHEN displaying video or audio content, THE System SHALL provide captions or transcripts (WCAG 1.2.1, 1.2.2)
3. THE System SHALL ensure all information conveyed by color is also available through text or other visual means (WCAG 1.4.1)
4. WHEN displaying stat colors, THE System SHALL include text labels (Speed, Stamina, etc.) alongside color indicators
5. THE System SHALL ensure text content can be resized up to 200% without loss of content or functionality (WCAG 1.4.4)
6. WHEN using images of text, THE System SHALL use actual text instead except for logos or decorative purposes (WCAG 1.4.5)

### Requirement 35: WCAG Level A - Operable Interface [P0]

**User Story:** As a user who relies on keyboard navigation, I want all functionality to be accessible via keyboard, so that I can use the app without a mouse.

#### Acceptance Criteria

1. THE System SHALL make all functionality available from a keyboard without requiring specific timings (WCAG 2.1.1)
2. THE System SHALL NOT trap keyboard focus in any component; users SHALL be able to navigate away using standard keys (WCAG 2.1.2)
3. WHEN content has time limits, THE System SHALL allow users to turn off, adjust, or extend the time limit (WCAG 2.2.1)
4. THE System SHALL NOT include content that flashes more than three times per second (WCAG 2.3.1)
5. THE System SHALL provide a mechanism to skip repetitive navigation blocks (skip to main content link) (WCAG 2.4.1)
6. THE System SHALL provide descriptive page titles for each page (Dashboard, Plan Details, Characters, Guide) (WCAG 2.4.2)
7. THE System SHALL ensure focus order follows a logical sequence that preserves meaning and operability (WCAG 2.4.3)
8. THE System SHALL ensure link text describes the purpose of the link (avoid "click here") (WCAG 2.4.4)

### Requirement 36: WCAG Level A - Understandable Content [P0]

**User Story:** As a user, I want the interface to be predictable and help me avoid errors, so that I can use the app confidently.

#### Acceptance Criteria

1. THE System SHALL specify the language of the page in the HTML lang attribute (WCAG 3.1.1)
2. WHEN a component receives focus, THE System SHALL NOT automatically change context (no unexpected navigation) (WCAG 3.2.1)
3. WHEN a user changes a form input setting, THE System SHALL NOT automatically change context unless warned beforehand (WCAG 3.2.2)
4. THE System SHALL identify navigation mechanisms consistently across pages (same menu structure) (WCAG 3.2.3)
5. THE System SHALL identify components with the same functionality consistently (same button labels) (WCAG 3.2.4)
6. WHEN an input error is detected, THE System SHALL identify the error and describe it in text (WCAG 3.3.1)
7. THE System SHALL provide labels or instructions for user input fields (WCAG 3.3.2)

### Requirement 37: WCAG Level A - Robust Markup [P0]

**User Story:** As a user of assistive technology, I want the app to use proper HTML semantics, so that my screen reader can interpret the content correctly.

#### Acceptance Criteria

1. THE System SHALL use valid HTML markup that can be parsed by user agents and assistive technologies (WCAG 4.1.1)
2. THE System SHALL provide name, role, and value for all user interface components (WCAG 4.1.2)
3. THE System SHALL use semantic HTML elements (button for buttons, nav for navigation, main for main content, etc.)
4. THE System SHALL use ARIA attributes correctly when native HTML semantics are insufficient
5. THE System SHALL ensure form inputs have associated labels using the for/id relationship or aria-labelledby
6. THE System SHALL announce status messages to assistive technologies without receiving focus (WCAG 4.1.3)

### Requirement 38: Accessible Color Contrast [P0]

**User Story:** As a user with low vision, I want sufficient color contrast, so that I can read text and distinguish UI elements.

#### Acceptance Criteria

1. THE System SHALL ensure normal text has a contrast ratio of at least 4.5:1 against its background (WCAG 1.4.3)
2. THE System SHALL ensure large text (18pt or 14pt bold) has a contrast ratio of at least 3:1 (WCAG 1.4.3)
3. WHEN displaying stat colors on backgrounds, THE System SHALL verify contrast meets minimum requirements
4. THE System SHALL ensure UI components and graphical objects have a contrast ratio of at least 3:1 (WCAG 1.4.11)
5. WHEN in dark mode, THE System SHALL maintain the same contrast ratio requirements
6. THE System SHALL provide a high contrast mode option for users who need enhanced visibility

### Requirement 39: Accessible Forms and Inputs [P0]

**User Story:** As a user with disabilities, I want forms to be accessible and provide clear feedback, so that I can complete tasks successfully.

#### Acceptance Criteria

1. THE System SHALL associate all form inputs with visible labels positioned consistently (above or to the left)
2. THE System SHALL group related form controls using fieldset and legend elements
3. WHEN a form field is required, THE System SHALL indicate this both visually and programmatically (aria-required)
4. WHEN validation errors occur, THE System SHALL move focus to the first error and announce it to screen readers
5. THE System SHALL provide autocomplete attributes for common fields (name, email) to assist autofill
6. THE System SHALL ensure custom form controls (dropdowns, date pickers) are keyboard accessible and have proper ARIA roles

### Requirement 40: Accessible Data Tables [P0]

**User Story:** As a screen reader user, I want data tables to be properly structured, so that I can understand the relationship between headers and data.

#### Acceptance Criteria

1. THE System SHALL use proper table markup (table, thead, tbody, th, td) for tabular data (skills list, turn history)
2. THE System SHALL associate data cells with headers using scope attribute or headers/id relationship
3. THE System SHALL provide a caption or aria-label describing the table's purpose
4. THE System SHALL NOT use tables for layout purposes
5. WHEN displaying the skills table, THE System SHALL mark column headers (Skill Name, SP Cost, Tier, Type, Status, Turn Acquired) with th elements
6. THE System SHALL ensure tables are responsive and remain accessible on mobile devices

### Requirement 41: Focus Management and Visibility [P0]

**User Story:** As a keyboard user, I want to always see where my focus is, so that I can navigate the interface effectively.

#### Acceptance Criteria

1. THE System SHALL provide a visible focus indicator on all interactive elements (minimum 2px outline)
2. THE System SHALL NOT remove or hide the default focus outline without providing an equally visible alternative
3. WHEN a modal opens, THE System SHALL move focus to the modal and trap focus within until closed
4. WHEN a modal closes, THE System SHALL return focus to the element that triggered it
5. WHEN content is dynamically loaded, THE System SHALL manage focus appropriately (move to new content or announce it)
6. THE System SHALL ensure focus indicators have sufficient contrast (3:1 ratio against adjacent colors)

### Requirement 42: Livewire Component Best Practices [P0]

**User Story:** As a developer, I want the frontend to follow Livewire best practices, so that the application is performant, maintainable, and provides a smooth user experience.

#### Acceptance Criteria

1. THE System SHALL use Livewire's wire:model.live sparingly and prefer wire:model.blur or wire:model.change for form inputs to reduce server requests
2. THE System SHALL implement loading states using wire:loading to provide visual feedback during server communication
3. THE System SHALL use wire:key on repeated elements (plan lists, skill rows) to ensure proper DOM diffing
4. THE System SHALL debounce search inputs using wire:model.debounce.300ms to prevent excessive server requests
5. THE System SHALL use Livewire's lazy loading (wire:init) for components that load heavy data
6. THE System SHALL implement optimistic UI updates where appropriate to improve perceived performance

### Requirement 43: Alpine.js Integration Best Practices [P0]

**User Story:** As a developer, I want Alpine.js to be used appropriately alongside Livewire, so that client-side interactions are fast and server load is minimized.

#### Acceptance Criteria

1. THE System SHALL use Alpine.js for purely client-side interactions (dropdowns, tabs, modals, tooltips) that don't require server state
2. THE System SHALL use x-cloak to prevent flash of unstyled content on Alpine components
3. THE System SHALL use $wire to communicate between Alpine and Livewire when needed
4. THE System SHALL prefer Alpine's x-show over Livewire re-renders for simple visibility toggles
5. THE System SHALL use Alpine's x-transition for smooth animations on show/hide operations
6. THE System SHALL avoid mixing Alpine state with Livewire state for the same data to prevent synchronization issues

### Requirement 44: TailwindCSS Styling Best Practices [P0]

**User Story:** As a developer, I want consistent and maintainable styling, so that the UI is cohesive and easy to update.

#### Acceptance Criteria

1. THE System SHALL use Tailwind's design tokens (colors, spacing, typography) consistently throughout the application
2. THE System SHALL extract repeated utility patterns into Blade components or @apply directives for reusability
3. THE System SHALL use Tailwind's responsive prefixes (sm:, md:, lg:, xl:) for mobile-first responsive design
4. THE System SHALL use Tailwind's dark: variant for dark mode styling instead of custom CSS
5. THE System SHALL define custom colors (stat colors, grade colors) in tailwind.config.js for consistency
6. THE System SHALL use Tailwind's group and peer utilities for interactive state styling (hover, focus)

### Requirement 45: Performance Optimization [P0]

**User Story:** As a user, I want the application to load quickly and respond instantly, so that I can track my training without delays.

#### Acceptance Criteria

1. THE System SHALL lazy load images using loading="lazy" attribute or Livewire's lazy loading
2. THE System SHALL implement pagination or infinite scroll for plan lists exceeding 20 items
3. THE System SHALL cache frequently accessed data (character roster, skill database) using Laravel's cache
4. THE System SHALL minimize Livewire component nesting to reduce re-render overhead
5. THE System SHALL use Vite for asset bundling with code splitting for route-based chunks
6. THE System SHALL implement service worker caching for static assets to improve repeat visit performance
7. THE System SHALL target First Contentful Paint (FCP) under 1.5 seconds and Time to Interactive (TTI) under 3 seconds

### Requirement 46: Error Boundary and Recovery [P0]

**User Story:** As a user, I want the application to handle errors gracefully, so that I don't lose my work when something goes wrong.

#### Acceptance Criteria

1. THE System SHALL implement Livewire error handling to display user-friendly messages instead of technical errors
2. THE System SHALL auto-save form data to localStorage every 30 seconds to prevent data loss
3. WHEN a Livewire connection is lost, THE System SHALL display a reconnection indicator and attempt automatic reconnection
4. THE System SHALL provide a "Restore Draft" option when returning to an unsaved form
5. WHEN a server error occurs, THE System SHALL log the error and display a retry option to the user
6. THE System SHALL implement circuit breaker pattern for external API calls to prevent cascade failures

### Requirement 47: Loading States and Skeleton Screens [P0]

**User Story:** As a user, I want to see loading indicators while content loads, so that I know the application is working.

#### Acceptance Criteria

1. WHEN a Livewire action is processing, THE System SHALL display a loading spinner or progress indicator
2. THE System SHALL use skeleton screens (placeholder shapes) for initial page loads instead of blank screens
3. THE System SHALL disable form submit buttons during submission to prevent double-clicks
4. WHEN loading plan details, THE System SHALL show skeleton placeholders for each tab section
5. THE System SHALL use wire:loading.class to add visual feedback to the triggering element
6. THE System SHALL provide loading text alternatives for screen readers using aria-busy and aria-live

### Requirement 48: Form UX Best Practices [P0]

**User Story:** As a user, I want forms to be intuitive and forgiving, so that I can enter data quickly and correctly.

#### Acceptance Criteria

1. THE System SHALL preserve form state when switching between tabs within the same page
2. THE System SHALL provide inline validation feedback as users type (after initial blur)
3. THE System SHALL use appropriate input types (number, email, tel) for mobile keyboard optimization
4. THE System SHALL support autofill for common fields and remember user preferences
5. THE System SHALL provide clear visual distinction between required and optional fields
6. THE System SHALL implement smart defaults based on previous entries or common values
7. THE System SHALL allow undo/redo for destructive actions within a reasonable time window

### Requirement 49: Navigation and Wayfinding [P0]

**User Story:** As a user, I want to always know where I am in the application, so that I can navigate confidently.

#### Acceptance Criteria

1. THE System SHALL highlight the current page/section in the navigation menu
2. THE System SHALL provide breadcrumb navigation on detail pages (Dashboard > Plans > Plan Name)
3. THE System SHALL maintain scroll position when returning to list pages from detail views
4. THE System SHALL use browser history correctly so back/forward buttons work as expected
5. THE System SHALL provide a consistent "Back" or "Cancel" action on all detail/edit pages
6. THE System SHALL show unsaved changes indicator in the page title or tab when forms are dirty

### Requirement 50: Empty States and Onboarding [P1]

**User Story:** As a new user, I want helpful guidance when starting out, so that I understand how to use the application.

#### Acceptance Criteria

1. WHEN the plan list is empty, THE System SHALL display an illustrated empty state with a clear call-to-action
2. THE System SHALL provide contextual help tooltips on complex features (aptitude grades, stat max)
3. THE System SHALL offer a first-run tutorial or guided tour for new users
4. WHEN a search returns no results, THE System SHALL suggest alternative searches or show popular items
5. THE System SHALL provide example data or templates for users to understand expected input formats
6. THE System SHALL include inline help text for game-specific terminology (linking to the Guide page)

### Requirement 51: Confirmation and Destructive Actions [P0]

**User Story:** As a user, I want protection against accidental data loss, so that I don't accidentally delete important records.

#### Acceptance Criteria

1. WHEN a user attempts to delete a plan, THE System SHALL require explicit confirmation with the plan name
2. THE System SHALL implement soft delete with a 30-day recovery period for deleted plans
3. WHEN navigating away from unsaved changes, THE System SHALL prompt for confirmation
4. THE System SHALL provide an "Undo" option for recently deleted items (within 10 seconds)
5. THE System SHALL require double-confirmation for bulk delete operations
6. THE System SHALL disable destructive action buttons during processing to prevent accidental double-clicks

### Requirement 52: Responsive Touch Interactions [P0]

**User Story:** As a mobile user, I want touch-friendly interactions, so that I can use the app comfortably on my phone or tablet.

#### Acceptance Criteria

1. THE System SHALL ensure all touch targets are at least 44x44 pixels for comfortable tapping
2. THE System SHALL support swipe gestures for common actions (swipe to delete, swipe between tabs)
3. THE System SHALL avoid hover-dependent interactions on touch devices
4. THE System SHALL provide touch-friendly alternatives for drag-and-drop (move up/down buttons)
5. THE System SHALL implement pull-to-refresh on list pages for mobile users
6. THE System SHALL use touch-action CSS property to prevent unwanted browser behaviors

### Requirement 53: Visual Hierarchy and Typography [P0]

**User Story:** As a user, I want a clear visual hierarchy, so that I can quickly scan and find important information.

#### Acceptance Criteria

1. THE System SHALL use consistent heading hierarchy (h1 for page title, h2 for sections, h3 for subsections)
2. THE System SHALL use font weight and size to establish clear information hierarchy
3. THE System SHALL limit body text line length to 65-75 characters for optimal readability
4. THE System SHALL use adequate line height (1.5 for body text) for comfortable reading
5. THE System SHALL use whitespace effectively to group related content and separate sections
6. THE System SHALL ensure primary actions are visually prominent (larger, colored buttons)

### Requirement 54: Feedback and Microinteractions [P1]

**User Story:** As a user, I want immediate feedback for my actions, so that I feel confident the application is responding.

#### Acceptance Criteria

1. THE System SHALL provide visual feedback within 100ms of user interaction (button press, link click)
2. THE System SHALL use subtle animations for state changes (checkbox toggle, accordion expand)
3. THE System SHALL animate number changes (stat values, SP totals) for better perception
4. THE System SHALL provide haptic feedback on mobile for important actions (if supported)
5. THE System SHALL use progress indicators for operations taking longer than 1 second
6. THE System SHALL celebrate achievements (plan completion, goal reached) with positive visual feedback

### Requirement 55: Internationalization Readiness [P2]

**User Story:** As a developer, I want the application to be ready for internationalization, so that it can be translated to other languages in the future.

#### Acceptance Criteria

1. THE System SHALL use Laravel's localization features (\_\_(), trans()) for all user-facing strings
2. THE System SHALL store translations in language files rather than hardcoding text in views
3. THE System SHALL support both English and Japanese text for game-specific terms (already in glossary)
4. THE System SHALL use relative date/time formatting that respects locale settings
5. THE System SHALL avoid concatenating translated strings to prevent grammar issues
6. THE System SHALL use ICU message format for pluralization and complex translations

### Requirement 56: Storage Mode (Local vs Account) and Offline Behavior [P0]

**User Story:** As a player, I want to choose where my plans are stored, so that I can use the app without an account or sync across devices when logged in.

#### Acceptance Criteria

1. THE System SHALL support two storage modes: Local (localStorage) and Account (database)
2. WHEN a user is not authenticated, THE System SHALL create all plans as Local_Runs stored in localStorage
3. WHEN a user is authenticated, THE System SHALL default to Account_Runs but allow choosing Local from the create modal
4. THE Plan_List SHALL display a storage mode badge (Local/Account) on each plan card
5. WHEN viewing a Local_Run, THE System SHALL indicate "Stored locally - available offline"
6. WHEN viewing an Account_Run without network connectivity, THE System SHALL display "Requires connection" and disable editing
7. WHEN a user logs in with existing Local_Runs, THE System SHALL offer a "Claim Plans" flow to convert them to Account_Runs
8. THE "Claim Plans" conversion SHALL be a Move operation by default (delete local after successful conversion), with an optional "Keep local copy" checkbox
9. THE System SHALL allow exporting Local_Runs to JSON for backup and importing on other devices
10. Local_Runs SHALL be fully functional offline including create, edit, delete, and export operations
11. THE System SHALL store Local_Runs under a versioned schema (e.g., `schema_version: "1.0"`) to support future migrations

**MVP Scope Note:** MVP uses localStorage for Local_Runs. A future enhancement may migrate Local_Run storage to IndexedDB for larger datasets, better performance, and structured queries. The sync-queue and conflict resolution patterns are explicitly deferred until IndexedDB migration.

### Requirement 56B: Local Data Management [P1]

**User Story:** As a player, I want a dedicated interface to manage my locally stored plans, so that I can backup, export, import, and clean up local data without losing important records.

#### Acceptance Criteria

1. THE System SHALL provide a Local Data Management interface accessible from the Dashboard or navigation menu
2. THE Local Data Management interface SHALL display all Local_Runs with storage size estimates and last modified dates
3. THE System SHALL provide an "Export All Local Data" action that generates a single JSON file containing all Local_Runs
4. THE System SHALL provide an "Import Local Data" action that accepts JSON files and merges/replaces local data with conflict resolution options
5. THE System SHALL provide a "Purge All Local Data" action with double-confirmation showing the count and total size to be deleted
6. WHEN a user is authenticated, THE System SHALL provide a "Convert All to Account" bulk action for migrating all Local_Runs
7. THE System SHALL display localStorage usage statistics (used/available space) and warn when approaching browser quota limits (~80% full)
8. THE System SHALL support selective export (choose which Local_Runs to include in export)
9. THE System SHALL display the current local schema version and indicate if migration is available

### Requirement 57: Draft Recovery and Auto-Save [P0]

**User Story:** As a player, I want my unsaved work to be automatically preserved, so that I don't lose data due to browser crashes or accidental navigation.

#### Acceptance Criteria

1. THE System SHALL auto-save form data to localStorage every 30 seconds while editing
2. WHEN returning to an unsaved form, THE System SHALL display a "Restore Draft" modal showing draft timestamp and preview
3. THE System SHALL provide a "Discard Local Draft" action to clear saved drafts
4. THE System SHALL maintain a draft timeline showing the last 3 auto-saved versions
5. WHEN a draft is older than 7 days, THE System SHALL prompt the user to restore or discard it
6. THE System SHALL clear drafts automatically after successful server save

### Requirement 58: Global Search [P1]

**User Story:** As a player, I want to search across all my data from a single search bar, so that I can quickly find plans, skills, goals, or races.

#### Acceptance Criteria

1. THE System SHALL provide a global search bar in the header accessible via "/" keyboard shortcut
2. WHEN a user types in the global search, THE System SHALL search across plans (title, character name), skills, goals, and race predictions
3. THE System SHALL display search results grouped by category (Plans, Skills, Goals, Races) with result counts
4. WHEN a user selects a search result, THE System SHALL navigate to the relevant plan/section with the match highlighted
5. THE System SHALL support search operators (e.g., "status:active", "character:Special Week", "skill:speed")
6. THE System SHALL display recent searches and provide search suggestions based on history

### Requirement 59: Saved Filters and Views [P2]

**User Story:** As a player, I want to save my frequently used filter combinations, so that I can quickly access specific subsets of my plans.

#### Acceptance Criteria

1. THE System SHALL allow users to save current filter settings as a named "Saved View"
2. THE System SHALL provide predefined views: "All Active", "Completed", "URA Finals Ready", "By Strategy"
3. WHEN a user selects a saved view, THE System SHALL apply all associated filters instantly
4. THE System SHALL allow users to edit, rename, and delete custom saved views
5. THE System SHALL display saved views in a sidebar or dropdown for quick access
6. THE System SHALL persist saved views to user preferences (localStorage or database if authenticated)

### Requirement 60: Tags and Folders Organization [P2]

**User Story:** As a player, I want to organize my plans with custom tags and folders, so that I can categorize and find them easily.

#### Acceptance Criteria

1. WHEN editing a plan, THE System SHALL allow adding multiple user-defined tags (e.g., "Aoharu", "Mile Cup", "Experiment")
2. THE System SHALL provide tag autocomplete from previously used tags
3. THE System SHALL support filtering the plan list by one or more tags
4. THE System SHALL allow creating folders/collections to group related plans
5. THE System SHALL support pinning favorite plans to the top of the list
6. THE System SHALL display tag counts and allow bulk tag management

### Requirement 61: Bulk Actions [P2]

**User Story:** As a player, I want to perform actions on multiple plans at once, so that I can efficiently manage large collections.

#### Acceptance Criteria

1. THE System SHALL provide a multi-select mode with checkboxes on the plan list
2. WHEN multiple plans are selected, THE System SHALL display a bulk action toolbar
3. THE System SHALL support bulk actions: Archive, Delete, Export, Add Tag, Remove Tag, Change Status
4. WHEN bulk delete is triggered, THE System SHALL require double-confirmation showing the count and plan names
5. THE System SHALL display progress feedback during bulk operations
6. THE System SHALL allow canceling bulk operations in progress

### Requirement 62: Support Card Loadout Tracking [P3]

**User Story:** As a player, I want to track my support card deck for each plan, so that I can record which cards contributed to my training run.

#### Acceptance Criteria

1. WHEN editing a plan, THE System SHALL provide a Support Cards tab for managing the 6-card deck
2. THE System SHALL allow selecting support cards from a searchable database with card name, type, and rarity
3. THE System SHALL track card level (1-50), limit break status, and friendship level (0-100%) for each card
4. THE System SHALL display skill hints available from each support card
5. THE System SHALL calculate and display total stat bonuses from the support card deck
6. WHEN friendship reaches ~80%, THE System SHALL indicate "Friendship Training Available" status

### Requirement 63: Plan Templates and Presets [P2]

**User Story:** As a player, I want to create plans from templates, so that I can quickly start new runs with predefined configurations.

#### Acceptance Criteria

1. THE System SHALL allow saving any plan as a reusable template
2. THE System SHALL provide predefined archetype templates (Front Runner Mile, Long Distance Stamina, etc.)
3. WHEN creating a new plan, THE System SHALL offer "Start from Template" option
4. THE System SHALL include preset skill sets (e.g., "Mile Core", "Debuff Set", "Speed Stack") selectable during plan creation
5. THE System SHALL allow customizing which fields are copied from templates (skills, goals, aptitudes, support cards)
6. THE System SHALL support community-shared templates (read-only imports from JSON)

### Requirement 64: Plan Comparison View [P3]

**User Story:** As a player, I want to compare multiple plans side-by-side, so that I can analyze differences in builds and outcomes.

#### Acceptance Criteria

1. THE System SHALL provide a "Compare" action allowing selection of 2-4 plans
2. THE System SHALL display a side-by-side comparison view with aligned sections
3. THE System SHALL highlight differences in stats, skills, and aptitudes between compared plans
4. THE System SHALL show stat radar charts overlaid for visual comparison
5. THE System SHALL calculate and display delta values (e.g., "+150 Speed", "-2 skills")
6. THE System SHALL allow exporting the comparison as an image or PDF

### Requirement 65: Race Readiness Scoring [P2]

**User Story:** As a player, I want to see readiness scores for upcoming races, so that I can make informed decisions about race scheduling.

#### Acceptance Criteria

1. WHEN viewing race predictions, THE System SHALL calculate a readiness score (0-100%) for each race
2. THE readiness score SHALL factor in: stat thresholds for distance, aptitude grades, running style match, mood modifier, and condition effects
3. THE System SHALL display readiness as a color-coded indicator (green ≥80%, yellow 50-79%, red <50%)
4. THE System SHALL show specific deficiencies (e.g., "Stamina 50 below recommended", "B aptitude penalty")
5. THE System SHALL provide recommendations to improve readiness (e.g., "Train Stamina +50", "Acquire skill X")
6. THE System SHALL support both Career mode and Champions Meeting stat thresholds

### Requirement 66: Stat Efficiency Analytics [P3]

**User Story:** As a player, I want to see training efficiency insights, so that I can optimize my training decisions.

#### Acceptance Criteria

1. WHEN viewing a plan with turn data, THE System SHALL display stat efficiency metrics
2. THE System SHALL calculate average stat gain per turn, per training type, and per career year
3. THE System SHALL identify best/worst training phases with visual highlights on the progression chart
4. THE System SHALL show "training ROI" comparing actual gains vs expected gains for character growth rates
5. THE System SHALL display a breakdown of stat sources (training, races, events, skills)
6. THE System SHALL provide trend analysis with projections for remaining turns

### Requirement 67: SP Planning and Skill Shopping List [P2]

**User Story:** As a player, I want to plan my skill purchases, so that I can optimize SP spending throughout my career run.

#### Acceptance Criteria

1. THE System SHALL track current SP balance and display it prominently on the Skills tab
2. THE System SHALL allow marking skills as "Planned" (want to acquire) vs "Acquired"
3. THE System SHALL calculate total SP needed for all planned skills and show shortfall/surplus
4. THE System SHALL display a prioritized shopping list sorted by user-defined priority or SP efficiency
5. WHEN SP is insufficient for planned skills, THE System SHALL display a warning with suggestions
6. THE System SHALL estimate SP income from remaining races and events based on career stage

### Requirement 68: Import Wizard and Versioned Export [P1]

**User Story:** As a player, I want to import previously exported data through a guided wizard, so that I can restore backups or migrate between devices.

#### Acceptance Criteria

1. WHEN a user navigates to /import, THE System SHALL display an Import Wizard with file upload and format detection
2. THE System SHALL support importing plans from .json export files (primary MVP format, compatible with uma-run-tracker JSON schema)
3. WHEN a file is uploaded, THE System SHALL detect the format and schema version, displaying a preview of importable plans
4. THE System SHALL validate imported data against the current schema and show warnings for incompatible fields
5. WHEN importing an older schema version, THE System SHALL automatically migrate data to current format with a migration report
6. THE System SHALL allow selective import (choose which plans to import from a multi-plan file)
7. IF import conflicts with existing plans (same ID), THEN THE System SHALL offer: Skip, Overwrite, or Import as Copy options
8. THE System SHALL allow choosing storage target: "Import as Local" (default for anonymous) or "Import as Account" (if authenticated)
9. THE System SHALL include a schema version number (e.g., "v1.0") in all exports for forward compatibility
10. WHEN import completes, THE System SHALL display a results report showing imported, skipped, and failed items
11. XLSX import is P2 scope and may be deferred; JSON import is required for MVP

### Requirement 69: Shareable Links [P3]

**User Story:** As a player, I want to share my plans with others via a link, so that I can get feedback or showcase my builds.

#### Acceptance Criteria

1. THE System SHALL provide a "Share" action that generates a read-only public link
2. THE System SHALL allow customizing share settings: include/exclude notes, anonymize personal data
3. THE shared view SHALL display plan data in a clean, read-only format without edit controls
4. THE System SHALL allow revoking share links at any time
5. THE System SHALL track share link access counts (optional analytics)
6. THE System SHALL support time-limited share links (expire after X days)

### Requirement 70: WCAG AA Compliance and Reduced Motion [P1]

**User Story:** As a user with disabilities, I want the application to meet WCAG AA standards and respect my motion preferences, so that I can use it comfortably.

#### Acceptance Criteria

1. THE System SHALL meet WCAG 2.1 Level AA criteria including 1.4.10 Reflow (content reflows at 400% zoom)
2. THE System SHALL meet WCAG 2.4.7 Focus Visible (focus indicator always visible)
3. WHEN the user has `prefers-reduced-motion: reduce` set, THE System SHALL disable all non-essential animations
4. THE System SHALL provide a manual "Reduce Motion" toggle in settings
5. WHEN reduced motion is active, THE Circular_Progress and Stat_Chart SHALL render without animation
6. THE System SHALL provide a tabular "Data View" alternative for all charts accessible to screen readers

### Requirement 71: Virtualized Lists and Performance Scaling [P2]

**User Story:** As a power user with many plans, I want the application to remain fast, so that I can manage large collections without lag.

#### Acceptance Criteria

1. THE System SHALL implement virtualized scrolling for plan lists exceeding 50 items
2. THE System SHALL implement virtualized scrolling for turn tables (70+ turns)
3. THE System SHALL use server-side pagination for skill database queries (100 items per page)
4. THE System SHALL implement route-based code splitting with prefetching on hover
5. THE System SHALL cache character roster and skill database with 24-hour TTL and manual invalidation
6. THE System SHALL target Largest Contentful Paint (LCP) under 2.5 seconds for plan list page

### Requirement 72: Security and Upload Hardening [P0]

**User Story:** As a user, I want my data to be secure, so that I can trust the application with my information.

#### Acceptance Criteria

1. THE System SHALL implement CSRF protection on all form submissions
2. THE System SHALL validate uploaded images using content-type sniffing (not just extension)
3. THE System SHALL limit image uploads to 2MB and resize images exceeding 1920px width
4. THE System SHALL sanitize all user input to prevent XSS attacks
5. THE System SHALL implement rate limiting on API endpoints (100 requests/minute per IP)
6. IF user accounts are implemented, THEN THE System SHALL ensure per-user plan isolation with audit logging

### Requirement 73: E2E Test Suite for Critical Flows [P1]

**User Story:** As a developer, I want automated end-to-end tests, so that I can ensure critical user flows don't regress.

#### Acceptance Criteria

1. THE System SHALL include Playwright E2E tests for: Dashboard load, Plan create, Plan edit, Plan delete, Export
2. THE System SHALL include visual regression tests for dark mode and responsive breakpoints
3. THE System SHALL provide a seed data generator for consistent test environments
4. THE E2E tests SHALL run in CI/CD pipeline on every pull request
5. THE System SHALL maintain test coverage for all WCAG accessibility requirements
6. THE System SHALL include performance benchmarks in E2E tests (page load times, interaction delays)

### Requirement 74: Contextual Glossary and Onboarding [P1]

**User Story:** As a new player, I want contextual help for game terminology, so that I can understand the app without external research.

#### Acceptance Criteria

1. THE System SHALL display tooltip definitions when hovering/clicking on game terms (stat max, URA Finale, aptitude grades)
2. THE System SHALL link glossary terms to relevant sections in the Guide page
3. THE System SHALL provide a one-click "Create Sample Plan" option for new users
4. THE System SHALL offer an interactive guided tour on first visit covering key features
5. THE System SHALL display a "What's New" changelog modal after updates
6. THE System SHALL remember dismissed tooltips and tour completion in user preferences

### Requirement 75: Turn Entry Shortcuts and Milestones [P1]

**User Story:** As a player, I want faster turn entry with smart defaults, so that I can log my progress efficiently.

#### Acceptance Criteria

1. WHEN adding a turn, THE System SHALL provide quick-select buttons for training type (Speed/Stamina/Power/Guts/Wit/Rest/Race)
2. THE System SHALL auto-suggest stat changes based on selected training type and character growth rates
3. THE System SHALL pre-populate milestone turns (Summer Camp, URA Finale) with known fixed events
4. THE System SHALL allow adding inline notes and optional screenshot attachments per turn
5. THE System SHALL support keyboard shortcuts for rapid turn entry (Tab between fields, Enter to save and add next)
6. THE System SHALL display a mini-timeline showing upcoming milestone turns based on current turn number

### Requirement 76: Dual Editing Modes (Inline and Fullscreen) [P1]

**User Story:** As a player, I want both quick inline editing and comprehensive fullscreen editing, so that I can make simple updates fast while having access to full features when needed.

#### Acceptance Criteria

1. WHEN a user clicks on a plan row in the list, THE Inline_Editor panel SHALL expand showing quick-edit fields
2. THE Inline_Editor SHALL support editing: status, current turn, SP balance, stamina %, mood, conditions, and notes
3. THE Inline_Editor SHALL provide a "Full Edit" button to open the Fullscreen_Editor
4. WHEN a user clicks "Edit" action button, THE System SHALL navigate to the Fullscreen_Editor with all tabs
5. THE Fullscreen_Editor SHALL support all tabs: General, Attributes, Aptitude Grades, Skills, Race Predictions, Goals, Turns, Support Cards
6. WHEN using browser back from Fullscreen_Editor, THE System SHALL return to the plan list with the edited plan visible
7. FOR Local_Runs, THE Inline_Editor changes SHALL auto-save to localStorage after 2 seconds of inactivity
8. FOR Account_Runs, THE Inline_Editor changes SHALL save on blur/change events with explicit Save button for multi-field edits
9. THE Inline_Editor SHALL provide a "Convert to Account" action for Local_Runs when user is authenticated

### Requirement 77: Race-Day Snapshots [P2]

**User Story:** As a player, I want to capture snapshots of my character's state before important races, so that I can track my build at key milestones.

#### Acceptance Criteria

1. WHEN viewing the Race Predictions tab, THE System SHALL provide a "Create Snapshot" action for each race entry
2. WHEN a snapshot is created, THE System SHALL capture: all current stats, acquired skills (references only), aptitude grades, mood, conditions, and energy level
3. THE System SHALL associate snapshots with specific races and display them in a timeline view
4. WHEN viewing a snapshot, THE System SHALL display the captured data in read-only format with timestamp
5. THE System SHALL allow comparing a snapshot to current stats with delta highlighting
6. THE System SHALL support exporting individual snapshots as part of the plan export
7. THE System SHALL limit snapshots to 20 per plan to manage storage
8. Snapshots SHALL NOT include image binaries; only image references (URLs/paths) SHALL be stored
9. FOR Local_Runs, THE System SHALL warn when localStorage is near quota (>80% full) and recommend exporting data

### Requirement 78: Livewire Connection State Management [P0]

**User Story:** As a player, I want clear feedback about connection status, so that I know when my changes are being saved.

#### Acceptance Criteria

1. WHEN Livewire connection is lost, THE System SHALL display a prominent "Connection Lost" banner with retry countdown
2. THE System SHALL automatically attempt reconnection every 5 seconds up to 5 attempts
3. WHEN reconnection succeeds, THE System SHALL display a brief "Reconnected" success message
4. WHEN editing an Account_Run without connection, THE System SHALL disable save buttons and show "Offline - Cannot Save"
5. THE System SHALL preserve unsaved form data as a draft in localStorage during disconnection
6. WHEN reconnection succeeds after editing an Account_Run, THE System SHALL restore the draft and prompt user to save (not auto-save)
7. WHEN all reconnection attempts fail, THE System SHALL offer "Retry Now" and "Work Offline" (switch to Local_Run if desired) options

### Requirement 79: Data-Testid Attributes for E2E Testing [P0]

**User Story:** As a developer, I want stable test selectors, so that E2E tests don't break when CSS classes change.

#### Acceptance Criteria

1. THE System SHALL add data-testid attributes to all interactive elements (buttons, inputs, links, modals)
2. THE System SHALL use consistent naming convention: `data-testid="[component]-[action]-[context]"` (e.g., "plan-create-button", "skill-search-input")
3. THE System SHALL add data-testid to modal containers, tab panels, and form sections
4. THE System SHALL add data-testid to list items with dynamic IDs (e.g., "plan-card-{id}", "skill-row-{index}")
5. THE System SHALL document all data-testid values in a test selector reference file
6. THE System SHALL ensure data-testid attributes are preserved during Livewire re-renders

### Requirement 80: User Authentication (Optional Sign-In) [P2]

**User Story:** As a player, I want to optionally sign in to my account, so that I can access my plans across devices while still being able to use the app anonymously.

#### Acceptance Criteria

1. THE System SHALL support anonymous/local mode where users can use all features without signing in
2. THE System SHALL provide optional authentication via Laravel's built-in authentication (email/password)
3. WHEN a user signs in, THE System SHALL offer to convert existing Local_Runs to Account_Runs via the "Claim Plans" flow
4. THE System SHALL display authentication status in the navbar with sign-in/sign-out options
5. THE System SHALL support "Remember Me" functionality for persistent sessions
6. WHEN a user signs out, THE System SHALL preserve any Local_Runs in localStorage
7. THE System SHALL provide password reset functionality via email
8. THE System SHALL implement secure session management with CSRF protection

### Requirement 81: Cloud Sync and Conflict Resolution [P3]

**User Story:** As a player using multiple devices, I want my Account_Runs to sync across devices with clear conflict handling, so that I can seamlessly continue my work anywhere.

#### Acceptance Criteria

1. WHEN a user edits an Account_Run on one device, THE System SHALL persist changes to the database immediately
2. WHEN a user opens an Account_Run that was modified on another device, THE System SHALL load the latest version from the database
3. IF a conflict is detected (local changes vs server changes), THE System SHALL display a conflict resolution modal
4. THE conflict resolution modal SHALL show both versions side-by-side with differences highlighted
5. THE System SHALL offer conflict resolution options: "Keep Mine", "Keep Server", or "Merge" (for compatible changes)
6. THE System SHALL log conflict resolutions for audit purposes
7. THE System SHALL use optimistic locking (updated_at timestamp) to detect conflicts
8. THE System SHALL NOT implement a complex offline sync queue; Account_Runs require connectivity to save

### Requirement 82: Scheduled Backups and Full Dataset Export [P3]

**User Story:** As a player, I want to backup all my data and optionally schedule automatic backups, so that I can protect against data loss.

#### Acceptance Criteria

1. THE System SHALL provide a "Backup All Data" action that exports all plans (Local and Account) to a single JSON file
2. THE backup file SHALL include all plans, skills, turns, goals, race predictions, snapshots, and user preferences
3. THE System SHALL include a backup manifest with timestamp, plan count, and schema version
4. FOR authenticated users, THE System SHALL offer optional scheduled backups (weekly/monthly) via email download link
5. THE System SHALL support importing full backup files to restore all data
6. WHEN importing a backup, THE System SHALL offer "Replace All" or "Merge" options
7. THE System SHALL retain the last 3 backup files on the server for authenticated users (30-day retention)

### Requirement 83: Skill Presets (Predefined Skill Collections) [P2]

**User Story:** As a player, I want to add predefined skill sets to my plan, so that I can quickly configure common skill builds.

#### Acceptance Criteria

1. THE System SHALL provide predefined skill presets: "Mile Core", "Long Distance Core", "Front Runner Essentials", "Debuff Set", "Recovery Set"
2. WHEN a user selects a skill preset, THE System SHALL add all skills in the preset to the plan with "Suggested" status
3. THE System SHALL allow users to create custom skill presets from their current plan's skills
4. THE System SHALL display preset contents (skill list with SP costs) before applying
5. THE System SHALL warn if applying a preset would add duplicate skills already in the plan
6. THE System SHALL calculate total SP cost for each preset and display it in the selection UI
7. THE System SHALL support importing/exporting skill presets as JSON for sharing

### Requirement 84: Visual Regression Testing [P1]

**User Story:** As a developer, I want automated visual regression tests, so that UI changes don't accidentally break the design.

#### Acceptance Criteria

1. THE System SHALL include Playwright visual comparison tests for key pages (Dashboard, Plan Editor, Character List)
2. THE System SHALL capture baseline screenshots for both light and dark modes
3. THE System SHALL capture baseline screenshots for mobile (375px), tablet (768px), and desktop (1280px) viewports
4. THE System SHALL fail CI builds when visual differences exceed 0.1% threshold
5. THE System SHALL provide a visual diff report showing changed pixels highlighted
6. THE System SHALL allow updating baseline screenshots via a dedicated command
7. THE System SHALL exclude dynamic content (timestamps, IDs) from visual comparisons using masking

### Requirement 85: Event Logging and Metrics [P2]

**User Story:** As a developer, I want to track key user events and errors, so that I can monitor application health and user behavior.

#### Acceptance Criteria

1. THE System SHALL log key events: plan_created, plan_updated, plan_deleted, plan_exported, sync_conflict, import_completed
2. THE System SHALL log errors with context: component name, user action, error message, stack trace
3. THE System SHALL track performance metrics: page load times, Livewire request durations, localStorage usage
4. THE System SHALL provide a developer dashboard showing event counts and error rates (admin only)
5. THE System SHALL implement client-side error boundary that reports JavaScript errors
6. THE System SHALL respect user privacy by not logging PII in event data
7. THE System SHALL support optional integration with external monitoring services (configurable)

### Requirement 86: Seed Data and Demo Mode [P1]

**User Story:** As a developer or new user, I want sample data available, so that I can test features or explore the app with realistic content.

#### Acceptance Criteria

1. THE System SHALL provide a "Load Demo Data" action that creates sample plans with realistic data
2. THE demo data SHALL include 3-5 plans at different career stages with varied characters and strategies
3. THE demo data SHALL include turn history, skills, goals, and race predictions
4. THE System SHALL clearly mark demo plans with a "Demo" badge
5. THE System SHALL provide a "Clear Demo Data" action to remove all demo plans
6. FOR development, THE System SHALL include database seeders that generate consistent test data
7. THE demo data SHALL showcase different features: completed plan, in-progress plan, archived plan

### Requirement 87: Game Mode Support (Career vs Champions Meeting) [P2]

**User Story:** As a player, I want to track builds for different game modes, so that I can optimize for Career mode or Champions Meeting separately.

#### Acceptance Criteria

1. WHEN creating a plan, THE System SHALL allow selecting game mode: "Career" (default) or "Champions Meeting"
2. THE System SHALL display different recommended stat thresholds based on game mode (Career: lower, CM: higher)
3. THE System SHALL adjust race readiness calculations based on game mode requirements
4. THE Plan_List SHALL support filtering by game mode
5. THE System SHALL display game mode badge on plan cards
6. THE System SHALL provide mode-specific tips and recommendations in the Guide page
7. THE System SHALL support "Team Stadium" as a future game mode option (placeholder)
