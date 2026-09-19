@extends('modules.layouts.master')

@push('title')
    Simulasi Pinjaman | BSM Koperasi
@endpush

@push('breadcrumbs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5 small">
            <li class="breadcrumb-item text-sm text-dark"><a class="opacity-5 text-dark"
                    href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Simulasi Pinjaman</li>
        </ol>
        <h6 class="font-weight-bolder mb-0">Kalkulator Simulasi Pinjaman</h6>
    </nav>
@endpush

@push('page-modules')

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible text-white small" role="alert">
            <span class="text-sm">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <form action="{{ route('simulasi-pinjaman.index') }}" method="GET">
                @csrf
                <div class="card shadow mb-4 border-left-orange">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-orange">
                            <i class="bi bi-calculator me-1"></i> Parameter Simulasi
                        </h6>
                        <div class="small text-muted">Hasil perhitungan real-time di samping</div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-medium small">Jenis Pinjaman <span
                                        class="text-danger">*</span></label>
                                <select id="jenis_pinjaman_id" name="jenis_pinjaman_id" class="form-select select2-advanced"
                                    required data-placeholder="Pilih jenis pinjaman">
                                    <option value="">-- Pilih Jenis Pinjaman --</option>
                                    @foreach ($jenisPinjaman as $jp)
                                        <option value="{{ $jp->id }}" @selected((string) $selectedJenisId === (string) $jp->id)
                                            data-bunga="{{ number_format($jp->bunga_tahunan, 2, '.', '') }}"
                                            data-tenor-min="{{ intval($jp->tenor_minimal ?? 1) }}"
                                            data-tenor-max="{{ intval($jp->tenor_maksimal ?? 120) }}"
                                            data-plafon-max="{{ number_format($jp->maksimal_plafon ?? 0, 2, '.', '') }}">
                                            {{ $jp->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jenis_pinjaman_id')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Jumlah Pinjaman (Rp) <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="jumlah_pinjaman" name="jumlah_pinjaman" min="0"
                                    step="100000" class="form-control"
                                    value="{{ old('jumlah_pinjaman', $jumlahPinjaman > 0 ? number_format($jumlahPinjaman, 0, '.', '') : '') }}"
                                    placeholder="Contoh: 10000000">
                                <div class="form-text small text-muted">Tidak boleh melebihi plafon maksimal jenis pinjaman.
                                </div>
                                @error('jumlah_pinjaman')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Tenor (Bulan) <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="tenor" name="tenor" min="1" step="1"
                                    class="form-control" value="{{ old('tenor', $tenor > 0 ? $tenor : '') }}"
                                    placeholder="Contoh: 24">
                                <div class="form-text small text-muted" id="tenorHint">Pilih jenis pinjaman terlebih dahulu
                                    untuk melihat rentang tenor.</div>
                                @error('tenor')
                                    <div class="small text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Bunga Efektif / Tahun (%)</label>
                                <input type="text" id="bunga_tahunan" class="form-control bg-light" readonly
                                    placeholder="Auto dari jenis pinjaman">
                                <div class="form-text small text-muted">Diambil otomatis dari pengaturan jenis pinjaman.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium small">Plafon Maksimum Jenis Ini</label>
                                <input type="text" id="plafon_maksimal" class="form-control bg-light" readonly
                                    placeholder="Auto dari jenis pinjaman">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-medium small">Angsuran Per Bulan (Simulasi)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-orange border-orange text-white fw-semibold">Rp</span>
                                    <input type="text" id="angsuran_per_bulan"
                                        class="form-control bg-orange-50 text-gray-900 fw-semibold fs-5 border-orange"
                                        readonly placeholder="Hasil perhitungan otomatis">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-end gap-2">
                        <button type="button" id="btnReset" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-sm btn-orange">
                            <i class="bi bi-calculator me-1"></i> Hitung Simulasi
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card shadow mb-4 h-100 border-left-info">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-info-50">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-table me-1"></i> Ringkasan Hasil Simulasi
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $h = $hasil ?? null;
                    @endphp
                    <div class="row g-2 small">
                        <div class="col-6 text-muted">Jenis Pinjaman</div>
                        <div class="col-6 text-end fw-semibold" id="resJenis">{{ $h['jenis_nama'] ?? '-' }}</div>

                        <div class="col-6 text-muted">Jumlah Pengajuan</div>
                        <div class="col-6 text-end fw-semibold text-orange" id="resJumlah">
                            {{ $h ? 'Rp ' . number_format($h['jumlah_pinjaman'], 0, ',', '.') : '-' }}</div>

                        <div class="col-6 text-muted">Tenor</div>
                        <div class="col-6 text-end fw-semibold" id="resTenor">
                            {{ $h ? number_format($h['tenor_bulan'], 0, ',', '.') . ' bulan' : '-' }}</div>

                        <div class="col-6 text-muted">Bunga / Tahun</div>
                        <div class="col-6 text-end fw-semibold" id="resBunga">
                            {{ $h ? number_format($h['bunga_tahunan'], 2, ',', '.') . '%' : '-' }}</div>

                        <div class="col-12 border-bottom my-2"></div>

                        <div class="col-6 text-muted">Angsuran Per Bulan</div>
                        <div class="col-6 text-end fw-bold text-orange fs-5" id="resAngsuran">
                            {{ $h ? 'Rp ' . number_format($h['angsuran_per_bulan'], 0, ',', '.') : '-' }}</div>

                        <div class="col-6 text-muted">Total Bayar Selama Tenor</div>
                        <div class="col-6 text-end fw-semibold" id="resTotal">
                            {{ $h ? 'Rp ' . number_format($h['total_bayar'], 0, ',', '.') : '-' }}</div>

                        <div class="col-6 text-muted">Total Bunga Dibayar</div>
                        <div class="col-6 text-end fw-semibold text-info" id="resBungaTotal">
                            {{ $h ? 'Rp ' . number_format($h['total_bunga'], 0, ',', '.') : '-' }}</div>

                        <div class="col-6 text-muted">Rasio Bunga / Pokok</div>
                        <div class="col-6 text-end fw-semibold" id="resRasio">
                            {{ $h && ($h['jumlah_pinjaman'] ?? 0) > 0 ? number_format(($h['total_bunga'] / $h['jumlah_pinjaman']) * 100, 2, ',', '.') . '%' : '-' }}
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded small">
                        <div class="fw-semibold text-gray-800 mb-1">
                            <i class="bi bi-info-circle me-1 text-info"></i> Catatan
                        </div>
                        <div class="text-muted">Perhitungan menggunakan metode anuitas (bunga efektif). Angsuran per bulan
                            bersifat flat. Nilai real dapat berbeda karena faktor pembulatan, hari libur, dan denda
                            keterlambatan (jika ada).</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endpush

@push('scripts')
    <script>
        (function() {
            var numberFormat = function(n) {
                if (!isFinite(n)) return '0';
                var dec = 0;
                var s = String(Math.round(n));
                var neg = s.charAt(0) === '-' ? '-' : '';
                if (neg) s = s.substr(1);
                var r = s.split('').reverse().join('');
                r = r.match(/.{1,3}/g).join('.').split('').reverse().join('');
                return neg + r;
            };

            function hitungSimulasi() {
                var $sel = document.getElementById('jenis_pinjaman_id');
                var opt = $sel.options[$sel.selectedIndex];
                if (!opt || opt.value === '') {
                    clearAll();
                    return;
                }
                var bunga = parseFloat(opt.getAttribute('data-bunga') || '0');
                var tenMin = parseInt(opt.getAttribute('data-tenor-min') || '1', 10);
                var tenMax = parseInt(opt.getAttribute('data-tenor-max') || '120', 10);
                var plafonMax = parseFloat(opt.getAttribute('data-plafon-max') || '0');

                document.getElementById('bunga_tahunan').value = (bunga > 0 ? bunga.toFixed(2) : '0.00') + '%';
                document.getElementById('plafon_maksimal').value = plafonMax > 0 ? 'Rp ' + numberFormat(plafonMax) :
                    'Tidak dibatasi';
                document.getElementById('tenorHint').textContent = 'Rentang diizinkan: minimal ' + tenMin +
                    ' bulan, maksimal ' + tenMax + ' bulan.';

                var $tenor = document.getElementById('tenor');
                if ($tenor && !$tenor.dataset.locked) {
                    $tenor.min = String(tenMin);
                    $tenor.max = String(tenMax);
                    if (parseInt($tenor.value || '0', 10) < tenMin) {
                        $tenor.value = String(tenMin);
                    }
                    if (parseInt($tenor.value || '0', 10) > tenMax) {
                        $tenor.value = String(tenMax);
                    }
                }

                var jumlah = parseFloat(document.getElementById('jumlah_pinjaman').value || '0');
                var tenor = parseInt(document.getElementById('tenor').value || '0', 10);
                if (plafonMax > 0 && jumlah > plafonMax) {
                    jumlah = plafonMax;
                    document.getElementById('jumlah_pinjaman').value = String(Math.round(jumlah));
                }

                var angsuran = 0;
                var totalBayar = 0;
                var totalBunga = 0;
                if (jumlah > 0 && tenor > 0) {
                    var r = (bunga / 100) / 12;
                    if (r <= 0 || bunga <= 0) {
                        angsuran = jumlah / tenor;
                    } else {
                        var pow = Math.pow(1 + r, tenor);
                        var denom = pow - 1;
                        if (denom <= 0) {
                            angsuran = jumlah / tenor;
                        } else {
                            angsuran = (jumlah * r * pow) / denom;
                        }
                    }
                    angsuran = Math.round(angsuran * 100) / 100;
                    totalBayar = Math.round(angsuran * tenor * 100) / 100;
                    totalBunga = Math.round((totalBayar - jumlah) * 100) / 100;
                }

                document.getElementById('angsuran_per_bulan').value = angsuran > 0 ? 'Rp ' + numberFormat(angsuran) :
                '';
                document.getElementById('resJenis').textContent = opt.textContent.trim();
                document.getElementById('resJumlah').textContent = jumlah > 0 ? 'Rp ' + numberFormat(jumlah) : '-';
                document.getElementById('resTenor').textContent = tenor > 0 ? numberFormat(tenor) + ' bulan' : '-';
                document.getElementById('resBunga').textContent = bunga > 0 ? bunga.toFixed(2).replace('.', ',') + '%' :
                    '-';
                document.getElementById('resAngsuran').textContent = angsuran > 0 ? 'Rp ' + numberFormat(angsuran) :
                '-';
                document.getElementById('resTotal').textContent = totalBayar > 0 ? 'Rp ' + numberFormat(totalBayar) :
                    '-';
                document.getElementById('resBungaTotal').textContent = totalBunga > 0 ? 'Rp ' + numberFormat(
                    totalBunga) : '-';
                if (jumlah > 0 && totalBunga > 0) {
                    var rasio = Math.round((totalBunga / jumlah) * 10000) / 100;
                    document.getElementById('resRasio').textContent = String(rasio).replace('.', ',') + '%';
                } else {
                    document.getElementById('resRasio').textContent = '-';
                }
            }

            function clearAll() {
                document.getElementById('bunga_tahunan').value = '';
                document.getElementById('plafon_maksimal').value = '';
                document.getElementById('tenorHint').textContent =
                    'Pilih jenis pinjaman terlebih dahulu untuk melihat rentang tenor.';
                document.getElementById('angsuran_per_bulan').value = '';
                document.getElementById('resJenis').textContent = '-';
                document.getElementById('resJumlah').textContent = '-';
                document.getElementById('resTenor').textContent = '-';
                document.getElementById('resBunga').textContent = '-';
                document.getElementById('resAngsuran').textContent = '-';
                document.getElementById('resTotal').textContent = '-';
                document.getElementById('resBungaTotal').textContent = '-';
                document.getElementById('resRasio').textContent = '-';
            }

            function bind() {
                var $jp = document.getElementById('jenis_pinjaman_id');
                if ($jp) {
                    $jp.addEventListener('change', function() {
                        hitungSimulasi();
                    });
                    if (window.jQuery) {
                        jQuery($jp).on('select2:select select2:clear select2:unselect', function() {
                            setTimeout(hitungSimulasi, 20);
                        });
                    }
                }
                var $jml = document.getElementById('jumlah_pinjaman');
                if ($jml) {
                    $jml.addEventListener('input', hitungSimulasi);
                    $jml.addEventListener('blur', hitungSimulasi);
                }
                var $tn = document.getElementById('tenor');
                if ($tn) {
                    $tn.addEventListener('input', hitungSimulasi);
                    $tn.addEventListener('blur', hitungSimulasi);
                }
                var $reset = document.getElementById('btnReset');
                if ($reset) {
                    $reset.addEventListener('click', function() {
                        if ($jp) {
                            $jp.value = '';
                            if (window.jQuery) jQuery($jp).trigger('change').trigger('select2:clear');
                        }
                        if ($jml) $jml.value = '';
                        if ($tn) $tn.value = '';
                        clearAll();
                    });
                }
            }

            bind();
            setTimeout(hitungSimulasi, 120);
            setTimeout(hitungSimulasi, 380);
            setTimeout(hitungSimulasi, 700);
        })();
    </script>
@endpush
