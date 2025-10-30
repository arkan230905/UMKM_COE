<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturDetail extends Model
{
    protected $fillable = [
        'retur_id', 'produk_id', 'ref_detail_id', 'qty', 'harga_satuan_asal'
    ];

    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
