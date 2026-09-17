@extends('modules.layouts.master')
@push('title', 'Dashboard')
@push('page-modules')

@if(!$isAnggota)
<div class="card shadow-sm mb-4 border-0">
    <div class="card-body py-3 px-4">
        <form action="{{ route('dashboard') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            @if(!empty($lockCabangToUser))
            <div class="col-md-4 col-12">
                <label class="form-label small fw-semibold mb-1 text-secondary">
                    Cabang <span class="small text-muted ms-1 fst-italic">(Terkunci Akun)</span>
                </label>
                <input
                    type="text"
                    class="form-control form-control-sm bg-light"
                    value="{{ (!empty($lockedCabangKode) ? $lockedCabangKode . ' — ' : '') }}{{ $lockedCabangNama ?? 'Cabang Anda' }}"
                    disabled
                    tabindex="-1"
                    aria-disabled="true"
                    style="cursor: not-allowed;"
                >
                <input type="hidden" name="cabang_id" value="{{ $lockedCabangId ?? '' }}">
            </div>
            @else
            <div class="col-md-4 col-12">
                <label class="form-label small fw-semibold mb-1 text-secondary">Cabang</label>
                <select name="cabang_id" class="form-select form-select-sm select2-single" style="width: 100%;" data-placeholder="- Pilih -">
                    <option value="">- Pilih -</option>
                    @foreach($cabangList as $c)
                        <option value="{{ $c->id }}" {{ old('cabang_id', $currentFilter['cabang_id'] ?? '') == $c->id ? 'selected' : '' }}>
                            {{ $c->kode_cabang }} — {{ $c->nama_cabang }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3 col-6">
                <label class="form-label small fw-semibold mb-1 text-secondary">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ old('tanggal_awal', $currentFilter['tanggal_awal']) }}">
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label small fw-semibold mb-1 text-secondary">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ old('tanggal_akhir', $currentFilter['tanggal_akhir']) }}">
            </div>
            <div class="col-md-2 col-12 d-flex gap-2">
                <button type="submit" name="apply_filter" value="1" class="btn btn-sm bg-orange text-white flex-grow-1 fw-semibold border-0" style="background-color: #f97316;">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <button type="submit" name="_reset_filter" value="1" class="btn btn-sm btn-outline-secondary flex-grow-1 fw-semibold">
                    Reset
                </button>
            </div>
        </form>
    </div>
</div>

@php
    $adaFilter = !empty($currentFilter['cabang_id']) || !empty($currentFilter['tanggal_awal']) || !empty($currentFilter['tanggal_akhir']);
    $cabangTerpilih = $cabangList->firstWhere('id', $currentFilter['cabang_id']);
@endphp
@if($adaFilter)
<div class="alert alert-info border-0 mb-4 py-2 px-3 small shadow-sm" role="alert" style="background-color: #eef6ff; color: #1e40af;">
    <i class="bi bi-info-circle me-2"></i>
    Menampilkan data
    @if($cabangTerpilih)
        <strong>Cabang {{ $cabangTerpilih->nama_cabang }}</strong>
    @else
        <strong>Seluruh Cabang</strong>
    @endif
    @if(!empty($currentFilter['tanggal_awal']) && !empty($currentFilter['tanggal_akhir']))
        , periode <strong>{{ \Carbon\Carbon::parse($currentFilter['tanggal_awal'])->translatedFormat('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($currentFilter['tanggal_akhir'])->translatedFormat('d M Y') }}</strong>
    @elseif(!empty($currentFilter['tanggal_awal']))
        , mulai dari <strong>{{ \Carbon\Carbon::parse($currentFilter['tanggal_awal'])->translatedFormat('d M Y') }}</strong>
    @elseif(!empty($currentFilter['tanggal_akhir']))
        , sampai dengan <strong>{{ \Carbon\Carbon::parse($currentFilter['tanggal_akhir'])->translatedFormat('d M Y') }}</strong>
    @else
        , seluruh periode
    @endif
</div>
@endif
@endif

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white bg-gradient-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">TOTAL ANGGOTA</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $totalAnggota ?? 0 }}</div>
                        <small>Seluruh anggota aktif</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white bg-gradient-orange shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">TOTAL CABANG</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $totalCabang ?? 0 }}</div>
                        <small>Kantor cabang terdaftar</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white bg-gradient-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">TOTAL SIMPANAN</div>
                        <div class="h5 mb-0 font-weight-bold">Rp {{ number_format($totalSimpanan ?? 0, 0, ',', '.') }}</div>
                        <small>Akumulasi seluruh simpanan</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-piggy-bank fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white bg-gradient-orange shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">TOTAL PINJAMAN</div>
                        <div class="h5 mb-0 font-weight-bold">Rp {{ number_format($totalPinjaman ?? 0, 0, ',', '.') }}</div>
                        <small>Akumulasi seluruh pinjaman</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-cash-stack fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border border-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">PINJAMAN BERJALAN</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPinjamanBerjalan ?? 0 }}</div>
                        <small class="text-muted">Pinjaman status dicairkan/berjalan</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-journal-check fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border border-orange shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-orange text-uppercase mb-1">TUNGGAKAN</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalAngsuranBelumLunas ?? 0, 0, ',', '.') }}</div>
                        <small class="text-muted">Total angsuran belum lunas</small>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar2-x fa-2x text-orange"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-8 col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Grafik Simpanan & Pinjaman (6 Bulan Terakhir)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="position: relative; height: 320px;">
                    <canvas id="chartSimpananPinjaman"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-orange">Distribusi Status Pinjaman</h6>
            </div>
            <div class="card-body">
                <div style="position: relative; height: 320px;">
                    <canvas id="chartStatusPinjaman"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        const ctx1 = document.getElementById('chartSimpananPinjaman');
        if (ctx1) {
            const labels = @json($labelsBulan ?? []);
            const dataSimpanan = @json($dataSimpananBulan ?? []);
            const dataPinjaman = @json($dataPinjamanBulan ?? []);
            const formatRupiah = (v) => 'Rp ' + (v ?? 0).toLocaleString('id-ID');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Simpanan',
                            data: dataSimpanan,
                            backgroundColor: 'rgba(2, 132, 199, 0.75)',
                            borderColor: '#0284c7',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'Pinjaman',
                            data: dataPinjaman,
                            backgroundColor: 'rgba(249, 115, 22, 0.75)',
                            borderColor: '#f97316',
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { font: { family: "'Plus Jakarta Sans'" } } },
                        tooltip: {
                            callbacks: { label: (ctx) => ctx.dataset.label + ': ' + formatRupiah(ctx.parsed.y) }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: { family: "'Plus Jakarta Sans'" },
                                callback: (v) => 'Rp ' + (v/1000000).toFixed(1) + ' Jt'
                            }
                        },
                        x: { ticks: { font: { family: "'Plus Jakarta Sans'" } } }
                    }
                }
            });
        }

        const ctx2 = document.getElementById('chartStatusPinjaman');
        if (ctx2) {
            const statusLabels = @json($statusLabels ?? []);
            const statusValues = @json($statusValues ?? []);
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusValues,
                        backgroundColor: [
                            '#0ea5e9', '#8b5cf6', '#0284c7', '#f97316',
                            '#f59e0b', '#22c55e', '#ef4444'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { family: "'Plus Jakarta Sans'", size: 11 } } },
                        tooltip: {
                            callbacks: { label: (ctx) => ctx.label + ': ' + (ctx.parsed ?? 0) + ' data' }
                        }
                    }
                }
            });
        }
    }
});
</script>
@endpush

<div class="row">
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Pinjaman Terbaru</h6>
            </div>
            <div class="card-body">
                @if(($pinjamanTerbaru ?? collect())->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Nomor Pinjaman</th>
                                <th>Anggota</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Tanggal Pengajuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pinjamanTerbaru ?? [] as $key => $pinjaman)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $pinjaman->nomor_pinjaman ?? '-' }}</td>
                                <td>{{ $pinjaman->anggota->nama ?? '-' }}</td>
                                <td>Rp {{ number_format($pinjaman->jumlah_pinjaman ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $status = strtolower($pinjaman->status ?? '');
                                        $badgeClass = 'bg-secondary';
                                        if(in_array($status, ['aktif', 'lunas'])) $badgeClass = 'bg-success';
                                        elseif($status === 'diajukan') $badgeClass = 'bg-info';
                                        elseif($status === 'disetujui') $badgeClass = 'bg-primary';
                                        elseif($status === 'dicairkan') $badgeClass = 'bg-orange text-white';
                                        elseif($status === 'berjalan') $badgeClass = 'bg-warning text-dark';
                                        elseif($status === 'ditolak') $badgeClass = 'bg-danger';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($pinjaman->status ?? '-') }}</span>
                                </td>
                                <td>@tanggal($pinjaman->tgl_pengajuan)</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox me-2"></i>Belum ada data pinjaman terbaru.
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-orange">Simpanan Terbaru</h6>
            </div>
            <div class="card-body">
                @if(($simpananTerbaru ?? collect())->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Anggota</th>
                                <th>Jenis</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($simpananTerbaru ?? [] as $key => $simpanan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>@tanggal($simpanan->tanggal)</td>
                                <td>{{ $simpanan->anggota->nama ?? '-' }}</td>
                                <td>{{ $simpanan->jenisSimpanan->nama_jenis ?? '-' }}</td>
                                <td>Rp {{ number_format($simpanan->nominal ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox me-2"></i>Belum ada data simpanan terbaru.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-12 col-lg-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold" style="color: #16a34a;">Anggota Terbaru (Pendaftaran Bulan Ini)</h6>
            </div>
            <div class="card-body">
                @if(($anggotaTerbaru ?? collect())->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead style="background-color: #16a34a; color: #ffffff;">
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama Anggota</th>
                                <th>Cabang</th>
                                <th>Email</th>
                                <th>Status Pendaftaran</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($anggotaTerbaru ?? [] as $ag)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $ag->nik ?? '-' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $ag->nama ?? '-' }}</div>
                                    @if($ag->no_anggota)
                                        <div class="small text-muted">No. Anggota: {{ $ag->no_anggota }}</div>
                                    @endif
                                </td>
                                <td>{{ $ag->cabang->kode_cabang ?? '-' }} — {{ $ag->cabang->nama_cabang ?? '-' }}</td>
                                <td>{{ $ag->user?->email ?? $ag->email ?? '-' }}</td>
                                <td>
                                    @php
                                        $st = strtolower($ag->status_pendaftaran ?? '');
                                        $clsSt = 'bg-secondary';
                                        if ($st === 'menunggu_verifikasi') $clsSt = 'bg-warning text-dark';
                                        elseif ($st === 'disetujui') $clsSt = 'bg-success';
                                        elseif ($st === 'ditolak') $clsSt = 'bg-danger';
                                        elseif ($st === 'tidak_perlu_verifikasi') $clsSt = 'bg-info';
                                        $labelSt = match($st) {
                                            'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                            'disetujui' => 'Disetujui',
                                            'ditolak' => 'Ditolak',
                                            'tidak_perlu_verifikasi' => 'Aktif',
                                            default => ucfirst($st ?: '-'),
                                        };
                                    @endphp
                                    <span class="badge {{ $clsSt }}">{{ $labelSt }}</span>
                                </td>
                                <td>@tanggalWaktu($ag->created_at)</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox me-2"></i>Tidak ada data anggota pada periode filter.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endpush
