# ADR-008: Daily Report Verification, Correction Window, and Streaming Export Architecture

**Date:** 2026-08-26  
**Status:** accepted  

## Context
Production reports compiled by operators during packaging shifts serve as legally binding physical and digital records of stamped tax goods (e.g., *Pita Cukai Hasil Tembakau* / PCHT). To maintain chain-of-custody integrity, the application requires:
1. Formal supervisor sign-off and verification that locks reports permanently.
2. A controlled correction window allowing operators to rectify clerical errors without supervisor intervention within 24 hours of creation.
3. Protected supervisor-only deletion with cascading order status restoration.
4. Memory-safe large-scale export to Excel without causing Out-of-Memory (OOM) fatal crashes on production servers.

## Decision
1. **Authorization & 24-Hour Edit Window**:
   - Encapsulate access rules within `DailyReportPolicy`.
   - Allow Petugas to edit `SUBMITTED` reports if `user_id === auth()->id()`, report is not `VERIFIED`, and `created_at >= now()->subHours(24)`.
   - Supervisors retain full override authority to edit or delete any unverified report at any time.
2. **Supervisor Verification Locking**:
   - Transition status from `SUBMITTED` $\rightarrow$ `VERIFIED`.
   - Persist `supervisor_id` and timestamp `verified_at`.
   - Render a verification badge/ribbon on both digital views and physical A4 printouts.
3. **OpenSpout XLSX Streaming**:
   - Utilize `OpenSpout` streaming writer for Excel file generation via `ReportExportController::export`.
   - Stream rows directly to `php://output` / temporary file buffer to keep memory consumption $<10\text{MB}$ even across hundreds of thousands of rows.
4. **Physical Print & PDF Compatibility**:
   - Provide standard portrait A4 layout with clean monochrome border styles, signature blocks, and print CSS `@media print` rules removing navigational headers.

## Consequences

### Positive
- **Tamper Protection**: Verified reports cannot be manipulated, guaranteeing compliance with internal audit requirements.
- **Operator Autonomy with Guardrails**: The 24-hour edit grace period enables quick self-corrections of typo counts without supervisor escalation.
- **Server Stability**: Streaming XLSX prevents memory spikes during shift-end mass export cycles.

### Negative
- **Strict 24h Lockout**: Operators needing corrections after 24 hours must request supervisor action.

### Neutral
- Print layouts default to native browser print dialogues, minimizing reliance on bulky headless browser rendering servers.
