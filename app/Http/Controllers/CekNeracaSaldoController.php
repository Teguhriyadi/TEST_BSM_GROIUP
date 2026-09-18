<?php

namespace App\Http\Controllers;

use App\Services\BukuBesarService;
use App\Services\NeracaService;
use App\Traits\WithExportable;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CekNeracaSaldoController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request, BukuBesarService $bbSvc, NeracaService $neracaSvc)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $sampai = $request->input('tanggal_cutoff', date('Y-m-d'));
            $carbonSampai = Carbon::parse($sampai);

            $neracaSaldo = $bbSvc->getNeracaSaldo6Kolom($carbonSampai, $cabangId);
            $neraca = $neracaSvc->getNeraca($carbonSampai, $cabangId);

            $akunTidakBalance = [];
            foreach ($neracaSaldo['rows'] as $r) {
                $netAwal = (float)$r['sa_debet'] - (float)$r['sa_kredit'];
                $netMutasi = (float)$r['mut_debet'] - (float)$r['mut_kredit'];
                $netAkhir = (float)$r['sk_debet'] - (float)$r['sk_kredit'];
                $ekspektasiAkhir = $netAwal + $netMutasi;
                if (abs($ekspektasiAkhir - $netAkhir) > 0.09) {
                    $akunTidakBalance[] = [
                        'kode_akun' => $r['kode_akun'],
                        'nama_akun' => $r['nama_akun'],
                        'saldo_awal_neto' => round($netAwal, 2),
                        'mutasi_neto' => round($netMutasi, 2),
                        'ekspektasi_akhir' => round($ekspektasiAkhir, 2),
                        'saldo_akhir_neto' => round($netAkhir, 2),
                        'selisih' => round($ekspektasiAkhir - $netAkhir, 2),
                    ];
                }
            }

            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $cabangId,
                'tanggal_awal' => null,
                'tanggal_akhir' => $sampai,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('cek-neraca-saldo.index');
            $exportPdfUrl = route('cek-neraca-saldo.exportPdf');
            $exportExcelUrl = route('cek-neraca-saldo.exportExcel');
            return view("modules.cek-neraca-saldo.index", compact(
                'neracaSaldo',
                'neraca',
                'akunTidakBalance',
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
            return back()->with('error', 'Gagal memuat cek neraca saldo: ' . $e->getMessage());
        }
    }

    private function getExportDataCek(Request $request, BukuBesarService $bbSvc, NeracaService $neracaSvc): array
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
        if (!$isAdmin && $user?->cabang_id) {
            $cabangId = $user->cabang_id;
        }
        $sampai = $request->input('tanggal_cutoff', date('Y-m-d'));
        $carbonSampai = Carbon::parse($sampai);

        $neracaSaldo = $bbSvc->getNeracaSaldo6Kolom($carbonSampai, $cabangId);

        $rows = [];
        $no = 1;
        $jmlBalance = 0;
        $jmlTidakBalance = 0;
        foreach ($neracaSaldo['rows'] as $r) {
            $netAwal = (float)$r['sa_debet'] - (float)$r['sa_kredit'];
            $netMutasi = (float)$r['mut_debet'] - (float)$r['mut_kredit'];
            $netAkhir = (float)$r['sk_debet'] - (float)$r['sk_kredit'];
            $ekspektasiAkhir = $netAwal + $netMutasi;
            $selisih = round($ekspektasiAkhir - $netAkhir, 2);
            $isBalance = abs($selisih) < 0.09;
            if ($isBalance) $jmlBalance++; else $jmlTidakBalance++;
            $rows[] = [
                'no' => $no++,
                'kode_akun' => $r['kode_akun'],
                'nama_akun' => $r['nama_akun'],
                'saldo_awal_neto' => round($netAwal, 2),
                'mutasi_neto' => round($netMutasi, 2),
                'ekspektasi_akhir' => round($ekspektasiAkhir, 2),
                'saldo_akhir_neto' => round($netAkhir, 2),
                'selisih' => $selisih,
                'status' => $isBalance ? 'BALANCE' : 'TIDAK BALANCE',
            ];
        }

        $summary = [
            [
                'no' => '',
                'kode_akun' => '',
                'nama_akun' => 'RINGKASAN: Total Akun Balance = ' . $jmlBalance . ' | Tidak Balance = ' . $jmlTidakBalance,
                'saldo_awal_neto' => '',
                'mutasi_neto' => '',
                'ekspektasi_akhir' => '',
                'saldo_akhir_neto' => '',
                'selisih' => '',
                'status' => $jmlTidakBalance === 0 ? 'SEMUA BALANCE ✓' : 'PERLU DICEK ✗',
            ]
        ];

        return [$rows, $summary, $cabangId, $sampai];
    }

    public function exportPdf(Request $request, BukuBesarService $bbSvc, NeracaService $neracaSvc)
    {
        [$rows, $summary, $cabangId, $sampai] = $this->getExportDataCek($request, $bbSvc, $neracaSvc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kode_akun', 'label' => 'Kode Akun', 'align' => 'text-start'],
            ['key' => 'nama_akun', 'label' => 'Nama Akun', 'align' => 'text-start'],
            ['key' => 'saldo_awal_neto', 'label' => 'SA Neto (Rp)', 'align' => 'text-end'],
            ['key' => 'mutasi_neto', 'label' => 'Mutasi Neto (Rp)', 'align' => 'text-end'],
            ['key' => 'ekspektasi_akhir', 'label' => 'Ekspektasi Akhir (Rp)', 'align' => 'text-end'],
            ['key' => 'saldo_akhir_neto', 'label' => 'SAkhir Neto (Rp)', 'align' => 'text-end'],
            ['key' => 'selisih', 'label' => 'Selisih (Rp)', 'align' => 'text-end'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'text-center'],
        ];

        return $this->generatePdf(
            'Cek Validasi Neraca Saldo (Perhitungan Balance Setiap Akun)',
            'cek-neraca-saldo',
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

    public function exportExcel(Request $request, BukuBesarService $bbSvc, NeracaService $neracaSvc)
    {
        [$rows, $summary, $cabangId, $sampai] = $this->getExportDataCek($request, $bbSvc, $neracaSvc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kode_akun', 'label' => 'Kode Akun', 'align' => 'text-start'],
            ['key' => 'nama_akun', 'label' => 'Nama Akun', 'align' => 'text-start'],
            ['key' => 'saldo_awal_neto', 'label' => 'SA Neto (Rp)', 'align' => 'text-end'],
            ['key' => 'mutasi_neto', 'label' => 'Mutasi Neto (Rp)', 'align' => 'text-end'],
            ['key' => 'ekspektasi_akhir', 'label' => 'Ekspektasi Akhir (Rp)', 'align' => 'text-end'],
            ['key' => 'saldo_akhir_neto', 'label' => 'SAkhir Neto (Rp)', 'align' => 'text-end'],
            ['key' => 'selisih', 'label' => 'Selisih (Rp)', 'align' => 'text-end'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'text-center'],
        ];

        return $this->generateExcel(
            'Cek Validasi Neraca Saldo (Perhitungan Balance Setiap Akun)',
            'cek-neraca-saldo',
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
