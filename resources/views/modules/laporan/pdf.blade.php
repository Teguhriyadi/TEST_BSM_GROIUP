<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $judul ?? 'Laporan' }}</title>
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; }
  h1 { font-size: 18px; color: #0284c7; text-align: center; margin-bottom: 4px; }
  h2 { font-size: 13px; color: #f97316; text-align: center; margin-top: 0; margin-bottom: 14px; font-weight: normal; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background-color: #0284c7; color: white; padding: 6px 8px; text-align: left; font-weight: bold; border: 1px solid #ddd; font-size: 11px; }
  td { padding: 5px 8px; border: 1px solid #ddd; font-size: 11px; }
  tr:nth-child(even) td { background-color: #f9fafb; }
  .header-info { margin-bottom: 18px; }
  .header-info td { border: none; padding: 2px; }
  .footer { margin-top: 30px; text-align: right; font-size: 11px; color: #666; }
  .text-end { text-align: right; }
  .text-center { text-align: center; }
</style>
</head>
<body>
<h1>SISTEM KOPERASI SIMPAN PINJAM</h1>
<h2>{{ strtoupper($judul ?? 'LAPORAN') }}</h2>

<table class="header-info" style="width: 55%;">
  <tr><td style="width: 130px;">Periode</td><td>: {{ $periode ?? '-' }}</td></tr>
  <tr><td>Cabang</td><td>: {{ $infoCabang ?? 'Seluruh Cabang' }}</td></tr>
  <tr><td>Dicetak Tanggal</td><td>: {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm') }}</td></tr>
</table>
<hr>

@if($jenis == 'anggota')
<table>
  <thead><tr>
    <th class="text-center">No</th><th>No Anggota</th><th>NIK</th><th>Nama</th>
    <th class="text-center">JK</th><th>No HP</th><th>Cabang</th>
    <th class="text-center">Status</th><th>Tanggal Daftar</th>
  </tr></thead>
  <tbody>
  @foreach($data as $item)
  <tr>
    <td class="text-center">{{ $loop->iteration }}</td>
    <td>{{ $item->no_anggota ?: '-' }}</td>
    <td>{{ $item->nik ?? '-' }}</td>
    <td>{{ $item->nama }}</td>
    <td class="text-center">{{ $item->jenis_kelamin == 'L' ? 'L' : 'P' }}</td>
    <td>{{ $item->no_hp ?? '-' }}</td>
    <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
    <td class="text-center">{{ ucwords($item->status) }}</td>
    <td>{{ \Carbon\Carbon::parse($item->created_at)->isoFormat('D MMMM Y HH:mm') }}</td>
  </tr>
  @endforeach
  </tbody>
</table>

@elseif($jenis == 'verifikasi')
<table>
  <thead><tr>
    <th class="text-center">No</th><th>NIK</th><th>Nama</th><th>No HP</th>
    <th>Cabang</th><th class="text-center">Status Pendaftaran</th><th>Tanggal Daftar</th>
  </tr></thead>
  <tbody>
  @foreach($data as $item)
  <tr>
    <td class="text-center">{{ $loop->iteration }}</td>
    <td>{{ $item->nik ?? '-' }}</td>
    <td>{{ $item->nama }}</td>
    <td>{{ $item->no_hp ?? '-' }}</td>
    <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
    <td class="text-center">{{ ucwords(str_replace('_',' ',$item->status_pendaftaran)) }}</td>
    <td>{{ \Carbon\Carbon::parse($item->created_at)->isoFormat('D MMMM Y HH:mm') }}</td>
  </tr>
  @endforeach
  </tbody>
</table>

@elseif($jenis == 'simpanan')
<table>
  <thead><tr>
    <th class="text-center">No</th><th>Tanggal</th><th>No Anggota</th><th>Nama Anggota</th>
    <th>Jenis Simpanan</th><th>Cabang</th>
    <th class="text-end">Nominal</th><th class="text-end">Saldo</th>
  </tr></thead>
  <tbody>
  @foreach($data as $item)
  <tr>
    <td class="text-center">{{ $loop->iteration }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMMM Y') }}</td>
    <td>{{ $item->anggota->no_anggota ?? '-' }}</td>
    <td>{{ $item->anggota->nama ?? '-' }}</td>
    <td>{{ $item->jenisSimpanan->nama_jenis ?? '-' }}</td>
    <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
    <td class="text-end">Rp {{ number_format($item->nominal,0,',','.') }}</td>
    <td class="text-end">Rp {{ number_format($item->saldo,0,',','.') }}</td>
  </tr>
  @endforeach
  </tbody>
</table>

@elseif($jenis == 'pinjaman')
<table>
  <thead><tr>
    <th class="text-center">No</th><th>No Pinjaman</th><th>Anggota</th><th>Jenis Pinjaman</th>
    <th>Cabang</th>
    <th class="text-end">Jumlah</th><th class="text-center">Tenor</th>
    <th class="text-end">Angsuran/Bln</th>
    <th class="text-center">Status</th><th>Tgl Pengajuan</th>
  </tr></thead>
  <tbody>
  @foreach($data as $item)
  <tr>
    <td class="text-center">{{ $loop->iteration }}</td>
    <td>{{ $item->nomor_pinjaman }}</td>
    <td>{{ $item->anggota->nama ?? '-' }}</td>
    <td>{{ $item->jenisPinjaman->nama_jenis ?? '-' }}</td>
    <td>{{ $item->cabang->nama_cabang ?? '-' }}</td>
    <td class="text-end">Rp {{ number_format($item->jumlah_pinjaman,0,',','.') }}</td>
    <td class="text-center">{{ $item->tenor }} bln</td>
    <td class="text-end">Rp {{ number_format($item->angsuran_per_bulan,0,',','.') }}</td>
    <td class="text-center">{{ ucwords(str_replace('_',' ',$item->status)) }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tgl_pengajuan)->isoFormat('D MMMM Y') }}</td>
  </tr>
  @endforeach
  </tbody>
</table>

@elseif($jenis == 'angsuran')
<table>
  <thead><tr>
    <th class="text-center">No</th><th>No Pinjaman</th><th>Anggota</th>
    <th class="text-center">Angsuran Ke</th>
    <th>Tgl Jatuh Tempo</th>
    <th class="text-end">Nominal</th><th class="text-end">Denda</th>
    <th class="text-end">Total</th><th class="text-center">Status</th>
  </tr></thead>
  <tbody>
  @foreach($data as $item)
  @php
    $statusLabel = $item->status_computed ?? 'belum_lunas';
  @endphp
  <tr>
    <td class="text-center">{{ $loop->iteration }}</td>
    <td>{{ $item->pinjaman->nomor_pinjaman ?? '-' }}</td>
    <td>{{ $item->pinjaman->anggota->nama ?? '-' }}</td>
    <td class="text-center">{{ $item->angsuran_ke }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tanggal_jatuh_tempo)->isoFormat('D MMMM Y') }}</td>
    <td class="text-end">Rp {{ number_format($item->nominal,0,',','.') }}</td>
    <td class="text-end">Rp {{ number_format($item->denda,0,',','.') }}</td>
    <td class="text-end">Rp {{ number_format(($item->nominal ?? 0) + ($item->denda ?? 0),0,',','.') }}</td>
    <td class="text-center">{{ ucwords(str_replace('_',' ',$statusLabel)) }}</td>
  </tr>
  @endforeach
  </tbody>
</table>

@elseif($jenis == 'pembayaran')
<table>
  <thead><tr>
    <th class="text-center">No</th><th>Tanggal Bayar</th><th>No Pinjaman</th><th>Anggota</th>
    <th class="text-center">Angsuran Ke</th>
    <th class="text-end">Jumlah Bayar</th><th>Metode</th><th>Dibayar Oleh</th>
  </tr></thead>
  <tbody>
  @foreach($data as $item)
  <tr>
    <td class="text-center">{{ $loop->iteration }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tanggal_bayar)->isoFormat('D MMMM Y') }}</td>
    <td>{{ $item->angsuran->pinjaman->nomor_pinjaman ?? '-' }}</td>
    <td>{{ $item->angsuran->pinjaman->anggota->nama ?? '-' }}</td>
    <td class="text-center">{{ $item->angsuran->angsuran_ke ?? '-' }}</td>
    <td class="text-end">Rp {{ number_format($item->jumlah_bayar,0,',','.') }}</td>
    <td>{{ ucwords(str_replace('_',' ',$item->metode_pembayaran)) }}</td>
    <td>{{ $item->dibayarOleh->name ?? '-' }}</td>
  </tr>
  @endforeach
  </tbody>
</table>
@endif

<div class="footer">
Dicetak oleh Sistem Koperasi Simpan Pinjam pada {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm:ss') }}
</div>
</body>
</html>
