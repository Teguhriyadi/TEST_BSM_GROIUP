<?php

namespace App\Http\Controllers;

use App\Http\Requests\MasterDokumen\MasterDokumenCreateRequest;
use App\Http\Requests\MasterDokumen\MasterDokumenUpdateRequest;
use App\Models\MasterDokumen;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterDokumenController extends Controller
{
    use WithModuleFilter;

    public function index(Request $request)
    {
        try {
            $query = MasterDokumen::query();
            $statusFilter = $request->get('status_filter');
            if ($statusFilter === 'aktif') {
                $query->where('is_active', true);
            } elseif ($statusFilter === 'nonaktif') {
                $query->where('is_active', false);
            }
            $cari = trim((string) $request->get('cari', ''));
            if ($cari !== '') {
                $query->where(function ($q) use ($cari) {
                    $q->where('kode_dokumen', 'like', '%' . $cari . '%')
                      ->orWhere('nama_dokumen', 'like', '%' . $cari . '%')
                      ->orWhere('deskripsi', 'like', '%' . $cari . '%');
                });
            }
            $masterDokumen = $query->orderBy('is_active', 'desc')
                ->orderBy('kode_dokumen', 'asc')
                ->paginate(25)
                ->withQueryString();

            $hideFilterBar = false;
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id' => null, 'tanggal_awal' => null, 'tanggal_akhir' => null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('master-dokumen.index');
            return view('modules.master-dokumen.index', compact(
                'masterDokumen',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
                'statusFilter',
                'cari'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat master dokumen: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('modules.master-dokumen.create');
    }

    public function store(MasterDokumenCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $valid = $request->validated();
            $valid['is_active'] = !empty($valid['is_active']) && in_array($valid['is_active'], ['1', 'true', 'on', true, 1], true);
            if (empty(trim((string) ($valid['format_diperbolehkan'] ?? '')))) {
                $valid['format_diperbolehkan'] = 'jpg,jpeg,png,pdf';
            }
            MasterDokumen::create($valid);
            DB::commit();
            return redirect()->route('master-dokumen.index')->with('success', 'Master dokumen berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan master dokumen: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $masterDokumen = MasterDokumen::withCount(['pinjamanDokumen', 'jenisPinjaman'])->findOrFail($id);
            $daftarJenis = $masterDokumen->jenisPinjaman()
                ->orderBy('nama_jenis', 'asc')
                ->limit(50)
                ->get(['jenis_pinjaman.id', 'nama_jenis', 'bunga_tahunan', 'tenor_minimal', 'tenor_maksimal']);
            $riwayatPinjaman = $masterDokumen->pinjamanDokumen()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->with(['pinjaman:id,nomor_pinjaman,tgl_pengajuan,anggota_id', 'pinjaman.anggota:id,nama,no_anggota'])
                ->get();
            return view('modules.master-dokumen.show', compact('masterDokumen', 'daftarJenis', 'riwayatPinjaman'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail master dokumen: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $masterDokumen = MasterDokumen::findOrFail($id);
        return view('modules.master-dokumen.edit', compact('masterDokumen'));
    }

    public function update(MasterDokumenUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $masterDokumen = MasterDokumen::findOrFail($id);
            $valid = $request->validated();
            $valid['is_active'] = !empty($valid['is_active']) && in_array($valid['is_active'], ['1', 'true', 'on', true, 1], true);
            if (empty(trim((string) ($valid['format_diperbolehkan'] ?? '')))) {
                $valid['format_diperbolehkan'] = 'jpg,jpeg,png,pdf';
            }
            $masterDokumen->update($valid);
            DB::commit();
            return redirect()->route('master-dokumen.index')->with('success', 'Master dokumen berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui master dokumen: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $masterDokumen = MasterDokumen::withCount(['pinjamanDokumen', 'jenisPinjaman'])->findOrFail($id);
            if (($masterDokumen->pinjaman_dokumen_count ?? 0) > 0) {
                throw new \RuntimeException('Master dokumen ini sudah dipakai pada data pinjaman, tidak dapat dihapus. Nonaktifkan saja melalui edit.');
            }
            if (($masterDokumen->jenis_pinjaman_count ?? 0) > 0) {
                throw new \RuntimeException('Master dokumen ini masih ter-assign sebagai persyaratan di beberapa jenis pinjaman, tidak dapat dihapus. Lepas dulu dari setting persyaratan.');
            }
            $masterDokumen->delete();
            DB::commit();
            return redirect()->route('master-dokumen.index')->with('success', 'Master dokumen berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus master dokumen: ' . $e->getMessage());
        }
    }
}
