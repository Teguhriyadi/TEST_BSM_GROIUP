@extends('modules.layouts.master')
@push('title', 'Buku Besar')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Buku Besar</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Buku Besar</li>
        </ol>
    </nav>
</div>

@php
$rowsBB = $summary6Kolom['rows'] ?? [];
$totalsBB = $summary6Kolom['totals'] ?? [];
$balanceBB = $summary6Kolom['balance'] ?? [];
$labelKelBB = [
    'aset_lancar' => '110 - AKTIVA LANCAR',
    'aset_tetap' => '120 - AKTIVA TETAP',
    'kewajiban' => '200 - KEWAJIBAN',
    'modal' => '300 - MODAL',
    'pendapatan' => '400 - PENDAPATAN',
    'beban' => '500 - BEBAN',
];
$groupedKelBB = [];
foreach ($rowsBB as $r) {
    $k = $r['kelompok'] ?? 'lainnya';
    if (!isset($groupedKelBB[$k])) {
        $groupedKelBB[$k] = [
            'label' => $labelKelBB[$k] ?? ucwords(str_replace('_', ' ', $k)),
            'items' => [],
            'sa_d' => 0, 'sa_k' => 0,
            'mut_d' => 0, 'mut_k' => 0,
            'sk_d' => 0, 'sk_k' => 0,
        ];
    }
    $groupedKelBB[$k]['items'][] = $r;
    $groupedKelBB[$k]['sa_d'] += $r['sa_debet'];
    $groupedKelBB[$k]['sa_k'] += $r['sa_kredit'];
    $groupedKelBB[$k]['mut_d'] += $r['mut_debet'];
    $groupedKelBB[$k]['mut_k'] += $r['mut_kredit'];
    $groupedKelBB[$k]['sk_d'] += $r['sk_debet'];
    $groupedKelBB[$k]['sk_k'] += $r['sk_kredit'];
}
@endphp

<div class="row mb-3">
    <div class="col-lg-12">
        <form method="GET" action="{{ route('buku-besar.index') }}" class="card shadow bg-white p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="coa_id" class="form-label">Pilih Akun (COA) untuk Detail Mutasi</label>
                    <select class="form-select select2 @error('coa_id') is-invalid @enderror" id="coa_id" name="coa_id" data-placeholder="-- Pilih Salah Satu Akun --">
                        <option value=""></option>
                        @foreach($daftarCoa as $c)
                        <option value="{{ $c->id }}" {{ $coaId == $c->id ? 'selected' : '' }}>{{ $c->kode_akun }} - {{ $c->nama_akun }}</option>
                        @endforeach
                    </select>
                    @error('coa_id')
                    <div class="invalid-feedback"><small>{{ $message }}</small></div>
                    @enderror
                    <div class="form-text small text-muted mt-1">Opsional: Kosongkan untuk menampilkan ringkasan semua akun.</div>
                </div>
                <div class="col-md-2">
                    <label for="tanggal_awal" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $dari }}">
                </div>
                <div class="col-md-2">
                    <label for="tanggal_akhir" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $sampai }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-search me-1"></i> Tampilkan
                    </button>
                    <a href="{{ route('buku-besar.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mb-3 g-2">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Saldo Awal D = K</span>
                    @if($balanceBB['sa_balance'] ?? false)
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
                    @if($balanceBB['mut_balance'] ?? false)
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
                    @if($balanceBB['sk_balance'] ?? false)
                        <span class="badge bg-success text-white px-3 py-1">BALANCE</span>
                    @else
                        <span class="badge bg-danger text-white px-3 py-1">TIDAK BALANCE</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light text-center">
                <h5 class="fw-bold text-gray-800 mb-0">RINGKASAN BUKU BESAR (SEMUA AKUN)</h5>
                <div class="small text-muted mt-1">
                    Periode: {{ \Carbon\Carbon::parse($dari)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d F Y') }}
                    · {{ $cabangId ? ('Cabang: ' . (\App\Models\Cabang::find($cabangId)?->nama_cabang ?? '-')) : 'Semua Cabang (Konsolidasi)' }}
                </div>
            </div>
            @if(count($rowsBB) > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0 small">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="8%" class="text-center">Kode</th>
                                <th width="26%">Nama Perkiraan</th>
                                <th width="11%" class="text-end">Saldo Awal Debet</th>
                                <th width="11%" class="text-end">Saldo Awal Kredit</th>
                                <th width="11%" class="text-end">Mutasi Debet</th>
                                <th width="11%" class="text-end">Mutasi Kredit</th>
                                <th width="11%" class="text-end">Saldo Akhir Debet</th>
                                <th width="11%" class="text-end">Saldo Akhir Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedKelBB as $gk)
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
                                <td colspan="2" class="text-end pe-4">Subtotal {{ $gk['label'] }}</td>
                                <td class="text-end">Rp {{ number_format($gk['sa_d'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($gk['sa_k'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($gk['mut_d'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($gk['mut_k'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($gk['sk_d'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($gk['sk_k'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-orange text-white fw-bold">
                                <td colspan="2" class="text-end pe-4 py-3">TOTAL</td>
                                <td class="text-end py-3">Rp {{ number_format($totalsBB['sa_debet'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($totalsBB['sa_kredit'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($totalsBB['mut_debet'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($totalsBB['mut_kredit'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($totalsBB['sk_debet'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end py-3">Rp {{ number_format($totalsBB['sk_kredit'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-journals display-4 d-block mb-3 opacity-25"></i>
                Belum ada data akun COA aktif.
            </div>
            @endif
        </div>
    </div>
</div>

@if($coaTerpilih)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-arrow-right me-2 text-orange"></i>
                        Detail Mutasi: {{ $coaTerpilih->kode_akun }} - {{ $coaTerpilih->nama_akun }}
                    </h6>
                    <div class="small text-muted mt-1">
                        Periode: {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}
                        · Saldo Normal: {{ $coaTerpilih->saldo_normal == 'debet' ? 'Debet' : 'Kredit' }}
                        · Kelompok: {{ ucwords(str_replace('_',' ',$coaTerpilih->kelompok)) }}
                    </div>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Saldo Akhir s/d {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}</div>
                    <div class="h4 fw-bold text-orange mb-0">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($detailRows->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="8%" class="text-center">Tanggal</th>
                                <th width="15%">No. Jurnal</th>
                                <th width="5%" class="text-center">Tipe</th>
                                <th width="32%">Keterangan</th>
                                <th width="12%" class="text-end">Debet</th>
                                <th width="12%" class="text-end">Kredit</th>
                                <th width="16%" class="text-end">Saldo Berjalan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailRows as $r)
                            <tr>
                                <td class="text-center">
                                    {{ isset($r['tipe']) && $r['tipe'] === 'SALDO_AWAL' ? '-' : \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}
                                </td>
                                <td class="fw-semibold">{{ $r['nomor_jurnal'] ?? '-' }}</td>
                                <td class="text-center">
                                    @if(($r['tipe'] ?? '') === 'SALDO_AWAL')
                                        <span class="badge bg-warning text-dark">SA</span>
                                    @elseif(($r['tipe'] ?? '') === 'otomatis')
                                        <span class="badge bg-info text-white">Auto</span>
                                    @else
                                        <span class="badge bg-secondary text-white">Man</span>
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($r['keterangan'] ?? '-', 70) }}</td>
                                <td class="text-end">{{ ($r['debet'] ?? 0) > 0 ? 'Rp ' . number_format($r['debet'], 0, ',', '.') : '-' }}</td>
                                <td class="text-end">{{ ($r['kredit'] ?? 0) > 0 ? 'Rp ' . number_format($r['kredit'], 0, ',', '.') : '-' }}</td>
                                <td class="text-end fw-semibold {{ ($r['saldo'] ?? 0) < 0 ? 'text-danger' : 'text-gray-800' }}">Rp {{ number_format($r['saldo'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="card-body text-center text-muted py-5">Tidak ada mutasi pada akun ini selama periode yang dipilih.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
@endpush
