@extends('modules.layouts.master')

@push('title')
    Master Dokumen Persyaratan | BSM Koperasi
@endpush

@push('breadcrumbs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5 small">
            <li class="breadcrumb-item text-sm text-dark"><a class="opacity-5 text-dark"
                    href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Master Dokumen</li>
        </ol>
        <h6 class="font-weight-bolder mb-0">Master Persyaratan Dokumen</h6>
    </nav>
@endpush

@push('page-modules')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible text-white small" role="alert">
            <span class="text-sm">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible text-white small" role="alert">
            <span class="text-sm">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-orange">
                <i class="bi bi-files me-1"></i> Daftar Master Dokumen
            </h6>
            <div class="d-flex gap-2">
                @haspermission('MASTER_DOKUMEN_CREATE')
                    <a href="{{ route('master-dokumen.create') }}" class="btn btn-sm btn-orange">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Master Dokumen
                    </a>
                @endhaspermission
            </div>
        </div>
        <div class="card-body">
            @if ($masterDokumen->isEmpty())
                <div class="text-center py-5 text-muted">
                    <p class="mb-0">Belum ada data master dokumen.</p>
                    @haspermission('MASTER_DOKUMEN_CREATE')
                        <a href="{{ route('master-dokumen.create') }}" class="btn btn-sm btn-outline-orange mt-3">
                            Tambah Dokumen Pertama
                        </a>
                    @endhaspermission
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">No.</th>
                                <th width="120">Kode</th>
                                <th>Nama Dokumen</th>
                                <th width="150">Format File</th>
                                <th width="80" class="text-center">Status</th>
                                <th width="80" class="text-center">Jml Jenis</th>
                                <th width="80" class="text-center">Dipakai</th>
                                <th width="220" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($masterDokumen as $i => $md)
                                <tr>
                                    <td class="text-center">{{ $masterDokumen->firstItem() + $i }}</td>
                                    <td class="fw-medium">{{ $md->kode_dokumen }}</td>
                                    <td>
                                        <div class="fw-semibold text-gray-800">{{ $md->nama_dokumen }}</div>
                                        @if (!empty($md->deskripsi))
                                            <div class="text-muted small mt-1">{{ $md->deskripsi }}</div>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        @php
                                            $arrFormat = $md->getFormatListArray();
                                        @endphp
                                        @if (!empty($arrFormat))
                                            @foreach ($arrFormat as $f)
                                                <span
                                                    class="badge bg-light text-gray-800 border me-1 mb-1 px-2 py-1">.{{ strtoupper($f) }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($md->is_active)
                                            <span class="badge bg-orange text-white px-2 py-1">AKTIF</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2 py-1">NONAKTIF</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-medium">
                                        {{ $md->jenis_pinjamans_count ?? ($md->jenisPinjaman()->count() ?? '-') }}
                                    </td>
                                    <td class="text-center fw-medium">
                                        {{ $md->pinjaman_dokumens_count ?? ($md->pinjamanDokumen()->count() ?? '-') }}
                                    </td>
                                    <td class="text-center">
                                        @hasanypermission(['MASTER_DOKUMEN_VIEW', 'MASTER_DOKUMEN_UPDATE', 'MASTER_DOKUMEN_DELETE'])
                                            <div class="d-flex justify-content-center gap-1">
                                                @haspermission('MASTER_DOKUMEN_VIEW')
                                                    <a href="{{ route('master-dokumen.show', $md->id) }}"
                                                        class="btn btn-sm btn-info text-white text-xs px-2" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                @endhaspermission
                                                @haspermission('MASTER_DOKUMEN_UPDATE')
                                                    <a href="{{ route('master-dokumen.edit', $md->id) }}"
                                                        class="btn btn-sm btn-warning text-white text-xs px-2" title="Ubah">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                @endhaspermission
                                                @haspermission('MASTER_DOKUMEN_DELETE')
                                                    <form onsubmit="return confirm('Hapus master dokumen ini?');"
                                                        action="{{ route('master-dokumen.destroy', $md->id) }}" method="POST"
                                                        class="d-inline m-0 p-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger text-white text-xs px-2"
                                                            title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endhaspermission
                                            </div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endhasanypermission
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted">
                        Menampilkan {{ $masterDokumen->firstItem() ?? 0 }} - {{ $masterDokumen->lastItem() ?? 0 }} dari
                        {{ $masterDokumen->total() ?? 0 }} data
                    </div>
                    <div>
                        {{ $masterDokumen->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

@endpush
