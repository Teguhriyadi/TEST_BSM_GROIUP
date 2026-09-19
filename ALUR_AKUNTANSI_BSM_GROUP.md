# DOKUMENTASI ALUR SISTEM AKUNTANSI BSM GROUP
## Double-Entry Otomatis — Laravel Observer Pattern

---

## RINGKASAN SISTEM

Sistem ini menerapkan **Akuntansi Double-Entry (Debet = Kredit)** secara **OTOMATIS** menggunakan **Laravel Observer Pattern**. Setiap transaksi harian (Simpanan, Pinjaman, Angsuran) yang dibuat user akan otomatis menghasilkan Jurnal Umum sesuai dengan **Mapping COA** yang sudah disetting Admin. Jurnal default berstatus **DRAF** dan perlu **diposting oleh Kepala Cabang (KC)** agar masuk ke seluruh Laporan Akuntansi.

> **PRINSIP UTAMA**: Sebelum ada transaksi apapun, Admin WAJIB menyelesaikan **3 Langkah Setup Master** di bawah ini berurutan. Jika salah satu dilewati, Observer tidak bisa mencari mapping akun → Jurnal otomatis GAGAL.

---

## BAB 1 — SETUP MASTER AWAL (3 LANGKAH WAJIB BERURUTAN)

| Urutan | Langkah | Module UI | Tujuan | Keterangan |
|--------|---------|-----------|--------|------------|
| **1** | **Buat Struktur COA** | `Akuntansi > COA` | Mendaftarkan semua akun perkiraan (Aktiva, Kewajiban, Modal, Pendapatan, Beban) | Format nomor akun sesuai kebijakan internal BSM, misal `1.1.01 Kas`, `2.1.01 Simpanan Pokok`, dll |
| **2** | **Mapping COA** | `Akuntansi > COA Mapping` | Memasangkan **7 tipe transaksi** dengan akun **Debet** dan **Kredit** masing-masing | Ini adalah "OTAK" dari seluruh otomasi jurnal. Salah mapping → seluruh laporan salah |
| **3** | **Input Saldo Awal** | `Akuntansi > COA Saldo Awal` | Memasukkan nominal saldo awal setiap akun di **awal periode akuntansi** berjalan | Contoh: mulai 1 Januari 2026, isikan saldo awal Kas, Piutang, Simpanan, Modal, dll |

### Mengapa Harus Berurutan?
```
COA (daftar akun) harus ADA TERLEBIH DAHULU
    ↓
COA Mapping baru bisa memilih akun Debet/Kredit dari daftar COA yang ada
    ↓
Saldo Awal baru bisa mengisi nominal ke akun yang sudah didaftarkan di COA
    ↓
OBSERVER OTOMASI BARU BISA BEKERJA NORMAL ✓
```

---

## BAB 2 — 7 TIPE TRANSAKSI & MAPPING DEBET/KREDIT

Berikut adalah **7 tipe transaksi** yang otomatis dijurnal oleh Observer. Admin **HARUS** melengkapi ke-7 baris ini di Module `COA Mapping`:

| # | Tipe Transaksi | Akun Debet | Akun Kredit | Contoh Nyata (Rp) |
|---|---------------|------------|-------------|-------------------|
| **1** | `SIMPANAN_SETOR` | **Kas / Bank** | **Simpanan (Pokok/Wajib/Sukarela)** | Anggota setor Simpanan Sukarela 5.000.000 → Debet Kas 5jt, Kredit Simpanan Sukarela 5jt |
| **2** | `SIMPANAN_TARIK` | **Simpanan (Pokok/Wajib/Sukarela)** | **Kas / Bank** | Anggota tarik Simpanan Sukarela 2.000.000 → Debet Simpanan 2jt, Kredit Kas 2jt |
| **3** | `PINJAMAN_CAIR` | **Piutang Pinjaman** | **Kas / Bank** | Pinjaman Mikro Usaha cair 10.000.000 → Debet Piutang 10jt, Kredit Kas 10jt |
| **4** | `ANGSURAN_POKOK` | **Kas / Bank** | **Piutang Pinjaman** | Angsuran Pokok bulan ke-1 sebesar 850.000 → Debet Kas 850rb, Kredit Piutang 850rb (piutang berkurang) |
| **5** | `ANGSURAN_BUNGA` | **Kas / Bank** | **Pendapatan Bunga** | Angsuran Bunga bulan ke-1 sebesar 150.000 → Debet Kas 150rb, Kredit Pendapatan Bunga 150rb |
| **6** | `ANGSURAN_ADMIN` | **Kas / Bank** | **Pendapatan Administrasi** | Biaya Admin bayar bulan ke-1 sebesar 25.000 → Debet Kas 25rb, Kredit Pendapatan Admin 25rb |
| **7** | `ANGSURAN_DENDA` | **Kas / Bank** | **Pendapatan Denda** | Denda keterlambatan 10.000 → Debet Kas 10rb, Kredit Pendapatan Denda 10rb |

> **CATATAN PENTING**: Satu kali transaksi **Pembayaran Angsuran** bisa menghasilkan **LEBIH DARI 1 PASANG** jurnal. Contoh: Anggota bayar Rp1.035.000 (Pokok 850rb + Bunga 150rb + Admin 25rb + Denda 10rb) → Observer menghasilkan **4 baris Jurnal Detail** (4 Debet total Rp1.035.000 ke Kas, dan 4 Kredit ke masing-masing akun pendapatan/piutang). **Total Debet SELALU = Total Kredit**.

---

## BAB 3 — ALUR OTOMATIS JURNAL DOUBLE ENTRY (OBSERVER PATTERN)

### 3.1 Diagram Alir Observer
```
[USER] membuat transaksi (Simpanan / Pinjaman Cair / Bayar Angsuran)
        ↓
[MODEL EVENT] Model memicu event `created` (atau `updated` untuk status perubahan)
        ↓
[OBSERVER CLASS] Menangkap event → memanggil method `created()` / `updated()`
  ├─ SimpananObserver.php
  ├─ PinjamanObserver.php
  └─ PembayaranAngsuranObserver.php
        ↓
[LOOKUP COA MAPPING] Query ke tabel `coa_mapping` berdasarkan `tipe_transaksi`
  → Dapatkan: `akun_debet_id` + `akun_kredit_id` + `is_active`
        ↓
[CREATE JURNAL HEADER] Buat `jurnal_umum_header` dengan:
  → nomor_jurnal auto (format: JU-YYYYMMDD-0001)
  → tanggal transaksi
  → keterangan = deskripsi transaksi (misal: "Setor Simpanan Sukarela - A001 Andi")
  → status = `DRAF` (default, BELUM masuk laporan)
  → id_ref = ID transaksi sumber (simpanan_id / pinjaman_id / pembayaran_angsuran_id)
  → tipe_transaksi = enum sesuai BAB 2
        ↓
[CREATE JURNAL DETAIL (2 baris minimum)]
  → Baris 1: `coa_id = akun_debet_id` + `debet = NOMINAL` + `kredit = 0`
  → Baris 2: `coa_id = akun_kredit_id` + `debet = 0` + `kredit = NOMINAL`
        ↓
[VALIDASI] SUM(debet) = SUM(kredit) ?
  ├─ YES → Simpan Jurnal Header + Detail
  └─ NO  → Rollback DB Transaction + Write Log Error (transaksi induk TIDAK dibatalkan)
        ↓
[FINAL] Jurnal tersimpan sebagai DRAF
        ↓
[KC POSTING] Kepala Cabang buka `Akuntansi > Jurnal Umum` → pilih DRAF → klik "Posting"
  → status berubah `DIPOSTING` → **SEKARANG MASUK KE SEMUA LAPORAN** ✓
```

### 3.2 Status Jurnal
| Status | Siapa yang Ubah | Masuk Laporan? | Keterangan |
|--------|-----------------|----------------|------------|
| **DRAF** | Otomatis Observer | ❌ TIDAK | Jurnal baru, perlu review KC |
| **DIPOSTING** | KC klik Posting | ✅ YA | Resmi, tidak bisa dihapus (hanya bisa reversing) |
| **DITOLAK** | KC klik Tolak | ❌ TIDAK | Ada kesalahan mapping/nilai |

---

## BAB 4 — 14 MODUL AKUNTANSI & ALIR DATA

Alir data laporan adalah **Bottom-Up**: dari transaksi mentah → diolah menjadi ringkasan → laporan keuangan akhir.

```
                    ┌──────────────────────────────────────┐
                    │  LEVEL 0: SUMBER TRANSAKSI (INPUT)   │
                    │  Simpanan · Pinjaman · Angsuran      │
                    │  Otomatis jadi Jurnal DRAF via Observer
                    └──────────────────┬───────────────────┘
                                       ↓
          ┌─────────────────────────────────────────────────────┐
          │  LEVEL 1: JURNAL (BUKTI TRANSAKSI)                  │
          │  ┌────────────────┐  ┌──────────────────────────┐   │
          │  │  Jurnal Harian │  │  Jurnal Umum (14 Kolom)   │   │
          │  │  (per tanggal   │  │  (DRAF ←→ POSTING by KC) │   │
          │  │   ringkas)      │  │                          │   │
          │  └────────┬───────┘  └──────────────┬───────────┘   │
          └───────────┼─────────────────────────┼───────────────┘
                      ↓                         ↓
          ┌─────────────────────────────────────────────────────┐
          │  LEVEL 2: BUKU BESAR & BUKU KAS (PER AKUN)          │
          │  ┌────────────────┐  ┌──────────────────────────┐   │
          │  │  Buku Besar    │  │  Buku Kas Harian          │   │
          │  │  (semua akun,  │  │  (khusus akun Kas/Bank,   │   │
          │  │   running       │  │   per tanggal + saldo     │   │
          │  │   saldo)        │  │   running)                │   │
          │  └────────┬───────┘  └──────────────┬───────────┘   │
          └───────────┼─────────────────────────┼───────────────┘
                      ↓                         ↓
          ┌─────────────────────────────────────────────────────┐
          │  LEVEL 3: REKAP & NERACA SALDO (UJI BALANCE)        │
          │  ┌──────────────────────┐  ┌────────────────────┐   │
          │  │ Rekap Kas & Non Kas  │  │ Neraca Saldo       │   │
          │  │ Harian               │  │ (Uji Balance: Debet│   │
          │  │ (per tanggal: Kas vs │  │  = Kredit, SALDO   │   │
          │  │  Non Kas)            │  │  AKHIR per akun)   │   │
          │  └──────────┬───────────┘  └─────────┬──────────┘   │
          └─────────────┼────────────────────────┼──────────────┘
                        ↓                        ↓
          ┌─────────────────────────────────────────────────────┐
          │  LEVEL 4: LAPORAN KEUANGAN UTAMA                    │
          │  ┌────────────┐  ┌─────────────┐  ┌──────────────┐  │
          │  │  Neraca    │  │ Laba Rugi   │  │ Arus Kas     │  │
          │  │ (Posisi    │  │ (Pendapatan │  │ (Aliran Kas  │  │
          │  │  Keuangan: │  │  vs Beban   │  │  Operasional │  │
          │  │  Aktiva =  │  │  → Laba /   │  │  Investasi   │  │
          │  │  Kewajiban │  │  Rugi       │  │  Pendanaan)  │  │
          │  │  + Modal)  │  │             │  │              │  │
          │  └─────┬──────┘  └──────┬──────┘  └──────┬───────┘  │
          └────────┼────────────────┼────────────────┼──────────┘
                   ↓                ↓                ↓
          ┌─────────────────────────────────────────────────────┐
          │  LEVEL 5: LAPORAN PENDUKUNG & PEMERIKSAAN           │
          │  ┌──────────────────┐  ┌────────────────────────┐   │
          │  │ Cek Neraca Saldo │  │ SHU (Sisa Hasil Usaha)  │   │
          │  │ (Verifikasi:     │  │ Distribusi SHU:         │   │
          │  │  BALANCE /       │  │ • 50% Cadangan Umum     │   │
          │  │  TIDAK BALANCE)  │  │ • 40% SHU Anggota       │   │
          │  │                  │  │ • 10% Dana Sosial       │   │
          │  └──────────────────┘  └────────────────────────┘   │
          └─────────────────────────────────────────────────────┘
```

### Tabel Detail 14 Modul
| # | Nama Module | UI Path | Fungsi Utama | Bisa Export? |
|---|------------|---------|--------------|--------------|
| 1 | **COA** | `Akuntansi > COA` | Daftar akun perkiraan (Chart of Accounts) | ✅ PDF + Excel |
| 2 | **COA Mapping** | `Akuntansi > COA Mapping` | 7 tipe transaksi ↔ Debet/Kredit | ✅ PDF + Excel |
| 3 | **COA Saldo Awal** | `Akuntansi > COA Saldo Awal` | Input nominal awal periode | ✅ PDF + Excel |
| 4 | **Jurnal Umum** | `Akuntansi > Jurnal Umum` | Jurnal lengkap (DRAF → POSTING by KC) | ✅ PDF + Excel |
| 5 | **Jurnal Harian** | `Akuntansi > Jurnal Harian` | Ringkasan jurnal per tanggal | ✅ PDF + Excel |
| 6 | **Buku Besar** | `Akuntansi > Buku Besar` | Mutasi per akun + running saldo | ✅ PDF + Excel |
| 7 | **Buku Kas Harian** | `Akuntansi > Buku Kas Harian` | Khusus Kas/Bank, per tanggal running | ✅ PDF + Excel |
| 8 | **Rekap Kas & Non Kas Harian** | `Akuntansi > Rekap Kas Non Kas` | Total per tanggal: Kas vs Non Kas | ✅ PDF + Excel |
| 9 | **Neraca Saldo** | `Laporan Akunting > Neraca Saldo` | Uji Balance: SA + Mutasi = SAkhir | ✅ PDF + Excel |
| 10 | **Cek Neraca Saldo** | `Laporan Akunting > Cek Neraca Saldo` | Verifikasi BALANCE / TIDAK BALANCE per akun | ✅ PDF + Excel |
| 11 | **Neraca** | `Laporan Akunting > Neraca` | Posisi Keuangan: Aktiva = Kewajiban + Modal | ✅ PDF + Excel |
| 12 | **Laba Rugi** | `Laporan Akunting > Laba Rugi` | Pendapatan (400) - Beban (500) = Laba/Rugi | ✅ PDF + Excel |
| 13 | **SHU** | `Laporan Akunting > SHU` | Distribusi Sisa Hasil Usaha (50/40/10) | ✅ PDF + Excel |
| 14 | **Arus Kas** | `Laporan Akunting > Arus Kas` | 3 Kelompok: Operasional · Investasi · Pendanaan | ✅ PDF + Excel |

---

## BAB 5 — TUTUP BUKU TAHUNAN & DISTRIBUSI SHU

### 5.1 Alur Tutup Buku
```
[AKHIR TAHUN: 31 Desember 2026]
        ↓
[Step 1 — Pastikan SEMUA Jurnal sudah DIPOSTING]
  → Cek `Jurnal Umum` filter status = DRAF → 0 baris
  → Jika masih ada DRAF → KC POSTING terlebih dahulu
        ↓
[Step 2 — Generate Laba Rugi Tahunan]
  → Buka `Laba Rugi` filter periode: 1 Jan 2026 s/d 31 Des 2026
  → Ambil nilai **Laba Bersih Setelah Pajak** (misal: Rp500.000.000)
        ↓
[Step 3 — Distribusi SHU (Rasio 50% / 40% / 10%)]
  → 50% Cadangan Umum         = 500jt × 50% = Rp250.000.000
  → 40% SHU untuk Anggota     = 500jt × 40% = Rp200.000.000
  → 10% Dana Sosial           = 500jt × 10% = Rp 50.000.000
  → TOTAL = Rp500.000.000 ✓ (sama dengan Laba Bersih)
        ↓
[Step 4 — Buat Jurnal Penutup OTOMATIS via SHU Module]
  → Debet  : Laba Rugi Ditahan            Rp500.000.000
  → Kredit : Cadangan Umum                Rp250.000.000
  → Kredit : Hutang SHU Anggota           Rp200.000.000
  → Kredit : Dana Sosial                  Rp 50.000.000
  → (Debet 500jt = Kredit 250+200+50 = 500jt ✓ Balance)
        ↓
[Step 5 — Saldo Awal Tahun Berikutnya Otomatis Terhitung]
  → Semua Akun **NERACA** (Aktiva/Kewajiban/Modal):
    Saldo Awal 2027 = Saldo Akhir 2026 (diteruskan)
  → Semua Akun **LABA RUGI** (Pendapatan/Beban):
    Saldo Awal 2027 = 0 (di-reset karena sudah ditutup ke SHU)
        ↓
[Step 6 — Kunci Periode (Opsional)]
  → Admin bisa setting COA Saldo Awal periode 2027:
    TIDAK bisa edit periode 2026 lagi (flag `is_closed = true`)
```

### 5.2 Rasio Distribusi SHU (Default)
| Komponen | Persentase | Tujuan |
|----------|-----------|--------|
| **Cadangan Umum** | 50% | Modal pengembangan koperasi, cadangan kerugian |
| **SHU Anggota** | 40% | Dibagikan ke anggota sebanding dengan Simpanan dan Volume Pinjaman |
| **Dana Sosial** | 10% | Program sosial, pendidikan anggota, bantuan musibah |

---

## BAB 6 — CONTOH E2E FLOW (1 TRANSAKSI SIMPANAN)

Contoh nyata urut **dari awal sampai laporan berubah**:

### Transaksi
> **Andi (anggota A001) setor Simpanan Sukarela Rp5.000.000 pada 15 Januari 2026 via Teller.**

### Alur Step-by-Step
```
[1] TELLER LOGIN → Buka `Transaksi > Simpanan > Create`
    → Pilih Anggota: Andi (A001)
    → Pilih Jenis: Simpanan Sukarela
    → Input Jumlah: Rp5.000.000
    → Upload bukti setor (auto ke Neo Object Storage + terkompresi)
    → Klik [Simpan]
         ↓
[2] DB::transaction OPEN
    → INSERT ke tabel `simpanan` (status=VALID)
    → Model Simpanan memicu event `created`
         ↓
[3] SimpananObserver → method `created(Simpanan $simpanan)`
    → TIPE_TRANSAKSI = `SIMPANAN_SETOR`
    → Query `coa_mapping` where tipe = 'SIMPANAN_SETOR'
    → HASIL: Debet = 1.1.01 Kas, Kredit = 2.1.03 Simpanan Sukarela
         ↓
[4] Buat Jurnal Header + Detail (wrapped transaction):
    Jurnal Header:
      → nomor_jurnal: JU-20260115-0001
      → tanggal: 2026-01-15
      → keterangan: "Setor Simpanan Sukarela - A001 Andi"
      → status: DRAF
      → id_ref: {simpanan_id}
    Jurnal Detail (2 baris):
      → Baris Debet: coa_id [1.1.01 Kas]       → debet 5.000.000 / kredit 0
      → Baris Kredit: coa_id [2.1.03 Simpanan] → debet 0 / kredit 5.000.000
    → SUM(debet=5jt) = SUM(kredit=5jt) ✓ VALID
    → COMMIT
         ↓
[5] Flash Success: "Simpanan berhasil dicatat. Jurnal DRAF menunggu posting KC."
         ↓
[6] KC LOGIN → Buka `Akuntansi > Jurnal Umum`
    → Filter Status = DRAF → terlihat 1 baris JU-20260115-0001
    → Checkbox → Klik [Posting]
    → Status berubah: DRAF → DIPOSTING ✓
         ↓
[7] SELURUH LAPORAN TERUPDATE OTOMATIS (14 modul terpengaruh):
    ┌─────────────────────────────────────────────────────────┐
    │ Jurnal Umum       → Muncul 1 baris status DIPOSTING     │
    │ Jurnal Harian     → Tanggal 15/01/2026 +5jt di Setoran │
    │ Buku Besar Kas    → Baris 15/01 → Debet 5jt → Saldo +5j │
    │ Buku Besar Simpanan → Baris 15/01 → Kredit 5jt → Saldo+5│
    │ Buku Kas Harian   → 15/01: Penerimaan 5jt, Saldo +5jt  │
    │ Rekap Kas Non Kas → Kas bertambah 5jt                   │
    │ Neraca Saldo      → Akun Kas (SA +5jt = SAkhir)        │
    │                   → Akun Simpanan (SA +5jt = SAkhir)   │
    │                   → Total Debet 5jt = Total Kredit 5jt │
    │ Cek Neraca Saldo  → Semua akun: STATUS = BALANCE ✓     │
    │ Neraca            → Aktiva (Kas +5jt)                  │
    │                   → Kewajiban (Simpanan +5jt)          │
    │                   → Aktiva = Kewajiban ✓ PADAN         │
    │ Laba Rugi         → TIDAK BERUBAH (bukan pendapatan/beban)│
    │ Arus Kas          → Operasional: Penerimaan +5jt       │
    └─────────────────────────────────────────────────────────┘
```

---

## LAMPIRAN A — DEFAULT ROLE & PERMISSION MATRIX

| Feature / Module | Admin | Teller | KC (Kepala Cabang) | Anggota |
|------------------|-------|--------|--------------------|---------|
| **Setup COA (Master)** | ✅ Full | ❌ | ✅ View | ❌ |
| **Setup COA Mapping** | ✅ Full | ❌ | ✅ Edit | ❌ |
| **Input Saldo Awal** | ✅ Full | ❌ | ✅ Edit | ❌ |
| **Transaksi Simpanan** | ✅ Full | ✅ Create/Edit | ✅ View | ✅ View (milik sendiri) |
| **Transaksi Pinjaman** | ✅ Full | ✅ Create | ✅ Approve | ✅ View (milik sendiri) |
| **Pencairan Pinjaman** | ✅ Full | ✅ Cairkan | ❌ | ❌ |
| **Pembayaran Angsuran** | ✅ Full | ✅ Bayar | ✅ View | ✅ View (milik sendiri) |
| **Jurnal Umum (Posting)** | ✅ Full | ❌ View Only | ✅ Posting/Tolak | ❌ |
| **Semua Laporan (14 modul)** | ✅ Full | ✅ PDF+Excel | ✅ Full | ❌ (hanya Simpanan/Pinjaman sendiri) |
| **Tutup Buku & SHU** | ✅ Full | ❌ | ✅ Approve | ❌ |
| **User & Role Management** | ✅ Full | ❌ | ❌ | ❌ |
| **Master Dokumen & Setting** | ✅ Full | ❌ | ✅ View | ❌ |

---

## LAMPIRAN B — CHECKLIST GO-LIVE AKUNTANSI

Sebelum transaksi pertama kali berjalan, pastikan checklist ini **100% SELURUHNYA HIJAU**:

- [ ] **Step 1 — Infrastruktur**: `composer install`, `php artisan migrate:fresh --force`, `php artisan db:seed --class=DatabaseSeeder --force`
- [ ] **Step 2 — Master Non Akuntansi (Auto Seeded)**: 3 Cabang ✓, 4 Role ✓, 150+ Permission ✓, 6 User default ✓, 4 Jenis Simpanan ✓, 4 Jenis Pinjaman ✓, 5 Master Dokumen ✓
- [ ] **Step 3 — Setup Manual via UI Admin (3 Langkah WAJIB)**:
  - [ ] COA: Semua struktur akun sudah dibuat sesuai kebijakan BSM
  - [ ] COA Mapping: KE-7 tipe transaksi (BAB 2) sudah dipasangkan Debet/Kredit, semua `is_active = true`
  - [ ] COA Saldo Awal: Semua akun diisi nominal awal periode, Total Debet = Total Kredit
- [ ] **Step 4 — Test Transaksi Dummy 1 Kali**:
  - [ ] Buat Simpanan Setoran Rp1jt → Jurnal DRAF terbentuk otomatis
  - [ ] KC Posting Jurnal → Status jadi DIPOSTING
  - [ ] Cek Neraca Saldo → Debet = Kredit (BALANCE) ✓
  - [ ] Cek Buku Besar Kas & Simpanan → Running saldo bertambah benar ✓
  - [ ] Cek Neraca → Aktiva = Kewajiban + Modal ✓ PADAN
- [ ] **Step 5 — Bersihkan Test Data**:
  - [ ] Hapus transaksi dummy (jurnal otomatis terhapus cascade)
  - [ ] Reset Saldo Awal jika nominal berubah karena test

---

## CATATAN TEKNIS PENTING
1. **Semua transaksi & jurnal di-wrap `DB::transaction()`** — jika ada kegagalan di tengah jalan, semua rollback (tidak ada data setengah jadi).
2. **Upload dokumen (bukti setor, bukti angsuran, dll) SEMUA ke Neo Object Storage** (bukan public/symlink lokal) dengan kompresi otomatis sebelum upload.
3. **Validasi SQL Injection**: Semua query menggunakan Eloquent / Query Builder parameterized, TIDAK ada raw string tanpa binding.
4. **Form Request Validasi**: Semua input user melewati Form Request class terpisah (bukan validasi inline di controller).
5. **Export PDF/Excel Semua 14 Modul**: Data export = data yang SAMA dengan filter yang sedang aktif di tabel (via query string append), jadi hasil export persis seperti yang dilihat user.
6. **Directive Blade `@hasanypermission`**: Bisa menerima string (pipe/comma) atau array permission secara fleksibel.

---
**Dokumen ini adalah panduan resmi alur sistem akuntansi BSM Group — update terakhir sesuai DatabaseSeeder tanpa dummy COA (setup 100% manual via UI Admin).**
