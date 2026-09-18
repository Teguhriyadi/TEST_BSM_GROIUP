<?php

namespace App\Http\Controllers;

use App\Services\NeracaService;
use App\Traits\WithExportable;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShuController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request, NeracaService $svc)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $tahun = (int) $request->input('tahun', date('Y'));

            $data = $svc->getLaporanSHU($tahun, $cabangId);

            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $cabangId,
                'tanggal_awal' => null,
                'tanggal_akhir' => null,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('laporan-akunting.shu');
            $exportPdfUrl = route('laporan-akunting.shu.exportPdf');
            $exportExcelUrl = route('laporan-akunting.shu.exportExcel');
            return view("modules.laporan-akunting.shu", compact(
                'tahun',
                'data',
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
            return back()->with('error', 'Gagal memuat laporan SHU: ' . $e->getMessage());
        }
    }

    private function getExportDataShu(Request $request, NeracaService $svc): array
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
        if (!$isAdmin && $user?->cabang_id) {
            $cabangId = $user->cabang_id;
        }
        $tahun = (int) $request->input('tahun', date('Y'));

        $data = $svc->getLaporanSHU($tahun, $cabangId);
        $d = $data['distribusi'];
        $labaBersih = $data['laba_rugi']['ringkasan']['laba_bersih'] ?? 0;

        $rows = [
            [
                'no' => 1,
                'keterangan' => 'Laba Bersih Setelah Pajak (Periode Tahun ' . $tahun . ')',
                'persentase' => '100%',
                'nominal' => round($labaBersih, 2),
                'catatan' => 'Diambil dari Laporan Laba Rugi periode 1 Jan s/d 31 Des ' . $tahun,
            ],
            [
                'no' => 2,
                'keterangan' => 'SHU yang Tersedia untuk Didistribusikan',
                'persentase' => '',
                'nominal' => $d['shu_tersedia'],
                'catatan' => 'Max(0, Laba Bersih) = jika rugi, SHU = Rp 0',
            ],
            [
                'no' => '',
                'keterangan' => str_repeat('—', 10),
                'persentase' => '',
                'nominal' => '',
                'catatan' => '',
            ],
            [
                'no' => 3,
                'keterangan' => '→ Cadangan Koperasi (Cadangan Umum)',
                'persentase' => '50%',
                'nominal' => $d['cadangan'],
                'catatan' => 'Untuk penguatan modal dan cadangan resiko',
            ],
            [
                'no' => 4,
                'keterangan' => '→ SHU untuk Anggota (Dibagikan Pro Rata)',
                'persentase' => '40%',
                'nominal' => $d['shu_anggota'],
                'catatan' => 'Dibagikan sesuai prosentase simpanan & pinjaman anggota',
            ],
            [
                'no' => 5,
                'keterangan' => '→ Dana Sosial & Pendidikan Anggota',
                'persentase' => '10%',
                'nominal' => $d['dana_sosial'],
                'catatan' => 'Untuk program sosial, pendidikan, dan pengembangan anggota',
            ],
            [
                'no' => '',
                'keterangan' => str_repeat('—', 10),
                'persentase' => '',
                'nominal' => '',
                'catatan' => '',
            ],
        ];

        $summary = [
            [
                'no' => '',
                'keterangan' => 'TOTAL DISTRIBUSI SHU TAHUN ' . $tahun,
                'persentase' => '100%',
                'nominal' => $d['total_distribusi'],
                'catatan' => 'Selisih Pembulatan = Rp ' . number_format($d['selisih_distribusi'], 2, ',', '.'),
            ]
        ];

        $dari = $tahun . '-01-01';
        $sampai = $tahun . '-12-31';
        return [$rows, $summary, $cabangId, $dari, $sampai];
    }

    public function exportPdf(Request $request, NeracaService $svc)
    {
        [$rows, $summary, $cabangId, $dari, $sampai] = $this->getExportDataShu($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'keterangan', 'label' => 'Keterangan Distribusi SHU', 'align' => 'text-start'],
            ['key' => 'persentase', 'label' => 'Persentase', 'align' => 'text-center'],
            ['key' => 'nominal', 'label' => 'Nominal (Rp)', 'align' => 'text-end'],
            ['key' => 'catatan', 'label' => 'Keterangan / Catatan', 'align' => 'text-start'],
        ];

        return $this->generatePdf(
            'Laporan Distribusi Sisa Hasil Usaha (SHU)',
            'laporan-shu',
            $headerKolom,
            $rows,
            fn($row) => $row,
            $dari,
            $sampai,
            $cabangId,
            'portrait',
            $summary
        );
    }

    public function exportExcel(Request $request, NeracaService $svc)
    {
        [$rows, $summary, $cabangId, $dari, $sampai] = $this->getExportDataShu($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'keterangan', 'label' => 'Keterangan Distribusi SHU', 'align' => 'text-start'],
            ['key' => 'persentase', 'label' => 'Persentase', 'align' => 'text-center'],
            ['key' => 'nominal', 'label' => 'Nominal (Rp)', 'align' => 'text-end'],
            ['key' => 'catatan', 'label' => 'Keterangan / Catatan', 'align' => 'text-start'],
        ];

        return $this->generateExcel(
            'Laporan Distribusi Sisa Hasil Usaha (SHU)',
            'laporan-shu',
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
