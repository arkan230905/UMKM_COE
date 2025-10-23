<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $fillable = [
        'tanggal', 'vendor_id', 'total',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
