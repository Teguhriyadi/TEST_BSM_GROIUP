<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Traits\WithModuleFilter;
use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Cabang;
use App\Models\PembayaranAngsuran;
use App\Models\Pinjaman;
use App\Models\Simpanan;

class LaporanController extends Controller
{
    use WithModuleFilter;

    // ============== LAPORAN ANGGOTA ==============
    public function anggotaIndex(Request $request)
    {
        try {
            $resolved = $this->resolveFilter($request, 'laporan_anggota', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'created_at',
                'force_scope_cabang_user' => true,
            ]);

            $filterView = $this->buildViewFilterVars($resolved);
            $tanggal_awal = $resolved['filter']['tanggal_awal'];
            $tanggal_akhir = $resolved['filter']['tanggal_akhir'];
            $forcedCabang = $this->userForcedCabangId();
            $cabang_id = $forcedCabang ?? ($resolved['filter']['cabang_id'] ?? null);

            $query = Anggota::with(['cabang', 'user']);
            if (! empty($cabang_id)) {
                $query->where('cabang_id', $cabang_id);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('created_at', [$tanggal_awal . ' 00:00:00', $tanggal_akhir . ' 23:59:59']);
            }
            $data = $query->orderBy('created_at', 'desc')->get();

            $jenisLaporan = 'anggota';
            $judulLaporan = 'Laporan Data Anggota';
            $filterFormAction = route('laporan.anggota');
            $exportAction = route('laporan.anggota.exportPdf');

            return view('modules.laporan.index', array_merge(compact(
                'data',
                'tanggal_awal',
                'tanggal_akhir',
                'cabang_id',
                'jenisLaporan',
                'judulLaporan',
                'filterFormAction',
                'exportAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat laporan anggota: ' . $e->getMessage());
        }
    }

    public function anggotaExportPdf(Request $request)
    {
        try {
            [$tanggal_awal, $tanggal_akhir, $cabang_id] = $this->getExportDateRangeCabang($request);

            $query = Anggota::with(['cabang', 'user']);
            if (! empty($cabang_id)) {
                $query->where('cabang_id', $cabang_id);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('created_at', [$tanggal_awal . ' 00:00:00', $tanggal_akhir . ' 23:59:59']);
            }
            $data = $query->orderBy('created_at', 'desc')->get();

            return $this->renderPdf('anggota', 'Laporan Data Anggota', $data, $tanggal_awal, $tanggal_akhir, $cabang_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF laporan anggota: ' . $e->getMessage());
        }
    }

    // ============== LAPORAN VERIFIKASI PENDAFTARAN ==============
    public function verifikasiIndex(Request $request)
    {
        try {
            $statusOptions = [
                ['value' => 'menunggu_verifikasi', 'label' => 'Menunggu Verifikasi'],
                ['value' => 'disetujui', 'label' => 'Disetujui'],
                ['value' => 'ditolak', 'label' => 'Ditolak'],
            ];
            $resolved = $this->resolveFilter($request, 'laporan_verifikasi', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'created_at',
                'force_scope_cabang_user' => true,
                'custom_filters' => [
                    'status_pendaftaran' => [
                        'key' => 'status_pendaftaran',
                        'label' => 'Status Pendaftaran',
                        'options' => $statusOptions,
                    ],
                ],
            ]);

            $filterView = $this->buildViewFilterVars($resolved);
            $tanggal_awal = $resolved['filter']['tanggal_awal'];
            $tanggal_akhir = $resolved['filter']['tanggal_akhir'];
            $forcedCabang = $this->userForcedCabangId();
            $cabang_id = $forcedCabang ?? ($resolved['filter']['cabang_id'] ?? null);
            $status_pendaftaran = $resolved['filter']['status_pendaftaran'] ?? null;

            $query = Anggota::with(['cabang', 'user'])->whereNotNull('status_pendaftaran');
            if (! empty($cabang_id)) {
                $query->where('cabang_id', $cabang_id);
            }
            if (! empty($status_pendaftaran)) {
                $query->where('status_pendaftaran', $status_pendaftaran);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('created_at', [$tanggal_awal . ' 00:00:00', $tanggal_akhir . ' 23:59:59']);
            }
            $data = $query->orderBy('created_at', 'desc')->get();

            $jenisLaporan = 'verifikasi';
            $judulLaporan = 'Laporan Verifikasi Pendaftaran Anggota';
            $filterFormAction = route('laporan.verifikasi');
            $exportAction = route('laporan.verifikasi.exportPdf');

            return view('modules.laporan.index', array_merge(compact(
                'data',
                'tanggal_awal',
                'tanggal_akhir',
                'cabang_id',
                'status_pendaftaran',
                'jenisLaporan',
                'judulLaporan',
                'filterFormAction',
                'exportAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat laporan verifikasi: ' . $e->getMessage());
        }
    }

    public function verifikasiExportPdf(Request $request)
    {
        try {
            [$tanggal_awal, $tanggal_akhir, $cabang_id] = $this->getExportDateRangeCabang($request);
            $status_pendaftaran = $request->input('status_pendaftaran') ?: null;

            $query = Anggota::with(['cabang', 'user'])->whereNotNull('status_pendaftaran');
            if (! empty($cabang_id)) {
                $query->where('cabang_id', $cabang_id);
            }
            if (! empty($status_pendaftaran)) {
                $query->where('status_pendaftaran', $status_pendaftaran);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('created_at', [$tanggal_awal . ' 00:00:00', $tanggal_akhir . ' 23:59:59']);
            }
            $data = $query->orderBy('created_at', 'desc')->get();

            return $this->renderPdf('verifikasi', 'Laporan Verifikasi Pendaftaran', $data, $tanggal_awal, $tanggal_akhir, $cabang_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF laporan verifikasi: ' . $e->getMessage());
        }
    }

    // ============== LAPORAN SIMPANAN ==============
    public function simpananIndex(Request $request)
    {
        try {
            $resolved = $this->resolveFilter($request, 'laporan_simpanan', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'tanggal',
                'force_scope_cabang_user' => true,
            ]);

            $filterView = $this->buildViewFilterVars($resolved);
            $tanggal_awal = $resolved['filter']['tanggal_awal'];
            $tanggal_akhir = $resolved['filter']['tanggal_akhir'];
            $forcedCabang = $this->userForcedCabangId();
            $cabang_id = $forcedCabang ?? ($resolved['filter']['cabang_id'] ?? null);

            $query = Simpanan::with(['anggota', 'cabang', 'jenisSimpanan']);
            if (! empty($cabang_id)) {
                $query->where('cabang_id', $cabang_id);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir]);
            }
            $data = $query->orderBy('tanggal', 'desc')->get();

            $jenisLaporan = 'simpanan';
            $judulLaporan = 'Laporan Transaksi Simpanan';
            $filterFormAction = route('laporan.simpanan');
            $exportAction = route('laporan.simpanan.exportPdf');

            return view('modules.laporan.index', array_merge(compact(
                'data',
                'tanggal_awal',
                'tanggal_akhir',
                'cabang_id',
                'jenisLaporan',
                'judulLaporan',
                'filterFormAction',
                'exportAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat laporan simpanan: ' . $e->getMessage());
        }
    }

    public function simpananExportPdf(Request $request)
    {
        try {
            [$tanggal_awal, $tanggal_akhir, $cabang_id] = $this->getExportDateRangeCabang($request);

            $query = Simpanan::with(['anggota', 'cabang', 'jenisSimpanan']);
            if (! empty($cabang_id)) {
                $query->where('cabang_id', $cabang_id);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('tanggal', [$tanggal_awal, $tanggal_akhir]);
            }
            $data = $query->orderBy('tanggal', 'desc')->get();

            return $this->renderPdf('simpanan', 'Laporan Simpanan', $data, $tanggal_awal, $tanggal_akhir, $cabang_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF laporan simpanan: ' . $e->getMessage());
        }
    }

    // ============== LAPORAN PINJAMAN ==============
    public function pinjamanIndex(Request $request)
    {
        try {
            $statusOptions = [
                ['value' => 'diajukan', 'label' => 'Diajukan'],
                ['value' => 'diverifikasi', 'label' => 'Diverifikasi'],
                ['value' => 'disetujui', 'label' => 'Disetujui'],
                ['value' => 'dicairkan', 'label' => 'Dicairkan'],
                ['value' => 'berjalan', 'label' => 'Berjalan'],
                ['value' => 'lunas', 'label' => 'Lunas'],
                ['value' => 'ditolak', 'label' => 'Ditolak'],
            ];
            $resolved = $this->resolveFilter($request, 'laporan_pinjaman', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'tgl_pengajuan',
                'force_scope_cabang_user' => true,
                'custom_filters' => [
                    'status' => [
                        'key' => 'status',
                        'label' => 'Status Pinjaman',
                        'options' => $statusOptions,
                    ],
                ],
            ]);

            $filterView = $this->buildViewFilterVars($resolved);
            $tanggal_awal = $resolved['filter']['tanggal_awal'];
            $tanggal_akhir = $resolved['filter']['tanggal_akhir'];
            $forcedCabang = $this->userForcedCabangId();
            $cabang_id = $forcedCabang ?? ($resolved['filter']['cabang_id'] ?? null);
            $status = $resolved['filter']['status'] ?? null;

            $query = Pinjaman::with(['anggota', 'cabang', 'jenisPinjaman']);
            if (! empty($cabang_id)) {
                $query->where('cabang_id', $cabang_id);
            }
            if (! empty($status)) {
                $query->where('status', $status);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('tgl_pengajuan', [$tanggal_awal, $tanggal_akhir]);
            }
            $data = $query->orderBy('tgl_pengajuan', 'desc')->get();

            $jenisLaporan = 'pinjaman';
            $judulLaporan = 'Laporan Pengajuan Pinjaman';
            $filterFormAction = route('laporan.pinjaman');
            $exportAction = route('laporan.pinjaman.exportPdf');

            return view('modules.laporan.index', array_merge(compact(
                'data',
                'tanggal_awal',
                'tanggal_akhir',
                'cabang_id',
                'status',
                'jenisLaporan',
                'judulLaporan',
                'filterFormAction',
                'exportAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat laporan pinjaman: ' . $e->getMessage());
        }
    }

    public function pinjamanExportPdf(Request $request)
    {
        try {
            [$tanggal_awal, $tanggal_akhir, $cabang_id] = $this->getExportDateRangeCabang($request);
            $status = $request->input('status') ?: null;

            $query = Pinjaman::with(['anggota', 'cabang', 'jenisPinjaman']);
            if (! empty($cabang_id)) {
                $query->where('cabang_id', $cabang_id);
            }
            if (! empty($status)) {
                $query->where('status', $status);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('tgl_pengajuan', [$tanggal_awal, $tanggal_akhir]);
            }
            $data = $query->orderBy('tgl_pengajuan', 'desc')->get();

            return $this->renderPdf('pinjaman', 'Laporan Pinjaman', $data, $tanggal_awal, $tanggal_akhir, $cabang_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF laporan pinjaman: ' . $e->getMessage());
        }
    }

    // ============== LAPORAN ANGSURAN ==============
    public function angsuranIndex(Request $request)
    {
        try {
            $statusOptions = [
                ['value' => 'belum_lunas', 'label' => 'Belum Lunas'],
                ['value' => 'sebagian_dibayar', 'label' => 'Sebagian Dibayar'],
                ['value' => 'lunas', 'label' => 'Lunas'],
                ['value' => 'lewat_jatuh_tempo', 'label' => 'Lewat Jatuh Tempo'],
            ];
            $resolved = $this->resolveFilter($request, 'laporan_angsuran', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'tanggal_jatuh_tempo',
                'force_scope_cabang_user' => true,
                'custom_filters' => [
                    'status' => [
                        'key' => 'status',
                        'label' => 'Status Angsuran',
                        'options' => $statusOptions,
                    ],
                ],
            ]);

            $filterView = $this->buildViewFilterVars($resolved);
            $tanggal_awal = $resolved['filter']['tanggal_awal'];
            $tanggal_akhir = $resolved['filter']['tanggal_akhir'];
            $forcedCabang = $this->userForcedCabangId();
            $cabang_id = $forcedCabang ?? ($resolved['filter']['cabang_id'] ?? null);
            $status = $resolved['filter']['status'] ?? null;

            $query = Angsuran::with(['pinjaman', 'pinjaman.anggota', 'pinjaman.cabang', 'pembayaranAngsuran']);
            if (! empty($cabang_id)) {
                $query->whereHas('pinjaman', function ($qq) use ($cabang_id) {
                    $qq->where('cabang_id', $cabang_id);
                });
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('tanggal_jatuh_tempo', [$tanggal_awal, $tanggal_akhir]);
            }
            $base = $query->orderBy('tanggal_jatuh_tempo', 'desc')->get();
            $data = $base->map(function ($item) {
                $totalBayar = (float) ($item->pembayaranAngsuran?->sum('jumlah_bayar') ?? 0);
                $nominal = (float) ($item->nominal ?? 0) + (float) ($item->denda ?? 0);
                $today = Carbon::now()->toDateString();
                if ($totalBayar >= $nominal && $nominal > 0) {
                    $item->status_computed = 'lunas';
                } elseif ($totalBayar > 0) {
                    $item->status_computed = 'sebagian_dibayar';
                } elseif ($item->tanggal_jatuh_tempo < $today) {
                    $item->status_computed = 'lewat_jatuh_tempo';
                } else {
                    $item->status_computed = 'belum_lunas';
                }
                return $item;
            });
            if (! empty($status)) {
                $data = $data->filter(fn ($i) => $i->status_computed === $status)->values();
            }

            $jenisLaporan = 'angsuran';
            $judulLaporan = 'Laporan Jadwal Angsuran';
            $filterFormAction = route('laporan.angsuran');
            $exportAction = route('laporan.angsuran.exportPdf');

            return view('modules.laporan.index', array_merge(compact(
                'data',
                'tanggal_awal',
                'tanggal_akhir',
                'cabang_id',
                'status',
                'jenisLaporan',
                'judulLaporan',
                'filterFormAction',
                'exportAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat laporan angsuran: ' . $e->getMessage());
        }
    }

    public function angsuranExportPdf(Request $request)
    {
        try {
            [$tanggal_awal, $tanggal_akhir, $cabang_id] = $this->getExportDateRangeCabang($request);
            $status = $request->input('status') ?: null;

            $query = Angsuran::with(['pinjaman', 'pinjaman.anggota', 'pinjaman.cabang', 'pembayaranAngsuran']);
            if (! empty($cabang_id)) {
                $query->whereHas('pinjaman', function ($qq) use ($cabang_id) {
                    $qq->where('cabang_id', $cabang_id);
                });
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('tanggal_jatuh_tempo', [$tanggal_awal, $tanggal_akhir]);
            }
            $base = $query->orderBy('tanggal_jatuh_tempo', 'desc')->get();
            $data = $base->map(function ($item) {
                $totalBayar = (float) ($item->pembayaranAngsuran?->sum('jumlah_bayar') ?? 0);
                $nominal = (float) ($item->nominal ?? 0) + (float) ($item->denda ?? 0);
                $today = Carbon::now()->toDateString();
                if ($totalBayar >= $nominal && $nominal > 0) {
                    $item->status_computed = 'lunas';
                } elseif ($totalBayar > 0) {
                    $item->status_computed = 'sebagian_dibayar';
                } elseif ($item->tanggal_jatuh_tempo < $today) {
                    $item->status_computed = 'lewat_jatuh_tempo';
                } else {
                    $item->status_computed = 'belum_lunas';
                }
                return $item;
            });
            if (! empty($status)) {
                $data = $data->filter(fn ($i) => $i->status_computed === $status)->values();
            }

            return $this->renderPdf('angsuran', 'Laporan Angsuran', $data, $tanggal_awal, $tanggal_akhir, $cabang_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF laporan angsuran: ' . $e->getMessage());
        }
    }

    // ============== LAPORAN PEMBAYARAN ANGSURAN ==============
    public function pembayaranIndex(Request $request)
    {
        try {
            $metodeOptions = [
                ['value' => 'tunai', 'label' => 'Tunai'],
                ['value' => 'transfer', 'label' => 'Transfer'],
                ['value' => 'potong_gaji', 'label' => 'Potong Gaji'],
            ];
            $resolved = $this->resolveFilter($request, 'laporan_pembayaran', [
                'show_cabang' => true,
                'show_tanggal' => true,
                'tanggal_kolom_default' => 'tanggal_bayar',
                'force_scope_cabang_user' => true,
                'custom_filters' => [
                    'metode_pembayaran' => [
                        'key' => 'metode_pembayaran',
                        'label' => 'Metode Pembayaran',
                        'options' => $metodeOptions,
                    ],
                ],
            ]);

            $filterView = $this->buildViewFilterVars($resolved);
            $tanggal_awal = $resolved['filter']['tanggal_awal'];
            $tanggal_akhir = $resolved['filter']['tanggal_akhir'];
            $forcedCabang = $this->userForcedCabangId();
            $cabang_id = $forcedCabang ?? ($resolved['filter']['cabang_id'] ?? null);
            $metode = $resolved['filter']['metode_pembayaran'] ?? null;

            $query = PembayaranAngsuran::with([
                'angsuran',
                'angsuran.pinjaman',
                'angsuran.pinjaman.anggota',
                'angsuran.pinjaman.cabang',
                'dibayarOleh',
            ]);
            if (! empty($cabang_id)) {
                $query->whereHas('angsuran.pinjaman', function ($qq) use ($cabang_id) {
                    $qq->where('cabang_id', $cabang_id);
                });
            }
            if (! empty($metode)) {
                $query->where('metode_pembayaran', $metode);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('tanggal_bayar', [$tanggal_awal, $tanggal_akhir]);
            }
            $data = $query->orderBy('tanggal_bayar', 'desc')->get();

            $jenisLaporan = 'pembayaran';
            $judulLaporan = 'Laporan Pembayaran Angsuran';
            $filterFormAction = route('laporan.pembayaran');
            $exportAction = route('laporan.pembayaran.exportPdf');

            return view('modules.laporan.index', array_merge(compact(
                'data',
                'tanggal_awal',
                'tanggal_akhir',
                'cabang_id',
                'metode',
                'jenisLaporan',
                'judulLaporan',
                'filterFormAction',
                'exportAction'
            ), $filterView));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat laporan pembayaran: ' . $e->getMessage());
        }
    }

    public function pembayaranExportPdf(Request $request)
    {
        try {
            [$tanggal_awal, $tanggal_akhir, $cabang_id] = $this->getExportDateRangeCabang($request);
            $metode = $request->input('metode_pembayaran') ?: null;

            $query = PembayaranAngsuran::with([
                'angsuran',
                'angsuran.pinjaman',
                'angsuran.pinjaman.anggota',
                'angsuran.pinjaman.cabang',
                'dibayarOleh',
            ]);
            if (! empty($cabang_id)) {
                $query->whereHas('angsuran.pinjaman', function ($qq) use ($cabang_id) {
                    $qq->where('cabang_id', $cabang_id);
                });
            }
            if (! empty($metode)) {
                $query->where('metode_pembayaran', $metode);
            }
            if (! empty($tanggal_awal) && ! empty($tanggal_akhir)) {
                $query->whereBetween('tanggal_bayar', [$tanggal_awal, $tanggal_akhir]);
            }
            $data = $query->orderBy('tanggal_bayar', 'desc')->get();

            return $this->renderPdf('pembayaran', 'Laporan Pembayaran Angsuran', $data, $tanggal_awal, $tanggal_akhir, $cabang_id);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export PDF laporan pembayaran: ' . $e->getMessage());
        }
    }

    // ============== INTERNAL HELPER ==============
    private function getExportDateRangeCabang(Request $request): array
    {
        $tanggal_awal = $request->input('tanggal_awal') ?: Carbon::now()->startOfMonth()->toDateString();
        $tanggal_akhir = $request->input('tanggal_akhir') ?: Carbon::now()->endOfMonth()->toDateString();
        $cabang_id = $request->filled('cabang_id') && trim((string) $request->input('cabang_id')) !== ''
            ? $request->input('cabang_id')
            : null;

        $user = auth()->user();
        if ($user && ! $user->hasRole('Administrator') && ! $user->hasRole('Anggota') && ! empty($user->cabang_id)) {
            $cabang_id = $user->cabang_id;
        }

        return [$tanggal_awal, $tanggal_akhir, $cabang_id];
    }

    private function namaCabang(?string $cabangId): string
    {
        if (empty($cabangId)) return 'Seluruh Cabang';
        static $cache = [];
        if (isset($cache[$cabangId])) return $cache[$cabangId];
        $c = Cabang::where('id', $cabangId)->first(['kode_cabang', 'nama_cabang']);
        $cache[$cabangId] = $c ? ($c->kode_cabang . ' — ' . $c->nama_cabang) : '-';
        return $cache[$cabangId];
    }

    private function renderPdf(string $jenis, string $judul, $data, $tanggal_awal, $tanggal_akhir, $cabang_id)
    {
        $periode = Carbon::parse($tanggal_awal)->locale('id')->isoFormat('D MMMM Y') . ' s/d ' .
                   Carbon::parse($tanggal_akhir)->locale('id')->isoFormat('D MMMM Y');
        $infoCabang = $this->namaCabang($cabang_id);

        $pdf = Pdf::loadView('modules.laporan.pdf', compact(
            'jenis',
            'judul',
            'data',
            'tanggal_awal',
            'tanggal_akhir',
            'periode',
            'infoCabang',
            'cabang_id'
        ));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('laporan-' . $jenis . '-' . date('YmdHis') . '.pdf');
    }
}
