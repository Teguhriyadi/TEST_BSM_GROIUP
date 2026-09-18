@extends('modules.layouts.master')
@push('title', 'Laporan Sisa Hasil Usaha (SHU)')
@push('page-modules')
@php
$tahunOptions = [];
$thnSkrg = (int) date('Y');
for ($i = $thnSkrg - 2; $i <= $thnSkrg + 1; $i++) { $tahunOptions[] = $i; }
@endphp
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => false,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Sisa Hasil Usaha (SHU)</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">SHU Tahunan</li>
        </ol>
    </nav>
</div>

<div class="row mb-3">
    <div class="col-lg-12">
        <form method="GET" action="{{ route('laporan-akunting.shu') }}" class="card shadow bg-white p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="tahun" class="form-label">Tahun Buku <span class="text-danger">*</span></label>
                    <select name="tahun" id="tahun" class="form-select select2-single" data-placeholder="- Pilih Tahun -">
                        @foreach($tahunOptions as $th)
                            <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>Tahun {{ $th }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-bar-chart-line me-1"></i> Tampilkan SHU
                    </button>
                    <a href="{{ route('laporan-akunting.shu') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

@php
$dist = $data['distribusi'] ?? [];
$lr = $data['laba_rugi'] ?? [];
$ringkasanLr = $lr['ringkasan'] ?? [];
@endphp

<div class="row mb-4 g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Pendapatan Tahun {{ $tahun }}</div>
                <div class="h5 fw-bold text-success mb-0">Rp {{ number_format($ringkasanLr['total_pendapatan'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Beban + Pajak</div>
                <div class="h5 fw-bold text-danger mb-0">Rp {{ number_format(($ringkasanLr['total_beban_operasional'] ?? 0) + ($ringkasanLr['total_pajak'] ?? 0), 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">SHU Tersedia (Laba Bersih)</div>
                <div class="h5 fw-bold {{ ($dist['shu_tersedia'] ?? 0) > 0 ? 'text-orange' : 'text-danger' }} mb-0">
                    Rp {{ number_format($dist['shu_tersedia'] ?? 0, 0, ',', '.') }}
                </div>
                @if(($dist['shu_tersedia'] ?? 0) > 0)
                    <span class="badge bg-orange text-white mt-2">SHU POSITIF</span>
                @else
                    <span class="badge bg-danger text-white mt-2">TIDAK ADA SHU</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 h-100">
            <div class="card-body py-3">
                <div class="small text-muted mb-1">Total Terdistribusi</div>
                <div class="h5 fw-bold text-gray-800 mb-0">Rp {{ number_format($dist['total_distribusi'] ?? 0, 0, ',', '.') }}</div>
                <div class="small text-muted mt-1">Selisih: Rp {{ number_format($dist['selisih_distribusi'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light text-center">
                <h5 class="fw-bold text-gray-800 mb-0">LAPORAN SISA HASIL USAHA (SHU)</h5>
                <div class="small text-muted mt-1">
                    Tahun Buku {{ $tahun }} (1 Januari s/d 31 Desember {{ $tahun }})
                    · {{ $cabangId ? ('Cabang: ' . (\App\Models\Cabang::find($cabangId)?->nama_cabang ?? '-')) : 'Semua Cabang (Konsolidasi)' }}
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="50%" class="text-center">Uraian</th>
                                <th width="25%" class="text-center">Persentase</th>
                                <th width="25%" class="text-center">Jumlah (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-orange bg-opacity-10">
                                <td class="fw-bold text-orange px-4 py-3">
                                    <h6 class="mb-0 fw-bold">A. SISA HASIL USAHA TERSEDIA (Laba Bersih Setelah Pajak)</h6>
                                </td>
                                <td class="text-center py-3 fw-bold">100%</td>
                                <td class="text-end py-3 fw-bold h6 mb-0 text-orange">
                                    Rp {{ number_format($dist['shu_tersedia'] ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            <tr><td colspan="3" class="py-1 bg-transparent border-0"></td></tr>

                            <tr>
                                <td colspan="3" class="bg-light fw-bold px-4 py-2 text-gray-800">
                                    B. DISTRIBUSI SHU (Sesuai Anggaran Dasar Koperasi)
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-3">
                                    <div class="fw-semibold">1. Cadangan Koperasi</div>
                                    <div class="small text-muted">Digunakan untuk penguatan modal koperasi (akun 320.01)</div>
                                </td>
                                <td class="text-center fw-semibold">50%</td>
                                <td class="text-end py-3">Rp {{ number_format($dist['cadangan'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-3 border-top-0">
                                    <div class="fw-semibold">2. SHU untuk Anggota</div>
                                    <div class="small text-muted">Dibagikan kepada anggota sesuai jasa usaha & modal (akun 330.02)</div>
                                </td>
                                <td class="text-center fw-semibold border-top-0">40%</td>
                                <td class="text-end py-3 border-top-0">Rp {{ number_format($dist['shu_anggota'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="ps-5 py-3 border-top-0">
                                    <div class="fw-semibold">3. Dana Sosial</div>
                                    <div class="small text-muted">Untuk kegiatan sosial & pendidikan anggota (akun 320.03)</div>
                                </td>
                                <td class="text-center fw-semibold border-top-0">10%</td>
                                <td class="text-end py-3 border-top-0">Rp {{ number_format($dist['dana_sosial'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-orange text-white">
                                <td class="fw-bold px-4 py-3">
                                    <h6 class="mb-0 fw-bold">TOTAL DISTRIBUSI SHU</h6>
                                </td>
                                <td class="text-center fw-bold py-3">100%</td>
                                <td class="text-end fw-bold py-3">
                                    <h5 class="mb-0 fw-bold">Rp {{ number_format($dist['total_distribusi'] ?? 0, 0, ',', '.') }}</h5>
                                </td>
                            </tr>
                            <tr class="bg-light">
                                <td class="text-end pe-4 py-3 fw-semibold small" colspan="2">
                                    SELISIH PEMBULATAN (SHU Tersedia - Total Didistribusikan)
                                </td>
                                <td class="text-end fw-bold py-3">
                                    Rp {{ number_format($dist['selisih_distribusi'] ?? 0, 0, ',', '.') }}
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
