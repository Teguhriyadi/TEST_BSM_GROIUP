@extends('modules.layouts.master')
@push('title', 'Edit Jurnal Manual')
@push('page-modules')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Jurnal Manual</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('jurnal-umum.index') }}">Jurnal Umum</a></li>
            <li class="breadcrumb-item"><a href="{{ route('jurnal-umum.show', $header->id) }}">Detail</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Form Edit Jurnal · {{ $header->nomor_jurnal }}</h6>
                <a href="{{ route('jurnal-umum.show', $header->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <form method="POST" id="formJurnal" action="{{ route('jurnal-umum.update', $header->id) }}">
                @method('PUT')
                @csrf
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="tanggal_jurnal" class="form-label">Tanggal Jurnal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal_jurnal') is-invalid @enderror" id="tanggal_jurnal" name="tanggal_jurnal" value="{{ old('tanggal_jurnal', $header->tanggal_jurnal ? \Carbon\Carbon::parse($header->tanggal_jurnal)->format('Y-m-d') : date('Y-m-d')) }}">
                            @error('tanggal_jurnal')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                        <div class="col-md-8">
                            <label for="keterangan" class="form-label">Keterangan / Uraian Jurnal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" value="{{ old('keterangan', $header->keterangan) }}">
                            @error('keterangan')
                            <div class="invalid-feedback"><small>{{ $message }}</small></div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0 text-gray-800">Detail Baris Jurnal</h6>
                        <button type="button" id="btnTambahBaris" class="btn btn-sm btn-outline-orange">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle" id="tabelJurnal">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">Akun (COA) <span class="text-danger">*</span></th>
                                    <th width="25%">Keterangan Baris</th>
                                    <th width="18%" class="text-end">Debet (Rp)</th>
                                    <th width="18%" class="text-end">Kredit (Rp)</th>
                                    <th width="4%"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyJurnal" data-existing='@json($header->details)'>
                            </tbody>
                            <tfoot class="bg-light fw-semibold">
                                <tr>
                                    <td colspan="3" class="text-end">TOTAL</td>
                                    <td class="text-end text-orange" id="totalDebet">Rp 0</td>
                                    <td class="text-end text-orange" id="totalKredit">Rp 0</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">STATUS BALANCE</td>
                                    <td colspan="2" class="text-center" id="statusBalance">
                                        <span class="badge bg-secondary text-white">Belum Balance</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @error('details')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="card-footer bg-light py-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('jurnal-umum.show', $header->id) }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" id="btnSimpan" class="btn btn-orange">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@push('scripts')
<script>
(function(){
    var coaList = @json($coaAktif->map(fn($c) => ['id' => $c->id, 'label' => $c->kode_akun . ' - ' . $c->nama_akun]));
    var existing = JSON.parse(document.getElementById('tbodyJurnal').getAttribute('data-existing') || '[]');
    var tbody = document.getElementById('tbodyJurnal');
    var idx = 0;
    function fmt(n){ return 'Rp ' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    function rowTemplate(i, data){
        data = data || {coa_id:'', keterangan:'', debet:0, kredit:0};
        var opts = '<option value="">-- Pilih Akun --</option>' + coaList.map(function(c){ return '<option value="'+c.id+'" '+(c.id==data.coa_id?'selected':'')+'>'+c.label+'</option>'; }).join('');
        return '<tr data-index="'+i+'">' +
            '<td class="text-center rowNo">'+(i+1)+'</td>' +
            '<td><select class="form-select select-coa" name="details['+i+'][coa_id]" required>'+opts+'</select></td>' +
            '<td><input type="text" class="form-control" name="details['+i+'][keterangan]" placeholder="Opsional" value="'+(data.keterangan||'').replace(/"/g,'&quot;')+'"></td>' +
            '<td class="text-end"><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end input-debet" name="details['+i+'][debet]" value="'+(data.debet ?? 0)+'"></td>' +
            '<td class="text-end"><input type="number" min="0" step="0.01" class="form-control form-control-sm text-end input-kredit" name="details['+i+'][kredit]" value="'+(data.kredit ?? 0)+'"></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus" title="Hapus baris"><i class="bi bi-x"></i></button></td>' +
        '</tr>';
    }
    function renumber(){
        var trs = tbody.querySelectorAll('tr');
        trs.forEach(function(tr, i){
            tr.setAttribute('data-index', i);
            var rn = tr.querySelector('.rowNo'); if (rn) rn.textContent = (i+1);
            tr.querySelectorAll('input, select').forEach(function(el){
                var nm = el.getAttribute('name'); if (!nm) return;
                el.setAttribute('name', nm.replace(/details\[\d+\]/, 'details['+i+']'));
            });
        });
        idx = trs.length;
    }
    function initRowSelect2(trEl){
        if (window.jQuery && $.fn.select2) {
            var sel = trEl.querySelector('.select-coa');
            if (sel) {
                var ph = sel.getAttribute('data-placeholder') || '-- Pilih Akun --';
                $(sel).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: ph,
                    allowClear: true,
                    language: {
                        noResults: function() { return 'Tidak ada hasil'; },
                        searching: function() { return 'Mencari...'; },
                        clear: function() { return 'Hapus'; }
                    }
                });
            }
        }
    }
    function tambahBaris(data){
        tbody.insertAdjacentHTML('beforeend', rowTemplate(idx++, data));
        var tr = tbody.lastElementChild;
        bindRow(tr);
        initRowSelect2(tr);
    }
    function hitung(){
        var td=0, tk=0, filled = 0;
        tbody.querySelectorAll('tr').forEach(function(tr){
            var coa = tr.querySelector('.select-coa');
            var d = parseFloat(tr.querySelector('.input-debet').value || 0);
            var k = parseFloat(tr.querySelector('.input-kredit').value || 0);
            td += d; tk += k;
            if ((coa && coa.value) && (d > 0 || k > 0)) filled++;
        });
        document.getElementById('totalDebet').textContent = fmt(td);
        document.getElementById('totalKredit').textContent = fmt(tk);
        var bal = document.getElementById('statusBalance');
        var btn = document.getElementById('btnSimpan');
        var diff = Math.abs(td - tk);
        if (filled >= 2 && diff < 0.01 && td > 0) {
            bal.innerHTML = '<span class="badge bg-orange text-white">BALANCE</span>';
            if (btn) btn.disabled = false;
        } else {
            var hint = '';
            if (filled < 2) hint = 'Min. 2 baris terisi';
            else if (diff > 0.01) hint = 'Selisih ' + fmt(diff);
            else hint = 'Nominal masih nol';
            bal.innerHTML = '<span class="badge bg-secondary text-white">Belum Balance · ' + hint + '</span>';
            if (btn) btn.disabled = true;
        }
    }
    function bindRow(tr){
        tr.querySelector('.btn-hapus').addEventListener('click', function(){
            if (tbody.querySelectorAll('tr').length > 1) { tr.remove(); renumber(); hitung(); }
        });
        tr.querySelectorAll('.input-debet, .input-kredit, .select-coa').forEach(function(el){
            el.addEventListener('input', hitung); el.addEventListener('change', hitung);
        });
        tr.querySelector('.input-debet').addEventListener('focusout', function(){
            var d = parseFloat(this.value || 0); if (d > 0) { tr.querySelector('.input-kredit').value = 0; hitung(); }
        });
        tr.querySelector('.input-kredit').addEventListener('focusout', function(){
            var k = parseFloat(this.value || 0); if (k > 0) { tr.querySelector('.input-debet').value = 0; hitung(); }
        });
    }
    document.addEventListener('DOMContentLoaded', function(){
        if (existing.length > 0) existing.forEach(function(d){ tambahBaris(d); });
        else { tambahBaris(); tambahBaris(); }
        hitung();
        document.getElementById('btnTambahBaris').addEventListener('click', function(){ tambahBaris(); hitung(); });
    });
})();
</script>
@endpush
