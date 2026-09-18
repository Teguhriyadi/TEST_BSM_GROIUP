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
        return view("modules.jenis-pinjaman.create");
    }

    public function store(JenisPinjamanCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $valid = $request->validated();
            $jenisPinjaman = JenisPinjaman::create($valid);

            DB::commit();
            return redirect()->route('jenis-pinjaman.index')->with('success', 'Data berhasil disimpan. Untuk mengatur persyaratan dokumen jenis ini, gunakan menu Master Dokumen → Setting Persyaratan Pinjaman.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $jenisPinjaman = JenisPinjaman::findOrFail($id);
        return view("modules.jenis-pinjaman.edit", compact('jenisPinjaman'));
    }

    public function update(JenisPinjamanUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $jenisPinjaman = JenisPinjaman::findOrFail($id);
            $valid = $request->validated();
            $jenisPinjaman->update($valid);

            DB::commit();
            return redirect()->route('jenis-pinjaman.index')->with('success', 'Data berhasil diubah. Untuk mengatur persyaratan dokumen jenis ini, gunakan menu Master Dokumen → Setting Persyaratan Pinjaman.');
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
