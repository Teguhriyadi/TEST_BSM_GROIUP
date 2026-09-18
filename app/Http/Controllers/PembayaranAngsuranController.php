<?php

namespace App\Http\Controllers;

use App\Models\PembayaranAngsuran;
use App\Models\Angsuran;
use App\Models\User;
use App\Traits\WithModuleFilter;
use App\Http\Requests\PembayaranAngsuran\PembayaranAngsuranCreateRequest;
use App\Http\Requests\PembayaranAngsuran\PembayaranAngsuranUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembayaranAngsuranController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $resolved = $this->resolveFilter($request, 'pembayaran-angsuran', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'tanggal_bayar',
                'force_scope_cabang_user' => true,
            ]);
            $query = PembayaranAngsuran::with(['angsuran', 'angsuran.pinjaman', 'angsuran.pinjaman.anggota', 'dibayarOleh']);
            $forcedCabangBayar = $this->userForcedCabangId();
            $appliedCabangBayar = $forcedCabangBayar ?? ($resolved['filter']['cabang_id'] ?? null);
            if (! empty($appliedCabangBayar)) {
                $query->whereHas('angsuran.pinjaman', function ($qq) use ($appliedCabangBayar) {
                    $qq->where('cabang_id', $appliedCabangBayar);
                });
            }
            if (! empty($resolved['filter']['tanggal_awal'])) {
                try {
                    $d = \Carbon\Carbon::createFromFormat('Y-m-d', $resolved['filter']['tanggal_awal'])?->startOfDay();
                    if ($d) {
                        $query->whereDate('tanggal_bayar', '>=', $d->toDateString());
                    }
                } catch (\Throwable $e) {
                }
            }
            if (! empty($resolved['filter']['tanggal_akhir'])) {
                try {
                    $d = \Carbon\Carbon::createFromFormat('Y-m-d', $resolved['filter']['tanggal_akhir'])?->endOfDay();
                    if ($d) {
                        $query->whereDate('tanggal_bayar', '<=', $d->toDateString());
                    }
                } catch (\Throwable $e) {
                }
            }
            $pembayaranAngsuran = $query->orderBy('tanggal_bayar', 'desc')->get();
            $filterFormAction = route('pembayaran-angsuran.index');
            $filterView = $this->buildViewFilterVars($resolved);
            return view("modules.pembayaran-angsuran.index", array_merge(compact(
                'pembayaranAngsuran',
                'filterFormAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data pembayaran angsuran: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $angsuran = Angsuran::where('status', '!=', 'lunas')->with(['pinjaman', 'pinjaman.anggota'])->get();
        $users = User::where('is_active', '1')->get(['id', 'nama']);
        return view("modules.pembayaran-angsuran.create", compact('angsuran', 'users'));
    }

    public function store(PembayaranAngsuranCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $valid = $request->validated();
            $valid['dibayar_oleh'] = $valid['dibayar_oleh'] ?? (auth()->check() ? auth()->id() : null);
            PembayaranAngsuran::create($valid);
            DB::commit();
            return redirect()->route('pembayaran-angsuran.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal disimpan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $pembayaranAngsuran = PembayaranAngsuran::findOrFail($id);
        $angsuran = Angsuran::where(function ($q) use ($pembayaranAngsuran) {
            $q->where('status', '!=', 'lunas')->orWhere('id', $pembayaranAngsuran->angsuran_id);
        })->with(['pinjaman', 'pinjaman.anggota'])->get();
        $users = User::where('is_active', '1')->get(['id', 'nama']);
        return view("modules.pembayaran-angsuran.edit", compact('pembayaranAngsuran', 'angsuran', 'users'));
    }

    public function update(PembayaranAngsuranUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $pembayaranAngsuran = PembayaranAngsuran::findOrFail($id);
            $valid = $request->validated();
            $valid['dibayar_oleh'] = $valid['dibayar_oleh'] ?? (auth()->check() ? auth()->id() : null);
            $pembayaranAngsuran->update($valid);
            DB::commit();
            return redirect()->route('pembayaran-angsuran.index')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pembayaranAngsuran = PembayaranAngsuran::findOrFail($id);
            $pembayaranAngsuran->delete();
            DB::commit();
            return redirect()->route('pembayaran-angsuran.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Data gagal dihapus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $pembayaran = PembayaranAngsuran::with([
                'angsuran',
                'angsuran.pinjaman',
                'angsuran.pinjaman.anggota:id,nama,no_anggota',
                'angsuran.pinjaman.cabang:id,nama_cabang',
                'dibayarOleh:id,nama',
            ])->findOrFail($id);

            $user = Auth::user();
            if ($user && ! $user->hasRole('Administrator') && $user->cabang_id) {
                if ((string) ($pembayaran->angsuran->pinjaman->cabang_id ?? '') !== (string) $user->cabang_id) {
                    abort(403, 'Anda hanya bisa melihat pembayaran di cabang Anda sendiri.');
                }
            }

            return view("modules.pembayaran-angsuran.show", compact('pembayaran'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail pembayaran: ' . $e->getMessage());
        }
    }
}
