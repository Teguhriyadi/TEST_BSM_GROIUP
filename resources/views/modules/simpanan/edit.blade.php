@extends('modules.layouts.master')
@push('title', 'Edit Simpanan')
@push('page-modules')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Simpanan</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('simpanan.update', $simpanan->id) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="anggota_id" class="form-label">Anggota <span class="text-danger">*</span></label>
                    <select name="anggota_id" id="anggota_id" class="form-select select2 @error('anggota_id') is-invalid @enderror">
                        <option value="">-- Pilih Anggota --</option>
                        @foreach($anggota as $a)
                        <option value="{{ $a->id }}" {{ old('anggota_id', $simpanan->anggota_id) == $a->id ? 'selected' : '' }}>
                            {{ $a->no_anggota }} - {{ $a->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('anggota_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="cabang_id" class="form-label">Cabang <span class="text-danger">*</span></label>
                    <select name="cabang_id" id="cabang_id" class="form-select select2 @error('cabang_id') is-invalid @enderror">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($cabang as $c)
                        <option value="{{ $c->id }}" {{ old('cabang_id', $simpanan->cabang_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_cabang }}
                        </option>
                        @endforeach
                    </select>
                    @error('cabang_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jenis_simpanan_id" class="form-label">Jenis Simpanan <span class="text-danger">*</span></label>
                    <select name="jenis_simpanan_id" id="jenis_simpanan_id" class="form-select select2 @error('jenis_simpanan_id') is-invalid @enderror">
                        <option value="">-- Pilih Jenis Simpanan --</option>
                        @foreach($jenisSimpanan as $js)
                        <option value="{{ $js->id }}" {{ old('jenis_simpanan_id', $simpanan->jenis_simpanan_id) == $js->id ? 'selected' : '' }}>
                            {{ $js->nama_jenis }}
                        </option>
                        @endforeach
                    </select>
                    @error('jenis_simpanan_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', is_object($simpanan->tanggal) ? $simpanan->tanggal->format('Y-m-d') : date('Y-m-d', strtotime($simpanan->tanggal))) }}">
                    @error('tanggal')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nominal" class="form-label">Nominal <span class="text-danger">*</span></label>
                    <input type="number" name="nominal" id="nominal" step="0.01" min="0" class="form-control @error('nominal') is-invalid @enderror" value="{{ old('nominal', $simpanan->nominal) }}" placeholder="Nominal simpanan (Rupiah)">
                    @error('nominal')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="saldo" class="form-label">Saldo <span class="text-danger">*</span></label>
                    <input type="number" name="saldo" id="saldo" step="0.01" min="0" class="form-control @error('saldo') is-invalid @enderror" value="{{ old('saldo', $simpanan->saldo) }}" placeholder="Saldo akhir simpanan (Rupiah)">
                    @error('saldo')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Catatan transaksi simpanan">{{ old('keterangan', $simpanan->keterangan) }}</textarea>
                    @error('keterangan')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-orange">
                    <i class="bi bi-save me-1"></i>Simpan
                </button>
                <button type="reset" class="btn btn-secondary ms-1">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </button>
                <a href="{{ route('simpanan.index') }}" class="btn btn-outline-primary ms-1">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endpush
