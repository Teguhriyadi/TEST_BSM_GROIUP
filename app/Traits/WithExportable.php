<?php

namespace App\Traits;

use App\Models\Cabang;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;

trait WithExportable
{
    protected function namaCabangExport(?string $cabangId): string
    {
        if (empty($cabangId)) return 'Seluruh Cabang';
        static $cache = [];
        if (isset($cache[$cabangId])) return $cache[$cabangId];
        $c = Cabang::where('id', $cabangId)->first(['kode_cabang', 'nama_cabang']);
        $cache[$cabangId] = $c ? ($c->kode_cabang . ' — ' . $c->nama_cabang) : '-';
        return $cache[$cabangId];
    }

    protected function periodeText(?string $tanggalAwal, ?string $tanggalAkhir): string
    {
        if (empty($tanggalAwal) && empty($tanggalAkhir)) return 'Seluruh Periode';
        $awal = $tanggalAwal ? Carbon::parse($tanggalAwal)->locale('id')->isoFormat('D MMMM Y') : 'Awal';
        $akhir = $tanggalAkhir ? Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y') : 'Akhir';
        return $awal . ' s/d ' . $akhir;
    }

    protected function generatePdf(
        string $judulLaporan,
        string $slugLaporan,
        array $headerKolom,
        iterable $dataRows,
        callable $rowCallback,
        ?string $tanggalAwal = null,
        ?string $tanggalAkhir = null,
        ?string $cabangId = null,
        string $orientation = 'landscape',
        array $summaryFooter = []
    ) {
        $periode = $this->periodeText($tanggalAwal, $tanggalAkhir);
        $infoCabang = $this->namaCabangExport($cabangId);

        $pdf = Pdf::loadView('modules.layouts.exports.pdf-generic', compact(
            'judulLaporan',
            'headerKolom',
            'dataRows',
            'rowCallback',
            'periode',
            'infoCabang',
            'summaryFooter'
        ));
        $pdf->setPaper('A4', $orientation);
        $filename = Str::slug($slugLaporan, '-') . '-' . date('YmdHis') . '.pdf';
        return $pdf->download($filename);
    }

    protected function generateExcel(
        string $judulLaporan,
        string $slugLaporan,
        array $headerKolom,
        iterable $dataRows,
        callable $rowCallback,
        ?string $tanggalAwal = null,
        ?string $tanggalAkhir = null,
        ?string $cabangId = null,
        array $summaryFooter = []
    ) {
        $periode = $this->periodeText($tanggalAwal, $tanggalAkhir);
        $infoCabang = $this->namaCabangExport($cabangId);

        $html = view('modules.layouts.exports.xls-generic', compact(
            'judulLaporan',
            'headerKolom',
            'dataRows',
            'rowCallback',
            'periode',
            'infoCabang',
            'summaryFooter'
        ))->render();

        $filename = Str::slug($slugLaporan, '-') . '-' . date('YmdHis') . '.xls';
        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
