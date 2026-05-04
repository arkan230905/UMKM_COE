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
        'jabatan_id',
        'tarif_per_jam',
        'satuan',
        'kapasitas_per_jam',
        'deskripsi_proses',
        'is_active'
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
        'tarif_per_jam' => 'decimal:2',
        'kapasitas_per_jam' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Relasi ke model Jabatan
     */
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    /**
     * Scope untuk filter BTKL aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Format tarif per jam
     *
     * @return string
     */
    public function getTarifPerJamFormattedAttribute()
    {
        return 'Rp ' . number_format($this->tarif_per_jam, 0, ',', '.');
    }

    /**
     * Get nama tenaga kerja (alias untuk jabatan nama)
     *
     * @return string
     */
    public function getNamaTenagaKerjaAttribute()
    {
        return $this->jabatan ? $this->jabatan->nama : '-';
    }

    /**
     * Get nama proses (alias untuk jabatan nama)
     *
     * @return string
     */
    public function getNamaProsesAttribute()
    {
        return $this->jabatan ? $this->jabatan->nama : '-';
    }

    /**
     * Calculate biaya per produk automatically
     * Formula: Tarif BTKL ÷ Kapasitas per Jam
     *
     * @return float
     */
    public function getBiayaPerProdukAttribute()
    {
        if ($this->kapasitas_per_jam > 0) {
            return $this->tarif_per_jam / $this->kapasitas_per_jam;
        }
        return 0;
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
}