@extends('modules.layouts.master')
@push('title', 'Detail Permission')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Izin (Permission)</h1>
        <div class="small text-muted mt-1">{{ $permission->kode_permission }} · {{ $permission->nama_permission }}</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Manajemen Permission</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Permission</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('PERMISSION_UPDATE')
                        <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah
                        </a>
                    @endhaspermission
                    <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Permission
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Kode Permission</div>
                        <div class="fw-semibold text-gray-800"><code>{{ $permission->kode_permission }}</code></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nama Permission</div>
                        <div class="fw-semibold text-gray-800">{{ $permission->nama_permission }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Digunakan Oleh Role</div>
                        <div class="fw-semibold text-orange">{{ (int) $permission->roles_count }} role</div>
                    </div>
                    @if(! empty($permission->deskripsi))
                    <div class="col-12">
                        <div class="small text-muted">Deskripsi</div>
                        <div class="fw-medium text-gray-800">{{ $permission->deskripsi }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(($permission->roles?->count() ?? 0) > 0)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-diagram-3 me-1"></i> Role Yang Memiliki Permission Ini ({{ $permission->roles->count() }} role)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Kode Role</th>
                            <th>Nama Role</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($permission->roles->sortBy('nama_role') as $r)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $r->kode_role }}</td>
                            <td>{{ $r->nama_role }}</td>
                            <td><span class="badge bg-{{ $r->is_active ? 'success' : 'secondary' }} text-white py-1 px-3">{{ $r->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
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
