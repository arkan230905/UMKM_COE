<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'produksi_id','bahan_baku_id','bahan_pendukung_id','qty_resep','satuan_resep','qty_konversi','harga_satuan','subtotal'
    ];

    public function produksi() { return $this->belongsTo(Produksi::class); }
    public function bahanBaku() { return $this->belongsTo(BahanBaku::class); }
    public function bahanPendukung() { return $this->belongsTo(BahanPendukung::class); }
}
