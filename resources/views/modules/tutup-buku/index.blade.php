@extends('modules.layouts.master')
@push('title', 'Tutup Buku')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => false,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tutup Buku Periode</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tutup Buku</li>
        </ol>
    </nav>
</div>

@php
    $tahunBulan = explode('-', $periode);
    $periodeLabel = count($tahunBulan) === 2
        ? \Carbon\Carbon::create((int)$tahunBulan[0], (int)$tahunBulan[1], 1)->translatedFormat('F Y')
        : $periode;
@endphp

@if(session('success'))
<div class="row mb-3">
    <div class="col-lg-12">
        <div class="alert alert-success border-0 shadow-sm py-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Berhasil!</strong> {{ session('success') }}
        </div>
    </div>
</div>
@endif
@if(session('error'))
<div class="row mb-3">
    <div class="col-lg-12">
        <div class="alert alert-danger border-0 shadow-sm py-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Gagal!</strong> {{ session('error') }}
        </div>
    </div>
</div>
@endif

<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-calendar2-x me-2 text-orange"></i>
                    Form Tutup Buku Periode
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tutup-buku.proses') }}" class="row g-3">
                    @csrf
                    <input type="hidden" name="cabang_id" value="{{ old('cabang_id', $cabangId ?? '') }}">

                    <div class="col-md-6">
                        <label for="periode" class="form-label">
                            Periode Tutup Buku <span class="text-danger">*</span>
                        </label>
                        <input
                            type="month"
                            name="periode"
                            id="periode"
                            required
                            class="form-control @error('periode') is-invalid @enderror"
                            value="{{ old('periode', $periode) }}"
                        >
                        @error('periode')
                        <div class="invalid-feedback small"><small>{{ $message }}</small></div>
                        @enderror
                        <div class="form-text small text-muted mt-1">
                            Bulan dan tahun periode yang akan ditutup.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Estimasi Tanggal Jurnal</label>
                        <input
                            type="text"
                            class="form-control bg-light"
                            disabled
                            tabindex="-1"
                            value="Akhir bulan periode {{ $periodeLabel }}"
                        >
                    </div>

                    <div class="col-md-12">
                        <div class="alert alert-warning border-0 py-2 small mb-0" style="background-color: #fff7ed; color: #92400e;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>PERHATIAN:</strong>
                            Proses tutup buku akan membuat Jurnal Penutup otomatis
                            (nolkan semua akun Pendapatan 400 dan Beban 500,
                            selisih dipindah ke akun SHU Tahun Berjalan 330.01.01).
                            Jurnal penutup otomatis berstatus "Diposting" dan tidak dapat dihapus sembarangan.
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2 pt-2">
                        <button type="submit" class="btn text-white fw-semibold border-0 flex-grow-1" style="background-color: #f97316;">
                            <i class="bi bi-check2-square me-1"></i>
                            Proses Tutup Buku
                        </button>
                        <a href="{{ route('laba-rugi.periode') }}" target="_blank" class="btn btn-outline-secondary flex-grow-1 fw-semibold">
                            <i class="bi bi-eye me-1"></i>
                            Cek Laba Rugi Dulu
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow h-100">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-info-circle me-2 text-blue"></i>
                    Alur Tutup Buku
                </h6>
            </div>
            <div class="card-body">
                <ol class="list-group list-group-numbered border-0">
                    <li class="list-group-item d-flex border-0 border-bottom py-2 px-0 align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-semibold">Pilih Periode</div>
                            <div class="small text-muted">Pilih bulan/tahun yang ingin ditutup buku.</div>
                        </div>
                    </li>
                    <li class="list-group-item d-flex border-0 border-bottom py-2 px-0 align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-semibold">Verifikasi Laba Rugi</div>
                            <div class="small text-muted">Pastikan Laba Rugi periode sesuai dengan klik tombol "Cek Laba Rugi Dulu".</div>
                        </div>
                    </li>
                    <li class="list-group-item d-flex border-0 border-bottom py-2 px-0 align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-semibold">Sistem Hitung Otomatis</div>
                            <div class="small text-muted">Semua akun Pendapatan (400) di-debet nolkan, semua Beban (500) di-kredit nolkan.</div>
                        </div>
                    </li>
                    <li class="list-group-item d-flex border-0 border-bottom py-2 px-0 align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-semibold">Pindah ke SHU</div>
                            <div class="small text-muted">Selisih Laba/Rugi dipindahkan ke akun 330.01.01 - SHU Tahun Berjalan.</div>
                        </div>
                    </li>
                    <li class="list-group-item d-flex border-0 py-2 px-0 align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-semibold">Jurnal Penutup Terbentuk</div>
                            <div class="small text-muted">Satu Jurnal Otomatis status Diposting + nomor jurnal bisa dicek di Jurnal Umum.</div>
                        </div>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow border-0">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-diagram-3 me-2 text-orange"></i>
                    Daftar Akun yang Akan Diproses (Contoh Alur)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="15%" class="text-center">Kelompok Akun</th>
                                <th width="25%" class="text-center">Range Kode</th>
                                <th width="30%" class="text-center">Posisi Jurnal Penutup</th>
                                <th width="30%" class="text-center">Tujuan Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center fw-semibold">Pendapatan</td>
                                <td class="text-center">400.xx.xx s/d 499.xx.xx</td>
                                <td class="text-center">Debet (nolkan akun)</td>
                                <td rowspan="2" class="text-center align-middle">
                                    <span class="badge bg-orange text-white px-3 py-1">
                                        330.01.01 SHU Tahun Berjalan
                                    </span>
                                    <br><small class="text-muted mt-1 d-block">Kredit jika Laba / Debet jika Rugi</small>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center fw-semibold">Beban Operasional</td>
                                <td class="text-center">510.xx.xx s/d 519.xx.xx</td>
                                <td class="text-center">Kredit (nolkan akun)</td>
                            </tr>
                            <tr>
                                <td class="text-center fw-semibold">Pajak</td>
                                <td class="text-center">520.xx.xx</td>
                                <td class="text-center">Kredit (nolkan akun)</td>
                                <td class="text-center">(Bagian dari perhitungan Laba Bersih)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
