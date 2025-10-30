<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ReturDetail;

class Retur extends Model
{
    protected $fillable = [
        'type',       // 'sale' | 'purchase'
        'ref_id',     // id penjualan/pembelian sumber
        'tanggal',
        'kompensasi', // 'refund' | 'credit'
        'status',     // 'draft' | 'approved' | 'posted'
        'alasan',
        'memo',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    public function details()
    {
        return $this->hasMany(ReturDetail::class, 'retur_id');
    }
}
