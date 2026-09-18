@extends('modules.layouts.master')
@push('title', 'Detail Pengguna (User)')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Pengguna (User)</h1>
        <div class="small text-muted mt-1">{{ $user->nama }} · {{ $user->email }}</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Data User</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Informasi User</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @haspermission('USERS_UPDATE')
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-pencil-square me-1"></i> Ubah
                        </a>
                    @endhaspermission
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nama Lengkap</div>
                        <div class="fw-semibold text-gray-800">{{ $user->nama }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Email</div>
                        <div class="fw-semibold text-gray-800">{{ $user->email }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Nomor HP</div>
                        <div class="fw-semibold text-gray-800">{{ $user->nomor_hp ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Role</div>
                        <div class="fw-semibold text-orange">{{ $user->role?->nama_role ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $user->cabang?->nama_cabang ?? 'Semua Cabang' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Status</div>
                        <div>
                            @if($user->is_active)
                                <span class="badge bg-orange text-white py-1 px-3">AKTIF</span>
                            @else
                                <span class="badge bg-secondary text-white py-1 px-3">TIDAK AKTIF</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Force Change Password</div>
                        <div>
                            @if($user->force_change_password)
                                <span class="badge bg-warning text-dark py-1 px-3">WAJIB GANTI</span>
                            @else
                                <span class="badge bg-success text-white py-1 px-3">TIDAK</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Password Terakhir Diubah</div>
                        <div class="fw-semibold text-gray-800">
                            @if(! empty($user->password_changed_at))
                                @tanggal($user->password_changed_at)
                            @else
                                <span class="text-muted">Belum pernah</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Login Terakhir</div>
                        <div class="fw-semibold text-gray-800">
                            @if(! empty($user->last_login_at))
                                {{ \Carbon\Carbon::parse($user->last_login_at)->translatedFormat('d M Y, H:i') }}
                            @else
                                <span class="text-muted">Belum pernah login</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Dibuat Tanggal</div>
                        <div class="fw-semibold text-gray-800">@tanggal($user->created_at)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($user->role && ($user->role->permissions?->count() ?? 0) > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-shield-lock me-1"></i> Izin (Permission) Role {{ $user->role->nama_role }} ({{ $user->role->permissions->count() }} izin)</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($user->role->permissions->sortBy('kode_permission') as $perm)
                        <span class="badge bg-light text-dark border px-3 py-2">{{ $perm->kode_permission }} <small class="text-muted">· {{ $perm->nama_permission }}</small></span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($aktivitasTerakhir->count() > 0)
<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock-history me-1"></i> Aktivitas Terakhir User (30 Aktivitas)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="18%">Waktu</th>
                            <th width="18%">Aksi</th>
                            <th>Detail</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($aktivitasTerakhir as $log)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i') }}</td>
                            <td><span class="badge bg-primary text-white py-1 px-2">{{ $log->aksi }}</span></td>
                            <td class="small">{{ $log->deskripsi }}</td>
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
