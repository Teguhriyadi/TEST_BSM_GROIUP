<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cabang;
use App\Models\Anggota;
use App\Models\Simpanan;
use App\Models\Pinjaman;
use App\Models\Angsuran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class AppController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            $isAnggota = Auth::check() && (Auth::user()->hasRole('Anggota') || Auth::user()->hasRole('Karyawan'));

            $filterSessionKey = 'dashboard_filter_v1';
            try {
                $defAwal = Carbon::now()->startOfMonth()->toDateString();
                $defAkhir = Carbon::now()->endOfMonth()->toDateString();
            } catch (\Throwable $e) {
                $defAwal = null;
                $defAkhir = null;
            }
            $defaultFilter = [
                'cabang_id' => null,
                'tanggal_awal' => $defAwal,
                'tanggal_akhir' => $defAkhir,
            ];

            if ($request->has('_reset_filter')) {
                $request->session()->forget($filterSessionKey);
            }

            $currentFilter = $request->session()->get($filterSessionKey, $defaultFilter);
            if ($request->isMethod('POST') || $request->filled('cabang_id') || $request->filled('tanggal_awal') || $request->filled('tanggal_akhir') || $request->has('apply_filter')) {
                $submit = $request->all();
                $rawCabang = $submit['cabang_id'] ?? $currentFilter['cabang_id'] ?? null;
                $rawAwal = $submit['tanggal_awal'] ?? $currentFilter['tanggal_awal'] ?? null;
                $rawAkhir = $submit['tanggal_akhir'] ?? $currentFilter['tanggal_akhir'] ?? null;
                if (is_string($rawCabang) && trim($rawCabang) === '') {
                    $rawCabang = null;
                }
                if (is_string($rawAwal) && trim($rawAwal) === '') {
                    $rawAwal = null;
                }
                if (is_string($rawAkhir) && trim($rawAkhir) === '') {
                    $rawAkhir = null;
                }
                $currentFilter = [
                    'cabang_id' => $rawCabang,
                    'tanggal_awal' => $rawAwal,
                    'tanggal_akhir' => $rawAkhir,
                ];
                $request->session()->put($filterSessionKey, $currentFilter);
            }

            $dTglAwal = null;
            $dTglAkhir = null;
            if (! empty($currentFilter['tanggal_awal'])) {
                try {
                    $dTglAwal = Carbon::createFromFormat('Y-m-d', $currentFilter['tanggal_awal'])?->startOfDay();
                } catch (\Throwable $e) { $dTglAwal = null; }
            }
            if (! empty($currentFilter['tanggal_akhir'])) {
                try {
                    $dTglAkhir = Carbon::createFromFormat('Y-m-d', $currentFilter['tanggal_akhir'])?->endOfDay();
                } catch (\Throwable $e) { $dTglAkhir = null; }
            }
            $cabangFilterId = $currentFilter['cabang_id'];

            $anggotaAkun = null;
            $anggotaIdFilter = null;
            $lockCabangToUser = false;
            $lockedCabangId = null;
            $lockedCabangNama = null;
            $lockedCabangKode = null;
            $userDashboard = Auth::user();
            if ($isAnggota) {
                $anggotaAkun = Anggota::where('users_id', Auth::id())->first();
                $anggotaIdFilter = $anggotaAkun?->id;
                $cabangFilterId = $anggotaAkun?->cabang_id;
            } elseif ($userDashboard && ! $userDashboard->hasRole('Administrator') && ! empty($userDashboard->cabang_id)) {
                $lockCabangToUser = true;
                $lockedCabangId = $userDashboard->cabang_id;
                $cabangFilterId = $lockedCabangId;
                $currentFilter['cabang_id'] = $lockedCabangId;
                $request->session()->put($filterSessionKey, $currentFilter);
                $cabDash = $userDashboard->cabang;
                if (! $cabDash) {
                    $cabDash = Cabang::where('id', $lockedCabangId)->first(['kode_cabang','nama_cabang']);
                }
                if ($cabDash) {
                    $lockedCabangNama = $cabDash->nama_cabang;
                    $lockedCabangKode = $cabDash->kode_cabang;
                }
            }

            $cabangList = Cabang::where('is_active', '1')
                ->orderBy('nama_cabang', 'asc')
                ->get(['id', 'kode_cabang', 'nama_cabang']);

            $applyScopeCabangTanggal = function ($query, $table = null, $tglKolom = 'created_at') use ($cabangFilterId, $dTglAwal, $dTglAkhir) {
                if ($cabangFilterId) {
                    if ($table) {
                        $query->where($table . '.cabang_id', $cabangFilterId);
                    } else {
                        $query->where('cabang_id', $cabangFilterId);
                    }
                }
                if ($dTglAwal) {
                    $query->whereDate($tglKolom, '>=', $dTglAwal->toDateString());
                }
                if ($dTglAkhir) {
                    $query->whereDate($tglKolom, '<=', $dTglAkhir->toDateString());
                }
                return $query;
            };

            $qAnggota = Anggota::query();
            if (! $isAnggota) {
                $applyScopeCabangTanggal($qAnggota, null, 'created_at');
            } else {
                $qAnggota->where('id', $anggotaIdFilter);
            }
            $totalAnggota = $isAnggota ? 1 : (clone $qAnggota)->count();

            $qCabang = Cabang::query();
            $totalCabang = $isAnggota ? 1 : (clone $qCabang)->count();

            $qSimpanan = Simpanan::query();
            if ($anggotaIdFilter) {
                $qSimpanan->where('anggota_id', $anggotaIdFilter);
            } else {
                $applyScopeCabangTanggal($qSimpanan, null, 'tanggal');
            }
            $totalSimpanan = (int) (clone $qSimpanan)->sum('nominal');

            $qPinjaman = Pinjaman::query();
            if ($anggotaIdFilter) {
                $qPinjaman->where('anggota_id', $anggotaIdFilter);
            } else {
                $applyScopeCabangTanggal($qPinjaman, null, 'tgl_pengajuan');
            }
            $totalPinjaman = (int) (clone $qPinjaman)->sum('jumlah_pinjaman');

            $qPinjamanBerjalan = (clone $qPinjaman)->whereIn('status', ['berjalan', 'dicairkan']);
            $totalPinjamanBerjalan = (clone $qPinjamanBerjalan)->count();

            $qAngsuran = Angsuran::query();
            if ($anggotaIdFilter) {
                $qAngsuran->whereHas('pinjaman', fn ($qq) => $qq->where('anggota_id', $anggotaIdFilter));
            } elseif ($cabangFilterId || $dTglAwal || $dTglAkhir) {
                $qAngsuran->whereHas('pinjaman', function ($qq) use ($cabangFilterId, $dTglAwal, $dTglAkhir) {
                    if ($cabangFilterId) {
                        $qq->where('cabang_id', $cabangFilterId);
                    }
                    if ($dTglAwal) {
                        $qq->whereDate('tgl_pengajuan', '>=', $dTglAwal->toDateString());
                    }
                    if ($dTglAkhir) {
                        $qq->whereDate('tgl_pengajuan', '<=', $dTglAkhir->toDateString());
                    }
                });
            }
            $totalAngsuranBelumLunas = (int) (clone $qAngsuran)->where('status', 'belum_lunas')->sum('total_bayar');

            $pinjamanTerbaru = Pinjaman::with(['anggota']);
            if ($anggotaIdFilter) {
                $pinjamanTerbaru->where('anggota_id', $anggotaIdFilter);
            } else {
                $applyScopeCabangTanggal($pinjamanTerbaru, null, 'tgl_pengajuan');
            }
            $pinjamanTerbaru = $pinjamanTerbaru
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $simpananTerbaru = Simpanan::with(['anggota', 'jenisSimpanan']);
            if ($anggotaIdFilter) {
                $simpananTerbaru->where('anggota_id', $anggotaIdFilter);
            } else {
                $applyScopeCabangTanggal($simpananTerbaru, null, 'tanggal');
            }
            $simpananTerbaru = $simpananTerbaru
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $anggotaTerbaru = Anggota::with(['cabang', 'user']);
            if ($isAnggota && $anggotaIdFilter) {
                $anggotaTerbaru->where('id', $anggotaIdFilter);
            } else {
                $applyScopeCabangTanggal($anggotaTerbaru, null, 'created_at');
            }
            $anggotaTerbaru = $anggotaTerbaru
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $labelsBulan = [];
            $dataSimpananBulan = [];
            $dataPinjamanBulan = [];
            for ($i = 5; $i >= 0; $i--) {
                $bulan = Carbon::now()->subMonths($i)->startOfMonth();
                $labelsBulan[] = $bulan->translatedFormat('M Y');
                $awal = $bulan->copy()->startOfMonth();
                $akhir = $bulan->copy()->endOfMonth();

                $sQ = Simpanan::whereBetween('tanggal', [$awal, $akhir]);
                $pQ = Pinjaman::whereBetween('tgl_pengajuan', [$awal, $akhir]);
                if ($anggotaIdFilter) {
                    $sQ->where('anggota_id', $anggotaIdFilter);
                    $pQ->where('anggota_id', $anggotaIdFilter);
                } else {
                    if ($cabangFilterId) {
                        $sQ->where('cabang_id', $cabangFilterId);
                        $pQ->where('cabang_id', $cabangFilterId);
                    }
                }
                $dataSimpananBulan[] = (int) $sQ->sum('nominal');
                $dataPinjamanBulan[] = (int) $pQ->sum('jumlah_pinjaman');
            }

            $statusLabels = ['Diajukan', 'Diverifikasi', 'Disetujui', 'Dicairkan', 'Berjalan', 'Lunas', 'Ditolak'];
            $buildStatusVal = function () use ($isAnggota, $anggotaIdFilter, $cabangFilterId, $dTglAwal, $dTglAkhir) {
                $arr = [];
                foreach (['diajukan', 'diverifikasi', 'disetujui', 'dicairkan', 'berjalan', 'lunas', 'ditolak'] as $s) {
                    $q = Pinjaman::where('status', $s);
                    if ($anggotaIdFilter) {
                        $q->where('anggota_id', $anggotaIdFilter);
                    } else {
                        if ($cabangFilterId) {
                            $q->where('cabang_id', $cabangFilterId);
                        }
                        if ($dTglAwal || $dTglAkhir) {
                            if ($dTglAwal) {
                                $q->whereDate('tgl_pengajuan', '>=', $dTglAwal->toDateString());
                            }
                            if ($dTglAkhir) {
                                $q->whereDate('tgl_pengajuan', '<=', $dTglAkhir->toDateString());
                            }
                        }
                    }
                    $arr[] = $q->count();
                }
                return $arr;
            };
            $statusValues = $buildStatusVal();

            return view("modules.dashboard", compact(
                'totalAnggota',
                'totalCabang',
                'totalSimpanan',
                'totalPinjaman',
                'totalPinjamanBerjalan',
                'totalAngsuranBelumLunas',
                'pinjamanTerbaru',
                'simpananTerbaru',
                'anggotaTerbaru',
                'labelsBulan',
                'dataSimpananBulan',
                'dataPinjamanBulan',
                'statusLabels',
                'statusValues',
                'cabangList',
                'currentFilter',
                'isAnggota',
                'lockCabangToUser',
                'lockedCabangId',
                'lockedCabangNama',
                'lockedCabangKode',
            ));
        } catch (\Exception $e) {
            $totalAnggota = 0;
            $totalCabang = 0;
            $totalSimpanan = 0;
            $totalPinjaman = 0;
            $totalPinjamanBerjalan = 0;
            $totalAngsuranBelumLunas = 0;
            $pinjamanTerbaru = new Collection();
            $simpananTerbaru = new Collection();
            $anggotaTerbaru = new Collection();
            $labelsBulan = [];
            $dataSimpananBulan = [];
            $dataPinjamanBulan = [];
            $statusLabels = [];
            $statusValues = [];
            $cabangList = collect([]);
            try {
                $defAwalCatch = Carbon::now()->startOfMonth()->toDateString();
                $defAkhirCatch = Carbon::now()->endOfMonth()->toDateString();
            } catch (\Throwable $e2) {
                $defAwalCatch = null;
                $defAkhirCatch = null;
            }
            $currentFilter = ['cabang_id' => null, 'tanggal_awal' => $defAwalCatch, 'tanggal_akhir' => $defAkhirCatch];
            $isAnggota = Auth::check() && Auth::user()->hasRole('Anggota');
            $lockCabangToUser = false;
            $lockedCabangId = null;
            $lockedCabangNama = null;
            $lockedCabangKode = null;

            return view("modules.dashboard", compact(
                'totalAnggota',
                'totalCabang',
                'totalSimpanan',
                'totalPinjaman',
                'totalPinjamanBerjalan',
                'totalAngsuranBelumLunas',
                'pinjamanTerbaru',
                'simpananTerbaru',
                'anggotaTerbaru',
                'labelsBulan',
                'dataSimpananBulan',
                'dataPinjamanBulan',
                'statusLabels',
                'statusValues',
                'cabangList',
                'currentFilter',
                'isAnggota',
                'lockCabangToUser',
                'lockedCabangId',
                'lockedCabangNama',
                'lockedCabangKode',
            ));
        }
    }
}
