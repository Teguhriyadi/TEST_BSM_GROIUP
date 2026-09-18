<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Services\BukuBesarService;
use App\Traits\WithExportable;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BukuKasHarianController extends Controller
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

            $query = Coa::where('is_active', '1')
                ->where(function ($q) {
                    $q->where('kode_akun', 'like', '111.%')
                        ->orWhere('kode_akun', 'like', '112.%');
                })
                ->where('level', 2);
            if (!$isAdmin && $user?->cabang_id) {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('cabang_id')->orWhere('cabang_id', $user->cabang_id);
                });
            }
            $daftarKasBank = $query->orderBy('kode_akun', 'asc')->get();
            $akunKasIds = $daftarKasBank->pluck('id')->map(fn($v)=>(string)$v)->all();

            $data = $svc->getBukuKasHarianSaldoBerjalan(
                $akunKasIds,
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
            $filterFormAction = route('buku-kas-harian.index');
            $exportPdfUrl = route('buku-kas-harian.exportPdf');
            $exportExcelUrl = route('buku-kas-harian.exportExcel');
            return view("modules.buku-kas-harian.index", compact(
                'daftarKasBank',
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
            return back()->with('error', 'Gagal memuat buku kas: ' . $e->getMessage());
        }
    }

    private function getExportDataBukuKas(Request $request, BukuBesarService $svc): array
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
        if (!$isAdmin && $user?->cabang_id) {
            $cabangId = $user->cabang_id;
        }
        $dari = $request->input('tanggal_awal', date('Y-m-01'));
        $sampai = $request->input('tanggal_akhir', date('Y-m-t'));

        $query = Coa::where('is_active', '1')
            ->where(function ($q) {
                $q->where('kode_akun', 'like', '111.%')
                    ->orWhere('kode_akun', 'like', '112.%');
            })
            ->where('level', 2);
        if (!$isAdmin && $user?->cabang_id) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('cabang_id')->orWhere('cabang_id', $user->cabang_id);
            });
        }
        $daftarKasBank = $query->orderBy('kode_akun', 'asc')->get();
        $akunKasIds = $daftarKasBank->pluck('id')->map(fn($v)=>(string)$v)->all();

        $data = $svc->getBukuKasHarianSaldoBerjalan(
            $akunKasIds,
            Carbon::parse($dari),
            Carbon::parse($sampai),
            $cabangId,
        );

        $rows = [];
        $no = 1;
        $runningGlobalKasMasuk = 0;
        $runningGlobalKasKeluar = 0;

        foreach ($data['per_akun'] as $block) {
            $coa = $block['coa'];
            $rows[] = [
                'is_header' => true,
                'no' => '',
                'tanggal' => '',
                'kode_akun' => $coa->kode_akun,
                'nama_akun' => strtoupper($coa->nama_akun),
                'uraian' => '',
                'reff' => '',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'saldo_berjalan' => '',
            ];
            $rows[] = [
                'is_header' => false,
                'no' => '',
                'tanggal' => Carbon::parse($dari)->locale('id')->isoFormat('D MMMM Y'),
                'kode_akun' => '',
                'nama_akun' => '',
                'uraian' => 'Saldo Awal',
                'reff' => '',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'saldo_berjalan' => $block['saldo_awal'],
            ];
            $runningSaldo = $block['saldo_awal'];
            foreach ($block['detail_rows'] as $dr) {
                if ($dr['tipe'] === 'SALDO_AWAL') continue;
                $runningSaldo += (float)$dr['debet'] - (float)$dr['kredit'];
                $runningGlobalKasMasuk += (float)$dr['debet'];
                $runningGlobalKasKeluar += (float)$dr['kredit'];
                $rows[] = [
                    'is_header' => false,
                    'no' => $no++,
                    'tanggal' => Carbon::parse($dr['tanggal'])->locale('id')->isoFormat('D MMMM Y'),
                    'kode_akun' => $dr['lawan_kode_akun'] ?? '',
                    'nama_akun' => $dr['lawan_nama_akun'] ?? '',
                    'uraian' => $dr['uraian'] ?? '',
                    'reff' => $dr['no_voucher'] ?? '',
                    'kas_masuk' => (float)$dr['debet'],
                    'kas_keluar' => (float)$dr['kredit'],
                    'saldo_berjalan' => round($runningSaldo, 2),
                ];
            }
            $rows[] = [
                'is_header' => true,
                'no' => '',
                'tanggal' => Carbon::parse($sampai)->locale('id')->isoFormat('D MMMM Y'),
                'kode_akun' => '',
                'nama_akun' => '',
                'uraian' => 'Saldo Akhir ' . $coa->nama_akun,
                'reff' => '',
                'kas_masuk' => $block['masuk'],
                'kas_keluar' => $block['keluar'],
                'saldo_berjalan' => $block['saldo_akhir'],
            ];
            $rows[] = [
                'is_header' => false,
                'no' => '',
                'tanggal' => '',
                'kode_akun' => '',
                'nama_akun' => '',
                'uraian' => '',
                'reff' => '',
                'kas_masuk' => '',
                'kas_keluar' => '',
                'saldo_berjalan' => '',
            ];
        }

        $t = $data['total'];
        $summary = [
            [
                'is_header' => true,
                'no' => '',
                'tanggal' => '',
                'kode_akun' => '',
                'nama_akun' => '',
                'uraian' => 'TOTAL SEMUA KAS / BANK',
                'reff' => '',
                'kas_masuk' => $t['masuk'],
                'kas_keluar' => $t['keluar'],
                'saldo_berjalan' => $t['saldo_akhir'],
            ]
        ];

        return [$rows, $summary, $cabangId, $dari, $sampai];
    }

    public function exportPdf(Request $request, BukuBesarService $svc)
    {
        [$rows, $summary, $cabangId, $dari, $sampai] = $this->getExportDataBukuKas($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'tanggal', 'label' => 'Tanggal', 'align' => 'text-start'],
            ['key' => 'kode_akun', 'label' => 'Kode Akun', 'align' => 'text-start'],
            ['key' => 'nama_akun', 'label' => 'Nama Akun Lawan', 'align' => 'text-start'],
            ['key' => 'uraian', 'label' => 'Uraian', 'align' => 'text-start'],
            ['key' => 'reff', 'label' => 'Reff / Voucher', 'align' => 'text-start'],
            ['key' => 'kas_masuk', 'label' => 'Kas Masuk (Rp)', 'align' => 'text-end'],
            ['key' => 'kas_keluar', 'label' => 'Kas Keluar (Rp)', 'align' => 'text-end'],
            ['key' => 'saldo_berjalan', 'label' => 'Saldo Berjalan (Rp)', 'align' => 'text-end'],
        ];

        return $this->generatePdf(
            'Buku Kas Harian (Semua Akun Kas & Bank)',
            'buku-kas-harian',
            $headerKolom,
            $rows,
            function($row) {
                $out = $row;
                if (!empty($row['is_header'])) {
                    $out['_is_bold'] = true;
                }
                return $out;
            },
            $dari,
            $sampai,
            $cabangId,
            'landscape',
            $summary
        );
    }

    public function exportExcel(Request $request, BukuBesarService $svc)
    {
        [$rows, $summary, $cabangId, $dari, $sampai] = $this->getExportDataBukuKas($request, $svc);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'tanggal', 'label' => 'Tanggal', 'align' => 'text-start'],
            ['key' => 'kode_akun', 'label' => 'Kode Akun', 'align' => 'text-start'],
            ['key' => 'nama_akun', 'label' => 'Nama Akun Lawan', 'align' => 'text-start'],
            ['key' => 'uraian', 'label' => 'Uraian', 'align' => 'text-start'],
            ['key' => 'reff', 'label' => 'Reff / Voucher', 'align' => 'text-start'],
            ['key' => 'kas_masuk', 'label' => 'Kas Masuk (Rp)', 'align' => 'text-end'],
            ['key' => 'kas_keluar', 'label' => 'Kas Keluar (Rp)', 'align' => 'text-end'],
            ['key' => 'saldo_berjalan', 'label' => 'Saldo Berjalan (Rp)', 'align' => 'text-end'],
        ];

        return $this->generateExcel(
            'Buku Kas Harian (Semua Akun Kas & Bank)',
            'buku-kas-harian',
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
