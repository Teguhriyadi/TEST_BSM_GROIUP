<?php

namespace App\Http\Controllers;

use App\Models\JenisPinjaman;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;

class SimulasiPinjamanController extends Controller
{
    use WithModuleFilter;

    private function hitungAngsuranPerBulan(float $jumlah, float $bungaTahunan, int $tenor): float
    {
        if ($tenor <= 0) {
            return round($jumlah, 2);
        }
        if ($bungaTahunan <= 0) {
            return round($jumlah / $tenor, 2);
        }
        $r = $bungaTahunan / 100 / 12;
        if ($r <= 0) {
            return round($jumlah / $tenor, 2);
        }
        $pow = pow(1 + $r, $tenor);
        $denominator = $pow - 1;
        if ($denominator <= 0) {
            return round($jumlah / $tenor, 2);
        }
        return round($jumlah * ($r * $pow) / $denominator, 2);
    }

    public function index(Request $request)
    {
        try {
            $jenisPinjaman = JenisPinjaman::orderBy('nama_jenis', 'asc')
                ->get(['id', 'nama_jenis', 'bunga_tahunan', 'tenor_minimal', 'tenor_maksimal', 'maksimal_plafon', 'keterangan']);

            $selectedJenisId = $request->old('jenis_pinjaman_id', $request->get('jenis_pinjaman_id'));
            $jumlahPinjaman = (float) $request->old('jumlah_pinjaman', $request->get('jumlah_pinjaman', 0));
            $tenor = (int) $request->old('tenor', $request->get('tenor', 0));

            $hasil = null;
            if ($selectedJenisId && $jumlahPinjaman > 0 && $tenor > 0) {
                $jenis = $jenisPinjaman->firstWhere('id', $selectedJenisId);
                if ($jenis) {
                    $bunga = (float) ($jenis->bunga_tahunan ?? 0);
                    $angsuranPerBulan = $this->hitungAngsuranPerBulan($jumlahPinjaman, $bunga, $tenor);
                    $totalBayar = round($angsuranPerBulan * $tenor, 2);
                    $totalBunga = round($totalBayar - $jumlahPinjaman, 2);
                    $hasil = [
                        'jenis_id' => $jenis->id,
                        'jenis_nama' => $jenis->nama_jenis,
                        'bunga_tahunan' => $bunga,
                        'jumlah_pinjaman' => $jumlahPinjaman,
                        'tenor_bulan' => $tenor,
                        'angsuran_per_bulan' => $angsuranPerBulan,
                        'total_bayar' => $totalBayar,
                        'total_bunga' => $totalBunga,
                    ];
                }
            }

            $hideFilterBar = true;
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id' => null, 'tanggal_awal' => null, 'tanggal_akhir' => null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('simulasi-pinjaman.index');
            return view('modules.simulasi-pinjaman.index', compact(
                'jenisPinjaman',
                'selectedJenisId',
                'jumlahPinjaman',
                'tenor',
                'hasil',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat simulasi pinjaman: ' . $e->getMessage());
        }
    }
}
