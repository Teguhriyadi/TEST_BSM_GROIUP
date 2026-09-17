<?php

namespace App\Http\Controllers;

use App\Models\Simpanan;
use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\JenisSimpanan;
use App\Traits\WithModuleFilter;
use App\Http\Requests\Simpanan\SimpananCreateRequest;
use App\Http\Requests\Simpanan\SimpananUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimpananController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            if (Auth::check() && Auth::user()->hasRole('Anggota')) {
                $anggotaAkun = Anggota::where('users_id', Auth::id())->first();
                if (! $anggotaAkun) {
                    return redirect(route('dashboard'))->with('warning', 'Akun anggota Anda belum terhubung dengan data nasabah. Hubungi teller koperasi.');
                }
                $simpanan = Simpanan::with(['anggota', 'cabang', 'jenisSimpanan'])
                    ->where('anggota_id', $anggotaAkun->id)
                    ->orderBy('tanggal', 'desc')
                    ->get();
                $cabangFilterList = collect();
                $currentModuleFilter = ['cabang_id'=>null,'tanggal_awal'=>null,'tanggal_akhir'=>null];
                $isAnggotaFilter = true;
                $moduleFilterSummary = null;
                $filterFormAction = route('simpanan.index');
                $hideFilterBar = true;
                return view("modules.simpanan.index", compact(
                    'simpanan',
                    'cabangFilterList',
                    'currentModuleFilter',
                    'isAnggotaFilter',
                    'moduleFilterSummary',
                    'filterFormAction',
                    'hideFilterBar',
                ));
            }

            $resolved = $this->resolveFilter($request, 'simpanan', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'tanggal',
                'force_scope_cabang_user' => true,
            ]);
            $query = Simpanan::with(['anggota', 'cabang', 'jenisSimpanan']);
            $this->applyFilter($query, $resolved);
            $simpanan = $query->orderBy('tanggal', 'desc')->get();
            $filterFormAction = route('simpanan.index');
            $filterView = $this->buildViewFilterVars($resolved);
            return view("modules.simpanan.index", array_merge(compact(
                'simpanan',
                'filterFormAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data simpanan: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $anggota = Anggota::where('status', 'aktif')->get(['id', 'nama', 'no_anggota']);
        $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);
        $jenisSimpanan = JenisSimpanan::all(['id', 'nama_jenis']);
        return view("modules.simpanan.create", compact('anggota', 'cabang', 'jenisSimpanan'));
    }

    public function store(SimpananCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            Simpanan::create($request->validated());
            DB::commit();
            return redirect()->route('simpanan.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $simpanan = Simpanan::findOrFail($id);
        $anggota = Anggota::where('status', 'aktif')->get(['id', 'nama', 'no_anggota']);
        $cabang = Cabang::where('is_active', '1')->get(['id', 'nama_cabang']);
        $jenisSimpanan = JenisSimpanan::all(['id', 'nama_jenis']);
        return view("modules.simpanan.edit", compact('simpanan', 'anggota', 'cabang', 'jenisSimpanan'));
    }

    public function update(SimpananUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $simpanan = Simpanan::findOrFail($id);
            $simpanan->update($request->validated());
            DB::commit();
            return redirect()->route('simpanan.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $simpanan = Simpanan::findOrFail($id);
            $simpanan->delete();
            DB::commit();
            return redirect()->route('simpanan.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Data gagal dihapus: ' . $e->getMessage());
        }
    }
}
