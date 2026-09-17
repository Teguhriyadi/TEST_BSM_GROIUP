<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>
        {{ config('app.name') }} - @stack("title")
    </title>

    @include("modules.layouts.components.style.css")

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        @include("modules.layouts.components.views.sidebar")

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                @include("modules.layouts.components.views.navbar")

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @stack("page-modules")
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            @include("modules.layouts.components.views.footer")

        </div>
    </div>
    <!-- End of Page Wrapper -->

    @if (auth()->check() && auth()->user()->force_change_password)
        <div class="modal fade" id="modalUbahPasswordPertama" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalUbahPasswordPertamaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('password.updateSelf') }}" method="POST">
                        @csrf
                        <div class="modal-header bg-gradient-orange text-white">
                            <h5 class="modal-title fw-bold" id="modalUbahPasswordPertamaLabel">
                                <i class="bi bi-shield-lock me-2"></i>Ubah Password Anda
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning small mb-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                Ini adalah login pertama Anda menggunakan akun ini. Demi keamanan, Anda <b>wajib mengganti password</b> sebelum dapat melanjutkan.
                            </div>

                            <div class="mb-3">
                                <label for="password_lama" class="form-label fw-semibold small">Password Saat Ini</label>
                                <input type="password" name="password_lama" id="password_lama" class="form-control @error('password_lama') is-invalid @enderror" placeholder="Masukkan password saat ini (default: password)">
                                @error('password_lama')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_baru" class="form-label fw-semibold small">Password Baru</label>
                                <input type="password" name="password_baru" id="password_baru" class="form-control @error('password_baru') is-invalid @enderror" placeholder="Masukkan password baru (min. 6 karakter)">
                                @error('password_baru')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_baru_confirmation" class="form-label fw-semibold small">Konfirmasi Password Baru</label>
                                <input type="password" name="password_baru_confirmation" id="password_baru_confirmation" class="form-control @error('password_baru_confirmation') is-invalid @enderror" placeholder="Ulangi password baru di atas">
                                @error('password_baru_confirmation')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-orange text-white px-4 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i>Simpan Password Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include("modules.layouts.components.views.modal")

    @include("modules.layouts.components.style.js")

    @if (auth()->check() && auth()->user()->force_change_password)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const el = document.getElementById('modalUbahPasswordPertama');
                if (el && window.bootstrap && bootstrap.Modal) {
                    const m = new bootstrap.Modal(el);
                    m.show();
                }
            });
        </script>
    @endif

    @stack('scripts')

</body>

</html>
