@extends('modules.layouts.master')
@push('title', 'Tambah Pembayaran Angsuran')
@push('page-modules')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Pembayaran Angsuran</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('pembayaran-angsuran.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="angsuran_id" class="form-label">Angsuran <span class="text-danger">*</span></label>
                    <select name="angsuran_id" id="angsuran_id" class="form-select select2 @error('angsuran_id') is-invalid @enderror">
                        <option value="">-- Pilih Angsuran --</option>
                        @foreach($angsuran as $a)
                        <option value="{{ $a->id }}" {{ old('angsuran_id') == $a->id ? 'selected' : '' }}>
                            Pinjaman {{ $a->pinjaman->nomor_pinjaman ?? '-' }} - Ke-{{ $a->angsuran_ke }} | Jatuh Tempo: {{ is_object($a->tanggal_jatuh_tempo) ? $a->tanggal_jatuh_tempo->format('d/m/Y') : $a->tanggal_jatuh_tempo }}
                        </option>
                        @endforeach
                    </select>
                    @error('angsuran_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tanggal_bayar" class="form-label">Tanggal Bayar <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="form-control @error('tanggal_bayar') is-invalid @enderror" value="{{ old('tanggal_bayar', date('Y-m-d')) }}">
                    @error('tanggal_bayar')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jumlah_bayar" class="form-label">Jumlah Bayar <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_bayar" id="jumlah_bayar" step="0.01" min="0" class="form-control @error('jumlah_bayar') is-invalid @enderror" value="{{ old('jumlah_bayar') }}" placeholder="Jumlah yang dibayar anggota (Rupiah)">
                    @error('jumlah_bayar')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-select select2 @error('metode_pembayaran') is-invalid @enderror">
                        <option value="tunai" {{ old('metode_pembayaran', 'tunai') == 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="transfer" {{ old('metode_pembayaran') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="lainnya" {{ old('metode_pembayaran') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('metode_pembayaran')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bukti_pembayaran" class="form-label">Bukti Pembayaran</label>
                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/jpeg,image/png,application/pdf" class="form-control @error('bukti_pembayaran') is-invalid @enderror">
                    <div class="form-text small text-muted mt-1">Format: JPG, JPEG, PNG, PDF. Maksimal 10 MB.</div>
                    @error('bukti_pembayaran')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="dibayar_oleh" class="form-label">Dibayar Oleh</label>
                    <select name="dibayar_oleh" id="dibayar_oleh" class="form-select select2 @error('dibayar_oleh') is-invalid @enderror">
                        <option value="">-- Pilih User --</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('dibayar_oleh') == $u->id ? 'selected' : '' }}>
                            {{ $u->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('dibayar_oleh')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Catatan bukti transfer / pembayaran (opsional)">{{ old('keterangan') }}</textarea>
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
                <a href="{{ route('pembayaran-angsuran.index') }}" class="btn btn-outline-primary ms-1">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endpush
