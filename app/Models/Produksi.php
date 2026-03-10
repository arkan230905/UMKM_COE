<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'produk_id','tanggal','qty_produksi','total_bahan','total_btkl','total_bop','total_biaya','catatan','status'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty_produksi' => 'decimal:4',
        'total_bahan' => 'decimal:2',
        'total_btkl' => 'decimal:2',
        'total_bop' => 'decimal:2',
        'total_biaya' => 'decimal:2',
    ];

    public function produk() { return $this->belongsTo(Produk::class); }
    public function details() { return $this->hasMany(ProduksiDetail::class); }
}
