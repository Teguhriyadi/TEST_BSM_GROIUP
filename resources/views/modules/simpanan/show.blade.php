@extends('modules.layouts.master')
@push('title', 'Detail Simpanan')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Simpanan</h1>
        <div class="small text-muted mt-1">Tanggal Transaksi: @tanggal($simpanan->tanggal)</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('simpanan.index') }}">Data Simpanan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    Informasi Transaksi Simpanan
                </h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('SIMPANAN_UPDATE')
                    <a href="{{ route('simpanan.edit', $simpanan->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-pencil-square me-1"></i> Ubah
                    </a>
                    @endhaspermission
                    <a href="{{ route('simpanan.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Simpanan
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Anggota</div>
                        <div class="fw-semibold text-gray-800">{{ $simpanan->anggota->nama ?? '-' }} <small class="text-muted">({{ $simpanan->anggota->no_anggota ?? '-' }})</small></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $simpanan->cabang->nama_cabang ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jenis Simpanan</div>
                        <div class="fw-semibold text-gray-800">{{ $simpanan->jenisSimpanan->nama_jenis ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tanggal Transaksi</div>
                        <div class="fw-semibold text-gray-800">@tanggal($simpanan->tanggal)</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nominal Transaksi</div>
                        <div class="fw-semibold text-orange">Rp {{ number_format($simpanan->nominal, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Saldo Terakhir</div>
                        <div class="fw-semibold text-success">Rp {{ number_format($simpanan->saldo, 0, ',', '.') }}</div>
                    </div>
                    @if(! empty($simpanan->keterangan))
                    <div class="col-12">
                        <div class="small text-muted">Keterangan</div>
                        <div class="fw-medium text-gray-800">{{ $simpanan->keterangan }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($riwayatSimpananAnggota->count() > 0)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-clock-history me-1"></i> Riwayat Simpanan Anggota (20 Transaksi Terakhir)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-end">Saldo</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($riwayatSimpananAnggota as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>@tanggal($item->tanggal)</td>
                                <td>{{ $item->jenisSimpanan->nama_jenis ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->saldo, 0, ',', '.') }}</td>
                                <td>{{ $item->keterangan ?? '-' }}</td>
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
