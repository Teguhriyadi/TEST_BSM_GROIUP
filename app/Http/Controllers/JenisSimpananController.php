<?php

namespace App\Http\Controllers;

use App\Models\JenisSimpanan;
use App\Traits\WithModuleFilter;
use App\Http\Requests\JenisSimpanan\JenisSimpananCreateRequest;
use App\Http\Requests\JenisSimpanan\JenisSimpananUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JenisSimpananController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $jenisSimpanan = JenisSimpanan::orderBy('nama_jenis', 'asc')->get();
            $hideFilterBar = true;
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id'=>null,'tanggal_awal'=>null,'tanggal_akhir'=>null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('jenis-simpanan.index');
            return view("modules.jenis-simpanan.index", compact(
                'jenisSimpanan',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data jenis simpanan: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view("modules.jenis-simpanan.create");
    }

    public function store(JenisSimpananCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            JenisSimpanan::create($request->validated());
            DB::commit();
            return redirect()->route('jenis-simpanan.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $jenisSimpanan = JenisSimpanan::findOrFail($id);
        return view("modules.jenis-simpanan.edit", compact('jenisSimpanan'));
    }

    public function update(JenisSimpananUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $jenisSimpanan = JenisSimpanan::findOrFail($id);
            $jenisSimpanan->update($request->validated());
            DB::commit();
            return redirect()->route('jenis-simpanan.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $jenisSimpanan = JenisSimpanan::findOrFail($id);
            $jenisSimpanan->delete();
            DB::commit();
            return redirect()->route('jenis-simpanan.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Data gagal dihapus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $jenisSimpanan = JenisSimpanan::withCount('simpanan')->findOrFail($id);
            $riwayatSimpanan = $jenisSimpanan->simpanan()
                ->orderBy('tanggal', 'desc')
                ->limit(30)
                ->with('anggota:id,nama,no_anggota', 'cabang:id,nama_cabang')
                ->get();
            return view("modules.jenis-simpanan.show", compact('jenisSimpanan', 'riwayatSimpanan'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail jenis simpanan: ' . $e->getMessage());
        }
    }
}
