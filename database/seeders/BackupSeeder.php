<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\JenisPinjaman;
use App\Models\JenisSimpanan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BackupSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $cabangPusatId = Str::uuid()->toString();
            $cabangBandungId = Str::uuid()->toString();
            $cabangSurabayaId = Str::uuid()->toString();
            Cabang::insert([
                ['id' => $cabangPusatId, 'kode_cabang' => 'CAB-001', 'nama_cabang' => 'Kantor Pusat', 'alamat' => 'Jl. Raya Koperasi No. 1, Jakarta Pusat', 'telepon' => '021-12345678', 'is_active' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id' => $cabangBandungId, 'kode_cabang' => 'CAB-002', 'nama_cabang' => 'Cabang Bandung', 'alamat' => 'Jl. Asia Afrika No. 10, Bandung', 'telepon' => '022-87654321', 'is_active' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id' => $cabangSurabayaId, 'kode_cabang' => 'CAB-003', 'nama_cabang' => 'Cabang Surabaya', 'alamat' => 'Jl. Tunjungan No. 45, Surabaya', 'telepon' => '031-54321098', 'is_active' => '1', 'created_at' => now(), 'updated_at' => now()],
            ]);
            
            $roleAdminId = Str::uuid()->toString();
            $roleTellerId = Str::uuid()->toString();
            $roleKcId = Str::uuid()->toString();
            $roleAnggotaId = Str::uuid()->toString();
            Role::insert([
                ['id' => $roleAdminId, 'kode_role' => 'ROL-ADM', 'nama_role' => 'Administrator', 'is_active' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id' => $roleTellerId, 'kode_role' => 'ROL-TEL', 'nama_role' => 'Teller', 'is_active' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id' => $roleKcId, 'kode_role' => 'ROL-KC', 'nama_role' => 'Kepala Cabang', 'is_active' => '1', 'created_at' => now(), 'updated_at' => now()],
                ['id' => $roleAnggotaId, 'kode_role' => 'ROL-ANGGOTA', 'nama_role' => 'Anggota', 'is_active' => '1', 'created_at' => now(), 'updated_at' => now()],
            ]);
            $roleAdmin = Role::find($roleAdminId);
            $roleTeller = Role::find($roleTellerId);
            $roleKc = Role::find($roleKcId);
            $roleAnggota = Role::find($roleAnggotaId);

            $permissionList = [
                ['kode' => 'DASHBOARD_VIEW',       'nama' => 'Lihat Dashboard',                'desk' => 'Akses halaman dashboard utama'],

                ['kode' => 'CABANG_INDEX',         'nama' => 'Lihat Data Cabang',              'desk' => 'Menampilkan daftar cabang'],
                ['kode' => 'CABANG_CREATE',        'nama' => 'Tambah Cabang',                  'desk' => 'Menambah data cabang baru'],
                ['kode' => 'CABANG_VIEW',          'nama' => 'Detail Cabang',                  'desk' => 'Melihat detail lengkap data cabang'],
                ['kode' => 'CABANG_UPDATE',        'nama' => 'Ubah Cabang',                    'desk' => 'Mengubah data cabang'],
                ['kode' => 'CABANG_DELETE',        'nama' => 'Hapus Cabang',                   'desk' => 'Menghapus data cabang'],

                ['kode' => 'ANGGOTA_INDEX',        'nama' => 'Lihat Data Anggota',             'desk' => 'Menampilkan daftar anggota'],
                ['kode' => 'ANGGOTA_CREATE',       'nama' => 'Tambah Anggota',                 'desk' => 'Menambah data anggota baru'],
                ['kode' => 'ANGGOTA_VIEW',         'nama' => 'Detail Anggota',                 'desk' => 'Melihat detail lengkap data anggota'],
                ['kode' => 'ANGGOTA_UPDATE',       'nama' => 'Ubah Anggota',                   'desk' => 'Mengubah data anggota'],
                ['kode' => 'ANGGOTA_DELETE',       'nama' => 'Hapus Anggota',                  'desk' => 'Menghapus data anggota'],
                ['kode' => 'ANGGOTA_PENDAFTARAN_VERIFIKASI', 'nama' => 'Verifikasi Pendaftaran Anggota', 'desk' => 'Melihat daftar pendaftar baru dan memutuskan disetujui/ditolak'],

                ['kode' => 'JENIS_SIMPANAN_INDEX', 'nama' => 'Lihat Jenis Simpanan',           'desk' => 'Menampilkan daftar jenis simpanan'],
                ['kode' => 'JENIS_SIMPANAN_CREATE','nama' => 'Tambah Jenis Simpanan',          'desk' => 'Menambah master jenis simpanan'],
                ['kode' => 'JENIS_SIMPANAN_VIEW',  'nama' => 'Detail Jenis Simpanan',          'desk' => 'Melihat detail jenis simpanan'],
                ['kode' => 'JENIS_SIMPANAN_UPDATE','nama' => 'Ubah Jenis Simpanan',            'desk' => 'Mengubah master jenis simpanan'],
                ['kode' => 'JENIS_SIMPANAN_DELETE','nama' => 'Hapus Jenis Simpanan',           'desk' => 'Menghapus jenis simpanan'],

                ['kode' => 'JENIS_PINJAMAN_INDEX', 'nama' => 'Lihat Jenis Pinjaman',           'desk' => 'Menampilkan daftar jenis pinjaman'],
                ['kode' => 'JENIS_PINJAMAN_CREATE','nama' => 'Tambah Jenis Pinjaman',          'desk' => 'Menambah master jenis pinjaman'],
                ['kode' => 'JENIS_PINJAMAN_VIEW',  'nama' => 'Detail Jenis Pinjaman',          'desk' => 'Melihat detail jenis pinjaman'],
                ['kode' => 'JENIS_PINJAMAN_UPDATE','nama' => 'Ubah Jenis Pinjaman',            'desk' => 'Mengubah master jenis pinjaman'],
                ['kode' => 'JENIS_PINJAMAN_DELETE','nama' => 'Hapus Jenis Pinjaman',           'desk' => 'Menghapus jenis pinjaman'],
                ['kode' => 'JENIS_PINJAMAN_PERSYARATAN_DOKUMEN', 'nama' => 'Atur Persyaratan Dokumen Jenis Pinjaman', 'desk' => 'Mengatur daftar dokumen wajib & opsional per jenis pinjaman'],

                ['kode' => 'SIMPANAN_INDEX',       'nama' => 'Lihat Data Simpanan',            'desk' => 'Menampilkan daftar transaksi simpanan'],
                ['kode' => 'SIMPANAN_CREATE',      'nama' => 'Input Simpanan',                 'desk' => 'Menambah transaksi simpanan anggota'],
                ['kode' => 'SIMPANAN_VIEW',        'nama' => 'Detail Simpanan',                'desk' => 'Melihat detail bukti simpanan'],
                ['kode' => 'SIMPANAN_UPDATE',      'nama' => 'Ubah Simpanan',                  'desk' => 'Mengubah transaksi simpanan'],
                ['kode' => 'SIMPANAN_DELETE',      'nama' => 'Hapus Simpanan',                 'desk' => 'Menghapus transaksi simpanan'],

                ['kode' => 'PINJAMAN_INDEX',       'nama' => 'Lihat Pengajuan Pinjaman',       'desk' => 'Menampilkan daftar pengajuan pinjaman'],
                ['kode' => 'PINJAMAN_CREATE',      'nama' => 'Input Pengajuan Pinjaman',       'desk' => 'Mendaftarkan pengajuan pinjaman baru'],
                ['kode' => 'PINJAMAN_VIEW',        'nama' => 'Detail Pinjaman',                'desk' => 'Melihat detail lengkap pengajuan & dokumen pinjaman'],
                ['kode' => 'PINJAMAN_UPDATE',      'nama' => 'Ubah Pinjaman',                  'desk' => 'Mengubah data pengajuan pinjaman'],
                ['kode' => 'PINJAMAN_DELETE',      'nama' => 'Hapus Pinjaman',                 'desk' => 'Menghapus pengajuan pinjaman'],
                ['kode' => 'PINJAMAN_DOKUMEN_UPLOAD', 'nama' => 'Unggah Dokumen Pinjaman',    'desk' => 'Mengunggah & mengganti dokumen persyaratan pinjaman'],
                ['kode' => 'PINJAMAN_DOKUMEN_VERIFIKASI', 'nama' => 'Verifikasi Dokumen Pinjaman', 'desk' => 'Menyetujui / menolak / meminta perbaikan dokumen pinjaman'],
                ['kode' => 'PINJAMAN_APPROVE',     'nama' => 'Persetujuan Pengajuan Pinjaman', 'desk' => 'Verifikasi akhir, setujui & tolak pengajuan pinjaman'],

                ['kode' => 'ANGSURAN_INDEX',       'nama' => 'Lihat Tagihan Angsuran',         'desk' => 'Menampilkan daftar tagihan angsuran'],
                ['kode' => 'ANGSURAN_CREATE',      'nama' => 'Buat Tagihan Angsuran',          'desk' => 'Membuat skema tagihan / cicilan angsuran'],
                ['kode' => 'ANGSURAN_VIEW',        'nama' => 'Detail Angsuran',                'desk' => 'Melihat detail tagihan & riwayat pembayaran'],
                ['kode' => 'ANGSURAN_UPDATE',      'nama' => 'Ubah Angsuran',                  'desk' => 'Mengubah tagihan angsuran'],
                ['kode' => 'ANGSURAN_DELETE',      'nama' => 'Hapus Angsuran',                 'desk' => 'Menghapus tagihan angsuran'],

                ['kode' => 'PEMBAYARAN_INDEX',     'nama' => 'Lihat Pembayaran Angsuran',      'desk' => 'Menampilkan daftar pembayaran angsuran'],
                ['kode' => 'PEMBAYARAN_CREATE',    'nama' => 'Input Pembayaran Angsuran',      'desk' => 'Mencatat bukti pembayaran angsuran anggota'],
                ['kode' => 'PEMBAYARAN_VIEW',      'nama' => 'Detail Pembayaran',              'desk' => 'Melihat detail bukti pembayaran'],
                ['kode' => 'PEMBAYARAN_UPDATE',    'nama' => 'Ubah Pembayaran',                'desk' => 'Mengubah data pembayaran'],
                ['kode' => 'PEMBAYARAN_DELETE',    'nama' => 'Hapus Pembayaran',               'desk' => 'Menghapus catatan pembayaran'],

                ['kode' => 'LAPORAN_ANGGOTA_INDEX',      'nama' => 'Lihat Laporan Anggota',              'desk' => 'Menampilkan laporan data anggota'],
                ['kode' => 'LAPORAN_ANGGOTA_EXPORT',     'nama' => 'Export PDF Laporan Anggota',          'desk' => 'Cetak laporan anggota ke PDF'],
                ['kode' => 'LAPORAN_VERIFIKASI_INDEX',   'nama' => 'Lihat Laporan Verifikasi Pendaftaran','desk' => 'Menampilkan laporan status verifikasi pendaftaran'],
                ['kode' => 'LAPORAN_VERIFIKASI_EXPORT',  'nama' => 'Export PDF Laporan Verifikasi',       'desk' => 'Cetak laporan verifikasi ke PDF'],
                ['kode' => 'LAPORAN_SIMPANAN_INDEX',     'nama' => 'Lihat Laporan Simpanan',             'desk' => 'Menampilkan laporan transaksi simpanan'],
                ['kode' => 'LAPORAN_SIMPANAN_EXPORT',    'nama' => 'Export PDF Laporan Simpanan',         'desk' => 'Cetak laporan simpanan ke PDF'],
                ['kode' => 'LAPORAN_PINJAMAN_INDEX',     'nama' => 'Lihat Laporan Pinjaman',             'desk' => 'Menampilkan laporan pengajuan pinjaman'],
                ['kode' => 'LAPORAN_PINJAMAN_EXPORT',    'nama' => 'Export PDF Laporan Pinjaman',         'desk' => 'Cetak laporan pinjaman ke PDF'],
                ['kode' => 'LAPORAN_ANGSURAN_INDEX',     'nama' => 'Lihat Laporan Angsuran',             'desk' => 'Menampilkan laporan tagihan & status angsuran'],
                ['kode' => 'LAPORAN_ANGSURAN_EXPORT',    'nama' => 'Export PDF Laporan Angsuran',         'desk' => 'Cetak laporan angsuran ke PDF'],
                ['kode' => 'LAPORAN_PEMBAYARAN_INDEX',   'nama' => 'Lihat Laporan Pembayaran',           'desk' => 'Menampilkan laporan pembayaran angsuran'],
                ['kode' => 'LAPORAN_PEMBAYARAN_EXPORT',  'nama' => 'Export PDF Laporan Pembayaran',       'desk' => 'Cetak laporan pembayaran ke PDF'],

                ['kode' => 'AKTIVITAS_LOG_INDEX',  'nama' => 'Lihat Aktivitas Log',           'desk' => 'Menampilkan riwayat log aktivitas sistem'],

                ['kode' => 'USERS_INDEX',          'nama' => 'Lihat Data Pengguna',           'desk' => 'Menampilkan daftar user sistem'],
                ['kode' => 'USERS_CREATE',         'nama' => 'Tambah Pengguna',               'desk' => 'Membuat akun user baru'],
                ['kode' => 'USERS_VIEW',           'nama' => 'Detail Pengguna',               'desk' => 'Melihat detail akun user'],
                ['kode' => 'USERS_UPDATE',         'nama' => 'Ubah Pengguna',                 'desk' => 'Mengubah data user'],
                ['kode' => 'USERS_DELETE',         'nama' => 'Hapus Pengguna',                'desk' => 'Menonaktifkan / menghapus user'],

                ['kode' => 'ROLE_INDEX',           'nama' => 'Lihat Data Role',               'desk' => 'Menampilkan daftar role sistem'],
                ['kode' => 'ROLE_CREATE',          'nama' => 'Tambah Role',                   'desk' => 'Membuat role baru'],
                ['kode' => 'ROLE_VIEW',            'nama' => 'Detail Role',                   'desk' => 'Melihat detail role & daftar permission'],
                ['kode' => 'ROLE_UPDATE',          'nama' => 'Ubah Role',                     'desk' => 'Mengubah data role'],
                ['kode' => 'ROLE_DELETE',          'nama' => 'Hapus Role',                    'desk' => 'Menghapus role sistem'],
                ['kode' => 'ROLE_ASSIGN_PERMISSION', 'nama' => 'Atur Permission Role',        'desk' => 'Memberi & mencabut permission pada role'],

                ['kode' => 'PERMISSION_INDEX',     'nama' => 'Lihat Data Permission',         'desk' => 'Menampilkan daftar permission sistem'],
                ['kode' => 'PERMISSION_CREATE',    'nama' => 'Tambah Permission',             'desk' => 'Membuat permission baru'],
                ['kode' => 'PERMISSION_VIEW',      'nama' => 'Detail Permission',             'desk' => 'Melihat detail permission'],
                ['kode' => 'PERMISSION_UPDATE',    'nama' => 'Ubah Permission',               'desk' => 'Mengubah data permission'],
                ['kode' => 'PERMISSION_DELETE',    'nama' => 'Hapus Permission',              'desk' => 'Menghapus permission sistem'],

                ['kode' => 'ANGGOTA_DASHBOARD',      'nama' => 'Dashboard Anggota',            'desk' => 'Akses portal anggota: lihat data pribadi & dashboard sendiri'],
                ['kode' => 'ANGGOTA_SIMPANAN_VIEW', 'nama' => 'Lihat Simpanan Sendiri',        'desk' => 'Melihat riwayat simpanan milik sendiri'],
                ['kode' => 'ANGGOTA_PINJAMAN_VIEW', 'nama' => 'Lihat Pinjaman Sendiri',        'desk' => 'Melihat daftar pengajuan pinjaman milik sendiri'],
                ['kode' => 'ANGGOTA_PINJAMAN_CREATE','nama' => 'Ajukan Pinjaman Mandiri',     'desk' => 'Mengajukan pinjaman baru secara mandiri oleh anggota'],
                ['kode' => 'ANGGOTA_DOKUMEN_UPLOAD','nama' => 'Unggah Dokumen Pinjaman Sendiri', 'desk' => 'Mengunggah dokumen pinjaman milik sendiri'],

                ['kode' => 'COA_INDEX',              'nama' => 'Lihat COA / Daftar Akun',      'desk' => 'Menampilkan daftar Chart of Accounts'],
                ['kode' => 'COA_CREATE',             'nama' => 'Tambah COA',                   'desk' => 'Menambah akun baru di COA'],
                ['kode' => 'COA_VIEW',               'nama' => 'Detail COA',                   'desk' => 'Melihat detail akun COA'],
                ['kode' => 'COA_UPDATE',             'nama' => 'Ubah COA',                     'desk' => 'Mengubah master COA'],
                ['kode' => 'COA_DELETE',             'nama' => 'Hapus COA',                    'desk' => 'Menghapus akun COA (jika belum dipakai)'],
                ['kode' => 'COA_MAPPING_INDEX',      'nama' => 'Lihat Mapping Akun',           'desk' => 'Menampilkan mapping akun per tipe transaksi'],
                ['kode' => 'COA_MAPPING_UPDATE',     'nama' => 'Ubah Mapping Akun',            'desk' => 'Mengubah pasangan debet/kredit per tipe transaksi'],
                ['kode' => 'COA_SALDO_AWAL_INDEX',   'nama' => 'Lihat Saldo Awal',             'desk' => 'Menampilkan saldo awal per akun per periode'],
                ['kode' => 'COA_SALDO_AWAL_UPDATE',  'nama' => 'Ubah Saldo Awal',              'desk' => 'Mengatur saldo awal periode baru'],
                ['kode' => 'JURNAL_INDEX',           'nama' => 'Lihat Jurnal Umum',            'desk' => 'Menampilkan daftar bukti jurnal'],
                ['kode' => 'JURNAL_CREATE',          'nama' => 'Tambah Jurnal Umum',           'desk' => 'Membuat jurnal manual baru'],
                ['kode' => 'JURNAL_VIEW',            'nama' => 'Detail Jurnal Umum',           'desk' => 'Melihat detail voucher jurnal'],
                ['kode' => 'JURNAL_UPDATE',          'nama' => 'Ubah Jurnal Umum',             'desk' => 'Mengubah jurnal status draf'],
                ['kode' => 'JURNAL_DELETE',          'nama' => 'Hapus Jurnal Umum',            'desk' => 'Menghapus jurnal draf / dibatalkan'],
                ['kode' => 'JURNAL_POSTING',         'nama' => 'Posting Jurnal',               'desk' => 'Memposting jurnal draf menjadi dicatat di buku besar'],
                ['kode' => 'BUKU_BESAR_INDEX',       'nama' => 'Lihat Buku Besar',             'desk' => 'Menampilkan laporan buku besar per akun dengan saldo berjalan'],
                ['kode' => 'NERACA_VIEW',            'nama' => 'Lihat Neraca',                 'desk' => 'Menampilkan laporan neraca per tanggal cutoff'],
            ];
            $permissionIdByKode = [];
            $batchPerm = [];
            foreach ($permissionList as $p) {
                $id = Str::uuid()->toString();
                $permissionIdByKode[$p['kode']] = $id;
                $batchPerm[] = [
                    'id' => $id,
                    'kode_permission' => $p['kode'],
                    'nama_permission' => $p['nama'],
                    'deskripsi' => $p['desk'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Permission::insert($batchPerm);

            $tellerKode = [
                'DASHBOARD_VIEW',
                'CABANG_INDEX',
                'ANGGOTA_INDEX',
                'ANGGOTA_CREATE',
                'ANGGOTA_VIEW',
                'ANGGOTA_UPDATE',
                'ANGGOTA_PENDAFTARAN_VERIFIKASI',
                'JENIS_SIMPANAN_INDEX',
                'JENIS_SIMPANAN_VIEW',
                'JENIS_PINJAMAN_INDEX',
                'JENIS_PINJAMAN_VIEW',
                'SIMPANAN_INDEX',
                'SIMPANAN_CREATE',
                'SIMPANAN_VIEW',
                'SIMPANAN_UPDATE',
                'PINJAMAN_INDEX',
                'PINJAMAN_CREATE',
                'PINJAMAN_VIEW',
                'PINJAMAN_UPDATE',
                'PINJAMAN_DOKUMEN_UPLOAD',
                'ANGSURAN_INDEX',
                'ANGSURAN_VIEW',
                'PEMBAYARAN_INDEX',
                'PEMBAYARAN_CREATE',
                'PEMBAYARAN_VIEW',
                'PEMBAYARAN_UPDATE',
                'LAPORAN_ANGGOTA_INDEX',
                'LAPORAN_VERIFIKASI_INDEX',
                'LAPORAN_SIMPANAN_INDEX',
                'LAPORAN_PINJAMAN_INDEX',
                'LAPORAN_ANGSURAN_INDEX',
                'LAPORAN_PEMBAYARAN_INDEX',
                'COA_INDEX',
                'COA_VIEW',
                'COA_MAPPING_INDEX',
                'COA_SALDO_AWAL_INDEX',
                'JURNAL_INDEX',
                'JURNAL_CREATE',
                'JURNAL_VIEW',
                'JURNAL_UPDATE',
                'BUKU_BESAR_INDEX',
                'NERACA_VIEW',
            ];
            $kcKode = [
                'DASHBOARD_VIEW',
                'CABANG_INDEX',
                'CABANG_VIEW',
                'ANGGOTA_INDEX',
                'ANGGOTA_CREATE',
                'ANGGOTA_VIEW',
                'ANGGOTA_UPDATE',
                'ANGGOTA_DELETE',
                'ANGGOTA_PENDAFTARAN_VERIFIKASI',
                'JENIS_SIMPANAN_INDEX',
                'JENIS_SIMPANAN_CREATE',
                'JENIS_SIMPANAN_VIEW',
                'JENIS_SIMPANAN_UPDATE',
                'JENIS_PINJAMAN_INDEX',
                'JENIS_PINJAMAN_CREATE',
                'JENIS_PINJAMAN_VIEW',
                'JENIS_PINJAMAN_UPDATE',
                'JENIS_PINJAMAN_PERSYARATAN_DOKUMEN',
                'SIMPANAN_INDEX',
                'SIMPANAN_CREATE',
                'SIMPANAN_VIEW',
                'SIMPANAN_UPDATE',
                'SIMPANAN_DELETE',
                'PINJAMAN_INDEX',
                'PINJAMAN_CREATE',
                'PINJAMAN_VIEW',
                'PINJAMAN_UPDATE',
                'PINJAMAN_DELETE',
                'PINJAMAN_DOKUMEN_UPLOAD',
                'PINJAMAN_DOKUMEN_VERIFIKASI',
                'PINJAMAN_APPROVE',
                'ANGSURAN_INDEX',
                'ANGSURAN_CREATE',
                'ANGSURAN_VIEW',
                'ANGSURAN_UPDATE',
                'ANGSURAN_DELETE',
                'PEMBAYARAN_INDEX',
                'PEMBAYARAN_CREATE',
                'PEMBAYARAN_VIEW',
                'PEMBAYARAN_UPDATE',
                'PEMBAYARAN_DELETE',
                'LAPORAN_ANGGOTA_INDEX',
                'LAPORAN_ANGGOTA_EXPORT',
                'LAPORAN_VERIFIKASI_INDEX',
                'LAPORAN_VERIFIKASI_EXPORT',
                'LAPORAN_SIMPANAN_INDEX',
                'LAPORAN_SIMPANAN_EXPORT',
                'LAPORAN_PINJAMAN_INDEX',
                'LAPORAN_PINJAMAN_EXPORT',
                'LAPORAN_ANGSURAN_INDEX',
                'LAPORAN_ANGSURAN_EXPORT',
                'LAPORAN_PEMBAYARAN_INDEX',
                'LAPORAN_PEMBAYARAN_EXPORT',
                'COA_INDEX',
                'COA_CREATE',
                'COA_VIEW',
                'COA_UPDATE',
                'COA_DELETE',
                'COA_MAPPING_INDEX',
                'COA_MAPPING_UPDATE',
                'COA_SALDO_AWAL_INDEX',
                'COA_SALDO_AWAL_UPDATE',
                'JURNAL_INDEX',
                'JURNAL_CREATE',
                'JURNAL_VIEW',
                'JURNAL_UPDATE',
                'JURNAL_DELETE',
                'JURNAL_POSTING',
                'BUKU_BESAR_INDEX',
                'NERACA_VIEW',
            ];

            $syncRole = function (Role $role, array $kodeList) use ($permissionIdByKode) {
                $ids = collect($kodeList)->map(fn ($k) => $permissionIdByKode[$k] ?? null)->filter()->values()->all();
                $rows = array_map(fn ($pid) => [
                    'id' => Str::uuid()->toString(),
                    'role_id' => $role->id,
                    'permission_id' => $pid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $ids);
                DB::table('role_permission')->insert($rows);
            };
            $syncRole($roleTeller, $tellerKode);
            $syncRole($roleKc, $kcKode);

            $anggotaKode = [
                'ANGGOTA_DASHBOARD',
                'ANGGOTA_SIMPANAN_VIEW',
                'ANGGOTA_PINJAMAN_VIEW',
                'ANGGOTA_PINJAMAN_CREATE',
                'ANGGOTA_DOKUMEN_UPLOAD',
            ];
            $syncRole($roleAnggota, $anggotaKode);

            $userAdminId = Str::uuid()->toString();
            $userTellerId = Str::uuid()->toString();
            $userAdmin2Id = Str::uuid()->toString();
            $userKcBandungId = Str::uuid()->toString();
            $userKcSurabayaId = Str::uuid()->toString();
            $userTeller2Id = Str::uuid()->toString();
            User::insert([
                ['id' => $userAdminId, 'cabang_id' => $cabangPusatId, 'nama' => 'Administrator Sistem', 'email' => 'admin@koperasi.test', 'password' => Hash::make('password'), 'role_id' => $roleAdminId, 'nomor_hp' => '081234567890', 'is_active' => '1', 'force_change_password' => 0, 'password_changed_at' => now(), 'created_at' => now(), 'updated_at' => now()],
                ['id' => $userAdmin2Id, 'cabang_id' => $cabangPusatId, 'nama' => 'Dwi Admin IT', 'email' => 'admin2@koperasi.test', 'password' => Hash::make('password'), 'role_id' => $roleAdminId, 'nomor_hp' => '081765432109', 'is_active' => '1', 'force_change_password' => 1, 'password_changed_at' => null, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $userTellerId, 'cabang_id' => $cabangPusatId, 'nama' => 'Siti Teller Koperasi', 'email' => 'teller@koperasi.test', 'password' => Hash::make('password'), 'role_id' => $roleTellerId, 'nomor_hp' => '081987654321', 'is_active' => '1', 'force_change_password' => 1, 'password_changed_at' => null, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $userTeller2Id, 'cabang_id' => $cabangBandungId, 'nama' => 'Riza Teller Bandung', 'email' => 'teller.bandung@koperasi.test', 'password' => Hash::make('password'), 'role_id' => $roleTellerId, 'nomor_hp' => '082188990011', 'is_active' => '1', 'force_change_password' => 1, 'password_changed_at' => null, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $userKcBandungId, 'cabang_id' => $cabangBandungId, 'nama' => 'Kepala Cabang Bandung', 'email' => 'kc.bandung@koperasi.test', 'password' => Hash::make('password'), 'role_id' => $roleKcId, 'nomor_hp' => '082233445566', 'is_active' => '1', 'force_change_password' => 1, 'password_changed_at' => null, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $userKcSurabayaId, 'cabang_id' => $cabangSurabayaId, 'nama' => 'Kepala Cabang Surabaya', 'email' => 'kc.surabaya@koperasi.test', 'password' => Hash::make('password'), 'role_id' => $roleKcId, 'nomor_hp' => '083344556677', 'is_active' => '1', 'force_change_password' => 1, 'password_changed_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ]);

            $jenisSimpananPokokId = Str::uuid()->toString();
            $jenisSimpananWajibId = Str::uuid()->toString();
            $jenisSimpananSukarelaId = Str::uuid()->toString();
            $jenisSimpananHariRayaId = Str::uuid()->toString();
            JenisSimpanan::insert([
                ['id' => $jenisSimpananPokokId, 'nama_jenis' => 'Simpanan Pokok', 'keterangan' => 'Simpanan wajib dibayar saat pertama kali menjadi anggota', 'setoran_minimal' => 100000, 'setoran_maksimal' => 100000, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $jenisSimpananWajibId, 'nama_jenis' => 'Simpanan Wajib', 'keterangan' => 'Simpanan rutin dibayar setiap bulan oleh anggota', 'setoran_minimal' => 50000, 'setoran_maksimal' => 500000, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $jenisSimpananSukarelaId, 'nama_jenis' => 'Simpanan Sukarela', 'keterangan' => 'Simpanan fleksibel, bisa ditabung dan diambil kapan saja', 'setoran_minimal' => 10000, 'setoran_maksimal' => null, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $jenisSimpananHariRayaId, 'nama_jenis' => 'Simpanan Hari Raya', 'keterangan' => 'Tabungan khusus untuk kebutuhan hari raya Idul Fitri / Natal', 'setoran_minimal' => 25000, 'setoran_maksimal' => 200000, 'created_at' => now(), 'updated_at' => now()],
            ]);

            $jenisPinjamanMikroId = Str::uuid()->toString();
            $jenisPinjamanKonsumtifId = Str::uuid()->toString();
            $jenisPinjamanPendidikanId = Str::uuid()->toString();
            $jenisPinjamanBprId = Str::uuid()->toString();
            JenisPinjaman::insert([
                ['id' => $jenisPinjamanMikroId, 'nama_jenis' => 'Pinjaman Mikro Usaha', 'keterangan' => 'Pinjaman untuk pengembangan usaha mikro dan kecil', 'maksimal_plafon' => 25000000, 'bunga_tahunan' => 12, 'tenor_minimal' => 3, 'tenor_maksimal' => 24, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $jenisPinjamanKonsumtifId, 'nama_jenis' => 'Pinjaman Konsumtif', 'keterangan' => 'Pinjaman untuk kebutuhan konsumsi anggota', 'maksimal_plafon' => 15000000, 'bunga_tahunan' => 15, 'tenor_minimal' => 6, 'tenor_maksimal' => 36, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $jenisPinjamanPendidikanId, 'nama_jenis' => 'Pinjaman Pendidikan', 'keterangan' => 'Pinjaman khusus biaya pendidikan anak anggota', 'maksimal_plafon' => 20000000, 'bunga_tahunan' => 8, 'tenor_minimal' => 12, 'tenor_maksimal' => 60, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $jenisPinjamanBprId, 'nama_jenis' => 'Pinjaman Perumahan', 'keterangan' => 'Pinjaman untuk pembelian / renovasi rumah anggota', 'maksimal_plafon' => 150000000, 'bunga_tahunan' => 10, 'tenor_minimal' => 60, 'tenor_maksimal' => 240, 'created_at' => now(), 'updated_at' => now()],
            ]);
            $jenisPinjamanMikro = JenisPinjaman::find($jenisPinjamanMikroId);
            $jenisPinjamanKonsumtif = JenisPinjaman::find($jenisPinjamanKonsumtifId);
            $jenisPinjamanPendidikan = JenisPinjaman::find($jenisPinjamanPendidikanId);
            $jenisPinjamanBpr = JenisPinjaman::find($jenisPinjamanBprId);

            // Master Dokumen Persyaratan
            $masterKtpId = Str::uuid()->toString();
            $masterKkId = Str::uuid()->toString();
            $masterSlipId = Str::uuid()->toString();
            $masterSuratKerjaId = Str::uuid()->toString();
            $masterJaminanId = Str::uuid()->toString();

            \App\Models\MasterDokumen::insert([
                ['id' => $masterKtpId, 'kode_dokumen' => 'KTP', 'nama_dokumen' => 'Kartu Tanda Penduduk (KTP)', 'deskripsi' => 'KTP asli anggota pemohon pinjaman', 'format_diperbolehkan' => 'jpg,jpeg,png,pdf', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $masterKkId, 'kode_dokumen' => 'KK', 'nama_dokumen' => 'Kartu Keluarga (KK)', 'deskripsi' => 'Kartu Keluarga asli anggota', 'format_diperbolehkan' => 'jpg,jpeg,png,pdf', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $masterSlipId, 'kode_dokumen' => 'SLIP_GAJI', 'nama_dokumen' => 'Slip Gaji', 'deskripsi' => 'Slip gaji 3 bulan terakhir atau bukti penghasilan lainnya', 'format_diperbolehkan' => 'jpg,jpeg,png,pdf', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $masterSuratKerjaId, 'kode_dokumen' => 'SURAT_KERJA', 'nama_dokumen' => 'Surat Keterangan Kerja', 'deskripsi' => 'Surat keterangan bekerja dari instansi/perusahaan tempat anggota bekerja', 'format_diperbolehkan' => 'jpg,jpeg,png,pdf', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['id' => $masterJaminanId, 'kode_dokumen' => 'DOK_JAMINAN', 'nama_dokumen' => 'Dokumen Jaminan', 'deskripsi' => 'Dokumen agunan/jaminan sesuai jenis pinjaman (SHM, Sertifikat Kendaraan, dll)', 'format_diperbolehkan' => 'jpg,jpeg,png,pdf', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // Sync Persyaratan per Jenis Pinjaman
            $urutan = 1;
            $jenisPinjamanMikro->dokumenPersyaratan()->sync([
                $masterKtpId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterKkId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterSlipId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterSuratKerjaId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterJaminanId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
            ]);

            $urutan = 1;
            $jenisPinjamanKonsumtif->dokumenPersyaratan()->sync([
                $masterKtpId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterKkId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterSlipId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterSuratKerjaId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
            ]);

            $urutan = 1;
            $jenisPinjamanPendidikan->dokumenPersyaratan()->sync([
                $masterKtpId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterKkId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterSlipId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterSuratKerjaId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
            ]);

            $urutan = 1;
            $jenisPinjamanBpr->dokumenPersyaratan()->sync([
                $masterKtpId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterKkId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterSlipId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterSuratKerjaId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
                $masterJaminanId => ['id' => Str::uuid()->toString(), 'is_wajib' => true, 'urutan' => $urutan++],
            ]);

            // ============================================================
            // INCREMENTAL SAFE UPSERT: Permission + Role + COA + Mapping
            // (Berjalan aman meskipun seeder dijalankan berulang kali)
            // ============================================================

            $newPermissionList = [
                ['kode' => 'COA_INDEX', 'nama' => 'Lihat COA / Daftar Akun', 'desk' => 'Menampilkan daftar Chart of Accounts'],
                ['kode' => 'COA_CREATE', 'nama' => 'Tambah COA', 'desk' => 'Menambah akun baru di COA'],
                ['kode' => 'COA_VIEW', 'nama' => 'Detail COA', 'desk' => 'Melihat detail akun COA'],
                ['kode' => 'COA_UPDATE', 'nama' => 'Ubah COA', 'desk' => 'Mengubah master COA'],
                ['kode' => 'COA_DELETE', 'nama' => 'Hapus COA', 'desk' => 'Menghapus akun COA (jika belum dipakai)'],
                ['kode' => 'COA_MAPPING_INDEX', 'nama' => 'Lihat Mapping Akun', 'desk' => 'Menampilkan mapping akun per tipe transaksi'],
                ['kode' => 'COA_MAPPING_UPDATE', 'nama' => 'Ubah Mapping Akun', 'desk' => 'Mengubah pasangan debet/kredit per tipe transaksi'],
                ['kode' => 'COA_SALDO_AWAL_INDEX', 'nama' => 'Lihat Saldo Awal', 'desk' => 'Menampilkan saldo awal per akun per periode'],
                ['kode' => 'COA_SALDO_AWAL_UPDATE', 'nama' => 'Ubah Saldo Awal', 'desk' => 'Mengatur saldo awal periode baru'],
                ['kode' => 'JURNAL_INDEX', 'nama' => 'Lihat Jurnal Umum', 'desk' => 'Menampilkan daftar bukti jurnal (CRUD manual)'],
                ['kode' => 'JURNAL_CREATE', 'nama' => 'Tambah Jurnal Umum', 'desk' => 'Membuat jurnal manual baru'],
                ['kode' => 'JURNAL_VIEW', 'nama' => 'Detail Jurnal Umum', 'desk' => 'Melihat detail voucher jurnal'],
                ['kode' => 'JURNAL_UPDATE', 'nama' => 'Ubah Jurnal Umum', 'desk' => 'Mengubah jurnal status draf'],
                ['kode' => 'JURNAL_DELETE', 'nama' => 'Hapus Jurnal Umum', 'desk' => 'Menghapus jurnal draf / dibatalkan'],
                ['kode' => 'JURNAL_POSTING', 'nama' => 'Posting Jurnal', 'desk' => 'Memposting jurnal draf menjadi dicatat di buku besar'],
                ['kode' => 'JURNAL_HARIAN_VIEW', 'nama' => 'Lihat Jurnal Harian', 'desk' => 'Laporan jurnal harian rekap per tanggal tanpa aksi CRUD'],
                ['kode' => 'BUKU_BESAR_INDEX', 'nama' => 'Lihat Buku Besar', 'desk' => 'Menampilkan laporan buku besar per akun dengan saldo berjalan'],
                ['kode' => 'REKAP_KAS_VIEW', 'nama' => 'Lihat Rekap Kas & Non Kas Harian', 'desk' => 'Laporan rekapitulasi penerimaan kas vs non kas per hari'],
                ['kode' => 'BUKU_KAS_VIEW', 'nama' => 'Lihat Buku Kas Harian', 'desk' => 'Laporan mutasi saldo kas & bank harian dengan saldo berjalan'],
                ['kode' => 'LABA_RUGI_PERIODE_VIEW', 'nama' => 'Lihat Laba Rugi Periode', 'desk' => 'Laporan laba rugi per periode tanggal mulai sampai akhir'],
                ['kode' => 'LABA_RUGI_KUMULATIF_VIEW', 'nama' => 'Lihat Laba Rugi Kumulatif', 'desk' => 'Laporan laba rugi YTD (akumulasi sejak awal tahun)'],
                ['kode' => 'NERACA_VIEW', 'nama' => 'Lihat Neraca', 'desk' => 'Menampilkan laporan neraca per tanggal cutoff'],
                ['kode' => 'NERACA_SALDO_VIEW', 'nama' => 'Lihat Neraca Saldo', 'desk' => 'Laporan Trial Balance 6 kolom (Saldo Awal, Mutasi, Saldo Akhir)'],
                ['kode' => 'TUTUP_BUKU_EXEC', 'nama' => 'Eksekusi Tutup Buku', 'desk' => 'Membuat jurnal penutup otomatis akun pendapatan & beban ke SHU per periode'],
                ['kode' => 'CEK_NERACA_SALDO_VIEW', 'nama' => 'Lihat Cek Neraca dan Saldo', 'desk' => 'Tool verifikasi keseimbangan total debet kredit saldo awal & mutasi'],
                ['kode' => 'LAP_SHU_VIEW', 'nama' => 'Lihat Laporan Sisa Hasil Usaha', 'desk' => 'Laporan SHU per tahun buku (sisa hasil usaha setelah pajak)'],
                ['kode' => 'LAP_ARUS_KAS_VIEW', 'nama' => 'Lihat Laporan Arus Kas', 'desk' => 'Laporan arus kas (aktivitas operasional, investasi, pendanaan)'],
                ['kode' => 'MASTER_DOKUMEN_INDEX', 'nama' => 'Lihat Master Dokumen', 'desk' => 'Menampilkan daftar master persyaratan dokumen'],
                ['kode' => 'MASTER_DOKUMEN_CREATE', 'nama' => 'Tambah Master Dokumen', 'desk' => 'Menambah master persyaratan dokumen baru'],
                ['kode' => 'MASTER_DOKUMEN_VIEW', 'nama' => 'Detail Master Dokumen', 'desk' => 'Melihat detail master dokumen beserta pemakaiannya'],
                ['kode' => 'MASTER_DOKUMEN_UPDATE', 'nama' => 'Ubah Master Dokumen', 'desk' => 'Mengubah master dokumen dan status aktifnya'],
                ['kode' => 'MASTER_DOKUMEN_DELETE', 'nama' => 'Hapus Master Dokumen', 'desk' => 'Menghapus master dokumen yang belum terpakai'],
                ['kode' => 'SIMULASI_PINJAMAN_VIEW', 'nama' => 'Lihat Simulasi Pinjaman', 'desk' => 'Menggunakan kalkulator simulasi angsuran pinjaman'],
                ['kode' => 'SETTING_PERSYARATAN_INDEX', 'nama' => 'Lihat Setting Persyaratan Dokumen', 'desk' => 'Menampilkan pengaturan persyaratan dokumen per jenis pinjaman'],
                ['kode' => 'SETTING_PERSYARATAN_UPDATE', 'nama' => 'Ubah Setting Persyaratan Dokumen', 'desk' => 'Mengatur daftar dokumen wajib per jenis pinjaman'],
            ];
            $permBatch = collect($newPermissionList)->map(fn ($p) => [
                'kode_permission' => $p['kode'],
                'nama_permission' => $p['nama'],
                'deskripsi' => $p['desk'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->values()->all();
            Permission::upsert($permBatch, ['kode_permission'], ['nama_permission', 'deskripsi', 'updated_at']);
            $permIdByKode = Permission::whereIn('kode_permission', collect($newPermissionList)->pluck('kode'))
                ->pluck('id', 'kode_permission');

            $adminRole = Role::where('kode_role', 'ROL-ADM')->first();
            if ($adminRole) {
                $allPermIds = Permission::pluck('id')->all();
                $adminRole->permissions()->syncWithoutDetaching($allPermIds);
            }
            $tellerRole = Role::where('kode_role', 'ROL-TEL')->first();
            if ($tellerRole) {
                $tellerPerms = [
                    'COA_INDEX','COA_VIEW','COA_MAPPING_INDEX','COA_SALDO_AWAL_INDEX',
                    'JURNAL_INDEX','JURNAL_CREATE','JURNAL_VIEW','JURNAL_UPDATE',
                    'JURNAL_HARIAN_VIEW','BUKU_BESAR_INDEX','NERACA_VIEW','NERACA_SALDO_VIEW',
                    'REKAP_KAS_VIEW','BUKU_KAS_VIEW',
                    'LABA_RUGI_PERIODE_VIEW','LABA_RUGI_KUMULATIF_VIEW',
                    'CEK_NERACA_SALDO_VIEW',
                    'LAP_SHU_VIEW','LAP_ARUS_KAS_VIEW',
                    'MASTER_DOKUMEN_INDEX','MASTER_DOKUMEN_VIEW',
                    'SETTING_PERSYARATAN_INDEX',
                    'SIMULASI_PINJAMAN_VIEW'
                ];
                $ids = collect($tellerPerms)->map(fn ($k) => $permIdByKode[$k] ?? null)->filter()->values()->all();
                $tellerRole->permissions()->syncWithoutDetaching($ids);
            }
            $kcRole = Role::where('kode_role', 'ROL-KC')->first();
            if ($kcRole) {
                $kcPerms = [
                    'COA_INDEX','COA_CREATE','COA_VIEW','COA_UPDATE','COA_DELETE',
                    'COA_MAPPING_INDEX','COA_MAPPING_UPDATE',
                    'COA_SALDO_AWAL_INDEX','COA_SALDO_AWAL_UPDATE',
                    'JURNAL_INDEX','JURNAL_CREATE','JURNAL_VIEW','JURNAL_UPDATE','JURNAL_DELETE','JURNAL_POSTING',
                    'JURNAL_HARIAN_VIEW','BUKU_BESAR_INDEX','NERACA_VIEW','NERACA_SALDO_VIEW',
                    'REKAP_KAS_VIEW','BUKU_KAS_VIEW',
                    'LABA_RUGI_PERIODE_VIEW','LABA_RUGI_KUMULATIF_VIEW',
                    'TUTUP_BUKU_EXEC','CEK_NERACA_SALDO_VIEW',
                    'LAP_SHU_VIEW','LAP_ARUS_KAS_VIEW',
                    'MASTER_DOKUMEN_INDEX','MASTER_DOKUMEN_CREATE','MASTER_DOKUMEN_VIEW','MASTER_DOKUMEN_UPDATE','MASTER_DOKUMEN_DELETE',
                    'SETTING_PERSYARATAN_INDEX','SETTING_PERSYARATAN_UPDATE',
                    'SIMULASI_PINJAMAN_VIEW'
                ];
                $ids = collect($kcPerms)->map(fn ($k) => $permIdByKode[$k] ?? null)->filter()->values()->all();
                $kcRole->permissions()->syncWithoutDetaching($ids);
            }
            // Default Master Dokumen (global, idempotent tanpa duplikat)
            $masterDefault = [
                ['kode' => 'DOC-KTP',      'nama' => 'Kartu Tanda Penduduk (KTP)', 'desk' => 'KTP suami / istri sesuai KUA', 'format' => 'jpg,jpeg,png,pdf'],
                ['kode' => 'DOC-KK',       'nama' => 'Kartu Keluarga (KK)',        'desk' => 'Kartu keluarga terbaru',      'format' => 'jpg,jpeg,png,pdf'],
                ['kode' => 'DOC-SLIPGAJI', 'nama' => 'Slip Gaji / Bukti Penghasilan', 'desk' => 'Slip gaji 3 bulan terakhir / surat keterangan penghasilan dari pemberi kerja', 'format' => 'jpg,jpeg,png,pdf'],
                ['kode' => 'DOC-SK',       'nama' => 'Surat Kerja / SK Pengangkatan', 'desk' => 'SK kerja aktif atau kontrak kerja', 'format' => 'jpg,jpeg,png,pdf'],
                ['kode' => 'DOC-JAMINAN',  'nama' => 'Surat Jaminan / Agunan',     'desk' => 'BPKB, sertifikat tanah, surat kendaraan atau agunan lainnya', 'format' => 'jpg,jpeg,png,pdf'],
                ['kode' => 'DOC-SKBM',     'nama' => 'Surat Keterangan Bekerja Mandiri', 'desk' => 'Bagi wirausaha: surat keterangan usaha dari RT/RW / SIUP / NIB', 'format' => 'jpg,jpeg,png,pdf'],
                ['kode' => 'DOC-FOTO',     'nama' => 'Foto Diri 3x4',               'desk' => 'Foto terbaru pasangan suami istri', 'format' => 'jpg,jpeg,png'],
                ['kode' => 'DOC-REKENING', 'nama' => 'Buku Rekening Koran / Mutasi', 'desk' => 'Mutasi rekening tabungan 3 bulan terakhir', 'format' => 'jpg,jpeg,png,pdf'],
            ];
            foreach ($masterDefault as $row) {
                $existing = DB::table('master_dokumen')->where('kode_dokumen', $row['kode'])->first();
                $payload = [
                    'nama_dokumen' => $row['nama'],
                    'deskripsi' => $row['desk'],
                    'format_diperbolehkan' => $row['format'],
                    'is_active' => true,
                    'updated_at' => now(),
                ];
                if ($existing) {
                    DB::table('master_dokumen')->where('id', $existing->id)->update($payload);
                } else {
                    DB::table('master_dokumen')->insert(array_merge([
                        'id' => Str::uuid()->toString(),
                        'kode_dokumen' => $row['kode'],
                        'created_at' => now(),
                    ], $payload));
                }
            }

            // -------- COA Default (global, cabang_id = null) --- 130+ akun sesuai Standar Akuntansi Koperasi --------
            $coaDefault = [
                // ========== 1. AKTIVA ==========
                // ===== 1.1 AKTIVA LANCAR =====
                ['kode' => '110.00.00', 'nama' => 'AKTIVA LANCAR',                   'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => null],
                ['kode' => '111.00.00', 'nama' => 'Setara Kas',                       'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '110.00.00'],
                ['kode' => '111.01.00', 'nama' => 'Kas',                              'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '111.00.00'],
                ['kode' => '111.02.00', 'nama' => 'Kas Mobile',                       'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '111.00.00'],
                ['kode' => '112.00.00', 'nama' => 'Bank',                             'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '110.00.00'],
                ['kode' => '112.01.00', 'nama' => 'Bank Induk',                       'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '112.00.00'],
                ['kode' => '112.01.01', 'nama' => 'Bank BCA',                         'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '112.01.00'],
                ['kode' => '112.02.01', 'nama' => 'Bank PERMATA',                     'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '112.01.00'],
                ['kode' => '112.03.01', 'nama' => 'Bank MANDIRI',                     'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '112.01.00'],
                ['kode' => '113.00.00', 'nama' => 'Deposito',                         'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '110.00.00'],
                ['kode' => '113.01.00', 'nama' => 'Deposito BCA',                     'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '113.00.00'],
                ['kode' => '114.00.00', 'nama' => 'Piutang Pinjaman Anggota',         'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '110.00.00'],
                ['kode' => '114.01.00', 'nama' => 'Piutang Angsuta',                  'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '114.00.00'],
                ['kode' => '114.02.00', 'nama' => 'Pinjaman Kendaraan',               'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '114.00.00'],
                ['kode' => '114.03.00', 'nama' => 'Pinjaman Usaha',                   'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '114.00.00'],
                ['kode' => '114.04.00', 'nama' => 'Pinjaman Konsumtif',               'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '114.00.00'],
                ['kode' => '114.05.00', 'nama' => 'Pinjaman Multiguna',               'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '114.00.00'],
                ['kode' => '114.06.00', 'nama' => 'Sisa Dana Talangan',               'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '114.00.00'],
                ['kode' => '115.00.00', 'nama' => 'Cadangan Kerugian Piutang',        'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '110.00.00'],
                ['kode' => '115.01.00', 'nama' => 'Cadangan Piutang Bersih',          'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '115.00.00'],
                ['kode' => '115.02.00', 'nama' => 'Akumulasi Cadangan Piutang',       'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '115.00.00'],
                ['kode' => '116.00.00', 'nama' => 'Persediaan',                       'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '110.00.00'],
                ['kode' => '116.01.00', 'nama' => 'Persediaan Manul',                 'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '116.00.00'],
                ['kode' => '116.02.00', 'nama' => 'Persediaan Barang Cetakan',        'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '116.00.00'],
                ['kode' => '117.00.00', 'nama' => 'Perlengkapan',                     'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '110.00.00'],
                ['kode' => '117.01.00', 'nama' => 'Perlengkapan Kerja',               'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '117.00.00'],
                ['kode' => '118.00.00', 'nama' => 'Kas Antar Cabang & Digital',       'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '110.00.00'],
                ['kode' => '118.01.00', 'nama' => 'Kas Tunai Kantor Cabang',          'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '118.00.00'],
                ['kode' => '118.02.00', 'nama' => 'Transfer Online Antar Cabang',     'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '118.00.00'],
                ['kode' => '118.03.00', 'nama' => 'Saluran Produk Digital',           'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '118.00.00'],
                ['kode' => '118.04.00', 'nama' => 'Kerjasama Bank Koresponden',       'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '118.00.00'],
                ['kode' => '118.05.00', 'nama' => 'Kerjasama Antar Koperasi',         'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '118.00.00'],
                ['kode' => '119.00.00', 'nama' => 'Aktiva Lancar Lainnya',            'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '110.00.00'],
                ['kode' => '119.01.00', 'nama' => 'Ruko / Gedung Investasi',          'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '119.00.00'],
                ['kode' => '119.02.00', 'nama' => 'Aktiva Lancar Lain-lain',          'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '119.00.00'],

                // ===== 1.2 AKTIVA TETAP =====
                ['kode' => '120.00.00', 'nama' => 'AKTIVA TETAP',                     'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => null],
                ['kode' => '120.01.00', 'nama' => 'Tanah',                            'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '120.00.00'],
                ['kode' => '120.02.00', 'nama' => 'Bangunan',                         'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '120.00.00'],
                ['kode' => '120.03.00', 'nama' => 'Kendaraan',                        'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '120.00.00'],
                ['kode' => '120.04.00', 'nama' => 'Inventaris Kantor',                'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '120.00.00'],
                ['kode' => '120.05.00', 'nama' => 'Aktiva Tetap Lainnya',             'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '120.00.00'],
                ['kode' => '120.06.00', 'nama' => 'Peralatan Kantor',                 'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'debet',  'parent' => '120.00.00'],
                ['kode' => '121.00.00', 'nama' => 'Akumulasi Penyusutan Aktiva',      'level' => 1, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '120.00.00'],
                ['kode' => '121.01.00', 'nama' => 'Akum. Penyusutan Bangunan',        'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '121.00.00'],
                ['kode' => '121.02.00', 'nama' => 'Akum. Penyusutan Kendaraan',       'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '121.00.00'],
                ['kode' => '121.03.00', 'nama' => 'Akum. Penyusutan Inventaris',      'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '121.00.00'],
                ['kode' => '121.04.00', 'nama' => 'Akum. Peny. Aktiva Lainnya',       'level' => 2, 'kel' => 'aset',       'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '121.00.00'],

                // ========== 2. KEWAJIBAN ==========
                ['kode' => '200.00.00', 'nama' => 'KEWAJIBAN',                        'level' => 1, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => null],
                ['kode' => '210.00.00', 'nama' => 'Simpanan Anggota',                 'level' => 1, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '200.00.00'],
                ['kode' => '210.01.00', 'nama' => 'Simpanan Angsuta',                 'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.02.00', 'nama' => 'Simpanan Sukarela',                'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.03.00', 'nama' => 'Simpanan Hari Raya',               'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.04.00', 'nama' => 'Simpanan Hari Tua',                'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.05.00', 'nama' => 'Simpanan Berencana',               'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.06.01', 'nama' => 'Simpanan Pendidikan 1 Tahun',      'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.07.01', 'nama' => 'Simpanan Pendidikan 2 Tahun',      'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.08.01', 'nama' => 'Simpanan Pendidikan 3 Tahun',      'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.09.01', 'nama' => 'Simpanan Qurban 1 Tahun',          'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.10.01', 'nama' => 'Simpanan Qurban 2 Tahun',          'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.11.01', 'nama' => 'Simpanan Qurban 3 Tahun',          'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.12.01', 'nama' => 'Simpanan Umroh 1 Tahun',           'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.13.01', 'nama' => 'Simpanan Umroh 2 Tahun',           'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.14.01', 'nama' => 'Simpanan Umroh 3 Tahun',           'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.15.01', 'nama' => 'Simpanan Renovasi Rumah',          'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.16.06', 'nama' => 'Simpanan Deposito 6 Bulan',        'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.17.12', 'nama' => 'Simpanan Deposito 12 Bulan',       'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.18.18', 'nama' => 'Simpanan Deposito 18 Bulan',       'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.19.24', 'nama' => 'Simpanan Deposito 24 Bulan',       'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.20.00', 'nama' => 'Simpanan Goro',                    'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '210.21.00', 'nama' => 'Simpanan Wisata',                  'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '210.00.00'],
                ['kode' => '220.00.00', 'nama' => 'Hutang Jangka Panjang',            'level' => 1, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '200.00.00'],
                ['kode' => '220.01.00', 'nama' => 'Panjar Angsuta Koperasi',          'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '220.00.00'],
                ['kode' => '220.02.00', 'nama' => 'Hutang Pihak Ketiga',              'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '220.00.00'],
                ['kode' => '230.00.00', 'nama' => 'Dana dan Kewajiban Lainnya',       'level' => 1, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '200.00.00'],
                ['kode' => '230.01.00', 'nama' => 'Dana Darurat',                     'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '230.00.00'],
                ['kode' => '230.02.01', 'nama' => 'Dana Pendidikan',                  'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '230.00.00'],
                ['kode' => '230.03.01', 'nama' => 'Dana Solidaritas',                 'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '230.00.00'],
                ['kode' => '230.04.00', 'nama' => 'Kewajiban Kemenkumham',            'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '230.00.00'],
                ['kode' => '230.05.01', 'nama' => 'Beban Dibayar Dimuka',             'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '230.00.00'],
                ['kode' => '240.00.00', 'nama' => 'Hutang Lain-lain',                 'level' => 1, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '200.00.00'],
                ['kode' => '240.01.01', 'nama' => 'Hutang YM Chinesse',               'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '240.00.00'],
                ['kode' => '240.02.01', 'nama' => 'PPH 21 Karyawan',                  'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '240.00.00'],
                ['kode' => '240.03.01', 'nama' => 'Cadangan Biaya Notaris',           'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '240.00.00'],
                ['kode' => '240.04.00', 'nama' => 'Modal Penyertaan',                 'level' => 2, 'kel' => 'kewajiban',  'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '240.00.00'],

                // ========== 3. MODAL (EKUITAS) ==========
                ['kode' => '300.00.00', 'nama' => 'MODAL',                            'level' => 1, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => null],
                ['kode' => '310.00.00', 'nama' => 'Simpanan Modal Anggota',           'level' => 1, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '300.00.00'],
                ['kode' => '310.01.00', 'nama' => 'Simpanan Modal Angsuta',           'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '310.00.00'],
                ['kode' => '310.02.00', 'nama' => 'Simpanan Pokok',                   'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '310.00.00'],
                ['kode' => '310.03.00', 'nama' => 'Simpanan Wajib',                   'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '310.00.00'],
                ['kode' => '320.00.00', 'nama' => 'Dana Modal Cadangan',              'level' => 1, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '300.00.00'],
                ['kode' => '320.01.00', 'nama' => 'Dana Cadangan',                    'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '320.00.00'],
                ['kode' => '320.02.00', 'nama' => 'Dana Laba Ditahan',                'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '320.00.00'],
                ['kode' => '320.03.00', 'nama' => 'Dana Cadangan Bersih',             'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '320.00.00'],
                ['kode' => '330.00.00', 'nama' => 'Sisa Hasil Usaha (SHU)',            'level' => 1, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '300.00.00'],
                ['kode' => '330.01.01', 'nama' => 'SHU Tahun Berjalan',               'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '330.00.00'],
                ['kode' => '330.02.01', 'nama' => 'SHU Tahun Lalu',                   'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '330.00.00'],
                ['kode' => '330.03.01', 'nama' => 'SHU Kumulatif',                    'level' => 2, 'kel' => 'ekuitas',    'pos' => 'neraca',    'sn' => 'kredit', 'parent' => '330.00.00'],

                // ========== 4. PENDAPATAN ==========
                ['kode' => '400.00.00', 'nama' => 'PENDAPATAN',                       'level' => 1, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => null],
                ['kode' => '410.00.00', 'nama' => 'Pendapatan Bunga & Jasa',          'level' => 1, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '400.00.00'],
                ['kode' => '410.01.00', 'nama' => 'Pendapatan Bunga Pinjaman',        'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.00.00'],
                ['kode' => '410.01.02', 'nama' => 'Bunga Pinjaman Kendaraan',         'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.01.00'],
                ['kode' => '410.01.03', 'nama' => 'Bunga Pinjaman Usaha',             'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.01.00'],
                ['kode' => '410.01.04', 'nama' => 'Bunga Pinjaman Konsumtif',         'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.01.00'],
                ['kode' => '410.01.05', 'nama' => 'Bunga Pinjaman Multiguna',         'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.01.00'],
                ['kode' => '410.01.06', 'nama' => 'Bunga Siaga / Dana Talangan',      'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.01.00'],
                ['kode' => '410.97.00', 'nama' => 'Provisi Pinjaman',                 'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.00.00'],
                ['kode' => '410.98.00', 'nama' => 'Administrasi & Layanan',            'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.00.00'],
                ['kode' => '410.99.00', 'nama' => 'Denda & Keterlambatan',            'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '410.00.00'],
                ['kode' => '420.00.00', 'nama' => 'Pendapatan Bunga Bank',            'level' => 1, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '400.00.00'],
                ['kode' => '420.01.01', 'nama' => 'Bunga Bank BCA',                   'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '420.00.00'],
                ['kode' => '420.02.01', 'nama' => 'Bunga Bank PERMATA',               'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '420.00.00'],
                ['kode' => '420.03.01', 'nama' => 'Bunga Bank MANDIRI',               'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '420.00.00'],
                ['kode' => '430.00.00', 'nama' => 'Pendapatan Non Operasional',       'level' => 1, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '400.00.00'],
                ['kode' => '430.01.01', 'nama' => 'Buku Simpanan / Denda Anggota',    'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '430.00.00'],
                ['kode' => '430.02.01', 'nama' => 'Penjualan Aktiva Tetap',           'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '430.00.00'],
                ['kode' => '430.99.00', 'nama' => 'Pendapatan Lainnya',               'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '430.00.00'],
                ['kode' => '440.00.00', 'nama' => 'Pendapatan Layanan Digital',       'level' => 1, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '400.00.00'],
                ['kode' => '440.01.00', 'nama' => 'Platform Layanan Digital',         'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '440.00.00'],
                ['kode' => '440.02.00', 'nama' => 'Pendapatan Produk Digital',        'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '440.00.00'],
                ['kode' => '440.03.00', 'nama' => 'Jasa Berbagi Bank',                'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '440.00.00'],
                ['kode' => '440.04.00', 'nama' => 'Pendapatan Antar Koperasi',        'level' => 2, 'kel' => 'pendapatan', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => '440.00.00'],

                // ========== 5. BEBAN ==========
                ['kode' => '500.00.00', 'nama' => 'BEBAN',                            'level' => 1, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => null],
                ['kode' => '510.00.00', 'nama' => 'Beban Bunga Simpanan',             'level' => 1, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '500.00.00'],
                ['kode' => '511.01.00', 'nama' => 'Bunga Simpanan Sukarela',          'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.02.00', 'nama' => 'Bunga Simpanan Hari Raya',         'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.03.00', 'nama' => 'Bunga Simpanan Hari Tua',          'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.04.01', 'nama' => 'Bunga Simpanan Pendidikan 1',      'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.05.01', 'nama' => 'Bunga Simpanan Pendidikan 2',      'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.06.01', 'nama' => 'Bunga Simpanan Pendidikan 3',      'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.07.01', 'nama' => 'Bunga Simpanan Qurban 1',          'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.08.01', 'nama' => 'Bunga Simpanan Qurban 2',          'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.09.01', 'nama' => 'Bunga Simpanan Qurban 3',          'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.10.01', 'nama' => 'Bunga Simpanan Umroh 1',           'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.11.01', 'nama' => 'Bunga Simpanan Umroh 2',           'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.12.01', 'nama' => 'Bunga Simpanan Umroh 3',           'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.13.00', 'nama' => 'Bunga Simpanan Aksi Sosial',       'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.14.00', 'nama' => 'Bunga Simpanan Berencana',         'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.16.06', 'nama' => 'Bunga Simpanan Deposito 6',        'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.17.12', 'nama' => 'Bunga Simpanan Deposito 12',       'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.18.18', 'nama' => 'Bunga Simpanan Deposito 18',       'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.19.24', 'nama' => 'Bunga Simpanan Deposito 24',       'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.20.00', 'nama' => 'Bunga Simpanan Goro',              'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '511.21.00', 'nama' => 'Bunga Simpanan Wisata',            'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '510.00.00'],
                ['kode' => '512.00.00', 'nama' => 'Beban Operasional',                'level' => 1, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '500.00.00'],
                ['kode' => '512.01.00', 'nama' => 'Gaji Karyawan',                    'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.02.00', 'nama' => 'Tunjangan Karyawan',               'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.03.00', 'nama' => 'Rekening Listrik / Telepon',       'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.04.00', 'nama' => 'Pembelian Alat Tulis Kantor',      'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.05.00', 'nama' => 'Pemeliharaan Kantor',              'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.06.00', 'nama' => 'Transportasi & Transaksi',         'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.07.00', 'nama' => 'Internet & Jaringan',              'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.08.00', 'nama' => 'Sewa Gedung & Ruang',              'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.09.00', 'nama' => 'Beban Cadangan Kerugian',          'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.98.00', 'nama' => 'Beban Administrasi Lainnya',       'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '512.99.00', 'nama' => 'BPJS & Ketenagakerjaan',           'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '512.00.00'],
                ['kode' => '513.00.00', 'nama' => 'Beban Penyusutan',                 'level' => 1, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '500.00.00'],
                ['kode' => '513.01.00', 'nama' => 'Beban Penyusutan Bangunan',        'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '513.00.00'],
                ['kode' => '513.02.00', 'nama' => 'Beban Penyusutan Kendaraan',       'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '513.00.00'],
                ['kode' => '513.03.00', 'nama' => 'Beban Penyusutan Inventaris',      'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '513.00.00'],
                ['kode' => '513.04.00', 'nama' => 'Beban Penyusutan Lainnya',         'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '513.00.00'],
                ['kode' => '520.00.00', 'nama' => 'PAJAK',                            'level' => 1, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '500.00.00'],
                ['kode' => '520.01.01', 'nama' => 'Pajak Penghasilan 21',             'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '520.00.00'],
                ['kode' => '520.02.01', 'nama' => 'Pajak Penghasilan 23',             'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '520.00.00'],
                ['kode' => '520.03.01', 'nama' => 'Pajak Penghasilan 25',             'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '520.00.00'],
                ['kode' => '520.99.00', 'nama' => 'Pajak Lainnya',                    'level' => 2, 'kel' => 'beban',      'pos' => 'laba_rugi', 'sn' => 'debet',  'parent' => '520.00.00'],

                ['kode' => '900.00.00', 'nama' => 'IKHTISAR LABA RUGI',               'level' => 1, 'kel' => 'ikhtisar_laba_rugi', 'pos' => 'laba_rugi', 'sn' => 'kredit', 'parent' => null],
            ];
            $parentIdByKode = [];
            foreach ($coaDefault as $row) {
                $payload = [
                    'parent_id' => $row['parent'] ? ($parentIdByKode[$row['parent']] ?? null) : null,
                    'cabang_id' => null,
                    'kode_akun' => $row['kode'],
                    'nama_akun' => $row['nama'],
                    'level' => $row['level'],
                    'kelompok' => $row['kel'],
                    'posisi_laporan' => $row['pos'],
                    'saldo_normal' => $row['sn'],
                    'is_active' => '1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $existing = DB::table('coa')
                    ->where('cabang_id', null)
                    ->where('kode_akun', $row['kode'])
                    ->first();
                if ($existing) {
                    DB::table('coa')->where('id', $existing->id)->update(array_diff_key($payload, ['created_at' => true]));
                    $parentIdByKode[$row['kode']] = $existing->id;
                } else {
                    $newId = Str::uuid()->toString();
                    DB::table('coa')->insert(array_merge(['id' => $newId], $payload));
                    $parentIdByKode[$row['kode']] = $newId;
                }
            }
            // Refetch id coa setelah upsert untuk mapping reference (berdasarkan kode, global cabang_id null)
            $coaIdByKode = DB::table('coa')->whereNull('cabang_id')
                ->whereIn('kode_akun', ['111.01.00','114.01.00','210.02.00','410.01.00','410.97.00','410.98.00','410.99.00'])
                ->pluck('id', 'kode_akun');

            $mappingList = [
                ['tipe' => 'simpanan_setoran',   'pos' => 'debet',  'akun' => '111.01.00', 'ket' => 'Kas simpanan masuk (Kas Kantor)'],
                ['tipe' => 'simpanan_setoran',   'pos' => 'kredit', 'akun' => '210.02.00', 'ket' => 'Kewajiban simpanan sukarela bertambah'],
                ['tipe' => 'simpanan_penarikan', 'pos' => 'debet',  'akun' => '210.02.00', 'ket' => 'Turun kewajiban simpanan sukarela'],
                ['tipe' => 'simpanan_penarikan', 'pos' => 'kredit', 'akun' => '111.01.00', 'ket' => 'Kas keluar bayar penarikan simpanan'],
                ['tipe' => 'pinjaman_cair',      'pos' => 'debet',  'akun' => '114.01.00', 'ket' => 'Terbentuk piutang pinjaman anggota'],
                ['tipe' => 'pinjaman_cair',      'pos' => 'kredit', 'akun' => '111.01.00', 'ket' => 'Kas keluar pencairan pinjaman'],
                ['tipe' => 'angsuran_pokok',     'pos' => 'debet',  'akun' => '111.01.00', 'ket' => 'Kas masuk angsuran pokok'],
                ['tipe' => 'angsuran_pokok',     'pos' => 'kredit', 'akun' => '114.01.00', 'ket' => 'Turun piutang pinjaman (porsi pokok)'],
                ['tipe' => 'angsuran_bunga',     'pos' => 'debet',  'akun' => '111.01.00', 'ket' => 'Kas masuk angsuran bunga'],
                ['tipe' => 'angsuran_bunga',     'pos' => 'kredit', 'akun' => '410.01.00', 'ket' => 'Pendapatan bunga pinjaman diakui'],
                ['tipe' => 'biaya_administrasi', 'pos' => 'debet',  'akun' => '111.01.00', 'ket' => 'Kas masuk biaya administrasi'],
                ['tipe' => 'biaya_administrasi', 'pos' => 'kredit', 'akun' => '410.98.00', 'ket' => 'Pendapatan administrasi layanan'],
                ['tipe' => 'denda_tunggakan',    'pos' => 'debet',  'akun' => '111.01.00', 'ket' => 'Kas masuk denda keterlambatan'],
                ['tipe' => 'denda_tunggakan',    'pos' => 'kredit', 'akun' => '410.99.00', 'ket' => 'Pendapatan denda & keterlambatan'],
            ];
            foreach ($mappingList as $row) {
                if (!isset($coaIdByKode[$row['akun']])) {
                    continue;
                }
                $existing = DB::table('coa_mapping')
                    ->where('cabang_id', null)
                    ->where('tipe_transaksi', $row['tipe'])
                    ->where('posisi', $row['pos'])
                    ->first();
                $payload = [
                    'cabang_id' => null,
                    'tipe_transaksi' => $row['tipe'],
                    'posisi' => $row['pos'],
                    'coa_id' => $coaIdByKode[$row['akun']],
                    'keterangan' => $row['ket'],
                    'updated_at' => now(),
                ];
                if ($existing) {
                    DB::table('coa_mapping')->where('id', $existing->id)->update($payload);
                } else {
                    DB::table('coa_mapping')->insert(array_merge(['id' => Str::uuid()->toString(), 'created_at' => now()], $payload));
                }
            }
        });
    }
}
