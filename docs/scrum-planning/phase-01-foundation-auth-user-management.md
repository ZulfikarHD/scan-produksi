# Phase 01 - Foundation, Authentication & User Management (Data Petugas)

**Sprint Goal**: Membangun fondasi arsitektur aplikasi (Laravel + Inertia Vue 3 + Wayfinder), sistem autentikasi pengguna dengan role-based access control (Supervisor & Petugas), serta manajemen data akun petugas (CRUD Data Petugas).

**Estimated Duration**: 3-4 days

**Depends on**: Initial Project Setup

---

## User Stories

### US-1.1: Base Layout & Theme Architecture

**As a** system user (Petugas / Supervisor)  
**I want** a responsive, clean, and modern web application layout with sidebar navigation, header profile menu, and quick feedback alerts  
**So that** I can easily access all production modules with clear operational status

**Acceptance Criteria:**
- [ ] Main application shell with responsive sidebar (collapsible on mobile/desktop).
- [ ] Active navigation state highlight based on current route via Wayfinder.
- [ ] User profile dropdown with logout button, display name, and active role badge (`Supervisor` / `Petugas`).
- [ ] Global flash toast notification system (success, error, warning, info) integrated with Inertia shared props.
- [ ] Timezone localized to `Asia/Jakarta` (WIB) with current date/shift banner on top header.
- [ ] Accessible color palette complying with modern UI/UX standards and dark/light contrast.

**UI Specs:**
- Sidebar with menu items: Dashboard, Update Database, Buat Laporan, Lihat Laporan, Data Petugas, Pengaturan.
- Top bar showing current shift info (Shift 1 / 2 / 3), real-time WIB clock, and user avatar.
- Toast container at top-right corner.

**Tasks:**
1. Setup base Vue layout `resources/js/layouts/AppLayout.vue` & `resources/js/layouts/GuestLayout.vue`.
2. Configure Inertia Shared Props for flash messages, authenticated user, and global app settings in `app/Http/Middleware/HandleInertiaRequests.php`.
3. Create reusable UI components: `Button.vue`, `Input.vue`, `Badge.vue`, `Modal.vue`, `Toast.vue`, `Table.vue`.
4. Configure Wayfinder routing helpers on Vue frontend (`import { route } from '@/wayfinder'`).
5. Write layout unit & component render tests.

---

### US-1.2: Authentication & Secure Session Login

**As a** petugas or supervisor  
**I want** to log in securely using my username/email and password  
**So that** only authorized packaging personnel can access production documentation and modify records

**Acceptance Criteria:**
- [ ] Dedicated Login page with username/email and password input fields.
- [ ] Remember Me checkbox functionality.
- [ ] Validation: username/email required, password required.
- [ ] Rate limiting on login attempts (throttle 5 attempts per minute with clear error message).
- [ ] Secure password hashing (Argon2id/Bcrypt).
- [ ] Redirect authenticated users to Dashboard upon successful login.
- [ ] Logout action that invalidates session and clears CSRF token.
- [ ] Guest middleware preventing logged-in users from accessing the login page.

**UI Specs:**
- Centered card layout with PT Percetakan Dokumen Sekuriti logo and branding.
- Clean input fields with toggle password visibility button.
- Inline field validation error feedback.
- Loading state on login button submit.

**Tasks:**
1. Configure Fortify / Auth controller & routes for Session Login.
2. Create Form Request `app/Http/Requests/Auth/LoginRequest.php`.
3. Create Login Page `resources/js/pages/Auth/Login.vue`.
4. Implement secure session invalidation in `app/Http/Controllers/Auth/AuthenticatedSessionController.php`.
5. Write Pest feature tests for valid login, invalid credentials, rate limiting, and logout in `tests/Feature/Auth/AuthenticationTest.php`.

---

### US-1.3: Role & Permission Access Control

**As a** system administrator / supervisor  
**I want** strict role-based access control between `Supervisor` (Kepala Unit/Admin) and `Petugas` (Operator Kemas)  
**So that** restricted actions (e.g. Master Data SAP import, user management, and critical report deletion) are protected

**Acceptance Criteria:**
- [ ] User migration and model extended with `role` enum (`supervisor`, `petugas`) and `is_active` boolean flag.
- [ ] Middleware `EnsureUserRole` created to protect supervisor-only endpoints.
- [ ] Inactive users cannot log in (shows account suspended warning).
- [ ] Front-end navigation items conditionally rendered based on user role capabilities.
- [ ] Authorization policies defined for Report and Master Data operations.

**Tasks:**
1. Create migration `database/migrations/xxxx_xx_xx_add_role_and_status_to_users_table.php`.
2. Create Enum `app/Enums/UserRole.php` (`SUPERVISOR = 'supervisor'`, `PETUGAS = 'petugas'`).
3. Create Middleware `app/Http/Middleware/EnsureUserRole.php`.
4. Register middleware alias in `bootstrap/app.php`.
5. Write Pest feature tests for role authorization in `tests/Feature/Auth/RoleAuthorizationTest.php`.

---

### US-1.4: Petugas Management - List & Search (Data Petugas)

**As a** supervisor  
**I want** to view a comprehensive list of all packaging personnel with search and filter capabilities  
**So that** I can monitor active operators and maintain staff records accurately

**Acceptance Criteria:**
- [ ] List all staff with: Name, Username, NIP / Employee ID, Email, Role, Status (`Aktif` / `Nonaktif`), and Last Login timestamp.
- [ ] Real-time search query by name, username, or NIP.
- [ ] Filter by role (`All`, `Supervisor`, `Petugas`) and status (`All`, `Aktif`, `Nonaktif`).
- [ ] Server-side pagination (10/25/50 per page).
- [ ] Active status badges with distinct visual indicators (Green: Aktif, Gray: Nonaktif).

**UI Specs:**
- Data table with sorting headers, search input bar, filter dropdowns, and pagination footer.
- Top action button: "Tambah Petugas Baru".
- Row actions dropdown: Edit, Toggle Status, Reset Password, Hapus.

**Tasks:**
1. Create `app/Http/Controllers/PetugasController.php` with `index` method.
2. Create Service `app/Services/UserService.php` for user querying, filtering, and data manipulation.
3. Create Inertia Page `resources/js/pages/Petugas/Index.vue`.
4. Create reusable `DataTable.vue` component with debounce search.
5. Write Pest feature tests for petugas list, search, and filtering in `tests/Feature/Petugas/PetugasIndexTest.php`.

---

### US-1.5: Petugas Management - Create, Edit & Deactivate

**As a** supervisor  
**I want** to add new operators, update their profile information, and toggle active status  
**So that** the system reflects current packaging unit staffing changes without losing historical audit logs

**Acceptance Criteria:**
- [ ] Create Petugas form with fields:
  - Nama Lengkap (required, max 100)
  - NIP / ID Karyawan (required, unique, max 30)
  - Username (required, unique, alpha_dash, max 50)
  - Email (required, email, unique)
  - Role (required: `supervisor` / `petugas`)
  - Password (required on create, min 8 chars with mixed case & number)
- [ ] Edit Petugas form allows updating name, NIP, email, role, and optional password change.
- [ ] Toggle active/inactive status with instant action button.
- [ ] Soft deletion or protected deletion: cannot delete user who is attached to existing production reports.
- [ ] Form submission feedback via Inertia with toast notifications and inline errors.

**UI Specs:**
- Modal dialog or dedicated page for Create / Edit.
- Password generator / visibility toggle helper.
- Delete confirmation modal explaining constraints.

**Tasks:**
1. Create Form Requests `app/Http/Requests/Petugas/StorePetugasRequest.php` and `UpdatePetugasRequest.php`.
2. Implement `store`, `update`, `destroy`, and `toggleStatus` methods in `app/Http/Controllers/PetugasController.php`.
3. Create `resources/js/pages/Petugas/Create.vue` and `Edit.vue` (or unified `PetugasFormModal.vue`).
4. Implement user factory and seeder for initial supervisor and operator accounts.
5. Write Pest feature tests for Petugas CRUD lifecycle in `tests/Feature/Petugas/PetugasCrudTest.php`.

---

## Technical Notes

### User Model & Role Enum

```php
// app/Enums/UserRole.php
namespace App\Enums;

enum UserRole: string
{
    case SUPERVISOR = 'supervisor';
    case PETUGAS = 'petugas';

    public function label(): string
    {
        return match($this) {
            self::SUPERVISOR => 'Supervisor / Ka. Unit',
            self::PETUGAS => 'Petugas Kemas',
        };
    }
}
```

```php
// app/Models/User.php
namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nip',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSupervisor(): bool
    {
        return $this->role === UserRole::SUPERVISOR;
    }
}
```

### Store Petugas Request

```php
// app/Http/Requests/Petugas/StorePetugasRequest.php
namespace App\Http\Requests\Petugas;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StorePetugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSupervisor() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'nip' => ['required', 'string', 'max:30', 'unique:users,nip'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', new Enum(UserRole::class)],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'is_active' => ['boolean'],
        ];
    }
}
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | US-1.1 Base Layout & Theme Architecture | 5 | Must |
| 2 | US-1.2 Authentication & Secure Session Login | 5 | Must |
| 3 | US-1.3 Role & Permission Access Control | 5 | Must |
| 4 | US-1.4 Petugas Management - List & Search | 5 | Must |
| 5 | US-1.5 Petugas Management - Create, Edit & Deactivate | 6 | Must |

**Total Story Points: 26**

---

## Dependencies

| Dependency | From |
|---|---|
| Laravel 12+ framework with session auth & Fortify | Initial Setup |
| Inertia Vue 3 + Tailwind CSS + Wayfinder | Frontend Architecture |
| SQLite/MySQL Database connection | Environment Configuration |

---

## Risks

| Risk | Mitigation |
|---|---|
| Accidental deletion of petugas accounts linked to historical reports | Use soft delete and foreign key integrity checks to block deletion if production records exist |
| Unauthorized access to administrative modules | Apply strict `EnsureUserRole` middleware checks both on route endpoints and UI navigation guards |
