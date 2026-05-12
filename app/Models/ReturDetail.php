<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'retur_id',
        'produk_id',
        'ref_detail_id',
        'qty',
        'harga_satuan_asal',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga_satuan_asal' => 'decimal:2',
    ];

    // Relasi ke retur
    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    // Relasi ke produk
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
