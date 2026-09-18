@extends('modules.layouts.master')
@push('title', 'Cek Neraca dan Saldo')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Cek Neraca dan Saldo</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cek Neraca Saldo</li>
        </ol>
    </nav>
</div>

<div class="row mb-3">
    <div class="col-lg-12">
        <form method="GET" action="{{ route('cek-neraca-saldo.index') }}" class="card shadow bg-white p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="tanggal_cutoff" class="form-label">Tanggal Cutoff (Per Posisi) <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_cutoff" name="tanggal_cutoff" value="{{ $sampai }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-check2-square me-1"></i> Jalankan Verifikasi
                    </button>
                    <a href="{{ route('cek-neraca-saldo.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

@php
$totals = $neracaSaldo['totals'] ?? [];
$balanceNS = $neracaSaldo['balance'] ?? [];
$ringkasanNeraca = $neraca['ringkasan'] ?? [];

$saDiff = abs(($totals['sa_debet'] ?? 0) - ($totals['sa_kredit'] ?? 0));
$mutDiff = abs(($totals['mut_debet'] ?? 0) - ($totals['mut_kredit'] ?? 0));
$skDiff = abs(($totals['sk_debet'] ?? 0) - ($totals['sk_kredit'] ?? 0));
$padananDiff = abs(($ringkasanNeraca['total_aset'] ?? 0) - ($ringkasanNeraca['total_kewajiban_ekuitas'] ?? 0));
@endphp

<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-header py-2 bg-light small fw-semibold text-gray-800 border-0">
                1. Saldo Awal (Debet vs Kredit)
            </div>
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Selisih</div>
                <div class="h5 fw-bold mb-2 {{ $saDiff < 0.01 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($saDiff, 0, ',', '.') }}
                </div>
                @if($saDiff < 0.01)
                    <span class="badge bg-success text-white px-3 py-1"><i class="bi bi-check-circle me-1"></i>BALANCE</span>
                @else
                    <span class="badge bg-danger text-white px-3 py-1"><i class="bi bi-x-circle me-1"></i>TIDAK BALANCE</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-header py-2 bg-light small fw-semibold text-gray-800 border-0">
                2. Mutasi Periode (Debet vs Kredit)
            </div>
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Selisih</div>
                <div class="h5 fw-bold mb-2 {{ $mutDiff < 0.01 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($mutDiff, 0, ',', '.') }}
                </div>
                @if($mutDiff < 0.01)
                    <span class="badge bg-success text-white px-3 py-1"><i class="bi bi-check-circle me-1"></i>BALANCE</span>
                @else
                    <span class="badge bg-danger text-white px-3 py-1"><i class="bi bi-x-circle me-1"></i>TIDAK BALANCE</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-header py-2 bg-light small fw-semibold text-gray-800 border-0">
                3. Saldo Akhir (Debet vs Kredit)
            </div>
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Selisih</div>
                <div class="h5 fw-bold mb-2 {{ $skDiff < 0.01 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($skDiff, 0, ',', '.') }}
                </div>
                @if($skDiff < 0.01)
                    <span class="badge bg-success text-white px-3 py-1"><i class="bi bi-check-circle me-1"></i>BALANCE</span>
                @else
                    <span class="badge bg-danger text-white px-3 py-1"><i class="bi bi-x-circle me-1"></i>TIDAK BALANCE</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-header py-2 bg-light small fw-semibold text-gray-800 border-0">
                4. Neraca (Aset = Kewajiban + Ekuitas)
            </div>
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Selisih Padanan</div>
                <div class="h5 fw-bold mb-2 {{ $padananDiff < 0.01 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($padananDiff, 0, ',', '.') }}
                </div>
                @if($padananDiff < 0.01)
                    <span class="badge bg-success text-white px-3 py-1"><i class="bi bi-check-circle me-1"></i>BALANCE</span>
                @else
                    <span class="badge bg-danger text-white px-3 py-1"><i class="bi bi-x-circle me-1"></i>TIDAK BALANCE</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    Ringkasan Global
                    <span class="small text-muted fw-normal ms-2">Periode per {{ \Carbon\Carbon::parse($sampai)->format('d F Y') }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <tbody>
                            <tr>
                                <td width="20%" class="bg-light fw-semibold">Total Aset (Aktiva)</td>
                                <td width="30%" class="text-end fw-bold text-gray-800">Rp {{ number_format($ringkasanNeraca['total_aset'] ?? 0, 0, ',', '.') }}</td>
                                <td width="20%" class="bg-light fw-semibold">Total Kewajiban</td>
                                <td width="30%" class="text-end fw-semibold">Rp {{ number_format($ringkasanNeraca['total_kewajiban'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light fw-semibold">Total Ekuitas + Laba Rugi</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($ringkasanNeraca['total_ekuitas'] ?? 0, 0, ',', '.') }}</td>
                                <td class="bg-light fw-semibold">Total Kewajiban + Ekuitas</td>
                                <td class="text-end fw-bold text-gray-800">Rp {{ number_format($ringkasanNeraca['total_kewajiban_ekuitas'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light fw-semibold" colspan="2">
                                    Laba Rugi Berjalan s/d Cutoff
                                </td>
                                <td class="text-end" colspan="2">
                                    <span class="fw-bold {{ ($ringkasanNeraca['laba_rugi_berjalan'] ?? 0) >= 0 ? 'text-orange' : 'text-danger' }}">
                                        Rp {{ number_format($ringkasanNeraca['laba_rugi_berjalan'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="m-0 font-weight-bold {{ count($akunTidakBalance) > 0 ? 'text-danger' : 'text-success' }}">
                    @if(count($akunTidakBalance) > 0)
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Ditemukan {{ count($akunTidakBalance) }} akun yang tidak balance:
                    @else
                        <i class="bi bi-shield-check me-2"></i>
                        SEMUA AKUN BALANCE - Tidak ditemukan selisih yang signifikan
                    @endif
                </h6>
                <div class="small text-muted">
                    Cutoff: {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}
                </div>
            </div>
            @if(count($akunTidakBalance) > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th width="10%" class="text-center">Kode Akun</th>
                                <th width="22%">Nama Akun</th>
                                <th width="13%" class="text-end">Saldo Awal Neto</th>
                                <th width="13%" class="text-end">Mutasi Neto</th>
                                <th width="13%" class="text-end">Ekspektasi Akhir</th>
                                <th width="13%" class="text-end">Aktual Akhir</th>
                                <th width="16%" class="text-end">Selisih (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($akunTidakBalance as $ab)
                            <tr>
                                <td class="text-center fw-semibold">{{ $ab['kode_akun'] }}</td>
                                <td>{{ $ab['nama_akun'] }}</td>
                                <td class="text-end">Rp {{ number_format($ab['saldo_awal_neto'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($ab['mutasi_neto'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($ab['ekspektasi_akhir'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($ab['saldo_akhir_neto'], 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-danger">Rp {{ number_format($ab['selisih'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-orange text-white fw-bold">
                                <td colspan="6" class="text-end py-3 pe-4">Total Selisih Keseluruhan</td>
                                <td class="text-end py-3">
                                    Rp {{ number_format(collect($akunTidakBalance)->sum('selisih'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body py-5 text-center text-muted">
                <i class="bi bi-check2-all display-4 d-block mb-3 text-success opacity-50"></i>
                Verifikasi Selesai - Semua Saldo Awal, Mutasi, Saldo Akhir, dan Padanan Neraca dalam kondisi BALANCE.
            </div>
            @endif
        </div>
    </div>
</div>
@endpush
