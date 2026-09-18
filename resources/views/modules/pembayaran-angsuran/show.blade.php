@extends('modules.layouts.master')
@push('title', 'Detail Pembayaran Angsuran')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Pembayaran Angsuran</h1>
        <div class="small text-muted mt-1">Tanggal Bayar: @tanggal($pembayaran->tanggal_bayar)</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pembayaran-angsuran.index') }}">Pembayaran Angsuran</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Detail Pembayaran</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('PEMBAYARAN_UPDATE')
                        <a href="{{ route('pembayaran-angsuran.edit', $pembayaran->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah
                        </a>
                    @endhaspermission
                    <a href="{{ route('pembayaran-angsuran.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Pembayaran
                    </a>
                    @haspermission('ANGSURAN_VIEW')
                        @if($pembayaran->angsuran)
                        <a href="{{ route('angsuran.show', $pembayaran->angsuran_id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Tagihan
                        </a>
                        @endif
                    @endhaspermission
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tanggal Bayar</div>
                        <div class="fw-semibold text-gray-800">@tanggal($pembayaran->tanggal_bayar)</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Metode Pembayaran</div>
                        <div class="fw-semibold text-gray-800">{{ $pembayaran->metode_pembayaran ? ucwords(str_replace('_',' ',$pembayaran->metode_pembayaran)) : '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jumlah Yang Dibayar</div>
                        <div class="fw-bold text-orange h5 mb-0">Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Dibayar Oleh (Petugas)</div>
                        <div class="fw-semibold text-gray-800">{{ $pembayaran->dibayarOleh?->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Bukti Pembayaran</div>
                        <div>
                            @if(! empty($pembayaran->bukti_pembayaran))
                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($pembayaran->bukti_pembayaran) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bi bi-file-image me-1"></i> Lihat Bukti
                                </a>
                            @else
                                <span class="text-muted small">Tidak ada bukti lampiran.</span>
                            @endif
                        </div>
                    </div>
                    @if(! empty($pembayaran->keterangan))
                    <div class="col-12">
                        <div class="small text-muted">Keterangan</div>
                        <div class="fw-medium text-gray-800">{{ $pembayaran->keterangan }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($pembayaran->angsuran && $pembayaran->angsuran->pinjaman)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-card-list me-1"></i> Informasi Tagihan & Pinjaman</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Angsuran Ke</div>
                        <div class="fw-semibold text-gray-800">{{ $pembayaran->angsuran->angsuran_ke }} / {{ (int) $pembayaran->angsuran->pinjaman?->tenor }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jatuh Tempo</div>
                        <div class="fw-semibold text-gray-800">@tanggal($pembayaran->angsuran->tanggal_jatuh_tempo)</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Status Tagihan</div>
                        <div><span class="badge bg-{{ $pembayaran->angsuran->status === 'lunas' ? 'success' : ($pembayaran->angsuran->status === 'sebagian_lunas' ? 'warning' : 'primary') }} text-white py-1 px-3">{{ ucwords(str_replace('_',' ',$pembayaran->angsuran->status)) }}</span></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">No Pinjaman</div>
                        <div class="fw-semibold text-gray-800">{{ $pembayaran->angsuran->pinjaman->no_pinjaman }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nama Anggota</div>
                        <div class="fw-semibold text-gray-800">{{ $pembayaran->angsuran->pinjaman->anggota?->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $pembayaran->angsuran->pinjaman->cabang?->nama_cabang ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Angsuran Pokok</div>
                        <div class="fw-semibold text-gray-800">Rp {{ number_format($pembayaran->angsuran->nominal, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Denda</div>
                        <div class="fw-semibold text-danger">Rp {{ number_format($pembayaran->angsuran->denda ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Total Tagihan</div>
                        <div class="fw-semibold text-orange">Rp {{ number_format($pembayaran->angsuran->total_bayar, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endpush
