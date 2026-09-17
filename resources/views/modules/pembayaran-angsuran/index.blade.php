@extends('modules.layouts.master')
@push('title', 'Pembayaran Angsuran')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Riwayat Pembayaran Angsuran</h6>
        @haspermission('PEMBAYARAN_CREATE')
        <a href="{{ route('pembayaran-angsuran.create') }}" class="btn btn-orange btn-sm">
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
                        <th>Tanggal Bayar</th>
                        <th>Angsuran</th>
                        <th class="text-end">Jumlah Bayar</th>
                        <th class="text-center">Metode</th>
                        <th>Bukti</th>
                        <th>Dibayar Oleh</th>
                        <th>Keterangan</th>
                        @hasanypermission(['PEMBAYARAN_VIEW','PEMBAYARAN_UPDATE','PEMBAYARAN_DELETE'])
                        <th class="text-center" width="13%">Aksi</th>
                        @endhasanypermission
                    </tr>
                </thead>
                <tbody>
                    @foreach($pembayaranAngsuran as $key => $item)
                    @php
                        $metodeClass = 'secondary';
                        if($item->metode_pembayaran == 'tunai') $metodeClass = 'success';
                        elseif($item->metode_pembayaran == 'transfer') $metodeClass = 'primary';
                        elseif($item->metode_pembayaran == 'lainnya') $metodeClass = 'info';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>@tanggal($item->tanggal_bayar)</td>
                        <td>
                            {{ $item->angsuran->pinjaman->nomor_pinjaman ?? '-' }} - Ke-{{ $item->angsuran->angsuran_ke ?? '-' }}
                        </td>
                        <td class="text-end">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge badge-status bg-{{ $metodeClass }}">{{ ucwords($item->metode_pembayaran) }}</span>
                        </td>
                        <td>
                            @if($item->bukti_pembayaran)
                            <a href="{{ $item->bukti_pembayaran }}" target="_blank" class="text-primary">
                                <i class="bi bi-file-earmark-text me-1"></i>Lihat
                            </a>
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ $item->dibayarOleh->nama ?? '-' }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                        @hasanypermission(['PEMBAYARAN_VIEW','PEMBAYARAN_UPDATE','PEMBAYARAN_DELETE'])
                        <td class="text-center">
                            @haspermission('PEMBAYARAN_VIEW')
                            <a href="{{ route('pembayaran-angsuran.show', $item->id) }}" class="btn btn-sm btn-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endhaspermission
                            @haspermission('PEMBAYARAN_UPDATE')
                            <a href="{{ route('pembayaran-angsuran.edit', $item->id) }}" class="btn btn-sm btn-success" title="Ubah">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @endhaspermission
                            @haspermission('PEMBAYARAN_DELETE')
                            <form class="d-inline form-delete" method="POST" action="{{ route('pembayaran-angsuran.destroy', $item->id) }}">
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
