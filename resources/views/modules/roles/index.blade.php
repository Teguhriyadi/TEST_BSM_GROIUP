@extends('modules.layouts.master')
@push('title', 'Data Role')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => false,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Role</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Role</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Role & Permission</h6>
                @haspermission('ROLES_CREATE')
                <a href="{{ route('roles.create') }}" class="btn btn-orange btn-sm">
                    <i class="bi bi-plus me-1"></i> Tambah Role
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
                                <th>Nama Role</th>
                                <th>Jumlah User</th>
                                <th>Permission Aktif</th>
                                <th>Status</th>
                                @hasanypermission(['ROLES_VIEW','ROLES_UPDATE','ROLES_DELETE','ROLE_ASSIGN_PERMISSION'])
                                <th>Aksi</th>
                                @endhasanypermission
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->kode_role }}</td>
                                <td>{{ $item->nama_role }}</td>
                                <td class="text-center">
                                    {{ $item->users_count ?? 0 }}
                                </td>
                                <td>
                                    @if($item->permissions->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($item->permissions as $perm)
                                        <span class="badge bg-light text-dark border">{{ $perm->kode_permission }}</span>
                                        @endforeach
                                    </div>
                                    @else
                                    <span class="text-muted small">Tidak ada</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_active == 1)
                                    <span class="badge bg-success">Aktif</span>
                                    @else
                                    <span class="badge bg-secondary">Non Aktif</span>
                                    @endif
                                </td>
                                @hasanypermission(['ROLES_VIEW','ROLES_UPDATE','ROLES_DELETE','ROLE_ASSIGN_PERMISSION'])
                                <td>
                                    @haspermission('ROLES_VIEW')
                                    <a href="{{ route('roles.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @endhaspermission
                                    @haspermission('ROLE_ASSIGN_PERMISSION')
                                    <a href="{{ route('roles.edit', $item->id) }}#permission" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-shield-lock"></i> Permission
                                    </a>
                                    @endhaspermission
                                    @haspermission('ROLES_UPDATE')
                                    <a href="{{ route('roles.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    @endhaspermission
                                    @haspermission('ROLES_DELETE')
                                    <form class="d-inline form-delete" method="POST" action="{{ route('roles.destroy', $item->id) }}">
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
