<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\CoaSaldoAwal;
use App\Models\JurnalUmumDetail;
use App\Models\JurnalUmumHeader;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BukuBesarService
{
    public function getPeriodeFromTanggal(Carbon $tanggal): string
    {
        return $tanggal->format('Y-m');
    }

    /**
     * Ambil saldo awal per akun pada periode + tanggal cutoff tertentu.
     * (Saldo Awal periode + mutasi dari awal periode s.d. tanggal mulai - 1 hari)
     */
    public function getSaldoAkhirPerAkun(Coa|string $coa, Carbon $sampaiTanggal, ?string $cabangId = null): float
    {
        $coaId = is_string($coa) ? $coa : (string) $coa->id;
        $coa = is_string($coa) ? Coa::find($coaId) : $coa;
        if (! $coa) {
            return 0;
        }
        $periode = $this->getPeriodeFromTanggal($sampaiTanggal);
        $saldoAwal = CoaSaldoAwal::where('coa_id', $coaId)
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->where('periode', $periode)
            ->first();
        $netAwal = 0;
        if ($saldoAwal) {
            $debet = (float) $saldoAwal->saldo_awal_debet;
            $kredit = (float) $saldoAwal->saldo_awal_kredit;
            $netAwal = match ($coa->saldo_normal) {
                'debet' => $debet - $kredit,
                'kredit' => $kredit - $debet,
                default => $debet - $kredit,
            };
        }
        $awalPeriode = Carbon::createFromFormat('Y-m-d', $sampaiTanggal->format('Y-m-01'))->startOfDay();
        $mutasi = JurnalUmumDetail::query()
            ->selectRaw('COALESCE(SUM(debet), 0) AS total_debet, COALESCE(SUM(kredit), 0) AS total_kredit')
            ->where('coa_id', $coaId)
            ->whereHas('header', function ($q) use ($awalPeriode, $sampaiTanggal, $cabangId) {
                $q->where('status', 'diposting')
                    ->whereBetween('tanggal_jurnal', [$awalPeriode->toDateString(), $sampaiTanggal->toDateString()])
                    ->when($cabangId, fn ($qq) => $qq->where('cabang_id', $cabangId));
            })
            ->first();
        $debetMutasi = (float) ($mutasi?->total_debet ?? 0);
        $kreditMutasi = (float) ($mutasi?->total_kredit ?? 0);
        $netMutasi = match ($coa->saldo_normal) {
            'debet' => $debetMutasi - $kreditMutasi,
            'kredit' => $kreditMutasi - $debetMutasi,
            default => $debetMutasi - $kreditMutasi,
        };
        return round($netAwal + $netMutasi, 2);
    }

    /**
     * Dapatkan baris Buku Besar (saldo berjalan) per akun dalam rentang tanggal.
     *
     * @return Collection<int, array{tanggal:mixed, nomor_jurnal:string|null, keterangan:string, debet:float, kredit:float, saldo:float, tipe:string|null, ref:string|null}>
     */
    public function getBukuBesarRows(Coa|string $coa, Carbon $dariTanggal, Carbon $sampaiTanggal, ?string $cabangId = null): Collection
    {
        $coaId = is_string($coa) ? $coa : (string) $coa->id;
        $coa = is_string($coa) ? Coa::find($coaId) : $coa;
        if (! $coa) {
            return collect();
        }
        $saldoSebelum = $this->getSaldoAkhirPerAkun($coa, $dariTanggal->copy()->subDay(), $cabangId);
        $detail = JurnalUmumDetail::with(['header' => fn ($q) => $q->select(['id', 'nomor_jurnal', 'tanggal_jurnal', 'status', 'ref_id', 'ref_tipe'])])
            ->where('coa_id', $coaId)
            ->whereHas('header', function ($q) use ($dariTanggal, $sampaiTanggal, $cabangId) {
                $q->where('status', 'diposting')
                    ->whereBetween('tanggal_jurnal', [$dariTanggal->toDateString(), $sampaiTanggal->toDateString()])
                    ->when($cabangId, fn ($qq) => $qq->where('cabang_id', $cabangId));
            })
            ->orderByRaw("(SELECT tanggal_jurnal FROM jurnal_umum_header WHERE jurnal_umum_header.id = jurnal_umum_detail.jurnal_umum_header_id) ASC")
            ->orderBy('id', 'asc')
            ->get();
        $rows = collect();
        $saldo = $saldoSebelum;
        $rows->push([
            'tanggal' => $dariTanggal->toDateString(),
            'nomor_jurnal' => null,
            'keterangan' => 'Saldo Awal',
            'debet' => 0,
            'kredit' => 0,
            'saldo' => round($saldo, 2),
            'tipe' => 'SALDO_AWAL',
            'ref' => null,
        ]);
        foreach ($detail as $row) {
            $d = (float) $row->debet;
            $k = (float) $row->kredit;
            $tambah = match ($coa->saldo_normal) {
                'debet' => $d - $k,
                'kredit' => $k - $d,
                default => $d - $k,
            };
            $saldo += $tambah;
            $rows->push([
                'tanggal' => $row->header?->tanggal_jurnal?->toDateString() ?? '',
                'nomor_jurnal' => $row->header?->nomor_jurnal ?? '',
                'keterangan' => $row->keterangan ?? ($row->header?->keterangan ?? ''),
                'debet' => round($d, 2),
                'kredit' => round($k, 2),
                'saldo' => round($saldo, 2),
                'tipe' => $row->header?->tipe ?? null,
                'ref' => $row->header?->ref_tipe ? class_basename($row->header->ref_tipe) . '/' . ($row->header->ref_id ?? '') : null,
            ]);
        }
        return $rows;
    }

    /**
     * Aggregasi total (debet/kredit) per akun per periode + cabang, untuk Neraca / Buku Besar daftar akun.
     */
    public function getMutasiAkunArray(Carbon $sampaiTanggal, ?string $cabangId = null, ?array $kelompokCoa = null): array
    {
        $periode = $this->getPeriodeFromTanggal($sampaiTanggal);
        $awalPeriode = Carbon::createFromFormat('Y-m-d', $sampaiTanggal->format('Y-m-01'))->startOfDay();
        $query = Coa::with(['parent'])->where('is_active', '1');
        if ($kelompokCoa) {
            $query->whereIn('kelompok', $kelompokCoa);
        }
        $listCoa = $query->orderBy('kode_akun')->get();
        $result = [];
        foreach ($listCoa as $coa) {
            $saldo = $this->getSaldoAkhirPerAkun($coa, $sampaiTanggal, $cabangId);
            $result[(string) $coa->id] = [
                'id' => (string) $coa->id,
                'kode_akun' => $coa->kode_akun,
                'nama_akun' => $coa->nama_akun,
                'level' => (int) $coa->level,
                'parent_id' => $coa->parent_id ? (string) $coa->parent_id : null,
                'kelompok' => $coa->kelompok,
                'posisi_laporan' => $coa->posisi_laporan,
                'saldo_normal' => $coa->saldo_normal,
                'saldo' => round($saldo, 2),
            ];
        }
        return $result;
    }

    /**
     * Neraca Saldo 6 Kolom per level-2 COA.
     *
     * @return array{rows:array, totals:array{sa_debet:float,sa_kredit:float,mut_debet:float,mut_kredit:float,sk_debet:float,sk_kredit:float}}
     */
    public function getNeracaSaldo6Kolom(Carbon $sampaiTanggal, ?string $cabangId = null): array
    {
        $periode = $this->getPeriodeFromTanggal($sampaiTanggal);
        $awalPeriode = Carbon::createFromFormat('Y-m-d', $sampaiTanggal->format('Y-m-01'))->startOfDay();
        $listCoa = Coa::with(['parent'])->where('is_active', '1')
            ->where('level', 2)
            ->orderBy('kode_akun', 'asc')
            ->get();

        $rows = [];
        $totals = [
            'sa_debet' => 0,
            'sa_kredit' => 0,
            'mut_debet' => 0,
            'mut_kredit' => 0,
            'sk_debet' => 0,
            'sk_kredit' => 0,
        ];

        foreach ($listCoa as $coa) {
            $coaId = (string) $coa->id;

            $saldoAwal = CoaSaldoAwal::where('coa_id', $coaId)
                ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
                ->where('periode', $periode)
                ->first();
            $saDebet = (float) ($saldoAwal?->saldo_awal_debet ?? 0);
            $saKredit = (float) ($saldoAwal?->saldo_awal_kredit ?? 0);

            $mutasi = JurnalUmumDetail::query()
                ->selectRaw('COALESCE(SUM(debet), 0) AS total_debet, COALESCE(SUM(kredit), 0) AS total_kredit')
                ->where('coa_id', $coaId)
                ->whereHas('header', function ($q) use ($awalPeriode, $sampaiTanggal, $cabangId) {
                    $q->where('status', 'diposting')
                        ->whereBetween('tanggal_jurnal', [$awalPeriode->toDateString(), $sampaiTanggal->toDateString()])
                        ->when($cabangId, fn ($qq) => $qq->where('cabang_id', $cabangId));
                })
                ->first();
            $mutDebet = (float) ($mutasi?->total_debet ?? 0);
            $mutKredit = (float) ($mutasi?->total_kredit ?? 0);

            $netAwal = match ($coa->saldo_normal) {
                'debet' => $saDebet - $saKredit,
                'kredit' => $saKredit - $saDebet,
                default => $saDebet - $saKredit,
            };
            $netMutasi = match ($coa->saldo_normal) {
                'debet' => $mutDebet - $mutKredit,
                'kredit' => $mutKredit - $mutDebet,
                default => $mutDebet - $mutKredit,
            };
            $netAkhir = round($netAwal + $netMutasi, 2);

            $skDebet = $coa->saldo_normal === 'debet' ? max($netAkhir, 0) : 0;
            $skKredit = $coa->saldo_normal === 'kredit' ? max($netAkhir, 0) : 0;
            if ($coa->saldo_normal === 'debet' && $netAkhir < 0) {
                $skKredit = abs($netAkhir);
            }
            if ($coa->saldo_normal === 'kredit' && $netAkhir < 0) {
                $skDebet = abs($netAkhir);
            }

            $rows[] = [
                'coa_id' => $coaId,
                'kode_akun' => $coa->kode_akun,
                'nama_akun' => $coa->nama_akun,
                'kelompok' => $coa->kelompok,
                'saldo_normal' => $coa->saldo_normal,
                'sa_debet' => round($saDebet, 2),
                'sa_kredit' => round($saKredit, 2),
                'mut_debet' => round($mutDebet, 2),
                'mut_kredit' => round($mutKredit, 2),
                'sk_debet' => round($skDebet, 2),
                'sk_kredit' => round($skKredit, 2),
            ];

            $totals['sa_debet'] += $saDebet;
            $totals['sa_kredit'] += $saKredit;
            $totals['mut_debet'] += $mutDebet;
            $totals['mut_kredit'] += $mutKredit;
            $totals['sk_debet'] += $skDebet;
            $totals['sk_kredit'] += $skKredit;
        }

        foreach ($totals as $k => $v) {
            $totals[$k] = round($v, 2);
        }

        return [
            'rows' => $rows,
            'totals' => $totals,
            'balance' => [
                'sa_balance' => abs($totals['sa_debet'] - $totals['sa_kredit']) < 0.01,
                'mut_balance' => abs($totals['mut_debet'] - $totals['mut_kredit']) < 0.01,
                'sk_balance' => abs($totals['sk_debet'] - $totals['sk_kredit']) < 0.01,
            ],
        ];
    }

    /**
     * Rekap harian Kas (berdasarkan akun 111/112) vs Non Kas per tanggal.
     */
    public function getRekapHarianKasNonKas(Carbon $dari, Carbon $sampai, ?string $cabangId = null): array
    {
        $listKas = Coa::where('is_active', '1')
            ->where(function ($q) {
                $q->where('kode_akun', 'like', '111.%')
                    ->orWhere('kode_akun', 'like', '112.%');
            })
            ->where('level', 2)
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->all();

        $headers = JurnalUmumHeader::with(['details.coa'])
            ->where('status', 'diposting')
            ->whereBetween('tanggal_jurnal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->orderBy('tanggal_jurnal', 'asc')
            ->get();

        $result = [];
        foreach ($headers as $h) {
            $tgl = $h->tanggal_jurnal->toDateString();
            if (!isset($result[$tgl])) {
                $result[$tgl] = [
                    'tanggal' => $tgl,
                    'kas_masuk' => 0,
                    'kas_keluar' => 0,
                    'non_kas_debet' => 0,
                    'non_kas_kredit' => 0,
                    'jml_transaksi' => 0,
                ];
            }
            $result[$tgl]['jml_transaksi']++;

            foreach ($h->details as $d) {
                $coaIdStr = (string) $d->coa_id;
                $debet = (float) $d->debet;
                $kredit = (float) $d->kredit;
                if (in_array($coaIdStr, $listKas, true)) {
                    $result[$tgl]['kas_masuk'] += $debet;
                    $result[$tgl]['kas_keluar'] += $kredit;
                } else {
                    $result[$tgl]['non_kas_debet'] += $debet;
                    $result[$tgl]['non_kas_kredit'] += $kredit;
                }
            }
        }

        $total = ['kas_masuk' => 0, 'kas_keluar' => 0, 'non_kas_debet' => 0, 'non_kas_kredit' => 0, 'jml_transaksi' => 0];
        foreach ($result as $t => &$r) {
            foreach (array_keys($total) as $kk) {
                $r[$kk] = round($r[$kk], 2);
                $total[$kk] += $r[$kk];
            }
        }
        unset($r);
        foreach (array_keys($total) as $kk) {
            $total[$kk] = round($total[$kk], 2);
        }

        return [
            'rows' => array_values($result),
            'total' => $total,
            'akun_kas_ids' => $listKas,
        ];
    }

    /**
     * Buku Kas Harian: saldo berjalan per akun Kas/Bank (111, 112) dalam rentang tanggal.
     */
    public function getBukuKasHarianSaldoBerjalan(array $akunKasIds, Carbon $dari, Carbon $sampai, ?string $cabangId = null): array
    {
        $coaList = Coa::whereIn('id', $akunKasIds)->orderBy('kode_akun')->get();
        $perAkun = [];
        $totalAll = ['saldo_awal' => 0, 'masuk' => 0, 'keluar' => 0, 'saldo_akhir' => 0];
        foreach ($coaList as $coa) {
            $saldoAwal = $this->getSaldoAkhirPerAkun($coa, $dari->copy()->subDay(), $cabangId);
            $rows = $this->getBukuBesarRows($coa, $dari, $sampai, $cabangId);
            $masuk = 0;
            $keluar = 0;
            foreach ($rows as $r) {
                if ($r['tipe'] === 'SALDO_AWAL') {
                    continue;
                }
                $masuk += (float) $r['debet'];
                $keluar += (float) $r['kredit'];
            }
            $saldoAkhir = $this->getSaldoAkhirPerAkun($coa, $sampai, $cabangId);
            $perAkun[] = [
                'coa' => $coa,
                'saldo_awal' => round($saldoAwal, 2),
                'masuk' => round($masuk, 2),
                'keluar' => round($keluar, 2),
                'saldo_akhir' => round($saldoAkhir, 2),
                'detail_rows' => $rows,
            ];
            $totalAll['saldo_awal'] += $saldoAwal;
            $totalAll['masuk'] += $masuk;
            $totalAll['keluar'] += $keluar;
            $totalAll['saldo_akhir'] += $saldoAkhir;
        }
        foreach ($totalAll as $k => $v) {
            $totalAll[$k] = round($v, 2);
        }
        return [
            'per_akun' => $perAkun,
            'total' => $totalAll,
        ];
    }
}
