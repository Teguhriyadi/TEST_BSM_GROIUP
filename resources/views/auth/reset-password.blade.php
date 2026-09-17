<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Sistem Koperasi Simpan Pinjam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #c2410c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .login-card {
            border: none;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .brand-section {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .form-section {
            padding: 3rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15);
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

        .input-group .form-control:focus+.input-group-text {
            border-color: #0284c7;
        }

        .btn-orange {
            background-color: #f97316;
            border: none;
            color: white;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
        }

        .btn-orange:hover {
            background-color: #ea580c;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .logo-icon {
            font-size: 3.5rem;
            color: #f97316;
        }

        @media (max-width: 991.98px) {
            .brand-section {
                padding: 2rem;
                text-align: center;
            }

            .form-section {
                padding: 2rem;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card login-card">
                    <div class="row g-0">
                        <div class="col-lg-5 brand-section">
                            <div class="mb-4">
                                <i class="bi bi-wallet2 logo-icon mb-3"></i>
                                <h2 class="fw-bold mb-2">Koperasi</h2>
                                <h3 class="fw-bold mb-4 text-warning">Simpan Pinjam</h3>
                                <p class="text-white-50 mb-0">Kelola simpanan, pinjaman, dan angsuran anggota dengan
                                    aman, mudah, dan efisien.</p>
                            </div>
                            <div class="mt-auto d-none d-lg-block">
                                <small class="text-white-50">&copy; 2026 Sistem Koperasi. Hak Cipta Dilindungi.</small>
                            </div>
                        </div>
                        <div class="col-lg-7 form-section">
                            <div class="mb-4">
                                <h3 class="fw-bold text-dark mb-1">Reset Password</h3>
                                <p class="text-muted text-sm">Silakan masukkan password baru Anda untuk mengganti password lama.</p>
                            </div>

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                                    <small>{{ session('error') }}</small>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                                    <small>{{ session('success') }}</small>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('password.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="mb-3">
                                    <label for="email"
                                        class="form-label fw-semibold text-secondary small">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $email) }}"
                                            placeholder="Masukkan email terdaftar">
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">
                                            <small>{{ $message }}</small>
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_baru"
                                        class="form-label fw-semibold text-secondary small">Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password"
                                            class="form-control @error('password_baru') is-invalid @enderror" id="password_baru"
                                            name="password_baru" placeholder="Minimal 6 karakter">
                                        <button class="btn btn-outline-secondary border-start-0" type="button"
                                            id="togglePasswordBaru" style="border-color: #cbd5e1;">
                                            <i class="bi bi-eye" id="toggleIconBaru"></i>
                                        </button>
                                    </div>
                                    @error('password_baru')
                                        <div class="invalid-feedback d-block">
                                            <small>{{ $message }}</small>
                                        </div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="password_baru_confirmation"
                                        class="form-label fw-semibold text-secondary small">Konfirmasi Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password"
                                            class="form-control @error('password_baru_confirmation') is-invalid @enderror" id="password_baru_confirmation"
                                            name="password_baru_confirmation" placeholder="Ketik ulang password baru">
                                        <button class="btn btn-outline-secondary border-start-0" type="button"
                                            id="togglePasswordKonfirm" style="border-color: #cbd5e1;">
                                            <i class="bi bi-eye" id="toggleIconKonfirm"></i>
                                        </button>
                                    </div>
                                    @error('password_baru_confirmation')
                                        <div class="invalid-feedback d-block">
                                            <small>{{ $message }}</small>
                                        </div>
                                    @enderror
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-orange btn-lg shadow-sm">Simpan Password Baru</button>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('login') }}" class="small text-decoration-none fw-medium link-primary">
                                        <i class="bi bi-arrow-left"></i> Kembali ke Halaman Login
                                    </a>
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
        function bindToggle(btnId, inputId, iconId) {
            const btn = document.getElementById(btnId);
            if (!btn) return;
            btn.addEventListener('click', function() {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                if (type === 'text') {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        }
        bindToggle('togglePasswordBaru', 'password_baru', 'toggleIconBaru');
        bindToggle('togglePasswordKonfirm', 'password_baru_confirmation', 'toggleIconKonfirm');
    </script>
</body>

</html>
