# Implementation Plan: Uma Musume Planner Backend

## Overview

This implementation plan follows the canonical domain model defined in the Dxx documents (D03, D04, D09) and consolidation spec. The backend provides RESTful APIs with user-friendly endpoints while using canonical database table names.

**Canonical Database Schema**: The implementation uses canonical table names (`career_runs`, `skill_career_runs`, `stat_progress`, `activity_logs`) as defined in D04 System Design Specifications, with Laravel models providing the API mapping.

## Tasks

- [x]   1. Set up project structure and core interfaces
    - [x] 1.1 Create service provider registration for services
        - Register PlanService, CacheService, ExportService, ImportService in AppServiceProvider
        - _Requirements: 3.1, 8.1_
    - [x] 1.2 Create API routes for v1 endpoints
        - Define routes in `routes/api.php` with `api/v1` prefix
        - Apply auth:sanctum middleware to protected routes
        - _Requirements: 2.1, 2.2, 2.4, 2.5, 2.6_
    - [x] 1.3 Set up property-based testing framework
        - Install Eris or PHPUnit Quickcheck via Composer
        - Create base test traits for property testing
        - _Requirements: Testing Strategy_

- [x]   2. Implement Plan API endpoints
    - [x] 2.1 Create StorePlanRequest and UpdatePlanRequest form requests
        - Implement validation rules per design document
        - _Requirements: 2.4, 2.5, 18.1_
    - [x] 2.2 Implement PlanController index action
        - Return paginated plans for authenticated user
        - Support filtering by status, character, date range
        - _Requirements: 2.1, 3.6, 3.7_
    - [x] 2.3 Implement PlanController show action
        - Return plan with eager-loaded relations
        - Support `include` parameter for selective loading
        - _Requirements: 2.2, 2.3, 15.6_
    - [x] 2.4 Implement PlanController store action
        - Create plan with related entities via PlanService
        - Return 201 with created resource
        - _Requirements: 2.4, 3.1_
    - [x] 2.5 Implement PlanController update action
        - Update plan and relations via PlanService
        - Return updated resource
        - _Requirements: 2.5, 3.2_
    - [x] 2.6 Implement PlanController destroy action
        - Soft delete plan via PlanService
        - Return 204 No Content
        - _Requirements: 2.6, 3.3_
    - [ ]\* 2.7 Write property test for User Data Isolation
        - **Property 1: User Data Isolation**
        - **Validates: Requirements 2.1, 2.6, 9.3**
    - [ ]\* 2.8 Write property test for Plan CRUD Round-Trip
        - **Property 2: Plan CRUD Round-Trip**
        - **Validates: Requirements 2.2, 2.4, 2.5, 3.1**

- [x]   3. Implement PlanService business logic
    - [x] 3.1 Implement PlanService create method
        - Handle plan creation with attributes, skills, goals in transaction
        - Dispatch PlanCreated event
        - _Requirements: FR-BE-3.1, FR-BE-3.4_
    - [x] 3.2 Implement PlanService update method
        - Handle plan updates with related entities
        - Dispatch PlanUpdated event
        - _Requirements: FR-BE-3.2, FR-BE-3.5_
    - [x] 3.3 Implement PlanService delete method
        - Cascade soft-delete to related entities
        - _Requirements: FR-BE-3.3_
    - [x] 3.4 Implement PlanService filtering and sorting
        - Support status, character, date range filters
        - Support sorting by created_at, updated_at, title
        - _Requirements: FR-BE-3.6, FR-BE-3.7_
    - [ ]\* 3.5 Write property test for Soft Delete Behavior
        - **Property 3: Soft Delete Behavior**
        - **Validates: FR-BE-1.3, FR-BE-2.7, FR-BE-3.3**

- [x]   4. Checkpoint - Ensure Plan API tests pass
    - Ensure all tests pass, ask the user if questions arise.

- [x]   5. Implement PlanPolicy authorization
    - [x] 5.1 Create PlanPolicy with view, update, delete methods
        - Ensure users can only access their own plans
        - _Requirements: FR-BE-9.3_
    - [x] 5.2 Register policy in AuthServiceProvider
        - Map Plan model to PlanPolicy
        - _Requirements: FR-BE-9.3_
    - [x] 5.3 Apply policy checks in PlanController
        - Use `authorize()` method in controller actions
        - _Requirements: FR-BE-2.8, FR-BE-2.9_
    - [ ]\* 5.4 Write property test for Authentication Enforcement
        - **Property 16: Authentication Enforcement**
        - **Validates: FR-BE-2.8, FR-BE-2.10**

- [x]   6. Implement Events and Cache Management
    - [x] 6.1 Create PlanCreated and PlanUpdated events
        - Implement ShouldBroadcast interface
        - _Requirements: FR-BE-3.4, FR-BE-3.5_
    - [x] 6.2 Implement CacheService
        - Methods for clearing plan cache and user plan list cache
        - Methods for caching and retrieving search results
        - _Requirements: FR-BE-8.1, FR-BE-8.3, FR-BE-8.4_
    - [x] 6.3 Create ClearPlanCache listener
        - Listen to PlanCreated and PlanUpdated events
        - Call CacheService to invalidate caches
        - _Requirements: FR-BE-8.5_
    - [x] 6.4 Register events and listeners in EventServiceProvider
        - Map events to listeners
        - _Requirements: FR-BE-8.1_
    - [ ]\* 6.5 Write property test for Event-Driven Cache Invalidation
        - **Property 4: Event-Driven Cache Invalidation**
        - **Validates: FR-BE-3.4, FR-BE-3.5, FR-BE-8.1**

- [x]   7. Implement Autosuggest API
    - [x] 7.1 Create AutosuggestController
        - Implement skills and characters endpoints
        - _Requirements: FR-BE-4.1, FR-BE-5.1_
    - [x] 7.2 Implement skill search with caching
        - Partial, case-insensitive matching on EN and JP names
        - Cache results with 5-minute TTL
        - Limit to 20 results
        - _Requirements: FR-BE-4.2, FR-BE-4.3, FR-BE-4.4, FR-BE-4.6_
    - [x] 7.3 Implement character search with caching
        - Partial, case-insensitive matching
        - Limit to 20 results
        - _Requirements: FR-BE-5.2, FR-BE-5.3_
    - [x] 7.4 Add rate limiting middleware
        - 60 requests per minute per user
        - _Requirements: FR-BE-4.5_
    - [ ]\* 7.5 Write property test for Search Behavior
        - **Property 5: Search Behavior**
        - **Validates: FR-BE-4.2, FR-BE-4.3, FR-BE-4.4, FR-BE-5.2, FR-BE-5.3**

- [x]   8. Checkpoint - Ensure API and search tests pass
    - Ensure all tests pass, ask the user if questions arise.

- [x]   9. Implement Skill Management
    - [x] 9.1 Update Skill model with status validation
        - Enforce turn_acquired required when status is acquired
        - Define status constants
        - _Requirements: FR-BE-12.1, FR-BE-12.2_
    - [x] 9.2 Implement SP calculation methods on Plan model
        - Calculate acquired SP total
        - Calculate suggested SP budget
        - _Requirements: FR-BE-12.3, FR-BE-12.4_
    - [x] 9.3 Add skill filtering to API
        - Support filtering by status
        - _Requirements: FR-BE-12.5_
    - [ ]\* 9.4 Write property test for Skill Status Validation
        - **Property 11: Skill Status Validation**
        - **Validates: FR-BE-12.1, FR-BE-12.2**
    - [ ]\* 9.5 Write property test for SP Calculation Accuracy
        - **Property 12: SP Calculation Accuracy**
        - **Validates: FR-BE-12.3, FR-BE-12.4**

- [x]   10. Implement Stat Progress Management
    - [x] 10.1 Update Turn model with validation
        - Validate stat values are within 0-1200 range (hard max)
        - _Requirements: FR-BE-11.3_
    - [x] 10.2 Add bulk stat entry endpoint
        - POST /api/v1/plans/{plan}/turns/bulk
        - _Requirements: FR-BE-11.5_
    - [x] 10.3 Ensure stats are ordered by turn_number
        - Add default scope or explicit ordering
        - _Requirements: FR-BE-11.6_
    - [x] 10.4 Implement stat validation
        - Values must be within 0-1200 range (hard max)
        - _Requirements: FR-BE-11.4_
    - [ ]\* 10.5 Write property test for Stat Value Validation
        - **Property 10: Stat Value Validation**
        - **Validates: FR-BE-11.3, FR-BE-11.4, FR-BE-11.6**

- [x]   11. Implement Activity Logging
    - [x] 11.1 Create ActivityLog model and migration (if not exists)
        - Fields: user_id, model_type, model_id, action, changes, created_at
        - _Requirements: FR-BE-10.1_
    - [x] 11.2 Create activity logging trait or observer
        - Log create, update, delete actions
        - Capture changed fields on update
        - _Requirements: FR-BE-10.1, FR-BE-10.2, FR-BE-10.3_
    - [x] 11.3 Add activity log scoping to user
        - Users can only see their own activity
        - _Requirements: FR-BE-10.4_
    - [x] 11.4 Add activity log filtering
        - Support filtering by action type and date range
        - _Requirements: FR-BE-10.5_
    - [ ]\* 11.5 Write property test for Activity Logging Completeness
        - **Property 9: Activity Logging Completeness**
        - **Validates: FR-BE-10.1, FR-BE-10.2, FR-BE-10.3**

- [x]   12. Checkpoint - Ensure skill, stat, and activity tests pass
    - Ensure all tests pass, ask the user if questions arise.

- [x]   13. Implement Export Service
    - [x] 13.1 Create ExportService class
        - Implement toJson method with schema_version
        - _Requirements: FR-BE-6.4_
    - [x] 13.2 Implement Excel export
        - Use Laravel Excel or PhpSpreadsheet
        - Define column schema
        - _Requirements: FR-BE-6.1_
    - [x] 13.3 Implement CSV export
        - UTF-8 encoding with proper quoting
        - _Requirements: FR-BE-6.2_
    - [x] 13.4 Implement Markdown export
        - Human-readable format
        - _Requirements: FR-BE-6.3_
    - [x] 13.5 Implement bulk export
        - Support exporting multiple plans
        - _Requirements: FR-BE-6.7_
    - [x] 13.6 Add export endpoints to API
        - GET /api/v1/plans/{plan}/export?format={json|xlsx|csv|md}
        - _Requirements: FR-BE-6.1, FR-BE-6.2, FR-BE-6.3_

- [x]   14. Implement Import Service
    - [x] 14.1 Create ImportService class
        - Implement format detection
        - _Requirements: FR-BE-7.1_
    - [x] 14.2 Implement JSON import
        - Parse and validate JSON structure
        - _Requirements: FR-BE-7.2_
    - [x] 14.3 Implement CSV import
        - Parse CSV with proper encoding handling
        - _Requirements: FR-BE-7.3_
    - [x] 14.4 Implement dry-run validation
        - Return row-level errors without persisting
        - _Requirements: FR-BE-7.4_
    - [x] 14.5 Implement duplicate detection
        - Check title, character, created date
        - _Requirements: FR-BE-7.6_
    - [x] 14.6 Implement import execution with transactions
        - Use DB::transaction for atomicity
        - Return counts of created/updated/skipped
        - _Requirements: FR-BE-7.5, FR-BE-7.8_
    - [x] 14.7 Implement error report generation
        - Generate CSV error report
        - _Requirements: FR-BE-7.7_
    - [x] 14.8 Add import endpoint to API
        - POST /api/v1/import
        - _Requirements: FR-BE-7.1_
    - [ ]\* 14.9 Write property test for Export/Import Round-Trip
        - **Property 6: Export/Import Round-Trip**
        - **Validates: FR-BE-6.1, FR-BE-6.2, FR-BE-6.4, FR-BE-7.2, FR-BE-7.3**
    - [ ]\* 14.10 Write property test for Import Validation
        - **Property 7: Import Validation**
        - **Validates: FR-BE-7.4, FR-BE-7.6, FR-BE-7.7, FR-BE-7.8**
    - [ ]\* 14.11 Write property test for Duplicate Detection
        - **Property 8: Duplicate Detection**
        - **Validates: FR-BE-7.6**

- [x]   15. Checkpoint - Ensure export/import tests pass
    - Ensure all tests pass, ask the user if questions arise.

- [x]   16. Implement Image Management
    - [x] 16.1 Create ImageService class
        - Implement file type validation (jpg, png, webp)
        - Implement file size validation (max 2MB)
        - _Requirements: FR-BE-15.1, FR-BE-15.2_
    - [x] 16.2 Implement MIME type verification
        - Content sniffing to verify actual file type
        - _Requirements: FR-BE-15.3_
    - [x] 16.3 Implement EXIF stripping
        - Remove metadata for privacy
        - _Requirements: FR-BE-15.4_
    - [x] 16.4 Implement thumbnail generation
        - Generate smaller variant for list views
        - _Requirements: FR-BE-15.5_
    - [x] 16.5 Add image upload endpoint
        - POST /api/v1/characters/{character}/image
        - _Requirements: FR-BE-15.1_
    - [ ]\* 16.6 Write property test for Image Validation
        - **Property 13: Image Validation**
        - **Validates: FR-BE-15.1, FR-BE-15.2, FR-BE-15.3, FR-BE-15.4, FR-BE-15.5**

- [x]   17. Implement Snapshot Management
    - [x] 17.1 Create Snapshot model and migration (if not exists)
        - Fields for all run state data
        - _Requirements: FR-BE-16.1_
    - [x] 17.2 Implement snapshot creation
        - Capture current run state
        - _Requirements: FR-BE-16.3_
    - [x] 17.3 Implement snapshot immutability
        - Reject update attempts
        - _Requirements: FR-BE-16.2_
    - [x] 17.4 Add snapshot endpoints to API
        - POST /api/v1/plans/{plan}/snapshots
        - GET /api/v1/plans/{plan}/snapshots
        - DELETE /api/v1/plans/{plan}/snapshots/{snapshot}
        - _Requirements: FR-BE-16.3, FR-BE-16.4_
    - [x] 17.5 Include snapshots in export
        - Add snapshots to plan export data
        - _Requirements: FR-BE-16.6_
    - [ ]\* 17.6 Write property test for Snapshot Immutability
        - **Property 14: Snapshot Immutability**
        - **Validates: FR-BE-16.1, FR-BE-16.2, FR-BE-16.3, FR-BE-16.5**

- [x]   18. Implement API Response Standards
    - [x] 18.1 Create PlanResource and PlanCollection
        - Implement toArray with all fields and relations
        - Include pagination metadata in collection
        - _Requirements: FR-BE-17.1, FR-BE-17.2_
    - [x] 18.2 Create ApiException handler
        - Consistent error response format
        - _Requirements: FR-BE-17.3_
    - [x] 18.3 Ensure proper HTTP status codes
        - Map operations to correct status codes
        - _Requirements: FR-BE-17.4_
    - [x] 18.4 Add Content-Type header middleware
        - Ensure all responses have application/json
        - _Requirements: FR-BE-17.5_
    - [ ]\* 18.5 Write property test for API Response Consistency
        - **Property 15: API Response Consistency**
        - **Validates: FR-BE-17.2, FR-BE-17.3, FR-BE-17.4, FR-BE-17.5**

- [x]   19. Implement Convert Local to Account
    - [x] 19.1 Create ConversionService class
        - Accept local run data and create DB CareerRun
        - _Requirements: FR-BE-18.1_
    - [x] 19.2 Preserve timestamps and ordering
        - Maintain created timestamps, turn ordering, skill statuses
        - _Requirements: FR-BE-18.2_
    - [x] 19.3 Implement duplicate detection for conversion
        - Check same title + character + created date
        - _Requirements: FR-BE-18.3_
    - [x] 19.4 Support bulk conversion
        - Convert multiple local runs in single request
        - _Requirements: FR-BE-18.4_
    - [x] 19.5 Use database transactions
        - Ensure atomicity for conversion operations
        - _Requirements: FR-BE-18.5_

- [x]   20. Complete Property-Based Testing Implementation
    - [x] 20.1 Install property-based testing framework
        - Install Eris via Composer: `composer require --dev giorgiosironi/eris`
        - Create base PropertyTestCase class extending TestCase
        - _Requirements: Testing Strategy_
    - [x] 20.2 Implement all 16 property tests from design document
        - Create tests/Feature/Properties/ directory
        - Implement each property test with minimum 100 iterations
        - Tag each test with property number and description
        - _Requirements: All FR-BE properties from design document_
    - [x] 20.3 Add property test configuration
        - Configure Eris with proper generators
        - Set up test data factories for property testing
        - Configure test timeouts and iteration counts
        - _Requirements: Testing Strategy_

- [x]   21. Enhance API Authentication and Authorization Testing
    - [x] 21.1 Add comprehensive authentication tests
        - Test Sanctum token authentication
        - Test session-based authentication
        - Test unauthenticated access returns 401
        - _Requirements: FR-BE-9.1, FR-BE-9.2, FR-BE-9.5_
    - [x] 21.2 Add authorization policy tests
        - Test user data isolation across all endpoints
        - Test admin role access (if implemented)
        - Test cross-user access returns 403
        - _Requirements: FR-BE-9.3, FR-BE-9.4_
    - [x] 21.3 Add rate limiting tests
        - Test autosuggest rate limiting (60 req/min)
        - Test rate limit headers and responses
        - _Requirements: FR-BE-4.5_

- [x]   22. Add Integration and Edge Case Testing
    - [x] 22.1 Add multi-step workflow tests
        - Test complete plan creation → update → export → import cycle
        - Test skill management workflows
        - Test snapshot creation and immutability
        - _Requirements: All FR-BE workflow requirements_
    - [x] 22.2 Add error handling and edge case tests
        - Test validation error responses (422)
        - Test not found responses (404)
        - Test malformed request handling (400)
        - Test server error handling (500)
        - _Requirements: FR-BE-17.3, FR-BE-17.4_
    - [x] 22.3 Add performance and load tests
        - Test autosuggest response time < 200ms
        - Test export performance with large datasets
        - Test pagination with large result sets
        - _Requirements: NFR-BE-1.1, NFR-BE-1.2_

- [x]   23. Final checkpoint - Ensure all tests pass
    - Ensure all tests pass, ask the user if questions arise.
    - Run full test suite with coverage report
    - Verify minimum 80% code coverage
    - Run property-based tests with full iteration counts
    - Validate all API endpoints with authentication
    - Test all error scenarios and edge cases

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Database uses canonical naming (`career_runs`, `career_run_id`) per D04 specification
- API uses user-friendly naming (`/plans`) with Laravel model bridging the mapping
- Foreign keys follow the pattern `{singular_table}_id` (e.g., `career_run_id`, not `plan_id`)

## Implementation Status Summary

**Completed (75-80% of backend):**

- ✅ All core API endpoints and controllers
- ✅ Complete service layer with business logic
- ✅ All models, relationships, and database schema
- ✅ Form requests and validation
- ✅ Events, listeners, and policies
- ✅ API resources and response formatting
- ✅ Export/import functionality
- ✅ Image processing and snapshot management
- ✅ Activity logging and cache management
- ✅ Basic unit and feature tests

**Remaining Work (20-25%):**

- ⚠️ Property-based testing framework setup and implementation
- ⚠️ Comprehensive authentication and authorization testing
- ⚠️ Integration tests and edge case coverage
- ⚠️ Performance testing and optimization validation
- ⚠️ Final test coverage verification and reporting

**Database Schema Status:**

The backend follows the canonical database schema from D04 System Design Specifications:

- `career_runs` - Main training run entity (API: `/plans`)
- `skill_career_runs` - Skills associated with plans (pivot table)
- `stat_progress` - Stat progression by turn
- `goals` - Training objectives
- `race_predictions` - Race planning entries
- `activity_logs` - User action history
- `attributes` - Character attributes
- `skill_references` - Skill reference data
- `uma_musumes` - Character master data

The backend follows the **canonical domain model** with Laravel models bridging the API-friendly names to canonical table names. The remaining tasks focus on comprehensive testing and quality assurance.
