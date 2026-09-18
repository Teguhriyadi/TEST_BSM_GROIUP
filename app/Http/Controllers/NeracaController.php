<?php

namespace App\Http\Controllers;

use App\Services\NeracaService;
use App\Traits\WithModuleFilter;
use App\Traits\WithExportable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class NeracaController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request, NeracaService $neracaSvc)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $cutoff = $request->input('tanggal_cutoff', date('Y-m-d'));
            $data = $neracaSvc->getNeraca(Carbon::parse($cutoff), $cabangId);
            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $cabangId,
                'tanggal_awal' => null,
                'tanggal_akhir' => null,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('neraca.index');
            $exportPdfUrl = route('neraca.exportPdf');
            $exportExcelUrl = route('neraca.exportExcel');
            $cutoffDisplay = $cutoff;
            return view("modules.neraca.index", compact(
                'data',
                'cabangId',
                'cutoff',
                'cutoffDisplay',
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
            return back()->with('error', 'Gagal memuat neraca: ' . $e->getMessage());
        }
    }

    // ==================== EXPORT ====================
    public function exportPdf(Request $request, NeracaService $neracaSvc)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $cutoff = $request->input('tanggal_cutoff', date('Y-m-d'));
            $data = $neracaSvc->getNeraca(Carbon::parse($cutoff), $cabangId);

            $header = [
                ['key' => 'keterangan', 'label' => 'Keterangan', 'align' => 'text-start'],
                ['key' => 'aktiva', 'label' => 'Aktiva (Rp)', 'align' => 'text-end'],
                ['key' => 'spacer', 'label' => '', 'align' => 'text-start'],
                ['key' => 'kw', 'label' => 'Kewajiban & Modal (Rp)', 'align' => 'text-end'],
            ];
            $rows = collect();
            $rowsAktiva = $data['aktiva']['rows'] ?? [];
            $rowsKewajiban = $data['pasiva']['rows'] ?? [];
            $max = max(count($rowsAktiva), count($rowsKewajiban));
            $totalAktiva = 0;
            $totalKw = 0;
            for ($i = 0; $i < $max; $i++) {
                $ra = $rowsAktiva[$i] ?? null;
                $rk = $rowsKewajiban[$i] ?? null;
                $labelA = $ra['label'] ?? '';
                $valA = (float)($ra['value'] ?? 0);
                $labelK = $rk['label'] ?? '';
                $valK = (float)($rk['value'] ?? 0);
                if (strpos($labelA, 'Total') === 0) { $totalAktiva = $valA; }
                if (strpos($labelK, 'Total') === 0 || strpos($labelK, 'Jumlah') === 0) { $totalKw = $valK; }
                $rows->push([
                    'keterangan' => e($labelA),
                    'aktiva' => $valA > 0 ? 'Rp ' . number_format($valA, 0, ',', '.') : '',
                    'spacer' => e($labelK),
                    'kw' => $valK > 0 ? 'Rp ' . number_format($valK, 0, ',', '.') : '',
                    '__is_summary' => (strpos($labelA, 'Total') === 0 || strpos($labelK, 'Total') === 0 || strpos($labelK, 'Jumlah') === 0),
                ]);
            }
            $summaryFooter = [[
                'cells' => [
                    0 => '<strong>TOTAL PADANAN</strong>',
                    'aktiva' => '<strong>Rp ' . number_format($totalAktiva, 0, ',', '.') . '</strong>',
                    'spacer' => '',
                    'kw' => '<strong>Rp ' . number_format($totalKw, 0, ',', '.') . '</strong>',
                ],
            ]];
            $rowFn = function ($row, $no) {
                $isSum = $row['__is_summary'] ?? false;
                unset($row['__is_summary']);
                if ($isSum) $row['__is_summary'] = true;
                return $row;
            };
            return $this->generatePdf('Neraca (per ' . Carbon::parse($cutoff)->locale('id')->isoFormat('D MMMM Y') . ')', 'neraca', $header, $rows, $rowFn, null, $cutoff, $cabangId, 'landscape', $summaryFooter);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF Neraca: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request, NeracaService $neracaSvc)
    {
        try {
            return $this->exportPdf($request, $neracaSvc);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export Excel Neraca: ' . $e->getMessage());
        }
    }
}
