<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;

    // Kolom yang boleh diisi
    protected $fillable = [
        'pembelian_id',
        'bahan_baku_id',
        'jumlah',
        'satuan',
        'harga_satuan',
        'subtotal',
    ];

    /**
     * Relasi ke tabel pembelian
     * Detail pembelian ini dimiliki oleh satu pembelian
     */
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class);
    }

    /**
     * Relasi ke tabel bahan baku
     * Setiap detail pembelian merujuk ke satu bahan baku
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
