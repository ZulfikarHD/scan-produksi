# Master Data Explorer & Manual Order Management (US-2.4 & US-2.5)

## Overview

The Master Data Explorer & Manual Order Management module (`/update-database` tab 2) provides supervisors and unit officers with interactive search, multi-faceted filtering, ad-hoc manual order registration, complete technical print specification drawers, and Excel export capabilities. It also completes the audit trail features (US-2.4) by enabling supervisors to download archived source spreadsheets and structured error audit logs.

---

## Architecture Diagram

```mermaid
flowchart TD
    Supervisor[Supervisor on /update-database] --> UI[UpdateDatabase Livewire Component]
    
    subgraph Tab1[Tab 1: Impor Berkas SAP - US-2.4]
        LogsTable[Import History Table: sap_import_logs]
        LogsTable -->|Download Original| StorageAdapter[Local Storage Disk: sap-imports/]
        LogsTable -->|Download Audit TXT| TxtBuilder[Generate Audit Error Text Report]
        LogsTable -->|View Error Modal| ErrorModal[Flux Modal: log-error-detail]
    end
    
    subgraph Tab2[Tab 2: Master Data Explorer - US-2.5]
        Toolbar[Debounced Search & Multi-Filters]
        Toolbar --> Scopes[ProductionOrder Eloquent Scopes]
        Scopes --> OrdersTable[Paginated Orders Table]
        
        OrdersTable -->|View Detail| DetailModal[Flux Modal: order-detail]
        OrdersTable -->|Add/Edit Order| FormModal[Flux Modal: order-form]
        OrdersTable -->|Delete Order| DeleteModal[Flux Modal: delete-order-confirm]
        Toolbar -->|Export Data| XlsxExport[SapImportService::exportOrdersToXlsx]
    end
```

---

## Data Model & Relationships

```mermaid
erDiagram
    USERS ||--o{ SAP_IMPORT_LOGS : "uploads / audits"
    USERS ||--o{ PRODUCTION_ORDERS : "creates / updates"
    
    SAP_IMPORT_LOGS {
        bigint id PK
        bigint user_id FK
        string file_name
        string file_path
        unsigned_int total_rows
        unsigned_int imported_count
        unsigned_int updated_count
        unsigned_int failed_count
        string status "success | partial | failed"
        json error_log "nullable"
        timestamps created_at
    }

    PRODUCTION_ORDERS {
        bigint id PK
        string po_number UK "Indexed barcode identifier"
        string no_obc "nullable"
        string perso_type "nullable"
        string seri "nullable"
        string product_code "nullable"
        string product_name
        string customer_name "nullable"
        text specification "nullable"
        string paper_type "nullable"
        string machine_number "nullable"
        unsigned_int target_quantity
        unsigned_int rencet_quantity
        unsigned_int kemas_quantity
        string unit "default: lembar"
        date order_date
        date delivery_deadline "nullable"
        string status "ready | in_progress | completed | cancelled"
        bigint created_by FK
        timestamps created_at
    }
```

---

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Model | `app/Models/ProductionOrder.php` | Eloquent model with casts, status enums, formatted numbers, fulfillment calculations, and query scopes (`search`, `byStatus`, `byMachine`, `byCustomer`, `byDateRange`) |
| Model | `app/Models/SapImportLog.php` | Import audit model with status helpers, error checks, and file storage links |
| Service | `app/Services/SapImportService.php` | Spreadsheet parsing, permanent audit archiving, sample template generation, and Excel dataset export (`exportOrdersToXlsx`) |
| Livewire Component | `app/Livewire/MasterData/UpdateDatabase.php` | Single-surface component orchestrating tabs, reactive filters, CRUD modal states, safety guardrails, and file downloads |
| Blade View | `resources/views/livewire/master-data/update-database.blade.php` | Responsive UI with tab navigation, filter toolbar, orders table, import history, and 4 modal dialogs (`import-preview`, `log-error-detail`, `order-form`, `order-detail`, `delete-order-confirm`) |
| Feature Test | `tests/Feature/MasterData/ImportLogTest.php` | Pest tests for audit logs, pagination, original file downloads, and error text reports (US-2.4) |
| Feature Test | `tests/Feature/MasterData/ProductionOrderCrudTest.php` | Pest tests for manual order creation, editing, detail view, deletion safety, filtering, and Excel export (US-2.5) |

---

## Flow Explanation

### 1. Master Data Searching & Multi-Attribute Filtering (US-2.5)
- Supervisor navigates to `/update-database` and switches to the **"Master Data Order Produksi"** tab.
- Livewire binds query state directly to URL parameters (`search`, `statusFilter`, `machineFilter`, `customerFilter`, `startDateFilter`, `endDateFilter`).
- Keyword search is debounced by 300ms across 5 fields (`po_number`, `no_obc`, `product_code`, `product_name`, `customer_name`).
- Machine dropdown dynamically retrieves distinct machine codes from `production_orders`.
- Resetting filters restores all properties to blank and resets pagination to page 1.

### 2. Manual Order Registration & Editing (Ad-Hoc / Emergency Jobs)
- Supervisor clicks **"+ Tambah Order Manual"**, opening `<flux:modal name="order-form">`.
- Form inputs are organized into 3 logical visual groups: *Identitas Order*, *Lini & Jadwal Produksi*, and *Target Kuantitas & Spesifikasi*.
- Real-time validation checks for unique `po_number`, required material name, positive integer target quantity, and valid dates.
- On save, `ProductionOrder::create()` or `$order->update()` executes, displaying a green Flux toast and refreshing the table.

### 3. Technical Specification Detail Drawer / Modal
- Clicking **"Lihat Detail"** on any order row opens `<flux:modal name="order-detail">`.
- Displays complete print specifications: full guilloche & security text, machine ID, packaging target vs real-time packed quantity, fulfillment progress percentage, and production cycle timestamps.

### 4. Order Deletion Guardrail
- Clicking **"Hapus Order"** opens `<flux:modal name="delete-order-confirm">`.
- If the order has status `in_progress` (currently active on the packaging shop floor), deletion is blocked with an alert toast to prevent session inconsistencies.
- For `ready` or completed orders, deletion safely removes the record and confirms via toast.

### 5. Filtered Data Export & Audit Log Downloads (US-2.4 & US-2.5)
- Clicking **"Ekspor Data (.xlsx)"** queries the currently filtered order set and streams an Excel file formatted with standard 24 SAP header columns.
- In the Import History table, supervisors can click **"Unduh Berkas Asli"** to retrieve the exact spreadsheet uploaded on previous shifts, or click **"Unduh Laporan Error (.txt)"** for audit analysis.

---

## Decisions & Trade-offs

- **Single Surface Architecture:** In strict compliance with the Phase 02 UX Plan, manual order creation, editing, detail viewing, deletion, and search are embedded in modal dialogs and tabs on `/update-database` rather than creating separate route endpoints.
- **Permanent Private Storage for Audit Files:** Uploaded files are copied to `storage/app/private/sap-imports/` upon import confirmation, ensuring long-term audit traceability for factory quality supervisors.
- **Safe In-Progress Deletion Blocking:** Deleting an order that is actively being packaged in Phase 03 could corrupt barcode scan sessions. The controller enforces status checks before executing delete operations.
