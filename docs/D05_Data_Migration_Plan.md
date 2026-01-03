# D05 - Data Migration Plan

## Uma Musume Career Planner

**Document Version:** 2.0
**Date:** 2026-01-03
**Status:** Active
**Last Updated:** 2026-01-03

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Source System Analysis](#2-source-system-analysis)
3. [Target System Schema](#3-target-system-schema)
4. [Migration Strategy](#4-migration-strategy)
5. [Import Adapters](#5-import-adapters)
6. [Data Transformation Rules](#6-data-transformation-rules)
7. [Conflict Resolution](#7-conflict-resolution)
8. [Migration Execution Plan](#8-migration-execution-plan)
9. [Rollback Procedures](#9-rollback-procedures)
10. [Communication Plan](#10-communication-plan)
11. [Success Metrics](#11-success-metrics)
12. [Appendices](#12-appendices)

---

## 1. Executive Summary

This Data Migration Plan outlines the strategy for migrating data from five legacy Uma Musume tracking applications into the consolidated Uma Musume Career Planner platform. The migration supports both automated import processes and manual data entry fallbacks.

### 1.1 Migration Scope

```mermaid
pie title Data Volume by Source Application
    "uma_musume_race_planner" : 500
    "umamusume-tracker" : 200
    "uma-tracker" : 150
    "uma-run-tracker" : 100
    "uma-tracker-form" : 50
```text
| Source Application | Data Volume (Est.) | Priority |
| ------------------ | ------------------ | -------- |
| uma_musume_race_planner | ~500 plans | High |
| umamusume-tracker | ~200 plans | High |
| uma-tracker | ~150 plans | Medium |
| uma-run-tracker | ~100 plans | Medium |
| uma-tracker-form | ~50 plans | Low |

### 1.2 Migration Objectives

1. Preserve all user data from legacy applications
2. Maintain data integrity during transformation
3. Minimize downtime and user disruption
4. Provide rollback capabilities
5. Support incremental migration

---

## 2. Source System Analysis

### 2.1 Source Systems Overview

```mermaid
flowchart LR
    subgraph Legacy["Legacy Applications"]
        A[uma_musume_race_planner<br/>PHP + MySQL]
        B[umamusume-tracker<br/>Laravel + React]
        C[uma-tracker<br/>Laravel + Blade]
        D[uma-run-tracker<br/>Static HTML + JS]
        E[uma-tracker-form<br/>Native PHP]
    end
    
    subgraph Target["Target System"]
        F[(Uma Musume<br/>Career Planner)]
    end
    
    A -->|MySQL dump/JSON| F
    B -->|JSON API| F
    C -->|MySQL dump| F
    D -->|localStorage JSON| F
    E -->|CSV/JSON| F
```text
### 2.2 uma_musume_race_planner (PHP + MySQL)

**Data Structures:**

- Characters table with aptitudes
- Runs table with stats and status
- Skills pivot table
- Race predictions table

**Export Format:** MySQL dump or JSON export

**Key Mappings:**

| Source Field | Target Field | Transformation |
| ------------ | ------------ | -------------- |
| run_id | uuid | Generate new UUID |
| total_sp | total_sp_available | Direct copy |
| stamina_pct | stamina_percentage | Direct copy |
| turn | turn_number | Direct copy |
| skill_status | status | Map to enum |

### 2.3 umamusume-tracker (Laravel + React)

**Data Structures:**

- Similar Laravel Eloquent models
- JSON API format exports

**Export Format:** JSON (primary)

**Key Mappings:**

| Source Field | Target Field | Transformation |
| ------------ | ------------ | -------------- |
| id | id (Account) / uuid (Local) | Context-dependent |
| career_run_id | career_run_id | Direct copy |
| acquired_turn | turn_acquired | Direct copy |

### 2.4 uma-run-tracker (Static HTML + JS)

**Data Structures:**

- localStorage JSON format
- Versioned schema already in place

**Export Format:** JSON from localStorage

**Key Mappings:**

| Source Field | Target Field | Transformation |
| ------------ | ------------ | -------------- |
| localId | uuid | Keep or regenerate |
| schema_version | schema_version | Migrate if older |

### 2.5 uma-tracker (Laravel + Blade)

**Data Structures:**

- Standard Laravel models
- MySQL database

**Export Format:** MySQL dump or Eloquent export

### 2.6 uma-tracker-form (Native PHP)

**Data Structures:**

- Simple flat file or MySQL
- Minimal relational structure

**Export Format:** CSV or JSON manual export

---

## 3. Target System Schema

### 3.1 Canonical Field Names

All migrations MUST use these canonical names:

| Entity | Canonical Fields |
| ------ | ---------------- |
| CareerRun | `career_run_id`, `total_sp_available`, `stamina_percentage`, `current_turn` |
| StatProgress | `career_run_id`, `turn_number` |
| SkillCareerRun | `career_run_id`, `skill_id`, `status`, `turn_acquired` |
| ActivityLog | `user_id`, `model_type`, `model_id` |

### 3.2 Schema Version

Target schema version: `1.0`

All imports include `schema_version` for forward compatibility.

---

## 4. Migration Strategy

### 4.1 Phased Approach

```mermaid
gantt
    title Migration Phases
    dateFormat  YYYY-MM-DD
    section Phase 1
    Export data from sources     :p1a, 2026-01-06, 3d
    Create migration adapters    :p1b, after p1a, 4d
    Set up staging environment   :p1c, after p1a, 2d
    Test migration scripts       :p1d, after p1b, 3d
    section Phase 2
    Pilot: uma-run-tracker       :p2a, after p1d, 2d
    Validate migrated data       :p2b, after p2a, 2d
    Collect feedback             :p2c, after p2b, 2d
    section Phase 3
    Migrate uma_musume_race_planner :p3a, after p2c, 3d
    Migrate umamusume-tracker    :p3b, after p3a, 2d
    Migrate uma-tracker          :p3c, after p3b, 2d
    Migrate uma-tracker-form     :p3d, after p3c, 1d
    section Phase 4
    Data integrity checks        :p4a, after p3d, 2d
    Generate migration reports   :p4b, after p4a, 1d
    Archive source data          :p4c, after p4b, 1d
    Decommission legacy systems  :p4d, after p4c, 2d
```text
**ASCII Diagram:**

```text
Phase 1: Preparation
├── Export data from all source systems
├── Create migration adapters
├── Set up staging environment
└── Test migration scripts

Phase 2: Pilot Migration
├── Migrate uma-run-tracker data (smallest, best format)
├── Validate migrated data
├── Collect feedback
└── Adjust migration scripts

Phase 3: Main Migration
├── Migrate uma_musume_race_planner (largest)
├── Migrate umamusume-tracker
├── Migrate uma-tracker
└── Migrate uma-tracker-form

Phase 4: Validation & Cleanup
├── Run data integrity checks
├── Generate migration reports
├── Archive source data
└── Decommission legacy systems
```text
### 4.2 Migration Methods

```mermaid
flowchart TD
    subgraph Primary["Automated Import (Primary)"]
        A1[Source System] --> A2[Export]
        A2 --> A3[JSON File]
        A3 --> A4[Import Wizard]
        A4 --> A5[Preview & Validate]
        A5 --> A6[Conflict Resolution]
        A6 --> A7[Execute Import]
        A7 --> A8[Target DB]
    end
    
    subgraph Secondary["Direct DB Migration (Secondary)"]
        B1[Source DB] --> B2[Migration Script]
        B2 --> B3[Schema Mapping]
        B3 --> B4[Data Validation]
        B4 --> B5[Insert with Transactions]
        B5 --> B6[Target DB]
    end
    
    subgraph Fallback["Manual Entry (Fallback)"]
        C1[Data Entry Templates]
        C2[CSV Bulk Import]
        C3[Manual Verification]
    end
```text
---

## 5. Import Adapters

### 5.1 Adapter Architecture

```mermaid
classDiagram
    class ImportAdapterInterface {
        <<interface>>
        +detect(UploadedFile file) bool
        +parse(UploadedFile file) Collection
        +getSchemaVersion() string
        +getSupportedFormats() array
    }
    
    class JsonImportAdapter {
        +detect(file) bool
        +parse(file) Collection
    }
    
    class CsvImportAdapter {
        +detect(file) bool
        +parse(file) Collection
    }
    
    class MySqlDumpAdapter {
        +detect(file) bool
        +parse(file) Collection
    }
    
    class LegacyJsonAdapter {
        +detect(file) bool
        +parse(file) Collection
    }
    
    ImportAdapterInterface <|.. JsonImportAdapter
    ImportAdapterInterface <|.. CsvImportAdapter
    ImportAdapterInterface <|.. MySqlDumpAdapter
    ImportAdapterInterface <|.. LegacyJsonAdapter
```text
### 5.2 Adapter Implementations

| Adapter | Source | Format | Priority |
| ------- | ------ | ------ | -------- |
| JsonImportAdapter | uma-run-tracker | JSON | P0 (MVP) |
| CsvImportAdapter | General | CSV | P1 |
| MySqlDumpAdapter | uma_musume_race_planner | SQL | P2 |
| LegacyJsonAdapter | umamusume-tracker | JSON | P1 |

### 5.3 Format Detection

```php
class FormatDetector
{
    public function detect(UploadedFile $file): ImportFormat
    {
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $content = $file->get();
        
        // JSON detection
        if ($this->isValidJson($content)) {
            return $this->detectJsonSchema($content);
        }
        
        // CSV detection
        if ($this->isValidCsv($content)) {
            return ImportFormat::Csv;
        }
        
        throw new UnsupportedFormatException();
    }
    
    private function detectJsonSchema(string $content): ImportFormat
    {
        $data = json_decode($content, true);
        
        if (isset($data['schema_version'])) {
            return ImportFormat::JsonVersioned;
        }
        
        if (isset($data['career_runs'])) {
            return ImportFormat::JsonLegacy;
        }
        
        return ImportFormat::JsonGeneric;
    }
}
```text
---

## 6. Data Transformation Rules

### 6.1 Field Mappings

```mermaid
flowchart LR
    subgraph Source["Source Fields"]
        S1[run_id]
        S2[total_sp]
        S3[stamina_pct]
        S4["status: ongoing"]
        S5["year: 1"]
    end
    
    subgraph Target["Target Fields"]
        T1[uuid]
        T2[total_sp_available]
        T3[stamina_percentage]
        T4["status: in_progress"]
        T5["career_stage: junior"]
    end
    
    S1 -->|Generate UUID| T1
    S2 -->|Direct copy| T2
    S3 -->|Direct copy| T3
    S4 -->|Enum mapping| T4
    S5 -->|Enum mapping| T5
```text
#### 6.1.1 CareerRun Transformations

| Source Pattern | Target | Rule |
| -------------- | ------ | ---- |
| `run_id` | `uuid` | Generate new UUID |
| `total_sp` | `total_sp_available` | Direct copy |
| `stamina_pct` | `stamina_percentage` | Direct copy |
| `status: "ongoing"` | `status: "in_progress"` | Enum mapping |
| `status: "done"` | `status: "completed"` | Enum mapping |
| `year: 1` | `career_stage: "junior"` | Enum mapping |
| `year: 2` | `career_stage: "classic"` | Enum mapping |
| `year: 3` | `career_stage: "senior"` | Enum mapping |

#### 6.1.2 Skill Status Transformations

| Source | Target |
| ------ | ------ |
| `"bought"`, `"purchased"`, `true` | `"acquired"` |
| `"skipped"`, `"passed"`, `false` | `"skipped"` |
| `"planned"`, `"suggested"`, `"maybe"` | `"suggested"` |

#### 6.1.3 Aptitude Grade Normalization

| Source | Target |
| ------ | ------ |
| `"s"`, `"S"`, `1` | `"S"` |
| `"a"`, `"A"`, `2` | `"A"` |
| ... | ... |
| `"g"`, `"G"`, `8` | `"G"` |

### 6.2 Data Validation Rules

```php
class MigrationValidator
{
    public function validate(array $plan): ValidationResult
    {
        $rules = [
            'title' => 'required|string|max:255',
            'status' => 'required|in:in_progress,completed,archived',
            'career_stage' => 'required|in:junior,classic,senior',
            'current_turn' => 'required|integer|min:1|max:78',
            'speed' => 'integer|min:0|max:1200',
            'stamina' => 'integer|min:0|max:1200',
            'power' => 'integer|min:0|max:1200',
            'guts' => 'integer|min:0|max:1200',
            'wit' => 'integer|min:0|max:1200',
        ];
        
        return Validator::make($plan, $rules);
    }
}
```text
---

## 7. Conflict Resolution

### 7.1 Duplicate Detection

Duplicates detected by:

1. Same title + character + created date
2. Same UUID (for Local runs)
3. Same source ID reference

### 7.2 Resolution Options

```mermaid
flowchart TD
    A[Duplicate Detected] --> B{User Choice}
    B -->|Skip| C[Keep existing, don't import]
    B -->|Overwrite| D[Replace existing with imported]
    B -->|Import as Copy| E[Create new with modified title]
    B -->|Merge| F[Combine data - P2 feature]
```text
| Option | Description |
| ------ | ----------- |
| Skip | Do not import, keep existing |
| Overwrite | Replace existing with imported |
| Import as Copy | Create new with modified title |
| Merge | Combine data (P2 feature) |

### 7.3 User Interface

```text
┌─────────────────────────────────────────────────┐
│ Conflict Detected                               │
├─────────────────────────────────────────────────┤
│                                                 │
│ "My Speed Build" already exists                 │
│                                                 │
│ Existing:                    Importing:         │
│ Created: 2025-12-01        Created: 2025-11-15 │
│ Status: In Progress        Status: Completed   │
│ Turn: 45                   Turn: 72            │
│                                                 │
│ [Skip] [Overwrite] [Import as Copy]             │
│                                                 │
└─────────────────────────────────────────────────┘
```text
---

## 8. Migration Execution Plan

### 8.1 Pre-Migration Checklist

- [ ] All source data exported
- [ ] Migration adapters tested
- [ ] Staging environment ready
- [ ] Rollback scripts prepared
- [ ] User communication sent
- [ ] Maintenance window scheduled

### 8.2 Execution Steps

```mermaid
flowchart TD
    A[1. Enable maintenance mode] --> B[2. Create database backup]
    B --> C[3. Run migration scripts]
    C --> D[4. Validate migrated data]
    D --> E{Validation passed?}
    E -->|Yes| F[5. Generate migration report]
    E -->|No| G[Rollback]
    F --> H[6. Disable maintenance mode]
    H --> I[7. Monitor for issues]
    G --> J[Investigate & fix]
    J --> C
```text
### 8.3 Post-Migration Verification

| Check | Query/Action |
| ----- | ------------ |
| Plan count matches | Compare source vs target counts |
| Skill associations | Verify pivot table integrity |
| Turn data complete | Check stat_progress records |
| Goals preserved | Verify goals table |
| No orphan records | Check foreign key integrity |

---

## 9. Rollback Procedures

### 9.1 Rollback Triggers

- Data corruption detected
- Validation failures > 5%
- Critical functionality broken
- User-reported data loss

### 9.2 Rollback Steps

```mermaid
flowchart TD
    A[Rollback Triggered] --> B[Stop migration process]
    B --> C[Drop newly created records by batch ID]
    C --> D[Restore from pre-migration backup]
    D --> E[Verify data integrity]
    E --> F[Notify stakeholders]
    F --> G[Root cause analysis]
```text
### 9.3 Rollback Window

- Full rollback: Within 24 hours of migration
- Partial rollback: Within 7 days (individual records)
- Soft delete recovery: Within 30 days

---

## 10. Communication Plan

### 10.1 Stakeholder Notifications

| Phase | Recipients | Message |
| ----- | ---------- | ------- |
| T-7 days | All users | Migration announcement |
| T-1 day | All users | Migration reminder |
| T-0 | All users | Maintenance notification |
| T+1 hour | All users | Migration complete |
| T+24 hours | All users | Follow-up & feedback request |

### 10.2 Support Plan

- FAQ document prepared
- Support team briefed
- Known issues documented
- Escalation path defined

---

## 11. Success Metrics

### 11.1 Migration KPIs

```mermaid
pie title Success Metrics Targets
    "Data Migration Success (>99%)" : 99
    "Downtime (<2 hours)" : 1
```text
| Metric | Target |
| ------ | ------ |
| Data migration success rate | > 99% |
| Downtime duration | < 2 hours |
| Post-migration support tickets | < 10 |
| User satisfaction | > 90% |

### 11.2 Data Integrity Metrics

| Check | Threshold |
| ----- | --------- |
| Record count accuracy | 100% |
| Field mapping accuracy | > 99% |
| Relationship integrity | 100% |
| Schema compliance | 100% |

---

## 12. Appendices

### 12.1 Source System Export Scripts

See D06_Data_Migration_Specifications for detailed scripts.

### 12.2 Related Documents

- D06_Data_Migration_Specifications
- D09_Database_Documentation
- D15_Data_Migration_Report

### 12.3 Revision History

| Version | Date | Author | Changes |
| ------- | ---- | ------ | ------- |
| 1.0 | 2026-01-03 | System | Initial draft |
| 2.0 | 2026-01-03 | System | Converted to markdown, added Mermaid diagrams, standardized formatting |
