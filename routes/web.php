<?php

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AngsuranController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AktivitasLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\CoaMappingController;
use App\Http\Controllers\CoaSaldoAwalController;
use App\Http\Controllers\JenisPinjamanController;
use App\Http\Controllers\JenisSimpananController;
use App\Http\Controllers\JurnalUmumController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NeracaController;
use App\Http\Controllers\PembayaranAngsuranController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MasterDokumenController;
use App\Http\Controllers\SimulasiPinjamanController;
use App\Http\Controllers\SettingPersyaratanPinjamanController;
use App\Http\Controllers\JurnalHarianController;
use App\Http\Controllers\RekapKasNonKasController;
use App\Http\Controllers\BukuKasHarianController;
use App\Http\Controllers\LabaRugiController;
use App\Http\Controllers\NeracaSaldoController;
use App\Http\Controllers\TutupBukuController;
use App\Http\Controllers\CekNeracaSaldoController;
use App\Http\Controllers\ShuController;
use App\Http\Controllers\ArusKasController;
use App\Http\Controllers\KaryawanController;
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

    Route::middleware(['anggota', 'karyawan'])->prefix('modules')->group(function () {
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

        Route::match(['get', 'post'], 'karyawan', [KaryawanController::class, 'index'])->name('karyawan.index')->middleware('permission:KARYAWAN_INDEX');
        Route::get('karyawan/create', [KaryawanController::class, 'create'])->name('karyawan.create')->middleware('permission:KARYAWAN_CREATE');
        Route::post('karyawan', [KaryawanController::class, 'store'])->name('karyawan.store')->middleware('permission:KARYAWAN_CREATE');
        Route::get('karyawan/{karyawan}', [KaryawanController::class, 'show'])->name('karyawan.show')->middleware('permission:KARYAWAN_VIEW');
        Route::get('karyawan/{karyawan}/edit', [KaryawanController::class, 'edit'])->name('karyawan.edit')->middleware('permission:KARYAWAN_UPDATE');
        Route::match(['put', 'patch'], 'karyawan/{karyawan}', [KaryawanController::class, 'update'])->name('karyawan.update')->middleware('permission:KARYAWAN_UPDATE');
        Route::delete('karyawan/{karyawan}', [KaryawanController::class, 'destroy'])->name('karyawan.destroy')->middleware('permission:KARYAWAN_DELETE');

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

        Route::match(['get', 'post'], 'coa', [CoaController::class, 'index'])->name('coa.index')->middleware('permission:COA_INDEX');
        Route::get('coa/create', [CoaController::class, 'create'])->name('coa.create')->middleware('permission:COA_CREATE');
        Route::post('coa', [CoaController::class, 'store'])->name('coa.store')->middleware('permission:COA_CREATE');
        Route::get('coa/{coa}', [CoaController::class, 'show'])->name('coa.show')->middleware('permission:COA_VIEW');
        Route::get('coa/{coa}/edit', [CoaController::class, 'edit'])->name('coa.edit')->middleware('permission:COA_UPDATE');
        Route::match(['put', 'patch'], 'coa/{coa}', [CoaController::class, 'update'])->name('coa.update')->middleware('permission:COA_UPDATE');
        Route::delete('coa/{coa}', [CoaController::class, 'destroy'])->name('coa.destroy')->middleware('permission:COA_DELETE');

        Route::match(['get', 'post'], 'coa-mapping', [CoaMappingController::class, 'index'])->name('coa-mapping.index')->middleware('permission:COA_MAPPING_INDEX');
        Route::match(['put', 'patch', 'post'], 'coa-mapping/update', [CoaMappingController::class, 'update'])->name('coa-mapping.update')->middleware('permission:COA_MAPPING_UPDATE');

        Route::match(['get', 'post'], 'coa-saldo-awal', [CoaSaldoAwalController::class, 'index'])->name('coa-saldo-awal.index')->middleware('permission:COA_SALDO_AWAL_INDEX');
        Route::match(['put', 'patch', 'post'], 'coa-saldo-awal/update', [CoaSaldoAwalController::class, 'update'])->name('coa-saldo-awal.update')->middleware('permission:COA_SALDO_AWAL_UPDATE');

        Route::match(['get', 'post'], 'jurnal-umum', [JurnalUmumController::class, 'index'])->name('jurnal-umum.index')->middleware('permission:JURNAL_INDEX');
        Route::get('jurnal-umum/create', [JurnalUmumController::class, 'create'])->name('jurnal-umum.create')->middleware('permission:JURNAL_CREATE');
        Route::post('jurnal-umum', [JurnalUmumController::class, 'store'])->name('jurnal-umum.store')->middleware('permission:JURNAL_CREATE');
        Route::get('jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'show'])->name('jurnal-umum.show')->middleware('permission:JURNAL_VIEW');
        Route::get('jurnal-umum/{jurnalUmum}/edit', [JurnalUmumController::class, 'edit'])->name('jurnal-umum.edit')->middleware('permission:JURNAL_UPDATE');
        Route::match(['put', 'patch'], 'jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'update'])->name('jurnal-umum.update')->middleware('permission:JURNAL_UPDATE');
        Route::post('jurnal-umum/{jurnalUmum}/posting', [JurnalUmumController::class, 'posting'])->name('jurnal-umum.posting')->middleware('permission:JURNAL_POSTING');
        Route::delete('jurnal-umum/{jurnalUmum}', [JurnalUmumController::class, 'destroy'])->name('jurnal-umum.destroy')->middleware('permission:JURNAL_DELETE');

        Route::match(['get', 'post'], 'buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index')->middleware('permission:BUKU_BESAR_INDEX');

        Route::match(['get', 'post'], 'neraca', [NeracaController::class, 'index'])->name('neraca.index')->middleware('permission:NERACA_VIEW');

        Route::match(['get', 'post'], 'master-dokumen', [MasterDokumenController::class, 'index'])->name('master-dokumen.index')->middleware('permission:MASTER_DOKUMEN_INDEX');
        Route::get('master-dokumen/create', [MasterDokumenController::class, 'create'])->name('master-dokumen.create')->middleware('permission:MASTER_DOKUMEN_CREATE');
        Route::post('master-dokumen', [MasterDokumenController::class, 'store'])->name('master-dokumen.store')->middleware('permission:MASTER_DOKUMEN_CREATE');
        Route::get('master-dokumen/{masterDokumen}', [MasterDokumenController::class, 'show'])->name('master-dokumen.show')->middleware('permission:MASTER_DOKUMEN_VIEW');
        Route::get('master-dokumen/{masterDokumen}/edit', [MasterDokumenController::class, 'edit'])->name('master-dokumen.edit')->middleware('permission:MASTER_DOKUMEN_UPDATE');
        Route::match(['put', 'patch'], 'master-dokumen/{masterDokumen}', [MasterDokumenController::class, 'update'])->name('master-dokumen.update')->middleware('permission:MASTER_DOKUMEN_UPDATE');
        Route::delete('master-dokumen/{masterDokumen}', [MasterDokumenController::class, 'destroy'])->name('master-dokumen.destroy')->middleware('permission:MASTER_DOKUMEN_DELETE');

        Route::match(['get', 'post'], 'setting-persyaratan-pinjaman', [SettingPersyaratanPinjamanController::class, 'index'])->name('setting-persyaratan-pinjaman.index')->middleware('permission:SETTING_PERSYARATAN_INDEX');
        Route::match(['put', 'patch', 'post'], 'setting-persyaratan-pinjaman/update/{jenisPinjaman}', [SettingPersyaratanPinjamanController::class, 'update'])->name('setting-persyaratan-pinjaman.update')->middleware('permission:SETTING_PERSYARATAN_UPDATE');

        Route::match(['get', 'post'], 'simulasi-pinjaman', [SimulasiPinjamanController::class, 'index'])->name('simulasi-pinjaman.index')->middleware('permission:SIMULASI_PINJAMAN_VIEW');

        Route::match(['get', 'post'], 'jurnal-harian', [JurnalHarianController::class, 'index'])->name('jurnal-harian.index')->middleware('permission:JURNAL_HARIAN_VIEW');

        Route::match(['get', 'post'], 'rekap-kas-non-kas', [RekapKasNonKasController::class, 'index'])->name('rekap-kas-non-kas.index')->middleware('permission:REKAP_KAS_VIEW');

        Route::match(['get', 'post'], 'buku-kas-harian', [BukuKasHarianController::class, 'index'])->name('buku-kas-harian.index')->middleware('permission:BUKU_KAS_VIEW');

        Route::match(['get', 'post'], 'laba-rugi/periode', [LabaRugiController::class, 'periode'])->name('laba-rugi.periode')->middleware('permission:LABA_RUGI_PERIODE_VIEW');
        Route::match(['get', 'post'], 'laba-rugi/kumulatif', [LabaRugiController::class, 'kumulatif'])->name('laba-rugi.kumulatif')->middleware('permission:LABA_RUGI_KUMULATIF_VIEW');

        Route::match(['get', 'post'], 'neraca-saldo', [NeracaSaldoController::class, 'index'])->name('neraca-saldo.index')->middleware('permission:NERACA_SALDO_VIEW');

        Route::match(['get', 'post'], 'tutup-buku', [TutupBukuController::class, 'index'])->name('tutup-buku.index')->middleware('permission:TUTUP_BUKU_EXEC');
        Route::post('tutup-buku/proses', [TutupBukuController::class, 'proses'])->name('tutup-buku.proses')->middleware('permission:TUTUP_BUKU_EXEC');

        Route::match(['get', 'post'], 'cek-neraca-saldo', [CekNeracaSaldoController::class, 'index'])->name('cek-neraca-saldo.index')->middleware('permission:CEK_NERACA_SALDO_VIEW');

        Route::match(['get', 'post'], 'laporan-akunting/neraca', [NeracaController::class, 'index'])->name('laporan-akunting.neraca')->middleware('permission:NERACA_VIEW');
        Route::match(['get', 'post'], 'laporan-akunting/shu', [ShuController::class, 'index'])->name('laporan-akunting.shu')->middleware('permission:LAP_SHU_VIEW');
        Route::match(['get', 'post'], 'laporan-akunting/arus-kas', [ArusKasController::class, 'index'])->name('laporan-akunting.arus-kas')->middleware('permission:LAP_ARUS_KAS_VIEW');

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

        // ==================== EXPORT PDF & EXCEL - MODUL AKUNTANSI & LAPORAN AKUNTING ====================
        Route::get('coa/export-pdf', [CoaController::class, 'exportPdf'])->name('coa.exportPdf')->middleware('permission:COA_INDEX');
        Route::get('coa/export-excel', [CoaController::class, 'exportExcel'])->name('coa.exportExcel')->middleware('permission:COA_INDEX');

        Route::get('coa-mapping/export-pdf', [CoaMappingController::class, 'exportPdf'])->name('coa-mapping.exportPdf')->middleware('permission:COA_MAPPING_INDEX');
        Route::get('coa-mapping/export-excel', [CoaMappingController::class, 'exportExcel'])->name('coa-mapping.exportExcel')->middleware('permission:COA_MAPPING_INDEX');

        Route::get('coa-saldo-awal/export-pdf', [CoaSaldoAwalController::class, 'exportPdf'])->name('coa-saldo-awal.exportPdf')->middleware('permission:COA_SALDO_AWAL_INDEX');
        Route::get('coa-saldo-awal/export-excel', [CoaSaldoAwalController::class, 'exportExcel'])->name('coa-saldo-awal.exportExcel')->middleware('permission:COA_SALDO_AWAL_INDEX');

        Route::get('jurnal-umum/export-pdf', [JurnalUmumController::class, 'exportPdf'])->name('jurnal-umum.exportPdf')->middleware('permission:JURNAL_INDEX');
        Route::get('jurnal-umum/export-excel', [JurnalUmumController::class, 'exportExcel'])->name('jurnal-umum.exportExcel')->middleware('permission:JURNAL_INDEX');

        Route::get('buku-besar/export-pdf', [BukuBesarController::class, 'exportPdf'])->name('buku-besar.exportPdf')->middleware('permission:BUKU_BESAR_INDEX');
        Route::get('buku-besar/export-excel', [BukuBesarController::class, 'exportExcel'])->name('buku-besar.exportExcel')->middleware('permission:BUKU_BESAR_INDEX');

        Route::get('neraca/export-pdf', [NeracaController::class, 'exportPdf'])->name('neraca.exportPdf')->middleware('permission:NERACA_VIEW');
        Route::get('neraca/export-excel', [NeracaController::class, 'exportExcel'])->name('neraca.exportExcel')->middleware('permission:NERACA_VIEW');

        Route::get('jurnal-harian/export-pdf', [JurnalHarianController::class, 'exportPdf'])->name('jurnal-harian.exportPdf')->middleware('permission:JURNAL_HARIAN_VIEW');
        Route::get('jurnal-harian/export-excel', [JurnalHarianController::class, 'exportExcel'])->name('jurnal-harian.exportExcel')->middleware('permission:JURNAL_HARIAN_VIEW');

        Route::get('rekap-kas-non-kas/export-pdf', [RekapKasNonKasController::class, 'exportPdf'])->name('rekap-kas-non-kas.exportPdf')->middleware('permission:REKAP_KAS_VIEW');
        Route::get('rekap-kas-non-kas/export-excel', [RekapKasNonKasController::class, 'exportExcel'])->name('rekap-kas-non-kas.exportExcel')->middleware('permission:REKAP_KAS_VIEW');

        Route::get('buku-kas-harian/export-pdf', [BukuKasHarianController::class, 'exportPdf'])->name('buku-kas-harian.exportPdf')->middleware('permission:BUKU_KAS_VIEW');
        Route::get('buku-kas-harian/export-excel', [BukuKasHarianController::class, 'exportExcel'])->name('buku-kas-harian.exportExcel')->middleware('permission:BUKU_KAS_VIEW');

        Route::get('laba-rugi/periode/export-pdf', [LabaRugiController::class, 'exportPdf'])->name('laba-rugi.periode.exportPdf')->middleware('permission:LABA_RUGI_PERIODE_VIEW');
        Route::get('laba-rugi/periode/export-excel', [LabaRugiController::class, 'exportExcel'])->name('laba-rugi.periode.exportExcel')->middleware('permission:LABA_RUGI_PERIODE_VIEW');

        Route::get('neraca-saldo/export-pdf', [NeracaSaldoController::class, 'exportPdf'])->name('neraca-saldo.exportPdf')->middleware('permission:NERACA_SALDO_VIEW');
        Route::get('neraca-saldo/export-excel', [NeracaSaldoController::class, 'exportExcel'])->name('neraca-saldo.exportExcel')->middleware('permission:NERACA_SALDO_VIEW');

        Route::get('cek-neraca-saldo/export-pdf', [CekNeracaSaldoController::class, 'exportPdf'])->name('cek-neraca-saldo.exportPdf')->middleware('permission:CEK_NERACA_SALDO_VIEW');
        Route::get('cek-neraca-saldo/export-excel', [CekNeracaSaldoController::class, 'exportExcel'])->name('cek-neraca-saldo.exportExcel')->middleware('permission:CEK_NERACA_SALDO_VIEW');

        Route::get('laporan-akunting/shu/export-pdf', [ShuController::class, 'exportPdf'])->name('laporan-akunting.shu.exportPdf')->middleware('permission:LAP_SHU_VIEW');
        Route::get('laporan-akunting/shu/export-excel', [ShuController::class, 'exportExcel'])->name('laporan-akunting.shu.exportExcel')->middleware('permission:LAP_SHU_VIEW');

        Route::get('laporan-akunting/arus-kas/export-pdf', [ArusKasController::class, 'exportPdf'])->name('laporan-akunting.arus-kas.exportPdf')->middleware('permission:LAP_ARUS_KAS_VIEW');
        Route::get('laporan-akunting/arus-kas/export-excel', [ArusKasController::class, 'exportExcel'])->name('laporan-akunting.arus-kas.exportExcel')->middleware('permission:LAP_ARUS_KAS_VIEW');

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
