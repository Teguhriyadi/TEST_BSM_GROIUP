@extends('modules.layouts.master')

@push('title')
<title>Tambah Master Dokumen | BSM Koperasi</title>
@endpush

@push('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5 small">
        <li class="breadcrumb-item text-sm text-dark"><a class="opacity-5 text-dark" href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item text-sm text-dark"><a class="opacity-5 text-dark" href="{{ route('master-dokumen.index') }}">Master Dokumen</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Tambah Baru</li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Tambah Master Dokumen</h6>
</nav>
@endpush

@push('page-modules')
<div class="container-fluid py-4">
    @if($errors->any())
        <div class="alert alert-danger text-white small">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master-dokumen.store') }}" method="POST">
        @csrf
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-orange">
                    <i class="bi bi-file-plus me-1"></i> Informasi Master Dokumen
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium small">Kode Dokumen <span class="text-danger">*</span></label>
                        <input type="text" maxlength="50" class="form-control" name="kode_dokumen" value="{{ old('kode_dokumen') }}" placeholder="Contoh: DOC-KTP">
                        <div class="form-text small text-muted">Unik, huruf kapital tanpa spasi. Digunakan sebagai referensi internal.</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-medium small">Nama Dokumen <span class="text-danger">*</span></label>
                        <input type="text" maxlength="150" class="form-control" name="nama_dokumen" value="{{ old('nama_dokumen') }}" placeholder="Contoh: Kartu Tanda Penduduk">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium small">Deskripsi / Panduan</label>
                        <textarea class="form-control" rows="2" name="deskripsi" placeholder="Contoh: KTP asli suami dan istri masih berlaku maksimal 3 bulan. Scan jelas.">{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium small">Format File Diperbolehkan</label>
                        <input type="text" class="form-control" name="format_diperbolehkan" value="{{ old('format_diperbolehkan', 'jpg,jpeg,png,pdf') }}" placeholder="Dipisah koma, contoh: jpg,jpeg,png,pdf">
                        <div class="form-text small text-muted">Kosongkan untuk default jpg,jpeg,png,pdf</div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', '1') === '1')>
                            <label class="form-check-label fw-medium" for="is_active">
                                Aktif (dapat dipilih sebagai persyaratan)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-between">
                <a href="{{ route('master-dokumen.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-chevron-left me-1"></i> Kembali
                </a>
                <button type="submit" class="btn btn-sm btn-orange">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </div>
        </div>
    </form>
</div>
@endpush
