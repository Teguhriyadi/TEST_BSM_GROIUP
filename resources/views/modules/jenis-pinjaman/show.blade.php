@extends('modules.layouts.master')
@push('title', 'Detail Jenis Pinjaman')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Jenis Pinjaman</h1>
        <div class="small text-muted mt-1">{{ $jenisPinjaman->nama_jenis }}</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('jenis-pinjaman.index') }}">Jenis Pinjaman</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Jenis Pinjaman</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('JENIS_PINJAMAN_UPDATE')
                        <a href="{{ route('jenis-pinjaman.edit', $jenisPinjaman->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah
                        </a>
                    @endhaspermission
                    <a href="{{ route('jenis-pinjaman.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Jenis Pinjaman
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nama Jenis</div>
                        <div class="fw-semibold text-gray-800">{{ $jenisPinjaman->nama_jenis }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Total Pengajuan Pinjaman</div>
                        <div class="fw-semibold text-orange">{{ $jenisPinjaman->pinjaman_count ?? 0 }} pengajuan</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Bunga / Tahun</div>
                        <div class="fw-semibold text-gray-800">{{ $jenisPinjaman->bunga_tahunan }} %</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Maksimal Plafon</div>
                        <div class="fw-semibold text-gray-800">Rp {{ number_format($jenisPinjaman->maksimal_plafon, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tenor Minimal</div>
                        <div class="fw-semibold text-gray-800">{{ $jenisPinjaman->tenor_minimal }} bulan</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tenor Maksimal</div>
                        <div class="fw-semibold text-gray-800">{{ $jenisPinjaman->tenor_maksimal }} bulan</div>
                    </div>
                    @if(! empty($jenisPinjaman->keterangan))
                    <div class="col-12">
                        <div class="small text-muted">Keterangan</div>
                        <div class="fw-medium text-gray-800">{{ $jenisPinjaman->keterangan }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(($jenisPinjaman->dokumenPersyaratanWajib?->count() ?? 0) > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-file-earmark-check me-1"></i> Dokumen Persyaratan Wajib</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Kode Dokumen</th>
                            <th>Nama Dokumen</th>
                            <th>Deskripsi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($jenisPinjaman->dokumenPersyaratanWajib->sortBy('pivot.urutan') as $d)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $d->kode_dokumen ?? '-' }}</td>
                            <td>{{ $d->nama_dokumen ?? '-' }}</td>
                            <td>{{ $d->deskripsi ?? '-' }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($daftarPinjaman->count() > 0)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-list-check me-1"></i> 25 Pengajuan Pinjaman Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>No Pinjaman</th>
                            <th>Anggota</th>
                            <th>Cabang</th>
                            <th class="text-end">Jumlah</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($daftarPinjaman as $p)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $p->no_pinjaman }}</td>
                            <td>{{ $p->anggota->nama ?? '-' }} <small class="text-muted">({{ $p->anggota->no_anggota ?? '-' }})</small></td>
                            <td>{{ $p->cabang->nama_cabang ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($p->jumlah_pinjaman, 0, ',', '.') }}</td>
                            <td><span class="badge bg-primary text-white py-1 px-3">{{ ucwords(str_replace('_',' ',$p->status)) }}</span></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endpush
