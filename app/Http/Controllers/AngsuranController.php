<?php

namespace App\Http\Controllers;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Traits\WithModuleFilter;
use App\Http\Requests\Angsuran\AngsuranCreateRequest;
use App\Http\Requests\Angsuran\AngsuranUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AngsuranController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $resolved = $this->resolveFilter($request, 'angsuran', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'tanggal_jatuh_tempo',
                'force_scope_cabang_user' => true,
                'custom_filters' => [
                    'status_angsuran' => [
                        'label' => 'Status Angsuran',
                        'placeholder' => 'Semua Status',
                        'options' => [
                            ['value' => 'belum_lunas', 'label' => 'Belum Lunas'],
                            ['value' => 'sebagian_lunas', 'label' => 'Dibayar Sebagian'],
                            ['value' => 'lunas', 'label' => 'Lunas'],
                            ['value' => 'lewat_jatuh_tempo', 'label' => 'Lewat Jatuh Tempo'],
                        ],
                    ],
                ],
            ]);
            $query = Angsuran::with(['pinjaman', 'pinjaman.anggota', 'pembayaranAngsuran']);
            $forcedCabang = $this->userForcedCabangId();
            $appliedCabang = $forcedCabang ?? ($resolved['filter']['cabang_id'] ?? null);
            if (! empty($appliedCabang)) {
                $query->whereHas('pinjaman', function ($qq) use ($appliedCabang) {
                    $qq->where('cabang_id', $appliedCabang);
                });
            }
            if (! empty($resolved['filter']['tanggal_awal'])) {
                try {
                    $d = \Carbon\Carbon::createFromFormat('Y-m-d', $resolved['filter']['tanggal_awal'])?->startOfDay();
                    if ($d) {
                        $query->whereDate('tanggal_jatuh_tempo', '>=', $d->toDateString());
                    }
                } catch (\Throwable $e) {
                }
            }
            if (! empty($resolved['filter']['tanggal_akhir'])) {
                try {
                    $d = \Carbon\Carbon::createFromFormat('Y-m-d', $resolved['filter']['tanggal_akhir'])?->endOfDay();
                    if ($d) {
                        $query->whereDate('tanggal_jatuh_tempo', '<=', $d->toDateString());
                    }
                } catch (\Throwable $e) {
                }
            }
            $statusAngsuran = $resolved['filter']['status_angsuran'] ?? null;
            if ($statusAngsuran !== null && $statusAngsuran !== '') {
                $today = \Carbon\Carbon::now()->toDateString();
                if ($statusAngsuran === 'lewat_jatuh_tempo') {
                    $query->where('tanggal_jatuh_tempo', '<', $today)
                        ->where(function ($qq) {
                            $qq->where('status', '!=', 'lunas');
                        });
                } else {
                    $query->where('status', $statusAngsuran);
                }
            }
            $angsuran = $query->orderBy('tanggal_jatuh_tempo', 'asc')->get();
            $filterFormAction = route('angsuran.index');
            $filterView = $this->buildViewFilterVars($resolved);
            return view("modules.angsuran.index", array_merge(compact(
                'angsuran',
                'filterFormAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data angsuran: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $pinjaman = Pinjaman::whereIn('status', ['dicairkan', 'berjalan'])->with(['anggota'])->get(['id', 'nomor_pinjaman', 'jumlah_pinjaman', 'anggota_id']);
        return view("modules.angsuran.create", compact('pinjaman'));
    }

    public function store(AngsuranCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            Angsuran::create($request->validated());
            DB::commit();
            return redirect()->route('angsuran.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $angsuran = Angsuran::findOrFail($id);
        $pinjaman = Pinjaman::whereIn('status', ['dicairkan', 'berjalan'])->with(['anggota'])->get(['id', 'nomor_pinjaman', 'jumlah_pinjaman', 'anggota_id']);
        return view("modules.angsuran.edit", compact('angsuran', 'pinjaman'));
    }

    public function update(AngsuranUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $angsuran = Angsuran::findOrFail($id);
            $angsuran->update($request->validated());
            DB::commit();
            return redirect()->route('angsuran.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $angsuran = Angsuran::findOrFail($id);
            $angsuran->delete();
            DB::commit();
            return redirect()->route('angsuran.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Data gagal dihapus: ' . $e->getMessage());
        }
    }
}
