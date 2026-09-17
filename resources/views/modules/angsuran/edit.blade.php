@extends('modules.layouts.master')
@push('title', 'Edit Angsuran')
@push('page-modules')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Angsuran</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('angsuran.update', $angsuran->id) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pinjaman_id" class="form-label">Pinjaman <span class="text-danger">*</span></label>
                    <select name="pinjaman_id" id="pinjaman_id" class="form-select select2 @error('pinjaman_id') is-invalid @enderror">
                        <option value="">-- Pilih Pinjaman --</option>
                        @foreach($pinjaman as $p)
                        <option value="{{ $p->id }}" {{ old('pinjaman_id', $angsuran->pinjaman_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->nomor_pinjaman }} - Rp {{ number_format($p->jumlah_pinjaman, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                    @error('pinjaman_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="angsuran_ke" class="form-label">Angsuran Ke <span class="text-danger">*</span></label>
                    <input type="number" name="angsuran_ke" id="angsuran_ke" min="1" step="1" class="form-control @error('angsuran_ke') is-invalid @enderror" value="{{ old('angsuran_ke', $angsuran->angsuran_ke) }}" placeholder="Angsuran ke-berapa (1, 2, 3, ...)">
                    @error('angsuran_ke')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tanggal_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" class="form-control @error('tanggal_jatuh_tempo') is-invalid @enderror" value="{{ old('tanggal_jatuh_tempo', is_object($angsuran->tanggal_jatuh_tempo) ? $angsuran->tanggal_jatuh_tempo->format('Y-m-d') : date('Y-m-d', strtotime($angsuran->tanggal_jatuh_tempo))) }}">
                    @error('tanggal_jatuh_tempo')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nominal" class="form-label">Nominal <span class="text-danger">*</span></label>
                    <input type="number" name="nominal" id="nominal" step="0.01" min="0" class="form-control @error('nominal') is-invalid @enderror" value="{{ old('nominal', $angsuran->nominal) }}" placeholder="Nominal angsuran pokok + bunga (Rupiah)">
                    @error('nominal')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="denda" class="form-label">Denda <span class="text-danger">*</span></label>
                    <input type="number" name="denda" id="denda" step="0.01" min="0" class="form-control @error('denda') is-invalid @enderror" value="{{ old('denda', $angsuran->denda) }}" placeholder="Denda keterlambatan (Rupiah, jika ada)">
                    @error('denda')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="total_bayar" class="form-label">Total Bayar <span class="text-danger">*</span></label>
                    <input type="number" name="total_bayar" id="total_bayar" step="0.01" min="0" class="form-control @error('total_bayar') is-invalid @enderror" value="{{ old('total_bayar', $angsuran->total_bayar) }}" placeholder="Total dibayar = nominal + denda (Rupiah)">
                    @error('total_bayar')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select select2 @error('status') is-invalid @enderror">
                        <option value="belum_lunas" {{ old('status', $angsuran->status) == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="lunas" {{ old('status', $angsuran->status) == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="telat" {{ old('status', $angsuran->status) == 'telat' ? 'selected' : '' }}>Telat</option>
                    </select>
                    @error('status')
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
                <a href="{{ route('angsuran.index') }}" class="btn btn-outline-primary ms-1">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endpush
