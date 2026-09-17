@extends('modules.layouts.master')
@push('title', 'Edit Pengguna')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Pengguna</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Pengguna</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Pengguna</h6>
                <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Pengguna <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $user->nama) }}" placeholder="Contoh: Budi Santoso">
                        @error('nama')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Contoh: nama@koperasi.test">
                        @error('email')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                        @error('password')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                        <div class="form-text text-muted">Kosongkan jika password tidak diubah.</div>
                    </div>
                    <div class="mb-3">
                        <label for="cabang_id" class="form-label">Cabang <span class="text-danger">*</span></label>
                        <select class="form-select select2 @error('cabang_id') is-invalid @enderror" id="cabang_id" name="cabang_id">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabang as $c)
                            <option value="{{ $c->id }}" {{ old('cabang_id', $user->cabang_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->kode_cabang }} - {{ $c->nama_cabang }}
                            </option>
                            @endforeach
                        </select>
                        @error('cabang_id')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select select2 @error('role_id') is-invalid @enderror" id="role_id" name="role_id">
                            <option value="">-- Pilih Role --</option>
                            @foreach($role as $r)
                            <option value="{{ $r->id }}" {{ old('role_id', $user->role_id) == $r->id ? 'selected' : '' }}>
                                {{ $r->kode_role }} - {{ $r->nama_role }}
                            </option>
                            @endforeach
                        </select>
                        @error('role_id')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nomor_hp" class="form-label">Nomor HP</label>
                        <input type="text" class="form-control @error('nomor_hp') is-invalid @enderror" id="nomor_hp" name="nomor_hp" value="{{ old('nomor_hp', $user->nomor_hp) }}" placeholder="Contoh: 081234567890">
                        @error('nomor_hp')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="is_active" class="form-label">Status Aktif</label>
                        <select class="form-select select2 @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                            <option value="1" {{ old('is_active', $user->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active', $user->is_active) == '0' ? 'selected' : '' }}>Non Aktif</option>
                        </select>
                        @error('is_active')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
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
