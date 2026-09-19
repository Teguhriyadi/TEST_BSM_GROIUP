@extends('modules.layouts.master')
@push('title', 'Jurnal Umum')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Jurnal Umum</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Jurnal Umum</li>
        </ol>
    </nav>
</div>

<div class="row mb-3">
    <div class="col-lg-12">
        <form method="GET" action="{{ route('jurnal-umum.index') }}" class="card shadow bg-white p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
                </div>
                <div class="col-md-3">
                    <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select select2-single" id="status" name="status" data-placeholder="-- Semua Status --">
                        <option value="">-- Semua Status --</option>
                        <option value="draf" {{ $status == 'draf' ? 'selected' : '' }}>Draf</option>
                        <option value="diposting" {{ $status == 'diposting' ? 'selected' : '' }}>Diposting</option>
                        <option value="dibatalkan" {{ $status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-orange">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('jurnal-umum.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Jurnal Umum</h6>
                @haspermission('JURNAL_CREATE')
                <a href="{{ route('jurnal-umum.create') }}" class="btn btn-orange btn-sm">
                    <i class="bi bi-plus me-1"></i> Jurnal Manual
                </a>
                @endhaspermission
            </div>
            <div class="card-body">
                @if($jurnal->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Tanggal</th>
                                <th>No. Jurnal</th>
                                <th>Tipe</th>
                                <th>Cabang</th>
                                <th>Keterangan</th>
                                <th class="text-end">Debet</th>
                                <th class="text-end">Kredit</th>
                                <th>Status</th>
                                <th>Diposting Oleh</th>
                                @hasanypermission(['JURNAL_VIEW','JURNAL_UPDATE','JURNAL_DELETE','JURNAL_POSTING'])
                                <th width="20%">Aksi</th>
                                @endhasanypermission
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jurnal as $j)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($j->tanggal_jurnal)->format('d/m/Y') }}</td>
                                <td class="fw-semibold">{{ $j->nomor_jurnal }}</td>
                                <td>{{ $j->tipe == 'otomatis' ? 'Otomatis' : 'Manual' }}</td>
                                <td>{{ $j->cabang?->nama_cabang ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($j->keterangan ?? '-', 50) }}</td>
                                <td class="text-end">Rp {{ number_format($j->total_debet ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($j->total_kredit ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    @if($j->status == 'draf')
                                    <span class="badge bg-warning text-dark">Draf</span>
                                    @elseif($j->status == 'diposting')
                                    <span class="badge bg-orange text-white">Diposting</span>
                                    @else
                                    <span class="badge bg-secondary text-white">Dibatalkan</span>
                                    @endif
                                </td>
                                <td>
                                    @if($j->diposting_at && $j->dipostingOleh)
                                    <small>{{ $j->dipostingOleh->nama }} <br> <span class="text-muted">{{ \Carbon\Carbon::parse($j->diposting_at)->format('d/m/Y H:i') }}</span></small>
                                    @else <span class="text-muted">-</span> @endif
                                </td>
                                @hasanypermission(['JURNAL_VIEW','JURNAL_UPDATE','JURNAL_DELETE','JURNAL_POSTING'])
                                <td>
                                    @haspermission('JURNAL_VIEW')
                                    <a href="{{ route('jurnal-umum.show', $j->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @endhaspermission
                                    @haspermission('JURNAL_UPDATE')
                                        @if($j->status == 'draf' && $j->tipe != 'otomatis')
                                        <a href="{{ route('jurnal-umum.edit', $j->id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        @endif
                                    @endhaspermission
                                    @haspermission('JURNAL_POSTING')
                                        @if($j->status == 'draf')
                                        <form class="d-inline form-posting" method="POST" action="{{ route('jurnal-umum.posting', $j->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success btn-posting">
                                                <i class="bi bi-check2-square"></i> Posting
                                            </button>
                                        </form>
                                        @endif
                                    @endhaspermission
                                    @haspermission('JURNAL_DELETE')
                                        @if($j->status != 'diposting' && $j->tipe != 'otomatis')
                                        <form class="d-inline form-delete" method="POST" action="{{ route('jurnal-umum.destroy', $j->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                        @endif
                                    @endhaspermission
                                </td>
                                @endhasanypermission
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    {{ $jurnal->links() }}
                </div>
                @else
                
                @endif
            </div>
        </div>
    </div>
</div>
@endpush
