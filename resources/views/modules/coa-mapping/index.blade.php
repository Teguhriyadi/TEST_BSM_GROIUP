@extends('modules.layouts.master')
@push('title', 'Mapping Akun Per Transaksi')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Mapping Akun Per Transaksi</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Mapping Akun</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Filter Mapping</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('coa-mapping.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="cabang_id" class="form-label">Cabang</label>
                        <select class="form-select select2-single" id="cabang_id" name="cabang_id" data-placeholder="-- Pengaturan Global / Cabang --">
                            <option value="">-- Pengaturan Global (Semua Cabang) --</option>
                            @foreach($cabangs as $c)
                            <option value="{{ $c->id }}" {{ $cabangId == $c->id ? 'selected' : '' }}>{{ $c->kode_cabang }} - {{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex gap-2">
                        <button type="submit" class="btn btn-orange">
                            <i class="bi bi-funnel me-1"></i> Terapkan
                        </button>
                        <a href="{{ route('coa-mapping.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    Matrix Mapping
                    <small class="fw-normal text-muted ms-2">{{ $cabangId ? 'Cabang Spesifik' : 'Global Default' }}</small>
                </h6>
            </div>
            <form method="POST" action="{{ route('coa-mapping.update') }}">
                @csrf
                <div class="card-body">
                    <input type="hidden" name="cabang_id" value="{{ $cabangId }}">
                    @if($coaAktif->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle" width="100%" cellspacing="0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">Tipe Transaksi</th>
                                    <th width="25%">Posisi Debet</th>
                                    <th width="25%">Posisi Kredit</th>
                                    <th width="15%">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tipeList as $kode => $label)
                                @php
                                    $debetRow = $mapping[$kode]['debet'] ?? null;
                                    $kreditRow = $mapping[$kode]['kredit'] ?? null;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="fw-medium">{{ $label }} <input type="hidden" name="mapping[{{ $loop->index * 2 }}][tipe_transaksi]" value="{{ $kode }}"><input type="hidden" name="mapping[{{ $loop->index * 2 }}][posisi]" value="debet"><input type="hidden" name="mapping[{{ $loop->index * 2 + 1 }}][tipe_transaksi]" value="{{ $kode }}"><input type="hidden" name="mapping[{{ $loop->index * 2 + 1 }}][posisi]" value="kredit"></td>
                                    <td>
                                        <select class="form-select select2-single" name="mapping[{{ $loop->index * 2 }}][coa_id]" data-placeholder="-- Pilih Akun Debet --">
                                            <option value="">-- Pilih Akun Debet --</option>
                                            @foreach($coaAktif as $c)
                                            <option value="{{ $c->id }}" {{ old('mapping.'.($loop->parent->index * 2).'.coa_id', $debetRow?->coa_id) == $c->id ? 'selected' : '' }}>{{ $c->kode_akun }} - {{ $c->nama_akun }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select select2-single" name="mapping[{{ $loop->index * 2 + 1 }}][coa_id]" data-placeholder="-- Pilih Akun Kredit --">
                                            <option value="">-- Pilih Akun Kredit --</option>
                                            @foreach($coaAktif as $c)
                                            <option value="{{ $c->id }}" {{ old('mapping.'.($loop->parent->index * 2 + 1).'.coa_id', $kreditRow?->coa_id) == $c->id ? 'selected' : '' }}>{{ $c->kode_akun }} - {{ $c->nama_akun }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="mapping[{{ $loop->index * 2 }}][keterangan]" value="{{ old('mapping.'.($loop->index * 2).'.keterangan', $debetRow?->keterangan) }}" placeholder="Opsional">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-outline-secondary">Reset Form</button>
                    @haspermission('COA_MAPPING_UPDATE')
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-save me-1"></i> Simpan Mapping
                    </button>
                    @endhaspermission
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
