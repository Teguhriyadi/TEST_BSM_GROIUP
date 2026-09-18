@extends('modules.layouts.master')
@push('title', 'Laba Rugi ' . $judulMode)
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        Laba Rugi {{ $judulMode }}
        @if($mode === 'kumulatif')
            <span class="badge bg-info text-white ms-2 small">YTD</span>
        @endif
    </h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Laba Rugi {{ $judulMode }}</li>
        </ol>
    </nav>
</div>

@php
$ringkasan = $data['ringkasan'] ?? [];
$pendapatan = $data['pendapatan'] ?? [];
$beban = $data['beban'] ?? [];
$pajak = $data['pajak'] ?? [];
$labaSebelumPajak = $ringkasan['laba_sebelum_pajak'] ?? 0;
$totalPajak = $ringkasan['total_pajak'] ?? 0;
$labaBersih = $ringkasan['laba_bersih'] ?? 0;
@endphp

<div class="row mb-4 g-3">
    <div class="col-xl-4 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Pendapatan (Kredit)</div>
                <div class="h5 fw-bold text-success mb-0">Rp {{ number_format($ringkasan['total_pendapatan'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Beban Operasional (Debet)</div>
                <div class="h5 fw-bold text-danger mb-0">Rp {{ number_format($ringkasan['total_beban_operasional'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12">
        <div class="card shadow border-0 h-100 {{ $labaBersih >= 0 ? '' : 'border border-danger' }}">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Laba Bersih Setelah Pajak</div>
                <div class="h4 fw-bold {{ $labaBersih >= 0 ? 'text-orange' : 'text-danger' }} mb-0">Rp {{ number_format($labaBersih, 0, ',', '.') }}</div>
                @if($labaBersih >= 0)
                    <span class="badge bg-orange text-white mt-2"><i class="bi bi-arrow-up me-1"></i>LABA</span>
                @else
                    <span class="badge bg-danger text-white mt-2"><i class="bi bi-arrow-down me-1"></i>RUGI</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light text-center">
                <h5 class="fw-bold text-gray-800 mb-0">
                    LAPORAN LABA RUGI
                    @if($mode === 'kumulatif') (KUMULATIF) @endif
                </h5>
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
                                <th width="14%" class="text-center">Kode</th>
                                <th width="56%">Uraian Akun</th>
                                <th width="15%" class="text-end">Mutasi Debet</th>
                                <th width="15%" class="text-end">Mutasi Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="bg-success bg-opacity-10 fw-bold text-success py-2 px-3">
                                    I. PENDAPATAN (400)
                                </td>
                            </tr>
                            @if(count($pendapatan) > 0)
                                @foreach($pendapatan as $p)
                                <tr>
                                    <td class="text-center">{{ $p['kode_akun'] }}</td>
                                    <td class="ps-4">{{ $p['nama_akun'] }}</td>
                                    <td class="text-end text-muted">
                                        {{ $p['mut_debet'] > 0 ? 'Rp ' . number_format($p['mut_debet'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end text-success">
                                        {{ $p['mut_kredit'] > 0 ? 'Rp ' . number_format($p['mut_kredit'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3 fst-italic">
                                        (Tidak ada transaksi pendapatan pada periode ini)
                                    </td>
                                </tr>
                            @endif
                            <tr class="fw-bold bg-success bg-opacity-5">
                                <td colspan="2" class="text-end pe-4">TOTAL PENDAPATAN</td>
                                <td class="text-end">-</td>
                                <td class="text-end text-success">Rp {{ number_format($ringkasan['total_pendapatan'] ?? 0, 0, ',', '.') }}</td>
                            </tr>

                            <tr><td colspan="4" class="py-1 bg-transparent border-0"></td></tr>

                            <tr>
                                <td colspan="4" class="bg-danger bg-opacity-10 fw-bold text-danger py-2 px-3">
                                    II. BEBAN OPERASIONAL (510)
                                </td>
                            </tr>
                            @if(count($beban) > 0)
                                @foreach($beban as $b)
                                <tr>
                                    <td class="text-center">{{ $b['kode_akun'] }}</td>
                                    <td class="ps-4">{{ $b['nama_akun'] }}</td>
                                    <td class="text-end text-danger">
                                        {{ $b['mut_debet'] > 0 ? 'Rp ' . number_format($b['mut_debet'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ $b['mut_kredit'] > 0 ? 'Rp ' . number_format($b['mut_kredit'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3 fst-italic">
                                        (Tidak ada transaksi beban operasional pada periode ini)
                                    </td>
                                </tr>
                            @endif
                            <tr class="fw-bold bg-danger bg-opacity-5">
                                <td colspan="2" class="text-end pe-4">TOTAL BEBAN OPERASIONAL</td>
                                <td class="text-end text-danger">Rp {{ number_format($ringkasan['total_beban_operasional'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">-</td>
                            </tr>

                            <tr class="fw-bold" style="background-color: #fff0db;">
                                <td colspan="2" class="text-end pe-4 py-3">
                                    <h6 class="mb-0 fw-bold">LABA (RUGI) SEBELUM PAJAK</h6>
                                </td>
                                <td class="text-end py-3" colspan="2">
                                    <h6 class="mb-0 fw-bold {{ $labaSebelumPajak >= 0 ? 'text-orange' : 'text-danger' }}">
                                        Rp {{ number_format($labaSebelumPajak, 0, ',', '.') }}
                                    </h6>
                                </td>
                            </tr>

                            <tr><td colspan="4" class="py-1 bg-transparent border-0"></td></tr>

                            <tr>
                                <td colspan="4" class="bg-warning bg-opacity-10 fw-bold text-warning-emphasis py-2 px-3">
                                    III. PAJAK (520)
                                </td>
                            </tr>
                            @if(count($pajak) > 0)
                                @foreach($pajak as $p)
                                <tr>
                                    <td class="text-center">{{ $p['kode_akun'] }}</td>
                                    <td class="ps-4">{{ $p['nama_akun'] }}</td>
                                    <td class="text-end text-warning-emphasis">
                                        {{ $p['mut_debet'] > 0 ? 'Rp ' . number_format($p['mut_debet'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ $p['mut_kredit'] > 0 ? 'Rp ' . number_format($p['mut_kredit'], 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3 fst-italic">
                                        (Belum ada transaksi pajak)
                                    </td>
                                </tr>
                            @endif
                            <tr class="fw-bold bg-warning bg-opacity-5">
                                <td colspan="2" class="text-end pe-4">TOTAL PAJAK</td>
                                <td class="text-end text-warning-emphasis">Rp {{ number_format($totalPajak, 0, ',', '.') }}</td>
                                <td class="text-end">-</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-orange text-white">
                                <td colspan="2" class="fw-bold py-3 text-end pe-4">
                                    <h5 class="mb-0 fw-bold">TOTAL LABA (RUGI) BERSIH SETELAH PAJAK</h5>
                                </td>
                                <td colspan="2" class="text-end py-3">
                                    <h4 class="mb-0 fw-bold">{{ $labaBersih >= 0 ? '' : '(' }}Rp {{ number_format(abs($labaBersih), 0, ',', '.') }}{{ $labaBersih >= 0 ? '' : ')' }}</h4>
                                    <div class="small fw-normal opacity-90 mt-1">
                                        {{ $labaBersih >= 0 ? 'LABA PERIODE INI' : 'RUGI PERIODE INI' }}
                                    </div>
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
