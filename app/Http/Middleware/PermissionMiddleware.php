<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissionKodes): Response
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Login Terlebih Dahulu');
        }

        if ($user->hasRole('Administrator')) {
            return $next($request);
        }

        if ($user->hasRole('Anggota')) {
            $current = $request->route()?->getName();
            $routeMap = [
                'dashboard' => ['ANGGOTA_DASHBOARD', 'DASHBOARD_VIEW'],
                'anggota.index' => ['ANGGOTA_DASHBOARD'],
                'simpanan.index' => ['ANGGOTA_SIMPANAN_VIEW'],
                'pinjaman.index' => ['ANGGOTA_PINJAMAN_VIEW'],
                'pinjaman.create' => ['ANGGOTA_PINJAMAN_CREATE'],
                'pinjaman.store' => ['ANGGOTA_PINJAMAN_CREATE'],
                'pinjaman.show' => ['ANGGOTA_PINJAMAN_VIEW', 'ANGGOTA_DOKUMEN_UPLOAD'],
                'pinjaman.dokumen.upload' => ['ANGGOTA_DOKUMEN_UPLOAD'],
            ];
            if ($current && isset($routeMap[$current])) {
                foreach ($routeMap[$current] as $own) {
                    if ($user->hasPermission($own)) {
                        return $next($request);
                    }
                }
            }
        }

        if ($user->hasRole('Karyawan')) {
            $current = $request->route()?->getName();
            $routeMap = [
                'dashboard' => ['ANGGOTA_DASHBOARD', 'DASHBOARD_VIEW'],
                'anggota.index' => ['ANGGOTA_DASHBOARD'],
                'simpanan.index' => ['ANGGOTA_SIMPANAN_VIEW'],
                'pinjaman.index' => ['ANGGOTA_PINJAMAN_VIEW'],
                'pinjaman.create' => ['ANGGOTA_PINJAMAN_CREATE'],
                'pinjaman.store' => ['ANGGOTA_PINJAMAN_CREATE'],
                'pinjaman.show' => ['ANGGOTA_PINJAMAN_VIEW', 'ANGGOTA_DOKUMEN_UPLOAD'],
                'pinjaman.dokumen.upload' => ['ANGGOTA_DOKUMEN_UPLOAD']
            ];
            if ($current && isset($routeMap[$current])) {
                foreach ($routeMap[$current] as $own) {
                    if ($user->hasPermission($own)) {
                        return $next($request);
                    }
                }
            }
        }

        foreach ($permissionKodes as $kode) {
            if ($user->hasPermission($kode)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
