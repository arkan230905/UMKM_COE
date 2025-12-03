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

    public function produk() { return $this->belongsTo(Produk::class); }
    public function details() { return $this->hasMany(ProduksiDetail::class); }
}
