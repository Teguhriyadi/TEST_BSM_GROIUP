<?php

namespace App\Http\Controllers;

use App\Services\BukuBesarService;
use App\Traits\WithExportable;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RekapKasNonKasController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request, BukuBesarService $svc)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $dari = $request->input('tanggal_awal', date('Y-m-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-t'));

            $rekap = $svc->getRekapHarianKasNonKas(
                Carbon::parse($dari),
                Carbon::parse($sampai),
                $cabangId,
            );

            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $cabangId,
                'tanggal_awal' => $dari,
                'tanggal_akhir' => $sampai,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('rekap-kas-non-kas.index');
            $exportPdfUrl = route('rekap-kas-non-kas.exportPdf');
            $exportExcelUrl = route('rekap-kas-non-kas.exportExcel');
            return view("modules.rekap-kas-non-kas.index", compact(
                'rekap',
                'dari',
                'sampai',
                'cabangId',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
                'exportPdfUrl',
                'exportExcelUrl',
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat rekap kas: ' . $e->getMessage());
        }
    }

    private function getExportDataRekap(Request $request, BukuBesarService $svc): array
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
        if (!$isAdmin && $user?->cabang_id) {
            $cabangId = $user->cabang_id;
        }
        $dari = $request->input('tanggal_awal', date('Y-m-01'));
        $sampai = $request->input('tanggal_akhir', date('Y-m-t'));

        $rekap = $svc->getRekapHarianKasNonKas(
            Carbon::parse($dari),
            Carbon::parse($sampai),
            $cabangId,
        );

        $rows = [];
        $no = 1;
        foreach ($rekap['rows'] as $row) {
            $netKas = $row['kas_masuk'] - $row['kas_keluar'];
            $netNonKas = $row['non_kas_debet'] - $row['non_kas_kredit'];
            $rows[] = [
                'no' => $no++,
                'tanggal' => Carbon::parse($row['tanggal'])->locale('id')->isoFormat('D MMMM Y'),
                'kas_masuk' => $row['kas_masuk'],
                'kas_keluar' => $row['kas_keluar'],
                'net_kas' => $netKas,
                'non_kas_debet' => $row['non_kas_debet'],
                'non_kas_kredit' => $row['non_kas_kredit'],
                'net_non_kas' => $netNonKas,
                'jml_transaksi' => $row['jml_transaksi'],
            ];
        }

        $t = $rekap['total'];
        $summary = [
            [
                'no' => '',
                'tanggal' => 'TOTAL SELURUHNYA',
                'kas_masuk' => $t['kas_masuk'],
                'kas_keluar' => $t['kas_keluar'],
                'net_kas' => round($t['kas_masuk'] - $t['kas_keluar'], 2),
                'non_kas_debet' => $t['non_kas_debet'],
                'non_kas_kredit' => $t['non_kas_kredit'],
                'net_non_kas' => round($t['non_kas_debet'] - $t['non_kas_kredit'], 2),
                'jml_transaksi' => $t['jml_transaksi'],
            ]
        ];

        return [$rows, $summary, $cabangId, $dari, $sampai];
    }

    public function exportPdf(Request $request, BukuBesarService $svc)
    {
        [$rows, $summary, $cabangId, $dari, $sampai] = $this->getExportDataRekap($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'tanggal', 'label' => 'Tanggal', 'align' => 'text-start'],
            ['key' => 'kas_masuk', 'label' => 'Kas Masuk (Rp)', 'align' => 'text-end'],
            ['key' => 'kas_keluar', 'label' => 'Kas Keluar (Rp)', 'align' => 'text-end'],
            ['key' => 'net_kas', 'label' => 'Net Kas (Rp)', 'align' => 'text-end'],
            ['key' => 'non_kas_debet', 'label' => 'Non Kas Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'non_kas_kredit', 'label' => 'Non Kas Kredit (Rp)', 'align' => 'text-end'],
            ['key' => 'net_non_kas', 'label' => 'Net Non Kas (Rp)', 'align' => 'text-end'],
            ['key' => 'jml_transaksi', 'label' => 'Jml Transaksi', 'align' => 'text-center'],
        ];

        return $this->generatePdf(
            'Rekap Harian Kas dan Non Kas',
            'rekap-kas-non-kas',
            $headerKolom,
            $rows,
            fn($row) => $row,
            $dari,
            $sampai,
            $cabangId,
            'landscape',
            $summary
        );
    }

    public function exportExcel(Request $request, BukuBesarService $svc)
    {
        [$rows, $summary, $cabangId, $dari, $sampai] = $this->getExportDataRekap($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'tanggal', 'label' => 'Tanggal', 'align' => 'text-start'],
            ['key' => 'kas_masuk', 'label' => 'Kas Masuk (Rp)', 'align' => 'text-end'],
            ['key' => 'kas_keluar', 'label' => 'Kas Keluar (Rp)', 'align' => 'text-end'],
            ['key' => 'net_kas', 'label' => 'Net Kas (Rp)', 'align' => 'text-end'],
            ['key' => 'non_kas_debet', 'label' => 'Non Kas Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'non_kas_kredit', 'label' => 'Non Kas Kredit (Rp)', 'align' => 'text-end'],
            ['key' => 'net_non_kas', 'label' => 'Net Non Kas (Rp)', 'align' => 'text-end'],
            ['key' => 'jml_transaksi', 'label' => 'Jml Transaksi', 'align' => 'text-center'],
        ];

        return $this->generateExcel(
            'Rekap Harian Kas dan Non Kas',
            'rekap-kas-non-kas',
            $headerKolom,
            $rows,
            fn($row) => $row,
            $dari,
            $sampai,
            $cabangId,
            $summary
        );
    }
}
