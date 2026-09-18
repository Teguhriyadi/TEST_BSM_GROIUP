@extends('modules.layouts.master')
@push('title', 'Detail Jenis Simpanan')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Jenis Simpanan</h1>
        <div class="small text-muted mt-1">{{ $jenisSimpanan->nama_jenis }}</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('jenis-simpanan.index') }}">Jenis Simpanan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Jenis Simpanan</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('JENIS_SIMPANAN_UPDATE')
                        <a href="{{ route('jenis-simpanan.edit', $jenisSimpanan->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah
                        </a>
                    @endhaspermission
                    <a href="{{ route('jenis-simpanan.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Jenis Simpanan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nama Jenis</div>
                        <div class="fw-semibold text-gray-800">{{ $jenisSimpanan->nama_jenis }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jumlah Transaksi</div>
                        <div class="fw-semibold text-orange">{{ $jenisSimpanan->simpanan_count ?? 0 }} transaksi</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Batas Setoran</div>
                        <div class="fw-semibold text-gray-800">
                            Min. Rp {{ number_format($jenisSimpanan->setoran_minimal ?? 0, 0, ',', '.') }}
                            <span class="text-muted">·</span>
                            Maks. Rp {{ number_format($jenisSimpanan->setoran_maksimal ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    @if(! empty($jenisSimpanan->keterangan))
                    <div class="col-12">
                        <div class="small text-muted">Keterangan</div>
                        <div class="fw-medium text-gray-800">{{ $jenisSimpanan->keterangan }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($riwayatSimpanan->count() > 0)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock-history me-1"></i> 30 Transaksi Simpanan Terbaru</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Tanggal</th>
                            <th>Anggota</th>
                            <th>Cabang</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($riwayatSimpanan as $s)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>@tanggal($s->tanggal)</td>
                            <td>{{ $s->anggota->nama ?? '-' }} <small class="text-muted">({{ $s->anggota->no_anggota ?? '-' }})</small></td>
                            <td>{{ $s->cabang->nama_cabang ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($s->nominal, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($s->saldo, 0, ',', '.') }}</td>
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
