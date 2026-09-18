<?php

namespace App\Http\Controllers;

use App\Services\NeracaService;
use App\Traits\WithModuleFilter;
use App\Traits\WithExportable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LabaRugiController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    private function prepareView(string $mode, Request $request, NeracaService $svc)
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));

        if ($mode === 'kumulatif') {
            $thnSekarang = date('Y');
            $dari = $request->input('tanggal_awal', "{$thnSekarang}-01-01");
            $sampai = $request->input('tanggal_akhir', date('Y-m-d'));
            $routeName = 'laba-rugi.kumulatif';
            $judulMode = 'Kumulatif (YTD)';
        } else {
            $dari = $request->input('tanggal_awal', date('Y-m-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-t'));
            $routeName = 'laba-rugi.periode';
            $judulMode = 'Periode';
        }

        $data = $svc->getLabaRugi(
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
        $filterFormAction = route($routeName);
        $exportPdfUrl = $mode === 'periode' ? route('laba-rugi.periode.exportPdf') : route('laba-rugi.periode.exportPdf');
        $exportExcelUrl = $mode === 'periode' ? route('laba-rugi.periode.exportExcel') : route('laba-rugi.periode.exportExcel');
        return view("modules.laba-rugi.index", compact(
            'mode',
            'judulMode',
            'data',
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
    }

    public function periode(Request $request, NeracaService $svc)
    {
        try {
            return $this->prepareView('periode', $request, $svc);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat laba rugi periode: ' . $e->getMessage());
        }
    }

    public function kumulatif(Request $request, NeracaService $svc)
    {
        try {
            return $this->prepareView('kumulatif', $request, $svc);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat laba rugi kumulatif: ' . $e->getMessage());
        }
    }

    // ==================== EXPORT ====================
    public function exportPdf(Request $request, NeracaService $svc)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $dari = $request->input('tanggal_awal', date('Y-m-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-t'));
            $data = $svc->getLabaRugi(Carbon::parse($dari), Carbon::parse($sampai), $cabangId);

            $header = [
                ['key' => 'kelompok', 'label' => 'Kelompok', 'align' => 'text-start'],
                ['key' => 'kode', 'label' => 'Kode Akun', 'align' => 'text-start'],
                ['key' => 'nama', 'label' => 'Nama Akun', 'align' => 'text-start'],
                ['key' => 'nominal', 'label' => 'Nominal (Rp)', 'align' => 'text-end'],
            ];
            $rows = collect();
            $pendapatanTotal = 0;
            $bebanTotal = 0;
            foreach (($data['pendapatan']['rows'] ?? []) as $r) {
                $v = (float)($r['value'] ?? 0);
                $pendapatanTotal += $v;
                $rows->push([
                    'kelompok' => $r['is_group'] ?? false ? '<strong>400 — Pendapatan</strong>' : '',
                    'kode' => e($r['kode_akun'] ?? ''),
                    'nama' => ($r['is_group'] ?? false) ? '<strong>' . e($r['label'] ?? '') . '</strong>' : e($r['label'] ?? ''),
                    'nominal' => $v > 0 ? 'Rp ' . number_format($v, 0, ',', '.') : '',
                    '__is_summary' => (bool)($r['is_group'] ?? false),
                ]);
            }
            $rows->push(['kelompok' => '', 'kode' => '', 'nama' => '<strong>Total Pendapatan</strong>', 'nominal' => '<strong>Rp ' . number_format($pendapatanTotal, 0, ',', '.') . '</strong>', '__is_summary' => true]);
            $rows->push(['kelompok' => '', 'kode' => '', 'nama' => '', 'nominal' => '']);
            foreach (($data['beban']['rows'] ?? []) as $r) {
                $v = (float)($r['value'] ?? 0);
                $bebanTotal += $v;
                $rows->push([
                    'kelompok' => $r['is_group'] ?? false ? '<strong>500 — Beban</strong>' : '',
                    'kode' => e($r['kode_akun'] ?? ''),
                    'nama' => ($r['is_group'] ?? false) ? '<strong>' . e($r['label'] ?? '') . '</strong>' : e($r['label'] ?? ''),
                    'nominal' => $v > 0 ? 'Rp ' . number_format($v, 0, ',', '.') : '',
                    '__is_summary' => (bool)($r['is_group'] ?? false),
                ]);
            }
            $rows->push(['kelompok' => '', 'kode' => '', 'nama' => '<strong>Total Beban</strong>', 'nominal' => '<strong>Rp ' . number_format($bebanTotal, 0, ',', '.') . '</strong>', '__is_summary' => true]);
            $labaBersih = $pendapatanTotal - $bebanTotal;
            $summaryFooter = [[
                'cells' => [
                    0 => '',
                    1 => '',
                    2 => '<strong>LABA / (RUGI) BERSIH PERIODE</strong>',
                    'nominal' => '<strong>Rp ' . number_format($labaBersih, 0, ',', '.') . '</strong>',
                ],
            ]];
            $rowFn = fn($r, $n) => $r;
            return $this->generatePdf('Laba Rugi Periode', 'laba-rugi-periode', $header, $rows, $rowFn, $dari, $sampai, $cabangId, 'landscape', $summaryFooter);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF Laba Rugi: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request, NeracaService $svc)
    {
        try {
            return $this->exportPdf($request, $svc);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export Excel Laba Rugi: ' . $e->getMessage());
        }
    }
}
