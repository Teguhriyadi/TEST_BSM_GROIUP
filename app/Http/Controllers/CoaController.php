<?php

namespace App\Http\Controllers;

use App\Http\Requests\Coa\CoaCreateRequest;
use App\Http\Requests\Coa\CoaUpdateRequest;
use App\Models\Coa;
use App\Models\Cabang;
use App\Traits\WithModuleFilter;
use App\Traits\WithExportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoaController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $userCabang = $user?->cabang_id;
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $query = Coa::with(['parent', 'cabang'])->orderBy('kode_akun', 'asc');
            if (!$isAdmin) {
                $query->where(function ($q) use ($userCabang) {
                    $q->whereNull('cabang_id')->orWhere('cabang_id', $userCabang);
                });
            }
            $coa = $query->get();
            $hideFilterBar = true;
            $cabangFilterList = collect();
            $currentModuleFilter = ['cabang_id' => null, 'tanggal_awal' => null, 'tanggal_akhir' => null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('coa.index');
            $exportPdfUrl = route('coa.exportPdf');
            $exportExcelUrl = route('coa.exportExcel');
            return view("modules.coa.index", compact(
                'coa',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
                'exportPdfUrl',
                'exportExcelUrl',
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data COA: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $cabangs = Cabang::where('is_active', '1')->orderBy('nama_cabang', 'asc')->get();
        $parents = Coa::whereNull('parent_id')->orderBy('kode_akun', 'asc')->get();
        return view("modules.coa.create", compact('cabangs', 'parents'));
    }

    public function store(CoaCreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $payload = $request->validated();
            $payload['is_active'] = $payload['is_active'] ?? '1';
            Coa::create($payload);
            DB::commit();
            return redirect()->route('coa.index')->with('success', 'Data COA berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data COA gagal disimpan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $coa = Coa::with(['parent', 'children', 'cabang', 'coaMapping', 'saldoAwal', 'jurnalDetail.header'])->findOrFail($id);
            $this->abortJikaAksesCabangTidakValid($coa);
            return view("modules.coa.show", compact('coa'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail COA: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $coa = Coa::findOrFail($id);
        $this->abortJikaAksesCabangTidakValid($coa);
        $cabangs = Cabang::where('is_active', '1')->orderBy('nama_cabang', 'asc')->get();
        $parents = Coa::whereNull('parent_id')
            ->where('id', '!=', $coa->id)
            ->orderBy('kode_akun', 'asc')->get();
        return view("modules.coa.edit", compact('coa', 'cabangs', 'parents'));
    }

    public function update(CoaUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $coa = Coa::findOrFail($id);
            $this->abortJikaAksesCabangTidakValid($coa);
            $payload = $request->validated();
            $payload['is_active'] = $payload['is_active'] ?? '1';
            $coa->update($payload);
            DB::commit();
            return redirect()->route('coa.index')->with('success', 'Data COA berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Data COA gagal diubah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $coa = Coa::findOrFail($id);
            $this->abortJikaAksesCabangTidakValid($coa);
            $dipakai = DB::table('coa_mapping')->where('coa_id', $coa->id)->exists()
                || DB::table('coa_saldo_awal')->where('coa_id', $coa->id)->exists()
                || DB::table('jurnal_umum_detail')->where('coa_id', $coa->id)->exists()
                || DB::table('coa')->where('parent_id', $coa->id)->exists();
            if ($dipakai) {
                DB::rollBack();
                return back()->with('error', 'COA tidak bisa dihapus karena sudah dipakai di mapping, saldo awal, atau transaksi jurnal.');
            }
            $coa->delete();
            DB::commit();
            return redirect()->route('coa.index')->with('success', 'Data COA berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Data COA gagal dihapus: ' . $e->getMessage());
        }
    }

    private function abortJikaAksesCabangTidakValid(Coa $coa): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if ($user->role?->kode_role === 'ROL-ADM') {
            return;
        }
        $userCabang = $user->cabang_id;
        if ($coa->cabang_id !== null && (string) $coa->cabang_id !== (string) $userCabang) {
            abort(403);
        }
    }

    // ==================== EXPORT ====================
    private function getCoaExportHeader(): array
    {
        return [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kode', 'label' => 'Kode Akun', 'align' => 'text-start'],
            ['key' => 'nama', 'label' => 'Nama Akun', 'align' => 'text-start'],
            ['key' => 'level', 'label' => 'Level', 'align' => 'text-center'],
            ['key' => 'kelompok', 'label' => 'Kelompok Akun', 'align' => 'text-start'],
            ['key' => 'normal', 'label' => 'Saldo Normal', 'align' => 'text-center'],
            ['key' => 'parent', 'label' => 'Parent', 'align' => 'text-start'],
            ['key' => 'cabang', 'label' => 'Cabang', 'align' => 'text-start'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'text-center'],
        ];
    }

    private function getCoaExportData(Request $request): array
    {
        $user = Auth::user();
        $userCabang = $user?->cabang_id;
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $query = Coa::with(['parent', 'cabang'])->orderBy('kode_akun', 'asc');
        if (!$isAdmin) {
            $query->where(function ($q) use ($userCabang) {
                $q->whereNull('cabang_id')->orWhere('cabang_id', $userCabang);
            });
        }
        return [$query->get(), null, null, null];
    }

    public function exportPdf(Request $request)
    {
        try {
            [$data, $tanggalAwal, $tanggalAkhir, $cabangId] = $this->getCoaExportData($request);
            $header = $this->getCoaExportHeader();
            $rowFn = function ($row, $no) {
                $mapKelompok = ['A' => 'Aktiva', 'L' => 'Kewajiban', 'E' => 'Modal', 'R' => 'Pendapatan', 'X' => 'Beban'];
                return [
                    'no' => $no,
                    'kode' => e($row->kode_akun),
                    'nama' => e($row->nama_akun),
                    'level' => $row->level,
                    'kelompok' => $mapKelompok[$row->tipe_akun] ?? e($row->tipe_akun),
                    'normal' => $row->saldo_normal === 'D' ? 'Debet' : 'Kredit',
                    'parent' => $row->parent ? (e($row->parent->kode_akun) . ' ' . e($row->parent->nama_akun)) : '-',
                    'cabang' => $row->cabang ? (e($row->cabang->kode_cabang) . ' ' . e($row->cabang->nama_cabang)) : 'Global',
                    'status' => $row->is_active === '1' ? 'Aktif' : 'Non Aktif',
                ];
            };
            return $this->generatePdf('Daftar Chart of Accounts (COA)', 'coa', $header, $data, $rowFn, $tanggalAwal, $tanggalAkhir, $cabangId, 'landscape');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF COA: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            [$data, $tanggalAwal, $tanggalAkhir, $cabangId] = $this->getCoaExportData($request);
            $header = $this->getCoaExportHeader();
            $rowFn = function ($row, $no) {
                $mapKelompok = ['A' => 'Aktiva', 'L' => 'Kewajiban', 'E' => 'Modal', 'R' => 'Pendapatan', 'X' => 'Beban'];
                return [
                    'no' => $no,
                    'kode' => e($row->kode_akun),
                    'nama' => e($row->nama_akun),
                    'level' => $row->level,
                    'kelompok' => $mapKelompok[$row->tipe_akun] ?? e($row->tipe_akun),
                    'normal' => $row->saldo_normal === 'D' ? 'Debet' : 'Kredit',
                    'parent' => $row->parent ? (e($row->parent->kode_akun) . ' ' . e($row->parent->nama_akun)) : '-',
                    'cabang' => $row->cabang ? (e($row->cabang->kode_cabang) . ' ' . e($row->cabang->nama_cabang)) : 'Global',
                    'status' => $row->is_active === '1' ? 'Aktif' : 'Non Aktif',
                ];
            };
            return $this->generateExcel('Daftar Chart of Accounts (COA)', 'coa', $header, $data, $rowFn, $tanggalAwal, $tanggalAkhir, $cabangId);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export Excel COA: ' . $e->getMessage());
        }
    }
}
