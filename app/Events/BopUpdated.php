<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BopUpdated
{
    use Dispatchable, SerializesModels;

    public $kodeAkun;
    public $amount;
    public $type;

    /**
     * Create a new event instance.
     *
     * @param string $kodeAkun
     * @param float $amount
     * @param string $type add|subtract|set
     */
    public function __construct($kodeAkun, $amount, $type = 'add')
    {
        $this->kodeAkun = $kodeAkun;
        $this->amount = (float) $amount;
        $this->type = in_array($type, ['add', 'subtract', 'set']) ? $type : 'add';
    }
}
