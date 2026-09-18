@extends('modules.layouts.master')
@push('title', 'Jurnal Harian')
@push('page-modules')
@include('modules.layouts.components.module-filter-card', [
    'showCabang' => true,
    'showTanggal' => true,
])
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Jurnal Harian</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Jurnal Harian</li>
        </ol>
    </nav>
</div>

<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card shadow">
            <div class="card-header py-3 bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="m-0 font-weight-bold text-primary">Rekapitulasi Jurnal Harian</h6>
                    <div class="small text-muted mt-1">
                        Periode: {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}
                        @if($cabangId)
                            · Cabang: {{ \App\Models\Cabang::find($cabangId)?->nama_cabang ?? '-' }}
                        @else
                            · Semua Cabang (Konsolidasi)
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Grand Total Selama Periode</div>
                    <div class="fw-bold text-orange">Debet: Rp {{ number_format($totalAllDebet, 0, ',', '.') }}</div>
                    <div class="fw-bold text-orange">Kredit: Rp {{ number_format($totalAllKredit, 0, ',', '.') }}</div>
                </div>
            </div>
            @if(count($grouped) > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    @foreach($grouped as $tgl => $g)
                    <div class="mb-4">
                        <div class="px-4 py-2 bg-orange text-white fw-semibold d-flex justify-content-between align-items-center">
                            <span>{{ \Carbon\Carbon::parse($tgl)->format('l, d F Y') }}</span>
                            <span class="small">
                                Voucher: {{ $g['jumlah_voucher'] }} buah
                            </span>
                        </div>
                        <table class="table table-striped table-bordered align-middle mb-0">
                            <thead class="bg-primary text-white small">
                                <tr>
                                    <th width="13%" class="text-center">No. Jurnal</th>
                                    <th width="8%" class="text-center">Tipe</th>
                                    <th width="8%" class="text-center">Cabang</th>
                                    <th width="34%">Keterangan</th>
                                    <th width="5%" class="text-center">Kode</th>
                                    <th width="17%">Akun</th>
                                    <th width="8%" class="text-end">Debet</th>
                                    <th width="8%" class="text-end">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($g['headers'] as $h)
                                    @php $firstRow = true; $totalD = 0; $totalK = 0; @endphp
                                    @foreach($h->details as $d)
                                        @php
                                            $totalD += (float)$d->debet;
                                            $totalK += (float)$d->kredit;
                                        @endphp
                                    <tr>
                                        @if($firstRow)
                                            <td class="fw-semibold align-top" rowspan="{{ $h->details->count() }}">{{ $h->nomor_jurnal }}</td>
                                            <td class="text-center align-top" rowspan="{{ $h->details->count() }}">
                                                @if($h->tipe === 'otomatis')
                                                    <span class="badge bg-info text-white">Auto</span>
                                                @else
                                                    <span class="badge bg-secondary text-white">Man</span>
                                                @endif
                                            </td>
                                            <td class="text-center small align-top" rowspan="{{ $h->details->count() }}">
                                                {{ $h->cabang?->kode_cabang ?? '-' }}
                                            </td>
                                            <td class="small align-top" rowspan="{{ $h->details->count() }}">
                                                {{ \Illuminate\Support\Str::limit($h->keterangan ?? '-', 80) }}
                                            </td>
                                            @php $firstRow = false; @endphp
                                        @endif
                                        <td class="text-center small">{{ $d->coa?->kode_akun ?? '-' }}</td>
                                        <td class="small">{{ $d->coa?->nama_akun ?? '-' }}</td>
                                        <td class="text-end small">
                                            {{ $d->debet > 0 ? 'Rp ' . number_format($d->debet, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="text-end small">
                                            {{ $d->kredit > 0 ? 'Rp ' . number_format($d->kredit, 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-light fw-semibold">
                                        <td colspan="6" class="text-end pe-4">Subtotal Voucher</td>
                                        <td class="text-end">Rp {{ number_format($totalD, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($totalK, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light fw-bold">
                                    <td colspan="6" class="text-end pe-4 py-3">Total Tanggal {{ \Carbon\Carbon::parse($tgl)->format('d/m/Y') }}</td>
                                    <td class="text-end py-3">Rp {{ number_format($g['total_debet'], 0, ',', '.') }}</td>
                                    <td class="text-end py-3">Rp {{ number_format($g['total_kredit'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-journal-check display-4 d-block mb-3 opacity-25"></i>
                Tidak ada transaksi jurnal pada periode yang dipilih.
            </div>
            @endif
        </div>
    </div>
</div>
@endpush
