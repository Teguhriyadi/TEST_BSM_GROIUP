@extends('modules.layouts.master')
@push('title', 'Rekapitulasi Kas dan Non Kas Harian')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Rekapitulasi Kas dan Non Kas Harian</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rekap Kas dan Non Kas</li>
        </ol>
    </nav>
</div>

@php
$rows = $rekap['rows'] ?? [];
$total = $rekap['total'] ?? ['kas_masuk' => 0, 'kas_keluar' => 0, 'non_kas_debet' => 0, 'non_kas_kredit' => 0, 'jml_transaksi' => 0];
@endphp

<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Kas Masuk</div>
                <div class="h5 fw-bold text-success mb-0">Rp {{ number_format($total['kas_masuk'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Kas Keluar</div>
                <div class="h5 fw-bold text-danger mb-0">Rp {{ number_format($total['kas_keluar'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Net Kas (Masuk - Keluar)</div>
                <div class="h5 fw-bold {{ $total['kas_masuk'] - $total['kas_keluar'] >= 0 ? 'text-orange' : 'text-danger' }} mb-0">
                    Rp {{ number_format($total['kas_masuk'] - $total['kas_keluar'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Jumlah Voucher</div>
                <div class="h5 fw-bold text-primary mb-0">{{ $total['jml_transaksi'] }} transaksi</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    Rekap Harian
                    <span class="small text-muted fw-normal ms-2">
                        Periode: {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}
                    </span>
                </h6>
            </div>
            @if(count($rows) > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white small">
                            <tr>
                                <th width="14%" class="text-center">Tanggal</th>
                                <th width="14%" class="text-end">Kas Masuk</th>
                                <th width="14%" class="text-end">Kas Keluar</th>
                                <th width="14%" class="text-end">Net Kas</th>
                                <th width="14%" class="text-end">Non Kas Debet</th>
                                <th width="14%" class="text-end">Non Kas Kredit</th>
                                <th width="8%" class="text-center">Jml Voucher</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $r)
                            @php $netKas = (float)$r['kas_masuk'] - (float)$r['kas_keluar']; @endphp
                            <tr>
                                <td class="text-center fw-semibold">
                                    {{ \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td class="text-end text-success">Rp {{ number_format($r['kas_masuk'], 0, ',', '.') }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($r['kas_keluar'], 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold {{ $netKas >= 0 ? 'text-orange' : 'text-danger' }}">
                                    Rp {{ number_format($netKas, 0, ',', '.') }}
                                </td>
                                <td class="text-end">Rp {{ number_format($r['non_kas_debet'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($r['non_kas_kredit'], 0, ',', '.') }}</td>
                                <td class="text-center">{{ $r['jml_transaksi'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php
                                $gtNet = $total['kas_masuk'] - $total['kas_keluar'];
                            @endphp
                            <tr class="bg-orange text-white fw-bold">
                                <td class="text-center py-3">GRAND TOTAL</td>
                                <td class="text-end py-3">Rp {{ number_format($total['kas_masuk'], 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($total['kas_keluar'], 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($gtNet, 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($total['non_kas_debet'], 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($total['non_kas_kredit'], 0, ',', '.') }}</td>
                                <td class="text-center py-3">{{ $total['jml_transaksi'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-cash-stack display-4 d-block mb-3 opacity-25"></i>
                Tidak ada data rekap pada periode yang dipilih.
            </div>
            @endif
        </div>
    </div>
</div>
@endpush
