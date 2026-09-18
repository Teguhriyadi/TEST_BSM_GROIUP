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
                    <select name="jenis_pinjaman_id" id="jenis_pinjaman_id" class="form-select select2 @error('jenis_pinjaman_id') is-invalid @enderror" data-auto-fill-targets="true">
                        <option value="">-- Pilih Jenis Pinjaman --</option>
                        @foreach($jenisPinjaman as $jp)
                        @php
                            $dokList = [];
                            foreach (($jp->dokumenPersyaratan ?? collect()) as $md) {
                                $dokList[] = [
                                    'id' => (string) $md->id,
                                    'kode' => $md->kode_dokumen ?? '',
                                    'nama' => $md->nama_dokumen ?? 'Dokumen',
                                    'deskripsi' => $md->deskripsi ?? '',
                                    'format' => $md->format_diperbolehkan ?? 'jpg,jpeg,png,pdf',
                                    'is_wajib' => (bool) ($md->pivot?->is_wajib ?? false),
                                    'urutan' => (int) ($md->pivot?->urutan ?? 999),
                                ];
                            }
                            usort($dokList, fn($a,$b) => $a['urutan'] - $b['urutan']);
                        @endphp
                        <option value="{{ $jp->id }}"
                            {{ old('jenis_pinjaman_id') == $jp->id ? 'selected' : '' }}
                            data-bunga="{{ number_format((float) $jp->bunga_tahunan, 2, '.', '') }}"
                            data-tenor-min="{{ (int) $jp->tenor_minimal }}"
                            data-tenor-maks="{{ (int) $jp->tenor_maksimal }}"
                            data-plafon="{{ number_format((float) $jp->maksimal_plafon, 2, '.', '') }}"
                            data-dokumen='@json($dokList)'>
                            {{ $jp->nama_jenis }}
                            (Bunga {{ (float) $jp->bunga_tahunan }}%/thn ·
                            Tenor {{ (int) $jp->tenor_minimal }}-{{ (int) $jp->tenor_maksimal }} bln ·
                            Plafon maks. Rp {{ number_format((float) $jp->maksimal_plafon, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                    @error('jenis_pinjaman_id')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                    <div id="jp_hint" class="form-text text-muted small mt-1"></div>
                    <div id="jp_dokumen_panel" class="mt-3"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="jumlah_pinjaman" class="form-label">Jumlah Pinjaman <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_pinjaman" id="jumlah_pinjaman" step="0.01" min="0" class="form-control @error('jumlah_pinjaman') is-invalid @enderror" value="{{ old('jumlah_pinjaman') }}" placeholder="Jumlah pinjaman diajukan (Rupiah)">
                    <div id="jp_plafon_alert" class="form-text text-danger small mt-1" style="display:none"></div>
                    @error('jumlah_pinjaman')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tenor" class="form-label">Tenor (bulan) <span class="text-danger">*</span></label>
                    <input type="number" name="tenor" id="tenor" min="1" step="1" class="form-control @error('tenor') is-invalid @enderror" value="{{ old('tenor') }}" placeholder="Pilih jenis pinjaman terlebih dahulu">
                    <div id="tenor_range_hint" class="form-text text-muted small mt-1"></div>
                    @error('tenor')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="bunga_display" class="form-label">Bunga (%) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" id="bunga_display" step="0.01" min="0" max="100" class="form-control bg-light" value="{{ old('bunga') }}" readonly disabled placeholder="Pilih jenis pinjaman terlebih dahulu">
                        <span class="input-group-text" title="Nilai paten dari jenis pinjaman">Dari Jenis</span>
                    </div>
                    <input type="hidden" name="bunga" id="bunga" value="{{ old('bunga') }}">
                    @error('bunga')
                    <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="angsuran_display" class="form-label">Angsuran Per Bulan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="angsuran_display" step="0.01" min="0" class="form-control bg-light" value="{{ old('angsuran_per_bulan', 0) }}" readonly disabled placeholder="Otomatis dihitung sistem">
                    </div>
                    <input type="hidden" name="angsuran_per_bulan" id="angsuran_per_bulan" value="{{ old('angsuran_per_bulan', 0) }}">
                    <div class="form-text text-muted small mt-1">
                        Dihitung otomatis sesuai rumus anuitas (jumlah, tenor, bunga tahunan).
                    </div>
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

@push('scripts')
<script>
(function(){
    var jpSel = document.getElementById('jenis_pinjaman_id');
    var bungaHidden = document.getElementById('bunga');
    var bungaDisplay = document.getElementById('bunga_display');
    var tenorInput = document.getElementById('tenor');
    var tenorRangeHint = document.getElementById('tenor_range_hint');
    var jumlahInput = document.getElementById('jumlah_pinjaman');
    var angsuranHidden = document.getElementById('angsuran_per_bulan');
    var angsuranDisplay = document.getElementById('angsuran_display');
    var jpHint = document.getElementById('jp_hint');
    var plafonAlert = document.getElementById('jp_plafon_alert');
    var dokumenPanel = document.getElementById('jp_dokumen_panel');

    function renderDokumenPanel(dokList) {
        if (! dokumenPanel) return;
        dokumenPanel.innerHTML = '';
        if (! Array.isArray(dokList) || dokList.length === 0) {
            dokumenPanel.innerHTML = '';
            return;
        }
        var wajibCount = dokList.filter(function(d){ return d.is_wajib; }).length;
        var opsCount = dokList.length - wajibCount;
        var summaryHtml =
            '<div class="card border border-primary bg-light shadow-sm">' +
            '  <div class="card-header py-2 px-3 bg-primary text-white d-flex flex-wrap align-items-center justify-content-between gap-2">' +
            '    <div class="fw-semibold"><i class="bi bi-files me-1"></i> Daftar Persyaratan Dokumen Jenis Ini</div>' +
            '    <div class="small">' +
                    (wajibCount > 0 ? '<span class="badge bg-orange text-white py-0.5 px-2 me-1">Wajib ' + wajibCount + '</span>' : '') +
                    (opsCount > 0 ? '<span class="badge bg-secondary text-white py-0.5 px-2">Opsional ' + opsCount + '</span>' : '') +
            '    </div>' +
            '  </div>' +
            '  <div class="card-body p-3">';
        summaryHtml += '<ul class="list-group list-group-flush small p-0 m-0">';
        dokList.forEach(function(d, idx) {
            var badge = d.is_wajib
                ? '<span class="badge bg-orange text-white py-0.5 px-2 ms-1">Wajib</span>'
                : '<span class="badge bg-secondary text-white py-0.5 px-2 ms-1">Opsional</span>';
            var formatTxt = (d.format || '').trim().toUpperCase().replace(/,/g, ', ');
            summaryHtml +=
                '<li class="list-group-item px-0 py-2 border-start-0 border-end-0 border-top-0 d-flex flex-column gap-1">' +
                '  <div class="d-flex flex-wrap align-items-center gap-1">' +
                '    <span class="fw-semibold text-gray-800">' + (idx + 1) + '. ' + (d.nama || 'Dokumen') + '</span>' + badge +
                (d.kode ? ' <small class="text-muted">(' + (d.kode) + ')</small>' : '') +
                '  </div>' +
                (d.deskripsi ? '  <div class="text-muted small">' + d.deskripsi + '</div>' : '') +
                (formatTxt ? '  <div class="text-muted small">Format yang diperbolehkan: ' + formatTxt + '</div>' : '') +
                '</li>';
        });
        summaryHtml += '</ul>';
        summaryHtml +=
            '    <div class="small text-muted mt-2">' +
            '      <i class="bi bi-info-circle me-1"></i>' +
            '      Unggah semua dokumen di atas setelah data pinjaman berhasil disimpan (halaman detail pengajuan). Dokumen wajib harus diunggah sebelum pengajuan bisa diverifikasi.' +
            '    </div>';
        summaryHtml += '  </div></div>';
        dokumenPanel.innerHTML = summaryHtml;
    }

    function formatRp(num) {
        if (! isFinite(num)) num = 0;
        num = Number(num) || 0;
        var neg = num < 0;
        var s = Math.abs(num).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return (neg ? '-Rp ' : 'Rp ') + s;
    }
    function hitungAngsuran(jumlah, bungaTahunan, tenor) {
        jumlah = Number(jumlah) || 0;
        bungaTahunan = Number(bungaTahunan) || 0;
        tenor = parseInt(tenor, 10) || 0;
        if (tenor <= 0) return 0;
        if (bungaTahunan <= 0) return Math.round((jumlah / tenor) * 100) / 100;
        var r = bungaTahunan / 100 / 12;
        if (r <= 0) return Math.round((jumlah / tenor) * 100) / 100;
        var pow = Math.pow(1 + r, tenor);
        var denom = pow - 1;
        if (denom <= 0) return Math.round((jumlah / tenor) * 100) / 100;
        return Math.round(jumlah * (r * pow) / denom * 100) / 100;
    }
    function hitungDanSetAngsuran() {
        var jumlah = Number(jumlahInput.value) || 0;
        var tenor = parseInt(tenorInput.value, 10) || 0;
        var bunga = Number(bungaHidden.value) || 0;
        var perBulan = hitungAngsuran(jumlah, bunga, tenor);
        angsuranHidden.value = perBulan > 0 ? perBulan.toFixed(2) : '';
        angsuranDisplay.value = perBulan > 0 ? perBulan.toFixed(2) : '';
        var opt = jpSel ? jpSel.options[jpSel.selectedIndex] : null;
        if (opt && opt.value) {
            var plafon = Number(opt.getAttribute('data-plafon')) || 0;
            if (plafon > 0 && jumlah > plafon) {
                plafonAlert.style.display = '';
                plafonAlert.textContent = '⚠ Jumlah pinjaman melebihi plafon maksimal jenis ini: ' + formatRp(plafon);
            } else {
                plafonAlert.style.display = 'none';
                plafonAlert.textContent = '';
            }
        } else if (plafonAlert) {
            plafonAlert.style.display = 'none';
            plafonAlert.textContent = '';
        }
    }
    function clampTenorToRange(t, tMin, tMaks) {
        if (isNaN(t) || t <= 0) return tMin;
        if (t < tMin) return tMin;
        if (t > tMaks) return tMaks;
        return t;
    }
    function isiDariJenis() {
        var opt = jpSel ? jpSel.options[jpSel.selectedIndex] : null;
        if (! opt || ! opt.value) {
            bungaHidden.value = '';
            bungaDisplay.value = '';
            if (tenorInput) {
                tenorInput.min = '1';
                tenorInput.removeAttribute('max');
            }
            if (tenorRangeHint) tenorRangeHint.textContent = '';
            angsuranHidden.value = '';
            angsuranDisplay.value = '';
            if (jpHint) jpHint.textContent = '';
            renderDokumenPanel([]);
            return;
        }
        var bunga = Number(opt.getAttribute('data-bunga')) || 0;
        var tMin = parseInt(opt.getAttribute('data-tenor-min'), 10) || 1;
        var tMaks = parseInt(opt.getAttribute('data-tenor-maks'), 10) || tMin;
        var plafon = Number(opt.getAttribute('data-plafon')) || 0;
        var dokRaw = opt.getAttribute('data-dokumen');
        var dokList = [];
        try {
            if (dokRaw) dokList = JSON.parse(dokRaw);
        } catch (e) { dokList = []; }
        renderDokumenPanel(dokList);
        bungaHidden.value = bunga > 0 ? bunga.toFixed(2) : '0.00';
        bungaDisplay.value = bunga > 0 ? bunga.toFixed(2) : '0.00';
        if (tenorInput) {
            tenorInput.min = String(tMin);
            tenorInput.max = String(tMaks);
            var tCurrent = parseInt(tenorInput.value, 10);
            var defaultTenor = clampTenorToRange(tCurrent, tMin, tMaks);
            if (! tCurrent || tCurrent < tMin || tCurrent > tMaks) {
                tenorInput.value = String(defaultTenor);
            }
        }
        if (tenorRangeHint) {
            tenorRangeHint.textContent = 'Rentang diizinkan: ' + tMin + ' - ' + tMaks + ' bulan (bisa diubah manual dalam rentang ini).';
        }
        if (jpHint) {
            jpHint.textContent =
                'Bunga ' + bunga.toFixed(2) + '%/tahun (paten) · ' +
                'Rentang tenor ' + tMin + '-' + tMaks + ' bulan' +
                (plafon > 0 ? ' · Plafon maksimal ' + formatRp(plafon) : '');
        }
        hitungDanSetAngsuran();
    }
    if (jpSel) {
        jpSel.addEventListener('change', function(){ isiDariJenis(); });
        if (window.jQuery && window.$) {
            $(jpSel).on('change select2:select select2:clear', function(){ isiDariJenis(); });
        }
    }
    [jumlahInput, tenorInput].forEach(function(el){
        if (! el) return;
        el.addEventListener('input', function(){ hitungDanSetAngsuran(); });
    });
    document.addEventListener('DOMContentLoaded', function(){
        isiDariJenis();
        hitungDanSetAngsuran();
    });
    if (window.jQuery && window.$) {
        $(document).ready(function(){
            setTimeout(isiDariJenis, 150);
            setTimeout(isiDariJenis, 400);
            setTimeout(isiDariJenis, 800);
            setTimeout(hitungDanSetAngsuran, 200);
            setTimeout(hitungDanSetAngsuran, 500);
        });
    } else {
        setTimeout(isiDariJenis, 200);
        setTimeout(isiDariJenis, 500);
        setTimeout(isiDariJenis, 900);
        setTimeout(hitungDanSetAngsuran, 250);
        setTimeout(hitungDanSetAngsuran, 550);
    }
})();
</script>
@endpush
