<?php

namespace App\Services;

use App\Models\CoaMapping;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use App\Models\Simpanan;
use App\Models\PembayaranAngsuran;
use App\Models\Pinjaman;
use App\Models\Angsuran;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JurnalService
{
    public const TIPE_SIMPANAN_SETORAN = 'simpanan_setoran';
    public const TIPE_SIMPANAN_PENARIKAN = 'simpanan_penarikan';
    public const TIPE_PINJAMAN_CAIR = 'pinjaman_cair';
    public const TIPE_ANGSURAN_POKOK = 'angsuran_pokok';
    public const TIPE_ANGSURAN_BUNGA = 'angsuran_bunga';
    public const TIPE_BIAYA_ADMINISTRASI = 'biaya_administrasi';
    public const TIPE_DENDA_TUNGGAKAN = 'denda_tunggakan';

    /**
     * Dapatkan pasangan akun debet + kredit dari mapping berdasarkan tipe transaksi & cabang.
     * Jika cabang_id tidak punya mapping spesifik, fallback ke global (cabang_id null).
     */
    public function getMappingAkun(string $tipeTransaksi, ?string $cabangId = null): array
    {
        $query = CoaMapping::with('coa')
            ->where('tipe_transaksi', $tipeTransaksi)
            ->where(function ($q) use ($cabangId) {
                $q->where('cabang_id', $cabangId)
                    ->orWhereNull('cabang_id');
            });
        $rows = $query->get();
        $byCabang = $rows->where('cabang_id', $cabangId);
        $final = $byCabang->isNotEmpty() ? $byCabang : $rows->whereNull('cabang_id');
        $debet = $final->where('posisi', 'debet')->first();
        $kredit = $final->where('posisi', 'kredit')->first();
        return [
            'debet' => $debet?->coa,
            'kredit' => $kredit?->coa,
        ];
    }

    /**
     * Buat Jurnal Umum Otomatis untuk transaksi Simpanan Setoran/Penarikan.
     */
    public function createDariSimpanan(Simpanan $simpanan): JurnalUmumHeader
    {
        $tipe = $simpanan->jenis === 'penarikan'
            ? self::TIPE_SIMPANAN_PENARIKAN
            : self::TIPE_SIMPANAN_SETORAN;
        $nominal = (float) $simpanan->nominal;
        $akun = $this->getMappingAkun($tipe, $simpanan->cabang_id);
        if (! $akun['debet'] || ! $akun['kredit']) {
            throw new \RuntimeException("Mapping COA untuk {$tipe} (cabang:{$simpanan->cabang_id}) belum lengkap.");
        }
        $anggotaNama = $simpanan->anggota?->nama ?? 'Anggota';
        $keterangan = match ($tipe) {
            self::TIPE_SIMPANAN_SETORAN => "Setoran Simpanan {$simpanan->jenisSimpanan?->nama_jenis} - {$anggotaNama}",
            self::TIPE_SIMPANAN_PENARIKAN => "Penarikan Simpanan {$simpanan->jenisSimpanan?->nama_jenis} - {$anggotaNama}",
            default => "Transaksi Simpanan - {$anggotaNama}",
        };
        return $this->buatJurnalBaru(
            cabangId: (string) $simpanan->cabang_id,
            tanggal: $simpanan->tanggal ? Carbon::parse($simpanan->tanggal) : Carbon::now(),
            tipe: 'otomatis',
            status: 'diposting',
            refId: (string) $simpanan->id,
            refTipe: Simpanan::class,
            keterangan: $keterangan,
            barisDetail: [
                ['coa_id' => (string) $akun['debet']->id, 'debet' => $nominal, 'kredit' => 0, 'keterangan' => $keterangan],
                ['coa_id' => (string) $akun['kredit']->id, 'debet' => 0, 'kredit' => $nominal, 'keterangan' => $keterangan],
            ],
        );
    }

    /**
     * Buat Jurnal Umum Otomatis ketika Pinjaman status berubah menjadi dicairkan.
     */
    public function createDariPinjamanCair(Pinjaman $pinjaman): JurnalUmumHeader
    {
        $akun = $this->getMappingAkun(self::TIPE_PINJAMAN_CAIR, $pinjaman->cabang_id);
        if (! $akun['debet'] || ! $akun['kredit']) {
            throw new \RuntimeException("Mapping COA untuk pinjaman_cair (cabang:{$pinjaman->cabang_id}) belum lengkap.");
        }
        $nominal = (float) $pinjaman->jumlah_pinjaman;
        $anggotaNama = $pinjaman->anggota?->nama ?? 'Anggota';
        $keterangan = "Pencairan Pinjaman {$pinjaman->nomor_pinjaman} - {$anggotaNama}";
        return $this->buatJurnalBaru(
            cabangId: (string) $pinjaman->cabang_id,
            tanggal: $pinjaman->tgl_cair ? Carbon::parse($pinjaman->tgl_cair) : Carbon::now(),
            tipe: 'otomatis',
            status: 'diposting',
            refId: (string) $pinjaman->id,
            refTipe: Pinjaman::class,
            keterangan: $keterangan,
            barisDetail: [
                ['coa_id' => (string) $akun['debet']->id, 'debet' => $nominal, 'kredit' => 0, 'keterangan' => $keterangan . ' (Piutang)'],
                ['coa_id' => (string) $akun['kredit']->id, 'debet' => 0, 'kredit' => $nominal, 'keterangan' => $keterangan . ' (Kas Keluar)'],
            ],
        );
    }

    /**
     * Jurnal dari Pembayaran Angsuran: pecah nominal jadi porsi Pokok + Bunga + (opsional) Denda + Biaya Admin.
     */
    public function createDariPembayaranAngsuran(PembayaranAngsuran $bayar): JurnalUmumHeader
    {
        $cabangId = (string) $bayar->cabang_id;
        $tanggal = $bayar->tanggal_bayar ? Carbon::parse($bayar->tanggal_bayar) : Carbon::now();
        $akunPokok = $this->getMappingAkun(self::TIPE_ANGSURAN_POKOK, $cabangId);
        $akunBunga = $this->getMappingAkun(self::TIPE_ANGSURAN_BUNGA, $cabangId);
        $akunDenda = $this->getMappingAkun(self::TIPE_DENDA_TUNGGAKAN, $cabangId);
        $akunAdmin = $this->getMappingAkun(self::TIPE_BIAYA_ADMINISTRASI, $cabangId);
        if (! $akunPokok['debet'] || ! $akunPokok['kredit']) {
            throw new \RuntimeException("Mapping COA untuk angsuran_pokok (cabang:{$cabangId}) belum lengkap.");
        }
        $pokok = (float) ($bayar->jumlah_pokok ?? 0);
        $bunga = (float) ($bayar->jumlah_bunga ?? 0);
        $denda = (float) ($bayar->jumlah_denda ?? 0);
        $admin = (float) ($bayar->biaya_administrasi ?? 0);
        if ($pokok <= 0 && $bunga <= 0 && $denda <= 0 && $admin <= 0) {
            $pokok = (float) $bayar->jumlah_bayar;
            $bunga = 0;
            $denda = 0;
            $admin = 0;
        }
        $nomorAngsuran = '';
        $anggotaNama = 'Anggota';
        try {
            $angsuran = $bayar->angsuran;
            if ($angsuran) {
                $nomorAngsuran = "Angsuran ke-{$angsuran->angsuran_ke}";
                $pinjaman = $angsuran->pinjaman;
                if ($pinjaman) {
                    $anggotaNama = $pinjaman->anggota?->nama ?? $anggotaNama;
                    $nomorAngsuran .= " {$pinjaman->nomor_pinjaman}";
                }
            }
        } catch (\Throwable $e) {
        }
        $keterangan = "Pembayaran {$nomorAngsuran} - {$anggotaNama}";
        $baris = [];
        $totalKas = 0;
        if ($pokok > 0) {
            $totalKas += $pokok;
            $baris[] = ['coa_id' => (string) $akunPokok['kredit']->id, 'debet' => 0, 'kredit' => $pokok, 'keterangan' => "{$keterangan} (Pokok)"];
        }
        if ($bunga > 0 && $akunBunga['debet'] && $akunBunga['kredit']) {
            $totalKas += $bunga;
            $baris[] = ['coa_id' => (string) $akunBunga['kredit']->id, 'debet' => 0, 'kredit' => $bunga, 'keterangan' => "{$keterangan} (Bunga)"];
        }
        if ($denda > 0 && $akunDenda['debet'] && $akunDenda['kredit']) {
            $totalKas += $denda;
            $baris[] = ['coa_id' => (string) $akunDenda['kredit']->id, 'debet' => 0, 'kredit' => $denda, 'keterangan' => "{$keterangan} (Denda)"];
        }
        if ($admin > 0 && $akunAdmin['debet'] && $akunAdmin['kredit']) {
            $totalKas += $admin;
            $baris[] = ['coa_id' => (string) $akunAdmin['kredit']->id, 'debet' => 0, 'kredit' => $admin, 'keterangan' => "{$keterangan} (Admin)"];
        }
        array_unshift($baris, [
            'coa_id' => (string) $akunPokok['debet']->id,
            'debet' => $totalKas,
            'kredit' => 0,
            'keterangan' => "{$keterangan} (Kas)",
        ]);
        return $this->buatJurnalBaru(
            cabangId: $cabangId,
            tanggal: $tanggal,
            tipe: 'otomatis',
            status: 'diposting',
            refId: (string) $bayar->id,
            refTipe: PembayaranAngsuran::class,
            keterangan: $keterangan,
            barisDetail: $baris,
        );
    }

    /**
     * Helper: buat Jurnal Header + Detail dalam 1 transaksi DB.
     *
     * @param array<int,array{coa_id:string,debet:float,kredit:float,keterangan?:string|null}> $barisDetail
     */
    public function buatJurnalBaru(
        string $cabangId,
        Carbon $tanggal,
        string $tipe,
        string $status,
        string $refId,
        string $refTipe,
        string $keterangan,
        array $barisDetail,
        ?string $dibuatOleh = null,
        bool $autoPosting = true,
    ): JurnalUmumHeader {
        $dibuatOleh = $dibuatOleh ?: (Auth::check() ? (string) Auth::id() : null);
        return DB::transaction(function () use ($cabangId, $tanggal, $tipe, $status, $refId, $refTipe, $keterangan, $barisDetail, $dibuatOleh, $autoPosting) {
            $header = JurnalUmumHeader::create([
                'cabang_id' => $cabangId,
                'dibuat_oleh' => $dibuatOleh,
                'tipe' => $tipe,
                'status' => $status === 'diposting' ? 'draf' : $status,
                'tanggal_jurnal' => $tanggal,
                'ref_id' => $refId,
                'ref_tipe' => $refTipe,
                'keterangan' => $keterangan,
                'total_debet' => 0,
                'total_kredit' => 0,
            ]);
            $totalDebet = 0;
            $totalKredit = 0;
            foreach ($barisDetail as $row) {
                $d = (float) ($row['debet'] ?? 0);
                $k = (float) ($row['kredit'] ?? 0);
                JurnalUmumDetail::create([
                    'jurnal_umum_header_id' => (string) $header->id,
                    'coa_id' => (string) $row['coa_id'],
                    'keterangan' => $row['keterangan'] ?? null,
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
            if (! $header->is_balance) {
                throw new \RuntimeException('Jurnal tidak balance. Total debet != total kredit.');
            }
            if (($status === 'diposting' || $autoPosting) && $header->status !== 'diposting') {
                $header->posting($dibuatOleh);
            }
            return $header->refresh();
        });
    }
}
