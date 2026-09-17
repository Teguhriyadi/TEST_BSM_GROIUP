@extends('modules.layouts.master')
@push('title', 'Data Angsuran')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Angsuran</h6>
        @haspermission('ANGSURAN_CREATE')
        <a href="{{ route('angsuran.create') }}" class="btn btn-orange btn-sm">
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
                        <th>Pinjaman</th>
                        <th class="text-center">Angsuran Ke</th>
                        <th>Tgl Jatuh Tempo</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-end">Denda</th>
                        <th class="text-end">Total Bayar</th>
                        <th class="text-center">Status</th>
                        @hasanypermission(['ANGSURAN_VIEW','ANGSURAN_UPDATE','ANGSURAN_DELETE'])
                        <th class="text-center" width="13%">Aksi</th>
                        @endhasanypermission
                    </tr>
                </thead>
                <tbody>
                    @foreach($angsuran as $key => $item)
                    @php
                        $statusClass = 'secondary';
                        if($item->status == 'lunas') $statusClass = 'success';
                        elseif($item->status == 'belum_lunas' || $item->status == 'nonaktif') $statusClass = 'secondary';
                        elseif($item->status == 'telat') $statusClass = 'danger';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->pinjaman->nomor_pinjaman ?? '-' }}</td>
                        <td class="text-center">{{ $item->angsuran_ke }}</td>
                        <td>@tanggal($item->tanggal_jatuh_tempo)</td>
                        <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->denda, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge badge-status bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
                        </td>
                        @hasanypermission(['ANGSURAN_VIEW','ANGSURAN_UPDATE','ANGSURAN_DELETE'])
                        <td class="text-center">
                            @haspermission('ANGSURAN_VIEW')
                            <a href="{{ route('angsuran.show', $item->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endhaspermission
                            @haspermission('ANGSURAN_UPDATE')
                            <a href="{{ route('angsuran.edit', $item->id) }}" class="btn btn-sm btn-success" title="Ubah">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @endhaspermission
                            @haspermission('ANGSURAN_DELETE')
                            <form class="d-inline form-delete" method="POST" action="{{ route('angsuran.destroy', $item->id) }}">
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
