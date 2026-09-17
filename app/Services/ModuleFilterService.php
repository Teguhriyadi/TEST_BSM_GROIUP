<?php

namespace App\Services;

use App\Models\Cabang;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleFilterService
{
    public const SESSION_KEY_PREFIX = 'module_filter_v2_';

    public static function defaultConfig(): array
    {
        return [
            'show_cabang' => true,
            'show_tanggal' => true,
            'tanggal_kolom_default' => 'created_at',
            'cabang_kolom_default' => 'cabang_id',
            'force_scope_cabang_user' => false,
            'reset_when_anggota' => true,
            'custom_filters' => [],
        ];
    }

    private static function defaultCustomValues(array $customFilters): array
    {
        $defaults = [];
        foreach ($customFilters as $key => $cfg) {
            $defaults[$key] = $cfg['default'] ?? null;
        }
        return $defaults;
    }

    public static function resolve(Request $request, string $routeKey, array $config = []): array
    {
        $config = array_merge(self::defaultConfig(), $config);
        if (! is_array($config['custom_filters'])) {
            $config['custom_filters'] = [];
        }
        $sessionKey = self::SESSION_KEY_PREFIX . $routeKey;

        $defaultsCustom = self::defaultCustomValues($config['custom_filters']);
        $defaultTanggalAwal = null;
        $defaultTanggalAkhir = null;
        if ($config['show_tanggal']) {
            try {
                $defaultTanggalAwal = Carbon::now()->startOfMonth()->toDateString();
                $defaultTanggalAkhir = Carbon::now()->endOfMonth()->toDateString();
            } catch (\Throwable $e) {
                $defaultTanggalAwal = null;
                $defaultTanggalAkhir = null;
            }
        }
        $default = array_merge([
            'cabang_id' => null,
            'tanggal_awal' => $defaultTanggalAwal,
            'tanggal_akhir' => $defaultTanggalAkhir,
        ], $defaultsCustom);

        if ($config['reset_when_anggota'] && Auth::check() && Auth::user()->hasRole('Anggota')) {
            $request->session()->forget($sessionKey);
            return [
                'filter' => $default,
                'cabang_list' => collect(),
                'custom_filter_options' => self::hydrateCustomOptions($config['custom_filters']),
                'is_anggota' => true,
                'session_key' => $sessionKey,
                'config' => $config,
            ];
        }

        if ($request->has('_reset_filter')) {
            $request->session()->forget($sessionKey);
        }

        $current = $request->session()->get($sessionKey, $default);
        if (! is_array($current)) {
            $current = $default;
        }
        $current = array_merge($default, $current);

        $submitted = $request->isMethod('POST') || $request->has('apply_filter');
        if (! $submitted) {
            foreach (array_keys($default) as $k) {
                if ($request->filled($k)) {
                    $submitted = true;
                    break;
                }
            }
        }

        if ($submitted) {
            $rawCabang = $request->input('cabang_id', $current['cabang_id'] ?? null);
            $rawAwal = $request->input('tanggal_awal', $current['tanggal_awal'] ?? null);
            $rawAkhir = $request->input('tanggal_akhir', $current['tanggal_akhir'] ?? null);

            if (is_string($rawCabang) && trim($rawCabang) === '') {
                $rawCabang = null;
            }
            if (is_string($rawAwal) && trim($rawAwal) === '') {
                $rawAwal = null;
            }
            if (is_string($rawAkhir) && trim($rawAkhir) === '') {
                $rawAkhir = null;
            }

            $current = [
                'cabang_id' => $rawCabang,
                'tanggal_awal' => $rawAwal,
                'tanggal_akhir' => $rawAkhir,
            ];
            foreach (array_keys($defaultsCustom) as $key) {
                $raw = $request->input($key, $current[$key] ?? $defaultsCustom[$key]);
                if (is_string($raw) && trim($raw) === '') {
                    $raw = null;
                }
                $current[$key] = $raw;
            }
            $request->session()->put($sessionKey, $current);
        }

        $user = Auth::user();
        $listCabang = $config['show_cabang']
            ? Cabang::where('is_active', '1')->orderBy('nama_cabang', 'asc')->get(['id', 'kode_cabang', 'nama_cabang'])
            : collect();

        $lockCabangToUser = false;
        $lockedCabangId = null;
        $lockedCabangNama = null;
        $lockedCabangKode = null;

        if ($config['force_scope_cabang_user'] && $user && ! $user->hasRole('Administrator') && ! $user->hasRole('Anggota')) {
            if (! empty($user->cabang_id)) {
                $lockCabangToUser = true;
                $lockedCabangId = $user->cabang_id;
                $current['cabang_id'] = $lockedCabangId;
                if ($user->relationLoaded('cabang') && $user->cabang) {
                    $lockedCabangNama = $user->cabang->nama_cabang;
                    $lockedCabangKode = $user->cabang->kode_cabang;
                } else {
                    $cabLock = Cabang::where('id', $lockedCabangId)->first(['kode_cabang', 'nama_cabang']);
                    if ($cabLock) {
                        $lockedCabangNama = $cabLock->nama_cabang;
                        $lockedCabangKode = $cabLock->kode_cabang;
                    }
                }
                // Force overwrite session agar tidak bisa diubah via session edit juga
                $request->session()->put($sessionKey, $current);
            }
        }

        return [
            'filter' => $current,
            'cabang_list' => $listCabang,
            'custom_filter_options' => self::hydrateCustomOptions($config['custom_filters']),
            'is_anggota' => (bool) ($user && $user->hasRole('Anggota')),
            'lock_cabang_to_user' => $lockCabangToUser,
            'locked_cabang_id' => $lockedCabangId,
            'locked_cabang_nama' => $lockedCabangNama,
            'locked_cabang_kode' => $lockedCabangKode,
            'session_key' => $sessionKey,
            'config' => $config,
        ];
    }

    private static function hydrateCustomOptions(array $customFilters): array
    {
        $out = [];
        foreach ($customFilters as $key => $cfg) {
            $options = $cfg['options'] ?? [];
            if (is_callable($options)) {
                try {
                    $options = $options();
                } catch (\Throwable $e) {
                    $options = [];
                }
            }
            $label = $cfg['label'] ?? ucfirst(str_replace('_', ' ', $key));
            $placeholder = '- Pilih -';
            $out[$key] = [
                'key' => $key,
                'label' => $label,
                'placeholder' => $placeholder,
                'options' => $options,
            ];
        }
        return $out;
    }

    public static function apply(Builder $query, array $resolved, array $override = []): Builder
    {
        $config = array_merge($resolved['config'], $override);
        $filter = $resolved['filter'];
        $tanggalKolom = $override['tanggal_kolom'] ?? $config['tanggal_kolom_default'];
        $cabangKolom = $override['cabang_kolom'] ?? $config['cabang_kolom_default'];
        $alias = $override['alias'] ?? null;
        $prefix = $alias ? $alias . '.' : '';

        if ($config['show_cabang'] && ! empty($filter['cabang_id'])) {
            $query->where($prefix . $cabangKolom, $filter['cabang_id']);
        }

        if ($config['show_tanggal']) {
            $awal = $filter['tanggal_awal'] ?? null;
            $akhir = $filter['tanggal_akhir'] ?? null;
            $dAwal = null;
            $dAkhir = null;
            if (! empty($awal)) {
                try {
                    $dAwal = Carbon::createFromFormat('Y-m-d', $awal)?->startOfDay();
                } catch (\Throwable $e) {
                    $dAwal = null;
                }
            }
            if (! empty($akhir)) {
                try {
                    $dAkhir = Carbon::createFromFormat('Y-m-d', $akhir)?->endOfDay();
                } catch (\Throwable $e) {
                    $dAkhir = null;
                }
            }
            if ($dAwal) {
                $query->whereDate($prefix . $tanggalKolom, '>=', $dAwal->toDateString());
            }
            if ($dAkhir) {
                $query->whereDate($prefix . $tanggalKolom, '<=', $dAkhir->toDateString());
            }
        }

        return $query;
    }

    public static function summaryText(array $resolved): string
    {
        $filter = $resolved['filter'];
        $config = $resolved['config'];
        $parts = [];

        if ($config['show_cabang']) {
            if (! empty($filter['cabang_id'])) {
                $cab = $resolved['cabang_list']->firstWhere('id', $filter['cabang_id']);
                $parts[] = 'Cabang ' . ($cab ? e($cab->nama_cabang) : 'Dipilih');
            } else {
                $parts[] = 'Seluruh Cabang';
            }
        }

        if ($config['show_tanggal']) {
            $awal = $filter['tanggal_awal'] ?? null;
            $akhir = $filter['tanggal_akhir'] ?? null;
            if ($awal && $akhir) {
                try {
                    $parts[] = 'Periode ' . Carbon::createFromFormat('Y-m-d', $awal)->translatedFormat('d M Y') . ' s/d ' . Carbon::createFromFormat('Y-m-d', $akhir)->translatedFormat('d M Y');
                } catch (\Throwable $e) {
                    $parts[] = 'Periode custom';
                }
            } elseif ($awal) {
                try {
                    $parts[] = 'Mulai ' . Carbon::createFromFormat('Y-m-d', $awal)->translatedFormat('d M Y');
                } catch (\Throwable $e) {
                }
            } elseif ($akhir) {
                try {
                    $parts[] = 'Sampai ' . Carbon::createFromFormat('Y-m-d', $akhir)->translatedFormat('d M Y');
                } catch (\Throwable $e) {
                }
            } else {
                $parts[] = 'Seluruh Periode';
            }
        }

        if (! empty($resolved['custom_filter_options'])) {
            foreach ($resolved['custom_filter_options'] as $key => $cfg) {
                $val = $filter[$key] ?? null;
                if ($val !== null && $val !== '') {
                    $label = $val;
                    foreach (($cfg['options'] ?? []) as $opt) {
                        if (strval($opt['value'] ?? '') === strval($val)) {
                            $label = $opt['label'] ?? $val;
                            break;
                        }
                    }
                    $parts[] = ($cfg['label'] ?? ucfirst($key)) . ': ' . e($label);
                }
            }
        }

        return implode(', ', $parts);
    }
}
