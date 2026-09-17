@extends('modules.layouts.master')
@push('title', 'Edit Cabang')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Cabang</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cabang.index') }}">Data Cabang</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Data Cabang</h6>
                <a href="{{ route('cabang.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('cabang.update', $cabang->id) }}">
                    @method('PUT')
                    @csrf
                    <div class="mb-3">
                        <label for="kode_cabang" class="form-label">Kode Cabang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kode_cabang') is-invalid @enderror" id="kode_cabang" name="kode_cabang" value="{{ old('kode_cabang', $cabang->kode_cabang) }}" placeholder="Contoh: KCP-001">
                        @error('kode_cabang')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nama_cabang" class="form-label">Nama Cabang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_cabang') is-invalid @enderror" id="nama_cabang" name="nama_cabang" value="{{ old('nama_cabang', $cabang->nama_cabang) }}" placeholder="Nama kantor cabang">
                        @error('nama_cabang')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap cabang">{{ old('alamat', $cabang->alamat) }}</textarea>
                        @error('alamat')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon', $cabang->telepon) }}" placeholder="Contoh: 021-1234567">
                        @error('telepon')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="is_active" class="form-label">Status Aktif</label>
                        <select class="form-select select2 @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                            <option value="1" {{ old('is_active', $cabang->is_active) == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active', $cabang->is_active) == '0' ? 'selected' : '' }}>Non Aktif</option>
                        </select>
                        @error('is_active')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
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
