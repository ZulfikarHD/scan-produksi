# ADR-002: Role-Based Access Control & Single-Surface Petugas Management

**Date:** 2026-08-26  
**Status:** accepted  

---

## Context
In the packaging unit operations at PT Percetakan Dokumen Sekuriti, user accounts are divided into two distinct responsibilities: **Petugas** (packaging operators responsible for scanning and reporting daily production) and **Supervisor** (unit heads and administrators responsible for staff management, SAP database synchronization, and report validation). 

The application required a robust access control mechanism to prevent unauthorized access to administrative endpoints while ensuring that supervisors can manage staff records quickly from shared workstation terminals without cumbersome multi-page navigation or disruptive page reloads.

---

## Decision
1. Implement an explicit **Role-Based Access Control (RBAC)** architecture using a PHP backed Enum (`App\Enums\UserRole`) with two roles: `SUPERVISOR = 'supervisor'` and `PETUGAS = 'petugas'`.
2. Restrict supervisor routes and resources using the custom middleware `App\Http\Middleware\EnsureUserRole` aliased as `role` in `bootstrap/app.php`.
3. Consolidate all staff management operations (listing, debounced real-time searching, role/status filtering, creation, editing, active status toggling, and deletion) into a **single page surface** (`/petugas`) powered by `App\Livewire\Petugas\PetugasIndex`.
4. Replace traditional dedicated CRUD routes (`/petugas/create`, `/petugas/{id}/edit`, `/petugas/{id}/delete`) with centered reactive **Flux UI Modal Dialogs** (`<flux:modal name="petugas-form">` and `<flux:modal name="delete-confirm">`).
5. Enforce integrity guardrails: prohibit self-deactivation and self-deletion for the logged-in supervisor account, and block permanent deletion of accounts tied to production records.

---

## Consequences

### Positive
- **Streamlined Workflow**: Supervisors can create, update, or deactivate operator accounts in 2–4 clicks without losing their current table search, filter, or pagination context.
- **Zero Page Reloads**: Livewire 4 reactivity combined with Flux modals provides seamless and instant user feedback through toast notifications.
- **Data Integrity & Security**: Unauthorized role escalation is strictly blocked at the middleware layer (returning standard 403 Forbidden), and critical historical data links are safeguarded against accidental deletion.

### Negative
- **Component State Size**: The single Livewire component handles both table listing and modal state, requiring clear variable naming and reset methods to avoid state leakage between create and edit actions.

### Neutral
- Password updates in the edit modal are optional, requiring conditional validation logic that ignores the password field when left empty while enforcing minimum length when populated.
