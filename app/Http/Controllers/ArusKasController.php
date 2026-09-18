<?php

namespace App\Http\Controllers;

use App\Services\NeracaService;
use App\Traits\WithExportable;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ArusKasController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request, NeracaService $svc)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $dari = $request->input('tanggal_awal', date('Y-01-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-d'));

            $data = $svc->getArusKas(
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
            $filterFormAction = route('laporan-akunting.arus-kas');
            $exportPdfUrl = route('laporan-akunting.arus-kas.exportPdf');
            $exportExcelUrl = route('laporan-akunting.arus-kas.exportExcel');
            return view("modules.laporan-akunting.arus-kas", compact(
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
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat arus kas: ' . $e->getMessage());
        }
    }

    private function getExportDataArusKas(Request $request, NeracaService $svc): array
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
        if (!$isAdmin && $user?->cabang_id) {
            $cabangId = $user->cabang_id;
        }
        $dari = $request->input('tanggal_awal', date('Y-01-01'));
        $sampai = $request->input('tanggal_akhir', date('Y-m-d'));

        $data = $svc->getArusKas(
            Carbon::parse($dari),
            Carbon::parse($sampai),
            $cabangId,
        );

        $rows = [
            [
                'no' => 1,
                'kelompok' => 'I',
                'keterangan' => 'AKTIVITAS OPERASIONAL',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '→ Penerimaan Kas dari Operasional (Simpanan, Pinjaman, dll)',
                'kas_masuk' => $data['aktivitas_operasional']['kas_masuk'],
                'kas_keluar' => '',
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '→ Pengeluaran Kas untuk Operasional',
                'kas_masuk' => '',
                'kas_keluar' => $data['aktivitas_operasional']['kas_keluar'],
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => 'Kas Bersih dari Aktivitas Operasional',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => $data['aktivitas_operasional']['net'],
                '_is_bold' => true,
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => '',
            ],
            [
                'no' => 2,
                'kelompok' => 'II',
                'keterangan' => 'AKTIVITAS INVESTASI',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '→ Penerimaan dari Penjualan Aktiva Tetap',
                'kas_masuk' => $data['aktivitas_investasi']['kas_masuk'],
                'kas_keluar' => '',
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '→ Pengeluaran untuk Pembelian Aktiva Tetap',
                'kas_masuk' => '',
                'kas_keluar' => $data['aktivitas_investasi']['kas_keluar'],
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => 'Kas Bersih dari Aktivitas Investasi',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => $data['aktivitas_investasi']['net'],
                '_is_bold' => true,
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => '',
            ],
            [
                'no' => 3,
                'kelompok' => 'III',
                'keterangan' => 'AKTIVITAS PENDANAAN',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '→ Penerimaan dari Modal / Hutang Jangka Panjang',
                'kas_masuk' => $data['aktivitas_pendanaan']['kas_masuk'],
                'kas_keluar' => '',
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '→ Pengeluaran untuk Pembayaran Hutang / Dividen',
                'kas_masuk' => '',
                'kas_keluar' => $data['aktivitas_pendanaan']['kas_keluar'],
                'net_kas' => '',
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => 'Kas Bersih dari Aktivitas Pendanaan',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => $data['aktivitas_pendanaan']['net'],
                '_is_bold' => true,
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => '',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => '',
            ],
        ];

        $summary = [
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => 'KENAIKAN (PENURUNAN) BERSIH KAS PERIODE',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => $data['ringkasan']['kenaikan_bersih_kas'],
                '_is_bold' => true,
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => 'Saldo Awal Kas & Bank (Awal Periode)',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => $data['ringkasan']['saldo_awal_kas'],
            ],
            [
                'no' => '',
                'kelompok' => '',
                'keterangan' => 'SALDO AKHIR KAS & BANK (Akhir Periode)',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'net_kas' => $data['ringkasan']['saldo_akhir_kas'],
                '_is_bold' => true,
            ],
        ];

        return [$rows, $summary, $cabangId, $dari, $sampai];
    }

    public function exportPdf(Request $request, NeracaService $svc)
    {
        [$rows, $summary, $cabangId, $dari, $sampai] = $this->getExportDataArusKas($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kelompok', 'label' => 'Gol', 'align' => 'text-center'],
            ['key' => 'keterangan', 'label' => 'Keterangan Aktivitas Arus Kas', 'align' => 'text-start'],
            ['key' => 'kas_masuk', 'label' => 'Kas Masuk (Rp)', 'align' => 'text-end'],
            ['key' => 'kas_keluar', 'label' => 'Kas Keluar (Rp)', 'align' => 'text-end'],
            ['key' => 'net_kas', 'label' => 'Kas Bersih (Rp)', 'align' => 'text-end'],
        ];

        return $this->generatePdf(
            'Laporan Arus Kas (Cash Flow Statement)',
            'laporan-arus-kas',
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

    public function exportExcel(Request $request, NeracaService $svc)
    {
        [$rows, $summary, $cabangId, $dari, $sampai] = $this->getExportDataArusKas($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kelompok', 'label' => 'Gol', 'align' => 'text-center'],
            ['key' => 'keterangan', 'label' => 'Keterangan Aktivitas Arus Kas', 'align' => 'text-start'],
            ['key' => 'kas_masuk', 'label' => 'Kas Masuk (Rp)', 'align' => 'text-end'],
            ['key' => 'kas_keluar', 'label' => 'Kas Keluar (Rp)', 'align' => 'text-end'],
            ['key' => 'net_kas', 'label' => 'Kas Bersih (Rp)', 'align' => 'text-end'],
        ];

        return $this->generateExcel(
            'Laporan Arus Kas (Cash Flow Statement)',
            'laporan-arus-kas',
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
