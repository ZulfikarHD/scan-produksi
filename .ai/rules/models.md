---
paths:
  - 'app/Models/*.php'
---

# Models

## Domain models use tablename_id primary keys (US-6.1 Pathway 2)
Physical PKs are user_id, production_order_id, daily_report_id, daily_report_item_id, sap_import_log_id. Set protected $primaryKey accordingly; define belongsTo/hasMany with explicit owner keys; keep getIdAttribute() returning getKey() for Blade/Livewire compatibility. Raw joins/orderBy/unique-ignore must use the physical PK column name, not id. Framework tables (jobs, sessions, cache) keep default id.
