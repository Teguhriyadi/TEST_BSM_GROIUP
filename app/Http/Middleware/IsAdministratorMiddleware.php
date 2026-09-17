<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdministratorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Login Terlebih Dahulu');
        }

        if (! $user->hasRole('Administrator')) {
            abort(403, 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
