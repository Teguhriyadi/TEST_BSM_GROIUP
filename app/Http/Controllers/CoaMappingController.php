<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoaMapping\CoaMappingUpdateRequest;
use App\Models\Coa;
use App\Models\CoaMapping;
use App\Models\Cabang;
use App\Traits\WithExportable;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoaMappingController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $tipeList = [
                'simpanan_setoran' => 'Setoran Simpanan Anggota',
                'simpanan_penarikan' => 'Penarikan Simpanan Anggota',
                'pinjaman_cair' => 'Pencairan Pinjaman Anggota',
                'angsuran_pokok' => 'Angsuran Pokok Pinjaman',
                'angsuran_bunga' => 'Angsuran Bunga Pinjaman',
                'biaya_administrasi' => 'Biaya Administrasi',
                'denda_tunggakan' => 'Denda Tunggakan',
            ];
            $mapping = [];
            $query = CoaMapping::with(['coa', 'cabang']);
            if ($cabangId) {
                $query->where('cabang_id', $cabangId);
            } else {
                $query->whereNull('cabang_id');
            }
            foreach ($query->get() as $row) {
                $mapping[$row->tipe_transaksi][$row->posisi] = $row;
            }
            $coaAktif = Coa::where('is_active', '1');
            if (!$isAdmin && $user?->cabang_id) {
                $coaAktif->where(function ($q) use ($user) {
                    $q->whereNull('cabang_id')->orWhere('cabang_id', $user->cabang_id);
                });
            }
            $coaAktif = $coaAktif->orderBy('kode_akun', 'asc')->get();
            $cabangs = Cabang::where('is_active', '1')->orderBy('nama_cabang', 'asc')->get();
            $hideFilterBar = true;
            $cabangFilterList = $cabangs;
            $currentModuleFilter = ['cabang_id' => $cabangId, 'tanggal_awal' => null, 'tanggal_akhir' => null];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('coa-mapping.index');
            $exportPdfUrl = route('coa-mapping.exportPdf');
            $exportExcelUrl = route('coa-mapping.exportExcel');
            return view("modules.coa-mapping.index", compact(
                'tipeList',
                'mapping',
                'coaAktif',
                'cabangs',
                'cabangId',
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
            return back()->with('error', 'Gagal memuat mapping akun: ' . $e->getMessage());
        }
    }

    private function getExportDataMapping(Request $request): array
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
        if (!$isAdmin && $user?->cabang_id) {
            $cabangId = $user->cabang_id;
        }

        $tipeList = [
            'simpanan_setoran' => 'Setoran Simpanan Anggota',
            'simpanan_penarikan' => 'Penarikan Simpanan Anggota',
            'pinjaman_cair' => 'Pencairan Pinjaman Anggota',
            'angsuran_pokok' => 'Angsuran Pokok Pinjaman',
            'angsuran_bunga' => 'Angsuran Bunga Pinjaman',
            'biaya_administrasi' => 'Biaya Administrasi',
            'denda_tunggakan' => 'Denda Tunggakan',
        ];

        $mapping = [];
        $query = CoaMapping::with(['coa', 'cabang']);
        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        } else {
            $query->whereNull('cabang_id');
        }
        foreach ($query->get() as $row) {
            $mapping[$row->tipe_transaksi][$row->posisi] = $row;
        }

        $rows = [];
        $no = 1;
        foreach ($tipeList as $tipeKey => $tipeLabel) {
            $debet = $mapping[$tipeKey]['debet'] ?? null;
            $kredit = $mapping[$tipeKey]['kredit'] ?? null;
            $rows[] = [
                'no' => $no++,
                'jenis_transaksi' => $tipeLabel,
                'kode_debet' => $debet?->coa?->kode_akun ?? '-',
                'nama_debet' => $debet?->coa?->nama_akun ?? '-',
                'kode_kredit' => $kredit?->coa?->kode_akun ?? '-',
                'nama_kredit' => $kredit?->coa?->nama_akun ?? '-',
                'cabang' => $debet?->cabang ? ($debet->cabang->kode_cabang . ' — ' . $debet->cabang->nama_cabang) : ($cabangId ? $this->namaCabangExport($cabangId) : 'Default / Semua Cabang'),
                'keterangan' => $debet?->keterangan ?? $kredit?->keterangan ?? '-',
            ];
        }

        return [$rows, $cabangId];
    }

    public function exportPdf(Request $request)
    {
        [$rows, $cabangId] = $this->getExportDataMapping($request);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'jenis_transaksi', 'label' => 'Jenis Transaksi', 'align' => 'text-start'],
            ['key' => 'kode_debet', 'label' => 'Kode COA Debet', 'align' => 'text-start'],
            ['key' => 'nama_debet', 'label' => 'Nama COA Debet', 'align' => 'text-start'],
            ['key' => 'kode_kredit', 'label' => 'Kode COA Kredit', 'align' => 'text-start'],
            ['key' => 'nama_kredit', 'label' => 'Nama COA Kredit', 'align' => 'text-start'],
            ['key' => 'cabang', 'label' => 'Cabang', 'align' => 'text-start'],
            ['key' => 'keterangan', 'label' => 'Keterangan', 'align' => 'text-start'],
        ];

        return $this->generatePdf(
            'Mapping Akun (COA) Otomatis',
            'coa-mapping',
            $headerKolom,
            $rows,
            fn($row) => $row,
            null,
            null,
            $cabangId,
            'landscape'
        );
    }

    public function exportExcel(Request $request)
    {
        [$rows, $cabangId] = $this->getExportDataMapping($request);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'jenis_transaksi', 'label' => 'Jenis Transaksi', 'align' => 'text-start'],
            ['key' => 'kode_debet', 'label' => 'Kode COA Debet', 'align' => 'text-start'],
            ['key' => 'nama_debet', 'label' => 'Nama COA Debet', 'align' => 'text-start'],
            ['key' => 'kode_kredit', 'label' => 'Kode COA Kredit', 'align' => 'text-start'],
            ['key' => 'nama_kredit', 'label' => 'Nama COA Kredit', 'align' => 'text-start'],
            ['key' => 'cabang', 'label' => 'Cabang', 'align' => 'text-start'],
            ['key' => 'keterangan', 'label' => 'Keterangan', 'align' => 'text-start'],
        ];

        return $this->generateExcel(
            'Mapping Akun (COA) Otomatis',
            'coa-mapping',
            $headerKolom,
            $rows,
            fn($row) => $row,
            null,
            null,
            $cabangId
        );
    }

    public function update(CoaMappingUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $rows = $request->validated()['mapping'];
            $cabangId = $request->input('cabang_id') ?: null;
            $now = now();
            foreach ($rows as $row) {
                $rowCabang = $row['cabang_id'] ?? $cabangId;
                $exist = CoaMapping::where('cabang_id', $rowCabang)
                    ->where('tipe_transaksi', $row['tipe_transaksi'])
                    ->where('posisi', $row['posisi'])
                    ->first();
                if ($exist) {
                    $exist->update([
                        'coa_id' => $row['coa_id'],
                        'keterangan' => $row['keterangan'] ?? null,
                    ]);
                } else {
                    CoaMapping::create([
                        'id' => (string) Str::uuid(),
                        'cabang_id' => $rowCabang,
                        'tipe_transaksi' => $row['tipe_transaksi'],
                        'posisi' => $row['posisi'],
                        'coa_id' => $row['coa_id'],
                        'keterangan' => $row['keterangan'] ?? null,
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('coa-mapping.index', ['cabang_id' => $cabangId])
                ->with('success', 'Mapping akun berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Mapping akun gagal disimpan: ' . $e->getMessage());
        }
    }
}
