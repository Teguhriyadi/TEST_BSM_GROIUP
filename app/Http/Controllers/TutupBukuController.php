<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Services\BukuBesarService;
use App\Services\JurnalService;
use App\Services\NeracaService;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TutupBukuController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $periode = $request->input('periode', date('Y-m'));

            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $cabangId,
                'tanggal_awal' => null,
                'tanggal_akhir' => null,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('tutup-buku.index');
            return view("modules.tutup-buku.index", compact(
                'periode',
                'cabangId',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat form tutup buku: ' . $e->getMessage());
        }
    }

    public function proses(Request $request, NeracaService $neracaSvc, BukuBesarService $bbSvc, JurnalService $jurnalSvc)
    {
        $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
            'cabang_id' => ['nullable', 'exists:cabang,id'],
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $periode = $request->input('periode');
            $cabangId = $request->input('cabang_id');
            if (empty($cabangId) && !empty($user?->cabang_id) && $user?->role?->kode_role !== 'ROL-ADM') {
                $cabangId = $user?->cabang_id;
            }

            [$tahun, $bulan] = explode('-', $periode);
            $akhirBulan = Carbon::create((int)$tahun, (int)$bulan, 1)->endOfMonth()->startOfDay();

            $lr = $neracaSvc->getLabaRugi(
                Carbon::create((int)$tahun, (int)$bulan, 1)->startOfMonth(),
                $akhirBulan,
                $cabangId,
            );

            $barisDetail = [];

            $akunSHU = Coa::where('kode_akun', '330.01.01')->first();
            if (!$akunSHU) {
                throw new \RuntimeException('Akun SHU Tahun Berjalan (330.01.01) tidak ditemukan di COA.');
            }

            $totalPendapatan = 0;
            foreach ($lr['pendapatan'] as $p) {
                $net = (float) ($p['net_mutasi'] ?? 0);
                if ($net > 0) {
                    $totalPendapatan += $net;
                    $barisDetail[] = [
                        'coa_id' => (string) $p['coa_id'],
                        'debet' => round($net, 2),
                        'kredit' => 0,
                        'keterangan' => "Tutup Buku Periode {$periode} - {$p['nama_akun']}",
                    ];
                }
            }

            $totalBeban = 0;
            foreach ($lr['beban'] as $b) {
                $net = (float) ($b['net_mutasi'] ?? 0);
                if ($net > 0) {
                    $totalBeban += $net;
                    $barisDetail[] = [
                        'coa_id' => (string) $b['coa_id'],
                        'debet' => 0,
                        'kredit' => round($net, 2),
                        'keterangan' => "Tutup Buku Periode {$periode} - {$b['nama_akun']}",
                    ];
                }
            }
            foreach ($lr['pajak'] as $px) {
                $net = (float) ($px['net_mutasi'] ?? 0);
                if ($net > 0) {
                    $totalBeban += $net;
                    $barisDetail[] = [
                        'coa_id' => (string) $px['coa_id'],
                        'debet' => 0,
                        'kredit' => round($net, 2),
                        'keterangan' => "Tutup Buku Periode {$periode} - Pajak: {$px['nama_akun']}",
                    ];
                }
            }

            $shuNeto = round($totalPendapatan - $totalBeban, 2);
            if ($shuNeto >= 0) {
                $barisDetail[] = [
                    'coa_id' => (string) $akunSHU->id,
                    'debet' => 0,
                    'kredit' => round($shuNeto, 2),
                    'keterangan' => "Tutup Buku Periode {$periode} - SHU Tahun Berjalan (Laba)",
                ];
            } else {
                $barisDetail[] = [
                    'coa_id' => (string) $akunSHU->id,
                    'debet' => round(abs($shuNeto), 2),
                    'kredit' => 0,
                    'keterangan' => "Tutup Buku Periode {$periode} - SHU Tahun Berjalan (Rugi)",
                ];
            }

            if (empty($cabangId)) {
                $cabangDefault = \App\Models\Cabang::first();
                if (!$cabangDefault) {
                    throw new \RuntimeException('Cabang belum diatur. Silakan buat data Cabang terlebih dahulu.');
                }
                $cabangId = (string) $cabangDefault->id;
            }
            $refId = \Illuminate\Support\Str::uuid()->toString();
            $header = $jurnalSvc->buatJurnalBaru(
                cabangId: $cabangId,
                tanggal: $akhirBulan,
                tipe: 'otomatis',
                status: 'diposting',
                refId: $refId,
                refTipe: 'TutupBuku',
                keterangan: "Jurnal Penutup Periode {$periode} (Tutup Buku)",
                barisDetail: $barisDetail,
                autoPosting: true,
            );

            DB::commit();
            return redirect()->route('tutup-buku.index', [
                'periode' => $periode,
                'cabang_id' => $cabangId,
            ])->with('success', "Tutup buku periode {$periode} berhasil. Nomor Jurnal: {$header->nomor_jurnal}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal proses tutup buku: ' . $e->getMessage())->withInput();
        }
    }
}
