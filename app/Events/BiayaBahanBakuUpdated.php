<?php

namespace App\Events;

use App\Models\BiayaBahanBaku;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BiayaBahanBakuUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $biayaBahanBaku;

    /**
     * Create a new event instance.
     */
    public function __construct(BiayaBahanBaku $biayaBahanBaku)
    {
        $this->biayaBahanBaku = $biayaBahanBaku;
    }
}
