<?php

namespace App\Observers;

use App\Models\Pinjaman;
use App\Services\JurnalService;
use Illuminate\Support\Facades\App;

class PinjamanObserver
{
    public function updated(Pinjaman $pinjaman): void
    {
        $originalStatus = (string) $pinjaman->getOriginal('status');
        $newStatus = (string) $pinjaman->status;
        if ($originalStatus !== 'dicairkan' && $newStatus === 'dicairkan') {
            try {
                /** @var JurnalService $svc */
                $svc = App::make(JurnalService::class);
                $svc->createDariPinjamanCair($pinjaman);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
