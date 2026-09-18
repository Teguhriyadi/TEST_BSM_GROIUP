@php
$statusOpts = [
    '' => 'Semua Status',
    'aktif' => 'Hanya Aktif',
    'nonaktif' => 'Hanya Nonaktif',
];
@endphp
<div class="row g-2 align-items-end ms-1">
    <div class="col-md-3">
        <label class="form-label small fw-medium mb-1">Cari Dokumen</label>
        <input type="text" class="form-control form-control-sm" name="cari" value="{{ $cari ?? '' }}" placeholder="Ketik kode / nama / deskripsi">
    </div>
    <div class="col-md-3">
        <label class="form-label small fw-medium mb-1">Filter Status</label>
        <select class="form-select form-select-sm select2-single" name="status_filter" data-placeholder="-- Semua Status --">
            @foreach($statusOpts as $v => $l)
                <option value="{{ $v }}" @selected(($statusFilter ?? '') === (string) $v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-auto">
        <button type="submit" class="btn btn-sm btn-orange">
            <i class="bi bi-search me-1"></i> Filter
        </button>
        <a href="{{ route('master-dokumen.index') }}" class="btn btn-sm btn-secondary">
            Reset
        </a>
    </div>
</div>
