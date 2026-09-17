# Perbaikan Sidebar, Permission Enforcement & Custom 403 Implementation Plan

## Repository Research (Temuan sebelum plan)

### A. Masalah Sidebar (Sub-Goal 1)
- Kondisi saat ini: CSS sidebar aktif (fix sebelumnya `.sidebar { overflow: hidden }` + `.nav-link { box-sizing border-box + border 2px transparent + `width: calc(100% - 0px)` menyebabkan visual sidebar "ngecil" tertekan karena width paksa.
- Root cause: Constraint `width: 100%` nav-link + border placeholder 2px membuat nav-link selalu menyesuaikan 100% dari parent yang sudah ada margin 10px kiri-kanan nav-item; kombinasi ini bikin tampilan sidebar terlihat sempit/mengecil.
- User request: Sidebar lebar menyesuaikan otomatis (tanpa 90%/100% paksa) TETAPI tetap menjaga bahwa class `nav-link.active gradien oranye TIDAK overflow keluar sidebar (tidak ada putih strip kiri).

### B. Masalah Permission-Role Tidak Berpengaruh (Sub-Goal 2A)
- `IsAutentikasiMiddleware` **hanya** melakukan `Auth::check()`. **TIDAK ADA pengecekan permission per route sama sekali. Method `User::hasPermission()` TIDAK pernah dipanggil dimanapun.
- Mapping kode permission (DASHBOARD_VIEW, CABANG_INDEX, dll.) di seed TIDAK ada hubungannya dengan route di `routes/web.php`.
- Role sync permission via RoleController edit TIDAK AKAN PERNAH berpengaruh karena tidak ada enforcement middleware yang mengecek `hasPermission()` sebelum request mencapai controller.
- Administrator bypass benar (via middleware `administrator` alias role name check) tapi untuk non-admin semuanya bebas akses semua modul.

### C. Masalah Jumlah User Role Index (Sub-Goal 2B)
- `Role::users()` HasMany relation benar di model Role sudah benar. `RoleController index sudah memanggil `withCount('users')`.
- Potensi penyebab count tampil 0: Tidak ada bug logic di withCount. BISA jadi masalah jika user dibuat role_id tidak sesuai UUID role (karena UserCreateRequest rules exists:role,id valid). Akan di cek ulang dan sekalian pastikan Role model juga punya UUID creating otomatis untuk role baru yang dibuat via form.

### D. Custom 403 Forbidden (Sub-Goal 3)
- Folder `resources/views/errors/` BELUM ADA sama sekali. Laravel akan menampilkan error page default.
- Perlu custom halaman 403 custom dengan design simpel, extend master layout, tanpa dekorasi (no sparkle, bintang, belah ketupat, lingkaran, bulat).
- Perlu `withExceptions` handler di bootstrap/app.php agar AuthorizationException / 403 di render sebagai view custom.

## Files and Modules

### Existing Files DIUBAH:
1. `resources/views/modules/layouts/components/style/css.blade.php` — Sidebar nav-link active indicator pakai `::before` pseudo-element absolute, hapus width constraint.
2. `app/Http/Middleware/PermissionMiddleware.php` — NEW: Check permission per route.
3. `bootstrap/app.php` — Daftarkan alias middleware permission + withExceptions render 403.
4. `routes/web.php` — Tambahkan `middleware(permission:XXX)` sesuai mapping modul.
5. `app/Models/Role.php` — Tambahkan `booted()` creating UUID otomatis (mirip User/AktivitasLog).

### File BARU dibuat:
6. `resources/views/errors/403.blade.php` — Halaman custom Forbidden simpel.

### Optional diperiksa:
7. `app/Http/Controllers/RoleController.php@index` — pastikan withCount users + eager load.

## Implementation Steps (berurutan, dependency order)

### Step 1: Fix Sidebar Width Auto-Adjust (Tanpa width 90%/100% paksa, tetap overflow active aman)
File target: [css.blade.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/resources/views/modules/layouts/components/style/css.blade.php)
- Hapus `.sidebar { overflow: hidden }` → ganti jadi `overflow-x: hidden; overflow-y: auto` (hanya potong horizontal, vertical tetap scroll jika menu panjang).
- Hapus nav-link `width: calc(100% - 0px)` dan `border: 2px solid transparent` dan `box-sizing: border-box` constraint.
- Untuk `.sidebar .nav-item .nav-link` KEMBALI ke natural width (tanpa width paksa). Tetap border-radius 0.55rem dll).
- Indikator ACTIVE putih sebelah kiri TIDAK PAKAI `border-left-width` pada nav-link (bikin box model berubah). GUNAKAN `::before` pseudo-element:
  ```css
  .sidebar .nav-item { position: relative; }
  .sidebar .nav-link.active::before {
    content: '';
    position: absolute;
    left: -10px; (sama dengan negasi margin nav-item 10px);
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 60%;
    background-color: #ffffff;
    border-radius: 0 2px 2px 0;
  }
  ```
- Gradient oranye active: tetap `.nav-link.active` normal width.
- Dengan pendekatan ini width nav-link natural → sidebar menyesuaikan otomatis tanpa paksa. Active indicator menggunakan pseudo element absolute di LUAR box nav-link tapi di dalam container sidebar (tidak menyebabkan overflow putih kiri ke luar sidebar karena overflow-x: hidden).

### Step 2: Buat PermissionMiddleware + Register Alias
File target: `app/Http/Middleware/PermissionMiddleware.php` (BARU) + `bootstrap/app.php`.
- PermissionMiddleware menerima 1 parameter string `$kodePermission`.
- Handle:
  - Jika tidak auth → redirect login.
  - Jika user `hasRole('Administrator') → bypass semua (return next).
  - Jika tidak punya via `$request->user()->hasPermission($kodePermission))` → lanjut.
  - Jika TIDAK PUNYA → `abort(403, 'Anda tidak memiliki izin akses.')
- Register alias `'permission' => PermissionMiddleware::class` di `bootstrap/app.php` withMiddleware.
- `withExceptions: Untuk 403:
  ```php
  $exceptions->render(function (AuthorizationException $e, Request $request) {
      if ($request->is('api/*') || $request->expectsJson()) return response()->json(...); 
      return response()->view('errors.403', [], 403);
  });
  ```
  Import `Illuminate\Auth\Access\AuthorizationException`.

### Step 3: Sinkronisasi Mapping Permission Code ke Setiap Route
File target: [routes/web.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/routes/web.php)
- Setiap `Route::resource` dan route individual dalam group `prefix('modules')` DAN dalam group middleware `autentikasi`.
- Daftar mapping:
  - GET /modules/dashboard → `middleware('permission:DASHBOARD_VIEW')`
  - Route::resource('cabang', ...) → `->middleware('permission:CABANG_INDEX|CABANG_CREATE|CABANG_UPDATE|CABANG_DELETE')` — tapi karena resource satu group, TIDAK BISA berbeda per action.
    → GUNAKAN grouping per-method atau: ALTERNATIF: Group `->middleware('permission:CABANG_*')` TAPI karena permission policy sudah ada granular INDEX/CREATE/UPDATE/DELETE terpisah.
    → PILIHAN TERBAIK sederhana: Buat middleware `PermissionMiddleware` bisa menerima multiple permission dengan format pipe, agar bisa `permission:CABANG_INDEX,CABANG_CREATE,CABANG_UPDATE,CABANG_DELETE`. Cek if salah satu SATU SAJA dimiliki → allow.
  - Mapping tabel:
    - cabang.* → permission:CABANG_INDEX,CABANG_CREATE,CABANG_UPDATE,CABANG_DELETE
    - anggota.* → permission:ANGGOTA_INDEX,ANGGOTA_CREATE,ANGGOTA_UPDATE,ANGGOTA_DELETE
    - jenis-simpanan.* → permission:JENIS_SIMPANAN_CRUD
    - jenis-pinjaman.* → permission:JENIS_PINJAMAN_CRUD
    - simpanan.* → permission:SIMPANAN_CRUD
    - pinjaman.* → permission:PINJAMAN_CRUD,PINJAMAN_APPROVE
    - angsuran.* → permission:ANGSURAN_CRUD
    - pembayaran-angsuran.* → permission:PEMBAYARAN_CRUD
    - laporan GET index → permission:LAPORAN_VIEW
    - laporan/export-pdf GET → permission:LAPORAN_EXPORT
    - users.* → permission:SYSTEM_USER_CRUD (SUDAH DALAM ADMINISTRATOR GROUP middleware group BISA TAMBAH permission TETAPI administrator middleware sudah role admin; untuk yang jelas).
    - roles.* → permission:SYSTEM_ROLE_CRUD
    - permissions.* → permission:SYSTEM_PERMISSION_CRUD
    - aktivitas-log → permission DALAM ADMIN group, TIDAK USAH permission tengah, admin only.

### Step 4: Tambahkan UUID creating ke Model Role, Pastikan Sinkron withCount Users
File target: [app/Models/Role.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Models/Role.php)
- Tambahkan `use Illuminate\Support\Str;`.
- Tambahkan `protected static function booted(): void { static::creating(fn($m) => $m->id = $m->id ?? (string) Str::uuid(); }`
- Pastikan `RoleController@index` query tetap `withCount('users')` dan eager load permissions. Diperlukan.

### Step 5: Buat Custom 403 View (Simpel Tanpa Dekorasi)
File target: `resources/views/errors/403.blade.php` (BARU)
- Struktur:
  - Extend `modules.layouts.master`
  - stack title '403 Akses Ditolak'
  - @push page-modules:
    - Container fluid → Row → Col md-8 mx-auto → Card shadow.
    - Card-body text-center py-5:
      - h2 text-orange fw-bold → display-4 → `403`
      - h5 text-gray mb-2 → "Akses Ditolak
      - p text-muted → "Anda tidak memiliki izin untuk mengakses halaman ini."
      - a btn btn-orange → href dashboard → Kembali ke Dashboard.
- **PENTING BATASAN: DILARANG KERAS menambahkan elemen dekoratif: sparkle, bintang, belah ketupat, lingkaran, bulat, icon dekoratif (hanya teks + tombol + card). TIDAK ADA bingkai bulat, TIDAK ADA elemen rounded-circle, TIDAK ADA border radius berlebih.

## Dependencies and Considerations
- PermissionMiddleware harus SUPPORT multiple kode permission dipisah KOMA (`,`), check **OR** logic: jika user punya SALAH SATU yang diminta → allow).
- Administrator Bypass: Middleware permission harus bypass otomatis jika user role Administrator (sesuai User hasRole method).
- Semua route yang dalam `Route::middleware(['administrator']) group (users/roles/permissions/aktivitas-log) TIDAK USAH tambah permission middleware (administrator saja cukup.
- Sidebar width alami, sidebar SB Admin 2 default width 14rem. Tanpa width paksa → akan kembali natural.

## Validation
1. Sidebar: Buka browser → sidebar menu tidak "ngecil" (kembali normal. Menu active → indikator putih kiri tampil. Gradient oranye TIDAK overflow ke luar sidebar (overflow-x:hidden).
2. Permission: Login sebagai teller@koperasi.test → buka URL `/modules/users` → DAPAT error 403 custom (bukan default). Buka `/modules/cabang` → BISA akses (teller punya).
3. Roles count users: Login admin → Roles → data  Role Teller: 2 user. Role KC: 2 user. Role Admin: 2 user. Tambah user baru role Teller via form → refresh halaman roles index count jadi 3.
4. Buat role BARU via form Roles Create → UUID otomatis tersimpan tanpa error.
5. 403: abort(403) → halaman custom layout master tampil tanpa ornamen dekoratif.
6. GetDiagnostics return kosong (no error lint).

## Risiko
- Risk: Route group middleware permission terlalu ketat mengunci halaman dashboard. Fallback: Jika user coba akses route langsung tanpa permission → 403 custom.
- Risk: Sidebar `::before` absolute salah posisi indicator kiri. → handle dengan `.nav-item { position: relative; } benar. Fallback adjust `left: calc(var(--nav-margin-negasi)).
- Risk: Role count users 0. Fallback: Pastikan User model role_id exists valid UUID cocok.
