# ADR-007: High-Speed Continuous Barcode Scanning, Focus Management, and Inline Table Editing Architecture

**Date:** 2026-08-26  
**Status:** accepted  

## Context
At the packaging station (*Unit Pengemasan*) of PT Percetakan Dokumen Sekuriti, operators process hundreds of physical machine cards (*Kartu Mesin Produksi*) per shift. Each card contains a Code 128 barcode encoding the SAP Production Order number (`po_number`).

Traditional web application paradigms with modal dialogs, separate edit pages, or mouse-dependent navigation create severe cognitive friction and operational bottlenecks on the factory floor. The scanning workflow requires sub-second response times, automated focus jumping between inputs, instant audio feedback, inline table corrections, and zero page reloads.

## Decision
1. **Automated Keyboard-Only Loop via Alpine.js**:
   - Pair Alpine.js `x-ref` and window event listeners (`@focus-scan-input.window`, `@focus-qty-input.window`) with Livewire actions.
   - On barcode scan + Enter $\rightarrow$ Livewire looks up SAP order $\rightarrow$ dispatches `@focus-qty-input` (auto-focusing and selecting the quantity input).
   - On quantity input + Enter $\rightarrow$ Livewire persists `DailyReportItem` snapshot $\rightarrow$ recalculates aggregates $\rightarrow$ dispatches `@focus-scan-input` (instantly returning focus to barcode scan input).
2. **Native Web Audio API Sound Generation**:
   - Synthesize dual-tone audio cues directly via browser `AudioContext` oscillators (high dual-tone melody for valid scans, low sawtooth buzzer for missing orders).
   - Eliminates HTTP audio asset download latency and caching failures.
3. **Modal-Free Inline Editable Table**:
   - Render all scanned rows on the same page with direct `<input type="number">` elements bound to Livewire `updateItemQuantity($itemId, $newQty)`.
   - Update aggregates dynamically on Enter or blur without page refreshes.
4. **Lifecycle Locking & Physical Print Readiness**:
   - Support report finalization from `draft` to `submitted`.
   - Lock operator editing on submitted reports while allowing supervisors to reopen if corrections are needed.
   - Provide a dedicated printable route `/reports/{report_code}/print` with standard A4 layout, official headers, metadata grid, and two-column signature fields.

## Consequences

### Positive
- **Extreme Operator Productivity**: 100% keyboard and scanner workflow enables scanning and logging hundreds of orders per shift without touching the mouse.
- **Immediate Error Rectification**: Inline table editing allows instant quantity corrections in 2 keypresses without dialog disruption.
- **Clean Audit Trail**: Snapshotted item attributes preserve data validity for supervisor sign-off and physical A4 printing.

### Negative
- **Browser Audio Permissions**: Web Audio API requires user interaction on the page before sound can play in strict browser environments (gracefully caught via fallback).

### Neutral
- Physical print views use native browser `window.print()` styling via `@media print` without external PDF compilation overhead.
