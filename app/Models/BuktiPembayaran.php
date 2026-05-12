<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiPembayaran extends Model
{
    protected $table = 'bukti_pembayaran';

    protected $fillable = [
        'penjualan_id',
        'file_path',
        'keterangan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }
}
