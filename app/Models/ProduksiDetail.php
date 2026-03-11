<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduksiDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'produksi_id','bahan_baku_id','bahan_pendukung_id','qty_resep','satuan_resep','qty_konversi','harga_satuan','subtotal','satuan'
    ];

    public function produksi() { return $this->belongsTo(Produksi::class); }
    public function bahanBaku() { return $this->belongsTo(BahanBaku::class); }
    public function bahanPendukung() { return $this->belongsTo(BahanPendukung::class); }
    public function produk() { return $this->hasOneThrough(Produk::class, Produksi::class, 'id', 'id', 'produksi_id', 'produk_id'); }
    
    // Helper method untuk mendapatkan nama bahan
    public function getNamaBahanAttribute()
    {
        if ($this->bahan_baku_id) {
            return $this->bahanBaku->nama_bahan ?? 'Bahan Baku';
        } elseif ($this->bahan_pendukung_id) {
            return $this->bahanPendukung->nama_bahan ?? 'Bahan Pendukung';
        }
        return 'Unknown';
    }
    
    // Helper method untuk mendapatkan jenis bahan
    public function getJenisBahanAttribute()
    {
        if ($this->bahan_baku_id) {
            return 'Bahan Baku';
        } elseif ($this->bahan_pendukung_id) {
            return 'Bahan Pendukung';
        }
        return 'Unknown';
    }
}
