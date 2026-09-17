<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfAnggota
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->hasRole('Anggota')) {
            $allowRoutes = [
                'dashboard',
                'anggota.index',
                'simpanan.index',
                'pinjaman.index',
                'pinjaman.create',
                'pinjaman.store',
                'pinjaman.show',
                'pinjaman.dokumen.upload',
                'password.updateSelf',
                'logout',
                'lupa-password',
            ];
            $current = $request->route()?->getName();
            if ($current && ! str_starts_with($current, 'password.') && ! in_array($current, $allowRoutes, true)) {
                abort(403, 'Akses tersebut hanya untuk petugas koperasi.');
            }
        }
        return $next($request);
    }
}
