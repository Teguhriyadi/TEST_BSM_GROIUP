@extends('modules.layouts.master')

@push('title')
<title>Setting Persyaratan Dokumen Pinjaman | BSM Koperasi</title>
@endpush

@push('breadcrumbs')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5 small">
        <li class="breadcrumb-item text-sm text-dark"><a class="opacity-5 text-dark" href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Setting Persyaratan Dokumen Pinjaman</li>
    </ol>
    <h6 class="font-weight-bolder mb-0">Setting Persyaratan Dokumen per Jenis Pinjaman</h6>
</nav>
@endpush

@push('page-modules')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible text-white small" role="alert">
            <span class="text-sm">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible text-white small" role="alert">
            <span class="text-sm">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($jenisList->count() === 0)
        <div class="card shadow mb-4">
            <div class="card-body text-center py-5">
                <div class="fw-semibold text-gray-800 mb-2">Belum Ada Jenis Pinjaman</div>
                <p class="text-muted small mb-3">Tambahkan jenis pinjaman terlebih dahulu sebelum mensetting persyaratan dokumen.</p>
                @haspermission('JENIS_PINJAMAN_CREATE')
                    <a href="{{ route('jenis-pinjaman.create') }}" class="btn btn-sm btn-orange">
                        <i class="bi bi-plus-lg me-1"></i> Buat Jenis Pinjaman
                    </a>
                @endhaspermission
            </div>
        </div>
    @endif

    @if($jenisList->count() > 0)
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow sticky-top mb-3">
                    <div class="card-header py-3 bg-orange-50">
                        <h6 class="m-0 font-weight-bold text-orange">
                            <i class="bi bi-list-ul me-1"></i> Pilih Jenis Pinjaman
                        </h6>
                    </div>
                    <div class="list-group list-group-flush p-2">
                        @foreach($jenisList as $j)
                            @php
                                $isActive = $selectedJenis && (string) $selectedJenis->id === (string) $j->id;
                                $countDok = $j->dokumenPersyaratan->count() ?? 0;
                            @endphp
                            <a href="{{ route('setting-persyaratan-pinjaman.index', ['jenis_pinjaman_id' => $j->id]) }}"
                               class="list-group-item list-group-item-action border-0 rounded mb-1 d-flex align-items-center justify-content-between px-3 py-2 {{ $isActive ? 'bg-orange text-white active' : '' }}">
                                <div>
                                    <div class="fw-medium small">{{ $j->nama_jenis }}</div>
                                    <div class="small {{ $isActive ? 'opacity-75' : 'text-muted' }}">Tenor {{ $j->tenor_minimal ? number_format($j->tenor_minimal, 0, ',', '.') : '?' }} - {{ $j->tenor_maksimal ? number_format($j->tenor_maksimal, 0, ',', '.') : '?' }} bulan | Bunga {{ number_format($j->bunga_tahunan, 2, ',', '.') }}%</div>
                                </div>
                                <div>
                                    <span class="badge {{ $isActive ? 'bg-white text-orange' : 'bg-orange text-white' }} px-2 py-1 small">{{ $countDok }} dok</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @if(! $selectedJenis)
                    <div class="card shadow">
                        <div class="card-body text-center py-5 text-muted">
                            <p class="mb-2">Pilih salah satu jenis pinjaman di panel kiri untuk mengatur persyaratan dokumen.</p>
                        </div>
                    </div>
                @else
                    @php
                        $selectedIds = $selectedJenis->dokumenPersyaratan
                            ? $selectedJenis->dokumenPersyaratan->pluck('id')->map(fn($v) => (string) $v)->values()->all()
                            : [];
                        $wajibMap = [];
                        foreach (($selectedJenis->dokumenPersyaratan ?? collect()) as $m) {
                            $wajibMap[(string) $m->id] = !empty($m->pivot?->is_wajib);
                        }
                    @endphp
                    <form action="{{ route('setting-persyaratan-pinjaman.update', $selectedJenis->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-orange">
                                    <i class="bi bi-files me-1"></i> Setting Persyaratan Dokumen
                                    <small class="text-muted fw-normal ms-2">{{ $selectedJenis->nama_jenis }}</small>
                                </h6>
                                <div class="d-flex gap-2">
                                    @haspermission('SETTING_PERSYARATAN_UPDATE')
                                        <button type="submit" class="btn btn-sm btn-orange">
                                            <i class="bi bi-save me-1"></i> Simpan Pengaturan
                                        </button>
                                    @endhaspermission
                                </div>
                            </div>
                            <div class="card-body">
                                @if($masterAktif->count() === 0)
                                    <div class="alert alert-warning mb-0 small">
                                        Belum ada Master Dokumen yang aktif. Silakan buat master dokumen terlebih dahulu di menu Master Dokumen.
                                        @haspermission('MASTER_DOKUMEN_CREATE')
                                            <br><a href="{{ route('master-dokumen.create') }}" class="fw-semibold text-warning text-decoration-underline">Tambah Master Dokumen</a>
                                        @endhaspermission
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover align-middle small mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40" class="text-center">
                                                        <div class="form-check m-0 p-0 d-flex justify-content-center">
                                                            <input type="checkbox" id="checkAll" class="form-check-input" style="cursor:pointer;">
                                                        </div>
                                                    </th>
                                                    <th width="120">Kode</th>
                                                    <th>Nama Dokumen</th>
                                                    <th width="120" class="text-center">Format</th>
                                                    <th width="110" class="text-center">Wajib</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($masterAktif as $i => $md)
                                                    @php
                                                        $checked = in_array((string) $md->id, $selectedIds, true);
                                                        $isWajib = $wajibMap[(string) $md->id] ?? true;
                                                    @endphp
                                                    <tr class="{{ $checked ? 'bg-orange-50' : '' }}">
                                                        <td class="text-center align-middle">
                                                            <div class="form-check m-0 p-0 d-flex justify-content-center">
                                                                <input class="form-check-input cekDok" type="checkbox" name="master_dokumen_ids[]"
                                                                    value="{{ $md->id }}" id="cek_{{ $md->id }}"
                                                                    @checked($checked)>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle">
                                                            <label for="cek_{{ $md->id }}" class="m-0 p-0 cursor-pointer fw-medium">{{ $md->kode_dokumen }}</label>
                                                        </td>
                                                        <td class="align-middle">
                                                            <label for="cek_{{ $md->id }}" class="m-0 p-0 cursor-pointer">
                                                                <div class="fw-semibold text-gray-800">{{ $md->nama_dokumen }}</div>
                                                                @if(! empty($md->deskripsi))
                                                                    <div class="text-muted small mt-1">{{ $md->deskripsi }}</div>
                                                                @endif
                                                            </label>
                                                        </td>
                                                        <td class="align-middle text-center">
                                                            @php
                                                                $arrFormat = $md->getFormatListArray();
                                                            @endphp
                                                            @if(! empty($arrFormat))
                                                                @foreach($arrFormat as $f)
                                                                    <span class="badge bg-light text-gray-800 border me-1 mb-1 px-2 py-1">.{{ strtoupper($f) }}</span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted small">default</span>
                                                            @endif
                                                        </td>
                                                        <td class="align-middle text-center">
                                                            <div class="form-check form-switch m-0 p-0 d-flex justify-content-center">
                                                                <input class="form-check-input" type="checkbox" name="wajib[{{ $md->id }}]" id="wajib_{{ $md->id }}" value="1"
                                                                    @checked($isWajib)>
                                                                <label class="form-check-label d-none" for="wajib_{{ $md->id }}">Wajib</label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="small text-muted mt-3">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Centang kolom kiri untuk memasukan dokumen ke daftar persyaratan jenis ini. Aktifkan switch Wajib untuk dokumen yang WAJIB diunggah sebelum pengajuan (bisa diberi tanda merah pada anggota yang belum upload).
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
@endpush

@push('scripts')
<script>
(function () {
    var $checkAll = document.getElementById('checkAll');
    if ($checkAll) {
        var $list = Array.prototype.slice.call(document.querySelectorAll('.cekDok'));
        function refreshCheckAllState() {
            var total = $list.length;
            var checkedCount = $list.filter(function (c) { return c.checked; }).length;
            $checkAll.checked = total > 0 && checkedCount === total;
            $checkAll.indeterminate = checkedCount > 0 && checkedCount < total;
        }
        $checkAll.addEventListener('change', function () {
            var on = $checkAll.checked;
            $list.forEach(function (c) { c.checked = on; });
            refreshRowsColor();
        });
        $list.forEach(function (c) { c.addEventListener('change', function () { refreshCheckAllState(); refreshRowsColor(); }); });
        function refreshRowsColor() {
            $list.forEach(function (c) {
                var tr = c.closest && c.closest('tr');
                if (!tr) return;
                if (c.checked) tr.classList.add('bg-orange-50');
                else tr.classList.remove('bg-orange-50');
            });
        }
        refreshCheckAllState();
        refreshRowsColor();
    }
})();
</script>
@endpush
