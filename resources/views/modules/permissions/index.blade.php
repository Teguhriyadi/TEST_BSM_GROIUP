@extends('modules.layouts.master')
@push('title', 'Data Permission')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => false,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Permission</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Permission</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Permission</h6>
                @haspermission('PERMISSIONS_CREATE')
                <a href="{{ route('permissions.create') }}" class="btn btn-orange btn-sm">
                    <i class="bi bi-plus me-1"></i> Tambah Permission
                </a>
                @endhaspermission
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Permission</th>
                                <th>Deskripsi</th>
                                <th>Digunakan Role</th>
                                @hasanypermission(['PERMISSIONS_VIEW','PERMISSIONS_UPDATE','PERMISSIONS_DELETE'])
                                <th>Aksi</th>
                                @endhasanypermission
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="fw-semibold">{{ $item->kode_permission }}</span></td>
                                <td>{{ $item->nama_permission }}</td>
                                <td>{{ $item->deskripsi ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-blue text-white">{{ $item->roles_count ?? 0 }}</span>
                                </td>
                                @hasanypermission(['PERMISSIONS_VIEW','PERMISSIONS_UPDATE','PERMISSIONS_DELETE'])
                                <td>
                                    @haspermission('PERMISSIONS_VIEW')
                                    <a href="{{ route('permissions.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @endhaspermission
                                    @haspermission('PERMISSIONS_UPDATE')
                                    <a href="{{ route('permissions.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    @endhaspermission
                                    @haspermission('PERMISSIONS_DELETE')
                                    <form class="d-inline form-delete" method="POST" action="{{ route('permissions.destroy', $item->id) }}">
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
