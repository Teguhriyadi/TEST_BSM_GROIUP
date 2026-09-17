@extends('modules.layouts.master')
@push('title', 'Detail Pengajuan Pinjaman')
@push('page-modules')

@php
    use Illuminate\Support\Facades\Auth;
    $bisaVerifikasiDokumen = Auth::check() && Auth::user()->hasPermission('PINJAMAN_DOKUMEN_VERIFIKASI');
    $bisaUploadDokumen = Auth::check() && (Auth::user()->hasPermission('PINJAMAN_DOKUMEN_UPLOAD') || Auth::user()->hasPermission('ANGGOTA_DOKUMEN_UPLOAD'));
    $bisaVerifikasiPengajuan = Auth::check() && Auth::user()->hasPermission('PINJAMAN_APPROVE');
    $dapatDiverifikasi = $pinjaman->dapatDiverifikasi();
    $hambatanVerifikasi = $pinjaman->hambatan_verifikasi;
@endphp

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Pengajuan Pinjaman</h1>
        <div class="small text-muted mt-1">Nomor Pinjaman: {{ $pinjaman->nomor_pinjaman }}</div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pinjaman.index') }}">Data Pinjaman</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

@php
    $statusPinjaman = $pinjaman->status;
    $statusPinjamanClass = 'secondary';
    if (in_array($statusPinjaman, ['disetujui', 'lunas'], true)) $statusPinjamanClass = 'success';
    elseif (in_array($statusPinjaman, ['diajukan', 'diverifikasi'], true)) $statusPinjamanClass = 'info';
    elseif (in_array($statusPinjaman, ['dicairkan', 'berjalan'], true)) $statusPinjamanClass = 'warning';
    elseif (in_array($statusPinjaman, ['ditolak', 'dibatalkan'], true)) $statusPinjamanClass = 'danger';
@endphp

<div class="row mb-4">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    Informasi Pengajuan
                </h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-{{ $statusPinjamanClass }} py-1 px-3 text-white">
                        {{ ucwords(str_replace('_', ' ', $statusPinjaman)) }}
                    </span>
                    @haspermission('PINJAMAN_UPDATE')
                    <a href="{{ route('pinjaman.edit', $pinjaman->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-pencil-square me-1"></i> Ubah Data
                    </a>
                    @endhaspermission
                    <a href="{{ route('pinjaman.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Pinjaman
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Anggota</div>
                        <div class="fw-semibold text-gray-800">{{ $pinjaman->anggota->nama ?? '-' }} <small class="text-muted">({{ $pinjaman->anggota->no_anggota ?? '-' }})</small></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $pinjaman->cabang->nama_cabang ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jenis Pinjaman</div>
                        <div class="fw-semibold text-gray-800">{{ $pinjaman->jenisPinjaman->nama_jenis ?? '-' }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Jumlah Pinjaman</div>
                        <div class="fw-semibold text-orange">Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tenor</div>
                        <div class="fw-semibold text-gray-800">{{ $pinjaman->tenor }} Bulan</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Bunga Tahunan</div>
                        <div class="fw-semibold text-gray-800">{{ $pinjaman->bunga }}%</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Angsuran Per Bulan</div>
                        <div class="fw-semibold text-gray-800">Rp {{ number_format($pinjaman->angsuran_per_bulan, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Tanggal Pengajuan</div>
                        <div class="fw-semibold text-gray-800">@tanggal($pinjaman->tgl_pengajuan)</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="small text-muted">Progress Verifikasi Dokumen</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="fw-semibold text-gray-800">{{ $pinjaman->progress_label }}</div>
                            <div class="progress bg-light border flex-grow-1" style="height: 8px;">
                                <div class="progress-bar {{ $pinjaman->persentase_progress_dokumen >= 100 ? 'bg-success' : ($pinjaman->persentase_progress_dokumen > 0 ? 'bg-primary' : 'bg-secondary') }}"
                                     role="progressbar"
                                     style="width: {{ $pinjaman->persentase_progress_dokumen }}%;"
                                     aria-valuenow="{{ $pinjaman->persentase_progress_dokumen }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    @if(! empty($pinjaman->tujuan))
                        <div class="col-12">
                            <div class="small text-muted">Tujuan</div>
                            <div class="fw-medium text-gray-800">{{ $pinjaman->tujuan }}</div>
                        </div>
                    @endif
                </div>

                @if($bisaVerifikasiPengajuan)
                    <hr class="my-4">
                    @if($hambatanVerifikasi !== [])
                        <div class="alert alert-warning mb-0">
                            <div class="fw-semibold mb-2">Pengajuan belum bisa diverifikasi ke tahap selanjutnya, karena hambatan berikut:</div>
                            <ul class="mb-0 ps-3">
                                @foreach($hambatanVerifikasi as $h)
                                    <li class="mb-1">{{ $h }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @elseif($dapatDiverifikasi)
                        <form method="POST" action="{{ route('pinjaman.verifikasi', $pinjaman->id) }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-0">
                            @csrf
                            <div>
                                <div class="fw-semibold text-success mb-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    Seluruh dokumen wajib sudah disetujui.
                                </div>
                                <div class="small text-muted">Anda dapat memverifikasi pengajuan ini sehingga status berubah menjadi "Diverifikasi" dan siap memasuki tahap persetujuan.</div>
                            </div>
                            <button type="submit" class="btn btn-orange text-white px-4">
                                <i class="bi bi-shield-check me-1"></i> Verifikasi Pengajuan
                            </button>
                        </form>
                    @elseif($pinjaman->status !== 'diajukan')
                        <div class="alert alert-info mb-0">
                            Status pengajuan saat ini adalah <strong>{{ ucwords(str_replace('_', ' ', $pinjaman->status)) }}</strong>. Verifikasi pengajuan hanya dapat dilakukan ketika status masih "Diajukan" dan seluruh dokumen wajib disetujui.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-files me-1"></i> Persyaratan & Unggah Dokumen
                </h6>
                <div class="small text-muted">
                    Format yang diperbolehkan: JPG, JPEG, PNG, PDF (Maks. 10 MB)
                </div>
            </div>
            <div class="card-body">
                @if($dokumenWajibList->count() === 0)
                    <div class="alert alert-warning mb-0">
                        Jenis pinjaman ini belum memiliki daftar persyaratan dokumen wajib. Silakan hubungi administrator untuk mengatur persyaratan dokumen pada master jenis pinjaman.
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($dokumenWajibList as $dok)
                            @php
                                $master = $dok->masterDokumen;
                                $status = $dok->status;
                                $warnaStatus = $dok->warna_status;
                                $labelStatus = $dok->label_status;
                                $urlFile = $dok->url_file;
                                $dapatUnggah = $bisaUploadDokumen && $dok->dapat_diunggah_ulang;
                                $format = $master?->format_diperbolehkan ?? 'jpg,jpeg,png,pdf';
                            @endphp
                            <div class="col-xl-6">
                                <div class="card border border-gray-200 h-100">
                                    <div class="card-header py-2 d-flex align-items-center justify-content-between bg-white border-bottom">
                                        <div>
                                            <div class="fw-semibold text-gray-800">{{ $master?->nama_dokumen ?? 'Dokumen Persyaratan' }}</div>
                                            <div class="small text-muted">{{ $master?->deskripsi ?? 'Dokumen persyaratan wajib' }}</div>
                                        </div>
                                        <span class="badge bg-{{ $warnaStatus }} text-white py-1 px-3">{{ $labelStatus }}</span>
                                    </div>
                                    <div class="card-body p-3">
                                        @if($urlFile)
                                            <div class="mb-3 border rounded p-2 bg-gray-50">
                                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                    <div class="text-truncate small fw-medium text-gray-800">
                                                        <i class="bi bi-file-earmark me-1"></i>
                                                        {{ $dok->nama_file_asli ?? 'file-dokumen' }}
                                                        <span class="text-muted">({{ $dok->ukuran_file_format ?? '0 B' }})</span>
                                                    </div>
                                                    <a href="{{ $urlFile }}" target="_blank" class="btn btn-sm btn-outline-primary text-nowrap">
                                                        <i class="bi bi-eye me-1"></i> Lihat / Unduh
                                                    </a>
                                                </div>
                                                @if($dok->tipe_mime && str_starts_with($dok->tipe_mime, 'image/'))
                                                    <div class="border bg-white p-1">
                                                        <img src="{{ $urlFile }}" alt="{{ $master?->nama_dokumen }}" style="max-height: 220px; max-width: 100%; display:block; margin: 0 auto;">
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="mb-3 border border-dashed rounded p-4 bg-gray-50 text-center text-muted small">
                                                Belum ada dokumen yang diunggah.
                                            </div>
                                        @endif

                                        @if($dok->catatan || ! empty($dok->tgl_verifikasi))
                                            <div class="mb-3">
                                                @if(! empty($dok->tgl_verifikasi))
                                                    <div class="small text-muted mb-1">
                                                        Diverifikasi pada: @tanggalWaktu($dok->tgl_verifikasi)
                                                        @if(! empty($dok->verifikator?->name))
                                                            · Oleh <strong>{{ $dok->verifikator->name }}</strong>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if(! empty($dok->catatan))
                                                    <div class="alert {{ in_array($status, ['ditolak','perlu_diperbaiki'], true) ? 'alert-danger' : 'alert-info' }} mb-0 py-2 small">
                                                        <span class="fw-semibold">Catatan Verifikasi:</span>
                                                        <div>{{ $dok->catatan }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if($dapatUnggah)
                                            <form method="POST" action="{{ route('pinjaman.dokumen.upload', [$pinjaman->id, $master?->id ?? $dok->master_dokumen_id]) }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="row g-2">
                                                    <div class="col-12">
                                                        <input
                                                            type="file"
                                                            name="dokumen"
                                                            class="form-control form-control-sm @error('dokumen') is-invalid @enderror"
                                                            accept=".jpg,.jpeg,.png,.pdf"
                                                            placeholder="Pilih file dokumen ({{ strtoupper($format) }})">
                                                        @error('dokumen')
                                                            <div class="invalid-feedback small">{{ $message }}</div>
                                                        @enderror
                                                        <div class="small text-muted mt-1">
                                                            Format: {{ strtoupper(str_replace(',', ', ', $format)) }} · Ukuran maksimal: 10 MB
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <button type="submit" class="btn btn-sm btn-orange text-white w-100">
                                                            <i class="bi bi-cloud-upload me-1"></i>
                                                            {{ $status === 'belum_diunggah' ? 'Unggah Dokumen' : 'Unggah Ulang Dokumen' }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        @elseif($status === 'menunggu_verifikasi')
                                            <div class="alert alert-info py-2 small mb-0">
                                                Dokumen sedang menunggu proses verifikasi oleh validator.
                                            </div>
                                        @elseif($status === 'disetujui')
                                            <div class="alert alert-success py-2 small mb-0">
                                                Dokumen telah disetujui. Jika ingin memperbarui dokumen, silakan hubungi validator.
                                            </div>
                                        @endif

                                        @if($bisaVerifikasiDokumen && $dok->id && $urlFile)
                                            <hr class="my-3">
                                            <form method="POST" action="{{ route('pinjaman.dokumen.verifikasi', [$pinjaman->id, $dok->id]) }}">
                                                @csrf
                                                <div class="small fw-semibold mb-2 text-primary">Verifikasi Dokumen</div>
                                                <div class="row g-2 mb-2">
                                                    <div class="col-md-6">
                                                        <select name="status" class="form-select form-select-sm @error('status') is-invalid @enderror" data-toggle-one="{{ $master?->id }}">
                                                            <option value="">Pilih Hasil Verifikasi</option>
                                                            <option value="disetujui" {{ old('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                                            <option value="perlu_diperbaiki" {{ old('status') === 'perlu_diperbaiki' ? 'selected' : '' }}>Perlu Diperbaiki</option>
                                                            <option value="ditolak" {{ old('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                                        </select>
                                                        @error('status')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                                                    </div>
                                                    <div class="col-md-6">
                                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                                            <i class="bi bi-send me-1"></i> Kirim Hasil Verifikasi
                                                        </button>
                                                    </div>
                                                </div>
                                                <div>
                                                    <textarea name="catatan" rows="2"
                                                        class="form-control form-control-sm @error('catatan') is-invalid @enderror"
                                                        placeholder="Catatan (Wajib diisi jika hasilnya Ditolak / Perlu Diperbaiki)">{{ old('catatan', $status === 'disetujui' ? '' : ($dok->catatan ?? '')) }}</textarea>
                                                    @error('catatan')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                                                </div>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endpush
