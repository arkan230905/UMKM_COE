<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Btkl extends Model
{
    use HasFactory;

    protected $table = 'btkls';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'kode_proses',
        'nama_btkl',
        'kualifikasi_id',
        'deskripsi_proses',
        'is_active',
    ];
    
    /**
     * Boot method - auto-fill user_id + global scope multi-tenant
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set user_id saat create
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });

        // Global scope: setiap query hanya ambil data milik user yang login
        static::addGlobalScope('user_id', function ($builder) {
            if (auth()->check()) {
                $builder->where('btkls.user_id', auth()->id());
            }
        });
    }

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relasi ke model Kualifikasi
     */
    public function kualifikasi()
    {
        return $this->belongsTo(Kualifikasi::class, 'kualifikasi_id');
    }

    /**
     * Scope untuk filter BTKL aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get tarif per produk dari kualifikasi
     *
     * @return float
     */
    public function getTarifPerProdukAttribute()
    {
        return $this->kualifikasi ? ($this->kualifikasi->tarif_produk ?? 0) : 0;
    }

    /**
     * Format tarif per produk
     *
     * @return string
     */
    public function getTarifPerProdukFormattedAttribute()
    {
        return 'Rp ' . number_format($this->tarif_per_produk, 0, ',', '.');
    }

    /**
     * Format tarif per jam (backward compatibility)
     * Sekarang menampilkan tarif per produk
     *
     * @return string
     */
    public function getTarifPerJamFormattedAttribute()
    {
        return $this->getTarifPerProdukFormattedAttribute();
    }

    /**
     * Get nama tenaga kerja (alias untuk kualifikasi nama)
     *
     * @return string
     */
    public function getNamaTenagaKerjaAttribute()
    {
        return $this->kualifikasi ? ($this->kualifikasi->nama_kualifikasi ?? '-') : '-';
    }

    /**
     * Get nama proses (alias untuk nama_btkl)
     *
     * @return string
     */
    public function getNamaProsesAttribute()
    {
        return $this->nama_btkl ?? '-';
    }

    /**
     * Get biaya per produk dari proses produksi terkait
     *
     * @return float
     */
    public function getBiayaPerProdukAttribute()
    {
        $proses = ProsesProduksi::where('btkl_id', $this->id)->first();
        return $proses ? $proses->total_btkl : 0;
    }

    /**
     * Get formatted biaya per produk
     *
     * @return string
     */
    public function getBiayaPerProdukFormattedAttribute()
    {
        return 'Rp ' . number_format($this->biaya_per_produk, 2, ',', '.');
    }

    /**
     * Check if tarif is consistent with kualifikasi
     *
     * @return bool
     */
    public function getIsConsistentAttribute()
    {
        if (!$this->kualifikasi) {
            return false;
        }

        $proses = ProsesProduksi::where('btkl_id', $this->id)->first();
        if (!$proses) {
            return true; // Belum ada proses, dianggap konsisten
        }

        $expectedTarif = $this->kualifikasi->tarif_produk ?? 0;
        
        // Toleransi 1 rupiah
        return abs($proses->tarif_per_produk - $expectedTarif) < 1;
    }

    /**
     * Get satuan (tidak digunakan lagi, return default)
     *
     * @return string
     */
    public function getSatuanAttribute()
    {
        return 'Produk';
    }

    /**
     * Get deskripsi_proses for compatibility with view
     *
     * @return string
     */
    public function getDeskripsiProsesAttribute()
    {
        return $this->attributes['deskripsi_proses'] ?? '-';
    }

    /**
     * Get tarif_btkl for compatibility (alias for tarif_per_produk)
     *
     * @return float
     */
    public function getTarifBtklAttribute()
    {
        return $this->tarif_per_produk;
    }

    /**
     * Get tarif_per_jam for compatibility (alias for tarif_per_produk)
     *
     * @return float
     */
    public function getTarifPerJamAttribute()
    {
        return $this->tarif_per_produk;
    }
}