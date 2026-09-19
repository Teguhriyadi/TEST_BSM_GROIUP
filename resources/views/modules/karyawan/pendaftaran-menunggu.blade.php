@extends('modules.layouts.master')
@push('title', 'Pendaftaran Anggota Menunggu Verifikasi')
@push('page-modules')

<div class="d-sm-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Pendaftaran Baru Menunggu Verifikasi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none link-primary">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pendaftaran Menunggu Verifikasi</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="{{ route('anggota.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Anggota
        </a>
    </div>
</div>

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
    <small>{!! session('success') !!}</small>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if (session('warning'))
<div class="alert alert-warning alert-dismissible fade show py-2 mb-3" role="alert">
    <small>{!! session('warning') !!}</small>
    @if (session('catatan_penolakan'))
    <div class="mt-1"><small><b>Catatan penolakan:</b> {{ session('catatan_penolakan') }}</small></div>
    @endif
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
    <small>{{ session('error') }}</small>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            Daftar Calon Anggota
            @if(isset($dataAnggota) && $dataAnggota->count())
                <span class="badge bg-orange text-white ms-2 small">{{ $dataAnggota->count() }} Menunggu</span>
            @endif
        </h6>
    </div>
    <div class="card-body">
        @if(!isset($dataAnggota) || $dataAnggota->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox text-secondary" style="font-size: 3.5rem;"></i>
                <p class="mt-3 mb-0 fw-semibold">Belum ada pendaftaran anggota baru yang menunggu verifikasi.</p>
                <small class="text-muted">Pendaftaran anggota baru via halaman "Daftar sebagai Anggota" akan muncul di daftar ini untuk diperiksa petugas.</small>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>No</th>
                        <th>Waktu Daftar</th>
                        <th>Cabang</th>
                        <th>NIK</th>
                        <th>Nama Lengkap</th>
                        <th>L/P</th>
                        <th>No. HP</th>
                        <th>Email (Login)</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataAnggota as $a)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="d-none">{{ $a->created_at }}</span>{{ $a->created_at ? \Carbon\Carbon::parse($a->created_at)->translatedFormat('d M Y, H:i') : '-' }}</td>
                        <td>{{ $a->cabang->nama_cabang ?? '-' }}</td>
                        <td><code class="text-primary" style="background:#f0f9ff;padding:0.1rem 0.25rem;border-radius:4px;">{{ $a->nik }}</code></td>
                        <td class="fw-semibold">{{ $a->nama }}</td>
                        <td>{{ $a->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td>{{ $a->no_hp ?? '-' }}</td>
                        <td>
                            @if($a->user)
                                <span class="text-primary">{{ $a->user->email }}</span>
                                <br><small class="badge bg-secondary">Siap login jika disetujui</small>
                            @else
                                <span class="text-muted">(tidak ada akun)</span>
                            @endif
                        </td>
                        <td>
                            @if($a->tgl_lahir)
                                {{ \Carbon\Carbon::parse($a->tgl_lahir)->translatedFormat('d M Y') }}
                                <br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($a->tgl_lahir)->age }} tahun</small>
                            @else
                                -
                            @endif
                        </td>
                        <td><small>{{ Str::limit(e($a->alamat), 80, '...') }}</small></td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('anggota.pendaftaran.formverifikasi', $a->id) }}" class="btn btn-orange btn-sm text-white">
                                    <i class="bi bi-clipboard-check me-1"></i>Verifikasi
                                </a>
                                @if($a->user && $a->user->email)
                                    <a href="mailto:{{ $a->user->email }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-envelope me-1"></i>Hubungi Email
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endpush
