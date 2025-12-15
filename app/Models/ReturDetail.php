<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Produk;
use App\Models\BahanBaku;

class ReturDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'retur_id',
        'produk_id',
        'ref_detail_id',
        'qty',
        'harga_satuan_asal',
        // Kolom skema baru
        'item_type',
        'item_id',
        'item_nama',
        'qty_retur',
        'satuan',
        'harga_satuan',
        'subtotal',
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

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'produk_id');
    }

    public function getItemTypeAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return $this->retur && ($this->retur->jenis_retur === 'pembelian') ? 'bahan_baku' : 'produk';
    }

    public function getItemIdAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return $this->produk_id;
    }

    public function getItemNamaAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->produk) {
            return $this->produk->nama_produk;
        }

        if ($this->bahanBaku) {
            return $this->bahanBaku->nama_bahan;
        }

        return '-';
    }

    public function getQtyDisplayAttribute(): float
    {
        $candidates = [
            $this->qty,
            $this->qty_retur,
        ];

        foreach ($candidates as $qty) {
            if (!is_null($qty)) {
                return (float) $qty;
            }
        }

        return 0.0;
    }

    public function getHargaDisplayAttribute(): float
    {
        $candidates = [
            $this->harga_satuan_asal,
            $this->harga_satuan,
        ];

        foreach ($candidates as $harga) {
            if (!is_null($harga)) {
                return (float) $harga;
            }
        }

        return 0.0;
    }

    public function calculateSubtotal(): float
    {
        if (!is_null($this->subtotal)) {
            return (float) $this->subtotal;
        }

        return $this->getQtyDisplayAttribute() * $this->getHargaDisplayAttribute();
    }
}
