@extends('modules.layouts.master')
@push('title', 'Data Karyawan')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        {{ (auth()->check() && auth()->user()->hasRole('Karyawan')) ? 'Profil Karyawan' : 'Data Karyawan' }}
    </h1>
    @if(!(auth()->check() && auth()->user()->hasRole('Karyawan')))
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Karyawan</li>
        </ol>
    </nav>
    @endif
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    {{ (auth()->check() && auth()->user()->hasRole('Karyawan')) ? 'Data Diri Karyawan' : 'Daftar Karyawan' }}
                </h6>
                @if(!(auth()->check() && auth()->user()->hasRole('Karyawan')))
                    @haspermission('KARYAWAN_CREATE')
                    <a href="{{ route('karyawan.create') }}" class="btn btn-orange btn-sm">
                        <i class="bi bi-plus me-1"></i> Tambah Karyawan
                    </a>
                    @endhaspermission
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>No</th>
                                <th>No Anggota</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>JK</th>
                                <th>Email (Login)</th>
                                <th>No HP</th>
                                <th>Cabang</th>
                                <th>Status</th>
                                <th>Akun Dibuat</th>
                                @if(!(auth()->check() && auth()->user()->hasRole('Karyawan')))
                                    @hasanypermission(['KARYAWAN_VIEW','KARYAWAN_UPDATE','KARYAWAN_DELETE'])
                                    <th>Aksi</th>
                                    @endhasanypermission
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataKaryawan ?? $karyawan as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->no_anggota }}</td>
                                <td>{{ $item->nik }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->jenis_kelamin }}</td>
                                <td>{!! $item->user->email ?? ($item->users_id ? '-' : '<span class="text-muted small">(tidak punya akun)</span>') !!}</td>
                                <td>{{ $item->no_hp }}</td>
                                <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                                <td>
                                    @if($item->status == 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                    @else
                                    <span class="badge bg-secondary">Non Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">@tanggal($item->created_at)</td>
                                @if(!(auth()->check() && auth()->user()->hasRole('Karyawan')))
                                    @hasanypermission(['KARYAWAN_VIEW','KARYAWAN_UPDATE','KARYAWAN_DELETE'])
                                    <td>
                                        @haspermission('KARYAWAN_VIEW')
                                        <a href="{{ route('karyawan.show', $item->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        @endhaspermission
                                        @haspermission('KARYAWAN_UPDATE')
                                        <a href="{{ route('karyawan.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        @endhaspermission
                                        @haspermission('KARYAWAN_DELETE')
                                        <form class="d-inline form-delete" method="POST" action="{{ route('karyawan.destroy', $item->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-delete">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                        @endhaspermission
                                    </td>
                                    @endhasanypermission
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
