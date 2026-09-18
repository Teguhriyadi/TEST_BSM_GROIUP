# Spec: Modul Akuntansi (COA, Jurnal, Buku Besar, Neraca) + Migasi Storage ke Neo Object Storage (NOS)

## 1. Problem Statement

Sistem saat ini mencatat transaksi operasional (Simpanan, Pinjaman, Angsuran, Pembayaran Angsuran) namun:
- Belum ada struktur Akuntansi formal (COA / Chart of Accounts / Daftar Akun).
- Belum ada pencatatan Jurnal Umum otomatis / manual sebagai dasar pemeriksaan keuangan.
- Belum ada Buku Besar per Akun dan laporan Neraca.
- Semua file upload (bukti bayar, dokumen pinjaman, foto, gambar profil dll) saat ini disimpan di disk `public` lokal (storage/app/public + symlink), padahal sudah tersedia credential Neo Object Storage (SATSETSOLUTION) di env.

## 2. Users & Tujuan

| Aktor | Tujuan |
|---|---|
| Teller | Mencatat transaksi Simpanan/Pembayaran Angsuran yang otomatis menghasilkan Jurnal. Melihat Jurnal untuk audit. |
| KC | Memverifikasi Jurnal manual (bila dibutuhkan). Melihat Neraca per periode & Buku Besar. |
| Admin | Mengelola Daftar Akun (COA) dan saldo awal periode. |

## 3. Functional Requirements (Rule)

### A. COA (Daftar Akun / Chart of Accounts)
FR-COA-1 Terdapat tabel `coa` dengan kolom: `id (uuid PK), kode_akun (unique, contoh: 110.001), nama_akun (varchar 150), posisi_laporan enum ['neraca','laba_rugi'], kelompok enum ['aset','kewajiban','ekuitas','pendapatan','beban'], saldo_normal enum ['debet','kredit'], parent_id (nullable uuid FK ke coa.id), level integer, is_active enum ('1','0'), cabang_id (nullable FK cabang.id untuk COA per cabang), created_at, updated_at`.

FR-COA-2 CRUD Admin: `COA_INDEX, COA_CREATE, COA_VIEW, COA_UPDATE, COA_DELETE`. Menu Sidebar `AKUNTANSI > Daftar Akun`.

### B. Jurnal Umum (Journal Voucher)
FR-JURNAL-1 Terdapat tabel `jurnal_umum_header`: `id (uuid PK), no_jurnal (unique varchar 40), tanggal_jurnal (date), tipe enum ['manual','otomatis'], keterangan text, cabang_id (uuid FK cabang.id), dibuat_oleh (FK users.id), status enum ['draf','diposting'], total_debet decimal(15,2), total_kredit decimal(15,2), ref_id (nullable uuid), ref_tipe (nullable varchar 30, contoh: simpanan/pembayaran_angsuran/pencairan_pinjam/pinjaman_tolak_angsuran), created_at, updated_at`.

FR-JURNAL-2 Terdapat tabel `jurnal_umum_detail`: `id (uuid PK), jurnal_umum_header_id (FK), coa_id (FK coa.id), urutan integer, keterangan varchar 255 nullable, debet decimal(15,2) default 0, kredit decimal(15,2) default 0`. CONSTRAINT: Satu Jurnal Header jumlah (sum debet) = (sum kredit) saat `status=diposting`.

FR-JURNAL-3 CRUD Jurnal Manual (Teller/KC): `JURNAL_INDEX, JURNAL_CREATE, JURNAL_VIEW, JURNAL_UPDATE, JURNAL_DELETE, JURNAL_POSTING`. Menu Sidebar `AKUNTANSI > Jurnal Umum`. Hanya Jurnal `status=draf` yang bisa diedit; sesudah `diposting` read-only.

FR-JURNAL-4 **Auto Journal Trigger** (TANPA intervensi user):
- FR-JURNAL-4a `Simpanan::created` (setor/tarik): generate Jurnal Header `tipe=otomatis, ref_id=simpanan.id, ref_tipe=simpanan, tanggal=tanggal, cabang_id=cabang_id, dibuat_oleh=Auth::id`. Detail: sesuai mapping COA Default (lihat FR-MAPPING-1).
- FR-JURNAL-4b `PembayaranAngsuran::created` (bayar cicilan): generate Jurnal Header `tipe=otomatis, ref_id=pembayaran_angsuran.id, ref_tipe=pembayaran_angsuran, tanggal=tanggal_bayar, cabang_id=angsuran.pinjaman.cabang_id, dibuat_oleh=dibayar_oleh`. Detail: jumlah_bayar dipecah menjadi bagian `pokok` + `bunga` (proporsi dari `angsuran.jumlah_bayar / (pinjaman.angsuran_per_bulan)`; sisa disamakan debet=kredit).
- FR-JURNAL-4c `Pinjaman dicairkan (status: disetujui → dicairkan)`: generate Jurnal `ref_tipe=pencairan_pinjam, ref_id=pinjaman.id, tanggal=tgl_cair, pinjaman.jumlah_pinjaman sebagai total`.
- FR-JURNAL-4d Rollback (delete/soft-delete transaksi sumber): bila transaksi sumber dihapus, Jurnal terkait `tipe=otomatis` ikut dihapus (jika masih draf) atau dibuat Jurnal Pembalik otomatis (jika sudah diposting).

FR-JURNAL-5 `total_debet` dan `total_kredit` di Jurnal Header SELALU disinkronisasi dari detail (sum) sebelum disimpan; tidak boleh percaya dari user/client.

### C. Mapping COA Default Per Transaksi
FR-MAPPING-1 Tabel `coa_mapping`: `id (uuid PK), cabang_id (nullable), tipe_transaksi enum ['simpanan_setor','simpanan_tarik','angsuran_pokok_diterima','angsuran_bunga_diterima','pencairan_pinjaman','pinjaman_dibayar_kembali_pokok','biaya_admin_pinjaman'], coa_id (FK coa.id), posisi_debit_kredit enum ['debet','kredit']`. Unique `(cabang_id, tipe_transaksi, posisi_debit_kredit)` dengan fallback `cabang_id=null` sebagai default.

FR-MAPPING-2 Setting Mapping di Sidebar `AKUNTANSI > Mapping Akun`. CRUD `COA_MAPPING_INDEX, COA_MAPPING_UPDATE`. Seed default akun kode dummy umum Koperasi (Kas, Simpanan Anggota, Pinjaman Anggota, Pendapatan Bunga, Pendapatan Admin) otomatis di seeder supaya mapping langsung jalan tanpa user input awal.

### D. Buku Besar (General Ledger)
FR-BB-1 Route `AKUNTANSI > Buku Besar`: filter `cabang_id, coa_id, tanggal_awal, tanggal_akhir`. Tampilkan perubahan saldo per akun: `tanggal | no_jurnal | keterangan | debet | kredit | saldo_debet | saldo_kredit`. Saldo berjalan dihitung dari SALDO AWAL periode (lihat FR-SALDO-1) + akumulasi jurnal detail di rentang tanggal, berurutan `tanggal ASC, id ASC`.

FR-BB-2 Saldo Akhir per Akun = (Saldo Awal period) + (sum debet - sum kredit periode); jika `saldo_normal=debet` maka positif = sisi debet.

### E. Saldo Awal (Opening Balance per Periode)
FR-SALDO-1 Tabel `coa_saldo_awal`: `id (uuid PK), coa_id (FK), cabang_id (FK), periode (char 7 'YYYY-MM', contoh: '2026-09'), saldo_debet decimal(15,2) default 0, saldo_kredit decimal(15,2) default 0`. Unique (coa_id, cabang_id, periode). Setting via menu `AKUNTANSI > Saldo Awal` permission `COA_SALDO_AWAL_INDEX, COA_SALDO_AWAL_UPDATE`.

FR-SALDO-2 Kalkulasi Saldo Awal otomatis fallback: bila periode tidak ada data di `coa_saldo_awal`, system mengambil `Saldo Akhir` periode sebelumnya (rekursif) atau 0 bila periode pertama.

### F. Neraca (Balance Sheet Report)
FR-NERACA-1 Route `AKUNTANSI > Neraca`: Parameter `cabang_id, tanggal (cutoff tanggal: default today)`. Output 3 kolom utama:
| Klasifikasi | Jumlah |
|---|---|
| ASET (Lancar + Tetap) | X |
| KEWAJIBAN (Jangka Pendek + Panjang) | Y |
| EKUITAS (Modal Awal + Saldo Laba Berjalan) | Z |
| **ASET = KEWAJIBAN + EKUITAS** | Balance? Ya/Tidak |

FR-NERACA-2 Nilai diambil dari `Saldo Akhir per Akun` sampai `tanggal` filter, kemudian digroup by `coa.kelompok`. Item akun yang mapping `posisi_laporan!=neraca` (laba rugi) tidak masuk Neraca; laba rugi berjalan masuk sebagai item `Laba Ditahan` di Ekuitas (jumlah = total_pendapatan - total_beban sampai tanggal).

### G. Storage: Neo Object Storage (SATSETSOLUTION key di env)
FR-STORAGE-1 Tambahkan disk `'neo'` di `config/filesystems.php` dengan `driver=s3, key=SATSETSOLUTION_ACCESS_KEY, secret=SATSETSOLUTION_SECRET_KEY, region=wjv-1, bucket=image-storage, endpoint=https://nos.wjv-1.neo.id, use_path_style_endpoint=true, visibility=public (default private per file jika tidak set visibility), throw=true`. `FILESYSTEM_DISK` env default TIDAK DIUBAH (biarkan default). Hanya upload document/image/pinjam/bukti bayar paksa pakai `neo` disk via helper atau eksplisit `disk('neo')`.

FR-STORAGE-2 Update [compressImage() helper](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Helpers/image_helper.php) agar SEMUA path hasil `storeAs` → menggunakan `disk='neo'` BUKAN `public`. Juga hapus referensi `Storage::disk('public')->path(...)` (path tidak tersedia di S3/NOS; fallback ke temporary stream via `Storage::disk('neo')->put($file, $content)`).

FR-STORAGE-3 Update [PinjamanDokumen.php accessor `file_url`](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Models/PinjamanDokumen.php#L87) dari `Storage::disk('public')->url(...)` → `Storage::disk('neo')->temporaryUrl(...)` (dengan expire 1 hari) atau `url()` kalau visibility public. Fallback: jika visibility null return temporaryUrl always.

FR-STORAGE-4 Update semua Controller yang sebelumnya `storeAs(..., 'public')` atau `Storage::disk('public')` → `disk('neo')`:
- FR-STORAGE-4a [PinjamanController uploadDokumen L361-L391](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Http/Controllers/PinjamanController.php#L361-L391): upload file, delete old file.
- FR-STORAGE-4b [PinjamanController destroy L652-L654](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Http/Controllers/PinjamanController.php#L652): cleanup file.
- FR-STORAGE-4c `PembayaranAngsuranController` upload bukti_pembayaran (jika ada; tambahkan jika belum ada form file upload).
- FR-STORAGE-4d `AnggotaController` foto (jika ada).
- FR-STORAGE-4e Semua file upload lain (User foto, Cabang logo dll) → neo.

FR-STORAGE-5 Untuk keamanan akses private file: setiap URL gambar/file dokumen yang ditampilkan di blade (pinjaman_dokumen, pembayaran bukti bayar) → generate via helper `neo_public_url($relativePath)` yang otomatis pilih `temporaryUrl` (default 24 jam) agar tidak expose presign URL permanen.

### H. Sidebar / UI / Menu
FR-UI-1 Tambah Section `AKUNTANSI` di sidebar SESUDAH section `TRANSAKSI`, TIDAK disatukan dengan Transaksi. Submenu:
- Daftar Akun (COA)
- Mapping Akun
- Saldo Awal
- Jurnal Umum
- Buku Besar
- Neraca

FR-UI-2 Tidak ada elemen dekorasi: tidak ada sparkle, bintang, belah ketupat, lingkaran, bulat, garis pemisah custom (kecuali sidebar divider default). Semua tombol / form pakai style default SB Admin 2 (card, form-group, button-primary, table-bordered). Tidak ada emoji di teks UI (flash message, label, placeholder, button).

### I. RBAC / Permission
FR-RBAC-1 Semua permission baru dimasukkan ke `DatabaseSeeder` (upsert idempotent berdasarkan `kode_permission`) dan di-assign role:
- Admin: Semua permission AKUNTANSI + STORAGE migration setting.
- Teller: JURNAL_INDEX, JURNAL_CREATE, JURNAL_VIEW, BB_INDEX, NERACA_VIEW.
- KC: JURNAL_INDEX, JURNAL_VIEW, JURNAL_POSTING, BB_INDEX, NERACA_VIEW.
- Anggota: Tidak dapat AKUNTANSI.

## 4. Non Functional Requirements (Rubric)

NFR-1 (rubric, threshold=2/2) Konsistensi pola: semua Controller baru, Request, Model, Route, View Blade di-generate mengikuti CONVENTION modul yang SUDAH ADA (contoh: [CabangController](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Http/Controllers/CabangController.php), [JenisSimpanan CRUD](file:///Users/teguhriyadi2909/Desktop/BSM-GROUP/TEST-BSM-GROUP/app/Http/Controllers/JenisSimpananController.php)). Struktur permission: `MODUL_INDEX, MODUL_CREATE, MODUL_VIEW, MODUL_UPDATE, MODUL_DELETE, ...`. Middleware permission sama seperti modul lain `permission:MODUL_ACTION`.
- Skor 2: pattern nama file, route prefix `/modules/...`, nama route `{resource}.{action}`, middleware identik.
- Skor 1: sebagian berbeda tapi masih kompatibel.
- Skor 0: beda pattern, route / model / controller tidak konsisten.

NFR-2 (rubric, threshold=2/2) Isolasi Cabang: Query COA, Jurnal, Buku Besar, Neraca SELALU `force_scope_cabang_user` kecuali user Admin Super (Administrator role && tidak punya cabang_id).
- Skor 2: 100% terfilter, user non admin tidak bisa melihat data antar cabang.
- Skor 1: ada 1 modul yang missing filter.
- Skor 0: lebih dari 1 modul leak lintas cabang.

NFR-3 (rubric, threshold=2/2) No Error / No Bug.
- Skor 2: `php artisan route:list` tidak ada Closure error; Controller tidak mengembalikan 500 pada index/create/show/edit/store/update/destroy; tidak ada `Undefined array key`, `Trying to get property of non-object`, `Call to a member function` pada semua alur.
- Skor 1: hanya 1 edge case error (contoh: delete jika child exists tanpa abort 500).
- Skor 0: error muncul di flow utama.

NFR-4 (rubric, threshold=2/2) Storage migration completeness: setiap `storeAs`, `Storage::disk('public')`, `asset('storage/...')` yang terkait upload user di modul-modul yang sudah ada (Pinjaman Dokumen, Bukti Pembayaran Angsuran, dll) 100% sudah ke `neo` disk.
- Skor 2: grep `Storage::disk\('public'\)` di app/ dan resource/ → 0 match (kecuali filesystems.php config comments).
- Skor 1: < 3 match sisa.
- Skor 0: >=3 match sisa masih pointing public.

NFR-5 (rubric, threshold=2/2) No decorative elements:
- Skor 2: 0 match emoji / unicode decorative U+2605, U+2728, U+25C6, U+25CF, U+25CB, U+2744, U+2500 box-drawing di blade dan controller message.
- Skor 1: hanya ada 1 lokasi decorative non penting.
- Skor 0: >= 2 lokasi decorative ada.

## 5. Constraints & Deps

- Depends: package `league/flysystem-aws-s3-v3` untuk S3-compatible driver. Cek composer.json ada / tidak. Jika tidak, masukkan task install (user run lokal `composer require league/flysystem-aws-s3-v3:^3.0` karena sandbox tidak bisa composer).
- Depends: env keys sudah terisi: `SATSETSOLUTION_ACCESS_KEY, SATSETSOLUTION_SECRET_KEY, SATSETSOLUTION_BUCKET, SATSETSOLUTION_ENDPOINT, SATSETSOLUTION_DEFAULT_REGION`.
- Constraint: UUID primary key untuk semua tabel baru (gunakan `HasUuids` trait seperti model lain).
- Constraint: Rollback transaksi sumber (delete Simpanan / PembayaranAngsuran) → Jurnal otomatis ditarik / dibalik. Tidak boleh Jurnal orphan (tanpa ref, total tidak balance).
- Constraint: Jurnal otomatis TIDAK BOLEH diedit manual; hanya `status=draf` sebelum posting bisa berubah; untuk pembetulan buat jurnal pembalik (manual).

## 6. Open Questions (Jawaban User untuk Approve Implementasi)

1. Simpanan saat ini belum punya kolom `jenis_transaksi` (setor/tarik). Hanya `jenis_simpanan_id` + `nominal`. **Q: Boleh menambah `simpanan.jenis enum ('setoran','penarikan') default 'setoran'`?** Tanpa ini tidak bisa mapping debet/kredit benar (setor anggota: Kas debet, Simpanan Anggota kredit; tarik: sebaliknya). Rekomendasi: TAMBAH kolom, dan di form create simpanan harus pilih jenis (setor/tarik).

2. **Q: Pinjaman dicairkan mapping ke akun yang mana?** Rekomendasi default seed:
   - Pencairan: `Pinjaman Anggota (Aset)` debet, `Kas` kredit (jumlah_pinjaman).
   - Pembayaran angsuran dibagi: `Kas` debet, `Pinjaman Anggota` kredit (pokok), `Pendapatan Bunga Pinjaman` kredit (bunga), dan bila ada admin fee → `Pendapatan Admin Pinjaman` kredit. Bisa user rubah lewat Mapping Akun nanti.

3. **Q: Jurnal otomatis default statusnya DRAF (bisa KC review/posting) atau langsung DIPOSTING (tidak perlu review)?** Rekomendasi: Default POSTING (otomatis langsung masuk Buku Besar), karena kebanyakan koperasi tidak punya reviewer manual untuk setiap transaksi harian. Bila user mau review bisa ubah ke DRAF via Config / COA Mapping nanti.

4. **Q: Upload di Neo visibility apa?** Rekomendasi: `public` untuk dokumen/bukti bayar (URL langsung bisa diakses tanpa login), `private` untuk file sensitif dengan temporary url. Untuk kecepatan implementasi sementara pakai `private + temporaryUrl 24 jam` semua file; user ganti sendiri nanti.

## 7. Acceptance Criteria

AC-1 (rule) Route: `/modules/coa, /modules/coa-mapping, /modules/coa-saldo-awal, /modules/jurnal-umum, /modules/buku-besar, /modules/neraca` dapat diakses tanpa 404, dengan permission middleware sesuai role.
AC-2 (rule) Create Simpanan tipe `setoran` nominal 100.000:
  - Muncul di list Simpanan.
  - Muncul Jurnal Umum otomatis `tipe=otomatis, ref_tipe=simpanan` dengan 2 detail (Kas debet 100k; Simpanan Anggota kredit 100k) TOTAL SEIMBANG.
  - Di Buku Besar akun Kas periode itu bertambah 100k di debet saldo; Simpanan Anggota bertambah 100k kredit.
  - Neraca Aset = Kas 100k, Kewajiban+Ekuitas = Simpanan Anggota 100k → BALANCE.
AC-3 (rule) Upload dokumen Pinjaman → file_path di PinjamanDokumen adalah path relatif di bucket `image-storage`, dan `file_url` accessor mengembalikan URL valid dari Neo (bukan /storage/...). Hapus dokumen → object di bucket ikut terhapus.
AC-4 (rule) grep global `Storage::disk\('public'\)` di `app/Http` dan `app/Models` dan `app/Helpers` menghasilkan 0 match; semua pakai `disk('neo')` atau helper `neo_storage_disk()` jika dibuat.
AC-5 (rule) Sidebar: Terdapat section `AKUNTANSI` SESUDAH section `TRANSAKSI` dengan 6 submenu seperti FR-UI-1; Akun Anggota login → tidak muncul section AKUNTANSI sama sekali.
AC-6 (rule) Pembayaran Angsuran 500.000 untuk pinjaman yang bunga & pokok terhitung → Jurnal auto generate setidaknya 3 baris detail (Kas debet 500k, Pinjaman Pokok kredit A, Bunga kredit B) dengan A+B=500k.
AC-7 (rule) Semua controller / model / blade TIDAK mengandung dekorasi visual (sparkle, bintang, belah ketupat, lingkaran, bulat, garis custom) dan tidak ada emoji.
AC-8 (rule) Run `php artisan migrate` setelah semua migration dibuat tanpa error foreign key, dan `DatabaseSeeder` (run ulang) tidak duplicate permission (upsert idempotent by kode_permission).
