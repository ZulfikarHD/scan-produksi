# Phase 01 - Foundation, Authentication & User Management (Data Petugas)

**Sprint Goal**: Membangun fondasi arsitektur aplikasi (Laravel 13 + Livewire 4 + Flux UI + Blaze), sistem autentikasi pengguna dengan Fortify & role-based access control (Supervisor & Petugas), serta manajemen data akun petugas (CRUD Data Petugas) berbasis Livewire Component.

**Estimated Duration**: 3-4 days

**Depends on**: Initial Project Setup

---

## User Stories

### US-1.1: Base Layout & Flux UI Shell Architecture

**As a** system user (Petugas / Supervisor)  
**I want** a responsive, clean, and modern web application layout with Flux UI sidebar navigation, header profile menu, and live feedback toasts  
**So that** I can easily access all production modules with clear operational status

**Acceptance Criteria:**
- [ ] Main application shell using Flux UI layout components (`<flux:sidebar>`, `<flux:header>`, `<flux:main>`).
- [ ] Active navigation state highlight based on current route (`route('dashboard')`, etc.).
- [ ] User profile dropdown (`<flux:profile>`) with logout action, display name, and active role badge (`Supervisor` / `Petugas`).
- [ ] Global toast notification system powered by Flux UI (`<flux:toast />` / `Flux::toast()`).
- [ ] Timezone localized to `Asia/Jakarta` (WIB) with current date and shift banner on top header.
- [ ] Accessible dark/light mode support integrated with Flux UI and Tailwind CSS v4.
- [ ] Optimized Blade component rendering using Blaze (`@blaze`).

**UI Specs:**
- Sidebar with menu items: Dashboard, Update Database, Buat Laporan, Lihat Laporan, Data Petugas, Pengaturan.
- Header showing current shift info (Shift 1 / 2 / 3), real-time WIB clock, and user avatar.
- Toast notifications appearing at top-right corner.

**Tasks:**
1. Configure base layout `resources/views/layouts/app.blade.php` with Flux UI layout components and `@blaze` optimizations.
2. Setup navigation links in `resources/views/layouts/app/sidebar.blade.php` and `resources/views/layouts/app/header.blade.php`.
3. Create shift helper and current time banner in header.
4. Test responsive sidebar collapse on mobile and desktop viewports.
5. Write layout unit & component render tests in `tests/Feature/LayoutTest.php`.

---

### US-1.2: Authentication & Secure Session Login with Fortify & Livewire

**As a** petugas or supervisor  
**I want** to log in securely using my username/email and password via Livewire  
**So that** only authorized packaging personnel can access production documentation and modify records

**Acceptance Criteria:**
- [ ] Dedicated Login page utilizing Livewire 4 component (`App\Livewire\Auth\Login`).
- [ ] Input fields: Username/Email and Password with Remember Me checkbox.
- [ ] Validation: username/email required, password required with instant Livewire feedback.
- [ ] Rate limiting on login attempts (throttle 5 attempts per minute with clear error message).
- [ ] Secure password hashing via Laravel Fortify.
- [ ] Redirect authenticated users to Dashboard upon successful login.
- [ ] Logout action via `App\Livewire\Actions\Logout` that invalidates session and regenerates CSRF token.
- [ ] Guest middleware preventing logged-in users from accessing the login page.

**UI Specs:**
- Centered card layout with PT Percetakan Dokumen Sekuriti branding and logo.
- Flux input fields (`<flux:input>`) with password view toggle and inline validation error messages.
- Loading indicator on login submit button (`wire:loading`).

**Tasks:**
1. Configure Fortify service provider `app/Providers/FortifyServiceProvider.php` for Livewire auth views.
2. Implement Livewire Component `app/Livewire/Auth/Login.php` and view `resources/views/livewire/auth/login.blade.php`.
3. Implement secure session invalidation in `app/Livewire/Actions/Logout.php`.
4. Write Pest feature tests for valid login, invalid credentials, rate limiting, and logout in `tests/Feature/Auth/AuthenticationTest.php`.

---

### US-1.3: Role & Permission Access Control

**As a** system administrator / supervisor  
**I want** strict role-based access control between `Supervisor` (Kepala Unit/Admin) and `Petugas` (Operator Kemas)  
**So that** restricted actions (e.g. Master Data SAP import, user management, and critical report deletion) are protected

**Acceptance Criteria:**
- [ ] User migration and model extended with `role` enum (`supervisor`, `petugas`) and `is_active` boolean flag.
- [ ] Middleware `EnsureUserRole` created to protect supervisor-only endpoints and Livewire components.
- [ ] Inactive users cannot log in (shows account suspended notification).
- [ ] Sidebar navigation items conditionally rendered using Blade `@if(auth()->user()->isSupervisor())`.
- [ ] Authorization policies defined for Report and Master Data operations.

**Tasks:**
1. Create migration `database/migrations/xxxx_xx_xx_add_role_and_status_to_users_table.php`.
2. Create Enum `app/Enums/UserRole.php` (`SUPERVISOR = 'supervisor'`, `PETUGAS = 'petugas'`).
3. Create Middleware `app/Http/Middleware/EnsureUserRole.php` and register alias in `bootstrap/app.php`.
4. Write Pest feature tests for role authorization in `tests/Feature/Auth/RoleAuthorizationTest.php`.

---

### US-1.4: Petugas Management - Livewire Data Table (Data Petugas)

**As a** supervisor  
**I want** to view a comprehensive list of all packaging personnel with real-time search and filter capabilities  
**So that** I can monitor active operators and maintain staff records accurately

**Acceptance Criteria:**
- [ ] Livewire Component `App\Livewire\Petugas\PetugasIndex` displaying all staff.
- [ ] Table columns: Name, Username, NIP / Employee ID, Email, Role, Status (`Aktif` / `Nonaktif`), and Last Login.
- [ ] Real-time search by name, username, or NIP using `wire:model.live.debounce.300ms`.
- [ ] Filter by role (`All`, `Supervisor`, `Petugas`) and status (`All`, `Aktif`, `Nonaktif`).
- [ ] Pagination using Livewire `WithPagination` (10/25/50 per page).
- [ ] Active status badges using `<flux:badge color="green">` (Aktif) and `<flux:badge color="zinc">` (Nonaktif).

**UI Specs:**
- Flux data table (`<flux:table>`) with column sort indicators.
- Search input bar and filter dropdowns at the top toolbar.
- Top action button: `<flux:button variant="primary" icon="plus">Tambah Petugas</flux:button>`.
- Row action menu (`<flux:dropdown>`) with Edit, Toggle Status, and Delete actions.

**Tasks:**
1. Create Livewire Component `app/Livewire/Petugas/PetugasIndex.php` and view `resources/views/livewire/petugas/index.blade.php`.
2. Implement search and role/status query filtering with query string synchronization.
3. Write Pest feature tests for petugas list, search, and filtering in `tests/Feature/Petugas/PetugasIndexTest.php`.

---

### US-1.5: Petugas Management - Create, Edit & Deactivate (Modal Form)

**As a** supervisor  
**I want** to add new operators, update their profile information, and toggle active status using Flux modals  
**So that** the system reflects current packaging unit staffing changes without page reloads

**Acceptance Criteria:**
- [ ] Create/Edit Petugas modal (`<flux:modal name="petugas-form">`) with fields:
  - Nama Lengkap (required, max 100)
  - NIP / ID Karyawan (required, unique, max 30)
  - Username (required, unique, alpha_dash, max 50)
  - Email (required, email, unique)
  - Role (required: `supervisor` / `petugas`)
  - Password (required on create, min 8 chars; optional on edit)
- [ ] Instant toggle active/inactive status button with confirmation.
- [ ] Protected deletion: cannot delete user who has authored existing production reports.
- [ ] Toast notification on success (`Flux::toast('Data petugas berhasil disimpan.')`).

**UI Specs:**
- Modal dialog with Flux form inputs (`<flux:field>`, `<flux:input>`, `<flux:select>`).
- Delete confirmation dialog (`<flux:modal name="delete-confirm">`).

**Tasks:**
1. Implement modal state and save logic in `app/Livewire/Petugas/PetugasIndex.php` (or dedicated Form component).
2. Create Form Request / Livewire Validation rules in `App\Livewire\Forms\PetugasForm`.
3. Implement user seeder for default Supervisor and Petugas accounts.
4. Write Pest feature tests for Petugas CRUD lifecycle in `tests/Feature/Petugas/PetugasCrudTest.php`.

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

### Livewire Petugas Component

```php
// app/Livewire/Petugas/PetugasIndex.php
namespace App\Livewire\Petugas;

use App\Enums\UserRole;
use App\Models\User;
use Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PetugasIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = '';

    #[Url]
    public string $status = '';

    public ?User $editingUser = null;
    public string $name = '';
    public string $nip = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $userRole = 'petugas';
    public bool $isActive = true;

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => 
                $sub->where('name', 'like', "%{$this->search}%")
                    ->orWhere('username', 'like', "%{$this->search}%")
                    ->orWhere('nip', 'like', "%{$this->search}%")
            ))
            ->when($this->role, fn ($q) => $q->where('role', $this->role))
            ->when($this->status !== '', fn ($q) => $q->where('is_active', $this->status === '1'))
            ->latest()
            ->paginate(10);

        return view('livewire.petugas.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
        ]);
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'nip' => ['required', 'string', 'max:30', 'unique:users,nip,' . $this->editingUser?->id],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username,' . $this->editingUser?->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $this->editingUser?->id],
            'userRole' => ['required'],
            'password' => [$this->editingUser ? 'nullable' : 'required', 'min:8'],
        ]);

        $data = [
            'name' => $this->name,
            'nip' => $this->nip,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->userRole,
            'is_active' => $this->isActive,
        ];

        if (!empty($this->password)) {
            $data['password'] = bcrypt($this->password);
        }

        User::updateOrCreate(['id' => $this->editingUser?->id], $data);

        $this->modal('petugas-form')->close();
        Flux::toast('Data petugas berhasil disimpan.', variant: 'success');
        $this->reset(['editingUser', 'name', 'nip', 'username', 'email', 'password', 'userRole', 'isActive']);
    }
}
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|---|---|---|
| 1 | US-1.1 Base Layout & Flux UI Shell Architecture | 5 | Must |
| 2 | US-1.2 Authentication & Secure Session Login with Fortify & Livewire | 5 | Must |
| 3 | US-1.3 Role & Permission Access Control | 5 | Must |
| 4 | US-1.4 Petugas Management - Livewire Data Table | 5 | Must |
| 5 | US-1.5 Petugas Management - Create, Edit & Deactivate Modal | 6 | Must |

**Total Story Points: 26**

---

## Dependencies

| Dependency | From |
|---|---|
| Laravel 13 + Livewire 4 + Flux UI + Blaze | Initial Setup |
| Fortify authentication backend | Starter kit |
| SQLite/MySQL Database connection | Environment Configuration |

---

## Risks

| Risk | Mitigation |
|---|---|
| Accidental deletion of petugas accounts linked to historical reports | Use foreign key constraints and block deletion if production reports exist |
| Unauthorized access to administrative modules | Apply strict `EnsureUserRole` middleware checks on Livewire component mount and routes |
