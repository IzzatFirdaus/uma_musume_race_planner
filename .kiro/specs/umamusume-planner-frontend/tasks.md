# Implementation Plan: Uma Musume Career Planner Frontend

## Scope Statement

**MVP Scope (This Plan):** Implements P0 + selected P1 requirements (Req 1-79). Covers core functionality including dual storage modes (Local/Account), plan CRUD, all editor tabs, export/import, accessibility compliance, and E2E testing.

**Post-MVP Scope:** Requirements 80-87 (optional auth, cloud sync, scheduled backups, skill presets, visual regression, event logging, demo mode, game mode support) are tracked in a separate section at the end of this document.

## Definition of Done (Per Task)

- All interactive elements have `data-testid` attributes
- Playwright coverage exists for key actions
- axe-core accessibility scan passes on touched pages
- Code follows Livewire 3 + Alpine.js best practices
- TailwindCSS classes use design tokens (no hardcoded colors)

## Technology Stack

- **Backend:** Laravel 12+, Livewire 3
- **Frontend:** Alpine.js, TailwindCSS v4
- **Testing:** Pest (PHP), Vitest (JS), Playwright (E2E)
- **Storage:** localStorage (Local_Runs), MySQL/MariaDB/SQLite (Account_Runs)

## Tasks

- [ ] 1. Project Setup and Configuration
  - [ ] 1.1 Configure TailwindCSS v4 with custom color tokens
    - Add stat colors, grade colors, tier colors, mood colors, storage mode colors to tailwind.config.js
    - Configure safelist for dynamic class generation (`text-stat-*`, `bg-grade-*`, `text-tier-*`, `bg-tier-*`, `text-mood-*`, `bg-storage-*`)
    - Set up content paths for Blade + Livewire scanning
    - _Requirements: 7.3, 8.5, 31.4, 44.1, 44.5_

  - [ ] 1.2 Set up Alpine.js stores and global state
    - Create connection store for Livewire connection state
    - Create preferences store for dark mode, reduced motion
    - Create toast store for notification management
    - _Requirements: 12.1, 12.3, 30.1, 46.3_

  - [ ] 1.3 Create base layout and navigation components
    - Create app layout with navbar, main content, toast container
    - Implement dark mode toggle with localStorage persistence
    - Add skip-to-main-content link for accessibility
    - _Requirements: 12.1, 12.2, 14.2, 35.5_

- [ ] 2. Checkpoint - Verify base setup
  - Ensure TailwindCSS builds correctly with custom colors
  - Verify dark mode toggle persists preference
  - Ask the user if questions arise

- [ ] 3. localStorage Service Layer
  - [ ] 3.1 Create LocalRunStorageService
    - Implement versioned schema wrapper (LocalRunsStore interface with `schema_version: "1.0"`)
    - Add CRUD operations for Local_Runs
    - Implement storage quota tracking (StorageStats)
    - Add schema migration support for future versions
    - _Requirements: 56.1, 56.2, 56.10, 56.11, 56B.7_

  - [ ]* 3.2 Write invariant tests for LocalRunStorageService (Vitest)
    - **Invariant 1: Round-trip consistency** - For any valid CareerRun, saving then loading produces equivalent data
    - Use Vitest with fast-check for property-based testing
    - **Validates: Requirements 56.1, 56.10**

  - [ ] 3.3 Create DraftService for auto-save functionality
    - Implement draft save/restore with RunKey format (`local:<uuid>` or `account:<id>`)
    - Add draft expiration logic (7 days)
    - Create draft timeline (last 3 versions)
    - _Requirements: 57.1, 57.2, 57.4, 57.5_

- [ ] 4. Core Data Models and Services
  - [ ] 4.1 Verify CareerRun model fields and add frontend helpers
    - Verify CareerRun has id/uuid dual identifier and storage_mode field
    - Add getRunKey() helper returning `local:<uuid>` or `account:<id>`
    - Add getRunRoute() helper returning `/plans/local/{uuid}` or `/plans/{id}`
    - _Requirements: 56.1, 56.4_

  - [ ] 4.2 Verify Skill model with status enum
    - Verify SkillStatus enum exists (acquired, skipped, suggested)
    - Verify turn_acquired conditional validation (required when status=acquired)
    - Add SP calculation methods if not present
    - _Requirements: 6.4, 6.5, 6.7_

  - [ ]* 4.3 Write invariant tests for SP calculations (Vitest)
    - **Invariant 2: Acquired SP sum** - For any skill set, total acquired SP equals sum of sp_cost where status=acquired
    - **Validates: Requirements 6.7**

  - [ ] 4.4 Create stat validation service
    - Implement hard max validation (1200 threshold)
    - Add total stats calculation
    - _Requirements: 7.5, 7.6_

  - [ ]* 4.5 Write invariant tests for stat validation (Vitest)
    - **Invariant 3: Hard max validation** - For any stat value, reject values above 1200
    - **Validates: Requirements 7.5, 7.6**

- [ ] 5. Checkpoint - Verify data layer
  - Ensure localStorage operations work correctly
  - Verify SP and effective stats calculations
  - Ask the user if questions arise

- [ ] 6. Dashboard Components
  - [ ] 6.1 Create Dashboard page component
    - Implement header banner with app branding
    - Add stats panel with aggregate counts
    - Include recent activity section
    - _Requirements: 1.1, 1.2, 1.4_

  - [ ] 6.2 Create PlanList Livewire component
    - Display both Local and Account plans together
    - Add storage mode badge to each plan card
    - Implement filtering by status, strategy, storage mode
    - _Requirements: 1.3, 3.1, 56.4_

  - [ ] 6.3 Create PlanCard component with inline expansion
    - Show plan title, character name, status, storage badge
    - Implement expandable inline details
    - Add action buttons (View, Edit, Delete, Duplicate)
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

  - [ ]* 6.4 Write invariant tests for plan list filtering (Pest with datasets)
    - **Invariant 4: Filter consistency** - For any filter combination, all displayed plans match filter criteria
    - **Validates: Requirements 3.1, 28.3**

- [ ] 7. Plan Creation Flow
  - [ ] 7.1 Create QuickCreateModal Alpine component
    - Implement modal with title, character, storage mode fields
    - Add storage mode selector (Local/Account based on auth)
    - Handle form submission with validation
    - _Requirements: 2.1, 2.2, 2.3, 56.3_

  - [ ] 7.2 Implement plan creation for both storage modes
    - Local: Generate UUID, save to localStorage, navigate to /plans/local/{uuid}/edit
    - Account: POST to server, navigate to /plans/{id}/edit
    - _Requirements: 2.3, 2.6, 56.2, 56.3_

  - [ ]* 7.3 Write invariant tests for plan creation validation (Pest)
    - **Invariant 5: Empty title rejection** - For any whitespace-only title, creation is rejected
    - **Validates: Requirements 2.4**

- [ ] 8. Checkpoint - Verify dashboard and creation
  - Ensure dashboard displays plans from both storage modes
  - Verify plan creation works for Local and Account modes
  - Ask the user if questions arise

- [ ] 9. Plan View Pages (Read-Only)
  - [ ] 9.1 Create PlanView page component for Account runs
    - Route: /plans/{id} or /plans/{id}/view
    - Load plan from database, display in read-only mode
    - Include all tabs in view-only state
    - _Requirements: 4.1, 4.2_

  - [ ] 9.2 Create LocalPlanView page component for Local runs
    - Route: /plans/local/{uuid} or /plans/local/{uuid}/view
    - Load plan from localStorage, display in read-only mode
    - Show "Stored locally" indicator
    - _Requirements: 4.1, 56.5_

- [ ] 10. Plan Editor - General Tab
  - [ ] 10.1 Create PlanEdit page component with routing
    - Handle both /plans/{id}/edit and /plans/local/{uuid}/edit routes
    - Load plan from appropriate storage based on route
    - Implement dirty state tracking
    - _Requirements: 5.1, 5.3, 76.4, 76.5_

  - [ ] 10.2 Create FormTabs component
    - Implement tabbed interface (General, Attributes, Aptitudes, Skills, Race Predictions, Goals, Turns)
    - Preserve state when switching tabs
    - Add keyboard navigation between tabs
    - _Requirements: 4.2, 5.2, 29.1_

  - [ ] 10.3 Implement General tab fields
    - Title, character selector, career stage, status
    - Mood selector with percentage display
    - Condition checkboxes with positive/negative styling
    - Energy level with color-coded indicator
    - _Requirements: 4.3, 31.1, 31.2, 31.3, 32.1, 32.2_

- [ ] 11. Plan Editor - Attributes Tab
  - [ ] 11.1 Create AttributesDisplay component
    - Display all five stats with numeric inputs
    - Add validation for 0-1200 range (hard max)
    - Show error when value exceeds 1200
    - _Requirements: 7.1, 7.2, 7.5_

  - [ ] 11.2 Create CircularProgress component
    - Implement animated circular indicator
    - Use stat-specific colors
    - Show overflow indicator for values > 1200
    - Respect prefers-reduced-motion (WCAG AA)
    - _Requirements: 20.1, 20.2, 20.3, 20.4, 20.5, 70.3_

  - [ ] 11.3 Add growth rate display
    - Show growth rate percentages for each stat
    - Display total stat sum and effective calculation
    - _Requirements: 7.4, 7.6_

- [ ] 12. Plan Editor - Aptitude Grades Tab
  - [ ] 12.1 Create AptitudeGrades component
    - Display terrain grades (Turf, Dirt)
    - Display distance grades (Sprint, Mile, Medium, Long)
    - Display style grades (Nige, Senkou, Sashi, Oikomi)
    - _Requirements: 8.1, 8.2, 8.3_

  - [ ] 12.2 Implement grade selector dropdowns
    - Show grade options SS-G with effectiveness percentages
    - Apply game-accurate colors to grades
    - _Requirements: 8.4, 8.5_

- [ ] 13. Plan Editor - Skills Tab
  - [ ] 13.1 Create SkillsEditor Livewire component
    - Display skills table with columns: Name, SP, Tier (G- to SS), Type, Status, Turn, Notes
    - Implement add/remove skill rows
    - Calculate and display total acquired SP and planned SP
    - _Requirements: 6.1, 6.2, 6.7_

  - [ ] 13.2 Implement skill autocomplete
    - Query skill reference database with debounced input (300ms)
    - Display matches with name, SP, tier (G- through SS), type, description
    - Auto-populate fields on selection
    - Support partial matching and Japanese names
    - _Requirements: 25.1, 25.2, 25.3, 25.4_

  - [ ] 13.3 Implement skill status selector
    - Three-state selector: Acquired, Skipped, Suggested
    - Show/require turn_acquired when status=Acquired
    - Validate turn range 1-78
    - _Requirements: 6.4, 6.5, 6.6_

  - [ ]* 13.4 Write invariant tests for skill validation (Pest)
    - **Invariant 6: Acquired requires turn** - For any skill with status=acquired, turn_acquired must be valid (1-78)
    - **Validates: Requirements 6.5**

- [ ] 14. Checkpoint - Verify plan editor core tabs
  - Ensure all tabs render correctly
  - Verify skill autocomplete and status changes work
  - Ask the user if questions arise

- [ ] 15. Plan Editor - Additional Tabs
  - [ ] 15.1 Create RacePredictions component
    - Display race prediction entries
    - Support add/remove/reorder predictions
    - Show recommended stamina thresholds
    - _Requirements: 9.1, 9.2, 9.3, 9.5, 9.6_

  - [ ] 15.2 Create GoalsEditor component
    - Display goal checklist
    - Support add/remove/toggle completion
    - Visual distinction for completed goals
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

  - [ ] 15.3 Create TurnsEditor component
    - Display turn entries organized by career year
    - Support add/delete turns with stat inputs
    - Highlight milestone turns (Summer Camp, URA Finale)
    - _Requirements: 18.1, 18.2, 18.3, 18.5, 18.6_

- [ ] 16. Trainee Image Upload
  - [ ] 16.1 Create TraineeImageHandler component
    - Implement image upload control in General tab
    - Preview image before saving
    - Validate file type (JPEG, PNG, WebP) and size (max 2MB)
    - _Requirements: 17.1, 17.2, 17.4_

  - [ ] 16.2 Implement image storage
    - For Account_Runs: Upload to server storage
    - For Local_Runs: Store image path/URL reference only (no base64 in localStorage)
    - Display error messages on upload failure
    - _Requirements: 17.3, 17.5, 72.2, 72.3_

- [ ] 17. Plan Save and Persistence
  - [ ] 17.1 Implement save logic for both storage modes
    - Local: Auto-save to localStorage after 2 seconds of inactivity
    - Account: Save on blur/change for quick fields; explicit Save button for multi-field edits
    - Show success/error notifications
    - _Requirements: 5.4, 5.5, 5.6, 76.7, 76.8_

  - [ ] 17.2 Implement unsaved changes warning
    - Track dirty state across all tabs
    - Warn before navigation with unsaved changes
    - Support Ctrl+S keyboard shortcut
    - _Requirements: 5.3, 29.3, 49.6_

  - [ ] 17.3 Implement draft auto-save
    - Auto-save to localStorage every 30 seconds
    - Show "Restore Draft" modal on return
    - Clear drafts after successful save
    - _Requirements: 57.1, 57.2, 57.6_

- [ ] 18. Checkpoint - Verify plan persistence
  - Ensure saves work for both storage modes
  - Verify draft recovery works
  - Ask the user if questions arise

- [ ] 19. Inline Editor
  - [ ] 19.1 Create InlineEditor component
    - Expand within plan list for quick edits
    - Support editing: status, turn, SP, mood, conditions, notes
    - Local_Runs: Auto-save after 2 seconds of inactivity
    - Account_Runs: Save on blur/change for single fields; explicit Save for multi-field edits
    - _Requirements: 76.1, 76.2, 76.7, 76.8_

  - [ ] 19.2 Add "Convert to Account" action
    - Show action for Local_Runs when authenticated
    - Implement single-run conversion flow
    - _Requirements: 76.9, 56.7_

- [ ] 20. Export Functionality
  - [ ] 20.1 Create ExportPreview modal
    - Display formatted plan data preview
    - Support format selection (JSON, Text, Markdown)
    - _Requirements: 22.1, 22.2, 22.3_

  - [ ] 20.2 Implement JSON export
    - Include all plan fields, skills, turns, goals
    - Add schema_version for compatibility (e.g., "v1.0")
    - Trigger browser download
    - _Requirements: 15.1, 15.2, 15.3, 68.9_

  - [ ]* 20.3 Write invariant tests for export completeness (Vitest)
    - **Invariant 7: Export contains all data** - For any plan, exported JSON contains all fields from CareerRun interface
    - **Validates: Requirements 15.3**

  - [ ] 20.4 Implement Text/Markdown export
    - Format with clear sections and alignment
    - Add "Copy to Clipboard" option
    - _Requirements: 24.1, 24.2, 24.3, 24.4_

- [ ] 21. Import Wizard
  - [ ] 21.1 Create ImportWizard page component
    - Route: /import
    - File upload with format detection
    - Schema version detection and migration
    - _Requirements: 68.1, 68.2, 68.3, 68.5_

  - [ ] 21.2 Implement import preview and validation
    - Show importable plans with conflict detection
    - Offer Skip/Overwrite/Import as Copy options
    - Allow selective import
    - _Requirements: 68.4, 68.6, 68.7_

  - [ ] 21.3 Implement import execution
    - Support import to Local or Account storage (68.8)
    - Show results report (68.10)
    - Note: XLSX import is P2 scope (68.11)
    - _Requirements: 68.8, 68.10_

  - [ ]* 21.4 Write invariant tests for import/export round-trip (Vitest)
    - **Invariant 8: Import/export round-trip** - For any plan, export then import produces equivalent data
    - **Validates: Requirements 68.3, 68.5**

- [ ] 22. Checkpoint - Verify export/import
  - Ensure export generates valid JSON with schema_version
  - Verify import handles schema versions correctly
  - Ask the user if questions arise

- [ ] 23. Local Data Management
  - [ ] 23.1 Create LocalDataManager page component
    - Route: /local-data
    - Display storage stats (used/available, run count)
    - Show warning when approaching quota (80%)
    - _Requirements: 56B.1, 56B.2, 56B.7_

  - [ ] 23.2 Implement Export All action
    - Generate single JSON with all Local_Runs
    - Include schema_version
    - Support selective export (choose which runs)
    - _Requirements: 56B.3, 56B.8_

  - [ ] 23.3 Implement Import action
    - Accept JSON files with validation
    - Merge/replace with conflict resolution
    - _Requirements: 56B.4_

  - [ ] 23.4 Implement Purge All action
    - Double-confirmation with count display
    - Clear localStorage
    - _Requirements: 56B.5_

  - [ ] 23.5 Implement Convert All action
    - Bulk conversion for authenticated users
    - Show results report
    - _Requirements: 56B.6_

- [ ] 24. Storage Mode Conversion Flow
  - [ ] 24.1 Create ConvertModal component
    - Show when user logs in with Local_Runs ("Claim Plans" flow)
    - Allow selecting plans to convert
    - Optional "Keep local copy" checkbox
    - _Requirements: 56.7, 56.8_

  - [ ] 24.2 Implement conversion logic
    - POST each plan to database
    - Delete local copy on success (unless keep option)
    - Show results report
    - _Requirements: 56.7, 56.8_

- [ ] 25. Race Snapshots
  - [ ] 25.1 Create RaceSnapshot component
    - Add "Create Snapshot" action on Race Predictions tab
    - Capture: stats, acquired skill references, aptitudes, mood, conditions, energy
    - _Requirements: 77.1, 77.2_

  - [ ] 25.2 Implement snapshot timeline view
    - Display snapshots associated with races
    - Show captured data in read-only format with timestamp
    - _Requirements: 77.3, 77.4_

  - [ ] 25.3 Implement snapshot comparison
    - Compare snapshot to current stats with delta highlighting
    - _Requirements: 77.5_

  - [ ] 25.4 Add snapshot storage limits
    - Limit to 20 snapshots per plan
    - Store references only (no image binaries)
    - Warn when localStorage near quota for Local_Runs
    - _Requirements: 77.6, 77.7, 77.8, 77.9_

- [ ] 26. Connection State Management
  - [ ] 26.1 Implement connection lost banner
    - Show prominent banner on Livewire connection loss
    - Display retry countdown
    - _Requirements: 78.1, 78.2_

  - [ ] 26.2 Implement reconnection logic
    - Auto-retry every 5 seconds (max 5 attempts)
    - Show "Reconnected" message on success
    - Offer "Retry Now" and "Work Offline" on failure
    - _Requirements: 78.2, 78.3, 78.7_

  - [ ] 26.3 Handle offline editing for Account runs
    - Disable save buttons when offline
    - Preserve draft in localStorage
    - Prompt to save on reconnection (not auto-save)
    - _Requirements: 78.4, 78.5, 78.6_

- [ ] 27. Checkpoint - Verify storage features
  - Ensure Local Data Management works
  - Verify conversion flow works
  - Test connection state handling
  - Ask the user if questions arise

- [ ] 28. Character Roster
  - [ ] 28.1 Create CharacterList page component
    - Route: /characters
    - Display all Uma Musume characters
    - Show name, team, rarity, base stats
    - _Requirements: 11.1, 11.2, 11.3_

  - [ ] 28.2 Implement character filtering
    - Filter by name, team, rarity, specialty
    - Display character images
    - _Requirements: 11.4, 11.5_

  - [ ] 28.3 Implement character selection for plans
    - Auto-populate growth rates and aptitudes
    - _Requirements: 11.6_

- [ ] 29. Guide Page
  - [ ] 29.1 Create GuidePage component
    - Route: /guide
    - Display comprehensive usage instructions
    - Include sections for each major feature
    - _Requirements: 13.1, 13.2_

  - [ ] 29.2 Implement sticky navigation
    - Jump between sections
    - Accessible from main navigation
    - _Requirements: 13.3, 13.4_

- [ ] 30. Accessibility Compliance (WCAG AA)
  - [ ] 30.1 Implement WCAG Level AA requirements
    - Add alt text to all images (1.1.1)
    - Ensure color info available via text (1.4.1)
    - Add skip-to-main link (2.4.1)
    - Use semantic HTML elements (4.1.2)
    - Meet 4.5:1 contrast ratio for normal text (1.4.3)
    - Support 400% zoom reflow (1.4.10)
    - Ensure focus always visible (2.4.7)
    - _Requirements: 34.1, 34.3, 35.5, 37.3, 38.1, 70.1, 70.2_

  - [ ] 30.2 Implement keyboard navigation
    - Tab navigation through all elements
    - Keyboard shortcuts (N, Ctrl+S, Escape, ?)
    - Focus management for modals
    - _Requirements: 29.1, 29.2, 29.3, 29.4, 35.1, 41.1, 41.3, 41.4_

  - [ ] 30.3 Implement reduced motion support
    - Respect prefers-reduced-motion
    - Provide manual "Reduce Motion" toggle
    - Disable animations on CircularProgress and StatChart when active
    - Provide tabular "Data View" alternative for charts
    - _Requirements: 70.3, 70.4, 70.5, 70.6_

  - [ ] 30.4 Implement accessible forms
    - Associate labels with inputs
    - Group related controls with fieldset
    - Announce validation errors to screen readers
    - _Requirements: 39.1, 39.2, 39.4_

  - [ ] 30.5 Implement accessible data tables
    - Use proper table markup with headers
    - Add captions/aria-labels
    - _Requirements: 40.1, 40.2, 40.3_

- [ ] 31. Responsive Design
  - [ ] 31.1 Implement mobile layout
    - Single-column design below 768px
    - Hamburger menu navigation
    - Touch-friendly targets (44px minimum)
    - _Requirements: 14.1, 14.2, 14.4, 52.1_

  - [ ] 31.2 Implement responsive form tabs
    - Horizontal scrolling on mobile
    - Maintain usability 320px-2560px
    - _Requirements: 14.3, 14.5_

- [ ] 32. Notification System
  - [ ] 32.1 Create Toast component
    - Success/error/warning variants
    - Auto-dismiss after 5 seconds
    - Dismissible via close button
    - Stack multiple notifications
    - _Requirements: 30.1, 30.2, 30.3, 30.4, 30.5_

- [ ] 33. Data-testid Attributes
  - [ ] 33.1 Add data-testid to all interactive elements
    - Buttons, inputs, links, modals
    - Use consistent naming: `data-testid="[component]-[action]-[context]"`
    - _Requirements: 79.1, 79.2, 79.3_

  - [ ] 33.2 Add data-testid to dynamic elements
    - Plan cards with IDs: `plan-card-{id}`
    - Skill rows with indices: `skill-row-{index}`
    - Document all data-testid values in test selector reference
    - _Requirements: 79.4, 79.5, 79.6_

- [ ] 34. E2E Test Suite
  - [ ] 34.1 Create Playwright test setup
    - Configure test environment
    - Create seed data generator
    - _Requirements: 73.3, 73.4_

  - [ ] 34.2 Write critical flow tests
    - Dashboard load
    - Plan create (Local and Account modes)
    - Plan view (both /plans/{id} and /plans/local/{uuid})
    - Plan edit
    - Plan delete
    - Export
    - _Requirements: 73.1_

  - [ ] 34.3 Write accessibility tests
    - Run axe-core on all pages
    - Verify WCAG AA compliance
    - _Requirements: 73.5_

- [ ] 35. Final Checkpoint
  - Ensure all tests pass
  - Verify accessibility compliance (WCAG AA)
  - Review all storage mode functionality
  - Ask the user if questions arise

---

## Post-MVP Tasks (Requirements 80-87)

These tasks cover P2/P3 requirements and are tracked separately from the MVP scope.

- [ ] PM-1. User Authentication (Optional Sign-In) [P2]
  - Implement optional email/password authentication
  - Add "Claim Plans" flow for converting Local_Runs on sign-in
  - Display auth status in navbar
  - _Requirements: 80.1-80.8_

- [ ] PM-2. Cloud Sync and Conflict Resolution [P3]
  - Implement optimistic locking for Account_Runs
  - Add conflict resolution modal with side-by-side diff
  - Offer "Keep Mine", "Keep Server", "Merge" options
  - _Requirements: 81.1-81.8_

- [ ] PM-3. Scheduled Backups and Full Dataset Export [P3]
  - Implement "Backup All Data" action
  - Add backup manifest with timestamp and schema version
  - Optional scheduled backups for authenticated users
  - _Requirements: 82.1-82.7_

- [ ] PM-4. Skill Presets [P2]
  - Create predefined skill presets (Mile Core, Long Distance Core, etc.)
  - Allow creating custom presets from current plan
  - Support import/export of presets as JSON
  - _Requirements: 83.1-83.7_

- [ ] PM-5. Visual Regression Testing [P1]
  - Add Playwright visual comparison tests
  - Capture baselines for light/dark modes
  - Capture baselines for mobile/tablet/desktop viewports
  - _Requirements: 84.1-84.7_

- [ ] PM-6. Event Logging and Metrics [P2]
  - Log key events (plan_created, plan_updated, etc.)
  - Track performance metrics
  - Implement client-side error boundary
  - _Requirements: 85.1-85.7_

- [ ] PM-7. Seed Data and Demo Mode [P1]
  - Implement "Load Demo Data" action
  - Create 3-5 sample plans at different career stages
  - Mark demo plans with "Demo" badge
  - _Requirements: 86.1-86.7_

- [ ] PM-8. Game Mode Support [P2]
  - Add game mode selector (Career, Champions Meeting)
  - Adjust stat thresholds based on game mode
  - Display game mode badge on plan cards
  - _Requirements: 87.1-87.7_

---

## Notes

- Tasks marked with `*` are optional invariant/property tests that can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Invariant tests use Vitest with fast-check (JS) or Pest with datasets (PHP)
- Unit tests validate specific examples and edge cases
- Route strategy: Account runs at `/plans/{id}`, Local runs at `/plans/local/{uuid}`
- Save behavior: Local_Runs auto-save after 2s inactivity; Account_Runs save on blur/change for quick fields, explicit Save for multi-field edits
