<?php

namespace App\Observers;

use App\Models\Angsuran;
use App\Models\PembayaranAngsuran;
use App\Services\JurnalService;
use Illuminate\Support\Facades\App;

class PembayaranAngsuranObserver
{
    public function creating(PembayaranAngsuran $bayar): void
    {
        try {
            if ($bayar->cabang_id === null || trim((string) $bayar->cabang_id) === '') {
                $angsuran = $bayar->angsuran;
                if ($angsuran && $angsuran->pinjaman) {
                    $bayar->cabang_id = (string) $angsuran->pinjaman->cabang_id;
                }
            }
            $total = (float) $bayar->jumlah_bayar;
            $pokok = (float) ($bayar->jumlah_pokok ?? 0);
            $bunga = (float) ($bayar->jumlah_bunga ?? 0);
            $denda = (float) ($bayar->jumlah_denda ?? 0);
            $admin = (float) ($bayar->biaya_administrasi ?? 0);
            $sudah = $pokok + $bunga + $denda + $admin;
            if ($sudah <= 0 && $total > 0) {
                $angsuran = Angsuran::with(['pinjaman'])->find($bayar->angsuran_id);
                if ($angsuran && $angsuran->pinjaman) {
                    $pinjaman = $angsuran->pinjaman;
                    $rateHarian = 0;
                    if ((int) $pinjaman->tenor > 0) {
                        $rateTahunan = (float) ($pinjaman->bunga ?: 0);
                        $rateHarian = $rateTahunan > 0 ? ($rateTahunan / 100) / 12 : 0;
                    }
                    $bungaPerBulan = (float) $angsuran->nominal * $rateHarian;
                    $pokokPorsi = (float) $angsuran->nominal - $bungaPerBulan;
                    if ($bungaPerBulan <= 0 && $pokokPorsi <= 0) {
                        $pokokPorsi = $total;
                        $bungaPerBulan = 0;
                    }
                    $ratio = $total / max(0.00001, (float) $angsuran->nominal);
                    if ($ratio <= 0) {
                        $ratio = 1;
                    }
                    $bunga = round(min($total, $bungaPerBulan * $ratio), 2);
                    $sisa = $total - $bunga;
                    if ($sisa < 0) {
                        $bunga = $total;
                        $sisa = 0;
                    }
                    $pokok = $sisa;
                    $bayar->jumlah_pokok = $pokok;
                    $bayar->jumlah_bunga = $bunga;
                } else {
                    $bayar->jumlah_pokok = $total;
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function created(PembayaranAngsuran $bayar): void
    {
        try {
            /** @var JurnalService $svc */
            $svc = App::make(JurnalService::class);
            $svc->createDariPembayaranAngsuran($bayar);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
