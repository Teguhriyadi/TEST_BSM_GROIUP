@extends('modules.layouts.master')
@push('title', 'Detail Jurnal Umum')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Jurnal Umum</h1>
        <div class="small text-muted mt-1">{{ $header->nomor_jurnal }} · {{ \Carbon\Carbon::parse($header->tanggal_jurnal)->format('d F Y') }}</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('jurnal-umum.index') }}">Jurnal Umum</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Jurnal</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('JURNAL_UPDATE')
                        @if($header->status == 'draf' && $header->tipe != 'otomatis')
                        <a href="{{ route('jurnal-umum.edit', $header->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah
                        </a>
                        @endif
                    @endhaspermission
                    @haspermission('JURNAL_POSTING')
                        @if($header->status == 'draf')
                        <form class="d-inline form-posting" method="POST" action="{{ route('jurnal-umum.posting', $header->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-orange btn-posting">
                                <i class="bi bi-check2-square me-1"></i> Posting Jurnal
                            </button>
                        </form>
                        @endif
                    @endhaspermission
                    @haspermission('JURNAL_DELETE')
                        @if($header->status != 'diposting' && $header->tipe != 'otomatis')
                        <form class="d-inline form-delete" method="POST" action="{{ route('jurnal-umum.destroy', $header->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                <i class="bi bi-trash me-1"></i> Hapus Draf
                            </button>
                        </form>
                        @endif
                    @endhaspermission
                    <a href="{{ route('jurnal-umum.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Jurnal
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Nomor Jurnal</div>
                        <div class="fw-semibold text-gray-800">{{ $header->nomor_jurnal }}</div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Tipe Jurnal</div>
                        <div class="fw-semibold text-gray-800">{{ $header->tipe == 'otomatis' ? 'Otomatis (Dari Transaksi)' : 'Manual' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Tanggal Jurnal</div>
                        <div class="fw-semibold text-gray-800">{{ \Carbon\Carbon::parse($header->tanggal_jurnal)->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Status</div>
                        <div class="fw-semibold">
                            @if($header->status == 'draf')<span class="badge bg-warning text-dark">Draf</span>
                            @elseif($header->status == 'diposting')<span class="badge bg-orange text-white">Diposting</span>
                            @else<span class="badge bg-secondary text-white">Dibatalkan</span>@endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $header->cabang?->nama_cabang ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Dibuat Oleh</div>
                        <div class="fw-semibold text-gray-800">{{ $header->dibuatOleh?->nama ?? '-' }} <small class="text-muted">({{ $header->created_at ? \Carbon\Carbon::parse($header->created_at)->format('d/m/Y H:i') : '' }})</small></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Diposting Oleh</div>
                        <div class="fw-semibold text-gray-800">
                            @if($header->diposting_at)
                                {{ $header->dipostingOleh?->nama ?? '-' }} <small class="text-muted">({{ \Carbon\Carbon::parse($header->diposting_at)->format('d/m/Y H:i') }})</small>
                            @else <span class="text-muted">-</span> @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Keterangan</div>
                        <div class="fw-medium text-gray-800">{{ $header->keterangan ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Detail Baris Jurnal</h6>
            </div>
            <div class="card-body p-0">
                @if($header->details->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">Kode Akun</th>
                                <th width="25%">Nama Akun</th>
                                <th width="25%">Keterangan Baris</th>
                                <th width="15%" class="text-end">Debet</th>
                                <th width="15%" class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $td = 0; $tk = 0;
                            @endphp
                            @foreach($header->details as $d)
                            @php
                                $td += (float) $d->debet;
                                $tk += (float) $d->kredit;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $d->coa?->kode_akun ?? '-' }}</td>
                                <td>{{ $d->coa?->nama_akun ?? '-' }}</td>
                                <td>{{ $d->keterangan ?? '-' }}</td>
                                <td class="text-end">{{ $d->debet > 0 ? 'Rp ' . number_format($d->debet,0,',','.') : '-' }}</td>
                                <td class="text-end">{{ $d->kredit > 0 ? 'Rp ' . number_format($d->kredit,0,',','.') : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-semibold">
                            <tr>
                                <td colspan="4" class="text-end">TOTAL</td>
                                <td class="text-end text-orange">Rp {{ number_format($td, 0, ',', '.') }}</td>
                                <td class="text-end text-orange">Rp {{ number_format($tk, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">STATUS</td>
                                <td colspan="2" class="text-center">
                                    @if(abs($td - $tk) < 0.01)
                                    <span class="badge bg-orange text-white">BALANCE</span>
                                    @else
                                    <span class="badge bg-danger text-white">TIDAK BALANCE · SELISIH Rp {{ number_format(abs($td - $tk), 0, ',', '.') }}</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endpush
