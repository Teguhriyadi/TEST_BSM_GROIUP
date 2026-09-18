@extends('modules.layouts.master')
@push('title', 'Tambah Akun (COA)')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Akun (COA)</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('coa.index') }}">Daftar Akun</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Form Tambah Akun</h6>
                <a href="{{ route('coa.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('coa.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="kode_akun" class="form-label">Kode Akun <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('kode_akun') is-invalid @enderror" id="kode_akun" name="kode_akun" value="{{ old('kode_akun') }}" placeholder="Contoh: 101, 101.01">
                            @error('kode_akun')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nama_akun" class="form-label">Nama Akun <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_akun') is-invalid @enderror" id="nama_akun" name="nama_akun" value="{{ old('nama_akun') }}" placeholder="Contoh: Kas di Tangan">
                            @error('nama_akun')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                            <select class="form-select select2-single @error('level') is-invalid @enderror" id="level" name="level" data-placeholder="-- Pilih Level --">
                                <option value="1" {{ old('level') == '1' ? 'selected' : '' }}>Level 1 (Header / Kelompok)</option>
                                <option value="2" {{ old('level', '2') == '2' ? 'selected' : '' }}>Level 2 (Detail / Posting)</option>
                            </select>
                            @error('level')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="kelompok" class="form-label">Kelompok <span class="text-danger">*</span></label>
                            <select class="form-select select2-single @error('kelompok') is-invalid @enderror" id="kelompok" name="kelompok" data-placeholder="-- Pilih Kelompok --">
                                <option value="aset" {{ old('kelompok') == 'aset' ? 'selected' : '' }}>Aset</option>
                                <option value="kewajiban" {{ old('kelompok') == 'kewajiban' ? 'selected' : '' }}>Kewajiban</option>
                                <option value="ekuitas" {{ old('kelompok') == 'ekuitas' ? 'selected' : '' }}>Ekuitas</option>
                                <option value="pendapatan" {{ old('kelompok') == 'pendapatan' ? 'selected' : '' }}>Pendapatan</option>
                                <option value="beban" {{ old('kelompok') == 'beban' ? 'selected' : '' }}>Beban</option>
                            </select>
                            @error('kelompok')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="saldo_normal" class="form-label">Saldo Normal <span class="text-danger">*</span></label>
                            <select class="form-select select2-single @error('saldo_normal') is-invalid @enderror" id="saldo_normal" name="saldo_normal" data-placeholder="-- Pilih Saldo Normal --">
                                <option value="debet" {{ old('saldo_normal') == 'debet' ? 'selected' : '' }}>Debet</option>
                                <option value="kredit" {{ old('saldo_normal') == 'kredit' ? 'selected' : '' }}>Kredit</option>
                            </select>
                            @error('saldo_normal')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="parent_id" class="form-label">Akun Induk (Header)</label>
                            <select class="form-select select2-single @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id" data-placeholder="-- Tidak ada (Level Tertinggi) --">
                                <option value="">-- Tidak ada (Level Tertinggi) --</option>
                                @foreach($parents as $p)
                                <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>{{ $p->kode_akun }} - {{ $p->nama_akun }}</option>
                                @endforeach
                            </select>
                            @error('parent_id')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="cabang_id" class="form-label">Cabang</label>
                            <select class="form-select select2-single @error('cabang_id') is-invalid @enderror" id="cabang_id" name="cabang_id" data-placeholder="-- Semua Cabang (Global) --">
                                <option value="">-- Semua Cabang (Global) --</option>
                                @foreach($cabangs as $c)
                                <option value="{{ $c->id }}" {{ old('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->kode_cabang }} - {{ $c->nama_cabang }}</option>
                                @endforeach
                            </select>
                            @error('cabang_id')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <input type="hidden" id="posisi_laporan" name="posisi_laporan" value="{{ old('posisi_laporan', 'neraca') }}">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="2" placeholder="Catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-orange">
                            <i class="bi bi-save me-1"></i> Simpan Data
                        </button>
                        <a href="{{ route('coa.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush
@push('scripts')
<script>
(function(){
    var kel = document.getElementById('kelompok');
    var pos = document.getElementById('posisi_laporan');
    function syncPosisi(){
        if(!kel || !pos) return;
        var v = kel.value;
        if (v === 'pendapatan' || v === 'beban' || v === 'ikhtisar_laba_rugi') pos.value = 'laba_rugi';
        else pos.value = 'neraca';
    }
    document.addEventListener('DOMContentLoaded', function(){
        if(kel) kel.addEventListener('change', syncPosisi);
        syncPosisi();
    });
})();
</script>
@endpush
