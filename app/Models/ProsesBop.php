<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProsesBop extends Model
{
    use HasFactory;

    protected $table = 'proses_bops';

    protected $fillable = [
        'proses_produksi_id',
        'komponen_bop_id',
        'kuantitas_default'
    ];

    protected $casts = [
        'kuantitas_default' => 'decimal:4'
    ];

    /**
     * Relasi ke Proses Produksi
     */
    public function prosesProduksi()
    {
        return $this->belongsTo(ProsesProduksi::class, 'proses_produksi_id');
    }

    /**
     * Relasi ke Komponen BOP
     */
    public function komponenBop()
    {
        return $this->belongsTo(KomponenBop::class, 'komponen_bop_id');
    }

    /**
     * Hitung total biaya default
     */
    public function getTotalBiayaDefaultAttribute(): float
    {
        return $this->kuantitas_default * ($this->komponenBop->tarif_per_satuan ?? 0);
    }
}
