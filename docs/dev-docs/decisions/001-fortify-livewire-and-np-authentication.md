# ADR-001: Fortify Livewire & Multi-Identifier NP Authentication

**Date:** 2026-08-26  
**Status:** accepted  

---

## Context
In the shop-floor environment of PT Percetakan Dokumen Sekuriti (Unit Pengemasan), operators and supervisors interact with the web system on shared workstation terminals during 3 distinct operational shifts. Packaging operators are accustomed to identifying themselves using their 4 to 6 character Nomor Pegawai (NP, e.g. `7426` or `T0087`), while supervisors and administrators may also use usernames or corporate emails. The authentication mechanism needed to be secure, resilient against brute-force attacks, fast to render, and supportive of role-based authorization without introducing unnecessary frontend SPA complexity.

---

## Decision
1. Utilize **Laravel Fortify** as the secure authentication backend combined with a dedicated **Livewire 4 Component** (`App\Livewire\Auth\Login`) for instantaneous user interaction and validation feedback.
2. Allow multi-identifier lookup in the single login field by querying `nip` (Nomor Pegawai), `username`, and `email` columns simultaneously.
3. Enforce active account status checks (`is_active === true`) during both Livewire and Fortify authentication pipelines.
4. Integrate IP + login identifier based rate-limiting (5 attempts per minute) with clear, humanized Indonesian notification text.
5. Create a `ShiftHelper` to calculate operational shifts (Shift 1: 07:00–15:00, Shift 2: 15:00–23:00, Shift 3: 23:00–07:00 WIB) and render live badges directly on the top application header.

---

## Consequences

### Positive
- **Reduced Operator Friction**: Operators can log in swiftly using their familiar NP without having to memorize or type long email addresses.
- **Security & Session Hygiene**: Native rate limiting, CSRF protection, and session invalidation upon logout prevent unauthorized access between shift transitions.
- **Unified UI Componentry**: Flux UI components provide unified dark/light theming, reactive toast notifications, and responsive navigation out of the box.

### Negative
- Unique constraint lookups must span three columns (`nip`, `username`, `email`) on user creation/updates.
