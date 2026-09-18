<?php

namespace App\Http\Controllers;

use App\Models\JurnalUmumHeader;
use App\Traits\WithModuleFilter;
use App\Traits\WithExportable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class JurnalHarianController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $dari = $request->input('tanggal_awal', date('Y-m-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-t'));

            $query = JurnalUmumHeader::with(['details.coa', 'cabang'])
                ->where('status', 'diposting')
                ->whereBetween('tanggal_jurnal', [$dari, $sampai]);
            $query = $this->applyModuleFilter($query, $request);
            $listHarian = $query->orderBy('tanggal_jurnal', 'asc')->orderBy('nomor_jurnal', 'asc')->get();

            $grouped = [];
            $totalAllDebet = 0;
            $totalAllKredit = 0;
            foreach ($listHarian as $h) {
                $tgl = $h->tanggal_jurnal->toDateString();
                if (!isset($grouped[$tgl])) {
                    $grouped[$tgl] = [
                        'tanggal' => $tgl,
                        'headers' => [],
                        'total_debet' => 0,
                        'total_kredit' => 0,
                        'jumlah_voucher' => 0,
                    ];
                }
                $td = 0;
                $tk = 0;
                foreach ($h->details as $d) {
                    $td += (float)$d->debet;
                    $tk += (float)$d->kredit;
                }
                $grouped[$tgl]['headers'][] = $h;
                $grouped[$tgl]['total_debet'] += $td;
                $grouped[$tgl]['total_kredit'] += $tk;
                $grouped[$tgl]['jumlah_voucher']++;
                $totalAllDebet += $td;
                $totalAllKredit += $tk;
            }
            foreach ($grouped as &$g) {
                $g['total_debet'] = round($g['total_debet'], 2);
                $g['total_kredit'] = round($g['total_kredit'], 2);
            }
            unset($g);

            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $cabangId,
                'tanggal_awal' => $dari,
                'tanggal_akhir' => $sampai,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('jurnal-harian.index');
            $exportPdfUrl = route('jurnal-harian.exportPdf');
            $exportExcelUrl = route('jurnal-harian.exportExcel');
            return view("modules.jurnal-harian.index", compact(
                'grouped',
                'totalAllDebet',
                'totalAllKredit',
                'dari',
                'sampai',
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
            return back()->with('error', 'Gagal memuat jurnal harian: ' . $e->getMessage());
        }
    }

    // ==================== EXPORT ====================
    public function exportPdf(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
            $cabangId = $request->input('cabang_id', ($isAdmin ? null : ($user?->cabang_id ?? null)));
            $dari = $request->input('tanggal_awal', date('Y-m-01'));
            $sampai = $request->input('tanggal_akhir', date('Y-m-t'));

            $query = JurnalUmumHeader::with(['details.coa', 'cabang'])
                ->where('status', 'diposting')
                ->whereBetween('tanggal_jurnal', [$dari, $sampai]);
            $query = $this->applyModuleFilter($query, $request);
            $listHarian = $query->orderBy('tanggal_jurnal', 'asc')->orderBy('nomor_jurnal', 'asc')->get();

            $header = [
                ['key' => 'tgl', 'label' => 'Tanggal', 'align' => 'text-center'],
                ['key' => 'no', 'label' => 'No Voucher', 'align' => 'text-start'],
                ['key' => 'ket', 'label' => 'Keterangan / Uraian', 'align' => 'text-start'],
                ['key' => 'reff', 'label' => 'Ref', 'align' => 'text-start'],
                ['key' => 'debet', 'label' => 'Debet (Rp)', 'align' => 'text-end'],
                ['key' => 'kredit', 'label' => 'Kredit (Rp)', 'align' => 'text-end'],
            ];
            $rows = collect();
            $totD = 0; $totK = 0;
            $noV = 1;
            foreach ($listHarian as $h) {
                $tanggalLabel = Carbon::parse($h->tanggal_jurnal)->locale('id')->isoFormat('dddd, D MMMM Y');
                $first = true;
                foreach ($h->details as $d) {
                    $debet = (float)$d->debet;
                    $kredit = (float)$d->kredit;
                    $totD += $debet;
                    $totK += $kredit;
                    $rows->push([
                        'tgl' => $first ? $tanggalLabel : '',
                        'no' => $first ? e($h->nomor_jurnal) : '',
                        'ket' => ($first ? ('<strong>' . e($h->keterangan ?? ''). '</strong><br>') : '&nbsp;&nbsp;&nbsp;&nbsp;') . e($d->coa?->kode_akun ?? '') . ' ' . e($d->coa?->nama_akun ?? ''),
                        'reff' => $first ? e($h->ref_tipe ?? '-') : '',
                        'debet' => $debet > 0 ? 'Rp ' . number_format($debet, 0, ',', '.') : '',
                        'kredit' => $kredit > 0 ? 'Rp ' . number_format($kredit, 0, ',', '.') : '',
                        '__is_summary' => false,
                    ]);
                    $first = false;
                }
                $rows->push(['tgl' => '', 'no' => '', 'ket' => '', 'reff' => '', 'debet' => '', 'kredit' => '', '__is_summary' => false]);
                $noV++;
            }
            $summaryFooter = [[
                'cells' => [
                    0 => '',
                    1 => '',
                    2 => '<strong>TOTAL</strong>',
                    3 => '',
                    'debet' => '<strong>Rp ' . number_format($totD, 0, ',', '.') . '</strong>',
                    'kredit' => '<strong>Rp ' . number_format($totK, 0, ',', '.') . '</strong>',
                ],
            ]];
            $rowFn = fn($r, $n) => $r;
            return $this->generatePdf('Jurnal Harian', 'jurnal-harian', $header, $rows, $rowFn, $dari, $sampai, $cabangId, 'landscape', $summaryFooter);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF Jurnal Harian: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            return $this->exportPdf($request);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export Excel Jurnal Harian: ' . $e->getMessage());
        }
    }
}
