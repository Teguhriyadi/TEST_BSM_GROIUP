@extends('modules.layouts.master')
@push('title', 'Detail Angsuran Pinjaman')
@push('page-modules')

@php
    $statusColor = 'secondary';
    if ($angsuran->status === 'lunas') $statusColor = 'success';
    elseif ($angsuran->status === 'sebagian_lunas') $statusColor = 'warning';
    elseif (\Carbon\Carbon::parse($angsuran->tanggal_jatuh_tempo)->isPast() && $angsuran->status !== 'lunas') $statusColor = 'danger';
@endphp

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Angsuran Pinjaman</h1>
        <div class="small text-muted mt-1">Angsuran ke-{{ $angsuran->angsuran_ke }} · Jatuh Tempo: @tanggal($angsuran->tanggal_jatuh_tempo)</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('angsuran.index') }}">Angsuran</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Tagihan Angsuran</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('ANGSURAN_UPDATE')
                        <a href="{{ route('angsuran.edit', $angsuran->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah
                        </a>
                    @endhaspermission
                    @haspermission('PEMBAYARAN_CREATE')
                        <a href="{{ route('pembayaran-angsuran.create') }}?angsuran_id={{ $angsuran->id }}" class="btn btn-sm btn-orange text-white">
                            <i class="bi bi-cash-coin me-1"></i> Input Pembayaran
                        </a>
                    @endhaspermission
                    <a href="{{ route('angsuran.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Angsuran
                    </a>
                    @haspermission('PINJAMAN_VIEW')
                        @if($angsuran->pinjaman)
                        <a href="{{ route('pinjaman.show', $angsuran->pinjaman_id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Pinjaman
                        </a>
                        @endif
                    @endhaspermission
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Status Angsuran</div>
                        <div class="mt-1">
                            <span class="badge bg-{{ $statusColor }} text-white py-1 px-4">{{ ucwords(str_replace('_',' ',$angsuran->status)) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Angsuran Ke</div>
                        <div class="fw-semibold text-gray-800">{{ $angsuran->angsuran_ke }} / {{ (int) $angsuran->pinjaman?->tenor }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tanggal Jatuh Tempo</div>
                        <div class="fw-semibold text-gray-800">@tanggal($angsuran->tanggal_jatuh_tempo)</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nominal Angsuran Pokok</div>
                        <div class="fw-semibold text-orange">Rp {{ number_format($angsuran->nominal, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Denda Saat Ini</div>
                        <div class="fw-semibold text-danger">Rp {{ number_format($angsuran->denda ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Total Harus Dibayar</div>
                        <div class="fw-bold text-primary h5 mb-0">Rp {{ number_format($angsuran->total_bayar, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($angsuran->pinjaman)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-info-circle me-1"></i> Informasi Pinjaman & Anggota</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">No Pinjaman</div>
                        <div class="fw-semibold text-gray-800">{{ $angsuran->pinjaman->no_pinjaman }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tanggal Pengajuan</div>
                        <div class="fw-semibold text-gray-800">@tanggal($angsuran->pinjaman->tgl_pengajuan)</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Status Pinjaman</div>
                        <div><span class="badge bg-primary text-white py-1 px-3">{{ ucwords(str_replace('_',' ',$angsuran->pinjaman->status)) }}</span></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nama Anggota</div>
                        <div class="fw-semibold text-gray-800">{{ $angsuran->pinjaman->anggota?->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">No Anggota</div>
                        <div class="fw-semibold text-gray-800">{{ $angsuran->pinjaman->anggota?->no_anggota ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $angsuran->pinjaman->cabang?->nama_cabang ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jumlah Pinjaman</div>
                        <div class="fw-semibold text-orange">Rp {{ number_format($angsuran->pinjaman->jumlah_pinjaman, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tenor</div>
                        <div class="fw-semibold text-gray-800">{{ (int) $angsuran->pinjaman->tenor }} bulan</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Bunga</div>
                        <div class="fw-semibold text-gray-800">{{ $angsuran->pinjaman->bunga }} % / tahun</div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-4">
                        <div class="border rounded p-3 bg-light">
                            <div class="small text-muted">Jumlah Pembayaran</div>
                            <div class="h5 mb-0 text-primary fw-bold">{{ (int) $angsuran->pembayaran_count }} x</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="border rounded p-3 bg-light">
                            <div class="small text-muted">Total Yang Sudah Dibayar</div>
                            <div class="h5 mb-0 text-success fw-bold">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 bg-light">
                            <div class="small text-muted">Sisa Kurang Bayar</div>
                            <div class="h5 mb-0 fw-bold {{ ($angsuran->total_bayar - $totalDibayar) > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format(max(0, $angsuran->total_bayar - $totalDibayar), 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($angsuran->pembayaran && $angsuran->pembayaran->count() > 0)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-receipt-cutoff me-1"></i> Riwayat Pembayaran</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Tanggal Bayar</th>
                            <th>Metode</th>
                            <th class="text-end">Jumlah Bayar</th>
                            <th>Dibayar Oleh</th>
                            <th>Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($angsuran->pembayaran->sortByDesc('tanggal_bayar') as $byr)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>@tanggal($byr->tanggal_bayar)</td>
                            <td>{{ $byr->metode_pembayaran ? ucwords(str_replace('_',' ',$byr->metode_pembayaran)) : '-' }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($byr->jumlah_bayar, 0, ',', '.') }}</td>
                            <td>{{ $byr->dibayarOleh?->nama ?? '-' }}</td>
                            <td>{{ $byr->keterangan ?? '-' }}</td>
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
