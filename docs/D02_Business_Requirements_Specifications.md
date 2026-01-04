# D02 - Business Requirements Specifications

## Uma Musume Career Planner

**Document Version:** 2.0  
**Date:** 2026-01-03  
**Status:** Active

---

## 1. Introduction

### 1.1 Purpose

This document defines the business requirements for the Uma Musume Career Planner application. It establishes the business context, stakeholder needs, and high-level requirements that will guide the technical implementation.

### 1.2 Scope

The Uma Musume Career Planner is a web application that enables players of Uma Musume: Pretty Derby to track, manage, and analyze their career progression. The system consolidates features from six legacy applications into a unified platform.

### 1.3 Definitions and Acronyms

| Term              | Definition                                                                  |
| ----------------- | --------------------------------------------------------------------------- |
| Plan/Career Run   | A career run record tracking an Uma Musume character's training progression |
| Uma Musume        | A horse girl character from the Uma Musume: Pretty Derby game               |
| SP (Skill Points) | Points earned from races and events, spent to purchase skills               |
| Stat Max          | Maximum stat value (1200) - hard cap, no values above allowed               |
| URA Finale        | The final race series at the end of Senior Year                             |

---

## 2. Business Context

### 2.1 Business Problem

Players of Uma Musume: Pretty Derby currently lack a comprehensive, unified tool to:

- Track character training progression across multiple career runs
- Plan skill acquisitions and race strategies
- Analyze training effectiveness and outcomes
- Export and share training records
- Access data across devices or offline

### 2.2 Business Opportunity

By consolidating five legacy tracking applications into one modern platform, we can:

- Provide a superior user experience with modern web technologies
- Enable offline-first usage for players without reliable connectivity
- Support cross-device access for authenticated users
- Improve accessibility for users with disabilities
- Reduce maintenance overhead of multiple codebases

### 2.3 Business Objectives

| Objective      | Success Metric                   |
| -------------- | -------------------------------- |
| User Adoption  | Active users tracking plans      |
| Data Migration | Successful import of legacy data |
| Accessibility  | WCAG AA compliance               |
| Performance    | Page load < 2 seconds            |
| Reliability    | 99% uptime for Account mode      |

### 2.4 Value Proposition (Mermaid)

````mermaid
mindmap
  root((Uma Musume Career Planner))
    Unified Platform
      Consolidate 5 legacy apps
      Single codebase
      Consistent UX
    Dual Storage
      Local Mode offline
      Account Mode sync
      Convert between modes
    Accessibility
      WCAG AA compliant
      Keyboard navigation
      Screen reader support
    Data Portability
      JSON export/import
      Excel export
      Markdown export
```text

---

## 3. Stakeholder Analysis

### 3.1 Primary Stakeholders

#### 3.1.1 Players (End Users)

**Needs:**

- Quick and easy plan creation
- Offline access to data
- Cross-device synchronization
- Data export for sharing/backup
- Accessible interface

**Pain Points:**

- Current tools are fragmented
- No offline capability
- Poor mobile experience
- Data locked in single application

#### 3.1.2 Developers/Maintainers

**Needs:**

- Single codebase to maintain
- Modern, well-documented architecture
- Comprehensive test coverage
- Clear development standards

### 3.2 Stakeholder Map (Mermaid)

```mermaid
quadrantChart
    title Stakeholder Influence vs Interest
    x-axis Low Interest --> High Interest
    y-axis Low Influence --> High Influence
    quadrant-1 Keep Satisfied
    quadrant-2 Manage Closely
    quadrant-3 Monitor
    quadrant-4 Keep Informed
    Players: [0.9, 0.7]
    Developers: [0.8, 0.9]
    Content Creators: [0.6, 0.3]
    Community: [0.5, 0.4]
```text

---

## 4. Business Requirements

### 4.1 Character Management [BR-1]

**Business Need:** Players need to manage their Uma Musume character roster with complete information.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-1.1 | Create, view, update, and delete character records | P0 |
| BR-1.2 | Store character images with visual preview | P1 |
| BR-1.3 | Track aptitude grades for terrain, distance, and style | P0 |
| BR-1.4 | Track growth rate bonuses for all five stats | P0 |

### 4.2 Career Run Management [BR-2]

**Business Need:** Players need to track individual training runs from start to completion.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-2.1 | Create career runs linked to characters | P0 |
| BR-2.2 | Track career year (Junior, Classic, Senior) | P0 |
| BR-2.3 | Track run status (In Progress, Completed, Archived) | P0 |
| BR-2.4 | Track current turn, SP available, stamina percentage | P0 |
| BR-2.5 | Support quick creation with minimal input | P0 |

### 4.3 Stat Progression Tracking [BR-3]

**Business Need:** Players need to log and visualize stat changes throughout a career run.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-3.1 | Log turn-by-turn stats (Speed, Stamina, Power, Guts, Wit) | P0 |
| BR-3.2 | Visualize stat progression with charts | P1 |
| BR-3.3 | Validate stats within 0-1200 range (hard max) | P0 |
| BR-3.4 | Calculate stat totals and growth rates | P0 |

### 4.4 Skill Management [BR-4]

**Business Need:** Players need to plan and track skill acquisitions.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-4.1 | Search skills from reference database | P0 |
| BR-4.2 | Track skill status (Acquired, Skipped, Suggested) | P0 |
| BR-4.3 | Track turn when skill was acquired | P0 |
| BR-4.4 | Calculate SP totals for acquired and planned skills | P0 |
| BR-4.5 | Support both English and Japanese skill names | P0 |

### 4.5 Race Planning [BR-5]

**Business Need:** Players need to plan race schedules and track results.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-5.1 | Record race predictions with venue and distance | P1 |
| BR-5.2 | Track predicted vs actual placement | P1 |
| BR-5.3 | Capture race-day snapshots of character state | P2 |
| BR-5.4 | Display recommended stamina thresholds | P1 |

### 4.6 Goals Management [BR-6]

**Business Need:** Players need to set and track training objectives.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-6.1 | Create goals with descriptions | P1 |
| BR-6.2 | Track goal completion status | P1 |
| BR-6.3 | Visual distinction for completed goals | P1 |

### 4.7 Data Export [BR-7]

**Business Need:** Players need to export data for backup and sharing.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-7.1 | Export to JSON format | P0 |
| BR-7.2 | Export to Excel format (.xlsx) | P1 |
| BR-7.3 | Export to plain text/Markdown | P1 |
| BR-7.4 | Preview export before download | P1 |
| BR-7.5 | Copy to clipboard functionality | P1 |
| BR-7.6 | Include schema version for compatibility | P1 |

### 4.8 Data Import [BR-8]

**Business Need:** Players need to import data from legacy systems or backups.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-8.1 | Import from JSON format | P1 |
| BR-8.2 | Detect and migrate schema versions | P1 |
| BR-8.3 | Preview data before import | P1 |
| BR-8.4 | Handle duplicate detection | P1 |
| BR-8.5 | Support import to Local or Account storage | P1 |

### 4.9 Dual Storage Mode [BR-9]

**Business Need:** Players need flexibility in how their data is stored and accessed.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-9.1 | Local storage mode (browser localStorage) | P0 |
| BR-9.2 | Account storage mode (database) | P0 |
| BR-9.3 | Clear visual indication of storage mode | P0 |
| BR-9.4 | Full offline functionality for Local runs | P0 |
| BR-9.5 | Convert Local runs to Account runs | P0 |
| BR-9.6 | Local data management interface | P1 |

### 4.10 User Interface [BR-10]

**Business Need:** Players need an intuitive, accessible interface.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-10.1 | Dark/Light mode toggle with persistence | P0 |
| BR-10.2 | Responsive design (320px to 2560px) | P0 |
| BR-10.3 | WCAG AA accessibility compliance | P0 |
| BR-10.4 | Keyboard navigation support | P1 |
| BR-10.5 | Reduced motion support | P1 |

### 4.11 Authentication (Optional) [BR-11]

**Business Need:** Some players want cross-device access and cloud backup.

| ID | Requirement | Priority |
|----|-------------|----------|
| BR-11.1 | Optional sign-in for Account mode | P2 |
| BR-11.2 | Full functionality without authentication | P0 |
| BR-11.3 | "Claim Plans" flow for Local to Account conversion | P2 |

### 4.12 Requirements Priority Matrix (Mermaid)

```mermaid
pie title Requirements by Priority
    "P0 - Critical" : 25
    "P1 - High" : 18
    "P2 - Medium" : 8
    "P3 - Low" : 5
````

---

## 5. Business Rules

### 5.1 Data Validation Rules

| Rule ID | Rule Description                                     |
| ------- | ---------------------------------------------------- |
| BV-1    | Plan title is required and cannot be empty           |
| BV-2    | Stat values must be between 1 and 2000               |
| BV-3    | Turn numbers must be between 1 and 78                |
| BV-4    | Skill status "Acquired" requires turn_acquired value |
| BV-5    | Energy level must be between 0 and 100               |

### 5.2 Calculation Rules

| Rule ID | Rule Description                                                                          |
| ------- | ----------------------------------------------------------------------------------------- |
| BC-1    | Effective stat = raw if ≤1200, else 1200 + (raw-1200)\*0.5                                |
| BC-2    | Acquired SP = sum of sp_cost where status = acquired                                      |
| BC-3    | Mood modifiers: Great +4%, Good +2%, Normal 0%, Bad -2%, Awful -4%                        |
| BC-4    | Aptitude effectiveness: SS=120%, S=110%, A=100%, B=90%, C=80%, D=70%, E=60%, F=50%, G=40% |

### 5.3 Storage Rules

| Rule ID | Rule Description                                                   |
| ------- | ------------------------------------------------------------------ |
| BS-1    | Local runs use UUID identifiers                                    |
| BS-2    | Account runs use database integer IDs                              |
| BS-3    | Local runs are fully functional offline                            |
| BS-4    | Account runs require network connectivity to save                  |
| BS-5    | Drafts are always saved to localStorage regardless of storage mode |

---

## 6. Business Process Flows

### 6.1 Plan Creation Flow (ASCII)

```text
User clicks "Create Plan"
    │
    ▼
Quick Create Modal opens
    │
    ▼
User enters: Title, Character, Storage Mode
    │
    ▼
├─ If Local: Generate UUID, save to localStorage
│      └─ Navigate to /plans/local/{uuid}/edit
│
└─ If Account: POST to server, save to database
       └─ Navigate to /plans/{id}/edit
```

### 6.2 Plan Creation Flow (Mermaid)

````mermaid
flowchart TD
    A[User clicks Create Plan] --> B[Quick Create Modal Opens]
    B --> C[User enters Title, Character, Storage Mode]
    C --> D{Storage Mode?}
    D -->|Local| E[Generate UUID]
    E --> F[Save to localStorage]
    F --> G[Navigate to /plans/local/uuid/edit]
    D -->|Account| H[POST to Server]
    H --> I[Save to Database]
    I --> J[Navigate to /plans/id/edit]
```text

### 6.3 Local to Account Conversion Flow (ASCII)

```text

User logs in with existing Local runs
    │
    ▼
"Claim Plans" modal appears
    │
    ▼
User selects plans to convert
    │
    ▼
For each plan:
    ├─ POST to database
    ├─ If success: Delete local (or keep if checkbox selected)
    └─ Update UI
    │
    ▼
Show results report

````

### 6.4 Local to Account Conversion Flow (Mermaid)

````mermaid
flowchart TD
    A[User logs in] --> B{Has Local runs?}
    B -->|Yes| C[Show Claim Plans modal]
    B -->|No| D[Continue to Dashboard]
    C --> E[User selects plans to convert]
    E --> F[For each selected plan]
    F --> G[POST to database]
    G --> H{Success?}
    H -->|Yes| I{Keep local copy?}
    I -->|No| J[Delete from localStorage]
    I -->|Yes| K[Keep in localStorage]
    H -->|No| L[Log error]
    J --> M[Update UI]
    K --> M
    L --> M
    M --> N[Show results report]
```text

### 6.5 Data Export Flow (Mermaid)

```mermaid
flowchart TD
    A[User clicks Export] --> B[Export Preview Modal Opens]
    B --> C[User selects format]
    C --> D{Action?}
    D -->|Download| E[Generate file]
    E --> F[Trigger browser download]
    D -->|Copy| G[Copy to clipboard]
    G --> H[Show toast notification]
```text

---

## 7. Success Metrics

### 7.1 User Experience Metrics

| Metric | Target | Measurement Method |
|--------|--------|-------------------|
| Time to create first plan | < 30 seconds | User testing |
| Task completion rate | > 95% | Analytics |
| User satisfaction | > 4/5 stars | Surveys |

### 7.2 Technical Metrics

| Metric | Target | Measurement Method |
|--------|--------|-------------------|
| Page load time | < 2 seconds | Performance monitoring |
| First Contentful Paint | < 1.5 seconds | Lighthouse |
| Accessibility score | 100% AA | axe-core |
| Error rate | < 1% | Error logging |

### 7.3 Adoption Metrics

| Metric | Target | Measurement Method |
|--------|--------|-------------------|
| Successful data imports | > 90% | Import logs |
| Local to Account conversions | Track adoption | Analytics |
| Feature utilization | Monitor usage | Analytics |

---

## 8. Constraints and Assumptions

### 8.1 Constraints

1. **Browser Storage Limits**: localStorage typically limited to 5-10MB
2. **Offline Limitations**: Account runs cannot be saved without connectivity
3. **Browser Support**: Modern browsers only (Chrome, Firefox, Safari, Edge)
4. **No Native Apps**: Web-only implementation for MVP

### 8.2 Assumptions

1. Users have access to modern web browsers
2. Users understand basic Uma Musume game mechanics
3. Users have sufficient browser storage for Local runs
4. English is the primary interface language (Japanese skill names supported)

---

## 9. Dependencies

### 9.1 External Dependencies

| Dependency | Description | Risk Level |
|------------|-------------|------------|
| Browser localStorage API | Local run storage | Low |
| Livewire connection | Account run operations | Medium |
| Database availability | Account data persistence | Medium |

### 9.2 Internal Dependencies

| Dependency | Description |
|------------|-------------|
| Character data | Required before creating career runs |
| Skill reference database | Required for skill autocomplete |
| Authentication (optional) | Required for Account mode |

### 9.3 Dependency Graph (Mermaid)

```mermaid
flowchart BT
    A[Character Data] --> B[Career Run]
    C[Skill Database] --> D[Skill Management]
    D --> B
    E[Authentication] -.->|Optional| F[Account Mode]
    F --> B
    G[localStorage] --> H[Local Mode]
    H --> B
```text

---

## 10. Appendices

### 10.1 Glossary of Game Terms

| Term | Japanese | Definition |
|------|----------|------------|
| Speed | スピード | Determines maximum running speed |
| Stamina | スタミナ | Determines HP/effective stamina |
| Power | パワー | Affects acceleration and lane-changing |
| Guts | 根性 | Affects last spurt and stamina consumption |
| Wit | 賢さ | Affects skill activation rate |
| Nige | 逃げ | Front Runner running style |
| Senkou | 先行 | Pace Chaser running style |
| Sashi | 差し | Late Surger running style |
| Oikomi | 追込 | End Closer running style |

### 10.2 Canonical Field Names

| UI Label | Canonical Field | Notes |
|----------|-----------------|-------|
| SP Balance | `total_sp_available` | Use canonical in code |
| Stamina % | `stamina_percentage` | |
| Turn | `turn_number` | In StatProgress table |
| Current Turn | `current_turn` | In CareerRun table |

### 10.3 Related Documents

- D01_System_Development_Plan
- D03_System_Requirements_Specifications
- D04_System_Design_Specifications

### 10.4 Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-01-03 | System | Initial draft |
| 2.0 | 2026-01-03 | System | Added Mermaid diagrams, updated structure |
````
