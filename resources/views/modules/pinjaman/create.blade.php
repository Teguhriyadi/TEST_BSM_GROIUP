@extends('modules.layouts.master')
@push('title', 'Tambah Pinjaman')
@push('page-modules')
@php
    $isAnggota = auth()->check() && auth()->user()->hasRole('Anggota');
    $defAnggotaId = $isAnggota && isset($anggota) && $anggota->count() === 1 ? (string) $anggota->first()->id : old('anggota_id');
    $defCabangId = $isAnggota && isset($cabang) && $cabang->count() === 1 ? (string) $cabang->first()->id : old('cabang_id');
@endphp
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            {{ $isAnggota ? 'Ajukan Pinjaman Mandiri' : 'Form Tambah Pinjaman' }}
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('pinjaman.store') }}">
            @csrf
            @if($isAnggota)
                <input type="hidden" name="anggota_id" value="{{ $defAnggotaId }}">
                <input type="hidden" name="cabang_id" value="{{ $defCabangId }}">
                <input type="hidden" name="status" value="diajukan">
            @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="anggota_id" class="form-label">Anggota <span class="text-danger">*</span></label>
                    <select name="{{ $isAnggota ? '' : 'anggota_id' }}" id="anggota_id" class="form-select select2 @error('anggota_id') is-invalid @enderror" {{ $isAnggota ? 'disabled' : '' }}>
                        <option value="">-- Pilih Anggota --</option>
                        @foreach($anggota as $a)
                        <option value="{{ $a->id }}" {{ $defAnggotaId == $a->id ? 'selected' : '' }}>
                            {{ $a->no_anggota }} - {{ $a->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('anggota_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                    @if($isAnggota)
                    <div class="form-text text-muted"><small>Data anggota otomatis sesuai akun login.</small></div>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="cabang_id" class="form-label">Cabang <span class="text-danger">*</span></label>
                    <select name="{{ $isAnggota ? '' : 'cabang_id' }}" id="cabang_id" class="form-select select2 @error('cabang_id') is-invalid @enderror" {{ $isAnggota ? 'disabled' : '' }}>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($cabang as $c)
                        <option value="{{ $c->id }}" {{ $defCabangId == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_cabang }}
                        </option>
                        @endforeach
                    </select>
                    @error('cabang_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                    @if($isAnggota)
                    <div class="form-text text-muted"><small>Cabang sesuai domisili anggota terdaftar.</small></div>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jenis_pinjaman_id" class="form-label">Jenis Pinjaman <span class="text-danger">*</span></label>
                    <select name="jenis_pinjaman_id" id="jenis_pinjaman_id" class="form-select select2 @error('jenis_pinjaman_id') is-invalid @enderror">
                        <option value="">-- Pilih Jenis Pinjaman --</option>
                        @foreach($jenisPinjaman as $jp)
                        <option value="{{ $jp->id }}" {{ old('jenis_pinjaman_id') == $jp->id ? 'selected' : '' }}>
                            {{ $jp->nama_jenis }}
                        </option>
                        @endforeach
                    </select>
                    @error('jenis_pinjaman_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nomor_pinjaman_info" class="form-label">Nomor Pinjaman</label>
                    <input type="text" id="nomor_pinjaman_info" class="form-control" value="Nomor akan dibuat otomatis sistem saat disimpan." disabled readonly>
                    <input type="hidden" name="nomor_pinjaman" value="">
                    <div class="form-text text-muted"><small>Format: [KodeCabang]-PIN-[yyyymm]-[urut4digit]. Contoh: KCP-PIN-202609-0001</small></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jumlah_pinjaman" class="form-label">Jumlah Pinjaman <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_pinjaman" id="jumlah_pinjaman" step="0.01" min="0" class="form-control @error('jumlah_pinjaman') is-invalid @enderror" value="{{ old('jumlah_pinjaman') }}" placeholder="Jumlah pinjaman diajukan (Rupiah)">
                    @error('jumlah_pinjaman')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tenor" class="form-label">Tenor (bulan) <span class="text-danger">*</span></label>
                    <input type="number" name="tenor" id="tenor" min="1" step="1" class="form-control @error('tenor') is-invalid @enderror" value="{{ old('tenor') }}" placeholder="Jumlah bulan (misal: 12)">
                    @error('tenor')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bunga" class="form-label">Bunga (%) <span class="text-danger">*</span></label>
                    <input type="number" name="bunga" id="bunga" step="0.01" min="0" max="100" class="form-control @error('bunga') is-invalid @enderror" value="{{ old('bunga') }}" placeholder="Persen bunga per tahun (0-100)">
                    @error('bunga')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="angsuran_per_bulan" class="form-label">Angsuran Per Bulan <span class="text-danger">*</span></label>
                    <input type="number" name="angsuran_per_bulan" id="angsuran_per_bulan" step="0.01" min="0" class="form-control @error('angsuran_per_bulan') is-invalid @enderror" value="{{ old('angsuran_per_bulan', 0) }}" placeholder="Angsuran per bulan (Rupiah)">
                    @error('angsuran_per_bulan')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label for="tujuan" class="form-label">Tujuan</label>
                    <textarea name="tujuan" id="tujuan" rows="3" class="form-control @error('tujuan') is-invalid @enderror" placeholder="Tujuan penggunaan pinjaman">{{ old('tujuan') }}</textarea>
                    @error('tujuan')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                @if(!$isAnggota)
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select select2 @error('status') is-invalid @enderror">
                        <option value="diajukan" {{ old('status', 'diajukan') == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="diverifikasi" {{ old('status') == 'diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
                        <option value="disetujui" {{ old('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ old('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="dicairkan" {{ old('status') == 'dicairkan' ? 'selected' : '' }}>Dicairkan</option>
                        <option value="berjalan" {{ old('status') == 'berjalan' ? 'selected' : '' }}>Berjalan</option>
                        <option value="lunas" {{ old('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="dibatalkan" {{ old('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                    @error('status')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                @else
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status Pengajuan</label>
                    <div class="form-control bg-light" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                        <span class="badge bg-warning text-dark" style="font-size: 0.8rem; padding: 0.35rem 0.6rem;">Diajukan (Otomatis)</span>
                    </div>
                    <div class="form-text text-muted"><small>Pengajuan baru otomatis masuk status Diajukan, akan diperiksa Teller / Kepala Cabang.</small></div>
                </div>
                @endif
                <div class="col-md-4 mb-3">
                    <label for="tgl_pengajuan" class="form-label">Tgl Pengajuan</label>
                    <input type="date" name="tgl_pengajuan" id="tgl_pengajuan" class="form-control @error('tgl_pengajuan') is-invalid @enderror" value="{{ old('tgl_pengajuan', date('Y-m-d')) }}">
                    @error('tgl_pengajuan')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tgl_cair" class="form-label">Tgl Cair</label>
                    <input type="date" name="tgl_cair" id="tgl_cair" class="form-control @error('tgl_cair') is-invalid @enderror" value="{{ old('tgl_cair') }}">
                    @error('tgl_cair')
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
                <a href="{{ route('pinjaman.index') }}" class="btn btn-outline-primary ms-1">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endpush
