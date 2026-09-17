# Fitur Persyaratan, Upload, dan Verifikasi Dokumen Pinjaman - Implementation Plan

## Repository Research (Temuan Existing)

### Struktur Existing yang Sudah Ada (TIDAK DIUBAH KECUALI TERCATAT):
- Tabel `jenis_pinjaman`: id (UUID PK), nama_jenis, keterangan, maksimal_plafon, bunga_tahunan, tenor_minimal, tenor_maksimal, timestamps.
- Tabel `pinjaman`: id (UUID PK), anggota_id FK, cabang_id FK, jenis_pinjaman_id FK, nomor_pinjaman UNIQUE, jumlah_pinjaman, tenor, bunga, angsuran_per_bulan, tujuan, status enum (diajukan, diverifikasi, disetujui, ditolak, dicairkan, berjalan, lunas, dibatalkan), tgl_pengajuan, tgl_cair, timestamps. TIDAK ada kolom ktp/kk/slip_gaji langsung (sesuai requirement).
- `JenisPinjaman` model: booted creating UUID BELUM ADA (mirip bug Role yang lalu). Akan ditambahkan agar saat create via form bisa insert UUID.
- `Pinjaman` model: booted creating UUID BELUM ADA. Akan ditambahkan.
- `PinjamanController`: resource index/create/store/edit/update/destroy; BELUM ada show, upload dokumen, verifikasi dokumen, approval dokumen.
- Routes web: `Route::resource('pinjaman', PinjamanController::class)->except(['show'])`. Resource pinjaman exclude show. Akan menambahkan route show + sub-route dokumen.
- Permissions existing: `PINJAMAN_CRUD` dan `PINJAMAN_APPROVE` sudah ada. Akan tambah `PINJAMAN_DOKUMEN_UPLOAD` (anggota/teller upload) + `PINJAMAN_DOKUMEN_VERIFIKASI` (validator approve dokumen) via seeder DatabaseSeeder.
- Composer autoload existing: TIDAK ADA `files` autoload (untuk helper). Akan ditambahkan files `app/Helpers/image_helper.php` lalu composer dump-autoload.
- `FILESYSTEM_DISK=public` di .env: sudah sesuai requirement storage:link.
- Seeder existing: 4 JenisPinjaman (Mikro Usaha, Konsumtif, Pendidikan, Perumahan). Status role: Teller punya PINJAMAN_CRUD; KC punya PINJAMAN_CRUD + PINJAMAN_APPROVE.

## Files and Modules

### File BARU (dibuat dari nol):
1. `database/migrations/2026_09_17_050000_create_master_dokumen_persyaratan_table.php` → `master_dokumen`: master daftar jenis dokumen (KTP, KK, Slip Gaji, Surat Kerja, Jaminan, dll).
2. `database/migrations/2026_09_17_050010_create_jenis_pinjaman_dokumen_persyaratan_table.php` → `jenis_pinjaman_dokumen_persyaratan`: pivot tabel yang menentukan dokumen mana yang WAJIB untuk suatu jenis_pinjaman.
3. `database/migrations/2026_09_17_050020_create_pinjaman_dokumen_table.php` → `pinjaman_dokumen`: tracking upload dokumen per pengajuan pinjaman, status, catatan, file_path.
4. `app/Helpers/image_helper.php` → Helper function global `compressImage($file, $directory, $quality=80, $maxWidth=1600)`.
5. `app/Models/MasterDokumen.php` → Model MasterDokumen (booted UUID, fillable kode/nama_dokumen/deskripsi/format_diperbolehkan/is_active).
6. `app/Models/JenisPinjamanDokumenPersyaratan.php` → Pivot model (belongsTo JenisPinjaman + MasterDokumen).
7. `app/Models/PinjamanDokumen.php` → Model PinjamanDokumen (booted UUID, fillable pinjaman_id, master_dokumen_id, uploader_users_id, file_path, nama_file_asli, ukuran_file, tipe_mime, status enum (belum_diunggah, menunggu_verifikasi, disetujui, ditolak, perlu_diperbaiki), catatan, verifikator_users_id, tgl_verifikasi, timestamps).
8. `app/Http/Requests/Pinjaman/DokumenPinjamanUploadRequest.php` → Validasi upload dokumen (single file), rules: required file, max 10MB, mimes: jpg,jpeg,png,pdf.
9. `app/Http/Requests/Pinjaman/DokumenPinjamanVerifikasiRequest.php` → Validasi verifikasi (status required in [disetujui, ditolak, perlu_diperbaiki]; catatan required jika ditolak/perlu diperbaiki).
10. `app/Http/Requests/Pinjaman/PinjamanApprovalRequest.php` → Before approve pinjaman, VALIDASI aturan: SEMUA dokumen WAJIB status = disetujui.
11. `resources/views/modules/jenis-pinjaman/form-persyaratan-dokumen.blade.php` → Part di form Jenis Pinjaman create/edit untuk checklist dokumen wajib.
12. `resources/views/modules/pinjaman/show.blade.php` → Halaman DETAIL pinjaman (untuk anggota upload + validator verifikasi).
13. `resources/views/modules/pinjaman/partials/dokumen-card-list.blade.php` → Partial daftar kartu dokumen per status, tombol upload/replace, verifikasi.

### Existing Files DIUBAH (hanya relevan):
14. `composer.json` → `autoload.files` tambahkan path `app/Helpers/image_helper.php`.
15. `database/seeders/DatabaseSeeder.php` → Tambah 5 `MasterDokumen` (KTP, KK, Slip Gaji, Surat Keterangan Kerja, Dokumen Jaminan). Sync `jenis_pinjaman_dokumen_persyaratan` untuk 4 jenis pinjaman existing. Tambah 2 permission baru PINJAMAN_DOKUMEN_UPLOAD dan PINJAMAN_DOKUMEN_VERIFIKASI, assign role: Teller = upload, KC = upload + verifikasi, Admin = semua.
16. `app/Models/JenisPinjaman.php` → Tambah `booted()` creating UUID auto + `belongsToMany(MasterDokumen::class, ...)` relation `dokumenPersyaratanWajib()`.
17. `app/Models/Pinjaman.php` → Tambah `booted()` creating UUID auto + `hasMany(PinjamanDokumen::class)` `dokumen()` + helper method `semuaDokumenWajibDisetujui(): bool` + `dapatDiverifikasi(): bool`.
18. `app/Http/Controllers/JenisPinjamanController.php` → store/update sync `$jenisPinjaman->dokumenPersyaratanWajib()->sync($request->master_dokumen_ids ?? [])`.
19. `resources/views/modules/jenis-pinjaman/create.blade.php` → include part `form-persyaratan-dokumen.blade.php` dengan list checkbox master dokumen.
20. `resources/views/modules/jenis-pinjaman/edit.blade.php` → Sama include part, dengan data selected lama.
21. `app/Http/Controllers/PinjamanController.php` → Tambah method `show($id)` (eager load `jenisPinjaman.dokumenPersyaratanWajib`, `dokumen.masterDokumen`, `dokumen.uploader`, `dokumen.verifikator`) → return view show. Tambah 2 method POST: `uploadDokumen($pinjamanId, $masterDokumenId)` + `verifikasiDokumen($pinjamanId, $pinjamanDokumenId)`. Tambah `approvalPinjaman($id)` dengan validasi rule semua dokumen wajib = disetujui sebelum ubah status ke diverifikasi/disetujui. Semua gunakan DB::transaction, try-catch, Storage::disk public.
22. `resources/views/modules/pinjaman/index.blade.php` → Kolom Aksi: UBAH tombol. Ganti icon pencil jadi `<a href="{{route('pinjaman.show')}}">Detail Dokumen</a>` (tambah tombol detail DENGAN tetap ada tombol edit + hapus untuk role tertentu). Tambah kolom baru "Progress Dokumen" menampilkan badge `X/Y disetujui`.
23. `routes/web.php` → Tambah di pinjaman group:
   - `GET modules/pinjaman/{pinjaman}` (name pinjaman.show) → permission: PINJAMAN_CRUD,PINJAMAN_APPROVE,PINJAMAN_DOKUMEN_UPLOAD,PINJAMAN_DOKUMEN_VERIFIKASI
   - `POST modules/pinjaman/{pinjaman}/dokumen/{masterDokumen}/upload` (pinjaman.dokumen.upload) → permission UPLOAD
   - `POST modules/pinjaman/{pinjaman}/dokumen/{pinjamanDokumen}/verifikasi` (pinjaman.dokumen.verifikasi) → permission VERIFIKASI
   - `POST modules/pinjaman/{pinjaman}/diverifikasi` → (pinjaman.verifikasi) → status dari diajukan → diverifikasi (via PinjamanApprovalRequest).
24. `app/Http/Requests/JenisPinjaman/JenisPinjamanCreateRequest.php` & `UpdateRequest` → tambah nullable array `master_dokumen_ids` validation exists:master_dokumen,id.

## Implementation Steps (Dependency Order)

Step 0: Persiapan composer autoload register helper files.
Step 1: Buat 3 migration (urut create: master_dokumen, jenis_pinjaman_dokumen_persyaratan, pinjaman_dokumen).
Step 2: Buat 3 Model (MasterDokumen, JenisPinjamanDokumenPersyaratan pivot, PinjamanDokumen) + edit JenisPinjaman & Pinjaman model booted + relation.
Step 3: Buat Helper `app/Helpers/image_helper.php` function compressImage() (JPG/JPEG/PNG support, GD extension atau intervention fallback to native GD if available. PILIH native GD agar tidak perlu install library baru).
Step 4: Edit 2 Form Request Jenis Pinjaman Create Update tambah array master_dokumen_ids.
Step 5: Buat 3 Form Request baru (Dokumen Upload, Verifikasi, Pinjaman Approval).
Step 6: Edit JenisPinjamanController store/update sync pivot persyaratan wajib.
Step 7: Edit JenisPinjaman create/edit view tambah section checklist dokumen wajib (partial).
Step 8: Edit PinjamanController TAMBAH method show, uploadDokumen, verifikasiDokumen, verifikasiPinjaman, approvalPinjaman. Store: setelah create, SEED tabel pinjaman_dokumen satu baris untuk SETIAP dokumen persyaratan wajib dengan status = 'belum_diunggah'.
Step 9: Tambah route baru di web.php (show, upload, verifikasi, diverifikasi).
Step 10: Edit pinjaman.index.blade.php tambah kolom Progress Dokumen dan tombol Detail.
Step 11: Buat show.blade.php detail + partial dokumen-card-list (untuk upload area, tombol replace, area verifikasi).
Step 12: Edit DatabaseSeeder TAMBAH master dokumen 5 row, sync persyaratan, tambah 2 permission baru assign ke role.
Step 13: GetDiagnostics verify.

## Dependencies and Considerations
- `compressImage` harus menggunakan **native GD** (tanpa install package baru) karena requirement helper reusable laravel helper. Jika GD tidak ada, fallback ke tanpa kompresi (tetap simpan file).
- Helper compressImage: Cek extension, untuk PNG simpan PNG, JPG/JPEG simpan JPEG. Resize proportional aspect ratio. Return path relative untuk `Storage::disk('public')`.
- Validasi ukuran file upload per dokumen: max. 10 MB (10240 KB).
- PDF: TIDAK diproses compressImage, simpan langsung via `$file->storeAs($dir, $filename, 'public')`.
- Pinjaman DIAJUKAN status awal tetap: `diajukan`. Pinjaman bisa ubah ke `diverifikasi` HANYA jika 100% dokumen wajib berstatus `disetujui`. Jika ada satu pun dokumen dengan status != disetujui → abort atau redirect dengan error.
- Role permissions:
  - Anggota (asumsi tidak ada role anggota, user login sebagai anggota belum ada → di luar scope. Anggota hanya ditambahkan data, tidak login).
  - Teller: PINJAMAN_DOKUMEN_UPLOAD (upload/replace).
  - Kepala Cabang (KC): PINJAMAN_DOKUMEN_UPLOAD + PINJAMAN_DOKUMEN_VERIFIKASI + PINJAMAN_APPROVE.
  - Administrator: SEMUA permission (auto bypass via middleware permission).
- Upload replace: Jika dokumen sudah pernah diunggah sebelumnya, upload baru OTOMATIS HAPUS file lama dari storage agar tidak menumpuk.
- Untuk tombol approval di halaman detail, disabled jika progress dokumen belum 100%.

## Validation
1. `php artisan migrate:fresh --seed` dijalankan → **semua table baru** muncul, seed 5 master dokumen, pivot sync 4 jenis pinjaman berbeda dokumen, seed permission 2 baru.
2. Buat jenis pinjaman BARU via form → checklist dokumen wajib 3 item → save → DB jenis_pinjaman_dokumen_persyaratan 3 row masuk.
3. Buat pinjaman BARU via form → after save, DB `pinjaman_dokumen` auto insert baris untuk setiap jenis dokumen wajib dengan status `belum_diunggah`.
4. Buka halaman pinjaman.show → Klik tombol Upload KTP, pilih file gambar KTP.jpg, submit → `compressImage` terpanggil, file disimpan di storage/app/public/dokumen-pinjaman/{pinjaman_id}/, path disimpan. Status otomatis jadi `menunggu_verifikasi`.
5. Upload file PDF (jaminan.pdf) → disimpan langsung tanpa kompresi, size validasi 10MB.
6. Login KC → buka halaman show pinjaman → panel verifikasi muncul. Klik Setujui KTP → status pinjaman_dokumen jadi disetujui, tgl_verifikasi + verifikator id terisi. Klik Tolak KK → catatan: "Foto buram, mohon diunggah ulang" → status ditolak, anggota bisa upload ulang (tombol replace muncul).
7. Semua dokumen disetujui → halaman show tombol "Verifikasi Pengajuan" (ubah ke diverifikasi) muncul, klik → status pinjaman jadi diverifikasi (halaman index badge berubah).
8. Coba Verifikasi Pengajuan ketika masih ada KK ditolak → muncul error "Terdapat dokumen yang perlu perbaiki/ditolak." status tidak berubah.
9. Tombol Detail Pinjaman di index → Progress Dokumen "1/3 disetujui" badge benar.
10. Storage:link sudah ada, akses via /storage/dokumen-pinjaman/{id}/ktp-xxx.jpg works.
11. GetDiagnostics empty.

## Risiko
- **GD not installed**: compressImage fallback to original file without resize/compression (tetap return path).
- **UUID booted missing di Model lama**: Fix tambahkan booted creating UUID di JenisPinjaman, Pinjaman (seperti Role User).
- **File collision filename**: Gunakan `Str::random(40)` + extension asli agar tidak bentrok.
- **Permission role Teller tidak bisa verifikasi**: Middleware permission akan abort 403 → expected behaviour, bukan bug.
- **Route show bertabrakan**: Karena resource pinjaman except(['show']), route manual GET show akan jalan. Tidak bentrok.
