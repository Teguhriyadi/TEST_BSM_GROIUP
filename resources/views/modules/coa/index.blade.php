@extends('modules.layouts.master')
@push('title', 'Daftar Akun (COA)')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => false,
    'showTanggal' => false,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Daftar Akun (COA)</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Daftar Akun (COA)</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Kode Akun</h6>
                @haspermission('COA_CREATE')
                <a href="{{ route('coa.create') }}" class="btn btn-orange btn-sm">
                    <i class="bi bi-plus me-1"></i> Tambah Akun
                </a>
                @endhaspermission
            </div>
            <div class="card-body">
                @if($coa->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th>Level</th>
                                <th>Kelompok</th>
                                <th>Saldo Normal</th>
                                <th>Posisi Laporan</th>
                                <th>Induk</th>
                                <th>Cabang</th>
                                <th>Status</th>
                                @hasanypermission(['COA_VIEW','COA_UPDATE','COA_DELETE'])
                                <th width="18%">Aksi</th>
                                @endhasanypermission
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coa as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $item->kode_akun }}</td>
                                <td>{{ $item->nama_akun }}</td>
                                <td>{{ $item->level == 1 ? 'Header' : 'Detail' }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $item->kelompok)) }}</td>
                                <td>{{ $item->saldo_normal == 'debet' ? 'Debet' : 'Kredit' }}</td>
                                <td>{{ $item->posisi_laporan == 'neraca' ? 'Neraca' : 'Laba Rugi' }}</td>
                                <td>{{ $item->parent?->kode_akun . ' - ' . ($item->parent?->nama_akun ?? '-') }}</td>
                                <td>{{ $item->cabang?->nama_cabang ?? 'Global' }}</td>
                                <td>
                                    @if($item->is_active == '1')
                                    <span class="badge bg-orange text-white">Aktif</span>
                                    @else
                                    <span class="badge bg-secondary text-white">Nonaktif</span>
                                    @endif
                                </td>
                                @hasanypermission(['COA_VIEW','COA_UPDATE','COA_DELETE'])
                                <td>
                                    @haspermission('COA_VIEW')
                                    <a href="{{ route('coa.show', $item->id) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @endhaspermission
                                    @haspermission('COA_UPDATE')
                                    <a href="{{ route('coa.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    @endhaspermission
                                    @haspermission('COA_DELETE')
                                    <form class="d-inline form-delete" method="POST" action="{{ route('coa.destroy', $item->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                    @endhaspermission
                                </td>
                                @endhasanypermission
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endpush
