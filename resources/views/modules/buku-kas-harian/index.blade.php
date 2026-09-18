@extends('modules.layouts.master')
@push('title', 'Buku Kas Harian')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Buku Kas Harian</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Buku Kas Harian</li>
        </ol>
    </nav>
</div>

@php
$perAkun = $data['per_akun'] ?? [];
$globalTotal = $data['total'] ?? ['saldo_awal' => 0, 'masuk' => 0, 'keluar' => 0, 'saldo_akhir' => 0];
@endphp

<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Saldo Awal Total</div>
                <div class="h5 fw-bold text-gray-800 mb-0">Rp {{ number_format($globalTotal['saldo_awal'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Pemasukan (Debet)</div>
                <div class="h5 fw-bold text-success mb-0">Rp {{ number_format($globalTotal['masuk'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Pengeluaran (Kredit)</div>
                <div class="h5 fw-bold text-danger mb-0">Rp {{ number_format($globalTotal['keluar'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Saldo Akhir Total</div>
                <div class="h5 fw-bold text-orange mb-0">Rp {{ number_format($globalTotal['saldo_akhir'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

@if(count($perAkun) > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="accordion" id="bukuKasAccordion">
            @foreach($perAkun as $idx => $acc)
            @php
                $coa = $acc['coa'];
                $detail = $acc['detail_rows'] ?? [];
                $panelId = 'bk' . $idx;
            @endphp
            <div class="card shadow mb-3 border-0">
                <div class="card-header bg-light py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <button class="btn btn-link text-decoration-none p-0 fw-bold text-gray-800" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $panelId }}" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $panelId }}">
                            <i class="bi bi-wallet2 me-2 text-orange"></i>
                            {{ $coa->kode_akun }} - {{ $coa->nama_akun }}
                            <span class="small text-muted fw-normal ms-2">(Saldo Normal: {{ ucfirst($coa->saldo_normal) }})</span>
                        </button>
                    </div>
                    <div class="d-flex gap-3 flex-wrap small">
                        <div>
                            <span class="text-muted">Awal:</span>
                            <span class="fw-semibold ms-1">Rp {{ number_format($acc['saldo_awal'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-muted">Masuk:</span>
                            <span class="fw-semibold text-success ms-1">+ Rp {{ number_format($acc['masuk'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-muted">Keluar:</span>
                            <span class="fw-semibold text-danger ms-1">- Rp {{ number_format($acc['keluar'], 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-muted">Akhir:</span>
                            <span class="fw-bold text-orange ms-1">Rp {{ number_format($acc['saldo_akhir'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div id="collapse{{ $panelId }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}" data-bs-parent="#bukuKasAccordion">
                    <div class="card-body p-0">
                        @if(count($detail) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle mb-0">
                                <thead class="bg-primary text-white small">
                                    <tr>
                                        <th width="10%" class="text-center">Tanggal</th>
                                        <th width="15%">No. Jurnal</th>
                                        <th width="5%" class="text-center">Tipe</th>
                                        <th width="38%">Keterangan</th>
                                        <th width="10%" class="text-end">Pemasukan (Debet)</th>
                                        <th width="10%" class="text-end">Pengeluaran (Kredit)</th>
                                        <th width="12%" class="text-end">Saldo Berjalan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detail as $dr)
                                    <tr>
                                        <td class="text-center small">
                                            {{ $dr['tipe'] === 'SALDO_AWAL' ? '-' : \Carbon\Carbon::parse($dr['tanggal'])->format('d/m/Y') }}
                                        </td>
                                        <td class="small fw-semibold">{{ $dr['nomor_jurnal'] ?? '-' }}</td>
                                        <td class="text-center small">
                                            @if($dr['tipe'] === 'SALDO_AWAL')
                                                <span class="badge bg-warning text-dark">SA</span>
                                            @elseif($dr['tipe'] === 'otomatis')
                                                <span class="badge bg-info text-white">Auto</span>
                                            @else
                                                <span class="badge bg-secondary text-white">Man</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            {{ \Illuminate\Support\Str::limit($dr['keterangan'] ?? '-', 90) }}
                                        </td>
                                        <td class="text-end text-success small">
                                            {{ $dr['debet'] > 0 ? 'Rp ' . number_format($dr['debet'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-end text-danger small">
                                            {{ $dr['kredit'] > 0 ? 'Rp ' . number_format($dr['kredit'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-end fw-semibold small {{ $dr['saldo'] < 0 ? 'text-danger' : 'text-gray-800' }}">
                                            Rp {{ number_format($dr['saldo'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-orange text-white fw-bold">
                                        <td colspan="4" class="text-end pe-4 py-3">Subtotal Akun {{ $coa->kode_akun }}</td>
                                        <td class="text-end py-3">Rp {{ number_format($acc['masuk'], 0, ',', '.') }}</td>
                                        <td class="text-end py-3">Rp {{ number_format($acc['keluar'], 0, ',', '.') }}</td>
                                        <td class="text-end py-3">Rp {{ number_format($acc['saldo_akhir'], 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @else
                        <div class="text-center text-muted py-4 small">
                            Tidak ada mutasi pada akun ini selama periode.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow py-5">
            <div class="card-body text-center text-muted">
                <i class="bi bi-wallet display-4 d-block mb-3 opacity-25"></i>
                Data akun Kas / Bank tidak ditemukan. Silakan setup COA untuk akun Kas (111.xx.xx) dan Bank (112.xx.xx).
            </div>
        </div>
    </div>
</div>
@endif
@endpush
