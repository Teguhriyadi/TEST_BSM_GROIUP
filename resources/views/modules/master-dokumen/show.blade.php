@extends('modules.layouts.master')

@push('title')
Detail Master Dokumen | BSM Koperasi
@endpush

@push('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5 small">
        <li class="breadcrumb-item text-sm text-dark"><a class="opacity-5 text-dark" href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item text-sm text-dark"><a class="opacity-5 text-dark" href="{{ route('master-dokumen.index') }}">Master Dokumen</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Detail</li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Detail Master Dokumen</h6>
</nav>
@endpush

@push('page-modules')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible text-white small" role="alert">
            <span class="text-sm">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-orange">
                        <i class="bi bi-info-circle me-1"></i> Informasi Dokumen
                    </h6>
                    <div class="d-flex gap-2">
                        @haspermission('MASTER_DOKUMEN_UPDATE')
                            <a href="{{ route('master-dokumen.edit', $masterDokumen->id) }}" class="btn btn-sm btn-warning text-white text-xs">
                                <i class="bi bi-pencil-square me-1"></i> Ubah
                            </a>
                        @endhaspermission
                        <a href="{{ route('master-dokumen.index') }}" class="btn btn-sm btn-secondary text-xs">
                            Daftar
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-muted">Kode Dokumen</div>
                            <div class="fw-semibold text-gray-800">{{ $masterDokumen->kode_dokumen }}</div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="small text-muted">Status</div>
                            @if($masterDokumen->is_active)
                                <span class="badge bg-orange text-white px-3 py-1">AKTIF</span>
                            @else
                                <span class="badge bg-secondary text-white px-3 py-1">NONAKTIF</span>
                            @endif
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">Nama Dokumen</div>
                            <div class="fw-semibold text-gray-800 fs-6">{{ $masterDokumen->nama_dokumen }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">Deskripsi / Panduan</div>
                            <div class="text-gray-800">{{ $masterDokumen->deskripsi ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted mb-1">Format File Diperbolehkan</div>
                            @php
                                $arrFormat = $masterDokumen->getFormatListArray();
                            @endphp
                            @if(! empty($arrFormat))
                                @foreach($arrFormat as $f)
                                    <span class="badge bg-light text-gray-800 border me-1 mb-1 px-2 py-1">.{{ strtoupper($f) }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">jpg, jpeg, png, pdf (default)</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Dipakai di Jumlah Jenis Pinjaman</div>
                            <div class="fw-semibold fs-5 text-orange">{{ $masterDokumen->jenis_pinjaman_count ?? 0 }} jenis</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Total Unggahan Dokumen</div>
                            <div class="fw-semibold fs-5 text-orange">{{ $masterDokumen->pinjaman_dokumen_count ?? 0 }} berkas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-orange">
                        <i class="bi bi-list-check me-1"></i> Daftar Jenis Pinjaman yang Membutuhkan Dokumen Ini
                    </h6>
                </div>
                <div class="card-body">
                    @if(empty($daftarJenis) || count($daftarJenis) === 0)
                        <div class="text-center text-muted py-5 small">Belum ada jenis pinjaman yang mensyaratkan dokumen ini.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th>No.</th>
                                        <th>Jenis Pinjaman</th>
                                        <th class="text-center">Tenor Min</th>
                                        <th class="text-center">Tenor Maks</th>
                                        <th class="text-end">Bunga / Tahun</th>
                                        <th class="text-end">Plafon Maks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daftarJenis as $i => $j)
                                        <tr>
                                            <td class="text-center">{{ $i+1 }}</td>
                                            <td class="fw-medium">{{ $j->nama_jenis }}</td>
                                            <td class="text-center">{{ $j->tenor_minimal ? number_format($j->tenor_minimal, 0, ',', '.') . ' bln' : '-' }}</td>
                                            <td class="text-center">{{ $j->tenor_maksimal ? number_format($j->tenor_maksimal, 0, ',', '.') . ' bln' : '-' }}</td>
                                            <td class="text-end">{{ number_format($j->bunga_tahunan, 2, ',', '.') }}%</td>
                                            <td class="text-end">Rp {{ number_format($j->maksimal_plafon ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-orange">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Riwayat Unggahan Dokumen (Terakhir 50 Data)
            </h6>
        </div>
        <div class="card-body">
            @if(empty($riwayatPinjaman) || count($riwayatPinjaman) === 0)
                <div class="text-center text-muted py-5 small">Belum ada riwayat unggah untuk master dokumen ini.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>Waktu Unggah</th>
                                <th>No. Pinjaman</th>
                                <th>Anggota</th>
                                <th>Status Verifikasi</th>
                                <th>Nama File Asli</th>
                                <th>Ukuran</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($riwayatPinjaman as $i => $pd)
                                <tr>
                                    <td class="text-center">{{ $i+1 }}</td>
                                    <td>{{ $pd->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="fw-medium">
                                        @haspermission('PINJAMAN_VIEW')
                                            <a href="{{ route('pinjaman.show', $pd->pinjaman_id) }}" target="_blank">{{ $pd->pinjaman?->nomor_pinjaman ?? '-' }}</a>
                                        @else
                                            {{ $pd->pinjaman?->nomor_pinjaman ?? '-' }}
                                        @endhaspermission
                                    </td>
                                    <td>{{ $pd->pinjaman?->anggota?->nama ?? '-' }} <small class="text-muted">({{ $pd->pinjaman?->anggota?->no_anggota ?? '-' }})</small></td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $pd->warna_status }} px-2 py-1">{{ $pd->label_status }}</span>
                                    </td>
                                    <td>{{ $pd->nama_file_asli ?? '-' }}</td>
                                    <td class="text-end">{{ $pd->ukuran_file_format }}</td>
                                    <td class="text-center">
                                        @if(! empty($pd->url_file))
                                            <a href="{{ $pd->url_file }}" target="_blank" class="btn btn-xs btn-info text-white px-2 py-0">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endpush
