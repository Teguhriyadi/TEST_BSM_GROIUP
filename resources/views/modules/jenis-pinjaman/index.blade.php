@extends('modules.layouts.master')
@push('title', 'Jenis Pinjaman')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => false,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Jenis Pinjaman</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Jenis Pinjaman</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Jenis Pinjaman</h6>
                @haspermission('JENIS_PINJAMAN_CREATE')
                <a href="{{ route('jenis-pinjaman.create') }}" class="btn btn-orange btn-sm">
                    <i class="bi bi-plus me-1"></i> Tambah
                </a>
                @endhaspermission
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Nama Jenis</th>
                                <th>Keterangan</th>
                                <th>Plafon Maks</th>
                                <th>Bunga Tahunan</th>
                                <th>Tenor Min</th>
                                <th>Tenor Maks</th>
                                @hasanypermission(['JENIS_PINJAMAN_VIEW','JENIS_PINJAMAN_UPDATE','JENIS_PINJAMAN_DELETE','JENIS_PINJAMAN_PERSYARATAN_DOKUMEN'])
                                <th>Aksi</th>
                                @endhasanypermission
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jenisPinjaman as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama_jenis }}</td>
                                <td>{{ $item->keterangan }}</td>
                                <td>Rp {{ number_format($item->maksimal_plafon ?? 0, 0, ',', '.') }}</td>
                                <td>{{ $item->bunga_tahunan ?? 0 }}%</td>
                                <td>{{ $item->tenor_minimal ?? '-' }}</td>
                                <td>{{ $item->tenor_maksimal ?? '-' }} bln</td>
                                @hasanypermission(['JENIS_PINJAMAN_VIEW','JENIS_PINJAMAN_UPDATE','JENIS_PINJAMAN_DELETE','JENIS_PINJAMAN_PERSYARATAN_DOKUMEN'])
                                <td>
                                    @haspermission('JENIS_PINJAMAN_VIEW')
                                    <a href="{{ route('jenis-pinjaman.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @endhaspermission
                                    @haspermission('JENIS_PINJAMAN_UPDATE')
                                    <a href="{{ route('jenis-pinjaman.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    @endhaspermission
                                    @haspermission('JENIS_PINJAMAN_PERSYARATAN_DOKUMEN')
                                    <a href="{{ route('jenis-pinjaman.edit', $item->id) }}#persyaratan-dokumen" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-file-earmark-text"></i> Persyaratan
                                    </a>
                                    @endhaspermission
                                    @haspermission('JENIS_PINJAMAN_DELETE')
                                    <form class="d-inline form-delete" method="POST" action="{{ route('jenis-pinjaman.destroy', $item->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                    @endhaspermission
                                </td>
                                @endhasanypermission
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
