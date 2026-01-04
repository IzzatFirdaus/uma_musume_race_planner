# D15 - Data Migration Report Template

## Uma Musume Career Planner

**Document Version:** 2.0  
**Date:** 2026-01-03  
**Status:** Template

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Migration Metrics](#2-migration-metrics)
3. [Issues & Resolutions](#3-issues--resolutions)
4. [Validation Checklist](#4-validation-checklist)
5. [Sign-off](#5-sign-off)

---

## 1. Executive Summary

### 1.1 Migration Overview

| Field                | Value                                               |
| -------------------- | --------------------------------------------------- |
| **Migration Date**   | [YYYY-MM-DD]                                        |
| **Source System(s)** | [List Sources, e.g., uma-run-tracker, legacy-mysql] |
| **Target System**    | Uma Musume Career Planner (Account Mode DB)         |
| **Executor**         | [Name/System]                                       |
| **Status**           | [SUCCESS / PARTIAL / FAILED]                        |

### 1.2 Migration Flow

```mermaid
flowchart LR
    subgraph Source["Source Systems"]
        S1["Legacy App 1"]
        S2["Legacy App 2"]
        S3["JSON Exports"]
    end

    subgraph Process["Migration Process"]
        Extract["Extract"]
        Transform["Transform"]
        Validate["Validate"]
        Load["Load"]
    end

    subgraph Target["Target System"]
        DB[(Uma Musume<br/>Career Planner)]
    end

    S1 --> Extract
    S2 --> Extract
    S3 --> Extract
    Extract --> Transform
    Transform --> Validate
    Validate --> Load
    Load --> DB
```

### 1.3 Status Summary

```mermaid
pie title Migration Status
    "Successfully Migrated" : 0
    "Duplicates Skipped" : 0
    "Errors/Failed" : 0
    "Warnings" : 0
```

---

## 2. Migration Metrics

### 2.1 Summary Metrics

| Metric                    | Count    | Notes                                       |
| ------------------------- | -------- | ------------------------------------------- |
| **Total Source Records**  | 0        | Plans identified for import                 |
| **Successfully Migrated** | 0        | Created in new DB                           |
| **Duplicates Skipped**    | 0        | Based on Title/Char/Date match              |
| **Errors/Failed**         | 0        | Validation failures                         |
| **Warnings**              | 0        | Data adapted/modified (e.g., status mapped) |
| **Duration**              | 00:00:00 | Execution time                              |

### 2.2 Migration Timeline

```mermaid
gantt
    title Migration Execution Timeline
    dateFormat HH:mm
    section Preparation
    Environment Setup    :prep, 00:00, 10m
    Source Connection    :conn, after prep, 5m
    section Extraction
    Extract Records      :extract, after conn, 15m
    section Transformation
    Field Mapping        :map, after extract, 10m
    Data Validation      :validate, after map, 20m
    section Loading
    Database Insert      :load, after validate, 30m
    section Verification
    Data Verification    :verify, after load, 15m
    Report Generation    :report, after verify, 5m
```

### 2.3 Records by Source

| Source System | Records | Migrated | Skipped | Failed |
| ------------- | ------- | -------- | ------- | ------ |
| [Source 1]    | 0       | 0        | 0       | 0      |
| [Source 2]    | 0       | 0        | 0       | 0      |
| **Total**     | **0**   | **0**    | **0**   | **0**  |

### 2.4 Records by Type

```mermaid
pie title Records by Entity Type
    "Career Runs" : 0
    "Skills" : 0
    "Turn History" : 0
    "Goals" : 0
```

---

## 3. Issues & Resolutions

### 3.1 Issue Classification

```mermaid
flowchart TD
    Issue["Issue Detected"]

    Issue --> Type{"Issue Type?"}

    Type -->|"Critical"| Critical["🔴 Critical<br/>Record Not Migrated"]
    Type -->|"Warning"| Warning["🟡 Warning<br/>Data Modified"]
    Type -->|"Info"| Info["🔵 Info<br/>Logged Only"]

    Critical --> ManualFix["Manual Remediation Required"]
    Warning --> AutoFix["Auto-corrected"]
    Info --> NoAction["No Action Needed"]
```

### 3.2 Critical Failures (Records Not Migrated)

| Source ID | Title   | Error Message | Remediation Action    |
| --------- | ------- | ------------- | --------------------- |
| [ID]      | [Title] | [Error]       | [Manual Fix / Ignore] |
| ...       | ...     | ...           | ...                   |

### 3.3 Data Warnings (Records Migrated with Modifications)

| Source ID | Title | Field  | Original  | Modified      | Reason               |
| --------- | ----- | ------ | --------- | ------------- | -------------------- |
| [ID]      | Run A | Status | "Ongoing" | "in_progress" | Enum Mapping         |
| [ID]      | Run B | Turn   | 0         | 1             | Min Value constraint |

### 3.4 Common Transformation Rules Applied

```mermaid
flowchart LR
    subgraph Original["Original Values"]
        O1["'Ongoing'"]
        O2["'Done'"]
        O3["Turn: 0"]
        O4["SP: null"]
    end

    subgraph Transformed["Transformed Values"]
        T1["'in_progress'"]
        T2["'completed'"]
        T3["Turn: 1"]
        T4["SP: 0"]
    end

    O1 -->|"Enum Map"| T1
    O2 -->|"Enum Map"| T2
    O3 -->|"Min Value"| T3
    O4 -->|"Default"| T4
```

---

## 4. Validation Checklist

### 4.1 Validation Process

```mermaid
flowchart TD
    Start["Start Validation"]

    Start --> Count["Record Count Check"]
    Count --> Integrity["Data Integrity Check"]
    Integrity --> Relations["Relationship Check"]
    Relations --> Timestamps["Timestamp Check"]
    Timestamps --> Users["User Association Check"]
    Users --> Complete["Validation Complete"]

    Count -->|"Fail"| CountFail["❌ Count Mismatch"]
    Integrity -->|"Fail"| IntegrityFail["❌ Data Corruption"]
    Relations -->|"Fail"| RelationsFail["❌ Broken Links"]
```

### 4.2 Checklist

| #   | Check                                                                       | Status | Notes |
| --- | --------------------------------------------------------------------------- | ------ | ----- |
| 1   | **Record Count:** Source count minus Skipped equals Target count            | ⬜     |       |
| 2   | **Data Integrity:** Checked 5 random records; Stats and Skills match source | ⬜     |       |
| 3   | **Relationships:** Skills are correctly linked to Plans                     | ⬜     |       |
| 4   | **Timestamps:** Original creation dates preserved                           | ⬜     |       |
| 5   | **User Association:** All records linked to correct User ID                 | ⬜     |       |
| 6   | **Canonical Fields:** All field names follow canonical naming               | ⬜     |       |
| 7   | **Enum Values:** All enum fields have valid values                          | ⬜     |       |

### 4.3 Sample Record Verification

| Source ID | Target ID | Title   | Stats Match | Skills Match | Verified |
| --------- | --------- | ------- | ----------- | ------------ | -------- |
| [SRC_1]   | [TGT_1]   | [Title] | ⬜          | ⬜           | ⬜       |
| [SRC_2]   | [TGT_2]   | [Title] | ⬜          | ⬜           | ⬜       |
| [SRC_3]   | [TGT_3]   | [Title] | ⬜          | ⬜           | ⬜       |
| [SRC_4]   | [TGT_4]   | [Title] | ⬜          | ⬜           | ⬜       |
| [SRC_5]   | [TGT_5]   | [Title] | ⬜          | ⬜           | ⬜       |

---

## 5. Sign-off

### 5.1 Approval Workflow

```mermaid
flowchart LR
    Engineer["Migration Engineer"]
    QA["QA Lead"]
    Manager["Project Manager"]

    Engineer -->|"Execute & Document"| QA
    QA -->|"Verify & Approve"| Manager
    Manager -->|"Final Sign-off"| Complete["Migration Complete"]
```

### 5.2 Signatures

| Role                   | Name | Signature | Date |
| ---------------------- | ---- | --------- | ---- |
| **Migration Engineer** |      |           |      |
| **QA Lead**            |      |           |      |
| **Project Manager**    |      |           |      |

---

## Document History

| Version | Date       | Author           | Changes                |
| ------- | ---------- | ---------------- | ---------------------- |
| 1.0     | 2026-01-03 | Development Team | Initial template       |
| 2.0     | 2026-01-03 | Development Team | Added Mermaid diagrams |
