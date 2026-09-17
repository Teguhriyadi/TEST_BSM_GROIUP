@extends('modules.layouts.master')
@push('title', $judulLaporan ?? 'Laporan')
@push('page-modules')
    @php
        $hideFilterBar = false;
        $showCabang = $showCabang ?? true;
        $showTanggal = $showTanggal ?? true;
    @endphp
    @include('modules.layouts.components.module-filter-card', [
        'showCabang' => $showCabang,
        'showTanggal' => $showTanggal,
    ])
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $judulLaporan ?? 'Laporan' }}</h1>
    </div>

    @if (!($data && count($data) > 0))
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Belum ada data pada periode ini.
        </div>
    @else
        @php
            $kodeExportMap = [
                'anggota'    => 'LAPORAN_ANGGOTA_EXPORT',
                'verifikasi' => 'LAPORAN_VERIFIKASI_EXPORT',
                'simpanan'   => 'LAPORAN_SIMPANAN_EXPORT',
                'pinjaman'   => 'LAPORAN_PINJAMAN_EXPORT',
                'angsuran'   => 'LAPORAN_ANGSURAN_EXPORT',
                'pembayaran' => 'LAPORAN_PEMBAYARAN_EXPORT',
            ];
            $exportPermission = $kodeExportMap[$jenisLaporan] ?? '';
            $bisaExport = $exportPermission && auth()->check() && auth()->user()->hasPermission($exportPermission);
        @endphp
        <form action="{{ $exportAction }}" method="POST" target="_blank">
            @csrf
            <input type="hidden" name="cabang_id" value="{{ $cabang_id ?? '' }}">
            <input type="hidden" name="tanggal_awal" value="{{ $tanggal_awal ?? '' }}">
            <input type="hidden" name="tanggal_akhir" value="{{ $tanggal_akhir ?? '' }}">
            @if (!empty($status_pendaftaran))
                <input type="hidden" name="status_pendaftaran" value="{{ $status_pendaftaran }}">
            @endif
            @if (!empty($status) && in_array($jenisLaporan, ['pinjaman', 'angsuran']))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            @if (!empty($metode))
                <input type="hidden" name="metode_pembayaran" value="{{ $metode }}">
            @endif
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Data Periode @tanggal($tanggal_awal) s/d @tanggal($tanggal_akhir)
                    </h6>
                    @if($bisaExport)
                    <button type="submit" class="btn btn-sm border-0 text-white fw-semibold"
                        style="background-color: #dc2626;">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%"
                            cellspacing="0">
                            <thead class="bg-primary text-white">
                                @if ($jenisLaporan == 'anggota')
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>No Anggota</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th class="text-center">JK</th>
                                        <th>No HP</th>
                                        <th>Cabang</th>
                                        <th class="text-center">Status</th>
                                        <th>Tanggal Daftar</th>
                                    </tr>
                                @elseif($jenisLaporan == 'verifikasi')
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>No HP</th>
                                        <th>Cabang</th>
                                        <th class="text-center">Status Pendaftaran</th>
                                        <th>Tanggal Daftar</th>
                                    </tr>
                                @elseif($jenisLaporan == 'simpanan')
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>Tanggal</th>
                                        <th>No Anggota</th>
                                        <th>Nama Anggota</th>
                                        <th>Jenis Simpanan</th>
                                        <th>Cabang</th>
                                        <th class="text-end">Nominal</th>
                                        <th class="text-end">Saldo</th>
                                    </tr>
                                @elseif($jenisLaporan == 'pinjaman')
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>No Pinjaman</th>
                                        <th>Anggota</th>
                                        <th>Jenis Pinjaman</th>
                                        <th>Cabang</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-center">Tenor</th>
                                        <th class="text-end">Angsuran/Bln</th>
                                        <th class="text-center">Status</th>
                                        <th>Tgl Pengajuan</th>
                                    </tr>
                                @elseif($jenisLaporan == 'angsuran')
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>No Pinjaman</th>
                                        <th>Anggota</th>
                                        <th class="text-center">Angsuran Ke</th>
                                        <th>Tgl Jatuh Tempo</th>
                                        <th class="text-end">Nominal</th>
                                        <th class="text-end">Denda</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                @elseif($jenisLaporan == 'pembayaran')
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>Tanggal Bayar</th>
                                        <th>No Pinjaman</th>
                                        <th>Anggota</th>
                                        <th class="text-center">Angsuran Ke</th>
                                        <th class="text-end">Jumlah Bayar</th>
                                        <th>Metode</th>
                                        <th>Dibayar Oleh</th>
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @if ($jenisLaporan == 'anggota')
                                    @foreach ($data as $item)
                                        @php $statusClass = $item->status == 'aktif' ? 'success' : 'secondary'; @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item->no_anggota ?: '-' }}</td>
                                            <td>{{ $item->nik ?? '-' }}</td>
                                            <td>{{ $item->nama }}</td>
                                            <td class="text-center">{{ $item->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
                                            <td>{{ $item->no_hp ?? '-' }}</td>
                                            <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-status bg-{{ $statusClass }}">{{ ucwords($item->status) }}</span>
                                            </td>
                                            <td>@tanggalWaktu($item->created_at)</td>
                                        </tr>
                                    @endforeach
                                @elseif($jenisLaporan == 'verifikasi')
                                    @foreach ($data as $item)
                                        @php
                                            $spClass = 'secondary';
                                            if ($item->status_pendaftaran == 'menunggu_verifikasi') {
                                                $spClass = 'warning';
                                            } elseif ($item->status_pendaftaran == 'disetujui') {
                                                $spClass = 'success';
                                            } elseif ($item->status_pendaftaran == 'ditolak') {
                                                $spClass = 'danger';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item->nik ?? '-' }}</td>
                                            <td>{{ $item->nama }}</td>
                                            <td>{{ $item->no_hp ?? '-' }}</td>
                                            <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-status bg-{{ $spClass }}">{{ ucwords(str_replace('_', ' ', $item->status_pendaftaran)) }}</span>
                                            </td>
                                            <td>@tanggalWaktu($item->created_at)</td>
                                        </tr>
                                    @endforeach
                                @elseif($jenisLaporan == 'simpanan')
                                    @foreach ($data as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>@tanggal($item->tanggal)</td>
                                            <td>{{ $item->anggota->no_anggota ?? '-' }}</td>
                                            <td>{{ $item->anggota->nama ?? '-' }}</td>
                                            <td>{{ $item->jenisSimpanan->nama_jenis ?? '-' }}</td>
                                            <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($item->saldo, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                @elseif($jenisLaporan == 'pinjaman')
                                    @foreach ($data as $item)
                                        @php
                                            $statusClass = 'secondary';
                                            if ($item->status == 'aktif' || $item->status == 'lunas') {
                                                $statusClass = 'success';
                                            } elseif ($item->status == 'diajukan' || $item->status == 'diverifikasi') {
                                                $statusClass = 'info';
                                            } elseif ($item->status == 'disetujui') {
                                                $statusClass = 'primary';
                                            } elseif ($item->status == 'dicairkan' || $item->status == 'berjalan') {
                                                $statusClass = 'warning';
                                            } elseif ($item->status == 'ditolak' || $item->status == 'telat') {
                                                $statusClass = 'danger';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item->nomor_pinjaman }}</td>
                                            <td>{{ $item->anggota->nama ?? '-' }}</td>
                                            <td>{{ $item->jenisPinjaman->nama_jenis ?? '-' }}</td>
                                            <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($item->jumlah_pinjaman, 0, ',', '.') }}</td>
                                            <td class="text-center">{{ $item->tenor }} bln</td>
                                            <td class="text-end">Rp {{ number_format($item->angsuran_per_bulan, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-status bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
                                            </td>
                                            <td>@tanggal($item->tgl_pengajuan)</td>
                                        </tr>
                                    @endforeach
                                @elseif($jenisLaporan == 'angsuran')
                                    @foreach ($data as $item)
                                        @php
                                            $statusLabel = $item->status_computed ?? 'belum_lunas';
                                            $statusClass = 'secondary';
                                            if ($statusLabel == 'lunas') {
                                                $statusClass = 'success';
                                            } elseif ($statusLabel == 'sebagian_dibayar') {
                                                $statusClass = 'warning';
                                            } elseif ($statusLabel == 'lewat_jatuh_tempo') {
                                                $statusClass = 'danger';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item->pinjaman->nomor_pinjaman ?? '-' }}</td>
                                            <td>{{ $item->pinjaman->anggota->nama ?? '-' }}</td>
                                            <td class="text-center">{{ $item->angsuran_ke }}</td>
                                            <td>@tanggal($item->tanggal_jatuh_tempo)</td>
                                            <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format($item->denda, 0, ',', '.') }}</td>
                                            <td class="text-end">Rp {{ number_format(($item->nominal ?? 0) + ($item->denda ?? 0), 0, ',', '.') }}
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-status bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $statusLabel)) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($jenisLaporan == 'pembayaran')
                                    @foreach ($data as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>@tanggal($item->tanggal_bayar)</td>
                                            <td>{{ $item->angsuran->pinjaman->nomor_pinjaman ?? '-' }}</td>
                                            <td>{{ $item->angsuran->pinjaman->anggota->nama ?? '-' }}</td>
                                            <td class="text-center">{{ $item->angsuran->angsuran_ke ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format($item->jumlah_bayar, 0, ',', '.') }}</td>
                                            <td>{{ ucwords(str_replace('_', ' ', $item->metode_pembayaran)) }}</td>
                                            <td>{{ $item->dibayarOleh->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    @endif
@endpush
