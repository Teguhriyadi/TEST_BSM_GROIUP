<?php

namespace App\Http\Controllers;

use App\Models\JenisPinjaman;
use App\Traits\WithModuleFilter;
use App\Http\Requests\JenisPinjaman\JenisPinjamanCreateRequest;
use App\Http\Requests\JenisPinjaman\JenisPinjamanUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JenisPinjamanController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $jenisPinjaman = JenisPinjaman::orderBy('nama_jenis', 'asc')->get();
            $hideFilterBar = true;
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id'=>null,'tanggal_awal'=>null,'tanggal_akhir'=>null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('jenis-pinjaman.index');
            return view("modules.jenis-pinjaman.index", compact(
                'jenisPinjaman',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data jenis pinjaman: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $masterDokumen = \App\Models\MasterDokumen::where('is_active', true)
            ->orderBy('nama_dokumen', 'asc')
            ->get(['id', 'kode_dokumen', 'nama_dokumen', 'deskripsi']);
        $selectedIds = old('master_dokumen_ids', []);
        return view("modules.jenis-pinjaman.create", compact('masterDokumen', 'selectedIds'));
    }

    public function store(JenisPinjamanCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $valid = $request->validated();
            $masterDokumenIds = array_values(array_unique(array_map('strval', $valid['master_dokumen_ids'] ?? [])));
            unset($valid['master_dokumen_ids']);
            $jenisPinjaman = JenisPinjaman::create($valid);

            if ($masterDokumenIds !== []) {
                $syncData = [];
                $urutan = 1;
                foreach ($masterDokumenIds as $mid) {
                    $syncData[$mid] = [
                        'id' => \Illuminate\Support\Str::uuid()->toString(),
                        'is_wajib' => true,
                        'urutan' => $urutan++,
                    ];
                }
                $jenisPinjaman->dokumenPersyaratan()->sync($syncData);
            }

            DB::commit();
            return redirect()->route('jenis-pinjaman.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $jenisPinjaman = JenisPinjaman::with('dokumenPersyaratanWajib:id')->findOrFail($id);
        $masterDokumen = \App\Models\MasterDokumen::where('is_active', true)
            ->orderBy('nama_dokumen', 'asc')
            ->get(['id', 'kode_dokumen', 'nama_dokumen', 'deskripsi']);
        $selectedIds = old(
            'master_dokumen_ids',
            $jenisPinjaman->dokumenPersyaratanWajib->pluck('id')->map(fn ($v) => (string) $v)->values()->all(),
        );
        return view("modules.jenis-pinjaman.edit", compact('jenisPinjaman', 'masterDokumen', 'selectedIds'));
    }

    public function update(JenisPinjamanUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $jenisPinjaman = JenisPinjaman::findOrFail($id);
            $valid = $request->validated();
            $masterDokumenIds = array_values(array_unique(array_map('strval', $valid['master_dokumen_ids'] ?? [])));
            unset($valid['master_dokumen_ids']);
            $jenisPinjaman->update($valid);

            $syncData = [];
            $urutan = 1;
            foreach ($masterDokumenIds as $mid) {
                $existing = $jenisPinjaman->dokumenPersyaratan()
                    ->where('master_dokumen_id', $mid)
                    ->first();
                $syncData[$mid] = [
                    'id' => $existing?->pivot?->id ?? \Illuminate\Support\Str::uuid()->toString(),
                    'is_wajib' => true,
                    'urutan' => $urutan++,
                ];
            }
            $jenisPinjaman->dokumenPersyaratan()->sync($syncData);

            DB::commit();
            return redirect()->route('jenis-pinjaman.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $jenisPinjaman = JenisPinjaman::findOrFail($id);
            $jenisPinjaman->delete();
            DB::commit();
            return redirect()->route('jenis-pinjaman.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Data gagal dihapus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $jenisPinjaman = JenisPinjaman::with('dokumenPersyaratanWajib', 'dokumenPersyaratan')
                ->withCount('pinjaman')
                ->findOrFail($id);
            $daftarPinjaman = $jenisPinjaman->pinjaman()
                ->orderBy('created_at', 'desc')
                ->limit(25)
                ->with('anggota:id,nama,no_anggota', 'cabang:id,nama_cabang')
                ->get();
            return view("modules.jenis-pinjaman.show", compact('jenisPinjaman', 'daftarPinjaman'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail jenis pinjaman: ' . $e->getMessage());
        }
    }
}
