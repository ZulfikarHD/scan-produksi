# Developer Documentation

Welcome to the technical developer documentation for the **Sistem Monitoring & Pencatatan Produksi** application.

---

## Architecture & Standards

- **Backend Framework**: Laravel 13 (PHP 8.5)
- **Frontend / Reactivity**: Livewire 4 + Flux UI + Livewire Blaze
- **CSS / Styling**: Tailwind CSS v4
- **Testing Suite**: Pest PHP 5
- **Timezone**: `Asia/Jakarta` (WIB)

---

## Features

| Feature Doc | Description | Status |
|-------------|-------------|--------|
| [Authentication & Layout Shell](features/authentication-and-layout-shell.md) | Flux UI application layout, live shift banner helper, Fortify & Livewire authentication supporting NP/Username/Email. | Implemented (Phase 01) |
| [Petugas Management & RBAC](features/petugas-management-and-rbac.md) | Single-surface operator management (`/petugas`), Livewire debounced search, role/status filtering, center modal CRUD forms, and `EnsureUserRole` middleware. | Implemented (Phase 01) |

---

## Architectural Decision Records (ADRs)

| ADR | Title | Date | Status |
|-----|-------|------|--------|
| [ADR-001](decisions/001-fortify-livewire-and-np-authentication.md) | Fortify Livewire & Multi-Identifier NP Authentication | 2026-08-26 | Accepted |
| [ADR-002](decisions/002-role-based-access-control-and-single-surface-user-management.md) | Role-Based Access Control & Single-Surface Petugas Management | 2026-08-26 | Accepted |
