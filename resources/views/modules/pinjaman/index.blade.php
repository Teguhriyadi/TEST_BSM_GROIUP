@extends('modules.layouts.master')
@push('title', 'Data Pinjaman')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Pinjaman</h6>
        @haspermission('PINJAMAN_CREATE')
        <a href="{{ route('pinjaman.create') }}" class="btn btn-orange btn-sm">
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
                        <th>Nomor Pinjaman</th>
                        <th>Anggota</th>
                        <th>Cabang</th>
                        <th>Jenis</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-center">Tenor</th>
                        <th class="text-center">Bunga</th>
                        <th class="text-end">Angsuran/Bulan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Progress Dokumen</th>
                        <th>Tgl Pengajuan</th>
                        @hasanypermission(['PINJAMAN_VIEW','PINJAMAN_UPDATE','PINJAMAN_DELETE','PINJAMAN_APPROVE'])
                        <th class="text-center" width="17%">Aksi</th>
                        @endhasanypermission
                    </tr>
                </thead>
                <tbody>
                    @foreach($pinjaman as $key => $item)
                    @php
                        $statusClass = 'secondary';
                        if($item->status == 'aktif' || $item->status == 'lunas' || $item->status == 'disetujui') $statusClass = 'success';
                        elseif($item->status == 'diajukan' || $item->status == 'diverifikasi') $statusClass = 'info';
                        elseif($item->status == 'dicairkan' || $item->status == 'berjalan') $statusClass = 'warning';
                        elseif($item->status == 'ditolak' || $item->status == 'telat') $statusClass = 'danger';
                        elseif($item->status == 'nonaktif' || $item->status == 'belum_lunas') $statusClass = 'secondary';
                        if($item->status == 'disetujui') $statusClass = 'primary';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->nomor_pinjaman }}</td>
                        <td>{{ $item->anggota->nama ?? '-' }}</td>
                        <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                        <td>{{ $item->jenisPinjaman->nama_jenis ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($item->jumlah_pinjaman, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item->tenor }} bln</td>
                        <td class="text-center">{{ $item->bunga }}%</td>
                        <td class="text-end">Rp {{ number_format($item->angsuran_per_bulan, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge badge-status bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $progress = $item->persentase_progress_dokumen ?? 0;
                                $label = $item->progress_label ?? '0/0 disetujui';
                                $progressClass = 'bg-secondary';
                                if ($progress >= 100) $progressClass = 'bg-success';
                                elseif ($progress >= 50) $progressClass = 'bg-primary';
                                elseif ($progress > 0) $progressClass = 'bg-warning';
                            @endphp
                            <div class="d-flex flex-column align-items-center gap-1">
                                <div class="small text-muted fw-medium">{{ $label }}</div>
                                <div class="w-100 progress bg-light border" style="height: 6px;">
                                    <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </td>
                        <td>@tanggal($item->tgl_pengajuan)</td>
                        @hasanypermission(['PINJAMAN_VIEW','PINJAMAN_UPDATE','PINJAMAN_DELETE','PINJAMAN_APPROVE'])
                        <td class="text-center">
                            @haspermission('PINJAMAN_VIEW')
                            <a href="{{ route('pinjaman.show', $item->id) }}" class="btn btn-sm btn-primary" title="Detail & Dokumen">
                                <i class="bi bi-folder2-open"></i>
                            </a>
                            @endhaspermission
                            @haspermission('PINJAMAN_UPDATE')
                            <a href="{{ route('pinjaman.edit', $item->id) }}" class="btn btn-sm btn-success" title="Ubah Data">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @endhaspermission
                            @haspermission('PINJAMAN_DELETE')
                            <form class="d-inline form-delete" method="POST" action="{{ route('pinjaman.destroy', $item->id) }}">
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
