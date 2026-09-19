<?php

use App\Http\Middleware\IsAutentikasiMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RedirectIfAnggota;
use App\Http\Middleware\RedirectIfAuthenticatedMiddleware;
use App\Http\Middleware\RedirectIfKaryawan;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            "autentikasi" => IsAutentikasiMiddleware::class,
            "guest" => RedirectIfAuthenticatedMiddleware::class,
            "administrator" => \App\Http\Middleware\IsAdministratorMiddleware::class,
            "permission" => PermissionMiddleware::class,
            "anggota" => RedirectIfAnggota::class,
            "karyawan" => RedirectIfKaryawan::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage() ?: 'Akses ditolak.',
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->view('errors.403', [
                'message' => $e->getMessage() ?: 'Anda tidak memiliki izin untuk mengakses halaman ini.',
            ], Response::HTTP_FORBIDDEN);
        });
    })->create();
