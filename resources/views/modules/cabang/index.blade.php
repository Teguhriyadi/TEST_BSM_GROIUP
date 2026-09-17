@extends('modules.layouts.master')
@push('title', 'Data Cabang')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => false,
    'showTanggal' => false,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Cabang</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Cabang</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Cabang</h6>
                @haspermission('CABANG_CREATE')
                <a href="{{ route('cabang.create') }}" class="btn btn-orange btn-sm">
                    <i class="bi bi-plus me-1"></i> Tambah Cabang
                </a>
                @endhaspermission
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Kode Cabang</th>
                                <th>Nama Cabang</th>
                                <th>Alamat</th>
                                <th>Telepon</th>
                                <th>Status</th>
                                @hasanypermission(['CABANG_VIEW','CABANG_UPDATE','CABANG_DELETE'])
                                <th>Aksi</th>
                                @endhasanypermission
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cabang as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->kode_cabang }}</td>
                                <td>{{ $item->nama_cabang }}</td>
                                <td>{{ $item->alamat }}</td>
                                <td>{{ $item->telepon }}</td>
                                <td>
                                    @if($item->is_active == 1)
                                    <span class="badge bg-success">Aktif</span>
                                    @else
                                    <span class="badge bg-secondary">Non Aktif</span>
                                    @endif
                                </td>
                                @hasanypermission(['CABANG_VIEW','CABANG_UPDATE','CABANG_DELETE'])
                                <td>
                                    @haspermission('CABANG_VIEW')
                                    <a href="{{ route('cabang.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @endhaspermission
                                    @haspermission('CABANG_UPDATE')
                                    <a href="{{ route('cabang.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    @endhaspermission
                                    @haspermission('CABANG_DELETE')
                                    <form class="d-inline form-delete" method="POST" action="{{ route('cabang.destroy', $item->id) }}">
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
