@extends('modules.layouts.master')
@push('title', 'Tambah Jenis Simpanan')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Jenis Simpanan</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('jenis-simpanan.index') }}">Jenis Simpanan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Tambah Jenis Simpanan</h6>
                <a href="{{ route('jenis-simpanan.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('jenis-simpanan.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="nama_jenis" class="form-label">Nama Jenis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_jenis') is-invalid @enderror" id="nama_jenis" name="nama_jenis" value="{{ old('nama_jenis') }}" placeholder="Contoh: Simpanan Pokok / Simpanan Wajib">
                        @error('nama_jenis')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Catatan tambahan / keterangan simpanan">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="setoran_minimal" class="form-label">Setoran Minimal <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('setoran_minimal') is-invalid @enderror" id="setoran_minimal" name="setoran_minimal" value="{{ old('setoran_minimal', 0) }}" step="0.01" placeholder="Minimal setoran (Rupiah)">
                        @error('setoran_minimal')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="setoran_maksimal" class="form-label">Setoran Maksimal</label>
                        <input type="number" class="form-control @error('setoran_maksimal') is-invalid @enderror" id="setoran_maksimal" name="setoran_maksimal" value="{{ old('setoran_maksimal') }}" step="0.01" placeholder="Maksimal setoran (Rupiah)">
                        @error('setoran_maksimal')
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
