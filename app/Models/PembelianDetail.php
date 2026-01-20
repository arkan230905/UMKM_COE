<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'pembelian_details';

    protected $fillable = [
        'pembelian_id',
        'tipe_item',
        'bahan_baku_id',
        'bahan_pendukung_id',
        'jumlah',
        'satuan',
        'harga_satuan',
        'subtotal',
        'faktor_konversi',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'faktor_konversi' => 'decimal:4',
    ];

    /**
     * Relasi ke Pembelian
     */
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    /**
     * Relasi ke BahanBaku
     */
    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Relasi ke BahanPendukung
     */
    public function bahanPendukung()
    {
        return $this->belongsTo(BahanPendukung::class, 'bahan_pendukung_id');
    }

    /**
     * Alias untuk bahanBaku (untuk backward compatibility)
     */
    public function bahan_baku()
    {
        return $this->bahanBaku();
    }
    
    /**
     * Get nama bahan (bahan baku atau bahan pendukung)
     */
    public function getNamaBahanAttribute()
    {
        if ($this->bahan_baku_id && $this->bahanBaku) {
            return $this->bahanBaku->nama_bahan;
        }
        if ($this->bahan_pendukung_id && $this->bahanPendukung) {
            return $this->bahanPendukung->nama_bahan;
        }
        return '-';
    }
    
    /**
     * Get tipe bahan
     */
    public function getTipeBahanAttribute()
    {
        if ($this->bahan_baku_id) {
            return 'Bahan Baku';
        }
        if ($this->bahan_pendukung_id) {
            return 'Bahan Pendukung';
        }
        return '-';
    }
}
