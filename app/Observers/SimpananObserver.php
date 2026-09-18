<?php

namespace App\Observers;

use App\Models\Simpanan;
use App\Services\JurnalService;
use Illuminate\Support\Facades\App;

class SimpananObserver
{
    public function created(Simpanan $simpanan): void
    {
        try {
            /** @var JurnalService $svc */
            $svc = App::make(JurnalService::class);
            $svc->createDariSimpanan($simpanan);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
