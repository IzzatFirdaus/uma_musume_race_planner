# JS DOM & API Integration Reference

## Critical DOM Elements/IDs/Classes

See developer comments in `components/plan-list.php` and `components/plan-inline-details.php` for required JS hooks.
All modals, forms, tables, and chart canvases must have the correct IDs/classes for JS to function.

## Required JS Libraries

- [Chart.js](https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js)
- [Bootstrap JS](https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js)
- [SweetAlert2](https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js)

## API Endpoints

- `/api/plan.php?action=list|get|delete|create`
- `/api/autosuggest.php?action=get&field=...`
- `/api/progress.php?action=chart&plan_id=...`
- `/api/plan_section.php?type=...&id=...`
- `export_plan_data.php?id=...&format=txt`

## Canonical JS Files

Only use `assets/js/plan_list.js` for plan list functionality. Legacy `plan-list.js` has been removed.

## Maintenance Checklist

- Ensure all DOM hooks are present in PHP templates/components.
- Confirm all API endpoints exist and return expected JSON.
- Load required JS libraries before dependent scripts.
- Remove legacy JS files and update references.
- See comments in code for further integration details.
# JS DOM & API Integration Reference

## Critical DOM Elements/IDs/Classes
- See developer comments in components/plan-list.php and components/plan-inline-details.php for required JS hooks.
- All modals, forms, tables, and chart canvases must have the correct IDs/classes for JS to function.

## Required JS Libraries
- Chart.js: https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js
- Bootstrap JS: https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js
- SweetAlert2: https://cdn.jsdelivr.net/npm/sweetalert2@11.10.7/dist/sweetalert2.all.min.js

## API Endpoints
- /api/plan.php?action=list|get|delete|create
- /api/autosuggest.php?action=get&field=...
- /api/progress.php?action=chart&plan_id=...
- /api/plan_section.php?type=...&id=...
- export_plan_data.php?id=...&format=txt

## Canonical JS Files
- Only use assets/js/plan_list.js for plan list functionality. Legacy plan-list.js has been removed.

## Maintenance Checklist
- Ensure all DOM hooks are present in PHP templates/components.
- Confirm all API endpoints exist and return expected JSON.
- Load required JS libraries before dependent scripts.
- Remove legacy JS files and update references.
- See comments in code for further integration details.
