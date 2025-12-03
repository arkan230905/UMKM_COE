<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'retur_id',
        'item_type',
        'item_id',
        'item_nama',
        'qty_retur',
        'satuan',
        'harga_satuan',
        'subtotal',
        'keterangan'
    ];

    protected $casts = [
        'qty_retur' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    // Relasi ke retur
    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    // Relasi polymorphic ke produk atau bahan baku
    public function item()
    {
        if ($this->item_type === 'produk') {
            return $this->belongsTo(Produk::class, 'item_id');
        } elseif ($this->item_type === 'bahan_baku') {
            return $this->belongsTo(BahanBaku::class, 'item_id');
        }
        return null;
    }

    // Accessor untuk mendapatkan item
    public function getItemAttribute()
    {
        if ($this->item_type === 'produk') {
            return Produk::find($this->item_id);
        } elseif ($this->item_type === 'bahan_baku') {
            return BahanBaku::find($this->item_id);
        }
        return null;
    }
}
