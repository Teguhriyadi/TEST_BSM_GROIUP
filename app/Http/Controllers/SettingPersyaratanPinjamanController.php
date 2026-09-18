<?php

namespace App\Http\Controllers;

use App\Models\JenisPinjaman;
use App\Models\MasterDokumen;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingPersyaratanPinjamanController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $jenisList = JenisPinjaman::with(['dokumenPersyaratan' => function ($q) {
                $q->where('master_dokumen.is_active', true)
                  ->orderByPivot('urutan', 'asc')
                  ->orderBy('master_dokumen.nama_dokumen', 'asc');
            }])->orderBy('nama_jenis', 'asc')->get();

            $masterAktif = MasterDokumen::where('is_active', true)
                ->orderBy('nama_dokumen', 'asc')
                ->get(['id', 'kode_dokumen', 'nama_dokumen', 'deskripsi', 'format_diperbolehkan']);

            $selectedJenisId = $request->get('jenis_pinjaman_id', $jenisList->first()?->id ?? null);
            $selectedJenis = $selectedJenisId ? $jenisList->firstWhere('id', $selectedJenisId) : null;

            $hideFilterBar = false;
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id' => null, 'tanggal_awal' => null, 'tanggal_akhir' => null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('setting-persyaratan-pinjaman.index');
            return view('modules.setting-persyaratan-pinjaman.index', compact(
                'jenisList',
                'masterAktif',
                'selectedJenisId',
                'selectedJenis',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat setting persyaratan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $jenisPinjamanId)
    {
        DB::beginTransaction();
        try {
            $jenis = JenisPinjaman::findOrFail($jenisPinjamanId);
            $masterDokumenIds = array_values(array_unique(array_map('strval', $request->input('master_dokumen_ids', []))));
            $requestWajibMap = $request->input('wajib', []);
            if (! is_array($requestWajibMap)) {
                $requestWajibMap = [];
            }
            $syncData = [];
            $urutan = 1;
            foreach ($masterDokumenIds as $mid) {
                $existing = $jenis->dokumenPersyaratan()
                    ->where('master_dokumen_id', $mid)
                    ->first();
                $isWajib = !empty($requestWajibMap[$mid]) && in_array($requestWajibMap[$mid], ['1', 'true', 'on', true, 1], true);
                $syncData[$mid] = [
                    'id' => $existing?->pivot?->id ?? Str::uuid()->toString(),
                    'is_wajib' => $isWajib,
                    'urutan' => $urutan++,
                ];
            }
            $jenis->dokumenPersyaratan()->sync($syncData);
            DB::commit();
            return redirect()
                ->route('setting-persyaratan-pinjaman.index', ['jenis_pinjaman_id' => $jenis->id])
                ->with('success', 'Setting persyaratan untuk ' . e($jenis->nama_jenis) . ' berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan setting persyaratan: ' . $e->getMessage());
        }
    }
}
