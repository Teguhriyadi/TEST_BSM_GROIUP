@extends('modules.layouts.master')
@push('title', 'Verifikasi Pendaftaran Anggota')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Verifikasi Pendaftaran</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none link-primary">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('anggota.pendaftaran.menunggu') }}" class="text-decoration-none link-primary">Pendaftaran Menunggu</a></li>
                <li class="breadcrumb-item active" aria-current="page">Verifikasi Detail</li>
            </ol>
        </nav>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
        <small><b>Gagal memverifikasi pendaftaran.</b> Silakan perbaiki kesalahan di bawah.</small>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@foreach(['success','warning','error'] as $s)
    @if(session($s))
        <div class="alert alert-{{ $s === 'error' ? 'danger' : $s }} alert-dismissible fade show py-2 mb-3" role="alert">
            <small>{{ session($s) }}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@endforeach

<div class="row mb-4">
    <div class="col-lg-7 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 bg-gradient-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="bi bi-file-earmark-person me-2"></i>Data Calon Anggota
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0" style="border-collapse: separate; border-spacing: 0 0.25rem;">
                        <tr>
                            <td style="width: 160px;" class="fw-semibold text-secondary small">Cabang Pendaftaran</td>
                            <td style="width: 12px;" class="text-muted small">:</td>
                            <td>{{ $anggota->cabang->nama_cabang ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary small">NIK</td>
                            <td class="text-muted small">:</td>
                            <td><code class="text-primary" style="background:#f0f9ff;padding:0.1rem 0.4rem;border-radius:4px;">{{ $anggota->nik }}</code></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary small">Nama Lengkap</td>
                            <td class="text-muted small">:</td>
                            <td class="fw-semibold">{{ $anggota->nama }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary small">Jenis Kelamin</td>
                            <td class="text-muted small">:</td>
                            <td>{{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary small">Tempat / Tgl. Lahir</td>
                            <td class="text-muted small">:</td>
                            <td>
                                @if($anggota->tgl_lahir)
                                    {{ \Carbon\Carbon::parse($anggota->tgl_lahir)->translatedFormat('d F Y') }}
                                    <small class="text-muted ms-2">({{ \Carbon\Carbon::parse($anggota->tgl_lahir)->age }} tahun)</small>
                                @else - @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary small">Nomor Handphone</td>
                            <td class="text-muted small">:</td>
                            <td>
                                @if($anggota->no_hp)
                                    <a href="tel:{{ $anggota->no_hp }}" class="text-decoration-none link-primary">
                                        <i class="bi bi-telephone me-1"></i>{{ $anggota->no_hp }}
                                    </a>
                                @else - @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary small">Email Login</td>
                            <td class="text-muted small">:</td>
                            <td>
                                @if($anggota->user?->email)
                                    <span class="fw-semibold text-primary">{{ $anggota->user->email }}</span>
                                    <small class="badge bg-success ms-1">Sudah ada akun</small>
                                @else
                                    <span class="text-muted">(tidak ada akun)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary small align-top pt-2">Alamat Lengkap</td>
                            <td class="text-muted small align-top pt-2">:</td>
                            <td>{{ $anggota->alamat ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-secondary small">Waktu Pendaftaran</td>
                            <td class="text-muted small">:</td>
                            <td>{{ $anggota->created_at ? \Carbon\Carbon::parse($anggota->created_at)->translatedFormat('l, d F Y - H:i') : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 mb-4">
        <form action="{{ route('anggota.pendaftaran.verifikasi', $anggota->id) }}" method="POST" novalidate>
            @csrf
            <div class="card shadow h-100 border border-orange">
                <div class="card-header py-3 bg-gradient-orange text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bi bi-check2-square me-2"></i>Formulir Keputusan Verifikasi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="keputusan" class="form-label fw-semibold small">Keputusan <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2 mt-2">
                            <label class="form-check border border-success rounded px-3 py-2 shadow-sm cursor-pointer @error('keputusan') border-danger @enderror" style="cursor:pointer;">
                                <div class="d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="radio" name="keputusan" id="kSetuju" value="disetujui" {{ old('keputusan') === 'disetujui' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-success" for="kSetuju">
                                        <i class="bi bi-check-circle-fill me-1"></i>DISETUJUI — Diterima menjadi anggota
                                    </label>
                                </div>
                            </label>
                            <label class="form-check border border-danger rounded px-3 py-2 shadow-sm cursor-pointer @error('keputusan') border-danger @enderror" style="cursor:pointer;">
                                <div class="d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="radio" name="keputusan" id="kTolak" value="ditolak" {{ old('keputusan') === 'ditolak' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-danger" for="kTolak">
                                        <i class="bi bi-x-circle-fill me-1"></i>DITOLAK — Tidak dapat diterima saat ini
                                    </label>
                                </div>
                            </label>
                        </div>
                        @error('keputusan')<div class="invalid-feedback d-block small mt-1"><small>{{ $message }}</small></div>@enderror
                    </div>

                    <div id="panelSetuju" style="display:none;">
                        <div class="mb-3">
                            <label for="no_anggota_baru" class="form-label fw-semibold small">Nomor Anggota Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                                <input type="text"
                                    id="no_anggota_baru"
                                    name="no_anggota_baru"
                                    maxlength="30"
                                    value="{{ old('no_anggota_baru', $noAnggotaSuggest) }}"
                                    class="form-control @error('no_anggota_baru') is-invalid @enderror"
                                    placeholder="Contoh: ANG-2026-00001">
                            </div>
                            @error('no_anggota_baru')<div class="invalid-feedback d-block small mt-1"><small>{{ $message }}</small></div>@enderror
                            <small class="form-text mt-1 d-block">Nomor anggota bersifat unik, disarankan format: ANG-[TAHUN]-[NOMOR URUT].</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_default" class="form-label fw-semibold small">Kata Sandi Default untuk Anggota <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="text"
                                    id="password_default"
                                    name="password_default"
                                    maxlength="32"
                                    value="{{ old('password_default', 'Anggota123!') }}"
                                    class="form-control @error('password_default') is-invalid @enderror"
                                    placeholder="Min. 6 karakter">
                                <button type="button" id="btnGenPw" class="btn btn-outline-primary border-start-0" style="border-color:#cbd5e1;">
                                    <i class="bi bi-dice-5 me-1"></i>Buat Acak
                                </button>
                            </div>
                            @error('password_default')<div class="invalid-feedback d-block small mt-1"><small>{{ $message }}</small></div>@enderror
                            <small class="form-text mt-1 d-block">Kata sandi ini akan dikirim melalui email ke anggota bersama dengan instruksi login & wajib diganti saat masuk pertama. (akan diberlakukan force change password)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan_verifikasi_pendaftaran" class="form-label fw-semibold small">
                            Catatan Verifikasi
                            <span class="text-danger">*</span>
                            <span class="text-muted fw-normal ms-1">(akan dikirim via email kepada anggota)</span>
                        </label>
                        <textarea name="catatan_verifikasi_pendaftaran" id="catatan_verifikasi_pendaftaran" rows="4" maxlength="600"
                            class="form-control @error('catatan_verifikasi_pendaftaran') is-invalid @enderror"
                            placeholder="Jika disetujui: ucapkan selamat datang & jelaskan langkah selanjutnya. Jika ditolak: jelaskan secara sopan alasan penolakan serta apa yang perlu diperbaiki jika ingin mendaftar kembali.">{{ old('catatan_verifikasi_pendaftaran') }}</textarea>
                        <div class="d-flex justify-content-end mt-1">
                            <small class="form-text"><span id="counterCatatan">0</span>/600 karakter</small>
                        </div>
                        @error('catatan_verifikasi_pendaftaran')<div class="invalid-feedback d-block small"><small>{{ $message }}</small></div>@enderror
                    </div>

                    <div class="alert alert-info small py-2" role="alert">
                        <i class="bi bi-info-circle me-1"></i>
                        Setelah klik tombol di bawah ini, sistem akan: <b>(1)</b> memperbarui status anggota, <b>(2)</b> mengirimkan notifikasi hasil ke email anggota beserta data login jika disetujui, dan <b>(3)</b> mencatat aktivitas log.
                    </div>
                </div>
                <div class="card-footer bg-white d-flex flex-column flex-sm-row gap-2 justify-content-end py-3 border-0">
                    <a href="{{ route('anggota.pendaftaran.menunggu') }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-orange text-white px-5 shadow-sm">
                        <i class="bi bi-send-check me-1"></i>Simpan & Kirim Hasil
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endpush
@push('scripts')
<script>
(function(){
    function updatePanel() {
        const val = document.querySelector('input[name="keputusan"]:checked')?.value || '';
        const panel = document.getElementById('panelSetuju');
        if (val === 'disetujui') {
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    }
    document.addEventListener('DOMContentLoaded', function(){
        const radios = document.querySelectorAll('input[name="keputusan"]');
        radios.forEach(r => r.addEventListener('change', updatePanel));
        updatePanel();
        const txt = document.getElementById('catatan_verifikasi_pendaftaran');
        const counter = document.getElementById('counterCatatan');
        function upd() { counter.textContent = String((txt.value || '').length); }
        txt.addEventListener('input', upd);
        upd();
        const btn = document.getElementById('btnGenPw');
        const pwInput = document.getElementById('password_default');
        btn.addEventListener('click', function() {
            const pool = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            const punc = '!@#%&+=';
            let s = '';
            for (let i=0;i<10;i++) s += pool.charAt(Math.floor(Math.random()*pool.length));
            s += punc.charAt(Math.floor(Math.random()*punc.length));
            pwInput.value = s;
        });
    });
})();
</script>
@endpush
