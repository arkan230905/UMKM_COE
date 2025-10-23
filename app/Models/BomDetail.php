<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bom_id', 'bahan_baku_id', 'jumlah', 'satuan', 'harga_per_satuan', 'total_harga'
    ];

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }
}
