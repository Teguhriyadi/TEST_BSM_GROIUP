<?php

namespace App\Http\Controllers;

use App\Services\BukuBesarService;
use App\Traits\WithExportable;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class NeracaSaldoController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request, BukuBesarService $svc)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $sampai = $request->input('tanggal_cutoff', date('Y-m-d'));

            $data = $svc->getNeracaSaldo6Kolom(
                Carbon::parse($sampai),
                $cabangId,
            );

            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $cabangId,
                'tanggal_awal' => null,
                'tanggal_akhir' => $sampai,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('neraca-saldo.index');
            $exportPdfUrl = route('neraca-saldo.exportPdf');
            $exportExcelUrl = route('neraca-saldo.exportExcel');
            return view("modules.neraca-saldo.index", compact(
                'data',
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
            return back()->with('error', 'Gagal memuat neraca saldo: ' . $e->getMessage());
        }
    }

    private function getExportDataNeracaSaldo(Request $request, BukuBesarService $svc): array
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
        if (!$isAdmin && $user?->cabang_id) {
            $cabangId = $user->cabang_id;
        }
        $sampai = $request->input('tanggal_cutoff', date('Y-m-d'));

        $data = $svc->getNeracaSaldo6Kolom(
            Carbon::parse($sampai),
            $cabangId,
        );

        $rows = [];
        $no = 1;
        foreach ($data['rows'] as $r) {
            $rows[] = [
                'no' => $no++,
                'kode_akun' => $r['kode_akun'],
                'nama_akun' => $r['nama_akun'],
                'kelompok' => $r['kelompok'],
                'sa_debet' => $r['sa_debet'],
                'sa_kredit' => $r['sa_kredit'],
                'mut_debet' => $r['mut_debet'],
                'mut_kredit' => $r['mut_kredit'],
                'sk_debet' => $r['sk_debet'],
                'sk_kredit' => $r['sk_kredit'],
            ];
        }

        $t = $data['totals'];
        $summary = [
            [
                'no' => '',
                'kode_akun' => '',
                'nama_akun' => 'TOTAL',
                'kelompok' => '',
                'sa_debet' => $t['sa_debet'],
                'sa_kredit' => $t['sa_kredit'],
                'mut_debet' => $t['mut_debet'],
                'mut_kredit' => $t['mut_kredit'],
                'sk_debet' => $t['sk_debet'],
                'sk_kredit' => $t['sk_kredit'],
            ]
        ];

        return [$rows, $summary, $cabangId, $sampai];
    }

    public function exportPdf(Request $request, BukuBesarService $svc)
    {
        [$rows, $summary, $cabangId, $sampai] = $this->getExportDataNeracaSaldo($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kode_akun', 'label' => 'Kode Akun', 'align' => 'text-start'],
            ['key' => 'nama_akun', 'label' => 'Nama Akun', 'align' => 'text-start'],
            ['key' => 'kelompok', 'label' => 'Kelompok', 'align' => 'text-start'],
            ['key' => 'sa_debet', 'label' => 'SA Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'sa_kredit', 'label' => 'SA Kredit (Rp)', 'align' => 'text-end'],
            ['key' => 'mut_debet', 'label' => 'Mutasi Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'mut_kredit', 'label' => 'Mutasi Kredit (Rp)', 'align' => 'text-end'],
            ['key' => 'sk_debet', 'label' => 'SK Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'sk_kredit', 'label' => 'SK Kredit (Rp)', 'align' => 'text-end'],
        ];

        return $this->generatePdf(
            'Neraca Saldo (6 Kolom) Per Tanggal Cutoff',
            'neraca-saldo',
            $headerKolom,
            $rows,
            fn($row) => $row,
            null,
            $sampai,
            $cabangId,
            'landscape',
            $summary
        );
    }

    public function exportExcel(Request $request, BukuBesarService $svc)
    {
        [$rows, $summary, $cabangId, $sampai] = $this->getExportDataNeracaSaldo($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kode_akun', 'label' => 'Kode Akun', 'align' => 'text-start'],
            ['key' => 'nama_akun', 'label' => 'Nama Akun', 'align' => 'text-start'],
            ['key' => 'kelompok', 'label' => 'Kelompok', 'align' => 'text-start'],
            ['key' => 'sa_debet', 'label' => 'SA Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'sa_kredit', 'label' => 'SA Kredit (Rp)', 'align' => 'text-end'],
            ['key' => 'mut_debet', 'label' => 'Mutasi Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'mut_kredit', 'label' => 'Mutasi Kredit (Rp)', 'align' => 'text-end'],
            ['key' => 'sk_debet', 'label' => 'SK Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'sk_kredit', 'label' => 'SK Kredit (Rp)', 'align' => 'text-end'],
        ];

        return $this->generateExcel(
            'Neraca Saldo (6 Kolom) Per Tanggal Cutoff',
            'neraca-saldo',
            $headerKolom,
            $rows,
            fn($row) => $row,
            null,
            $sampai,
            $cabangId,
            $summary
        );
    }
}
