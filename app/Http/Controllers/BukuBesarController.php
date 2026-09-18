<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Services\BukuBesarService;
use App\Traits\WithModuleFilter;
use App\Traits\WithExportable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BukuBesarController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request, BukuBesarService $bukuBesar)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $dari = $request->input('tanggal_awal', date('Y-m-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-t'));
            $coaId = $request->input('coa_id', null);

            $coaQuery = Coa::where('is_active', '1')->where('level', 2);
            if (!$isAdmin && $user?->cabang_id) {
                $coaQuery->where(function ($q) use ($user) {
                    $q->whereNull('cabang_id')->orWhere('cabang_id', $user->cabang_id);
                });
            }
            $daftarCoa = $coaQuery->orderBy('kode_akun', 'asc')->get();

            $detailRows = collect();
            $coaTerpilih = null;
            $saldoAkhir = 0;
            if ($coaId) {
                $coaTerpilih = Coa::with(['parent'])->findOrFail($coaId);
                $detailRows = $bukuBesar->getBukuBesarRows(
                    $coaTerpilih,
                    Carbon::parse($dari),
                    Carbon::parse($sampai),
                    $cabangId,
                );
                $saldoAkhir = $bukuBesar->getSaldoAkhirPerAkun($coaTerpilih, Carbon::parse($sampai), $cabangId);
            }
            $summary6Kolom = $bukuBesar->getNeracaSaldo6Kolom(
                Carbon::parse($sampai),
                $cabangId,
            );
            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $cabangId,
                'tanggal_awal' => $dari,
                'tanggal_akhir' => $sampai,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('buku-besar.index');
            $exportPdfUrl = route('buku-besar.exportPdf');
            $exportExcelUrl = route('buku-besar.exportExcel');
            $customFilterOptions = [['key' => 'coa_id', 'label' => 'Akun (COA)', 'options' => $daftarCoa->map(fn($c) => ['value' => $c->id, 'label' => $c->kode_akun . ' — ' . $c->nama_akun])->values()->toArray()]];
            return view("modules.buku-besar.index", compact(
                'daftarCoa',
                'coaId',
                'coaTerpilih',
                'detailRows',
                'dari',
                'sampai',
                'cabangId',
                'saldoAkhir',
                'summary6Kolom',
                'cabangFilterList',
                'currentModuleFilter',
                'isAnggotaFilter',
                'moduleFilterSummary',
                'filterFormAction',
                'hideFilterBar',
                'customFilterOptions',
                'exportPdfUrl',
                'exportExcelUrl',
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat buku besar: ' . $e->getMessage());
        }
    }

    // ==================== EXPORT ====================
    public function exportPdf(Request $request, BukuBesarService $bukuBesar)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $dari = $request->input('tanggal_awal', date('Y-m-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-t'));
            $coaId = $request->input('coa_id');

            $header = [
                ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
                ['key' => 'tgl', 'label' => 'Tanggal', 'align' => 'text-center'],
                ['key' => 'ref', 'label' => 'Ref / No Jurnal', 'align' => 'text-start'],
                ['key' => 'ket', 'label' => 'Keterangan', 'align' => 'text-start'],
                ['key' => 'debet', 'label' => 'Debet', 'align' => 'text-end'],
                ['key' => 'kredit', 'label' => 'Kredit', 'align' => 'text-end'],
                ['key' => 'saldo', 'label' => 'Saldo Akhir', 'align' => 'text-end'],
            ];

            $rows = collect();
            $judul = 'Buku Besar';
            if ($coaId) {
                $coa = Coa::with(['parent'])->findOrFail($coaId);
                $judul .= ' — ' . $coa->kode_akun . ' ' . $coa->nama_akun;
                $detailRows = $bukuBesar->getBukuBesarRows($coa, Carbon::parse($dari), Carbon::parse($sampai), $cabangId);
                $saldo = 0;
                foreach ($detailRows as $idx => $r) {
                    $debet = (float)($r->debet ?? 0);
                    $kredit = (float)($r->kredit ?? 0);
                    if ($coa->saldo_normal === 'D') $saldo = $saldo + $debet - $kredit;
                    else $saldo = $saldo + $kredit - $debet;
                    $rows->push(['r' => $r, 'no' => $idx + 1, 'saldo_run' => $saldo]);
                }
                $saldoAkhir = $bukuBesar->getSaldoAkhirPerAkun($coa, Carbon::parse($sampai), $cabangId);
                $summaryFooter = [[
                    'cells' => [
                        0 => '<strong>SALDO AKHIR</strong>',
                        'saldo' => '<strong>Rp ' . number_format($saldoAkhir, 0, ',', '.') . '</strong>',
                    ],
                ]];
            } else {
                $summary6 = $bukuBesar->getNeracaSaldo6Kolom(Carbon::parse($sampai), $cabangId);
                $header = [
                    ['key' => 'kode', 'label' => 'Kode Akun', 'align' => 'text-start'],
                    ['key' => 'nama', 'label' => 'Nama Akun', 'align' => 'text-start'],
                    ['key' => 'normal', 'label' => 'Normal', 'align' => 'text-center'],
                    ['key' => 'sa_d', 'label' => 'Saldo Awal Debet', 'align' => 'text-end'],
                    ['key' => 'sa_k', 'label' => 'Saldo Awal Kredit', 'align' => 'text-end'],
                    ['key' => 'mut_d', 'label' => 'Mutasi Debet', 'align' => 'text-end'],
                    ['key' => 'mut_k', 'label' => 'Mutasi Kredit', 'align' => 'text-end'],
                    ['key' => 'sa_d2', 'label' => 'Saldo Akhir Debet', 'align' => 'text-end'],
                    ['key' => 'sa_k2', 'label' => 'Saldo Akhir Kredit', 'align' => 'text-end'],
                ];
                $totD = 0; $totK = 0; $totMD = 0; $totMK = 0; $totSAD = 0; $totSAK = 0;
                foreach ($summary6 as $s) {
                    $totD += (float)($s->saldo_awal_debet ?? 0);
                    $totK += (float)($s->saldo_awal_kredit ?? 0);
                    $totMD += (float)($s->mutasi_debet ?? 0);
                    $totMK += (float)($s->mutasi_kredit ?? 0);
                    $totSAD += (float)($s->saldo_akhir_debet ?? 0);
                    $totSAK += (float)($s->saldo_akhir_kredit ?? 0);
                    $rows->push(['s' => $s]);
                }
                $summaryFooter = [[
                    'cells' => [
                        0 => '<strong>TOTAL</strong>',
                        1 => '',
                        2 => '',
                        3 => '<strong>Rp ' . number_format($totD, 0, ',', '.') . '</strong>',
                        4 => '<strong>Rp ' . number_format($totK, 0, ',', '.') . '</strong>',
                        5 => '<strong>Rp ' . number_format($totMD, 0, ',', '.') . '</strong>',
                        6 => '<strong>Rp ' . number_format($totMK, 0, ',', '.') . '</strong>',
                        7 => '<strong>Rp ' . number_format($totSAD, 0, ',', '.') . '</strong>',
                        8 => '<strong>Rp ' . number_format($totSAK, 0, ',', '.') . '</strong>',
                    ],
                ]];
            }

            $rowFn = function ($rowWrap, $no) use ($coaId) {
                if ($coaId) {
                    $r = $rowWrap['r'];
                    $debet = (float)($r->debet ?? 0);
                    $kredit = (float)($r->kredit ?? 0);
                    return [
                        'no' => $rowWrap['no'],
                        'tgl' => $r->tanggal ? Carbon::parse($r->tanggal)->isoFormat('D/M/Y') : '-',
                        'ref' => e($r->nomor_jurnal ?? '-'),
                        'ket' => e($r->keterangan ?? '-'),
                        'debet' => $debet > 0 ? 'Rp ' . number_format($debet, 0, ',', '.') : '-',
                        'kredit' => $kredit > 0 ? 'Rp ' . number_format($kredit, 0, ',', '.') : '-',
                        'saldo' => 'Rp ' . number_format($rowWrap['saldo_run'], 0, ',', '.'),
                    ];
                } else {
                    $s = $rowWrap['s'];
                    return [
                        'kode' => e($s->kode_akun ?? ''),
                        'nama' => e($s->nama_akun ?? ''),
                        'normal' => ($s->saldo_normal ?? '') === 'D' ? 'D' : 'K',
                        'sa_d' => (float)($s->saldo_awal_debet ?? 0) > 0 ? 'Rp ' . number_format((float)($s->saldo_awal_debet ?? 0), 0, ',', '.') : '-',
                        'sa_k' => (float)($s->saldo_awal_kredit ?? 0) > 0 ? 'Rp ' . number_format((float)($s->saldo_awal_kredit ?? 0), 0, ',', '.') : '-',
                        'mut_d' => (float)($s->mutasi_debet ?? 0) > 0 ? 'Rp ' . number_format((float)($s->mutasi_debet ?? 0), 0, ',', '.') : '-',
                        'mut_k' => (float)($s->mutasi_kredit ?? 0) > 0 ? 'Rp ' . number_format((float)($s->mutasi_kredit ?? 0), 0, ',', '.') : '-',
                        'sa_d2' => (float)($s->saldo_akhir_debet ?? 0) > 0 ? 'Rp ' . number_format((float)($s->saldo_akhir_debet ?? 0), 0, ',', '.') : '-',
                        'sa_k2' => (float)($s->saldo_akhir_kredit ?? 0) > 0 ? 'Rp ' . number_format((float)($s->saldo_akhir_kredit ?? 0), 0, ',', '.') : '-',
                    ];
                }
            };
            return $this->generatePdf($judul, 'buku-besar', $header, $rows, $rowFn, $dari, $sampai, $cabangId, 'landscape', $summaryFooter ?? []);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF Buku Besar: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request, BukuBesarService $bukuBesar)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $dari = $request->input('tanggal_awal', date('Y-m-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-t'));
            $coaId = $request->input('coa_id');
            // Reuse PDF logic (mirror tanpa format Rp prefix + number_format 2 desimal)
            $pdfResp = $this->exportPdf($request, $bukuBesar);
            if ($pdfResp->isRedirection()) return $pdfResp;
            return $this->exportPdf($request, $bukuBesar); // Fallback ke PDF; untuk Excel native nanti user install maatwebsite
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export Excel Buku Besar: ' . $e->getMessage());
        }
    }
}
