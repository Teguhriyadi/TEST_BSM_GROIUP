@php
$selectedIds = is_array($selectedIds ?? null) ? $selectedIds : [];
$selectedIds = array_map('strval', $selectedIds);
@endphp

<div class="card shadow mb-4 border-left-warning">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
        <h6 class="m-0 font-weight-bold text-orange">
            <i class="bi bi-files me-1"></i> Persyaratan Dokumen Wajib
        </h6>
        <small class="text-muted">
            Pilih dokumen yang wajib diunggah untuk jenis pinjaman ini
        </small>
    </div>
    <div class="card-body">
        @if(($masterDokumen ?? collect())->count() === 0)
            <div class="alert alert-warning mb-0">
                Belum ada master dokumen yang aktif. Silakan tambahkan master dokumen terlebih dahulu.
            </div>
        @else
            <div class="row g-3">
                @foreach($masterDokumen as $md)
                    @php
                        $checked = in_array((string) $md->id, $selectedIds, true);
                    @endphp
                    <div class="col-sm-6 col-xl-4">
                        <label class="form-check card card-body shadow-sm h-100 p-3 border {{ $checked ? 'border-orange bg-orange-50' : 'border-gray-200 bg-white' }}" style="cursor:pointer; transition: all 0.2s ease-in-out;">
                            <div class="d-flex align-items-start gap-2">
                                <input
                                    type="checkbox"
                                    name="master_dokumen_ids[]"
                                    value="{{ $md->id }}"
                                    class="form-check-input mt-1"
                                    {{ $checked ? 'checked' : '' }}>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-gray-800 mb-1">{{ $md->nama_dokumen }}</div>
                                    <div class="small text-muted mb-1">
                                        Kode: <span class="fw-medium">{{ $md->kode_dokumen }}</span>
                                    </div>
                                    @if(! empty($md->deskripsi))
                                        <div class="small text-muted">{{ $md->deskripsi }}</div>
                                    @endif
                                </div>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('master_dokumen_ids')
                <div class="small text-danger mt-2">{{ $message }}</div>
            @enderror
            @error('master_dokumen_ids.*')
                <div class="small text-danger mt-2">{{ $message }}</div>
            @enderror
        @endif
    </div>
</div>
