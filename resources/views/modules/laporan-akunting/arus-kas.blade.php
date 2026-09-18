@extends('modules.layouts.master')
@push('title', 'Laporan Arus Kas')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Laporan Arus Kas</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Arus Kas</li>
        </ol>
    </nav>
</div>

@php
$op = $data['aktivitas_operasional'] ?? [];
$inv = $data['aktivitas_investasi'] ?? [];
$pen = $data['aktivitas_pendanaan'] ?? [];
$ring = $data['ringkasan'] ?? [];
@endphp

<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Kas dari Aktivitas Operasional</div>
                <div class="h5 fw-bold {{ ($op['net'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                    {{ ($op['net'] ?? 0) >= 0 ? '' : '(' }}Rp {{ number_format(abs($op['net'] ?? 0), 0, ',', '.') }}{{ ($op['net'] ?? 0) >= 0 ? '' : ')' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Kas dari Aktivitas Investasi</div>
                <div class="h5 fw-bold {{ ($inv['net'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                    {{ ($inv['net'] ?? 0) >= 0 ? '' : '(' }}Rp {{ number_format(abs($inv['net'] ?? 0), 0, ',', '.') }}{{ ($inv['net'] ?? 0) >= 0 ? '' : ')' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Kas dari Aktivitas Pendanaan</div>
                <div class="h5 fw-bold {{ ($pen['net'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                    {{ ($pen['net'] ?? 0) >= 0 ? '' : '(' }}Rp {{ number_format(abs($pen['net'] ?? 0), 0, ',', '.') }}{{ ($pen['net'] ?? 0) >= 0 ? '' : ')' }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100 border-left-4 {{ ($ring['kenaikan_bersih_kas'] ?? 0) >= 0 ? '' : 'border-danger' }}" style="border-left: 4px solid #f97316;">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Kenaikan/Penurunan Kas Bersih</div>
                <div class="h4 fw-bold {{ ($ring['kenaikan_bersih_kas'] ?? 0) >= 0 ? 'text-orange' : 'text-danger' }} mb-0">
                    {{ ($ring['kenaikan_bersih_kas'] ?? 0) >= 0 ? '' : '(' }}Rp {{ number_format(abs($ring['kenaikan_bersih_kas'] ?? 0), 0, ',', '.') }}{{ ($ring['kenaikan_bersih_kas'] ?? 0) >= 0 ? '' : ')' }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light text-center">
                <h5 class="fw-bold text-gray-800 mb-0">LAPORAN ARUS KAS</h5>
                <div class="small text-muted mt-1">
                    Periode: {{ \Carbon\Carbon::parse($dari)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d F Y') }}
                    · {{ $cabangId ? ('Cabang: ' . (\App\Models\Cabang::find($cabangId)?->nama_cabang ?? '-')) : 'Semua Cabang (Konsolidasi)' }}
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="65%" class="text-center">URAIAN AKTIVITAS</th>
                                <th width="17%" class="text-center">PENERIMAAN (Kas Masuk)</th>
                                <th width="18%" class="text-center">PENGELUARAN (Kas Keluar)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-info bg-opacity-10">
                                <td colspan="3" class="fw-bold text-info px-4 py-2">
                                    I. AKTIVITAS OPERASIONAL
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-2">
                                    Kas masuk dari simpanan, angsuran pinjaman, pendapatan operasional
                                </td>
                                <td class="text-end py-2 text-success">Rp {{ number_format($op['kas_masuk'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-2">-</td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-2 border-top-0">
                                    Kas keluar untuk penarikan simpanan, pencairan pinjaman, beban operasional
                                </td>
                                <td class="text-end py-2 border-top-0">-</td>
                                <td class="text-end py-2 border-top-0 text-danger">Rp {{ number_format($op['kas_keluar'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="fw-semibold bg-info bg-opacity-5">
                                <td class="text-end pe-4 py-3">
                                    <span class="fw-bold">KAS BERSIH DARI AKTIVITAS OPERASIONAL</span>
                                </td>
                                <td class="text-end py-3" colspan="2">
                                    <span class="fw-bold {{ ($op['net'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($op['net'] ?? 0) >= 0 ? '' : '(' }}Rp {{ number_format(abs($op['net'] ?? 0), 0, ',', '.') }}{{ ($op['net'] ?? 0) >= 0 ? '' : ')' }}
                                    </span>
                                </td>
                            </tr>

                            <tr><td colspan="3" class="py-1 bg-transparent border-0"></td></tr>

                            <tr class="bg-warning bg-opacity-10">
                                <td colspan="3" class="fw-bold text-warning-emphasis px-4 py-2">
                                    II. AKTIVITAS INVESTASI (Aktiva Tetap)
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-2">
                                    Penerimaan dari penjualan aktiva tetap & investasi jangka panjang
                                </td>
                                <td class="text-end py-2 text-success">Rp {{ number_format($inv['kas_masuk'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-2">-</td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-2 border-top-0">
                                    Pengeluaran untuk pembelian tanah, gedung, kendaraan, inventaris kantor
                                </td>
                                <td class="text-end py-2 border-top-0">-</td>
                                <td class="text-end py-2 border-top-0 text-danger">Rp {{ number_format($inv['kas_keluar'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="fw-semibold bg-warning bg-opacity-5">
                                <td class="text-end pe-4 py-3">
                                    <span class="fw-bold">KAS BERSIH DARI AKTIVITAS INVESTASI</span>
                                </td>
                                <td class="text-end py-3" colspan="2">
                                    <span class="fw-bold {{ ($inv['net'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($inv['net'] ?? 0) >= 0 ? '' : '(' }}Rp {{ number_format(abs($inv['net'] ?? 0), 0, ',', '.') }}{{ ($inv['net'] ?? 0) >= 0 ? '' : ')' }}
                                    </span>
                                </td>
                            </tr>

                            <tr><td colspan="3" class="py-1 bg-transparent border-0"></td></tr>

                            <tr class="bg-purple bg-opacity-10" style="background-color: #f3e8ff;">
                                <td colspan="3" class="fw-bold px-4 py-2" style="color: #7e22ce;">
                                    III. AKTIVITAS PENDANAAN (Modal & Hutang Jangka Panjang)
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-2">
                                    Penerimaan modal anggota & pinjaman jangka panjang dari bank/lembaga
                                </td>
                                <td class="text-end py-2 text-success">Rp {{ number_format($pen['kas_masuk'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-2">-</td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-2 border-top-0">
                                    Pengembalian pinjaman jangka panjang & pengembalian modal
                                </td>
                                <td class="text-end py-2 border-top-0">-</td>
                                <td class="text-end py-2 border-top-0 text-danger">Rp {{ number_format($pen['kas_keluar'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="fw-semibold" style="background-color: #faf5ff;">
                                <td class="text-end pe-4 py-3">
                                    <span class="fw-bold" style="color: #7e22ce;">KAS BERSIH DARI AKTIVITAS PENDANAAN</span>
                                </td>
                                <td class="text-end py-3" colspan="2">
                                    <span class="fw-bold {{ ($pen['net'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($pen['net'] ?? 0) >= 0 ? '' : '(' }}Rp {{ number_format(abs($pen['net'] ?? 0), 0, ',', '.') }}{{ ($pen['net'] ?? 0) >= 0 ? '' : ')' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-orange text-white">
                                <td class="px-4 py-3">
                                    <h6 class="mb-0 fw-bold">KENAIKAN (PENURUNAN) KAS BERSIH PERIODE INI</h6>
                                </td>
                                <td class="text-end py-3" colspan="2">
                                    <h5 class="mb-0 fw-bold">
                                        {{ ($ring['kenaikan_bersih_kas'] ?? 0) >= 0 ? '' : '(' }}Rp {{ number_format(abs($ring['kenaikan_bersih_kas'] ?? 0), 0, ',', '.') }}{{ ($ring['kenaikan_bersih_kas'] ?? 0) >= 0 ? '' : ')' }}
                                    </h5>
                                </td>
                            </tr>
                            <tr class="bg-light fw-semibold">
                                <td class="px-4 py-3 text-end pe-4">
                                    SALDO KAS AWAL PERIODE ({{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }})
                                </td>
                                <td class="text-end py-3" colspan="2">Rp {{ number_format($ring['saldo_awal_kas'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-orange bg-opacity-10 fw-bold">
                                <td class="px-4 py-3 text-end pe-4 text-orange">
                                    <h6 class="mb-0 fw-bold">SALDO KAS AKHIR PERIODE ({{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }})</h6>
                                </td>
                                <td class="text-end py-3" colspan="2">
                                    <h4 class="mb-0 fw-bold text-orange">Rp {{ number_format($ring['saldo_akhir_kas'] ?? 0, 0, ',', '.') }}</h4>
                                </td>
                            </tr>
                            <tr class="bg-gray-50 small">
                                <td class="px-4 py-2 text-end pe-4 text-muted" colspan="2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Laba Bersih Periode:
                                </td>
                                <td class="text-end py-2 fw-semibold">
                                    <span class="{{ ($ring['laba_bersih_periode'] ?? 0) >= 0 ? 'text-orange' : 'text-danger' }}">
                                        Rp {{ number_format($ring['laba_bersih_periode'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
