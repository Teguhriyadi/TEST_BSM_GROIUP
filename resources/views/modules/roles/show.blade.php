@extends('modules.layouts.master')
@push('title', 'Detail Role')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Role</h1>
        <div class="small text-muted mt-1">{{ $role->nama_role }} ({{ $role->kode_role }})</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Manajemen Role</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Role</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('ROLE_UPDATE')
                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah Role
                        </a>
                    @endhaspermission
                    @haspermission('ROLE_ASSIGN_PERMISSION')
                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-shield-check me-1"></i> Atur Permission
                        </a>
                    @endhaspermission
                    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Role
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Kode Role</div>
                        <div class="fw-semibold text-gray-800">{{ $role->kode_role }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nama Role</div>
                        <div class="fw-semibold text-gray-800">{{ $role->nama_role }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Status</div>
                        <div>
                            @if($role->is_active)
                                <span class="badge bg-orange text-white py-1 px-3">AKTIF</span>
                            @else
                                <span class="badge bg-secondary text-white py-1 px-3">TIDAK AKTIF</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jumlah Permission</div>
                        <div class="fw-semibold text-primary">{{ $role->permissions ? $role->permissions->count() : 0 }} izin</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jumlah User Memakai</div>
                        <div class="fw-semibold text-orange">{{ (int) $role->users_count }} pengguna</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(($role->permissions?->count() ?? 0) > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-shield-lock me-1"></i> Daftar Izin (Permission) Aktif Role Ini</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($role->permissions->sortBy('kode_permission') as $p)
                        <span class="badge bg-light text-dark border px-3 py-2">
                            <strong>{{ $p->kode_permission }}</strong>
                            <small class="text-muted">· {{ $p->nama_permission }}</small>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(($role->users?->count() ?? 0) > 0)
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-people me-1"></i> Daftar User Yang Menggunakan Role Ini</h6>
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
                        @foreach($role->users->sortBy('nama') as $u)
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

@endpush
