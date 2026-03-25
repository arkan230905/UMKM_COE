<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetailKonversi extends Model
{
    use HasFactory;

    protected $table = 'pembelian_detail_konversi';

    protected $fillable = [
        'pembelian_detail_id',
        'satuan_id',
        'satuan_nama',
        'jumlah_konversi',
        'faktor_konversi_manual',
        'keterangan'
    ];

    protected $casts = [
        'jumlah_konversi' => 'decimal:4',
        'faktor_konversi_manual' => 'decimal:4'
    ];

    /**
     * Relasi ke PembelianDetail
     */
    public function pembelianDetail()
    {
        return $this->belongsTo(PembelianDetail::class, 'pembelian_detail_id');
    }

    /**
     * Relasi ke Satuan
     */
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
}