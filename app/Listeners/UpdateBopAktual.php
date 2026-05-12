<?php

namespace App\Listeners;

use App\Events\BopUpdated;
use App\Services\BopService;

class UpdateBopAktual
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\BopUpdated  $event
     * @return void
     */
    public function handle(BopUpdated $event)
    {
        $kodeAkun = $event->kodeAkun;
        $amount = $event->amount;
        $type = $event->type;

        if ($type === 'set') {
            BopService::recalculateAktual($kodeAkun, $amount);
        } else {
            BopService::updateAktual($kodeAkun, $amount, $type);
        }
    }
}
