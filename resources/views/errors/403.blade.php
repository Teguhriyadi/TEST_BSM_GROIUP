@extends('modules.layouts.master')
@push('title', '403 Akses Ditolak')
@push('page-modules')

<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-6">
        <div class="card shadow mb-4">
            <div class="card-body py-5 px-4 px-sm-5 text-center">
                <div class="mb-4">
                    <h1 class="display-4 fw-bold text-orange mb-0">403</h1>
                </div>
                <h5 class="h5 fw-semibold text-gray-800 mb-2">Akses Ditolak</h5>
                <p class="text-muted mb-4">
                    {{ $message ?? 'Anda tidak memiliki izin untuk mengakses halaman atau melakukan aksi pada menu ini.' }}
                </p>
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-orange text-white fw-semibold px-4">
                        Kembali ke Dashboard
                    </a>
                    <button type="button" onclick="history.back()" class="btn btn-outline-secondary fw-semibold px-4">
                        Kembali ke Halaman Sebelumnya
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endpush
