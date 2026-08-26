# ADR-009: Dashboard Analytics Aggregation, Zero-Fill Timeseries, and Chart.js Integration

**Date:** 2026-08-26
**Status:** accepted

## Context
Phase 05 of the application requires a centralized command dashboard (`/dashboard`) that gives operators and supervisors an immediate, single-glance operational overview. Key challenges included:
1. Production time-series graphs (7, 14, 30 days) often suffer from "skipping" dates when no reports are submitted on holidays/weekends, which distorts Chart.js curves if not properly continuous.
2. Analytics queries across multiple tables (`daily_reports`, `daily_report_items`, `users`) could become costly if executed on every page request.
3. The UI requires interactive date range toggles (7, 14, 30 days) and chart format selectors (Area, Line, Bar) without triggering full page reloads or layout shifts.

## Decision
1. **Service Layer Aggregation (`DashboardAnalyticsService`)**:
   - Centralized all KPI math, reject rate formulas, and trend data inside `DashboardAnalyticsService`.
   - Used `CarbonPeriod` to pre-generate all dates in the requested window and zero-fill good/reject values for dates with no recorded activity.
2. **Alpine.js + Global Chart.js Binding**:
   - Bundled `chart.js` via NPM and exposed `window.Chart = Chart` in `resources/js/app.js`.
   - Built a custom Alpine component `productionChartComponent` inside `resources/views/livewire/dashboard/index.blade.php` that dynamically manages the canvas lifecycle (`renderChart`, `destroy`, dark mode detection, and Indonesian currency/number formatting).
3. **Adaptive Caching Architecture**:
   - Wrapped analytical query responses in a 5-minute cache with driver detection (`Cache::tags` when available, standard keys when using file/sqlite cache).
   - Added a manual "Segarkan Data" button in the dashboard header that flushes the cache on demand and dispatches a Flux toast notification.

## Consequences

### Positive
- Time-series charts are always uniformly spaced and mathematically accurate across 7, 14, and 30-day horizons.
- Zero full-page reloads when filtering dates or chart types via Livewire URL state (`range` and `type`).
- Low database overhead due to 5-minute cached analytical snapshots.
- Single consolidated surface adheres strictly to the Phase 05 UX plan without fragmented routes.

### Negative
- Client-side chart bundle increases `app.js` size by ~60KB gzip (acceptable given high visual fidelity and native tooltips).

### Neutral
- Cache keys follow the convention `dashboard_metrics_today`, `dashboard_trend_{days}`, `dashboard_shifts_{days}`, and `dashboard_machines_{days}_{limit}`.
