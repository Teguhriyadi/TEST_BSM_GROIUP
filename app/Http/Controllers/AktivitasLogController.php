<?php

namespace App\Http\Controllers;

use App\Models\AktivitasLog;
use App\Models\Cabang;
use App\Models\User;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AktivitasLogController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $tanggalAwalRaw = $request->input('tanggal_awal');
            $tanggalAkhirRaw = $request->input('tanggal_akhir');
            $cabangId = $request->filled('cabang_id') && trim((string)$request->input('cabang_id')) !== '' ? $request->input('cabang_id') : null;
            $userId = $request->filled('users_id') && trim((string)$request->input('users_id')) !== '' ? $request->input('users_id') : null;
            $tipe = $request->filled('tipe_aktivitas') && trim((string)$request->input('tipe_aktivitas')) !== '' ? $request->input('tipe_aktivitas') : null;

            $tanggalAwal = $tanggalAwalRaw
                ? (Carbon::canBeCreatedFromFormat('Y-m-d', $tanggalAwalRaw) ? Carbon::createFromFormat('Y-m-d', $tanggalAwalRaw) : Carbon::now()->startOfMonth())
                : Carbon::now()->startOfMonth();
            $tanggalAkhir = $tanggalAkhirRaw
                ? (Carbon::canBeCreatedFromFormat('Y-m-d', $tanggalAkhirRaw) ? Carbon::createFromFormat('Y-m-d', $tanggalAkhirRaw) : Carbon::now()->endOfMonth())
                : Carbon::now()->endOfMonth();

            $query = AktivitasLog::with(['user', 'cabang'])
                ->whereBetween('created_at', [$tanggalAwal->copy()->startOfDay(), $tanggalAkhir->copy()->endOfDay()]);

            if (! empty($cabangId)) {
                $query->where('cabang_id', $cabangId);
            }
            if (! empty($userId)) {
                $query->where('users_id', $userId);
            }
            if (! empty($tipe)) {
                $query->where('tipe_aktivitas', $tipe);
            }

            $aktivitas = $query->orderBy('created_at', 'desc')->get();

            $daftarCabang = Cabang::orderBy('nama_cabang')->get();
            $daftarUser = User::orderBy('nama')->get();
            $daftarTipe = AktivitasLog::select('tipe_aktivitas')->distinct()->orderBy('tipe_aktivitas')->pluck('tipe_aktivitas')->toArray();

            $hideFilterBar = true;
            $lockCabangToUser = false;
            $lockedCabangId = null;
            $lockedCabangNama = null;
            $lockedCabangKode = null;
            $customFilterOptions = [];
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id'=>null,'tanggal_awal'=>null,'tanggal_akhir'=>null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('aktivitas-log.index');

            // Force scope user cabang untuk form filter bawah (AktivitasLog CARD ATAS DIHAPUS, tapi scope bawah juga apply)
            $user = auth()->user();
            if ($user && ! $user->hasRole('Administrator') && ! $user->hasRole('Anggota') && ! empty($user->cabang_id)) {
                $cabangId = $user->cabang_id;
                // Jika form di-submit dengan cabang_id selain user.cabang_id (iseng edit via devtools), override paksa
                if ($cabangId !== null && $cabangId !== '') {
                    $cabangId = $user->cabang_id;
                }
                if (! $daftarCabang->contains('id', $cabangId)) {
                    $cabangId = $user->cabang_id;
                }
            }

            return view('modules.aktivitas-log.index', compact(
                'aktivitas',
                'daftarCabang',
                'daftarUser',
                'daftarTipe',
                'tanggalAwal',
                'tanggalAkhir',
                'cabangId',
                'userId',
                'tipe',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar'
            ));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Gagal memuat aktivitas log: ' . $e->getMessage());
        }
    }
}
