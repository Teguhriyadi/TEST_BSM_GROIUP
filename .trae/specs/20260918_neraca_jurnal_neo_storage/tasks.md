# Tasks: Modul Akuntansi + Neo Storage Migration

## Task 1: Install dependensi S3 Flysystem & config disk Neo
- Priority: high
- Status: pending
- Blocked By: None
- AC References: FR-STORAGE-1, AC-4
- Scope:
  - Konfirmasi package `league/flysystem-aws-s3-v3` di composer.json.
  - Update [config/filesystems.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/config/filesystems.php): tambahkan disk `neo` dengan key dari env SATSETSOLUTION_* (L50-L61 sebagai template s3).
  - Tambahkan helper `neo_storage_disk()` di [image_helper.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Helpers/image_helper.php) atau buat helper baru `storage_helper.php` & autoload via AppServiceProvider seperti image_helper.
- TR (rule): `config('filesystems.disks.neo.driver')` harus sama dengan `s3` dan `config('filesystems.disks.neo.endpoint')` = env SATSETSOLUTION_ENDPOINT.
- TR (rule): composer.json ada `league/flysystem-aws-s3-v3 ^3.0` di require. Jika tidak ada tambahkan di task evidence dan catat untuk user `composer require` manual.

## Task 2: Database Migrations (Semua tabel Akuntansi + kolom simpanan.jenis)
- Priority: high
- Status: pending
- Blocked By: Task 1 (optional: tidak hard dependency tapi migration jalan terpisah)
- AC References: FR-COA-1, FR-JURNAL-1, FR-JURNAL-2, FR-MAPPING-1, FR-SALDO-1, AC-8, Open Q #1
- Scope:
  - `2026_09_18_001000_add_jenis_to_simpanan_table`: tambah `simpanan.jenis enum('setoran','penarikan') default 'setoran'` setelah `jenis_simpanan_id`. Idempotent cek `Schema::hasColumn`.
  - `2026_09_18_002000_create_coa_table`: COA sesuai spec (parent_id self ref FK, level, kelompok, posisi_laporan, saldo_normal, cabang_id FK cabang.id nullable, index).
  - `2026_09_18_003000_create_coa_mapping_table`: FK cabang & coa, enum tipe_transaksi 7 pilihan, unique compound index.
  - `2026_09_18_004000_create_coa_saldo_awal_table`: FK coa + cabang, periode, unique (coa,cabang,periode).
  - `2026_09_18_005000_create_jurnal_umum_header_table`: FK cabang & dibuat_oleh, enum status/tipe, ref_id+ref_tipe, no_jurnal unique, check total debet=kredit bisa via app atau migration constraint (DB default MySQL cukup app level).
  - `2026_09_18_006000_create_jurnal_umum_detail_table`: FK header + FK coa, index header_id + coa_id.
- TR (rule): Setiap migration `Schema::create(...)` dengan `id=uuid primary`, `timestamps()`, foreign key `->constrained()->cascadeOnDelete()` untuk child dan `restrictOnDelete()` untuk coa (tidak boleh hapus coa jika ada mapping/jurnal). Up bisa dijalankan berurutan; down balik urutan drop FK.
- TR (rule): `php artisan migrate --pretend` tidak mengeluarkan error syntax.

## Task 3: Seeders Permission + COA Default + COA Mapping Default Upsert Idempotent
- Priority: high
- Status: pending
- Blocked By: Task 2 (harus ada migration dulu sebelum seeder run, tapi code bisa ditulis paralel)
- AC References: FR-RBAC-1, FR-MAPPING-2, AC-8
- Scope:
  - Tambah di [DatabaseSeeder.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/database/seeders/DatabaseSeeder.php) permission list baru:
    `COA_INDEX, COA_CREATE, COA_VIEW, COA_UPDATE, COA_DELETE, COA_MAPPING_INDEX, COA_MAPPING_UPDATE, COA_SALDO_AWAL_INDEX, COA_SALDO_AWAL_UPDATE, JURNAL_INDEX, JURNAL_CREATE, JURNAL_VIEW, JURNAL_UPDATE, JURNAL_DELETE, JURNAL_POSTING, BB_INDEX, NERACA_VIEW`
  - Sync permission ke role:
    *Admin = semua AKUNTANSI; *Teller = JURNAL_INDEX, JURNAL_CREATE, JURNAL_VIEW, BB_INDEX, NERACA_VIEW, COA_INDEX, COA_VIEW, COA_MAPPING_INDEX, COA_SALDO_AWAL_INDEX; *KC = JURNAL_INDEX, JURNAL_VIEW, JURNAL_POSTING, BB_INDEX, NERACA_VIEW, COA_INDEX, COA_VIEW, COA_MAPPING_INDEX, COA_SALDO_AWAL_INDEX; *Anggota = tanpa akuntansi.
  - Seed COA default Koperasi (contoh kode umum):
    *100.000 Kas & Setara Kas (aset, neraca, debet, level=1) → 100.001 Kas di Teller (level=2),
    *110.000 Simpanan di Bank (aset),
    *120.000 Pinjaman Anggota (aset, debet),
    *200.000 Simpanan Anggota (kewajiban, kredit),
    *210.000 Hutang Jangka Pendek lain,
    *300.000 Modal Disetor (ekuitas, kredit),
    *310.000 Laba Ditahan (ekuitas, kredit),
    *400.000 Pendapatan Bunga Pinjaman (laba_rugi, kredit, pendapatan),
    *410.000 Pendapatan Admin Pinjaman (pendapatan),
    *500.000 Beban Operasional (laba_rugi, debet, beban).
  - Seed COA Mapping default (cabang_id=null):
    `simpanan_setor: debet=Kas 100.001; kredit=Simpanan Anggota 200.000;`
    `simpanan_tarik: debet=Simpanan Anggota 200.000; kredit=Kas 100.001;`
    `pencairan_pinjaman: debet=Pinjaman Anggota 120.000; kredit=Kas 100.001;`
    `angsuran_pokok_diterima: debet=Kas 100.001; kredit=Pinjaman Anggota 120.000;`
    `angsuran_bunga_diterima: debet=Kas 100.001; kredit=Pendapatan Bunga 400.000;`
    `biaya_admin_pinjaman: debet=Kas 100.001; kredit=Pendapatan Admin 410.000`.
- TR (rule): run `db:seed --class=DatabaseSeeder` berulang tidak menyebabkan duplicate; setiap insert permission / coa / coa_mapping pakai `upsert` dengan unique key.
- TR (rule): permission baru dapat di-query via `Permission::where('kode_permission', 'COA_INDEX')->exists()` = true.

## Task 4: Model & Service Akuntansi (Eloquent)
- Priority: high
- Status: pending
- Blocked By: Task 2
- Scope:
  - `app/Models/Coa.php`: HasUuids, $fillable, casts enum string di PHP (kelompok, posisi_laporan, saldo_normal), relasi parent/children (HasMany self), cabang, coaMapping, coaSaldoAwal, jurnalDetail.
  - `app/Models/CoaMapping.php`: HasUuids, belongsTo coa & cabang, enum tipe_transaksi.
  - `app/Models/CoaSaldoAwal.php`: HasUuids, belongsTo coa & cabang.
  - `app/Models/JurnalUmumHeader.php`: HasUuids, $fillable, enum tipe/status, belongsTo cabang/dibuatOleh, hasMany detail, accessor `is_balance` = (sum debet == sum kredit), mutator `posting()` set status diposting dan validasi balance.
  - `app/Models/JurnalUmumDetail.php`: HasUuids, belongsTo header/coa.
  - `app/Services/JurnalService.php`: method static `createOtomatis(array $param)` → ambil mapping dari COA Mapping per cabang (fallback null), hitung jumlah alokasi pokok/bunga untuk angsuran, buat header + detail sekaligus dalam DB transaction, throw Exception bila balance tidak sama.
  - `app/Services/BukuBesarService.php`: method `saldoAkhirPerAkun($cabangId, $coaId, $tanggalCutoff)` → loop dari saldo_awal periode coa + sum debet/kredit sampai cutoff tanggal; untuk tampilan BB berjalan `getBukuBesarRows(...)` urut tanggal ASC.
  - `app/Services/NeracaService.php`: method `getNeraca($cabangId=null, $tanggal=null)` → aggregasi per kelompok coa (aset, kewajiban, ekuitas) + laba berjalan = (pendapatan-beban sampai tanggal).
- TR (rule): `Coa::find('uuid-xxx')->children` tidak lazy load error; relation keys benar.
- TR (rule): `JurnalService::createOtomatis([...])` return instance JurnalUmumHeader dengan sum(debet)=sum(kredit).

## Task 5: Observers / Hooks Auto Jurnal dari Transaksi
- Priority: high
- Status: pending
- Blocked By: Task 3 (mapping seeded) dan Task 4 (JurnalService)
- AC References: FR-JURNAL-4, AC-2, AC-6
- Scope:
  - `SimpananObserver::created`: panggil JurnalService dengan tipe berdasarkan `simpanan.jenis` (setoran/penarikan) + jumlah = `nominal`. Tanggal = tanggal transaksi.
  - `SimpananObserver::deleted`: hapus / buat jurnal pembalik otomatis (jurnal otomatis draf hapus; posting → insert jurnal pembalik tipe manual ref_tipe rollback).
  - `PembayaranAngsuranObserver::created`: hitung porsi pokok = `(pinjaman.sisa_pokok_sebelumnya)`? Jika tidak ada sisa pokok kolom, hitung estimasi: total angsuran_per_bulan - bunga bulan itu (bunga = `sisa_pokok_awal * (bunga_tahunan/12/100)`, pokok = `jumlah_bayar - bunga`, bila jumlah_bayar berbeda dengan angsuran_per_bulan maka bunga dihitung proporsional; total debet kredit balance).
  - `PinjamanObserver::updated (status)`: when `status` berubah dari `disetujui` ke `dicairkan` (dalam cairkanPinjaman method) → trigger auto jurnal pencairan_pinjaman jumlah = jumlah_pinjaman.
  - Bisa register observers di `AppServiceProvider::boot()`.
- TR (rule): AC-2 flow lulus (simpan 100k setoran → jurnal auto balance muncul).
- TR (rule): AC-6 lulus (bayar cicilan → jurnal auto minimal 3 baris, jumlah balance).

## Task 6: Requests, Controllers, Routes, Sidebar UI COA / Mapping / Saldo Awal
- Priority: medium
- Status: pending
- Blocked By: Task 3, 4
- Scope:
  - Requests Create/Update untuk Coa dan CoaMapping dan CoaSaldoAwal (simpan di `app/Http/Requests/Coa/...CoaCreateRequest.php`).
  - Controllers: `CoaController`, `CoaMappingController`, `CoaSaldoAwalController` dengan CRUD index/create/store/show/edit/update/destroy mengikuti pola CabangController.
  - Route group di `routes/web.php` SESUDAH route pinjaman / pembayaran-angsuran, prefix `modules/coa`, `modules/coa-mapping`, `modules/coa-saldo-awal` dengan middleware permission sesuai RBAC.
  - View Blade: `modules/coa/{index,create,edit,show}.blade.php`, `modules/coa-mapping/index.blade.php + edit` (1 page inline update), `modules/coa-saldo-awal/index.blade.php` dengan select periode & cabang, form edit inline.
  - Sidebar tambah section `AKUNTANSI` SESUDAH `TRANSAKSI` di [sidebar.blade.php](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/resources/views/modules/layouts/components/views/sidebar.blade.php#L173-L216).
- TR (rule): Setiap route muncul di `route:list` dengan middleware `permission:...` benar.
- TR (rule): Login Anggota → sidebar AKUNTANSI section tidak dirender (wrap dalam `@if(!$user->hasRole('Anggota'))`).
- TR (rule): NFR-5 (0 decorative) lulus.

## Task 7: Controllers, Views, Routes Jurnal Umum + Buku Besar + Neraca
- Priority: high
- Status: pending
- Blocked By: Task 4 (Service), Task 6 (UI base)
- Scope:
  - `JurnalUmumController`: index (filter cabang/tanggal/tipe/status), create (manual: input banyak baris detail debet/kredit, JS kalkulasi total realtime), store manual (pakai JurnalService), show detail, edit (hanya status draf), posting action (POST ubah status ke diposting + validasi balance, log user yang posting di header kolom baru `diposting_oleh` & `diposting_at` bila dibutuhkan - kalau perlu tambah migration alter jurnal_header kolom nullable, masuk task 2), destroy (hanya draf).
  - `BukuBesarController`: index GET + POST filter, render tabel saldo berjalan (gunakan BukuBesarService), filter wajib `coa_id` (single akun) & tanggal awal-akhir.
  - `NeracaController`: index GET + POST filter (cabang, tanggal cutoff default today), tampilkan table 3 bagian (Aset, Kewajiban, Ekuitas) dan 1 baris Balance check.
  - JS di form create Jurnal Manual: button `Tambah Baris Detail`, auto recalc sumDebit & sumKredit di header card, disable submit bila tidak balance (sementara 0 rupiah toleransi).
- TR (rule): Jurnal Manual Create dengan 2 detail (Kas 50k debet, Pendapatan lain 50k kredit) → berhasil tersimpan, status draf.
- TR (rule): Buku Besar akun Kas di tanggal tersebut menampilkan jurnal tersebut & saldo berjalan benar.
- TR (rule): Neraca saat itu menambahkan ASET (Kas bertambah 50k) dan Ekuitas (Laba berjalan bertambah 50k dari kredit pendapatan) → balance.

## Task 8: Refactor Storage Upload (Image/PDF/File) ke Neo disk
- Priority: high
- Status: pending
- Blocked By: Task 1 (disk neo config)
- AC References: FR-STORAGE-2, FR-STORAGE-3, FR-STORAGE-4a-4e, AC-3, AC-4, NFR-4
- Scope:
  - Refactor `compressImage()` helper: ubah semua `'public'` disk parameter ke `'neo'`; karena S3 disk tidak punya `->path()` method, hapus semua code path() usage (contoh compress via file path); ganti algoritma compressImage menjadi:
    a) stream UploadedFile ke temporary file `/tmp/...` dengan `$file->move(sys_get_temp_dir(), $randName)`,
    b) compress GD/Imagick ke temp file baru `/tmp/$compressedName`,
    c) upload via `Storage::disk('neo')->put($relativePath, file_get_contents($tmpCompressed), $visibility)`; hapus temp file lokal.
  - Tambah helper `neo_file_url($relativePath, $expireMinutes = 1440)`: cek visibility, jika public return `Storage::disk('neo')->url()`, jika private return `temporaryUrl($relativePath, now()->addMinutes($expireMinutes))`.
  - [PinjamanDokumen model](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Models/PinjamanDokumen.php#L87): ganti accessor `file_url` ke `return neo_file_url($this->file_path)`.
  - [PinjamanController](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Http/Controllers/PinjamanController.php#L361-L391 & L652): storage → `disk('neo')`.
  - `PembayaranAngsuranController::store/update`: jika nanti input `bukti_pembayaran` file upload (blm tentu ada di form), gunakan compressImage dengan disk neo. Check form create sekarang apa sudah ada file input (bukti_pembayaran). Kalau belum, tambah form input file bukti bayar, store path ke `bukti_pembayaran` field.
  - Audit codebase untuk grep `disk('public')` dan `Storage::disk('public')` → ganti semua app/Http, app/Models, app/Helpers (kecuali filesystems config, docs).
- TR (rule): AC-3 lulus (upload, url neo valid, delete).
- TR (rule): AC-4 lulus (grep 0 match Storage::disk('public') di app/).

## Task 9: Verify, Test Flow, Fix bugs, Optimize
- Priority: medium
- Status: pending
- Blocked By: Semua task 1-8 completed
- Scope:
  - `php artisan optimize:clear` + `composer dump-autoload` (catatan user manual di sandbox failed).
  - Review error log `storage/logs/laravel.log` untuk error dari flow yang jalan.
  - Full manual flow end-to-end sesuai AC-2, AC-3, AC-6, AC-5, AC-7.
  - Perbaiki bug yang ditemukan (misal observer event tidak ter-trigger, calculation salah, foreign key restrict delete).
- TR (rule): NFR-3 skor 2 (no error flow utama).
- TR (rule): NFR-1 skor 2 (convention sama modul existing).
- TR (rule): NFR-2 skor 2 (isolasi cabang).
