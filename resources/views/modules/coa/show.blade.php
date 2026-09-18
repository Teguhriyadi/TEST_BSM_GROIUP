@extends('modules.layouts.master')
@push('title', 'Detail Akun (COA)')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Akun (COA)</h1>
        <div class="small text-muted mt-1">{{ $coa->kode_akun }} - {{ $coa->nama_akun }}</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('coa.index') }}">Daftar Akun</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('COA_UPDATE')
                    <a href="{{ route('coa.edit', $coa->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-pencil-square me-1"></i> Ubah
                    </a>
                    @endhaspermission
                    <a href="{{ route('coa.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Akun
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Kode Akun</div>
                        <div class="fw-semibold text-gray-800">{{ $coa->kode_akun }}</div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Level</div>
                        <div class="fw-semibold text-gray-800">{{ $coa->level == 1 ? 'Header (Kelompok)' : 'Detail (Posting)' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Kelompok</div>
                        <div class="fw-semibold text-gray-800">{{ ucwords(str_replace('_',' ',$coa->kelompok)) }}</div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Saldo Normal</div>
                        <div class="fw-semibold text-orange">{{ $coa->saldo_normal == 'debet' ? 'Debet' : 'Kredit' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="small text-muted">Posisi Laporan</div>
                        <div class="fw-semibold text-gray-800">{{ $coa->posisi_laporan == 'neraca' ? 'Neraca' : 'Laba Rugi' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Akun Induk</div>
                        <div class="fw-semibold text-gray-800">{{ $coa->parent ? ($coa->parent->kode_akun . ' - ' . $coa->parent->nama_akun) : 'Tidak ada (Level Tertinggi)' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $coa->cabang?->nama_cabang ?? 'Semua Cabang (Global)' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Status</div>
                        <div class="fw-semibold">
                            @if($coa->is_active == '1')
                            <span class="badge bg-orange text-white">Aktif</span>
                            @else
                            <span class="badge bg-secondary text-white">Nonaktif</span>
                            @endif
                        </div>
                    </div>
                    @if(! empty($coa->keterangan))
                    <div class="col-12">
                        <div class="small text-muted">Keterangan</div>
                        <div class="fw-medium text-gray-800">{{ $coa->keterangan }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($coa->level == 1 && $coa->children->count() > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Akun Anak (Child Detail)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th>Kode</th>
                                <th>Nama Akun</th>
                                <th>Saldo Normal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coa->children as $ch)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $ch->kode_akun }}</td>
                                <td>{{ $ch->nama_akun }}</td>
                                <td>{{ $ch->saldo_normal == 'debet' ? 'Debet' : 'Kredit' }}</td>
                                <td>
                                    @if($ch->is_active == '1')<span class="badge bg-orange text-white">Aktif</span>@else<span class="badge bg-secondary text-white">Nonaktif</span>@endif
                                </td>
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

@if($coa->jurnalDetail->count() > 0)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Riwayat Jurnal (50 Transaksi Terakhir)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th>Tanggal</th>
                                <th>No. Jurnal</th>
                                <th>Tipe</th>
                                <th>Keterangan</th>
                                <th class="text-end">Debet</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coa->jurnalDetail->take(50) as $jd)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $jd->header?->tanggal_jurnal ? \Carbon\Carbon::parse($jd->header->tanggal_jurnal)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $jd->header?->nomor_jurnal ?? '-' }}</td>
                                <td>{{ $jd->header?->tipe == 'otomatis' ? 'Otomatis' : 'Manual' }}</td>
                                <td>{{ $jd->keterangan ?? ($jd->header?->keterangan ?? '-') }}</td>
                                <td class="text-end">{{ $jd->debet > 0 ? 'Rp ' . number_format($jd->debet,0,',','.') : '-' }}</td>
                                <td class="text-end">{{ $jd->kredit > 0 ? 'Rp ' . number_format($jd->kredit,0,',','.') : '-' }}</td>
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
