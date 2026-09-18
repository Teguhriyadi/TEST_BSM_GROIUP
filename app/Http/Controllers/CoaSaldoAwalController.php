<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoaSaldoAwal\CoaSaldoAwalUpdateRequest;
use App\Models\Coa;
use App\Models\CoaSaldoAwal;
use App\Models\Cabang;
use App\Traits\WithExportable;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoaSaldoAwalController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $periode = $request->input('periode', date('Y-m'));
            if (!$cabangId) {
                $cabangPertama = Cabang::where('is_active', '1')->orderBy('kode_cabang', 'asc')->first();
                $cabangId = $cabangPertama?->id;
            }
            $coaAktif = Coa::with(['saldoAwal' => function ($q) use ($cabangId, $periode) {
                $q->where('periode', $periode)->where('cabang_id', $cabangId);
            }])->where('is_active', '1')->where('level', 2);
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
            $filterFormAction = route('coa-saldo-awal.index');
            $exportPdfUrl = route('coa-saldo-awal.exportPdf');
            $exportExcelUrl = route('coa-saldo-awal.exportExcel');
            return view("modules.coa-saldo-awal.index", compact(
                'coaAktif',
                'cabangs',
                'cabangId',
                'periode',
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
            return back()->with('error', 'Gagal memuat saldo awal: ' . $e->getMessage());
        }
    }

    private function getExportDataSaldoAwal(Request $request): array
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
        $periode = $request->input('periode', date('Y-m'));
        if (!$cabangId) {
            $cabangPertama = Cabang::where('is_active', '1')->orderBy('kode_cabang', 'asc')->first();
            $cabangId = $cabangPertama?->id;
        }
        if (!$isAdmin && $user?->cabang_id) {
            $cabangId = $user->cabang_id;
        }

        $coaAktif = Coa::with(['saldoAwal' => function ($q) use ($cabangId, $periode) {
            $q->where('periode', $periode)->where('cabang_id', $cabangId);
        }])->where('is_active', '1')->where('level', 2);
        if (!$isAdmin && $user?->cabang_id) {
            $coaAktif->where(function ($q) use ($user) {
                $q->whereNull('cabang_id')->orWhere('cabang_id', $user->cabang_id);
            });
        }
        $coaAktif = $coaAktif->orderBy('kode_akun', 'asc')->get();

        $rows = [];
        $no = 1;
        $totalDebet = 0;
        $totalKredit = 0;
        foreach ($coaAktif as $coa) {
            $sa = $coa->saldoAwal->first();
            $debet = (float) ($sa?->saldo_awal_debet ?? 0);
            $kredit = (float) ($sa?->saldo_awal_kredit ?? 0);
            $totalDebet += $debet;
            $totalKredit += $kredit;
            $rows[] = [
                'no' => $no++,
                'kode_akun' => $coa->kode_akun,
                'nama_akun' => $coa->nama_akun,
                'kelompok' => $coa->kelompok,
                'saldo_normal' => strtoupper($coa->saldo_normal),
                'saldo_awal_debet' => $debet,
                'saldo_awal_kredit' => $kredit,
                'periode' => $periode,
                'cabang' => $this->namaCabangExport($cabangId),
            ];
        }

        $summary = [
            [
                'no' => '',
                'kode_akun' => '',
                'nama_akun' => 'TOTAL',
                'kelompok' => '',
                'saldo_normal' => '',
                'saldo_awal_debet' => round($totalDebet, 2),
                'saldo_awal_kredit' => round($totalKredit, 2),
                'periode' => '',
                'cabang' => '',
            ]
        ];

        return [$rows, $summary, $cabangId, $periode];
    }

    public function exportPdf(Request $request)
    {
        [$rows, $summary, $cabangId, $periode] = $this->getExportDataSaldoAwal($request);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kode_akun', 'label' => 'Kode COA', 'align' => 'text-start'],
            ['key' => 'nama_akun', 'label' => 'Nama Akun', 'align' => 'text-start'],
            ['key' => 'kelompok', 'label' => 'Kelompok', 'align' => 'text-start'],
            ['key' => 'saldo_normal', 'label' => 'Normal', 'align' => 'text-center'],
            ['key' => 'saldo_awal_debet', 'label' => 'Saldo Awal Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'saldo_awal_kredit', 'label' => 'Saldo Awal Kredit (Rp)', 'align' => 'text-end'],
            ['key' => 'periode', 'label' => 'Periode', 'align' => 'text-start'],
        ];

        $tanggalAwal = $periode ? $periode . '-01' : null;
        return $this->generatePdf(
            'Saldo Awal Perkiraan (COA) Periode ' . $periode,
            'coa-saldo-awal',
            $headerKolom,
            $rows,
            fn($row) => $row,
            $tanggalAwal,
            null,
            $cabangId,
            'landscape',
            $summary
        );
    }

    public function exportExcel(Request $request)
    {
        [$rows, $summary, $cabangId, $periode] = $this->getExportDataSaldoAwal($request);

        $headerKolom = [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'kode_akun', 'label' => 'Kode COA', 'align' => 'text-start'],
            ['key' => 'nama_akun', 'label' => 'Nama Akun', 'align' => 'text-start'],
            ['key' => 'kelompok', 'label' => 'Kelompok', 'align' => 'text-start'],
            ['key' => 'saldo_normal', 'label' => 'Normal', 'align' => 'text-center'],
            ['key' => 'saldo_awal_debet', 'label' => 'Saldo Awal Debet (Rp)', 'align' => 'text-end'],
            ['key' => 'saldo_awal_kredit', 'label' => 'Saldo Awal Kredit (Rp)', 'align' => 'text-end'],
            ['key' => 'periode', 'label' => 'Periode', 'align' => 'text-start'],
        ];

        $tanggalAwal = $periode ? $periode . '-01' : null;
        return $this->generateExcel(
            'Saldo Awal Perkiraan (COA) Periode ' . $periode,
            'coa-saldo-awal',
            $headerKolom,
            $rows,
            fn($row) => $row,
            $tanggalAwal,
            null,
            $cabangId,
            $summary
        );
    }

    public function update(CoaSaldoAwalUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $periode = $request->validated()['periode'];
            $rows = $request->validated()['rows'];
            foreach ($rows as $row) {
                $exist = CoaSaldoAwal::where('cabang_id', $row['cabang_id'])
                    ->where('coa_id', $row['coa_id'])
                    ->where('periode', $periode)
                    ->first();
                if ($exist) {
                    $exist->update([
                        'saldo_awal_debet' => $row['saldo_awal_debet'],
                        'saldo_awal_kredit' => $row['saldo_awal_kredit'],
                    ]);
                } else {
                    CoaSaldoAwal::create([
                        'id' => (string) Str::uuid(),
                        'cabang_id' => $row['cabang_id'],
                        'coa_id' => $row['coa_id'],
                        'periode' => $periode,
                        'saldo_awal_debet' => $row['saldo_awal_debet'],
                        'saldo_awal_kredit' => $row['saldo_awal_kredit'],
                    ]);
                }
            }
            $cabangId = $request->input('cabang_id') ?: null;
            DB::commit();
            return redirect()->route('coa-saldo-awal.index', [
                'cabang_id' => $cabangId, 'periode' => $periode,
            ])->with('success', 'Saldo awal berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Saldo awal gagal disimpan: ' . $e->getMessage());
        }
    }
}
