@extends('modules.layouts.master')
@push('title', 'Detail Cabang')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Cabang</h1>
        <div class="small text-muted mt-1">Kode Cabang: {{ $cabang->kode_cabang }}</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cabang.index') }}">Data Cabang</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Umum Cabang</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('CABANG_UPDATE')
                        <a href="{{ route('cabang.edit', $cabang->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah
                        </a>
                    @endhaspermission
                    <a href="{{ route('cabang.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Cabang
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Kode Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $cabang->kode_cabang }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nama Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $cabang->nama_cabang }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Status</div>
                        <div>
                            @if($cabang->is_active)
                                <span class="badge bg-orange text-white py-1 px-3">AKTIF</span>
                            @else
                                <span class="badge bg-secondary text-white py-1 px-3">TIDAK AKTIF</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Telepon</div>
                        <div class="fw-semibold text-gray-800">{{ $cabang->telepon ?? '-' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Alamat</div>
                        <div class="fw-medium text-gray-800">{{ $cabang->alamat ?? '-' }}</div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="row g-3 text-center">
                    <div class="col-6 col-lg-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="small text-muted">Anggota</div>
                            <div class="h4 mb-0 text-primary fw-bold">{{ $cabang->anggota_count ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="small text-muted">User Aktif</div>
                            <div class="h4 mb-0 text-success fw-bold">{{ $cabang->users_count ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="small text-muted">Transaksi Simpanan</div>
                            <div class="h4 mb-0 text-orange fw-bold">{{ $cabang->simpanan_count ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded p-3 bg-light">
                            <div class="small text-muted">Pengajuan Pinjaman</div>
                            <div class="h4 mb-0 text-info fw-bold">{{ $cabang->pinjaman_count ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($listAnggota->count() > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-people me-1"></i> Daftar Anggota (20 Terbaru)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>No Anggota</th>
                            <th>Nama</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($listAnggota as $a)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $a->no_anggota }}</td>
                            <td>{{ $a->nama }}</td>
                            <td><span class="badge bg-{{ $a->is_active ? 'success' : 'secondary' }} text-white py-1 px-3">{{ $a->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
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

@if($listUsers->count() > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-person-badge me-1"></i> Daftar User (20 Terbaru)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No HP</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($listUsers as $u)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $u->nama }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->nomor_hp ?? '-' }}</td>
                            <td><span class="badge bg-{{ $u->is_active ? 'success' : 'secondary' }} text-white py-1 px-3">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
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

@if($listPinjaman->count() > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-wallet2 me-1"></i> Pengajuan Pinjaman (20 Terbaru)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>No Pinjaman</th>
                            <th>Anggota</th>
                            <th>Jenis</th>
                            <th class="text-end">Jumlah</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($listPinjaman as $p)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $p->no_pinjaman }}</td>
                            <td>{{ $p->anggota->nama ?? '-' }} <small class="text-muted">({{ $p->anggota->no_anggota ?? '-' }})</small></td>
                            <td>{{ $p->jenisPinjaman->nama_jenis ?? '-' }}</td>
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
