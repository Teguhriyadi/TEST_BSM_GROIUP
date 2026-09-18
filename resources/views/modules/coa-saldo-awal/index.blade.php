@extends('modules.layouts.master')
@push('title', 'Saldo Awal Per Akun')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Saldo Awal Per Akun</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Saldo Awal</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Filter Periode & Cabang</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('coa-saldo-awal.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="cabang_id" class="form-label">Cabang <span class="text-danger">*</span></label>
                        <select class="form-select select2-single" id="cabang_id" name="cabang_id" required data-placeholder="-- Pilih Cabang --">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($cabangs as $c)
                            <option value="{{ $c->id }}" {{ $cabangId == $c->id ? 'selected' : '' }}>{{ $c->kode_cabang }} - {{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="periode" class="form-label">Periode (Bulan/Tahun) <span class="text-danger">*</span></label>
                        <input type="month" class="form-control" id="periode" name="periode" value="{{ $periode }}" required>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-orange">
                            <i class="bi bi-funnel me-1"></i> Muat Saldo
                        </button>
                        <a href="{{ route('coa-saldo-awal.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                    Daftar Saldo Awal
                    <small class="fw-normal text-muted ms-2">Periode {{ date('F Y', strtotime($periode . '-01')) }} · {{ $cabangs->firstWhere('id', $cabangId)?->nama_cabang ?? '-' }}</small>
                </h6>
            </div>
            <form method="POST" action="{{ route('coa-saldo-awal.update') }}">
                @csrf
                <div class="card-body">
                    <input type="hidden" name="cabang_id" value="{{ $cabangId }}">
                    <input type="hidden" name="periode" value="{{ $periode }}">
                    @if($coaAktif->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle" width="100%" cellspacing="0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="15%">Kode Akun</th>
                                    <th width="30%">Nama Akun</th>
                                    <th width="12%">Kelompok</th>
                                    <th width="18%" class="text-end">Saldo Awal Debet</th>
                                    <th width="18%" class="text-end">Saldo Awal Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalDebet = 0;
                                    $totalKredit = 0;
                                @endphp
                                @foreach($coaAktif as $c)
                                @php
                                    $saldo = $c->saldoAwal->first();
                                    $debetVal = old('rows.'.$loop->index.'.saldo_awal_debet', $saldo?->saldo_awal_debet ?? 0);
                                    $kreditVal = old('rows.'.$loop->index.'.saldo_awal_kredit', $saldo?->saldo_awal_kredit ?? 0);
                                    $totalDebet += (float) $debetVal;
                                    $totalKredit += (float) $kreditVal;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $c->kode_akun }}<input type="hidden" name="rows[{{ $loop->index }}][cabang_id]" value="{{ $cabangId }}"><input type="hidden" name="rows[{{ $loop->index }}][coa_id]" value="{{ $c->id }}"></td>
                                    <td>{{ $c->nama_akun }}</td>
                                    <td>{{ ucwords(str_replace('_',' ',$c->kelompok)) }}</td>
                                    <td class="text-end">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end input-debet" data-row="{{ $loop->index }}" name="rows[{{ $loop->index }}][saldo_awal_debet]" value="{{ number_format($debetVal,2,'.','') }}">
                                    </td>
                                    <td class="text-end">
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end input-kredit" data-row="{{ $loop->index }}" name="rows[{{ $loop->index }}][saldo_awal_kredit]" value="{{ number_format($kreditVal,2,'.','') }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-semibold">
                                <tr>
                                    <td colspan="4" class="text-end">TOTAL</td>
                                    <td class="text-end text-orange" id="totalDebet">Rp {{ number_format($totalDebet,0,',','.') }}</td>
                                    <td class="text-end text-orange" id="totalKredit">Rp {{ number_format($totalKredit,0,',','.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end">SELISIH</td>
                                    <td colspan="2" class="text-end" id="selisihBox">
                                        @if(abs($totalDebet - $totalKredit) < 0.01)
                                        <span class="badge bg-orange text-white">BALANCE</span>
                                        @else
                                        <span class="badge bg-danger text-white">TIDAK BALANCE · Rp {{ number_format(abs($totalDebet - $totalKredit),0,',','.') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                    @haspermission('COA_SALDO_AWAL_UPDATE')
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-save me-1"></i> Simpan Saldo Awal
                    </button>
                    @endhaspermission
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@push('scripts')
<script>
(function(){
    function hitungTotal() {
        var td = 0, tk = 0;
        document.querySelectorAll('.input-debet').forEach(function(el){ td += parseFloat(el.value || 0); });
        document.querySelectorAll('.input-kredit').forEach(function(el){ tk += parseFloat(el.value || 0); });
        function fmt(n){ return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
        var tdEl = document.getElementById('totalDebet');
        var tkEl = document.getElementById('totalKredit');
        var sb = document.getElementById('selisihBox');
        if (tdEl) tdEl.textContent = fmt(td);
        if (tkEl) tkEl.textContent = fmt(tk);
        if (sb) {
            var diff = Math.abs(td - tk);
            if (diff < 0.01) sb.innerHTML = '<span class="badge bg-orange text-white">BALANCE</span>';
            else sb.innerHTML = '<span class="badge bg-danger text-white">TIDAK BALANCE · Rp ' + Math.round(diff).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '</span>';
        }
    }
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.input-debet, .input-kredit').forEach(function(el){
            el.addEventListener('input', hitungTotal);
            el.addEventListener('change', hitungTotal);
        });
    });
})();
</script>
@endpush
