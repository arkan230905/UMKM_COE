<?php

namespace App\Events;

use App\Models\ProsesProduksi;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BtklUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $prosesProduksi;

    /**
     * Create a new event instance.
     */
    public function __construct(ProsesProduksi $prosesProduksi)
    {
        $this->prosesProduksi = $prosesProduksi;
    }
}
