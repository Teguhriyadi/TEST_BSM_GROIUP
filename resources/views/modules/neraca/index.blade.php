@extends('modules.layouts.master')
@push('title', 'Laporan Neraca')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => false,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Laporan Neraca</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Neraca</li>
        </ol>
    </nav>
</div>

<div class="row mb-3">
    <div class="col-lg-12">
        <form method="GET" action="{{ route('neraca.index') }}" class="card shadow bg-white p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="tanggal_cutoff" class="form-label">Tanggal Cutoff (Per Posisi) <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="tanggal_cutoff" name="tanggal_cutoff" value="{{ $cutoff }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-bar-chart me-1"></i> Tampilkan Neraca
                    </button>
                    <a href="{{ route('neraca.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

@php
    $kelompokAset = $data['kelompok_aset'] ?? [];
    $kelompokKewajiban = $data['kelompok_kewajiban'] ?? [];
    $kelompokEkuitas = $data['kelompok_ekuitas'] ?? [];
    $kelompokPendapatan = $data['kelompok_pendapatan'] ?? [];
    $kelompokBeban = $data['kelompok_beban'] ?? [];
    $r = $data['ringkasan'] ?? [];
    $totalAset = $r['total_aset'] ?? 0;
    $totalKewajiban = $r['total_kewajiban'] ?? 0;
    $totalEkuitasSebelum = $r['total_ekuitas_sebelum'] ?? 0;
    $labaRugiBerjalan = $r['laba_rugi_berjalan'] ?? 0;
    $totalEkuitas = $r['total_ekuitas'] ?? 0;
    $totalKewajibanEkuitas = $r['total_kewajiban_ekuitas'] ?? 0;
    $selisih = $r['selisih_balance'] ?? 0;
    $isBalance = $r['is_balance'] ?? false;

    $buildRows = function(array $list){
        $byId = [];
        foreach ($list as $row) $byId[$row['id']] = $row;
        $parents = [];
        $children = [];
        foreach ($list as $row) {
            if (empty($row['parent_id']) || $row['level'] == 1) {
                if (!isset($parents[$row['id']])) $parents[$row['id']] = $row;
            } else {
                $children[$row['parent_id']][] = $row;
            }
        }
        $out = [];
        foreach ($parents as $pid => $p) {
            $kidRows = $children[$pid] ?? [];
            $subSaldo = count($kidRows) ? 0 : ($p['saldo'] ?? 0);
            foreach ($kidRows as $kr) $subSaldo += ($kr['saldo'] ?? 0);
            $out[] = ['parent' => $p, 'children' => $kidRows, 'subtotal' => $subSaldo];
        }
        $loneChilds = [];
        foreach ($list as $row) {
            if (!empty($row['parent_id']) && !isset($parents[$row['parent_id']])) {
                $loneChilds[] = $row;
            }
        }
        foreach ($loneChilds as $lc) {
            $out[] = ['parent' => null, 'children' => [$lc], 'subtotal' => $lc['saldo'] ?? 0];
        }
        return $out;
    };

    $renderKelompok = function(array $rowsKelompok, bool $showVirtual = false) use ($buildRows, $labaRugiBerjalan) {
        $builtGroups = $buildRows($rowsKelompok);
        $out = '';
        foreach ($builtGroups as $g) {
            if ($g['parent']) {
                $p = $g['parent'];
                $out .= '<tr><td colspan="3" class="bg-light fw-bold text-gray-800 py-2">' . htmlspecialchars($p['kode_akun'] . ' - ' . $p['nama_akun']) . '</td></tr>';
            }
            foreach ($g['children'] as $c) {
                $saldoC = (float) ($c['saldo'] ?? 0);
                $labelC = htmlspecialchars($c['kode_akun'] . ' - ' . $c['nama_akun']);
                $valC = abs($saldoC) < 0.005 ? '-' : 'Rp ' . number_format($saldoC, 0, ',', '.');
                $out .= "<tr><td width=\"5%\"></td><td width=\"65%\">{$labelC}</td><td width=\"30%\" class=\"text-end\">{$valC}</td></tr>";
            }
            if ($g['parent']) {
                $s = (float) $g['subtotal'];
                $val = abs($s) < 0.005 ? '-' : 'Rp ' . number_format($s, 0, ',', '.');
                $out .= "<tr class=\"fw-semibold\"><td></td><td class=\"text-end pe-4\">Subtotal</td><td class=\"text-end\">{$val}</td></tr>";
            }
        }
        return $out;
    };
@endphp

<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light text-center">
                <h5 class="fw-bold text-gray-800 mb-0">NERACA</h5>
                <div class="small text-muted mt-1">
                    Per {{ \Carbon\Carbon::parse($cutoff)->format('d F Y') }}
                    · {{ $cabangId ? ('Cabang: ' . (\App\Models\Cabang::find($cabangId)?->nama_cabang ?? '-')) : 'Semua Cabang (Konsolidasi)' }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow h-100">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">ASET</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <tbody>
                            {!! $renderKelompok($kelompokAset) !!}
                        </tbody>
                        <tfoot>
                            <tr class="bg-orange text-white">
                                <td colspan="2" class="fw-bold py-3">TOTAL ASET</td>
                                <td class="text-end fw-bold py-3 h5 mb-0">Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow h-100">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">KEWAJIBAN &amp; EKUITAS</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <tbody>
                            {!! $renderKelompok($kelompokKewajiban) !!}
                            {!! $renderKelompok($kelompokEkuitas) !!}
                            <tr>
                                <td width="5%"></td>
                                <td width="65%">Laba Rugi Berjalan s/d Cutoff <span class="badge bg-info text-white ms-1">Virtual</span></td>
                                <td width="30%" class="text-end">{{ abs($labaRugiBerjalan) < 0.005 ? '-' : 'Rp ' . number_format($labaRugiBerjalan, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="fw-semibold">
                                <td></td>
                                <td class="text-end pe-4">Subtotal Ekuitas + Laba Rugi</td>
                                <td class="text-end">Rp {{ number_format($totalEkuitas, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-orange text-white">
                                <td colspan="2" class="fw-bold py-3">TOTAL KEWAJIBAN &amp; EKUITAS</td>
                                <td class="text-end fw-bold py-3 h5 mb-0">Rp {{ number_format($totalKewajibanEkuitas, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light text-center">
                <h5 class="fw-bold text-gray-800 mb-0">TOTAL PADANAN NERACA</h5>
                <div class="small text-muted mt-1">
                    Aset = Kewajiban + Ekuitas (Persamaan Akuntansi Dasar)
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white small">
                            <tr>
                                <th width="40%" class="text-center py-3">
                                    <h6 class="mb-0 fw-bold">AKTIVA (ASET)</h6>
                                </th>
                                <th width="5%" class="text-center py-3">
                                    <h5 class="mb-0">=</h5>
                                </th>
                                <th width="40%" class="text-center py-3">
                                    <h6 class="mb-0 fw-bold">PASSIVA (KEWAJIBAN + EKUITAS)</h6>
                                </th>
                                <th width="15%" class="text-center py-3">
                                    <h6 class="mb-0 fw-bold">STATUS</h6>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center py-4" style="background-color: #fef2e6;">
                                    <div class="small text-muted mb-1">Total Aktiva Lancar + Aktiva Tetap</div>
                                    <div class="h4 fw-bold text-orange mb-0">Rp {{ number_format($totalAset, 0, ',', '.') }}</div>
                                </td>
                                <td class="text-center py-4 h3 fw-bold text-gray-800 mb-0" style="background-color: #f3f4f6;">=</td>
                                <td class="text-center py-4" style="background-color: #eef6ff;">
                                    <div class="small text-muted mb-1">Total Kewajiban + Modal + Laba Rugi</div>
                                    <div class="h4 fw-bold text-blue mb-0">Rp {{ number_format($totalKewajibanEkuitas, 0, ',', '.') }}</div>
                                </td>
                                <td class="text-center py-4">
                                    @if($isBalance)
                                    <span class="badge bg-orange text-white px-4 py-2 fs-6 fw-bold">
                                        <i class="bi bi-check-circle me-2"></i>BALANCE
                                    </span>
                                    <div class="small text-success fw-semibold mt-2">
                                        SELISIH Rp 0
                                    </div>
                                    @else
                                    <span class="badge bg-danger text-white px-4 py-2 fs-6 fw-bold">
                                        <i class="bi bi-x-circle me-2"></i>TIDAK BALANCE
                                    </span>
                                    <div class="small text-danger fw-semibold mt-2">
                                        SELISIH Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-body border-top small py-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <span class="text-muted">Total Kewajiban:</span>
                        <span class="fw-semibold text-gray-800 ms-2">Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted">Ekuitas Awal:</span>
                        <span class="fw-semibold text-gray-800 ms-2">Rp {{ number_format($totalEkuitasSebelum, 0, ',', '.') }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted">Laba Rugi Berjalan:</span>
                        <span class="fw-semibold {{ $labaRugiBerjalan < 0 ? 'text-danger' : 'text-gray-800' }} ms-2">Rp {{ number_format($labaRugiBerjalan, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
