# Requirements Document

## Introduction

This document outlines the functional and non-functional requirements for the Uma Musume Planner backend system. The backend provides RESTful APIs, data management, and business logic for tracking Uma Musume training runs.

**Schema Authority**: This specification follows the canonical domain model defined in the Dxx documents (D03, D04, D09) and consolidation spec. The database uses canonical table names while the API provides user-friendly endpoints.

## Glossary

- **System**: The Uma Musume Planner backend API
- **Plan**: A training run for an Uma Musume character (database table: `career_runs`, API: `/plans`)
- **Turn**: A single turn's stat progression data (database table: `stat_progress`)
- **Skill**: A skill entry in a plan (database table: `skill_career_runs`)
- **User**: An authenticated user of the system
- **Character**: An Uma Musume character (database table: `uma_musumes`)
- **Snapshot**: An immutable capture of a plan's state at a specific point
- **Activity_Log**: A record of user actions for audit purposes (database table: `activity_logs`)

## Requirements

### Requirement 1: Database Schema Implementation

**User Story:** As a developer, I want a properly structured database schema, so that I can store and retrieve Uma Musume training data efficiently.

#### Acceptance Criteria

1. THE System SHALL use the canonical `career_runs` table as the primary entity for training runs
2. THE System SHALL maintain foreign key relationships between `career_runs` and related tables (`skill_career_runs`, `stat_progress`, `goals`, etc.)
3. THE System SHALL support soft deletion for plans using `deleted_at` timestamp
4. THE System SHALL enforce referential integrity through foreign key constraints
5. THE System SHALL use appropriate indexes for query performance

### Requirement 2: Plan Management API

**User Story:** As a user, I want to manage my training plans through an API, so that I can create, read, update, and delete my plans programmatically.

#### Acceptance Criteria

1. WHEN a user requests their plans, THE System SHALL return only plans belonging to that user
2. WHEN a user creates a plan, THE System SHALL validate the input and return the created plan with a 201 status
3. WHEN a user requests a specific plan, THE System SHALL return the plan with all related data if the user owns it
4. WHEN a user updates a plan, THE System SHALL validate changes and return the updated plan
5. WHEN a user deletes a plan, THE System SHALL soft-delete the plan and return a 204 status
6. WHEN a user attempts to access another user's plan, THE System SHALL return a 403 Forbidden response
7. THE System SHALL support pagination for plan listings
8. THE System SHALL require authentication for all plan operations
9. THE System SHALL validate user authorization for each plan operation
10. THE System SHALL return appropriate HTTP status codes and error messages

### Requirement 3: Plan Service Layer

**User Story:** As a developer, I want a service layer for plan operations, so that business logic is centralized and reusable.

#### Acceptance Criteria

1. WHEN creating a plan, THE System SHALL handle related entities (attributes, skills, goals) in a single transaction
2. WHEN updating a plan, THE System SHALL update related entities and maintain data consistency
3. WHEN deleting a plan, THE System SHALL cascade soft-delete to related entities
4. WHEN plan operations occur, THE System SHALL dispatch appropriate events for cache invalidation
5. WHEN plan operations occur, THE System SHALL dispatch appropriate events for activity logging
6. THE System SHALL support filtering plans by status, character, and date range
7. THE System SHALL support sorting plans by various criteria

### Requirement 4: Skill Search API

**User Story:** As a user, I want to search for skills by name, so that I can quickly find and add skills to my plans.

#### Acceptance Criteria

1. THE System SHALL provide a skill search endpoint at `/api/v1/autosuggest/skills`
2. WHEN searching skills, THE System SHALL match partial names in both English and Japanese
3. WHEN searching skills, THE System SHALL return case-insensitive matches
4. WHEN searching skills, THE System SHALL limit results to 20 items maximum
5. THE System SHALL implement rate limiting of 60 requests per minute per user
6. THE System SHALL cache search results for performance

### Requirement 5: Character Search API

**User Story:** As a user, I want to search for Uma Musume characters, so that I can associate them with my training plans.

#### Acceptance Criteria

1. THE System SHALL provide a character search endpoint at `/api/v1/autosuggest/characters`
2. WHEN searching characters, THE System SHALL match partial names case-insensitively
3. WHEN searching characters, THE System SHALL limit results to 20 items maximum

### Requirement 6: Export Functionality

**User Story:** As a user, I want to export my training plans, so that I can backup my data or share it with others.

#### Acceptance Criteria

1. THE System SHALL support exporting plans to Excel format
2. THE System SHALL support exporting plans to CSV format
3. THE System SHALL support exporting plans to Markdown format
4. THE System SHALL include a schema version in JSON exports
5. THE System SHALL support bulk export of multiple plans
6. THE System SHALL include all related data (skills, turns, goals) in exports
7. THE System SHALL support exporting individual plans via API endpoints

### Requirement 7: Import Functionality

**User Story:** As a user, I want to import training plans from files, so that I can restore backups or migrate data.

#### Acceptance Criteria

1. THE System SHALL detect file format automatically (JSON, CSV)
2. THE System SHALL support importing plans from JSON format
3. THE System SHALL support importing plans from CSV format
4. THE System SHALL provide dry-run validation without persisting data
5. THE System SHALL execute imports within database transactions for atomicity
6. THE System SHALL detect duplicate plans based on title, character, and created date
7. THE System SHALL generate error reports for failed imports
8. THE System SHALL return accurate counts of created, updated, and skipped records

### Requirement 8: Cache Management

**User Story:** As a system administrator, I want efficient cache management, so that the system performs well under load.

#### Acceptance Criteria

1. WHEN plans are created or updated, THE System SHALL invalidate related caches
2. THE System SHALL cache search results with appropriate TTL
3. THE System SHALL provide cache clearing methods for plan-related data
4. THE System SHALL cache user plan lists for performance
5. THE System SHALL use event-driven cache invalidation

### Requirement 9: Authentication and Authorization

**User Story:** As a user, I want secure access to my data, so that my training plans remain private.

#### Acceptance Criteria

1. THE System SHALL support Sanctum token-based authentication
2. THE System SHALL support session-based authentication
3. THE System SHALL enforce user data isolation (users can only access their own plans)
4. THE System SHALL implement role-based access control if admin features are needed
5. THE System SHALL return 401 for unauthenticated requests to protected endpoints

### Requirement 10: Activity Logging

**User Story:** As a user, I want to see a history of my actions, so that I can track changes to my plans.

#### Acceptance Criteria

1. WHEN plans are created, THE System SHALL log the action with user and timestamp
2. WHEN plans are updated, THE System SHALL log the action with changed fields
3. WHEN plans are deleted, THE System SHALL log the action
4. THE System SHALL scope activity logs to the authenticated user
5. THE System SHALL support filtering activity logs by action type and date range

### Requirement 11: Stat Progress Management

**User Story:** As a user, I want to track stat progression over turns, so that I can monitor my character's growth.

#### Acceptance Criteria

1. THE System SHALL store stat values for each turn in the `stat_progress` table
2. THE System SHALL validate stat values are within 0-1200 range (hard maximum)
3. THE System SHALL validate stat values are non-negative integers
4. THE System SHALL support bulk entry of stat data
5. THE System SHALL return stats ordered by turn number
6. THE System SHALL enforce unique constraint on career_run_id + turn_number

### Requirement 12: Skill Management

**User Story:** As a user, I want to manage skills in my plans, so that I can track which skills I've acquired or plan to acquire.

#### Acceptance Criteria

1. WHEN a skill status is 'acquired', THE System SHALL require a turn_acquired value
2. THE System SHALL allow null turn_acquired for 'skipped' and 'suggested' skills
3. THE System SHALL calculate total SP spent from acquired skills
4. THE System SHALL calculate suggested SP budget from suggested skills
5. THE System SHALL support filtering skills by status

### Requirement 13: Image Management

**User Story:** As a user, I want to upload character images, so that I can personalize my training plans.

#### Acceptance Criteria

1. THE System SHALL accept only jpg, png, and webp image formats
2. THE System SHALL enforce a maximum file size of 2MB
3. THE System SHALL verify MIME type matches file content
4. THE System SHALL strip EXIF metadata for privacy
5. THE System SHALL generate thumbnails for list views

### Requirement 14: Snapshot Management

**User Story:** As a user, I want to capture snapshots of my training state, so that I can preserve important milestones.

#### Acceptance Criteria

1. THE System SHALL capture complete plan state in snapshots
2. THE System SHALL prevent modification of existing snapshots
3. THE System SHALL support creating snapshots via API
4. THE System SHALL support deleting snapshots via API
5. THE System SHALL return snapshots ordered by turn number and timestamp
6. THE System SHALL include snapshots in plan exports

### Requirement 15: API Response Standards

**User Story:** As a developer, I want consistent API responses, so that I can reliably integrate with the backend.

#### Acceptance Criteria

1. THE System SHALL return JSON responses with appropriate Content-Type headers
2. THE System SHALL include pagination metadata in list responses
3. THE System SHALL use consistent error response format with message and errors fields
4. THE System SHALL return appropriate HTTP status codes for all operations
5. THE System SHALL include proper headers for CORS and content negotiation

### Requirement 16: Local to Account Conversion

**User Story:** As a user, I want to convert my local plans to account plans, so that I can access them across devices.

#### Acceptance Criteria

1. THE System SHALL accept local plan data and create database records
2. THE System SHALL preserve original timestamps and turn ordering during conversion
3. THE System SHALL detect duplicates during conversion
4. THE System SHALL support bulk conversion of multiple plans
5. THE System SHALL use database transactions for conversion operations

### Requirement 17: Performance Requirements

**User Story:** As a user, I want fast response times, so that the application feels responsive.

#### Acceptance Criteria

1. THE System SHALL respond to autosuggest queries within 200ms
2. THE System SHALL handle plan exports efficiently for large datasets
3. THE System SHALL support pagination for large result sets
4. THE System SHALL use appropriate database indexes for query optimization
5. THE System SHALL implement caching for frequently accessed data

### Requirement 18: Data Validation

**User Story:** As a user, I want proper data validation, so that my data remains consistent and valid.

#### Acceptance Criteria

1. THE System SHALL validate all input data according to defined rules
2. THE System SHALL return detailed validation errors for invalid input
3. THE System SHALL enforce business rules (e.g., turn_acquired required for acquired skills)
4. THE System SHALL validate foreign key relationships
5. THE System SHALL prevent data corruption through proper validation
