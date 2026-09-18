<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Helpers/image_helper.php');
    }

    public function boot(): void
    {
        config([
            'app.timezone' => 'Asia/Jakarta',
            'app.locale' => 'id',
            'app.fallback_locale' => 'id',
            'app.faker_locale' => 'id_ID',
        ]);

        Carbon::setLocale('id');
        Carbon::setToStringFormat('D MMMM Y HH:mm:ss');

        Blade::directive('tanggal', function ($expression) {
            return "<?php
                \$_dt = {$expression};
                if (empty(\$_dt)) {
                    echo '-';
                } elseif (\$_dt instanceof \Carbon\CarbonInterface) {
                    echo \$_dt->isoFormat('D MMMM Y');
                } elseif (is_string(\$_dt)) {
                    try {
                        echo \Carbon\Carbon::parse(\$_dt)->isoFormat('D MMMM Y');
                    } catch (\Throwable) {
                        echo '-';
                    }
                } else {
                    echo '-';
                }
                unset(\$_dt);
            ?>";
        });

        Blade::directive('tanggalWaktu', function ($expression) {
            return "<?php
                \$_dt = {$expression};
                if (empty(\$_dt)) {
                    echo '-';
                } elseif (\$_dt instanceof \Carbon\CarbonInterface) {
                    echo \$_dt->isoFormat('D MMMM Y HH:mm:ss');
                } elseif (is_string(\$_dt)) {
                    try {
                        echo \Carbon\Carbon::parse(\$_dt)->isoFormat('D MMMM Y HH:mm:ss');
                    } catch (\Throwable) {
                        echo '-';
                    }
                } else {
                    echo '-';
                }
                unset(\$_dt);
            ?>";
        });

        Blade::if('haspermission', function (string $kodePermission) {
            $user = Auth::user();
            return (bool) ($user && $user->hasPermission($kodePermission));
        });

        Blade::if('hasanypermission', function (array $kodePermissions) {
            $user = Auth::user();
            if (! $user) {
                return false;
            }
            foreach ($kodePermissions as $kode) {
                if ($user->hasPermission((string) $kode)) {
                    return true;
                }
            }
            return false;
        });
    }
}
