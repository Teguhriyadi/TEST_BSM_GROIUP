<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Pendaftaran Anggota Baru - Sistem Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #c2410c 100%);
            min-height: 100vh;
            padding: 1.5rem 0;
            overflow-x: hidden;
        }

        .register-card {
            border: none;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .brand-section {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            min-height: 100%;
        }

        .form-section {
            padding: 2rem 2.5rem 2.5rem 2.5rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #334155;
            margin-bottom: 0.35rem;
        }

        .form-control,
        .form-select {
            padding: 0.7rem 0.9rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            font-size: 0.93rem;
            background-color: #ffffff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
        }

        .form-text {
            font-size: 0.78rem;
            color: #64748b;
            line-height: 1.35;
        }

        .input-group-text {
            background-color: transparent;
            border-right: none;
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
            color: #64748b;
        }

        .input-group .form-control {
            border-left: none;
        }

        .btn-orange {
            background-color: #f97316;
            border: none;
            color: white;
            padding: 0.85rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            width: 100%;
        }

        .btn-orange:hover:not(:disabled) {
            background-color: #ea580c;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .btn-orange:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .logo-icon {
            font-size: 3rem;
            color: #f97316;
        }

        .step-indicator {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 1.5rem;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            background-color: #0284c7;
            color: white;
            border-radius: 0.35rem;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .step-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #0f172a;
        }

        .divider-text {
            position: relative;
            text-align: center;
            margin: 1.25rem 0;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .invalid-feedback {
            font-size: 0.78rem;
            margin-top: 0.25rem;
        }

        .password-strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.35rem;
            overflow: hidden;
            background-color: #e2e8f0;
        }

        .password-strength-bar > div {
            height: 100%;
            width: 0%;
            transition: all 0.2s ease;
        }

        @media (max-width: 991.98px) {
            body {
                padding: 0.75rem;
            }
            .brand-section {
                padding: 1.75rem 1.25rem;
                text-align: center;
            }
            .form-section {
                padding: 1.5rem 1.1rem 2rem 1.1rem;
            }
        }

        .checkbox-label {
            font-size: 0.85rem;
            color: #334155;
            font-weight: 500;
            cursor: pointer;
        }

        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 0.75rem 1rem;
            border-radius: 0.35rem;
            margin-bottom: 1.25rem;
        }

        .info-box p {
            margin: 0;
            font-size: 0.8rem;
            color: #075985;
            line-height: 1.5;
        }

        .form-check-input {
            width: 1.1rem;
            height: 1.1rem;
            border-color: #cbd5e1;
        }

        .form-check-input:checked {
            background-color: #0284c7;
            border-color: #0284c7;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-11 col-xxl-10">
                <div class="card register-card">
                    <div class="row g-0">
                        <div class="col-lg-5 brand-section">
                            <div class="mb-4">
                                <i class="bi bi-wallet2 logo-icon mb-3"></i>
                                <h2 class="fw-bold mb-2">Daftar Anggota</h2>
                                <h3 class="fw-bold mb-4 text-warning">Koperasi Simpan Pinjam</h3>
                                <p class="text-white-50 mb-4" style="line-height:1.6;">
                                    Bergabung bersama kami dan nikmati kemudahan layanan simpanan, pinjaman, dan angsuran
                                    dengan suku bunga kompetitif serta proses yang transparan.
                                </p>
                            </div>

                            <div class="mt-auto">
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="step-number">1</div>
                                    <div>
                                        <div class="step-title text-white">Isi Formulir Pendaftaran</div>
                                        <small class="text-white-50" style="font-size:0.75rem">Data yang valid mempercepat proses verifikasi.</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3 mb-3">
                                    <div class="step-number" style="background-color:#f97316;">2</div>
                                    <div>
                                        <div class="step-title text-white">Petugas Cabang Memverifikasi</div>
                                        <small class="text-white-50" style="font-size:0.75rem">Anda akan menerima email notifikasi hasilnya.</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-3">
                                    <div class="step-number" style="background-color:#22c55e;">3</div>
                                    <div>
                                        <div class="step-title text-white">Aktivasi & Layanan Siap Digunakan</div>
                                        <small class="text-white-50" style="font-size:0.75rem">No. Anggota otomatis terbit saat disetujui.</small>
                                    </div>
                                </div>
                                <div class="mt-5 pt-3 d-none d-lg-block">
                                    <small class="text-white-50">Sudah punya akun?</small><br>
                                    <a href="{{ route('login') }}" class="text-warning fw-semibold text-decoration-none link-offset-2">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk ke Akun Anda
                                    </a>
                                    <div class="mt-4">
                                        <small class="text-white-50">&copy; 2026 Sistem Koperasi. Hak Cipta Dilindungi.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 form-section">
                            <div class="mb-3 d-lg-none">
                                <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none link-offset-2 small">
                                    <i class="bi bi-arrow-left me-1"></i>Sudah punya akun? Masuk di sini
                                </a>
                            </div>

                            <div class="mb-4">
                                <h3 class="fw-bold text-dark mb-1">Formulir Pendaftaran</h3>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">
                                    Lengkapi data di bawah ini. Formulir ini hanya untuk calon anggota baru.
                                </p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                                    <small><b>Gagal memproses pendaftaran.</b><br>Silakan perbaiki kesalahan pada kolom yang ditandai merah di bawah ini.</small>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close" style="font-size:0.75rem;padding:0.5rem;"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                                    <small>{{ session('error') }}</small>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('anggota.register.store') }}" method="POST" novalidate>
                                @csrf

                                <div class="step-indicator">
                                    <span class="step-number">A</span>
                                    <span class="step-title">Informasi Cabang & Akun Login</span>
                                </div>

                                <div class="info-box">
                                    <p><i class="bi bi-info-circle me-1"></i> Pilih cabang tempat Anda berencana bertransaksi. Email dan kata sandi yang Anda buat di bawah ini akan digunakan untuk masuk ke sistem <b>setelah</b> pendaftaran Anda disetujui petugas.</p>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12">
                                        <label for="cabang_id" class="form-label">Cabang Pendaftaran <span class="text-danger">*</span></label>
                                        <select name="cabang_id" id="cabang_id" class="form-select @error('cabang_id') is-invalid @enderror">
                                            <option value="">-- Pilih Kantor Cabang --</option>
                                            @foreach($cabang as $c)
                                            <option value="{{ $c->id }}" {{ old('cabang_id') == $c->id ? 'selected' : '' }}>
                                                {{ $c->nama_cabang }} — {{ $c->alamat ?? 'Alamat tersedia di kantor cabang' }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('cabang_id')
                                        <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                                        @enderror
                                    </div>

                                    <div class="col-md-7 col-lg-7">
                                        <label for="email" class="form-label">Alamat Email Aktif <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" id="email" name="email" maxlength="100"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email') }}" placeholder="Contoh: nama.anda@gmail.com" autocomplete="email">
                                        </div>
                                        @error('email')
                                        <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                                        @enderror
                                        <div class="form-text mt-1"><small>Notifikasi hasil verifikasi (disetujui / ditolak) akan dikirim ke alamat ini.</small></div>
                                    </div>

                                    <div class="col-md-5 col-lg-5">
                                        <label for="no_hp" class="form-label">Nomor Handphone <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                            <input type="tel" id="no_hp" name="no_hp" maxlength="15"
                                                class="form-control @error('no_hp') is-invalid @enderror"
                                                value="{{ old('no_hp') }}" placeholder="Contoh: 0812xxxxxxxx" autocomplete="tel">
                                        </div>
                                        @error('no_hp')
                                        <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 col-lg-6">
                                        <label for="password" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" id="password" name="password" maxlength="32"
                                                class="form-control @error('password') is-invalid @enderror"
                                                placeholder="Min. 8 karakter: huruf besar, kecil, dan angka" autocomplete="new-password">
                                            <button type="button" class="btn btn-outline-secondary border-start-0"
                                                id="togglePassword" style="border-color: #cbd5e1;">
                                                <i class="bi bi-eye" id="toggleIconPw"></i>
                                            </button>
                                        </div>
                                        <div class="password-strength-bar" aria-hidden="true">
                                            <div id="pwStrength" style="background-color:#e2e8f0;"></div>
                                        </div>
                                        <small id="pwStrengthLabel" class="form-text d-block mt-1">-</small>
                                        @error('password')
                                        <div class="invalid-feedback d-block mt-1"><small>{{ $message }}</small></div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 col-lg-6">
                                        <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                            <input type="password" id="password_confirmation" name="password_confirmation" maxlength="32"
                                                class="form-control @error('password_confirmation') is-invalid @enderror"
                                                placeholder="Ulangi kata sandi di atas" autocomplete="new-password">
                                            <button type="button" class="btn btn-outline-secondary border-start-0"
                                                id="togglePassword2" style="border-color: #cbd5e1;">
                                                <i class="bi bi-eye" id="toggleIconPw2"></i>
                                            </button>
                                        </div>
                                        @error('password_confirmation')
                                        <div class="invalid-feedback d-block mt-1"><small>{{ $message }}</small></div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="step-indicator">
                                    <span class="step-number" style="background-color:#f97316;">B</span>
                                    <span class="step-title">Data Pribadi Sesuai KTP</span>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6 col-lg-6">
                                        <label for="nik" class="form-label">Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                            <input type="text" id="nik" name="nik" maxlength="16" inputmode="numeric" pattern="[0-9]*"
                                                class="form-control @error('nik') is-invalid @enderror"
                                                value="{{ old('nik') }}" placeholder="16 digit angka sesuai KTP">
                                        </div>
                                        @error('nik')
                                        <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 col-lg-6">
                                        <label for="nama" class="form-label">Nama Lengkap (Sesuai KTP) <span class="text-danger">*</span></label>
                                        <input type="text" id="nama" name="nama" maxlength="100"
                                            class="form-control @error('nama') is-invalid @enderror"
                                            value="{{ old('nama') }}" placeholder="Contoh: Budi Santoso">
                                        @error('nama')
                                        <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 col-lg-4">
                                        <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-4 align-items-center pt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jkL" value="L" {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }}>
                                                <label class="form-check-label checkbox-label" for="jkL">Laki-laki</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jkP" value="P" {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}>
                                                <label class="form-check-label checkbox-label" for="jkP">Perempuan</label>
                                            </div>
                                        </div>
                                        @error('jenis_kelamin')
                                        <div class="invalid-feedback d-block mt-1"><small>{{ $message }}</small></div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 col-lg-4">
                                        <label for="tgl_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                        <input type="date" id="tgl_lahir" name="tgl_lahir"
                                            class="form-control @error('tgl_lahir') is-invalid @enderror"
                                            value="{{ old('tgl_lahir') }}" max="{{ date('Y-m-d', strtotime('-17 years')) }}">
                                        @error('tgl_lahir')
                                        <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                                        @enderror
                                        <div class="form-text mt-1"><small>Usia minimal 17 tahun pada hari pendaftaran.</small></div>
                                    </div>

                                    <div class="col-md-4 col-lg-4 d-none d-md-block"></div>

                                    <div class="col-12">
                                        <label for="alamat" class="form-label">Alamat Lengkap Sesuai KTP <span class="text-danger">*</span></label>
                                        <textarea id="alamat" name="alamat" rows="3" maxlength="500"
                                            class="form-control @error('alamat') is-invalid @enderror"
                                            placeholder="Tuliskan alamat lengkap: RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten, Provinsi">{{ old('alamat') }}</textarea>
                                        <div class="d-flex justify-content-end mt-1">
                                            <small class="form-text"><span id="alamatCounter">0</span>/500 karakter</small>
                                        </div>
                                        @error('alamat')
                                        <div class="invalid-feedback d-block"><small>{{ $message }}</small></div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="persetujuan_syarat" id="persetujuan_syarat" value="1" {{ old('persetujuan_syarat') ? 'checked' : '' }}>
                                        <label class="form-check-label checkbox-label" for="persetujuan_syarat">
                                            Saya telah membaca dan menyetujui seluruh <b>Syarat & Ketentuan Pendaftaran</b> serta <b>Kebijakan Privasi</b> Koperasi Simpan Pinjam. Data yang saya isikan adalah benar dan dapat dipertanggungjawabkan.
                                        </label>
                                    </div>
                                    @error('persetujuan_syarat')
                                    <div class="invalid-feedback d-block mt-1"><small>{{ $message }}</small></div>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2 mb-3">
                                    <button type="submit" id="btnSubmit" class="btn btn-orange shadow-sm">
                                        <i class="bi bi-send me-1"></i>Ajukan Pendaftaran Anggota
                                    </button>
                                </div>

                                <div class="divider-text d-none d-md-block">
                                    — Atau —
                                </div>

                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                                    <small class="text-muted">
                                        <i class="bi bi-shield-lock text-primary me-1"></i>
                                        Data Anda dienkripsi & hanya dapat diakses oleh petugas yang berwenang.
                                    </small>
                                    <div class="d-lg-none">
                                        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none link-offset-2 small">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Masuk ke Akun
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (function(){
            const togglePw = document.getElementById('togglePassword');
            const togglePw2 = document.getElementById('togglePassword2');
            const pw = document.getElementById('password');
            const pw2 = document.getElementById('password_confirmation');
            const iconPw = document.getElementById('toggleIconPw');
            const iconPw2 = document.getElementById('toggleIconPw2');
            const strengthBar = document.getElementById('pwStrength');
            const strengthLabel = document.getElementById('pwStrengthLabel');
            const nikInput = document.getElementById('nik');
            const noHpInput = document.getElementById('no_hp');
            const alamatInput = document.getElementById('alamat');
            const alamatCounter = document.getElementById('alamatCounter');
            const btnSubmit = document.getElementById('btnSubmit');

            function toggle(btn, input, icon) {
                btn.addEventListener('click', () => {
                    const t = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', t);
                    if (t === 'text') {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                });
            }
            toggle(togglePw, pw, iconPw);
            toggle(togglePw2, pw2, iconPw2);

            function strengthScore(val) {
                let s = 0;
                if (val.length >= 8) s += 1;
                if (val.length >= 12) s += 1;
                if (/[a-z]/.test(val) && /[A-Z]/.test(val)) s += 1;
                if (/[0-9]/.test(val)) s += 1;
                if (/[^a-zA-Z0-9]/.test(val)) s += 1;
                return s;
            }
            function updateStrength() {
                const v = pw.value || '';
                const score = strengthScore(v);
                if (v === '') {
                    strengthBar.style.width = '0%';
                    strengthBar.style.backgroundColor = '#e2e8f0';
                    strengthLabel.textContent = '-';
                    return;
                }
                let label, color, width;
                if (score <= 1) { label = 'Kekuatan: LEMAH (perbaiki)'; color = '#ef4444'; width = '25%'; }
                else if (score === 2) { label = 'Kekuatan: CUKUP (disarankan lebih kuat)'; color = '#f59e0b'; width = '50%'; }
                else if (score === 3) { label = 'Kekuatan: BAIK'; color = '#0ea5e9'; width = '75%'; }
                else { label = 'Kekuatan: SANGAT BAIK'; color = '#22c55e'; width = '100%'; }
                strengthBar.style.width = width;
                strengthBar.style.backgroundColor = color;
                strengthLabel.textContent = label;
            }
            pw.addEventListener('input', updateStrength);
            updateStrength();

            nikInput.addEventListener('input', () => {
                nikInput.value = (nikInput.value || '').replace(/[^0-9]/g, '').slice(0, 16);
            });

            noHpInput.addEventListener('input', () => {
                noHpInput.value = (noHpInput.value || '').replace(/[^0-9+]/g, '').slice(0, 15);
            });

            function updateAlamatCounter() {
                alamatCounter.textContent = String((alamatInput.value || '').length);
            }
            alamatInput.addEventListener('input', updateAlamatCounter);
            updateAlamatCounter();

            const form = document.querySelector('form');
            form.addEventListener('submit', (ev) => {
                const ok = form.checkValidity();
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Memproses...';
                if (!ok) {
                    ev.preventDefault();
                    setTimeout(() => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="bi bi-send me-1"></i>Ajukan Pendaftaran Anggota';
                    }, 500);
                    const firstInvalid = form.querySelector('.is-invalid, input:invalid, select:invalid, textarea:invalid');
                    if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
                return true;
            });
        })();
    </script>
</body>

</html>
