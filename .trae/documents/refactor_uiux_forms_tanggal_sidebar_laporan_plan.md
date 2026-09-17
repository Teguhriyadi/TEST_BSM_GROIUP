# Refactor UI UX Lengkap (Form, Tanggal Carbon ID, Sidebar, Laporan Default) Implementation Plan

## Repository Research
Total 4 area refactor utama berdasarkan request user terbaru:

### Area 1 — Form Validation + Placeholder (17 Form Blade Files total)
- Saat ini: **72 atribut `required` tersebar di semua create/edit** (grep result <input|select|textarea required> = 72 line). User minta **hapus HTML required** karena validasi sudah ditangani Form Request (Create/Update Request terpisah per folder) dengan response redirect + old() + SweetAlert error toast.
- Saat ini: **BANYAK input TANPA `placeholder`** (contoh: anggota/create.blade.php input no_anggota, nik, nama, alamat, no_hp — tidak ada placeholder panduan isi). User minta **setiap input/textarea diberi placeholder** yang mendeskripsikan format expected (misal: NIK → "Masukkan 16 digit NIK sesuai KTP", nama → "Masukkan nama lengkap anggota", nomor HP → "Contoh: 081234567890", nominal → "Masukkan nominal dalam Rupiah tanpa titik/koma", dll).
- Files terlibat: `anggota/{create,edit}.blade.php`, `cabang/{create,edit}.blade.php`, `jenis-simpanan/{create,edit}.blade.php`, `jenis-pinjaman/{create,edit}.blade.php`, `simpanan/{create,edit}.blade.php`, `pinjaman/{create,edit}.blade.php`, `angsuran/{create,edit}.blade.php`, `pembayaran-angsuran/{create,edit}.blade.php`, `laporan/index.blade.php` = TOTAL 17 file form + filter laporan.

### Area 2 — Format Tanggal Carbon Bahasa Indonesia
- Saat ini: SEMUA tampilan tanggal menggunakan format `d/m/Y` angka (contoh: `13/09/2020`) di dashboard, 8 module index, laporan index + pdf (grep result 58 line format date).
- User minta: "dibikin tanggalan aja ngga sih kaya 13 September 2020, atau 13 September 2020 10:01:41 intinya pake carbon kalau ada tanggalan".
- Strategy:
  - Buat **Blade Custom Directive** di `AppServiceProvider.php boot()`: `@tanggal($date)` → format "13 September 2020", `@tanggalWaktu($date)` → "13 September 2020 10:01:41".
  - Directive otomatis detek apakah parameter adalah Carbon instance / string / null (fallback `-`).
  - Gunakan `Carbon::setLocale('id')` + `->isoFormat('D MMMM Y')` / `->isoFormat('D MMMM Y HH:mm:ss')` agar nama bulan otomatis Bahasa Indonesia (Januari, Februari, ..., Desember).
  - Replace SEMUA 58 line tampilan tanggal (yang sekarang `is_object($x->kolom) ? $x->kolom->format('d/m/Y') : date('d/m/Y', strtotime($x->kolom))`) → cukup `@tanggal($x->kolom)`.
  - Khusus `created_at` / `updated_at` jika muncul → pakai `@tanggalWaktu`.

### Area 3 — Sidebar Elegant + Active State Route
- Saat ini di [sidebar.blade.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/resources/views/modules/layouts/components/views/sidebar.blade.php):
  - Sidebar heading font-size cuma **0.68rem** (terlalu kecil = user minta "jangan di bikin kecil-kecil").
  - Sidebar brand text font-size 0.85rem (agak kecil).
  - **SEMUA `<li class="nav-item">` + `<a class="nav-link">` TIDAK ADA LOGIC ACTIVE STATE SAMA SEKALI** → tidak peduli route apa yang dibuka, semua item plain biru (user bilang "kalau sedang sesuai route / url nya, harusnya jadi kaya ada border background sidebarnya").
- Target setelah di-upgrade:
  - Font sidebar heading **0.8rem** (naik 2 level), sidebar brand-text **1rem / tidak kecil**.
  - Spasi nav-link lebih nyaman (padding kiri/kanan/atas/bawah ditambah, jangan rapat).
  - Logic Active State: Pakai `request()->routeIs('xxx.*')` (wildcard semua route di dalam modul itu). Contoh: `request()->routeIs('cabang.*')` = TRUE untuk cabang.index, cabang.create, cabang.edit, cabang.store, cabang.update, cabang.destroy. Jadi ketika user buka Tambah Cabang / Edit Cabang → menu Cabang di sidebar TETAP AKTIF (bukan cuma index).
  - Style `.nav-link.active`: background **gradient oranye solid (#f97316)** dengan border left 4px lebih tebal oranye, text putih bold, icon berwarna putih. Jangan transparan — harus kelihatan jelas "ini yang lagi dibuka".
  - Sidebar Toggle button diperbesar sedikit (tidak kecil).

### Area 4 — Laporan Default Awal s/d Akhir Bulan
- Saat ini di [LaporanController.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Http/Controllers/LaporanController.php):
  - `index()` line 19-20: `$tanggal_awal = $request->input('tanggal_awal'); $tanggal_akhir = $request->input('tanggal_akhir');` → KALAU USER TIDAK ISI = NULL → SEMUA DATA TERBUKA (bukan estimasi awal-akhir bulan).
  - `exportPdf()` line 67-68: SAMA PERSIS MASALAHNYA — kalau user pilih Export PDF tanpa isi tanggal = download SEMUA DATA, dan di `laporan/index.blade.php` tanggal input juga kosong (tidak terisi default).
- User minta: "dibikin start awal nya (awal bulan), dan akhir nya (akhir bulan), jadi kaya estimasi dari awal - akhir aja gitu".
- Implementasi:
  - Controller `index()`: Jika `$tanggal_awal` KOSONG → isi default `Carbon::now()->startOfMonth()->toDateString()` (tanggal 1 bulan ini). Jika `$tanggal_akhir` KOSONG → isi default `Carbon::now()->endOfMonth()->toDateString()` (tanggal 30/31 bulan ini).
  - Controller `exportPdf()`: LOGIC SAMA PERSIS — default startOfMonth / endOfMonth bila kosong.
  - View `laporan/index.blade.php`: input tanggal default valuenya sudah otomatis dari `compact('tanggal_awal','tanggal_akhir')` dari controller (sekarang pakai `request(...)` yang salah → diganti pakai variable `$tanggal_awal` / `$tanggal_akhir` yang sudah di-set default di controller).
  - TETAP FLEKSIBEL: User BISA UBAH tanggal kapan saja (bukan paksa). Cuma default value kalau belum diisi = awal-akhir bulan ini, sehingga pertama kali buka laporan tidak kebuka semua data.

## Files and Modules (Rincian Detail)

| # | File Path | Perubahan |
|---|---|---|
| 1 | `app/Providers/AppServiceProvider.php` | Tambah `Carbon::setLocale('id')` di boot. Tambah Blade directive `@tanggal($expression)` + `@tanggalWaktu($expression)` (isoFormat Indonesia). |
| 2 | `app/Http/Controllers/LaporanController.php` (2 method) | `index()` + `exportPdf()`: Set default `startOfMonth()` / `endOfMonth()` bila request tanggal kosong. Use Carbon. |
| 3 | `resources/views/modules/layouts/components/views/sidebar.blade.php` | (a) Naik font size heading + brand. (b) Tambah logic `request()->routeIs('....*')` setiap nav-item. (c) `<a class="nav-link {{ aktif ? 'active' : '' }}">`. (d) Custom CSS `.sidebar .nav-link.active` → background gradient oranye + bold text putih + border-left tebal. |
| 4 | `resources/views/modules/layouts/components/style/css.blade.php` | Tambah CSS block custom sidebar active state styling (karena inline style di blade susah, masukkan ke css.blade custom section). |
| 5 | `resources/views/modules/dashboard.blade.php` (2 line) | Replace `is_object(...)->format('d/m/Y')` → `@tanggal(...)` pada tgl_pengajuan pinjaman terbaru dan tanggal simpanan terbaru. |
| 6 | `resources/views/modules/simpanan/index.blade.php` | Replace tampilan tanggal → `@tanggal($item->tanggal)`. |
| 7 | `resources/views/modules/pinjaman/index.blade.php` | Replace tgl_pengajuan → `@tanggal($item->tgl_pengajuan)`. |
| 8 | `resources/views/modules/angsuran/index.blade.php` | Replace tanggal_jatuh_tempo → `@tanggal($item->tanggal_jatuh_tempo)`. |
| 9 | `resources/views/modules/pembayaran-angsuran/index.blade.php` | Replace tanggal_bayar → `@tanggal($item->tanggal_bayar)`. |
| 10 | `resources/views/modules/laporan/index.blade.php` | (a) Value input tanggal_awal/tanggal_akhir: ganti `request(...)` → `$tanggal_awal ?? ''`. (b) Replace SEMUA tampilan tanggal di 4 tab tabel (simpanan, pinjaman, angsuran) → `@tanggal(...)`. (c) Periode info line `s/d` → pakai `@tanggal(...)`. |
| 11 | `resources/views/modules/laporan/pdf.blade.php` | Periode info + 4 tab tabel semua tanggal → `@tanggal(...)` (agar PDF juga format Indonesia, bukan angka m/d/Y). |
| 12 | `anggota/create.blade.php` | (a) Hapus semua `required` pada input/select. (b) Tambah placeholder SETIAP input/textarea: no_anggota "Contoh: ANGG-000001", nik "Masukkan 16 digit NIK", nama "Masukkan nama lengkap", alamat "Alamat lengkap sesuai KTP", no_hp "Contoh: 081234567890", tgl_lahir tidak butuh placeholder (type=date), status_anggota tidak butuh placeholder. Select sudah punya placeholder dari option value="". |
| 13 | `anggota/edit.blade.php` | SAMA PERSIS anggota create: hapus required + tambah placeholder (value old() + default edit tetap). |
| 14 | `cabang/{create,edit}.blade.php` | Hapus required. Placeholder: kode_cabang "Contoh: KCP-001", nama_cabang "Nama kantor cabang", alamat "Alamat lengkap cabang", telepon "Contoh: 021-1234567". |
| 15 | `jenis-simpanan/{create,edit}.blade.php` | Hapus required. Placeholder: nama_jenis "Contoh: Simpanan Pokok", setoran_minimal "Minimal setoran (Rupiah)". |
| 16 | `jenis-pinjaman/{create,edit}.blade.php` | Hapus required. Placeholder: nama_jenis "Contoh: Pinjaman Usaha", bunga_tahunan "Persen bunga per tahun (0-100)". |
| 17 | `simpanan/{create,edit}.blade.php` | Hapus required di select + input. Placeholder: nominal "Nominal simpanan (Rupiah)", saldo "Saldo akhir simpanan". type=date TIDAK perlu placeholder. Select otomatis punya placeholder. |
| 18 | `pinjaman/{create,edit}.blade.php` | Hapus required. Placeholder: nomor_pinjaman "Contoh: PINJ-2026-0001", jumlah_pinjaman "Jumlah pinjaman diajukan (Rupiah)", tenor "Jumlah bulan (misal: 12)", bunga "Persen bunga per tahun (0-100)", angsuran_per_bulan "Angsuran per bulan (Rupiah)". tgl_pengajuan & tgl_cair = date no placeholder. |
| 19 | `angsuran/{create,edit}.blade.php` | Hapus required. Placeholder: angsuran_ke "Angsuran ke-berapa (1,2,3,...)", nominal "Nominal angsuran pokok + bunga", denda "Denda keterlambatan (jika ada)", total_bayar "Total dibayar (nominal + denda)". date no placeholder. |
| 20 | `pembayaran-angsuran/{create,edit}.blade.php` | Hapus required. Placeholder: jumlah_bayar "Jumlah yang dibayar anggota (Rupiah)". date no placeholder. |

## Implementation Steps (Dependency Order)
1. **AppServiceProvider.php** — Setup Carbon locale ID + Blade custom directives `@tanggal` & `@tanggalWaktu`. (INI LANGKAH PERTAMA — directive harus ada sebelum bisa dipakai di blade.)
2. **sidebar.blade.php + css.blade.php** — Upgrade font size sidebar, tambah logic active state `request()->routeIs()` per nav item, tambah CSS class `.nav-link.active` background gradient oranye bold.
3. **LaporanController.php** — index() + exportPdf() method, set default tanggal_awal = startOfMonth, tanggal_akhir = endOfMonth bila request kosong.
4. **Upgrade laporan/index.blade.php + pdf.blade.php** — value date input pakai variable controller default + replace seluruh tampilan tanggal pakai `@tanggal()`.
5. **Replace tampilan tanggal format Indonesia di 4 area views**: dashboard, simpanan.index, pinjaman.index, angsuran.index, pembayaran-angsuran.index (total 5 blade files) → gunakan directive `@tanggal`.
6. **Form upgrade BATCH 1 (anggota + cabang)**: create + edit, hapus `required`, tambah placeholder.
7. **Form upgrade BATCH 2 (jenis-simpanan + jenis-pinjaman)**: create + edit, same.
8. **Form upgrade BATCH 3 (simpanan + pinjaman)**: create + edit, same.
9. **Form upgrade BATCH 4 (angsuran + pembayaran-angsuran)**: create + edit, same.
10. **Verifikasi**: `php artisan view:clear && php artisan view:cache`, `php -l app/Providers/AppServiceProvider.php`, `php -l app/Http/Controllers/LaporanController.php`. Buka browser test: menu sidebar active ketika buka /modules/cabang/create, laporan default tanggal awal 1 September 2026 & tanggal akhir 30 September 2026 (otomatis), tabel-tabel menampilkan "17 September 2026" bukan angka, semua form tidak ada validasi HTML required (bisa submit kosong → tampil error Form Request via SweetAlert + invalid feedback).

## Dependencies and Considerations
- **Carbon Locale Indonesia**: Laravel 13 Carbon default locale `en` — WAJIB panggil `Carbon\Carbon::setLocale('id')` di AppServiceProvider boot(). Juga bisa set `config/app.php timezone='Asia/Jakarta'` + `locale='id'` (jika belum) untuk konsistensi, tapi directive isoFormat cukup dengan setLocale saja.
- **Laravel Pint**: Jalanin `./vendor/bin/pint app/Providers/AppServiceProvider.php app/Http/Controllers/LaporanController.php` setelah edit agar kode sesuai PSR-12.
- **Sidebar route is pattern**: Wildcard `*.index` / `*.create` / `*.edit` — pakai pattern `request()->routeIs('nama-modul.*')` agar COCOK dengan seluruh route resource (index/create/store/edit/update/destroy) — user tidak perlu bingung "pada halaman create menu kok tidak aktif".
- **Select2 placeholder**: Select di form SUDAH PUNYA placeholder dari `<option value="">-- Pilih X --</option>` (dimanfaatkan Select2 allowClear + auto-init) — jadi tidak perlu tambah attribute `placeholder=""` lagi di tag select. Cuma input/textarea yang butuh placeholder.
- **type=date placeholder**: Browser tidak mensupport placeholder asli pada `<input type="date">`. Biarkan tanpa placeholder (user tahu harus pilih tanggal).
- **Active state Dashboard**: Dashboard route cuma `route('dashboard')` single (bukan resource) → cek `request()->routeIs('dashboard') SAJA (tidak pakai wildcard).

## Validation
1. `php -l app/Providers/AppServiceProvider.php` → No syntax errors.
2. `php -l app/Http/Controllers/LaporanController.php` → No syntax errors.
3. `php artisan view:clear && php artisan view:cache` → Blade templates cached successfully (tidak ada error directive @tanggal undefined).
4. Buka `/modules/laporan` tanpa parameter → input tanggal_awal OTOMATIS terisi `2026-09-01` dan tanggal_akhir `2026-09-30` (sesuai bulan ini).
5. Buka `/modules/cabang/create` → menu Cabang di sidebar BERWARNA LAIN (background oranye bold).
6. Buka `/modules/dashboard` → card Pinjaman Terbaru kolom Tanggal Pengajuan tampilkan "17 September 2026" BUKAN angka.
7. Buka `/modules/anggota/create` → hapus required → klik submit tanpa isi → muncul SweetAlert toast error "Data gagal disimpan, silakan periksa kembali inputan" dan label field merah + invalid feedback (Form Request Laravel BUKAN HTML validation).
8. Placeholder check: input No Anggota ketika diklik kosong → muncul abu-abu "Contoh: ANGG-000001".

## Risks
- **RISK 1: Directive @tanggal undefined sebelum cache di-clear**: Jika user masih ada view:cache lama → directive tidak dikenali. **Handling**: Step 10 wajib panggil `php artisan view:clear && view:cache` sebelum report complete.
- **RISK 2: Sidebar routeIs mismatch jika ada route name berbeda pattern**: Pattern sidebar harus match list route name dari `php artisan route:list` (sudah diverify: cabang.*, anggota.*, jenis-simpanan.*, jenis-pinjaman.*, simpanan.*, pinjaman.*, angsuran.*, pembayaran-angsuran.*, laporan.*, dashboard = single). **Handling**: Sebelum write, double-check 10 nav-item vs list route exact name.
- **RISK 3: Carbon null parameter error di @tanggal(null)**: Directive harus handle null dengan return `-`. **Handling**: Dalam Blade directive closure, cek `empty($date) || $date === null` → return `-`.
- **RISK 4: Laporan filter user sebelumnya tersimpan di session (jika pake redirect back)**: Karena controller sekarang set default jika TIDAK ADA request input → user yang ingin melihat semua data BISA clear isi field tanggal manual (tidak paksa). Cuma DEFAULT nilai pertama dibuka = bulan ini. Tidak ada masalah.
