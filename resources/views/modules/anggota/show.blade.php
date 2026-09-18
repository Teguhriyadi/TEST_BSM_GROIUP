@extends('modules.layouts.master')
@push('title', 'Detail Anggota')
@push('page-modules')

@php
    use Illuminate\Support\Facades\Auth;
    $bisaUbah = Auth::check() && Auth::user()->hasPermission('ANGGOTA_UPDATE');
    $bisaHapus = Auth::check() && Auth::user()->hasPermission('ANGGOTA_DELETE');
@endphp

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Detail Anggota</h1>
        <div class="small text-muted mt-1">
            No. Anggota: {{ $anggota->no_anggota ?? '-' }}
            @if($anggota->nik)
                &nbsp;•&nbsp; NIK: {{ $anggota->nik }}
            @endif
        </div>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Data Anggota</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
        </ol>
    </nav>
</div>

@php
    $statusA = $anggota->status ?? 'nonaktif';
    $statusClassA = $statusA === 'aktif' ? 'success' : 'secondary';
    $statusDaftar = $anggota->status_pendaftaran ?? 'tidak_perlu_verifikasi';
    $statusDaftarClass = match(true){
        in_array($statusDaftar, ['disetujui','tidak_perlu_verifikasi'], true) => 'success',
        $statusDaftar === 'menunggu_verifikasi' => 'warning',
        $statusDaftar === 'ditolak' => 'danger',
        default => 'info',
    };
@endphp

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between gap-2 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Data Pribadi Anggota</h6>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-{{ $statusClassA }} py-1 px-3 text-white">
                        Status: {{ ucfirst($statusA) }}
                    </span>
                    <span class="badge bg-{{ $statusDaftarClass }} py-1 px-3 text-white">
                        Pendaftaran: {{ ucwords(str_replace('_',' ', $statusDaftar)) }}
                    </span>
                    @if($bisaUbah)
                    <a href="{{ route('anggota.edit', $anggota->id) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-pencil-square me-1"></i> Ubah Data
                    </a>
                    @endif
                    <a href="{{ route('anggota.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i> Daftar Anggota
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Nama Lengkap</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->nama ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">No. Anggota</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->no_anggota ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">NIK</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->nik ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Jenis Kelamin</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->jenis_kelamin ? ucfirst($anggota->jenis_kelamin) : '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tempat, Tanggal Lahir</div>
                        <div class="fw-semibold text-gray-800">
                            {{ ($anggota->tempat_lahir ?? '') . ($anggota->tempat_lahir && $anggota->tgl_lahir ? ', ' : '') . ($anggota->tgl_lahir ? \Carbon\Carbon::parse($anggota->tgl_lahir)->isoFormat('DD MMMM YYYY') : '-') }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Pekerjaan</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->pekerjaan ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Nomor HP</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->no_hp ?? $anggota->user?->nomor_hp ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Email</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->user?->email ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Agama</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->agama ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Status Perkawinan</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->status_perkawinan ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Nama Ibu Kandung</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->nama_ibu ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Pendidikan Terakhir</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->pendidikan_terakhir ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tanggal Registrasi</div>
                        <div class="fw-semibold text-gray-800">@tanggal($anggota->created_at ?? '-')</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tanggal Menjadi Anggota</div>
                        <div class="fw-semibold text-gray-800">@tanggal($anggota->tgl_anggota ?? null)</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Cabang</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->cabang?->nama_cabang ?? '-' }} ({{ $anggota->cabang?->kode_cabang ?? '-' }})</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Alamat Lengkap</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->alamat ?? '-' }}</div>
                    </div>
                </div>
                @if($anggota->status_pendaftaran === 'ditolak' || $anggota->status_pendaftaran === 'disetujui')
                <hr class="my-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Tanggal Verifikasi Pendaftaran</div>
                        <div class="fw-semibold text-gray-800">@tanggal($anggota->tgl_verifikasi_pendaftaran ?? null)</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Catatan Verifikasi</div>
                        <div class="fw-semibold text-gray-800">{{ $anggota->catatan_verifikasi_pendaftaran ?? '-' }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow mb-3">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Akun Login & Ringkasan</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Akun Terhubung</div>
                        <div class="fw-semibold text-gray-800">
                            @if($anggota->user)
                                <span class="badge bg-success text-white">Ya</span>
                            @else
                                <span class="badge bg-secondary text-white">Belum dibuat</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Wajib Ganti Password</div>
                        <div class="fw-semibold text-gray-800">
                            @if(($anggota->user?->force_change_password ?? 0) == '1' || ($anggota->user?->force_change_password ?? false))
                                <span class="badge bg-warning text-dark">Ya</span>
                            @else
                                <span class="badge bg-success text-white">Tidak</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Terakhir Login</div>
                        <div class="fw-semibold text-gray-800">@tanggalWaktu($anggota->user?->last_login_at ?? null)</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Password Diubah</div>
                        <div class="fw-semibold text-gray-800">@tanggalWaktu($anggota->user?->password_changed_at ?? null)</div>
                    </div>
                </div>
                <hr class="my-3">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="small text-muted">Total Simpanan (Semua Jenis)</div>
                        <div class="fs-4 fw-bold text-orange">Rp {{ number_format((float) ($totalSimpanan ?? 0), 0, ',', '.') }}</div>
                    </div>
                    @if(($simpananPerJenis ?? collect())->isNotEmpty())
                    <div class="col-12">
                        <div class="small text-muted mb-2">Rincian per Jenis Simpanan</div>
                        <div class="list-group">
                            @foreach($simpananPerJenis as $namaJenis => $nominal)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-2 py-1 border-0 border-bottom rounded-0">
                                    <span>{{ $namaJenis }}</span>
                                    <span class="fw-semibold text-primary">Rp {{ number_format((float)$nominal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="col-12">
                        <div class="small text-muted">Jumlah Pinjaman Aktif (berjalan / dicairkan)</div>
                        <div class="fs-4 fw-bold text-primary">
                            {{ $daftarPinjaman->whereIn('status', ['berjalan','dicairkan','diverifikasi','disetujui'])->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Riwayat Simpanan</h6>
            </div>
            <div class="card-body">
                @if($daftarSimpanan->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover datatable align-middle">
                        <thead>
                            <tr>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Jenis Simpanan</th>
                                <th class="text-center">Keterangan</th>
                                <th class="text-center">Jumlah (Rp)</th>
                                <th class="text-center">Saldo Berjalan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($daftarSimpanan->reverse() as $s)
                                <tr>
                                    <td class="text-center">@tanggal($s->tanggal ?? null)</td>
                                    <td class="text-center">{{ $s->jenisSimpanan?->nama_jenis ?? '-' }}</td>
                                    <td class="text-center">{{ $s->keterangan ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format((float)$s->nominal, 0, ',', '.') }}</td>
                                    <td class="text-end"><b>Rp {{ number_format((float)$s->saldo, 0, ',', '.') }}</b></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-secondary mb-0">Belum ada riwayat simpanan untuk anggota ini.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-light">
                <h6 class="m-0 font-weight-bold text-primary">Riwayat Pinjaman</h6>
            </div>
            <div class="card-body">
                @if($daftarPinjaman->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover datatable align-middle">
                        <thead>
                            <tr>
                                <th class="text-center">Tanggal Pengajuan</th>
                                <th class="text-center">Nomor Pinjaman</th>
                                <th class="text-center">Jenis Pinjaman</th>
                                <th class="text-center">Jumlah (Rp)</th>
                                <th class="text-center">Tenor</th>
                                <th class="text-center">Angsuran/Bulan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($daftarPinjaman as $p)
                                @php
                                    $st = $p->status ?? 'diajukan';
                                    $cls = 'secondary';
                                    if (in_array($st, ['disetujui','lunas'], true)) $cls = 'success';
                                    elseif (in_array($st, ['diajukan','diverifikasi'], true)) $cls = 'info';
                                    elseif (in_array($st, ['dicairkan','berjalan'], true)) $cls = 'warning';
                                    elseif (in_array($st, ['ditolak','dibatalkan','ditolak_pencairan'], true)) $cls = 'danger';
                                    $lunas = $p->angsuran->where('status', 'lunas')->count();
                                    $totalAngs = $p->angsuran->count() ?: $p->tenor;
                                @endphp
                                <tr>
                                    <td class="text-center">@tanggal($p->tgl_pengajuan ?? null)</td>
                                    <td class="text-center">{{ $p->nomor_pinjaman ?? '-' }}</td>
                                    <td class="text-center">{{ $p->jenisPinjaman?->nama_jenis ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format((float)$p->jumlah_pinjaman, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $p->tenor }} bulan</td>
                                    <td class="text-end">Rp {{ number_format((float)$p->angsuran_per_bulan, 0, ',', '.') }}</td>
                                    <td class="text-center"><span class="badge bg-{{ $cls }} text-white px-2 py-1">{{ ucwords(str_replace('_',' ',$st)) }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('pinjaman.show', $p->id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-secondary mb-0">Belum ada pengajuan pinjaman untuk anggota ini.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endpush
