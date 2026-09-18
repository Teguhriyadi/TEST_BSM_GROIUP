<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class NeracaService
{
    public function __construct(private readonly BukuBesarService $bukuBesar)
    {
    }

    /**
     * Menyusun laporan Neraca per tanggal cutoff:
     * - Aset
     * - Kewajiban
     * - Ekuitas
     * + Laba Rugi Berjalan (otomatis)
     * Balance: Aset = Kewajiban + Ekuitas
     */
    public function getNeraca(Carbon $tanggalCutoff, ?string $cabangId = null): array
    {
        $akun = $this->bukuBesar->getMutasiAkunArray($tanggalCutoff, $cabangId);

        $kelompoks = ['aset' => [], 'kewajiban' => [], 'ekuitas' => [], 'pendapatan' => [], 'beban' => []];
        $parentGroup = [];
        foreach ($akun as $row) {
            if (in_array($row['kelompok'], array_keys($kelompoks), true)) {
                $kelompoks[$row['kelompok']][$row['id']] = $row;
                if ($row['parent_id']) {
                    $parentGroup[$row['parent_id']][] = $row['id'];
                }
            }
        }

        $hitungSubtotal = function (array $rows, array $parentMap) use (&$hitungSubtotal): float {
            $total = 0;
            $seen = [];
            foreach ($rows as $id => $row) {
                if (in_array($id, $seen, true)) {
                    continue;
                }
                if (!empty($parentMap[$id])) {
                    $anak = [];
                    foreach ($parentMap[$id] as $cid) {
                        if (isset($rows[$cid])) {
                            $anak[$cid] = $rows[$cid];
                        }
                    }
                    $sub = count($anak) ? $hitungSubtotal($anak, $parentMap) : $row['saldo'];
                    $total += $sub;
                    foreach (array_keys($anak) as $cid) {
                        $seen[] = $cid;
                    }
                } else {
                    $total += $row['saldo'];
                }
                $seen[] = $id;
            }
            return round($total, 2);
        };

        $totalAset = $hitungSubtotal($kelompoks['aset'], $parentGroup);
        $totalKewajiban = $hitungSubtotal($kelompoks['kewajiban'], $parentGroup);
        $totalEkuitasBefore = $hitungSubtotal($kelompoks['ekuitas'], $parentGroup);
        $totalPendapatan = $hitungSubtotal($kelompoks['pendapatan'], $parentGroup);
        $totalBeban = $hitungSubtotal($kelompoks['beban'], $parentGroup);

        $labaRugiBerjalan = round($totalPendapatan - $totalBeban, 2);
        $totalEkuitas = round($totalEkuitasBefore + $labaRugiBerjalan, 2);
        $totalKewajibanEkuitas = round($totalKewajiban + $totalEkuitas, 2);
        $selisih = round($totalAset - $totalKewajibanEkuitas, 2);

        return [
            'tanggal_cutoff' => $tanggalCutoff->toDateString(),
            'cabang_id' => $cabangId,
            'kelompok_aset' => $kelompoks['aset'],
            'kelompok_kewajiban' => $kelompoks['kewajiban'],
            'kelompok_ekuitas' => $kelompoks['ekuitas'],
            'kelompok_pendapatan' => $kelompoks['pendapatan'],
            'kelompok_beban' => $kelompoks['beban'],
            'ringkasan' => [
                'total_aset' => $totalAset,
                'total_kewajiban' => $totalKewajiban,
                'total_ekuitas_sebelum' => $totalEkuitasBefore,
                'laba_rugi_berjalan' => $labaRugiBerjalan,
                'total_ekuitas' => $totalEkuitas,
                'total_kewajiban_ekuitas' => $totalKewajibanEkuitas,
                'selisih_balance' => $selisih,
                'is_balance' => abs($selisih) < 0.001,
            ],
        ];
    }

    /**
     * Laba Rugi: detail akun 400 (Pendapatan) dan 500 (Beban) + hitung laba sebelum pajak, pajak (akun 520), laba bersih.
     */
    public function getLabaRugi(Carbon $dari, Carbon $sampai, ?string $cabangId = null): array
    {
        $periodeSample = $this->bukuBesar->getNeracaSaldo6Kolom($sampai, $cabangId);
        $rows = $periodeSample['rows'];

        $pendapatan = [];
        $beban = [];
        $pajak = [];
        $totalPendapatan = 0;
        $totalBebanOperasional = 0;
        $totalPajak = 0;

        foreach ($rows as $r) {
            $kode = $r['kode_akun'];
            $mutasiKredit = (float) $r['mut_kredit'];
            $mutasiDebet = (float) $r['mut_debet'];
            $net = 0;
            if ($r['saldo_normal'] === 'kredit') {
                $net = $mutasiKredit - $mutasiDebet;
            } else {
                $net = $mutasiDebet - $mutasiKredit;
            }
            $net = round($net, 2);
            if (str_starts_with($kode, '4')) {
                $pendapatan[] = array_merge($r, ['net_mutasi' => $net]);
                $totalPendapatan += $net;
            } elseif (str_starts_with($kode, '520')) {
                $pajak[] = array_merge($r, ['net_mutasi' => $net]);
                $totalPajak += $net;
            } elseif (str_starts_with($kode, '5')) {
                $beban[] = array_merge($r, ['net_mutasi' => $net]);
                $totalBebanOperasional += $net;
            }
        }

        $totalPendapatan = round($totalPendapatan, 2);
        $totalBebanOperasional = round($totalBebanOperasional, 2);
        $totalPajak = round($totalPajak, 2);
        $labaSebelumPajak = round($totalPendapatan - $totalBebanOperasional, 2);
        $labaBersih = round($labaSebelumPajak - $totalPajak, 2);

        return [
            'dari_tanggal' => $dari->toDateString(),
            'sampai_tanggal' => $sampai->toDateString(),
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            'pajak' => $pajak,
            'ringkasan' => [
                'total_pendapatan' => $totalPendapatan,
                'total_beban_operasional' => $totalBebanOperasional,
                'laba_sebelum_pajak' => $labaSebelumPajak,
                'total_pajak' => $totalPajak,
                'laba_bersih' => $labaBersih,
            ],
        ];
    }

    /**
     * Laporan SHU (Sisa Hasil Usaha) per tahun buku.
     */
    public function getLaporanSHU(int $tahun, ?string $cabangId = null): array
    {
        $dari = Carbon::create($tahun, 1, 1)->startOfDay();
        $sampai = Carbon::create($tahun, 12, 31)->endOfDay();
        $lr = $this->getLabaRugi($dari, $sampai, $cabangId);
        $labaBersih = $lr['ringkasan']['laba_bersih'];

        $shu = max($labaBersih, 0);
        $cadangan = round($shu * 0.5, 2);
        $shuAnggota = round($shu * 0.4, 2);
        $danaSosial = round($shu * 0.1, 2);
        $totalDistribusi = round($cadangan + $shuAnggota + $danaSosial, 2);
        $selisihDistribusi = round($shu - $totalDistribusi, 2);

        return [
            'tahun' => $tahun,
            'laba_rugi' => $lr,
            'distribusi' => [
                'shu_tersedia' => $shu,
                'cadangan' => $cadangan,
                'shu_anggota' => $shuAnggota,
                'dana_sosial' => $danaSosial,
                'total_distribusi' => $totalDistribusi,
                'selisih_distribusi' => $selisihDistribusi,
            ],
        ];
    }

    /**
     * Laporan Arus Kas sederhana: Aktivitas Operasional, Investasi, Pendanaan.
     */
    public function getArusKas(Carbon $dari, Carbon $sampai, ?string $cabangId = null): array
    {
        $rekap = $this->bukuBesar->getRekapHarianKasNonKas($dari, $sampai, $cabangId);
        $lr = $this->getLabaRugi($dari, $sampai, $cabangId);

        $operasionalKasMasuk = 0;
        $operasionalKasKeluar = 0;
        $investasiKasMasuk = 0;
        $investasiKasKeluar = 0;
        $pendanaanKasMasuk = 0;
        $pendanaanKasKeluar = 0;

        $listSimpanan = \App\Models\Coa::where('is_active', '1')->where('kode_akun', 'like', '210.%')->pluck('id')->map(fn($v)=>(string)$v)->all();
        $listPinjaman = \App\Models\Coa::where('is_active', '1')->where('kode_akun', 'like', '114.%')->pluck('id')->map(fn($v)=>(string)$v)->all();
        $listAktivaTetap = \App\Models\Coa::where('is_active', '1')->where(function($q){ $q->where('kode_akun','like','120.%')->orWhere('kode_akun','like','121.%'); })->pluck('id')->map(fn($v)=>(string)$v)->all();
        $listModal = \App\Models\Coa::where('is_active', '1')->where('kode_akun', 'like', '3%')->pluck('id')->map(fn($v)=>(string)$v)->all();
        $listHutangPJ = \App\Models\Coa::where('is_active', '1')->where('kode_akun', 'like', '220.%')->pluck('id')->map(fn($v)=>(string)$v)->all();

        $headers = \App\Models\JurnalUmumHeader::with(['details.coa'])
            ->where('status', 'diposting')
            ->whereBetween('tanggal_jurnal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($cabangId, fn($q)=>$q->where('cabang_id', $cabangId))
            ->get();

        foreach ($headers as $h) {
            foreach ($h->details as $d) {
                $cid = (string) $d->coa_id;
                $debet = (float) $d->debet;
                $kredit = (float) $d->kredit;
                if (in_array($cid, $listSimpanan, true) || in_array($cid, $listPinjaman, true)) {
                    $operasionalKasMasuk += $debet;
                    $operasionalKasKeluar += $kredit;
                } elseif (in_array($cid, $listAktivaTetap, true)) {
                    if ($debet > 0) $investasiKasKeluar += $debet;
                    if ($kredit > 0) $investasiKasMasuk += $kredit;
                } elseif (in_array($cid, $listModal, true) || in_array($cid, $listHutangPJ, true)) {
                    if ($kredit > 0) $pendanaanKasMasuk += $kredit;
                    if ($debet > 0) $pendanaanKasKeluar += $debet;
                } else {
                    $operasionalKasMasuk += $debet;
                    $operasionalKasKeluar += $kredit;
                }
            }
        }

        $ns6 = $this->bukuBesar->getNeracaSaldo6Kolom($sampai, $cabangId);
        $kasBankIds = \App\Models\Coa::where('is_active','1')->where(function($q){ $q->where('kode_akun','like','111.%')->orWhere('kode_akun','like','112.%'); })->where('level',2)->pluck('id')->map(fn($v)=>(string)$v)->all();
        $saldoAkhirKas = 0;
        $saldoAwalKas = 0;
        foreach ($ns6['rows'] as $r) {
            if (in_array($r['coa_id'], $kasBankIds, true)) {
                $saldoAwalKas += ($r['sa_debet'] - $r['sa_kredit']);
                $saldoAkhirKas += ($r['sk_debet'] - $r['sk_kredit']);
            }
        }
        $saldoAwalKas = round($saldoAwalKas, 2);
        $saldoAkhirKas = round($saldoAkhirKas, 2);

        $netOperasional = round($operasionalKasMasuk - $operasionalKasKeluar, 2);
        $netInvestasi = round($investasiKasMasuk - $investasiKasKeluar, 2);
        $netPendanaan = round($pendanaanKasMasuk - $pendanaanKasKeluar, 2);
        $kenaikanBersih = round($netOperasional + $netInvestasi + $netPendanaan, 2);

        return [
            'dari_tanggal' => $dari->toDateString(),
            'sampai_tanggal' => $sampai->toDateString(),
            'aktivitas_operasional' => [
                'kas_masuk' => round($operasionalKasMasuk, 2),
                'kas_keluar' => round($operasionalKasKeluar, 2),
                'net' => $netOperasional,
            ],
            'aktivitas_investasi' => [
                'kas_masuk' => round($investasiKasMasuk, 2),
                'kas_keluar' => round($investasiKasKeluar, 2),
                'net' => $netInvestasi,
            ],
            'aktivitas_pendanaan' => [
                'kas_masuk' => round($pendanaanKasMasuk, 2),
                'kas_keluar' => round($pendanaanKasKeluar, 2),
                'net' => $netPendanaan,
            ],
            'ringkasan' => [
                'kenaikan_bersih_kas' => $kenaikanBersih,
                'saldo_awal_kas' => $saldoAwalKas,
                'saldo_akhir_kas' => $saldoAkhirKas,
                'laba_bersih_periode' => $lr['ringkasan']['laba_bersih'],
            ],
        ];
    }
}
