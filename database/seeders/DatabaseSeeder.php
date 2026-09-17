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

class DatabaseSeeder extends Seeder
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
        });
    }
}
