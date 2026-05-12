<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailReturPenjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'retur_penjualan_id',
        'penjualan_detail_id',
        'produk_id',
        'qty_retur',
        'harga_barang',
        'subtotal',
        'keterangan'
    ];

    protected $casts = [
        'qty_retur' => 'decimal:4',
        'harga_barang' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function returPenjualan()
    {
        return $this->belongsTo(ReturPenjualan::class);
    }

    public function penjualanDetail()
    {
        return $this->belongsTo(PenjualanDetail::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detail) {
            // Hitung subtotal otomatis
            $detail->subtotal = $detail->qty_retur * $detail->harga_barang;
        });

        static::saved(function ($detail) {
            // Update total retur di parent
            $detail->returPenjualan->calculateTotalRetur();
        });

        static::deleted(function ($detail) {
            // Update total retur di parent setelah delete
            $detail->returPenjualan->calculateTotalRetur();
        });
    }
}
