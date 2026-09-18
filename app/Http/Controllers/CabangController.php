<?php

namespace App\Http\Controllers;

use App\Models\AktivitasLog;
use App\Models\Cabang;
use App\Http\Requests\Cabang\CabangCreateRequest;
use App\Http\Requests\Cabang\CabangUpdateRequest;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CabangController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $resolved = $this->resolveFilter($request, 'cabang', [
                'show_cabang' => false,
                'show_tanggal' => false,
                'custom_filters' => [
                    'status_cabang' => [
                        'label' => 'Status',
                        'placeholder' => 'Semua Status',
                        'options' => [
                            ['value' => '1', 'label' => 'Aktif'],
                            ['value' => '0', 'label' => 'Tidak Aktif'],
                        ],
                    ],
                ],
            ]);
            $query = Cabang::query();
            $filterStatus = $resolved['filter']['status_cabang'] ?? null;
            if ($filterStatus !== null && $filterStatus !== '') {
                $query->where('is_active', $filterStatus);
            }
            $cabang = $query->orderBy('nama_cabang', 'asc')->get();
            $filterFormAction = route('cabang.index');
            $filterView = $this->buildViewFilterVars($resolved);
            return view("modules.cabang.index", array_merge(compact(
                'cabang',
                'filterFormAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data cabang: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view("modules.cabang.create");
    }

    public function store(CabangCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $cabang = Cabang::create($request->validated());
            AktivitasLog::record(
                'CREATE_CABANG',
                'Menambah cabang baru: ' . $cabang->nama_cabang . ' (' . $cabang->kode_cabang . ')',
                $cabang
            );
            DB::commit();
            return redirect()->route('cabang.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $cabang = Cabang::findOrFail($id);
        return view("modules.cabang.edit", compact('cabang'));
    }

    public function update(CabangUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $cabang = Cabang::findOrFail($id);
            $cabang->update($request->validated());
            AktivitasLog::record(
                'UPDATE_CABANG',
                'Memperbarui cabang: ' . $cabang->nama_cabang . ' (' . $cabang->kode_cabang . ')',
                $cabang
            );
            DB::commit();
            return redirect()->route('cabang.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $cabang = Cabang::findOrFail($id);
            $snapshot = $cabang->nama_cabang . ' (' . $cabang->kode_cabang . ')';
            $cabang->delete();
            AktivitasLog::record(
                'DELETE_CABANG',
                'Menghapus cabang: ' . $snapshot
            );
            DB::commit();
            return redirect()->route('cabang.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Data gagal dihapus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $cabang = Cabang::withCount(['anggota', 'simpanan', 'pinjaman', 'users'])->findOrFail($id);
            $listAnggota = $cabang->anggota()->orderBy('created_at', 'desc')->limit(20)->get();
            $listUsers = $cabang->users()->orderBy('created_at', 'desc')->limit(20)->get();
            $listSimpanan = $cabang->simpanan()->orderBy('created_at', 'desc')->limit(20)->with('anggota:id,nama,no_anggota', 'jenisSimpanan:id,nama_jenis')->get();
            $listPinjaman = $cabang->pinjaman()->orderBy('created_at', 'desc')->limit(20)->with('anggota:id,nama,no_anggota', 'jenisPinjaman:id,nama_jenis')->get();
            return view("modules.cabang.show", compact(
                'cabang',
                'listAnggota',
                'listUsers',
                'listSimpanan',
                'listPinjaman'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail cabang: ' . $e->getMessage());
        }
    }
}
