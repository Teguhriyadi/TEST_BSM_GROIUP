<?php

namespace App\Traits;

use App\Services\ModuleFilterService;
use Illuminate\Http\Request;

trait WithModuleFilter
{
    protected function resolveFilter(Request $request, string $routeKey, array $config = []): array
    {
        try {
            return ModuleFilterService::resolve($request, $routeKey, $config);
        } catch (\Throwable $e) {
            return [
                'cabang_list' => \App\Models\Cabang::where('is_active', '1')->orderBy('nama_cabang', 'asc')->get(),
                'filter' => [
                    'cabang_id' => $request->input('cabang_id'),
                    'tanggal_awal' => $request->input('tanggal_awal'),
                    'tanggal_akhir' => $request->input('tanggal_akhir'),
                ],
                'is_anggota' => false,
                'custom_filter_options' => [],
                'lock_cabang_to_user' => false,
                'locked_cabang_id' => null,
                'locked_cabang_nama' => null,
                'locked_cabang_kode' => null,
            ];
        }
    }

    protected function applyFilter($query, array $resolved, array $override = [])
    {
        try {
            return ModuleFilterService::apply($query, $resolved, $override);
        } catch (\Throwable $e) {
            return $query;
        }
    }

    protected function filterSummary(array $resolved): string
    {
        try {
            return ModuleFilterService::summaryText($resolved);
        } catch (\Throwable $e) {
            return '';
        }
    }

    protected function userForcedCabangId(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }
        if ($user->hasRole('Administrator') || $user->hasRole('Anggota')) {
            return null;
        }
        if (empty($user->cabang_id)) {
            return null;
        }
        return $user->cabang_id;
    }

    protected function buildViewFilterVars(array $resolved): array
    {
        return [
            'cabangFilterList' => $resolved['cabang_list'],
            'currentModuleFilter' => $resolved['filter'],
            'isAnggotaFilter' => $resolved['is_anggota'],
            'customFilterOptions' => $resolved['custom_filter_options'] ?? [],
            'lockCabangToUser' => $resolved['lock_cabang_to_user'] ?? false,
            'lockedCabangId' => $resolved['locked_cabang_id'] ?? null,
            'lockedCabangNama' => $resolved['locked_cabang_nama'] ?? null,
            'lockedCabangKode' => $resolved['locked_cabang_kode'] ?? null,
            'moduleFilterSummary' => $this->filterSummary($resolved),
        ];
    }

    protected function applyModuleFilter($query, Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return $query;
        }
        $isAdmin = $user?->role?->kode_role === 'ROL-ADM';
        if (! $isAdmin && $user?->cabang_id) {
            $cabangCol = method_exists($query->getModel(), 'cabang') ? 'cabang_id' : ($query->getModel()->getTable() . '.cabang_id');
            $query->where($cabangCol, $user->cabang_id);
        } else {
            $cabangReq = $request->input('cabang_id');
            if ($cabangReq) {
                $cabangCol = method_exists($query->getModel(), 'cabang') ? 'cabang_id' : ($query->getModel()->getTable() . '.cabang_id');
                $query->where($cabangCol, $cabangReq);
            }
        }
        return $query;
    }

    protected function getCabangFilterList()
    {
        return \App\Models\Cabang::where('is_active', '1')->orderBy('kode_cabang', 'asc')->orderBy('nama_cabang', 'asc')->get();
    }
}
