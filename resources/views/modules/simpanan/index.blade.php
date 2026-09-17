@extends('modules.layouts.master')
@push('title', 'Data Simpanan')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Simpanan</h6>
        @haspermission('SIMPANAN_CREATE')
        <a href="{{ route('simpanan.create') }}" class="btn btn-orange btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah
        </a>
        @endhaspermission
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle datatable">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="text-center" width="5%">No</th>
                        <th>Tanggal</th>
                        <th>No Anggota</th>
                        <th>Nama Anggota</th>
                        <th>Cabang</th>
                        <th>Jenis</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-end">Saldo</th>
                        <th>Keterangan</th>
                        @hasanypermission(['SIMPANAN_VIEW','SIMPANAN_UPDATE','SIMPANAN_DELETE'])
                        <th class="text-center" width="13%">Aksi</th>
                        @endhasanypermission
                    </tr>
                </thead>
                <tbody>
                    @foreach($simpanan as $key => $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>@tanggal($item->tanggal)</td>
                        <td>{{ $item->anggota->no_anggota ?? '-' }}</td>
                        <td>{{ $item->anggota->nama ?? '-' }}</td>
                        <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                        <td>{{ $item->jenisSimpanan->nama_jenis ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->saldo, 0, ',', '.') }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                        @hasanypermission(['SIMPANAN_VIEW','SIMPANAN_UPDATE','SIMPANAN_DELETE'])
                        <td class="text-center">
                            @haspermission('SIMPANAN_VIEW')
                            <a href="{{ route('simpanan.show', $item->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endhaspermission
                            @haspermission('SIMPANAN_UPDATE')
                            <a href="{{ route('simpanan.edit', $item->id) }}" class="btn btn-sm btn-success" title="Ubah">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @endhaspermission
                            @haspermission('SIMPANAN_DELETE')
                            <form class="d-inline form-delete" method="POST" action="{{ route('simpanan.destroy', $item->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger btn-delete" title="Hapus">
                                    <i class="bi bi-trash"></i>
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
    </div>
</div>
@endpush
