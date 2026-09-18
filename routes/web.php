<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AngsuranController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AktivitasLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\JenisPinjamanController;
use App\Http\Controllers\JenisSimpananController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PembayaranAngsuranController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/pages/login');
});

Route::middleware(['web', 'guest'])->group(function () {
    Route::prefix('pages')->group(function () {
        Route::get('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/login', [AuthController::class, 'postLogin'])->name('postLogin');

        Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->name('password.email');

        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');

        Route::get('/daftar-anggota', [AuthController::class, 'showRegisterAnggota'])->name('anggota.register');
        Route::post('/daftar-anggota', [AuthController::class, 'registerAnggota'])->name('anggota.register.store');
    });
});

Route::middleware(['web', 'autentikasi'])->group(function () {
    Route::post('/pages/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/modules/update-password-sendiri', [AuthController::class, 'updatePasswordSelf'])->name('password.updateSelf');

    Route::middleware(['anggota'])->prefix('modules')->group(function () {
        Route::match(['get', 'post'], '/dashboard', [AppController::class, 'dashboard'])
            ->name('dashboard')
            ->middleware('permission:DASHBOARD_VIEW');

        Route::match(['get', 'post'], 'cabang', [CabangController::class, 'index'])->name('cabang.index')->middleware('permission:CABANG_INDEX');
        Route::get('cabang/create', [CabangController::class, 'create'])->name('cabang.create')->middleware('permission:CABANG_CREATE');
        Route::post('cabang', [CabangController::class, 'store'])->name('cabang.store')->middleware('permission:CABANG_CREATE');
        Route::get('cabang/{cabang}', [CabangController::class, 'show'])->name('cabang.show')->middleware('permission:CABANG_VIEW');
        Route::get('cabang/{cabang}/edit', [CabangController::class, 'edit'])->name('cabang.edit')->middleware('permission:CABANG_UPDATE');
        Route::match(['put', 'patch'], 'cabang/{cabang}', [CabangController::class, 'update'])->name('cabang.update')->middleware('permission:CABANG_UPDATE');
        Route::delete('cabang/{cabang}', [CabangController::class, 'destroy'])->name('cabang.destroy')->middleware('permission:CABANG_DELETE');

        Route::match(['get', 'post'], 'anggota', [AnggotaController::class, 'index'])->name('anggota.index')->middleware('permission:ANGGOTA_INDEX');
        Route::get('anggota/create', [AnggotaController::class, 'create'])->name('anggota.create')->middleware('permission:ANGGOTA_CREATE');
        Route::post('anggota', [AnggotaController::class, 'store'])->name('anggota.store')->middleware('permission:ANGGOTA_CREATE');
        Route::get('anggota/{anggota}', [AnggotaController::class, 'show'])->name('anggota.show')->middleware('permission:ANGGOTA_VIEW');
        Route::get('anggota/{anggota}/edit', [AnggotaController::class, 'edit'])->name('anggota.edit')->middleware('permission:ANGGOTA_UPDATE');
        Route::match(['put', 'patch'], 'anggota/{anggota}', [AnggotaController::class, 'update'])->name('anggota.update')->middleware('permission:ANGGOTA_UPDATE');
        Route::delete('anggota/{anggota}', [AnggotaController::class, 'destroy'])->name('anggota.destroy')->middleware('permission:ANGGOTA_DELETE');

        Route::get('anggota-pendaftaran/menunggu', [AnggotaController::class, 'pendaftaranMenunggu'])
            ->name('anggota.pendaftaran.menunggu')
            ->middleware('permission:ANGGOTA_PENDAFTARAN_VERIFIKASI');
        Route::get('anggota-pendaftaran/verifikasi/{anggota}', [AnggotaController::class, 'formVerifikasiPendaftaran'])
            ->name('anggota.pendaftaran.formverifikasi')
            ->middleware('permission:ANGGOTA_PENDAFTARAN_VERIFIKASI');
        Route::post('anggota-pendaftaran/verifikasi/{anggota}', [AnggotaController::class, 'verifikasiPendaftaran'])
            ->name('anggota.pendaftaran.verifikasi')
            ->middleware('permission:ANGGOTA_PENDAFTARAN_VERIFIKASI');

        Route::match(['get', 'post'], 'jenis-simpanan', [JenisSimpananController::class, 'index'])->name('jenis-simpanan.index')->middleware('permission:JENIS_SIMPANAN_INDEX');
        Route::get('jenis-simpanan/create', [JenisSimpananController::class, 'create'])->name('jenis-simpanan.create')->middleware('permission:JENIS_SIMPANAN_CREATE');
        Route::post('jenis-simpanan', [JenisSimpananController::class, 'store'])->name('jenis-simpanan.store')->middleware('permission:JENIS_SIMPANAN_CREATE');
        Route::get('jenis-simpanan/{jenisSimpanan}', [JenisSimpananController::class, 'show'])->name('jenis-simpanan.show')->middleware('permission:JENIS_SIMPANAN_VIEW');
        Route::get('jenis-simpanan/{jenisSimpanan}/edit', [JenisSimpananController::class, 'edit'])->name('jenis-simpanan.edit')->middleware('permission:JENIS_SIMPANAN_UPDATE');
        Route::match(['put', 'patch'], 'jenis-simpanan/{jenisSimpanan}', [JenisSimpananController::class, 'update'])->name('jenis-simpanan.update')->middleware('permission:JENIS_SIMPANAN_UPDATE');
        Route::delete('jenis-simpanan/{jenisSimpanan}', [JenisSimpananController::class, 'destroy'])->name('jenis-simpanan.destroy')->middleware('permission:JENIS_SIMPANAN_DELETE');

        Route::match(['get', 'post'], 'simpanan', [SimpananController::class, 'index'])->name('simpanan.index')->middleware('permission:SIMPANAN_INDEX');
        Route::get('simpanan/create', [SimpananController::class, 'create'])->name('simpanan.create')->middleware('permission:SIMPANAN_CREATE');
        Route::post('simpanan', [SimpananController::class, 'store'])->name('simpanan.store')->middleware('permission:SIMPANAN_CREATE');
        Route::get('simpanan/{simpanan}', [SimpananController::class, 'show'])->name('simpanan.show')->middleware('permission:SIMPANAN_VIEW');
        Route::get('simpanan/{simpanan}/edit', [SimpananController::class, 'edit'])->name('simpanan.edit')->middleware('permission:SIMPANAN_UPDATE');
        Route::match(['put', 'patch'], 'simpanan/{simpanan}', [SimpananController::class, 'update'])->name('simpanan.update')->middleware('permission:SIMPANAN_UPDATE');
        Route::delete('simpanan/{simpanan}', [SimpananController::class, 'destroy'])->name('simpanan.destroy')->middleware('permission:SIMPANAN_DELETE');

        Route::match(['get', 'post'], 'jenis-pinjaman', [JenisPinjamanController::class, 'index'])->name('jenis-pinjaman.index')->middleware('permission:JENIS_PINJAMAN_INDEX');
        Route::get('jenis-pinjaman/create', [JenisPinjamanController::class, 'create'])->name('jenis-pinjaman.create')->middleware('permission:JENIS_PINJAMAN_CREATE');
        Route::post('jenis-pinjaman', [JenisPinjamanController::class, 'store'])->name('jenis-pinjaman.store')->middleware('permission:JENIS_PINJAMAN_CREATE');
        Route::get('jenis-pinjaman/{jenisPinjaman}', [JenisPinjamanController::class, 'show'])->name('jenis-pinjaman.show')->middleware('permission:JENIS_PINJAMAN_VIEW');
        Route::get('jenis-pinjaman/{jenisPinjaman}/edit', [JenisPinjamanController::class, 'edit'])->name('jenis-pinjaman.edit')->middleware('permission:JENIS_PINJAMAN_UPDATE');
        Route::match(['put', 'patch'], 'jenis-pinjaman/{jenisPinjaman}', [JenisPinjamanController::class, 'update'])->name('jenis-pinjaman.update')->middleware('permission:JENIS_PINJAMAN_UPDATE');
        Route::delete('jenis-pinjaman/{jenisPinjaman}', [JenisPinjamanController::class, 'destroy'])->name('jenis-pinjaman.destroy')->middleware('permission:JENIS_PINJAMAN_DELETE');

        Route::match(['get', 'post'], 'pinjaman', [PinjamanController::class, 'index'])->name('pinjaman.index')->middleware('permission:PINJAMAN_INDEX');
        Route::get('pinjaman/create', [PinjamanController::class, 'create'])->name('pinjaman.create')->middleware('permission:PINJAMAN_CREATE,ANGGOTA_PINJAMAN_CREATE');
        Route::post('pinjaman', [PinjamanController::class, 'store'])->name('pinjaman.store')->middleware('permission:PINJAMAN_CREATE,ANGGOTA_PINJAMAN_CREATE');
        Route::get('pinjaman/{pinjaman}', [PinjamanController::class, 'show'])->name('pinjaman.show')->middleware('permission:PINJAMAN_VIEW,ANGGOTA_PINJAMAN_VIEW');
        Route::get('pinjaman/{pinjaman}/edit', [PinjamanController::class, 'edit'])->name('pinjaman.edit')->middleware('permission:PINJAMAN_UPDATE');
        Route::match(['put', 'patch'], 'pinjaman/{pinjaman}', [PinjamanController::class, 'update'])->name('pinjaman.update')->middleware('permission:PINJAMAN_UPDATE');
        Route::delete('pinjaman/{pinjaman}', [PinjamanController::class, 'destroy'])->name('pinjaman.destroy')->middleware('permission:PINJAMAN_DELETE');

        Route::post('pinjaman/{pinjaman}/dokumen/{masterDokumen}/upload', [PinjamanController::class, 'uploadDokumen'])
            ->name('pinjaman.dokumen.upload')
            ->middleware('permission:PINJAMAN_DOKUMEN_UPLOAD,ANGGOTA_DOKUMEN_UPLOAD');

        Route::post('pinjaman/{pinjaman}/dokumen/{pinjamanDokumen}/verifikasi', [PinjamanController::class, 'verifikasiDokumen'])
            ->name('pinjaman.dokumen.verifikasi')
            ->middleware('permission:PINJAMAN_DOKUMEN_VERIFIKASI');

        Route::post('pinjaman/{pinjaman}/verifikasi-pengajuan', [PinjamanController::class, 'verifikasiPengajuan'])
            ->name('pinjaman.verifikasi')
            ->middleware('permission:PINJAMAN_APPROVE');

        Route::post('pinjaman/{pinjaman}/approve', [PinjamanController::class, 'approvePengajuan'])
            ->name('pinjaman.approve')
            ->middleware('permission:PINJAMAN_APPROVE');

        Route::post('pinjaman/{pinjaman}/reject', [PinjamanController::class, 'tolakPengajuan'])
            ->name('pinjaman.reject')
            ->middleware('permission:PINJAMAN_APPROVE');

        Route::post('pinjaman/{pinjaman}/cairkan', [PinjamanController::class, 'cairkanPinjaman'])
            ->name('pinjaman.cairkan')
            ->middleware('permission:PINJAMAN_CAIRKAN');

        Route::match(['get', 'post'], 'angsuran', [AngsuranController::class, 'index'])->name('angsuran.index')->middleware('permission:ANGSURAN_INDEX');
        Route::get('angsuran/create', [AngsuranController::class, 'create'])->name('angsuran.create')->middleware('permission:ANGSURAN_CREATE');
        Route::post('angsuran', [AngsuranController::class, 'store'])->name('angsuran.store')->middleware('permission:ANGSURAN_CREATE');
        Route::get('angsuran/{angsuran}', [AngsuranController::class, 'show'])->name('angsuran.show')->middleware('permission:ANGSURAN_VIEW');
        Route::get('angsuran/{angsuran}/edit', [AngsuranController::class, 'edit'])->name('angsuran.edit')->middleware('permission:ANGSURAN_UPDATE');
        Route::match(['put', 'patch'], 'angsuran/{angsuran}', [AngsuranController::class, 'update'])->name('angsuran.update')->middleware('permission:ANGSURAN_UPDATE');
        Route::delete('angsuran/{angsuran}', [AngsuranController::class, 'destroy'])->name('angsuran.destroy')->middleware('permission:ANGSURAN_DELETE');

        Route::match(['get', 'post'], 'pembayaran-angsuran', [PembayaranAngsuranController::class, 'index'])->name('pembayaran-angsuran.index')->middleware('permission:PEMBAYARAN_INDEX');
        Route::get('pembayaran-angsuran/create', [PembayaranAngsuranController::class, 'create'])->name('pembayaran-angsuran.create')->middleware('permission:PEMBAYARAN_CREATE');
        Route::post('pembayaran-angsuran', [PembayaranAngsuranController::class, 'store'])->name('pembayaran-angsuran.store')->middleware('permission:PEMBAYARAN_CREATE');
        Route::get('pembayaran-angsuran/{pembayaranAngsuran}', [PembayaranAngsuranController::class, 'show'])->name('pembayaran-angsuran.show')->middleware('permission:PEMBAYARAN_VIEW');
        Route::get('pembayaran-angsuran/{pembayaranAngsuran}/edit', [PembayaranAngsuranController::class, 'edit'])->name('pembayaran-angsuran.edit')->middleware('permission:PEMBAYARAN_UPDATE');
        Route::match(['put', 'patch'], 'pembayaran-angsuran/{pembayaranAngsuran}', [PembayaranAngsuranController::class, 'update'])->name('pembayaran-angsuran.update')->middleware('permission:PEMBAYARAN_UPDATE');
        Route::delete('pembayaran-angsuran/{pembayaranAngsuran}', [PembayaranAngsuranController::class, 'destroy'])->name('pembayaran-angsuran.destroy')->middleware('permission:PEMBAYARAN_DELETE');

        Route::match(['get','post'],'laporan/anggota', [LaporanController::class, 'anggotaIndex'])
            ->name('laporan.anggota')
            ->middleware('permission:LAPORAN_ANGGOTA_INDEX');
        Route::post('laporan/anggota/export-pdf', [LaporanController::class, 'anggotaExportPdf'])
            ->name('laporan.anggota.exportPdf')
            ->middleware('permission:LAPORAN_ANGGOTA_EXPORT');

        Route::match(['get','post'],'laporan/verifikasi-pendaftaran', [LaporanController::class, 'verifikasiIndex'])
            ->name('laporan.verifikasi')
            ->middleware('permission:LAPORAN_VERIFIKASI_INDEX,ANGGOTA_PENDAFTARAN_VERIFIKASI');
        Route::post('laporan/verifikasi-pendaftaran/export-pdf', [LaporanController::class, 'verifikasiExportPdf'])
            ->name('laporan.verifikasi.exportPdf')
            ->middleware('permission:LAPORAN_VERIFIKASI_EXPORT');

        Route::match(['get','post'],'laporan/simpanan', [LaporanController::class, 'simpananIndex'])
            ->name('laporan.simpanan')
            ->middleware('permission:LAPORAN_SIMPANAN_INDEX');
        Route::post('laporan/simpanan/export-pdf', [LaporanController::class, 'simpananExportPdf'])
            ->name('laporan.simpanan.exportPdf')
            ->middleware('permission:LAPORAN_SIMPANAN_EXPORT');

        Route::match(['get','post'],'laporan/pinjaman', [LaporanController::class, 'pinjamanIndex'])
            ->name('laporan.pinjaman')
            ->middleware('permission:LAPORAN_PINJAMAN_INDEX');
        Route::post('laporan/pinjaman/export-pdf', [LaporanController::class, 'pinjamanExportPdf'])
            ->name('laporan.pinjaman.exportPdf')
            ->middleware('permission:LAPORAN_PINJAMAN_EXPORT');

        Route::match(['get','post'],'laporan/angsuran', [LaporanController::class, 'angsuranIndex'])
            ->name('laporan.angsuran')
            ->middleware('permission:LAPORAN_ANGSURAN_INDEX');
        Route::post('laporan/angsuran/export-pdf', [LaporanController::class, 'angsuranExportPdf'])
            ->name('laporan.angsuran.exportPdf')
            ->middleware('permission:LAPORAN_ANGSURAN_EXPORT');

        Route::match(['get','post'],'laporan/pembayaran-angsuran', [LaporanController::class, 'pembayaranIndex'])
            ->name('laporan.pembayaran')
            ->middleware('permission:LAPORAN_PEMBAYARAN_INDEX');
        Route::post('laporan/pembayaran-angsuran/export-pdf', [LaporanController::class, 'pembayaranExportPdf'])
            ->name('laporan.pembayaran.exportPdf')
            ->middleware('permission:LAPORAN_PEMBAYARAN_EXPORT');

        Route::middleware(['administrator'])->group(function () {
            Route::match(['get','post'],'aktivitas-log', [AktivitasLogController::class, 'index'])
                ->name('aktivitas-log.index')
                ->middleware('permission:AKTIVITAS_LOG_INDEX');

            Route::match(['get', 'post'], 'users', [UserController::class, 'index'])->name('users.index')->middleware('permission:USERS_INDEX');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:USERS_CREATE');
            Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:USERS_CREATE');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('permission:USERS_VIEW');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:USERS_UPDATE');
            Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:USERS_UPDATE');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:USERS_DELETE');

            Route::match(['get', 'post'], 'roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:ROLE_INDEX');
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:ROLE_CREATE');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:ROLE_CREATE');
            Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show')->middleware('permission:ROLE_VIEW,ROLE_ASSIGN_PERMISSION');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:ROLE_UPDATE');
            Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:ROLE_UPDATE');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:ROLE_DELETE');

            Route::match(['get', 'post'], 'permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('permission:PERMISSION_INDEX');
            Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create')->middleware('permission:PERMISSION_CREATE');
            Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:PERMISSION_CREATE');
            Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show')->middleware('permission:PERMISSION_VIEW');
            Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit')->middleware('permission:PERMISSION_UPDATE');
            Route::match(['put', 'patch'], 'permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:PERMISSION_UPDATE');
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:PERMISSION_DELETE');
        });
    });
});
