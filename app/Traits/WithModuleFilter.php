<?php

namespace App\Traits;

use App\Services\ModuleFilterService;
use Illuminate\Http\Request;

trait WithModuleFilter
{
    protected function resolveFilter(Request $request, string $routeKey, array $config = []): array
    {
        return ModuleFilterService::resolve($request, $routeKey, $config);
    }

    protected function applyFilter($query, array $resolved, array $override = [])
    {
        return ModuleFilterService::apply($query, $resolved, $override);
    }

    protected function filterSummary(array $resolved): string
    {
        return ModuleFilterService::summaryText($resolved);
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
}
