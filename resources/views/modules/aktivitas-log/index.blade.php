@extends('modules.layouts.master')
@push('title', 'Aktivitas Log')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Aktivitas Log Sistem</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Aktivitas Log</li>
        </ol>
    </nav>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-funnel me-2"></i>Filter Data</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('aktivitas-log.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" class="form-control" value="{{ old('tanggal_awal', $tanggalAwal?->format('Y-m-d')) }}" placeholder="Pilih tanggal awal">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" class="form-control" value="{{ old('tanggal_akhir', $tanggalAkhir?->format('Y-m-d')) }}" placeholder="Pilih tanggal akhir">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Cabang</label>
                    <select name="cabang_id" class="form-select select2" data-placeholder="-- Semua Cabang --">
                        <option value=""></option>
                        @foreach ($daftarCabang as $c)
                            <option value="{{ $c->id }}" {{ (string)$cabangId === (string)$c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">User</label>
                    <select name="users_id" class="form-select select2" data-placeholder="-- Semua User --">
                        <option value=""></option>
                        @foreach ($daftarUser as $u)
                            <option value="{{ $u->id }}" {{ (string)$userId === (string)$u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Tipe Aktivitas</label>
                    <select name="tipe_aktivitas" class="form-select select2" data-placeholder="-- Semua Tipe --">
                        <option value=""></option>
                        @foreach ($daftarTipe as $t)
                            <option value="{{ $t }}" {{ $tipe === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end gap-2 pt-1">
                    <a href="{{ route('aktivitas-log.index') }}" class="btn btn-sm btn-secondary px-4">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary text-white px-4">
                        <i class="bi bi-search me-1"></i>Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-orange">Daftar Riwayat Aktivitas</h6>
        <div class="small text-muted">Total: {{ $aktivitas->count() }} data</div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle datatable" width="100%" cellspacing="0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Cabang</th>
                        <th>Tipe Aktivitas</th>
                        <th>Modul / Data</th>
                        <th>Deskripsi</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($aktivitas as $log)
                        @php
                            $badgeTipe = 'bg-secondary';
                            $tipe = strtoupper($log->tipe_aktivitas ?? '');
                            if (str_contains($tipe, 'LOGIN')) $badgeTipe = 'bg-success';
                            elseif (str_contains($tipe, 'LOGOUT')) $badgeTipe = 'bg-dark';
                            elseif (str_contains($tipe, 'CREATE')) $badgeTipe = 'bg-primary';
                            elseif (str_contains($tipe, 'UPDATE')) $badgeTipe = 'bg-orange text-white';
                            elseif (str_contains($tipe, 'DELETE')) $badgeTipe = 'bg-danger';
                            elseif (str_contains($tipe, 'UBAH_PASSWORD')) $badgeTipe = 'bg-info';
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>@tanggalWaktu($log->created_at)</td>
                            <td>{{ $log->user?->nama ?? '-' }}
                                @if ($log->user?->role)
                                    <div class="small text-muted">{{ $log->user->role->nama_role }}</div>
                                @endif
                            </td>
                            <td>{{ $log->cabang?->nama_cabang ?? '-' }}</td>
                            <td><span class="badge {{ $badgeTipe }}">{{ $log->tipe_aktivitas }}</span></td>
                            <td class="small">
                                @if ($log->model_type)
                                    @php
                                        $parts = explode('\\', $log->model_type);
                                        $shortName = end($parts);
                                    @endphp
                                    {{ $shortName }}
                                    @if ($log->model_id)
                                        <div class="text-muted" style="font-size: 0.7rem;">ID: {{ substr($log->model_id, 0, 8) }}...</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="small">{{ $log->deskripsi ?? '-' }}</td>
                            <td class="small">
                                {{ $log->ip_address ?? '-' }}
                                @if ($log->users_agent)
                                    <div class="text-muted" title="{{ $log->users_agent }}" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.7rem;">
                                        {{ $log->users_agent }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endpush
