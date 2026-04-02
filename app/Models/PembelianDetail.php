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
        'jumlah' => 'float',
        'harga_satuan' => 'float',
        'subtotal' => 'float',
        'faktor_konversi' => 'decimal:4',
    ];

    protected $appends = ['nama_bahan', 'tipe_bahan', 'jumlah_satuan_utama', 'satuan_utama', 'satuan_nama'];

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
     * Relasi ke Satuan
     */
    public function satuanRelation()
    {
        return $this->belongsTo(Satuan::class, 'satuan', 'id');
    }

    /**
     * Relasi ke PembelianDetailKonversi
     */
    public function konversiManual()
    {
        return $this->hasMany(PembelianDetailKonversi::class, 'pembelian_detail_id');
    }

    /**
     * Relasi ke konversi tambahan (alias)
     */
    public function additionalConversions()
    {
        return $this->hasMany(PembelianDetailKonversi::class, 'pembelian_detail_id');
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
    
    /**
     * Get jumlah dalam satuan utama (untuk keperluan stok)
     */
    public function getJumlahSatuanUtamaAttribute()
    {
        return $this->jumlah * ($this->faktor_konversi ?? 1);
    }
    
    /**
     * Get nama satuan pembelian
     */
    public function getSatuanNamaAttribute()
    {
        if ($this->satuanRelation) {
            return $this->satuanRelation->nama;
        }
        return $this->satuan ?? 'unit';
    }
    
    /**
     * Get nama satuan utama
     */
    public function getSatuanUtamaAttribute()
    {
        if ($this->bahan_baku_id && $this->bahanBaku && $this->bahanBaku->satuan) {
            return $this->bahanBaku->satuan->nama;
        }
        if ($this->bahan_pendukung_id && $this->bahanPendukung && $this->bahanPendukung->satuanRelation) {
            return $this->bahanPendukung->satuanRelation->nama;
        }
        return 'unit';
    }
}
