<?php

namespace App\Http\Controllers;

use App\Http\Requests\JurnalUmum\JurnalUmumCreateRequest;
use App\Http\Requests\JurnalUmum\JurnalUmumUpdateRequest;
use App\Models\Coa;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Services\JurnalService;
use App\Traits\WithModuleFilter;
use App\Traits\WithExportable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class JurnalUmumController extends Controller
{
    use WithModuleFilter;
    use WithExportable;

    public function index(Request $request)
    {
        try {
            $query = JurnalUmumHeader::with(['cabang', 'dibuatOleh:id,nama', 'dipostingOleh:id,nama']);
            $tanggalAwal = $request->input('tanggal_awal', date('Y-m-01'));
            $tanggalAkhir = $request->input('tanggal_akhir', date('Y-m-t'));
            $status = $request->input('status', null);
            if ($tanggalAwal) {
                $query->whereDate('tanggal_jurnal', '>=', $tanggalAwal);
            }
            if ($tanggalAkhir) {
                $query->whereDate('tanggal_jurnal', '<=', $tanggalAkhir);
            }
            if ($status) {
                $query->where('status', $status);
            }
            $query = $this->applyModuleFilter($query, $request);
            $query->orderBy('tanggal_jurnal', 'desc')->orderBy('created_at', 'desc');
            $jurnal = $query->paginate(50)->withQueryString();
            $hideFilterBar = false;
            $cabangFilterList = $this->getCabangFilterList();
            $currentModuleFilter = [
                'cabang_id' => $request->input('cabang_id'),
                'tanggal_awal' => $tanggalAwal,
                'tanggal_akhir' => $tanggalAkhir,
            ];
            $isAnggotaFilter = false;
            $moduleFilterSummary = null;
            $filterFormAction = route('jurnal-umum.index');
            $exportPdfUrl = route('jurnal-umum.exportPdf');
            $exportExcelUrl = route('jurnal-umum.exportExcel');
            return view("modules.jurnal-umum.index", compact(
                'jurnal',
                'tanggalAwal',
                'tanggalAkhir',
                'status',
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
            return back()->with('error', 'Gagal memuat daftar jurnal: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $coaAktif = Coa::where('is_active', '1')->where('level', 2);
        if (!$isAdmin && $user?->cabang_id) {
            $coaAktif->where(function ($q) use ($user) {
                $q->whereNull('cabang_id')->orWhere('cabang_id', $user->cabang_id);
            });
        }
        $coaAktif = $coaAktif->orderBy('kode_akun', 'asc')->get();
        return view("modules.jurnal-umum.create", compact('coaAktif'));
    }

    public function store(JurnalUmumCreateRequest $request, JurnalService $jurnalSvc)
    {
        DB::beginTransaction();
        try {
            $valid = $request->validated();
            $user = Auth::user();
            $cabangId = $user?->cabang_id;
            if (!$cabangId) {
                throw new \RuntimeException('User tidak memiliki cabang, tidak bisa membuat jurnal.');
            }
            $header = JurnalUmumHeader::create([
                'cabang_id' => (string) $cabangId,
                'dibuat_oleh' => (string) $user->id,
                'tipe' => 'manual',
                'status' => 'draf',
                'tanggal_jurnal' => Carbon::parse($valid['tanggal_jurnal']),
                'ref_id' => null,
                'ref_tipe' => null,
                'keterangan' => $valid['keterangan'] ?? null,
                'total_debet' => 0,
                'total_kredit' => 0,
            ]);
            $totalDebet = 0;
            $totalKredit = 0;
            foreach ($valid['details'] as $r) {
                $d = (float) $r['debet'];
                $k = (float) $r['kredit'];
                JurnalUmumDetail::create([
                    'jurnal_umum_header_id' => (string) $header->id,
                    'coa_id' => (string) $r['coa_id'],
                    'keterangan' => $r['keterangan'] ?? null,
                    'debet' => round($d, 2),
                    'kredit' => round($k, 2),
                ]);
                $totalDebet += $d;
                $totalKredit += $k;
            }
            $header->forceFill([
                'total_debet' => round($totalDebet, 2),
                'total_kredit' => round($totalKredit, 2),
            ])->save();
            DB::commit();
            return redirect()->route('jurnal-umum.show', $header->id)
                ->with('success', 'Jurnal manual berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Jurnal manual gagal dibuat: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $header = JurnalUmumHeader::with(['details.coa', 'cabang', 'dibuatOleh:id,nama', 'dipostingOleh:id,nama'])
                ->findOrFail($id);
            $this->abortJikaAksesCabangTidakValid($header);
            return view("modules.jurnal-umum.show", compact('header'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat detail jurnal: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $header = JurnalUmumHeader::with('details')->findOrFail($id);
        $this->abortJikaAksesCabangTidakValid($header);
        if ($header->status === 'diposting') {
            return redirect()->route('jurnal-umum.show', $header->id)
                ->with('error', 'Jurnal yang sudah diposting tidak bisa diedit.');
        }
        $user = Auth::user();
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        $coaAktif = Coa::where('is_active', '1')->where('level', 2);
        if (!$isAdmin && $user?->cabang_id) {
            $coaAktif->where(function ($q) use ($user) {
                $q->whereNull('cabang_id')->orWhere('cabang_id', $user->cabang_id);
            });
        }
        $coaAktif = $coaAktif->orderBy('kode_akun', 'asc')->get();
        return view("modules.jurnal-umum.edit", compact('header', 'coaAktif'));
    }

    public function update(JurnalUmumUpdateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $header = JurnalUmumHeader::with('details')->findOrFail($id);
            $this->abortJikaAksesCabangTidakValid($header);
            if ($header->status === 'diposting') {
                throw new \RuntimeException('Jurnal sudah diposting, tidak bisa diedit.');
            }
            $valid = $request->validated();
            $header->update([
                'tanggal_jurnal' => Carbon::parse($valid['tanggal_jurnal']),
                'keterangan' => $valid['keterangan'] ?? $header->keterangan,
            ]);
            JurnalUmumDetail::where('jurnal_umum_header_id', $header->id)->delete();
            $totalDebet = 0;
            $totalKredit = 0;
            foreach ($valid['details'] as $r) {
                $d = (float) $r['debet'];
                $k = (float) $r['kredit'];
                JurnalUmumDetail::create([
                    'jurnal_umum_header_id' => (string) $header->id,
                    'coa_id' => (string) $r['coa_id'],
                    'keterangan' => $r['keterangan'] ?? null,
                    'debet' => round($d, 2),
                    'kredit' => round($k, 2),
                ]);
                $totalDebet += $d;
                $totalKredit += $k;
            }
            $header->forceFill([
                'total_debet' => round($totalDebet, 2),
                'total_kredit' => round($totalKredit, 2),
            ])->save();
            DB::commit();
            return redirect()->route('jurnal-umum.show', $header->id)
                ->with('success', 'Jurnal berhasil diubah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Jurnal gagal diubah: ' . $e->getMessage());
        }
    }

    public function posting(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $header = JurnalUmumHeader::findOrFail($id);
            $this->abortJikaAksesCabangTidakValid($header);
            $header->posting(Auth::check() ? (string) Auth::id() : null);
            DB::commit();
            return redirect()->route('jurnal-umum.show', $header->id)
                ->with('success', 'Jurnal berhasil diposting.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Jurnal gagal diposting: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $header = JurnalUmumHeader::findOrFail($id);
            $this->abortJikaAksesCabangTidakValid($header);
            if ($header->status === 'diposting' || $header->tipe === 'otomatis') {
                throw new \RuntimeException('Jurnal otomatis / yang sudah diposting tidak bisa dihapus.');
            }
            JurnalUmumDetail::where('jurnal_umum_header_id', $header->id)->delete();
            $header->delete();
            DB::commit();
            return redirect()->route('jurnal-umum.index')
                ->with('success', 'Jurnal draf berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Jurnal gagal dihapus: ' . $e->getMessage());
        }
    }

    private function abortJikaAksesCabangTidakValid(JurnalUmumHeader $header): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if ($user->role?->kode_role === 'ROL-ADM') {
            return;
        }
        if ((string) $header->cabang_id !== (string) $user->cabang_id) {
            abort(403);
        }
    }

    // ==================== EXPORT ====================
    private function getJuExportData(Request $request): array
    {
        $query = JurnalUmumHeader::with(['cabang', 'dibuatOleh:id,nama', 'dipostingOleh:id,nama', 'details']);
        $tanggalAwal = $request->input('tanggal_awal', date('Y-m-01'));
        $tanggalAkhir = $request->input('tanggal_akhir', date('Y-m-t'));
        $status = $request->input('status', null);
        $cabangId = $request->input('cabang_id', null);
        if ($tanggalAwal) $query->whereDate('tanggal_jurnal', '>=', $tanggalAwal);
        if ($tanggalAkhir) $query->whereDate('tanggal_jurnal', '<=', $tanggalAkhir);
        if ($status) $query->where('status', $status);
        $query = $this->applyModuleFilter($query, $request);
        $query->orderBy('tanggal_jurnal', 'asc')->orderBy('created_at', 'asc');
        return [$query->get(), $tanggalAwal, $tanggalAkhir, $cabangId];
    }

    private function getJuHeaderKolom(): array
    {
        return [
            ['key' => 'no', 'label' => 'No', 'align' => 'text-center'],
            ['key' => 'tgl', 'label' => 'Tanggal', 'align' => 'text-center'],
            ['key' => 'nomor', 'label' => 'No Jurnal', 'align' => 'text-start'],
            ['key' => 'kode', 'label' => 'Kode Akun', 'align' => 'text-start'],
            ['key' => 'akun', 'label' => 'Nama Akun', 'align' => 'text-start'],
            ['key' => 'ket', 'label' => 'Keterangan', 'align' => 'text-start'],
            ['key' => 'debet', 'label' => 'Debet', 'align' => 'text-end'],
            ['key' => 'kredit', 'label' => 'Kredit', 'align' => 'text-end'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'text-center'],
            ['key' => 'cabang', 'label' => 'Cabang', 'align' => 'text-start'],
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            [$headers, $tanggalAwal, $tanggalAkhir, $cabangId] = $this->getJuExportData($request);
            $flatRows = collect();
            $totalDebet = 0;
            $totalKredit = 0;
            foreach ($headers as $h) {
                foreach ($h->details as $idxD => $d) {
                    $debet = (float)$d->debet;
                    $kredit = (float)$d->kredit;
                    $totalDebet += $debet;
                    $totalKredit += $kredit;
                    $flatRows->push([
                        '_header' => $h,
                        '_detail' => $d,
                        '_idx_detail' => $idxD,
                    ]);
                }
            }
            $summaryFooter = [[
                'cells' => [
                    0 => '<strong>TOTAL</strong>',
                    1 => '',
                    2 => '',
                    3 => '',
                    4 => '',
                    5 => '',
                    'debet' => '<strong>Rp ' . number_format($totalDebet, 0, ',', '.') . '</strong>',
                    'kredit' => '<strong>Rp ' . number_format($totalKredit, 0, ',', '.') . '</strong>',
                ],
            ]];
            $rowFn = function ($row, $no) {
                $h = $row['_header'];
                $d = $row['_detail'];
                $idxD = $row['_idx_detail'];
                $labelStatus = match ($h->status) {
                    'draf' => 'Draf', 'diposting' => 'Diposting', 'dibatalkan' => 'Dibatalkan', default => ucfirst($h->status)
                };
                return [
                    'no' => $idxD === 0 ? $no : '',
                    'tgl' => $idxD === 0 ? Carbon::parse($h->tanggal_jurnal)->isoFormat('D/M/Y') : '',
                    'nomor' => $idxD === 0 ? e($h->nomor_jurnal) : '',
                    'kode' => e($d->coa?->kode_akun ?? '-'),
                    'akun' => e($d->coa?->nama_akun ?? '-'),
                    'ket' => $idxD === 0 ? e($h->keterangan ?? '-') : (e($d->keterangan ?? '')),
                    'debet' => (float)$d->debet > 0 ? 'Rp ' . number_format((float)$d->debet, 0, ',', '.') : '-',
                    'kredit' => (float)$d->kredit > 0 ? 'Rp ' . number_format((float)$d->kredit, 0, ',', '.') : '-',
                    'status' => $idxD === 0 ? $labelStatus : '',
                    'cabang' => $idxD === 0 ? ($h->cabang ? e($h->cabang->nama_cabang) : '-') : '',
                ];
            };
            return $this->generatePdf('Jurnal Umum', 'jurnal-umum', $this->getJuHeaderKolom(), $flatRows, $rowFn, $tanggalAwal, $tanggalAkhir, $cabangId, 'landscape', $summaryFooter);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF Jurnal Umum: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            [$headers, $tanggalAwal, $tanggalAkhir, $cabangId] = $this->getJuExportData($request);
            $flatRows = collect();
            $totalDebet = 0;
            $totalKredit = 0;
            foreach ($headers as $h) {
                foreach ($h->details as $idxD => $d) {
                    $debet = (float)$d->debet;
                    $kredit = (float)$d->kredit;
                    $totalDebet += $debet;
                    $totalKredit += $kredit;
                    $flatRows->push(['_header' => $h, '_detail' => $d, '_idx_detail' => $idxD]);
                }
            }
            $summaryFooter = [[
                'cells' => [
                    0 => 'TOTAL',
                    6 => number_format($totalDebet, 2, ',', '.'),
                    7 => number_format($totalKredit, 2, ',', '.'),
                ],
            ]];
            $rowFn = function ($row, $no) {
                $h = $row['_header'];
                $d = $row['_detail'];
                $idxD = $row['_idx_detail'];
                $labelStatus = match ($h->status) {
                    'draf' => 'Draf', 'diposting' => 'Diposting', 'dibatalkan' => 'Dibatalkan', default => ucfirst($h->status)
                };
                return [
                    'no' => $idxD === 0 ? $no : '',
                    'tgl' => $idxD === 0 ? Carbon::parse($h->tanggal_jurnal)->isoFormat('D/M/Y') : '',
                    'nomor' => $idxD === 0 ? e($h->nomor_jurnal) : '',
                    'kode' => e($d->coa?->kode_akun ?? '-'),
                    'akun' => e($d->coa?->nama_akun ?? '-'),
                    'ket' => $idxD === 0 ? e($h->keterangan ?? '-') : e($d->keterangan ?? ''),
                    'debet' => number_format((float)$d->debet, 2, ',', '.'),
                    'kredit' => number_format((float)$d->kredit, 2, ',', '.'),
                    'status' => $idxD === 0 ? $labelStatus : '',
                    'cabang' => $idxD === 0 ? ($h->cabang ? e($h->cabang->nama_cabang) : '-') : '',
                ];
            };
            return $this->generateExcel('Jurnal Umum', 'jurnal-umum', $this->getJuHeaderKolom(), $flatRows, $rowFn, $tanggalAwal, $tanggalAkhir, $cabangId, $summaryFooter);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export Excel Jurnal Umum: ' . $e->getMessage());
        }
    }
}
