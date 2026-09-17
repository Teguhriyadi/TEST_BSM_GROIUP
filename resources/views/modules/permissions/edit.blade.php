@extends('modules.layouts.master')
@push('title', 'Edit Permission')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Permission</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permission</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Permission</h6>
                <a href="{{ route('permissions.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('permissions.update', $permission->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="kode_permission" class="form-label">Kode Permission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kode_permission') is-invalid @enderror" id="kode_permission" name="kode_permission" value="{{ old('kode_permission', $permission->kode_permission) }}" placeholder="Contoh: CABANG_CREATE">
                        @error('kode_permission')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nama_permission" class="form-label">Nama Permission <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_permission') is-invalid @enderror" id="nama_permission" name="nama_permission" value="{{ old('nama_permission', $permission->nama_permission) }}" placeholder="Contoh: Tambah Data Cabang">
                        @error('nama_permission')
                        <div class="invalid-feedback"><small>{{ $message }}</small></div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3" placeholder="Penjelasan singkat tentang permission ini">{{ old('deskripsi', $permission->deskripsi) }}</textarea>
                        @error('deskripsi')
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
