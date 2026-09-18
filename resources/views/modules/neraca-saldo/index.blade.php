@extends('modules.layouts.master')
@push('title', 'Neraca Saldo')
@push('page-modules')
@php
    $customFilter = [
        [
            'key' => 'tanggal_cutoff',
            'label' => 'Tanggal Cutoff',
            'type' => 'date',
        ],
    ];
@endphp
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => false,
    'customFilterOptions' => [
        [
            'key' => 'tanggal_cutoff_dummy',
            'label' => 'Tanggal Cutoff',
            'options' => [],
        ],
    ],
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Neraca Saldo</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Neraca Saldo</li>
        </ol>
    </nav>
</div>

<div class="row mb-3">
    <div class="col-lg-12">
        <form method="GET" action="{{ route('neraca-saldo.index') }}" class="card shadow bg-white p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="tanggal_cutoff" class="form-label">Tanggal Cutoff (Per Posisi) <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_cutoff" name="tanggal_cutoff" value="{{ $sampai }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-search me-1"></i> Tampilkan Neraca Saldo
                    </button>
                    <a href="{{ route('neraca-saldo.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

@php
$rows = $data['rows'] ?? [];
$totals = $data['totals'] ?? [];
$balance = $data['balance'] ?? [];
$labelKelompok = [
    'aset_lancar' => '110 - Aset Lancar',
    'aset_tetap' => '120 - Aset Tetap',
    'kewajiban' => '200 - Kewajiban',
    'modal' => '300 - Modal',
    'pendapatan' => '400 - Pendapatan',
    'beban' => '500 - Beban',
];
$groupedByKelompok = [];
foreach ($rows as $r) {
    $k = $r['kelompok'] ?? 'lainnya';
    if (!isset($groupedByKelompok[$k])) {
        $groupedByKelompok[$k] = [
            'label' => $labelKelompok[$k] ?? ucwords(str_replace('_', ' ', $k)),
            'items' => [],
            'subtotal_sa_d' => 0, 'subtotal_sa_k' => 0,
            'subtotal_mut_d' => 0, 'subtotal_mut_k' => 0,
            'subtotal_sk_d' => 0, 'subtotal_sk_k' => 0,
        ];
    }
    $groupedByKelompok[$k]['items'][] = $r;
    $groupedByKelompok[$k]['subtotal_sa_d'] += $r['sa_debet'];
    $groupedByKelompok[$k]['subtotal_sa_k'] += $r['sa_kredit'];
    $groupedByKelompok[$k]['subtotal_mut_d'] += $r['mut_debet'];
    $groupedByKelompok[$k]['subtotal_mut_k'] += $r['mut_kredit'];
    $groupedByKelompok[$k]['subtotal_sk_d'] += $r['sk_debet'];
    $groupedByKelompok[$k]['subtotal_sk_k'] += $r['sk_kredit'];
}
@endphp

<div class="row mb-3 g-2">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Saldo Awal D = K</span>
                    @if($balance['sa_balance'] ?? false)
                        <span class="badge bg-success text-white px-3 py-1">BALANCE</span>
                    @else
                        <span class="badge bg-danger text-white px-3 py-1">TIDAK BALANCE</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Mutasi D = K</span>
                    @if($balance['mut_balance'] ?? false)
                        <span class="badge bg-success text-white px-3 py-1">BALANCE</span>
                    @else
                        <span class="badge bg-danger text-white px-3 py-1">TIDAK BALANCE</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Saldo Akhir D = K</span>
                    @if($balance['sk_balance'] ?? false)
                        <span class="badge bg-success text-white px-3 py-1">BALANCE</span>
                    @else
                        <span class="badge bg-danger text-white px-3 py-1">TIDAK BALANCE</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light text-center">
                <h5 class="fw-bold text-gray-800 mb-0">NERACA SALDO</h5>
                <div class="small text-muted mt-1">
                    Per {{ \Carbon\Carbon::parse($sampai)->format('d F Y') }}
                    · {{ $cabangId ? ('Cabang: ' . (\App\Models\Cabang::find($cabangId)?->nama_cabang ?? '-')) : 'Semua Cabang (Konsolidasi)' }}
                </div>
            </div>
            @if(count($rows) > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="9%" class="text-center">Kode Akun</th>
                                <th width="25%">Nama Akun</th>
                                <th width="11%" class="text-end">Saldo Awal Debet</th>
                                <th width="11%" class="text-end">Saldo Awal Kredit</th>
                                <th width="11%" class="text-end">Mutasi Debet</th>
                                <th width="11%" class="text-end">Mutasi Kredit</th>
                                <th width="11%" class="text-end">Saldo Akhir Debet</th>
                                <th width="11%" class="text-end">Saldo Akhir Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedByKelompok as $gk)
                            <tr>
                                <td colspan="8" class="bg-light fw-bold text-gray-800 py-2 px-3">
                                    {{ $gk['label'] }}
                                </td>
                            </tr>
                            @foreach($gk['items'] as $r)
                            <tr>
                                <td class="text-center">{{ $r['kode_akun'] }}</td>
                                <td>{{ $r['nama_akun'] }}</td>
                                <td class="text-end">
                                    {{ $r['sa_debet'] > 0 ? 'Rp ' . number_format($r['sa_debet'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end">
                                    {{ $r['sa_kredit'] > 0 ? 'Rp ' . number_format($r['sa_kredit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end">
                                    {{ $r['mut_debet'] > 0 ? 'Rp ' . number_format($r['mut_debet'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end">
                                    {{ $r['mut_kredit'] > 0 ? 'Rp ' . number_format($r['mut_kredit'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end">
                                    {{ $r['sk_debet'] > 0 ? 'Rp ' . number_format($r['sk_debet'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end">
                                    {{ $r['sk_kredit'] > 0 ? 'Rp ' . number_format($r['sk_kredit'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                            <tr class="fw-semibold">
                                <td colspan="2" class="text-end pe-4 bg-gray-50">Subtotal {{ $gk['label'] }}</td>
                                <td class="text-end bg-gray-50">Rp {{ number_format($gk['subtotal_sa_d'], 0, ',', '.') }}</td>
                                <td class="text-end bg-gray-50">Rp {{ number_format($gk['subtotal_sa_k'], 0, ',', '.') }}</td>
                                <td class="text-end bg-gray-50">Rp {{ number_format($gk['subtotal_mut_d'], 0, ',', '.') }}</td>
                                <td class="text-end bg-gray-50">Rp {{ number_format($gk['subtotal_mut_k'], 0, ',', '.') }}</td>
                                <td class="text-end bg-gray-50">Rp {{ number_format($gk['subtotal_sk_d'], 0, ',', '.') }}</td>
                                <td class="text-end bg-gray-50">Rp {{ number_format($gk['subtotal_sk_k'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-orange text-white fw-bold">
                                <td colspan="2" class="text-end pe-4 py-3 h6 mb-0">TOTAL NERACA SALDO</td>
                                <td class="text-end py-3 h6 mb-0">Rp {{ number_format($totals['sa_debet'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3 h6 mb-0">Rp {{ number_format($totals['sa_kredit'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3 h6 mb-0">Rp {{ number_format($totals['mut_debet'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3 h6 mb-0">Rp {{ number_format($totals['mut_kredit'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3 h6 mb-0">Rp {{ number_format($totals['sk_debet'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3 h6 mb-0">Rp {{ number_format($totals['sk_kredit'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-bar-chart display-4 d-block mb-3 opacity-25"></i>
                Belum ada data akun (COA) aktif. Silakan setup Daftar Akun terlebih dahulu.
            </div>
            @endif
        </div>
    </div>
</div>
@endpush
