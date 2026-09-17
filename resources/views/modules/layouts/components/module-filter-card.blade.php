@if(empty($hideFilterBar))
@php
    $tampilCabang = !($isAnggotaFilter ?? false) && ($showCabang ?? true);
    $tampilTanggal = $showTanggal ?? true;
    $customList = $customFilterOptions ?? [];
    if (!is_array($customList)) { $customList = []; }
    $jumlahCustom = count($customList);
    $totalFieldFilter = ($tampilCabang ? 1 : 0) + ($tampilTanggal ? 2 : 0) + $jumlahCustom;

    if ($totalFieldFilter === 0) {
        $colBtn = 'col-12';
    } elseif ($totalFieldFilter <= 1) {
        $colCustom = 'col-md-5';
        $colCabang = 'col-md-5';
        $colTgl = 'col-md-3';
        $colBtn = 'col-md-2';
    } elseif ($totalFieldFilter === 2) {
        $colCustom = 'col-md-3';
        $colCabang = 'col-md-4';
        $colTgl = 'col-md-3';
        $colBtn = 'col-md-2';
    } elseif ($totalFieldFilter === 3) {
        $colCustom = 'col-md-3';
        $colCabang = 'col-md-3';
        $colTgl = 'col-md-2';
        $colBtn = 'col-md-2';
    } elseif ($totalFieldFilter === 4) {
        $colCustom = 'col-md-2';
        $colCabang = 'col-md-2';
        $colTgl = 'col-md-2';
        $colBtn = 'col-md-2';
    } else {
        $colCustom = 'col-md-2';
        $colCabang = 'col-md-2';
        $colTgl = 'col-md-2';
        $colBtn = 'col-md-2';
    }
@endphp
<div class="card shadow-sm mb-4 border-0">
    <div class="card-body py-3 px-4">
        <form action="{{ $filterFormAction ?? '' }}" method="POST" class="row g-2 align-items-end">
            @csrf
            @if($tampilCabang)
                @if(!empty($lockCabangToUser))
                    <div class="{{ $colCabang }} col-12">
                        <label class="form-label small fw-semibold mb-1 text-secondary">
                            Cabang <span class="small text-muted ms-1 fst-italic">(Terkunci Akun)</span>
                        </label>
                        <input
                            type="text"
                            class="form-control form-control-sm bg-light"
                            value="{{ $lockedCabangKode ? $lockedCabangKode . ' — ' : '' }}{{ $lockedCabangNama ?? 'Cabang Anda' }}"
                            disabled
                            tabindex="-1"
                            aria-disabled="true"
                            style="cursor: not-allowed;"
                        >
                        <input type="hidden" name="cabang_id" value="{{ $lockedCabangId ?? '' }}">
                    </div>
                @else
                    <div class="{{ $colCabang }} col-12">
                        <label class="form-label small fw-semibold mb-1 text-secondary">Cabang</label>
                        <select name="cabang_id" class="form-select form-select-sm select2-single" style="width: 100%;" data-placeholder="- Pilih -">
                            <option value="">- Pilih -</option>
                            @foreach($cabangFilterList ?? [] as $c)
                                <option value="{{ $c->id }}" {{ old('cabang_id', $currentModuleFilter['cabang_id'] ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->kode_cabang }} — {{ $c->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @endif

            @foreach($customList as $customKey => $cfgCustom)
                @php
                    $cfgKey = $cfgCustom['key'] ?? $customKey;
                    $cfgLabel = $cfgCustom['label'] ?? ucfirst($cfgKey);
                    $cfgPlaceholder = '- Pilih -';
                    $cfgOptions = $cfgCustom['options'] ?? [];
                    $currentVal = old($cfgKey, $currentModuleFilter[$cfgKey] ?? '');
                @endphp
                <div class="{{ $colCustom }} col-12">
                    <label class="form-label small fw-semibold mb-1 text-secondary">{{ $cfgLabel }}</label>
                    <select name="{{ $cfgKey }}" class="form-select form-select-sm select2-single" style="width: 100%;" data-placeholder="{{ $cfgPlaceholder }}">
                        <option value="">- Pilih -</option>
                        @foreach($cfgOptions as $opt)
                            @php
                                $optVal = $opt['value'] ?? '';
                                $optLabel = $opt['label'] ?? $optVal;
                            @endphp
                            <option value="{{ $optVal }}" {{ strval($currentVal) === strval($optVal) ? 'selected' : '' }}>
                                {{ $optLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            @if($tampilTanggal)
            <div class="{{ $colTgl }} col-6">
                <label class="form-label small fw-semibold mb-1 text-secondary">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ old('tanggal_awal', $currentModuleFilter['tanggal_awal'] ?? '') }}">
            </div>
            <div class="{{ $colTgl }} col-6">
                <label class="form-label small fw-semibold mb-1 text-secondary">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ old('tanggal_akhir', $currentModuleFilter['tanggal_akhir'] ?? '') }}">
            </div>
            @endif

            <div class="{{ $colBtn }} col-12 d-flex gap-2">
                <button type="submit" name="apply_filter" value="1" class="btn btn-sm text-white flex-grow-1 fw-semibold border-0" style="background-color: #f97316;">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <button type="submit" name="_reset_filter" value="1" class="btn btn-sm btn-outline-secondary flex-grow-1 fw-semibold">
                    Reset
                </button>
            </div>
        </form>
    </div>
</div>

@if(!empty($moduleFilterSummary))
<div class="alert alert-info border-0 mb-4 py-2 px-3 small shadow-sm" role="alert" style="background-color: #eef6ff; color: #1e40af;">
    <i class="bi bi-info-circle me-2"></i>{{ $moduleFilterSummary }}
</div>
@endif
@endif
