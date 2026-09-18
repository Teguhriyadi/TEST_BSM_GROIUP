@extends('modules.layouts.master')
@push('title', 'Edit Jenis Pinjaman')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Jenis Pinjaman</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('jenis-pinjaman.index') }}">Jenis Pinjaman</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Jenis Pinjaman</h6>
                <a href="{{ route('jenis-pinjaman.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('jenis-pinjaman.update', $jenisPinjaman->id) }}">
                    @method('PUT')
                    @csrf
                    <div class="mb-3">
                        <label for="nama_jenis" class="form-label">Nama Jenis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_jenis') is-invalid @enderror" id="nama_jenis" name="nama_jenis" value="{{ old('nama_jenis', $jenisPinjaman->nama_jenis) }}" placeholder="Contoh: Pinjaman Usaha Mikro / Pinjaman Pendidikan">
                        @error('nama_jenis')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Syarat / ketentuan / keterangan tambahan">{{ old('keterangan', $jenisPinjaman->keterangan) }}</textarea>
                        @error('keterangan')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="maksimal_plafon" class="form-label">Maksimal Plafon</label>
                        <input type="number" class="form-control @error('maksimal_plafon') is-invalid @enderror" id="maksimal_plafon" name="maksimal_plafon" value="{{ old('maksimal_plafon', $jenisPinjaman->maksimal_plafon) }}" step="0.01" placeholder="Plafon maksimal pinjaman (Rupiah)">
                        @error('maksimal_plafon')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="bunga_tahunan" class="form-label">Bunga Tahunan (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('bunga_tahunan') is-invalid @enderror" id="bunga_tahunan" name="bunga_tahunan" value="{{ old('bunga_tahunan', $jenisPinjaman->bunga_tahunan) }}" min="0" max="100" step="0.01" placeholder="Persen bunga per tahun (0-100)">
                        @error('bunga_tahunan')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="tenor_minimal" class="form-label">Tenor Minimal</label>
                        <input type="number" class="form-control @error('tenor_minimal') is-invalid @enderror" id="tenor_minimal" name="tenor_minimal" value="{{ old('tenor_minimal', $jenisPinjaman->tenor_minimal) }}" step="0.01" placeholder="Tenor minimal (bulan)">
                        @error('tenor_minimal')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="tenor_maksimal" class="form-label">Tenor Maksimal (bulan)</label>
                        <input type="number" class="form-control @error('tenor_maksimal') is-invalid @enderror" id="tenor_maksimal" name="tenor_maksimal" value="{{ old('tenor_maksimal', $jenisPinjaman->tenor_maksimal) }}" min="0" step="1" placeholder="Tenor maksimal (bulan)">
                        @error('tenor_maksimal')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="alert alert-info small mb-4 p-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Pengaturan persyaratan dokumen untuk jenis pinjaman ini sekarang dikelola secara dinamis melalui menu
                        <strong>Master Dokumen &rarr; Setting Persyaratan Pinjaman</strong>.
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
