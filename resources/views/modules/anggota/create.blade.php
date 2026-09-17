@extends('modules.layouts.master')
@push('title', 'Tambah Anggota')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Anggota</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Data Anggota</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Tambah Data Anggota</h6>
                <a href="{{ route('anggota.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('anggota.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="cabang_id" class="form-label">Cabang</label>
                        <select class="form-select select2 @error('cabang_id') is-invalid @enderror" id="cabang_id" name="cabang_id">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabang as $c)
                            <option value="{{ $c->id }}" {{ old('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                        @error('cabang_id')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="no_anggota" class="form-label">No Anggota <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('no_anggota') is-invalid @enderror" id="no_anggota" name="no_anggota" value="{{ old('no_anggota') }}" maxlength="30" placeholder="Contoh: ANGG-000001">
                        @error('no_anggota')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}" maxlength="16" placeholder="Masukkan 16 digit NIK sesuai KTP">
                        @error('nik')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" maxlength="100" placeholder="Masukkan nama lengkap anggota">
                        @error('nama')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <div class="d-flex gap-4">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" {{ old('jenis_kelamin', 'L') == 'L' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jk_l">Laki-laki</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jk_p">Perempuan</label>
                            </div>
                        </div>
                        @error('jenis_kelamin')
                        <div class="invalid-feedback d-block">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap sesuai KTP">{{ old('alamat') }}</textarea>
                        @error('alamat')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control @error('tgl_lahir') is-invalid @enderror" id="tgl_lahir" name="tgl_lahir" value="{{ old('tgl_lahir') }}">
                        @error('tgl_lahir')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">No HP</label>
                        <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" maxlength="15" placeholder="Contoh: 081234567890">
                        @error('no_hp')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (untuk Akun Login Anggota)</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" maxlength="100" placeholder="Email anggota, jika diisi maka akan otomatis dibuatkan akun login">
                        <div class="form-text text-muted small">
                            Jika kolom ini diisi, sistem otomatis membuatkan akun login dengan password default: <b>password</b> (wajib diganti saat login pertama).
                            Jika dikosongkan, tidak dibuatkan akun login (data nasabah biasa saja).
                        </div>
                        @error('email')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="status_anggota" class="form-label">Tanggal Masuk</label>
                        <input type="date" class="form-control @error('status_anggota') is-invalid @enderror" id="status_anggota" name="status_anggota" value="{{ old('status_anggota') }}">
                        @error('status_anggota')
                        <div class="invalid-feedback">
                            <small>{{ $message }}</small>
                        </div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select select2 @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Non Aktif</option>
                        </select>
                        @error('status')
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
