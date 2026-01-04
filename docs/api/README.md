# Uma Musume Planner API Documentation

## Overview

The Uma Musume Planner provides a RESTful API (v1) for programmatic access to all core features. The API is public by default and does not require authentication, though Laravel Sanctum can be enabled for protected endpoints.

Base URL: `/api/v1`

## Authentication

The API is currently public. For protected access, Laravel Sanctum token authentication can be enabled.

## Response Format

All responses are JSON with consistent structure:

```json
{
  "data": { ... },
  "meta": { ... }
}
```

Error responses follow Laravel's standard format:

```json
{
    "message": "Error description",
    "errors": { "field": ["validation error"] }
}
```

---

## Endpoints

### Plans (Career Runs)

| Method | Endpoint                     | Description                         |
| ------ | ---------------------------- | ----------------------------------- |
| GET    | `/plans`                     | List all plans with pagination      |
| POST   | `/plans`                     | Create a new plan                   |
| GET    | `/plans/{id}`                | Get plan details                    |
| PUT    | `/plans/{id}`                | Update a plan                       |
| DELETE | `/plans/{id}`                | Delete a plan                       |
| POST   | `/plans/quick`               | Quick create with minimal fields    |
| GET    | `/plans/{id}/progress-chart` | Get chart data for stat progression |

#### Create Plan Request

```json
{
    "uma_musume_id": 1,
    "year": "junior",
    "status": "ongoing",
    "uma_class": "debut",
    "scenario": "URA",
    "storage_mode": "account",
    "current_turn": 1,
    "total_sp_available": 0,
    "stamina_percentage": 100
}
```

---

### Stat Progress

| Method | Endpoint                       | Description                      |
| ------ | ------------------------------ | -------------------------------- |
| GET    | `/plans/{plan}/stats`          | List all stat entries for a plan |
| POST   | `/plans/{plan}/stats`          | Add a stat entry                 |
| GET    | `/plans/{plan}/stats/{id}`     | Get single stat entry            |
| PUT    | `/plans/{plan}/stats/{id}`     | Update stat entry                |
| DELETE | `/plans/{plan}/stats/{id}`     | Delete stat entry                |
| GET    | `/plans/{plan}/stats/totals`   | Get stat totals                  |
| GET    | `/plans/{plan}/stats/averages` | Get stat averages                |
| GET    | `/plans/{plan}/stats/chart`    | Get chart-ready data             |
| GET    | `/plans/{plan}/stats/summary`  | Get comprehensive summary        |

#### Add Stat Entry Request

```json
{
    "turn_number": 1,
    "speed": 150,
    "stamina": 120,
    "power": 130,
    "guts": 100,
    "wit": 110
}
```

#### Stat Summary Response

```json
{
  "data": {
    "totals": { "speed": 1200, "stamina": 1100, ... },
    "averages": { "speed": 150.5, "stamina": 137.5, ... },
    "growth": { "speed": 50, "stamina": 45, ... },
    "turn_count": 8
  }
}
```

---

### Skills

| Method | Endpoint                      | Description               |
| ------ | ----------------------------- | ------------------------- |
| GET    | `/skills`                     | List all skill references |
| GET    | `/skills/search?q={query}`    | Search skills (EN + JP)   |
| GET    | `/plans/{plan}/skills`        | List skills for a plan    |
| POST   | `/plans/{plan}/skills`        | Add skill to plan         |
| PUT    | `/plans/{plan}/skills/{id}`   | Update skill status       |
| DELETE | `/plans/{plan}/skills/{id}`   | Remove skill from plan    |
| GET    | `/plans/{plan}/skills/totals` | Get SP totals             |

#### Skill Search

```
GET /api/v1/skills/search?q=last&limit=10
```

Response:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Last Legs",
            "name_jp": "ラストスパート",
            "type": "speed",
            "sp_cost": 40
        }
    ],
    "meta": {
        "query": "last",
        "count": 1,
        "cached": true
    }
}
```

#### Add Skill to Plan

```json
{
    "skill_id": 1,
    "status": "acquired",
    "turn_acquired": 15,
    "notes": "Acquired during Classic year"
}
```

Skill status values: `acquired`, `skipped`, `suggested`

Note: `turn_acquired` is required when status is `acquired`.

#### SP Totals Response

```json
{
    "data": {
        "acquired_total": 450,
        "suggested_total": 200,
        "skipped_total": 80,
        "skill_count": {
            "acquired": 12,
            "suggested": 5,
            "skipped": 2
        }
    }
}
```

---

### Uma Musume (Characters)

| Method | Endpoint                       | Description           |
| ------ | ------------------------------ | --------------------- |
| GET    | `/uma-musume`                  | List all characters   |
| POST   | `/uma-musume`                  | Create character      |
| GET    | `/uma-musume/{id}`             | Get character details |
| PUT    | `/uma-musume/{id}`             | Update character      |
| DELETE | `/uma-musume/{id}`             | Delete character      |
| GET    | `/uma-musume-search?q={query}` | Search characters     |

#### Create Character Request

```json
{
    "name": "Special Week",
    "name_jp": "スペシャルウィーク",
    "aptitude_style": { "front": "A", "pace": "B", "late": "C", "end": "D" },
    "aptitude_distance": {
        "sprint": "E",
        "mile": "B",
        "medium": "A",
        "long": "S"
    },
    "aptitude_track": { "turf": "A", "dirt": "C" },
    "growth_speed": 10,
    "growth_stamina": 20,
    "growth_power": 0,
    "growth_guts": 10,
    "growth_wit": 0
}
```

---

### Export

| Method | Endpoint                                          | Description                   |
| ------ | ------------------------------------------------- | ----------------------------- |
| GET    | `/export/formats`                                 | List available export formats |
| GET    | `/export/career-run/{id}?format={format}`         | Export plan data              |
| GET    | `/export/career-run/{id}/preview?format={format}` | Preview export                |
| POST   | `/export/bulk`                                    | Export multiple plans         |

#### Available Formats

- `json` - JSON format with schema version
- `csv` - UTF-8 CSV with proper quoting
- `markdown` - Human-readable markdown
- `xlsx` - Excel spreadsheet (via download)

#### Export Preview Response

```json
{
    "data": {
        "format": "markdown",
        "content": "# Career Run: Special Week\n\n## Stats\n...",
        "estimated_size": "2.4 KB"
    }
}
```

---

### Dashboard

| Method | Endpoint                | Description              |
| ------ | ----------------------- | ------------------------ |
| GET    | `/dashboard/stats`      | Get dashboard statistics |
| GET    | `/dashboard/activities` | Get recent activities    |

---

### Autosuggest

| Method | Endpoint                             | Description         |
| ------ | ------------------------------------ | ------------------- |
| GET    | `/autosuggest?type={type}&q={query}` | Generic autosuggest |

Types: `skill`, `character`, `race`

---

## Rate Limiting

- Skill search endpoint: 60 requests/minute per IP
- General endpoints: 1000 requests/minute per IP

## Caching

- Skill search results: 5-minute TTL
- Character data: 10-minute TTL
- Dashboard stats: 1-minute TTL

## Error Codes

| Code | Description                           |
| ---- | ------------------------------------- |
| 400  | Bad Request - Invalid parameters      |
| 404  | Not Found - Resource doesn't exist    |
| 422  | Validation Error - Check errors field |
| 429  | Too Many Requests - Rate limited      |
| 500  | Server Error                          |
