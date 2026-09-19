@extends('modules.layouts.master')
@push('title', 'Edit Role')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Role</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Role</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Role & Permission</h6>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('roles.update', $role->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="kode_role" class="form-label">Kode Role <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_role') is-invalid @enderror" id="kode_role" name="kode_role" value="{{ old('kode_role', $role->kode_role) }}" placeholder="Contoh: ROL-AUD">
                            @error('kode_role')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nama_role" class="form-label">Nama Role <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_role') is-invalid @enderror" id="nama_role" name="nama_role" value="{{ old('nama_role', $role->nama_role) }}" placeholder="Contoh: Auditor Internal">
                            @error('nama_role')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select select2 @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $role->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_active', $role->is_active) == '0' ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                            @error('is_active')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                    </div>

                    @php
                        $aktif = old('permissions', $role->permissions->pluck('id')->toArray() ?? []);
                        if (!is_array($aktif)) $aktif = [];
                    @endphp

                    @haspermission('ROLE_ASSIGN_PERMISSION')
                    <div class="mb-4" id="permission">
                        <label class="form-label fw-semibold">
                            Permission Role
                            <span class="text-muted fw-normal small">(centang yang diizinkan)</span>
                        </label>
                        <div class="card border bg-light">
                            <div class="card-body p-3">
                                <div class="row">
                                    @foreach($permissions as $p)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" id="perm_{{ $p->id }}" value="{{ $p->id }}" {{ in_array($p->id, $aktif) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ $p->id }}">
                                                <span class="fw-semibold">{{ $p->kode_permission }}</span>
                                                <div class="text-muted small">{{ $p->nama_permission }}</div>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @error('permissions')
                        <div class="text-danger small mt-1"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                    @endhaspermission

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-orange">
                            <i class="bi bi-save me-1"></i> Simpan Data
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush
